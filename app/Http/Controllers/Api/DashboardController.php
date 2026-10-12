<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FinanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Ringkasan untuk layar dashboard.
 *
 * Semua angka dihitung ulang dari tabel penjualan/detail, bukan dari cache,
 * supaya dashboard selalusinkron dengan transaksi yang baru saja masuk.
 */
class DashboardController extends Controller
{
    public function __construct(protected FinanceService $finance) {}

    /**
     * GET /api/dashboard?tanggal=YYYY-MM-DD
     */
    public function index(Request $request): JsonResponse
    {
        $tanggal = $request->filled('tanggal')
            ? Carbon::parse($request->query('tanggal'))
            : Carbon::today();

        $dari = $tanggal->copy()->startOfDay();
        $sampai = $tanggal->copy()->endOfDay();

        // Untuk kasir, angka dibatasi ke miliknya sendiri supaya tidak
        // membocorkan performance orang lain.
        $user = $request->user();
        $kasirId = $user->can('isAdmin') ? null : $user->idUser;

        $data = [
            'tanggal' => $tanggal->toDateString(),

            'ringkasan' => $this->ringkasan($dari, $sampai, $kasirId),

            'grafik' => $this->grafikMinggu($kasirId),

            'top_obat' => $this->topObat($dari, $sampai),

            'stok_kritis' => $this->stokKritis(),

            'mendekati_kedaluwarsa' => $this->mendekatiKedaluwarsa(),

            'transaksi_terbaru' => $this->transaksiTerbaru($user),
        ];

        // Cek konsistensi batch hanya untuk admin. Query-nya memindai seluruh
        // tabel, jadi kasir tidak perlu menanggung beban itu.
        if ($user->can('isAdmin')) {
            $data['kesehatan_batch'] = $this->kesehatanBatch();
        }

        return response()->json($data);
    }

    /**
     * Apakah sisa seluruh batch masih cocok dengan `buku.stock`?
     *
     * Kalau tidak cocok berarti ada penjualan di luar API (mis. lewat web) atau
     * ada koreksi stok manual yang tidak ikut memperbarui batch. Worth
     * ditcitepkan owner karena InventoryValue jadi tidak bisa dipercaya.
     */
    protected function kesehatanBatch(): array
    {
        $selisih = DB::select('
            SELECT COUNT(*) AS jml
            FROM (
                SELECT b.idBuku
                FROM buku b
                LEFT JOIN batch_obat t ON t.idBuku = b.idBuku
                GROUP BY b.idBuku, b.stock
                HAVING b.stock <> COALESCE(SUM(t.stokMasuk - t.stokKeluar), 0)
            ) x
        ');

        $jumlah = (int) ($selisih[0]->jml ?? 0);

        return [
            'sehat'      => $jumlah === 0,
            'jumlah'     => $jumlah,
            'pesan'      => $jumlah === 0
                ? 'Sisa seluruh batch cocok dengan stok buku.'
                : "$jumlah obat selisihnya antara batch dan stok buku.",
        ];
    }

    // ------------------------------------------------------------ bagian

    /**
     * Kartu-kartu angka utama.
     */
    protected function ringkasan($dari, $sampai, ?string $kasirId): array
    {
        $pajak = DB::table('penjualan')
            ->whereBetween('tanggal', [$dari, $sampai])
            ->where('status', 1)
            ->when($kasirId, fn ($q) => $q->where('idUser', $kasirId));

        $omzet = (clone $pajak)->sum('total');
        $trx = (clone $pajak)->count();

        $batal = DB::table('penjualan')
            ->whereBetween('tanggal', [$dari, $sampai])
            ->where('status', 0)
            ->when($kasirId, fn ($q) => $q->where('idUser', $kasirId))
            ->count();

        // Laba kotor = (harga jual - diskon) - harga pokok, dari baris detail.
        // Pakai SUM per baris supaya akurat walau satu obat terjual >1 batch.
        $laba = DB::table('detail_penjualan as d')
            ->join('penjualan as p', 'd.idPenjualan', '=', 'p.idPenjualan')
            ->whereBetween('p.tanggal', [$dari, $sampai])
            ->where('p.status', 1)
            ->when($kasirId, fn ($q) => $q->where('p.idUser', $kasirId))
            ->selectRaw('
                COALESCE(SUM(d.hargaJual * d.jumlah), 0) - COALESCE(SUM(d.disc), 0) AS pemasukan,
                COALESCE(SUM(d.hargaPokok * d.jumlah), 0) AS modal,
                COALESCE(SUM(d.jumlah), 0) AS qty
            ')
            ->first();

        $pemasukan = (int) ($laba->pemasukan ?? 0);
        $modal = (int) ($laba->modal ?? 0);

        // Pengeluaran operasional pada tanggal yang sama.
        $operasional = DB::table('pengeluaran')
            ->whereBetween('tanggal', [$dari, $sampai])
            ->sum('pengeluaran');

        $labaKotor = $pemasukan - $modal;
        $labaBersih = $labaKotor - $operasional;

        return [
            // SUM() di MariaDB/MySQL mengembalikan string, jadi harus
            // di-casting agar tipe JSON-nya number (bukan "134300").
            'omzet'           => (int) $omzet,
            'transaksi'       => (int) $trx,
            'transaksi_batal' => (int) $batal,
            'item_terjual'    => (int) ($laba->qty ?? 0),
            'laba_kotor'      => $labaKotor,
            'pengeluaran'     => (int) $operasional,
            'laba_bersih'     => $labaBersih,
            'rata_per_transaksi' => $trx > 0 ? (int) round($omzet / $trx) : 0,
        ];
    }

    /**
     * Omzet 7 hari terakhir untuk grafik batang.
     */
    protected function grafikMinggu(?string $kasirId): array
    {
        $mulai = Carbon::today()->subDays(6)->startOfDay();
        $akhir = Carbon::today()->endOfDay();

        $baris = DB::table('penjualan')
            ->whereBetween('tanggal', [$mulai, $akhir])
            ->where('status', 1)
            ->when($kasirId, fn ($q) => $q->where('idUser', $kasirId))
            ->selectRaw('DATE(tanggal) as t, SUM(total) as omzet, COUNT(*) as trx')
            ->groupBy(DB::raw('DATE(tanggal)'))
            ->pluck('omzet', 't');

        $jumlah = DB::table('penjualan')
            ->whereBetween('tanggal', [$mulai, $akhir])
            ->where('status', 1)
            ->when($kasirId, fn ($q) => $q->where('idUser', $kasirId))
            ->selectRaw('DATE(tanggal) as t, COUNT(*) as c')
            ->groupBy(DB::raw('DATE(tanggal)'))
            ->pluck('c', 't');

        $hasil = [];

        // Singkatan hari dalam Bahasa Indonesia. Carbon::translatedFormat()
        // memakai locale global yang di aplikasi ini belum di-set, jadi
        // lebih aman memakai daftar tetap.
        $hariSingkat = [
            Carbon::SUNDAY    => 'Min',
            Carbon::MONDAY    => 'Sen',
            Carbon::TUESDAY   => 'Sel',
            Carbon::WEDNESDAY => 'Rab',
            Carbon::THURSDAY  => 'Kam',
            Carbon::FRIDAY    => 'Jum',
            Carbon::SATURDAY  => 'Sab',
        ];

        for ($i = 0; $i < 7; $i++) {
            $d = $mulai->copy()->addDays($i);
            $tanggal = $d->toDateString();

            $hasil[] = [
                'tanggal'   => $tanggal,
                'label'     => $hariSingkat[$d->dayOfWeek] ?? '-',
                'omzet'     => (int) ($baris[$tanggal] ?? 0),
                'transaksi' => (int) ($jumlah[$tanggal] ?? 0),
            ];
        }

        return $hasil;
    }

    /**
     * Obat paling laku pada tanggal terpilih.
     */
    protected function topObat($dari, $sampai): array
    {
        return DB::table('detail_penjualan as d')
            ->join('penjualan as p', 'd.idPenjualan', '=', 'p.idPenjualan')
            ->whereBetween('p.tanggal', [$dari, $sampai])
            ->where('p.status', 1)
            ->selectRaw('
                d.idBuku,
                d.obat,
                SUM(d.jumlah) AS qty,
                SUM(d.hargaJual * d.jumlah - d.disc) AS omzet
            ')
            ->groupBy('d.idBuku', 'd.obat')
            ->orderByDesc('qty')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'id_buku' => $r->idBuku,
                'obat'   => $r->obat,
                'qty'    => (int) $r->qty,
                'omzet'  => (int) $r->omzet,
            ])
            ->all();
    }

    /**
     * Obat yang stoksnya sudah di bawah batas minimum.
     */
    protected function stokKritis(): array
    {
        $min = (int) (\App\Models\Site::first()->min_stok ?? 0);

        return DB::table('buku')
            ->where('stock', '<=', $min)
            ->orderBy('stock', 'asc')
            ->limit(20)
            ->get(['idBuku', 'obat', 'stock', 'barcode'])
            ->map(fn ($r) => [
                'id'         => $r->idBuku,
                'obat'       => $r->obat,
                'stock'      => (int) $r->stock,
                'barcode'    => $r->barcode,
                'stok_status'=> $r->stock <= 0 ? 'habis' : 'menipis',
            ])
            ->all();
    }

    /**
     * Batch obat yang akan segera kedaluwarsa.
     *
     * Sumbernya `batch_obat`, bukan `detail_pembelian` langsung. Bedanya
     * penting: `detail_pembelian` masih mencatat batch yang stoknya sudah
     * habis terjual, sehingga apotek bisa diberi peringatan obat kedaluwarsa
     * yang sebenarnya sudah tidak ada di rak. `batch_obat` hanya menyimpan
     * batch yang masih punya sisa stok.
     *
     * Batch yang tanggalnya sudah lewat tetap ditampilkan (paling atas,
     * pakai nilai `hari_lagi` negatif) karena justru itu yang paling perlu
     * dibuang — jangan diam-diam ikut terjual.
     */
    protected function mendekatiKedaluwarsa(): array
    {
        $batas = Carbon::today()->addDays(90)->endOfDay();

        $rows = DB::table('batch_obat as t')
            ->join('buku as b', 't.idBuku', '=', 'b.idBuku')
            ->whereNotNull('t.expired')
            ->where('t.expired', '>', '2000-01-01')
            ->where('t.expired', '<=', $batas)
            // Hanya batch yang masih ada barangnya.
            ->whereColumn('t.stokMasuk', '>', 't.stokKeluar')
            ->selectRaw('
                b.idBuku,
                b.obat,
                MIN(t.expired) AS kedaluwarsa,
                SUM(t.stokMasuk - t.stokKeluar) AS stokBatch,
                COALESCE(b.stock, 0) AS stock
            ')
            ->groupBy('b.idBuku', 'b.obat', 'b.stock')
            ->orderBy('kedaluwarsa', 'asc')
            ->limit(20)
            ->get();

        return $rows
            ->map(function ($r) {
                $kedaluwarsa = Carbon::parse($r->kedaluwarsa)->startOfDay();
                $sisa = (int) now()->startOfDay()->diffInDays($kedaluwarsa, false);

                return [
                    'id'           => $r->idBuku,
                    'obat'         => $r->obat,
                    'kedaluwarsa'  => $kedaluwarsa->toDateString(),
                    'hari_lagi'    => $sisa,
                    'sudah_lewat'  => $sisa < 0,
                    'stock'        => (int) $r->stock,
                    // Stok pada batch berumur terdekat — inilah yang harus
                    // keluar lebih dulu sesuai FEFO.
                    'stok_batch'   => (int) $r->stokBatch,
                ];
            })
            ->all();
    }

    /**
     * Enam transaksi terakhir.
     */
    protected function transaksiTerbaru($user): array
    {
        $bisaLihatSemua = $user->can('isAdmin') || $user->can('isAdminGudang');

        return DB::table('penjualan')
            ->when(! $bisaLihatSemua, fn ($q) => $q->where('idUser', $user->idUser))
            ->orderByDesc('idbaru')
            ->limit(6)
            ->get(['idPenjualan', 'namaUser', 'tanggal', 'total', 'status'])
            ->map(fn ($r) => [
                'id'        => $r->idPenjualan,
                'nama_user' => $r->namaUser,
                'tanggal'   => Carbon::parse($r->tanggal)->toDateTimeString(),
                'total'     => (int) $r->total,
                'status'    => (bool) $r->status,
            ])
            ->all();
    }
}