<?php

namespace App\Services;

use App\Models\Langganan;
use App\Models\PaketLangganan;
use App\Models\User;
use Carbon\CarbonInterface;

/**
 * Inti logika langganan — dipakai bersama oleh middleware (mengunci
 * transaksi saat kedaluwarsa) dan controller (status + aktivasi).
 *
 * Status dihitung dari baris langganan TERAKHIR:
 * - 'belum'       : belum pernah berlangganan sama sekali
 * - 'aktif'       : sekarang <= berakhir
 * - 'tenggang'    : lewat berakhir tapi masih dalam masa tenggang paket —
 *                   transaksi MASIH dibolehkan (memberi waktu berpindah
 *                   uang/konfirmasi transfer tanpa toko terhenti)
 * - 'kedaluwarsa' : lewat berakhir + masa tenggang — transaksi dikunci
 */
class LanggananService
{
    public const BELUM = 'belum';
    public const AKTIF = 'aktif';
    public const TENGGANG = 'tenggang';
    public const KEDALUWARSA = 'kedaluwarsa';

    /**
     * Status langganan yang sedang berjalan, bentuk siap dikirim ke aplikasi.
     */
    public function status(): array
    {
        $terakhir = Langganan::with('paket')
            ->orderByDesc('id')
            ->first();

        if (! $terakhir) {
            return [
                'status'        => self::BELUM,
                'mulai'         => null,
                'berakhir'      => null,
                'sisa_hari'     => 0,
                'tenggang_hari' => 0,
                'paket'         => null,
            ];
        }

        $now       = now();
        $berakhir  = $terakhir->berakhir;
        $tenggang  = max(0, (int) ($terakhir->paket->masa_tenggang_hari ?? 3));
        $habisTenggang = $berakhir->copy()->addDays($tenggang);

        $status = self::KEDALUWARSA;
        if ($now->lte($berakhir)) {
            $status = self::AKTIF;
        } elseif ($now->lte($habisTenggang)) {
            $status = self::TENGGANG;
        }

        return [
            'status'        => $status,
            'mulai'         => $terakhir->mulai->toDateTimeString(),
            'berakhir'      => $berakhir->toDateTimeString(),
            'sisa_hari'     => $status === self::AKTIF
                ? $this->selisihHari($now, $berakhir)
                : 0,
            'tenggang_hari' => $status === self::TENGGANG
                ? $this->selisihHari($now, $habisTenggang)
                : 0,
            'paket'         => $terakhir->paket
                ? $this->bentukPaket($terakhir->paket)
                : null,
        ];
    }

    /**
     * True bila transaksi boleh jalan (aktif atau masih tenggang).
     */
    public function bolehTransaksi(): bool
    {
        $s = $this->status();

        return $s['status'] === self::AKTIF || $s['status'] === self::TENGGANG;
    }

    /**
     * Aktifkan/perpanjang dengan paket pilihan.
     *
     * Kalau masa aktif lama belum habis, hari baru disambung SETELAH
     * tanggal berakhir lama — pembayaran lebih awal tidak menghanguskan
     * sisa masa yang sudah dibayar.
     */
    public function aktivasi(User $user, int $idPaket, ?string $idPerangkat = null, ?string $namaPerangkat = null): Langganan
    {
        $paket = PaketLangganan::where('aktif', true)->findOrFail($idPaket);

        $mulai = now();
        $terakhir = Langganan::orderByDesc('id')->first();
        if ($terakhir && now()->lt($terakhir->berakhir)) {
            $mulai = $terakhir->berakhir;
        }

        return Langganan::create([
            'id_paket'      => $paket->id,
            'idUser'        => $user->idUser,
            'nama_user'     => $user->nama,
            'mulai'         => $mulai,
            'berakhir'      => $mulai->copy()->addDays($paket->durasi_hari),
            'id_perangkat'  => $idPerangkat,
            'nama_perangkat'=> $namaPerangkat,
        ]);
    }

    /**
     * Daftar pilihan paket untuk layar Langganan — hanya yang aktif,
     * diurutkan sesuai kolom `urutan` (boleh diatur di database).
     */
    public function daftarPaket(): array
    {
        return PaketLangganan::query()
            ->where('aktif', true)
            ->orderBy('urutan')
            ->orderBy('id')
            ->get()
            ->map(fn ($p) => $this->bentukPaket($p))
            ->all();
    }

    protected function bentukPaket(PaketLangganan $p): array
    {
        return [
            'id'                => (string) $p->id,
            'nama'              => $p->nama,
            'durasi_hari'       => (int) $p->durasi_hari,
            'harga'             => (int) $p->harga,
            'masa_tenggang_hari'=> (int) $p->masa_tenggang_hari,
            'keterangan'        => $p->keterangan,
        ];
    }

    /**
     * Hari dihitung DIBULATKAN KE ATAS: sisa 2 jam tetap terbaca
     * "1 hari" di aplikasi — lebih aman daripada menampilkan 0 padahal
     * sebentar lagi habis.
     */
    protected function selisihHari(CarbonInterface $dari, CarbonInterface $sampai): int
    {
        return max(0, (int) ceil($dari->diffInSeconds($sampai) / 86400));
    }
}
