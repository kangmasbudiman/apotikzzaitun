<?php

namespace App\Services;

use App\Models\Site;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Laporan penjualan harian otomatis ke WhatsApp pemilik toko.
 *
 * Laporan yang dikirim adalah penjualan KEMARIN (penuh satu hari), bukan
 * hari berjalan, supaya angkanya final. Pemicunya bisa apa saja — aplikasi
 * saat dibuka, atau cron `php artisan laporan:wa` — idempotensi ditahan
 * kolom `laporan_wa_terakhir`: laporan satu tanggal hanya dikirim sekali.
 *
 * Token gateway Fonnte & nomor tujuan disimpan di tabel `pengaturan` dan
 * diatur admin dari aplikasi, jadi tiap deployment mandiri tanpa .env.
 */
class LaporanWaService
{
    /** Daftar manual — locale Carbon tidak di-set di aplikasi ini. */
    protected const HARI = [
        Carbon::SUNDAY    => 'Minggu',
        Carbon::MONDAY    => 'Senin',
        Carbon::TUESDAY   => 'Selasa',
        Carbon::WEDNESDAY => 'Rabu',
        Carbon::THURSDAY  => 'Kamis',
        Carbon::FRIDAY    => 'Jumat',
        Carbon::SATURDAY  => 'Sabtu',
    ];

    public function __construct(protected RingkasanService $ringkasan) {}

    /**
     * Kirim laporan kemarin bila semua syarat terpenuhi & belum terkirim.
     *
     * Selalu mengembalikan status (tidak melempar exception) supaya pemicu
     * dari aplikasi/cron tidak perlu penanganan khusus.
     */
    public function kirimJikaPerlu(): array
    {
        $site = Site::first();

        if (! $site || ! $site->laporan_wa_aktif) {
            return ['terkirim' => false, 'pesan' => 'Laporan WA harian nonaktif.'];
        }

        if (empty($site->wa_pemilik)) {
            return ['terkirim' => false, 'pesan' => 'Nomor WhatsApp pemilik belum diisi.'];
        }

        if (empty($site->wa_token)) {
            return ['terkirim' => false, 'pesan' => 'Token gateway Fonnte belum diisi.'];
        }

        $kemarin = now()->subDay()->startOfDay();

        if ($site->laporan_wa_terakhir
            && Carbon::parse($site->laporan_wa_terakhir)->startOfDay()->gte($kemarin)) {
            return [
                'terkirim' => false,
                'pesan'    => 'Laporan tanggal '.$kemarin->format('d-m-Y').' sudah terkirim sebelumnya.',
            ];
        }

        try {
            $this->kirim($site->wa_pemilik, $this->susunPesan($kemarin));
        } catch (\Throwable $e) {
            Log::error('Laporan WA gagal terkirim: '.$e->getMessage());

            // laporan_wa_terakhir sengaja tidak diubah supaya mencoba lagi
            // pada pemicu berikutnya (aplikasi dibuka / cron).
            return ['terkirim' => false, 'pesan' => 'Gagal mengirim laporan WA: '.$e->getMessage()];
        }

        $site->update(['laporan_wa_terakhir' => $kemarin->toDateString()]);
        cache()->forget('site');

        return [
            'terkirim' => true,
            'pesan'    => 'Laporan '.$kemarin->format('d-m-Y').' terkirim ke '.$site->wa_pemilik.'.',
        ];
    }

    /**
     * Pesan uji pendek — untuk memverifikasi token & nomor dari layar
     * pengaturan. Tidak menyentuh penanda idempotensi.
     */
    public function kirimUji(): array
    {
        $site = Site::first();

        if (! $site || empty($site->wa_pemilik)) {
            return ['terkirim' => false, 'pesan' => 'Nomor WhatsApp pemilik belum diisi.'];
        }

        if (empty($site->wa_token)) {
            return ['terkirim' => false, 'pesan' => 'Token gateway Fonnte belum diisi.'];
        }

        $nama = $site->nama_sistem ?: 'FARMALIX';

        try {
            $this->kirim(
                $site->wa_pemilik,
                "*PESAN UJI — {$nama}*\n\n"
                .'Pengaturan laporan WA harian sudah benar. '
                ."Laporan penjualan akan masuk ke nomor ini setiap hari.\n\n"
                .'Simpan pesan ini? Tidak perlu — ini hanya uji.'
            );
        } catch (\Throwable $e) {
            Log::error('Pesan uji WA gagal terkirim: '.$e->getMessage());

            return ['terkirim' => false, 'pesan' => 'Gagal mengirim pesan uji: '.$e->getMessage()];
        }

        return ['terkirim' => true, 'pesan' => 'Pesan uji terkirim ke '.$site->wa_pemilik.'.'];
    }

    /**
     * Kirim teks lewat gateway Fonnte.
     *
     * @throws \RuntimeException saat gateway menolak/menolak terhubung.
     */
    protected function kirim(string $target, string $pesan): void
    {
        $token = Site::first()->wa_token;

        $respons = Http::withHeaders(['Authorization' => $token])
            ->timeout(15)
            ->acceptJson()
            ->post('https://api.fonnte.com/send', [
                'target'  => $target,
                'message' => $pesan,
            ]);

        if ($respons->failed()) {
            throw new \RuntimeException('HTTP '.$respons->status());
        }

        // Fonnte membalas 200 dengan {"status": false, "reason": ...}
        // untuk kegagalan seperti token salah atau nomor tujuan tanpa WA.
        $isi = $respons->json();

        if (! ($isi['status'] ?? false)) {
            throw new \RuntimeException($isi['reason'] ?? 'ditolak gateway');
        }
    }

    /**
     * Susun laporan satu hari penuh.
     */
    public function susunPesan(Carbon $tanggal): string
    {
        $site = Site::first();

        $dari = $tanggal->copy()->startOfDay();
        $sampai = $tanggal->copy()->endOfDay();

        $r = $this->ringkasan->ringkasan($dari, $sampai);
        $top = $this->ringkasan->topObat($dari, $sampai, 3);

        $bonBaru = DB::table('piutang')
            ->whereDate('tanggal', $tanggal->toDateString())
            ->selectRaw('COUNT(*) AS jml, COALESCE(SUM(nominal), 0) AS total')
            ->first();

        $belumLunas = DB::table('piutang')
            ->where('status', 1)
            ->selectRaw('COUNT(*) AS jml, COALESCE(SUM(nominal), 0) AS total')
            ->first();

        $b = implode("\n", array_filter([
            '*LAPORAN HARIAN — '.strtoupper($site->nama_toko ?: 'TOKO').'*',
            'Tanggal: '.self::HARI[$tanggal->dayOfWeek].', '.$tanggal->format('d-m-Y'),
            '',
            'Omzet: '.$this->rp($r['omzet']),
            'Transaksi: '.$r['transaksi']
                .($r['transaksi_batal'] > 0 ? ' ('.$r['transaksi_batal'].' batal)' : ''),
            'Item terjual: '.$r['item_terjual'].' pcs',
            'Laba kotor: '.$this->rp($r['laba_kotor']),
            'Pengeluaran: '.$this->rp($r['pengeluaran']),
            'Laba bersih: '.$this->rp($r['laba_bersih']),
            'Rata-rata/transaksi: '.$this->rp($r['rata_per_transaksi']),
        ]));

        if ($top !== []) {
            $b .= "\n\nObat terlaris:";

            foreach ($top as $i => $t) {
                $b .= "\n".($i + 1).'. '.$t['obat'].' — '.$t['qty'].' pcs ('.$this->rp($t['omzet']).')';
            }
        }

        $menipis = $this->ringkasan->jumlahStokMenipis();

        $b .= "\n\nBon/piutang baru: ".(int) $bonBaru->jml.' ('.$this->rp($bonBaru->total).')';
        $b .= "\nBelum lunas: ".(int) $belumLunas->jml.' bon ('.$this->rp($belumLunas->total).')';
        $b .= "\nStok menipis: ".$menipis.' obat';

        return $b;
    }

    protected function rp($angka): string
    {
        return 'Rp '.number_format((float) $angka, 0, ',', '.');
    }
}
