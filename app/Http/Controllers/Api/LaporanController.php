<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Laporan penjualan periode (admin).
 *
 * Berbeda dari /dashboard yang selalu "hari ini + grafik 7 hari", laporan
 * menghitung angka untuk rentang tanggal bebas yang dipilih pemilik:
 * ringkasan laba, omzet per hari (grafik), obat terlaris, dan omzet per
 * kasir.
 */
class LaporanController extends Controller
{
    /**
     * GET /api/laporan?dari=YYYY-MM-DD&sampai=YYYY-MM-DD
     *
     * Default rentang: bulan ini. Maksimum 366 hari supaya satu salah
     * ketik tahun tidak melipat gandakan beban query.
     */
    public function index(Request $request): JsonResponse
    {
        $dari = $request->filled('dari')
            ? Carbon::parse($request->query('dari'))->startOfDay()
            : Carbon::today()->startOfMonth();

        $sampai = $request->filled('sampai')
            ? Carbon::parse($request->query('sampai'))->endOfDay()
            : Carbon::today()->endOfDay();

        if ($dari->gt($sampai)) {
            [$dari, $sampai] = [$sampai->copy()->startOfDay(), $dari->copy()->endOfDay()];
        }

        if ($dari->diffInDays($sampai) > 366) {
            $sampai = $dari->copy()->addDays(366)->endOfDay();
        }

        return response()->json([
            'dari'   => $dari->toDateString(),
            'sampai' => $sampai->toDateString(),
            'ringkasan'   => $this->ringkasan($dari, $sampai),
            'per_hari'    => $this->perHari($dari, $sampai),
            'top_obat'    => $this->topObat($dari, $sampai),
            'per_kasir'   => $this->perKasir($dari, $sampai),
        ]);
    }

    // ------------------------------------------------------------ bagian

    protected function ringkasan($dari, $sampai): array
    {
        $omzet = (int) DB::table('penjualan')
            ->whereBetween('tanggal', [$dari, $sampai])
            ->where('status', 1)
            ->sum('total');

        $trx = (int) DB::table('penjualan')
            ->whereBetween('tanggal', [$dari, $sampai])
            ->where('status', 1)
            ->count();

        $batal = (int) DB::table('penjualan')
            ->whereBetween('tanggal', [$dari, $sampai])
            ->where('status', 0)
            ->count();

        $laba = DB::table('detail_penjualan as d')
            ->join('penjualan as p', 'd.idPenjualan', '=', 'p.idPenjualan')
            ->whereBetween('p.tanggal', [$dari, $sampai])
            ->where('p.status', 1)
            ->selectRaw('
                COALESCE(SUM(d.hargaJual * d.jumlah), 0) - COALESCE(SUM(d.disc), 0) AS pemasukan,
                COALESCE(SUM(d.hargaPokok * d.jumlah), 0) AS modal,
                COALESCE(SUM(d.jumlah), 0) AS qty
            ')
            ->first();

        $operasional = (int) DB::table('pengeluaran')
            ->whereBetween('tanggal', [$dari, $sampai])
            ->sum('pengeluaran');

        $labaKotor = (int) ($laba->pemasukan ?? 0) - (int) ($laba->modal ?? 0);

        return [
            'omzet'              => $omzet,
            'transaksi'          => $trx,
            'transaksi_batal'    => $batal,
            'item_terjual'       => (int) ($laba->qty ?? 0),
            'laba_kotor'         => $labaKotor,
            'pengeluaran'        => $operasional,
            'laba_bersih'        => $labaKotor - $operasional,
            'rata_per_transaksi' => $trx > 0 ? (int) round($omzet / $trx) : 0,
        ];
    }

    /**
     * Omzet per hari untuk grafik — setiap tanggal dalam rentang selalu
     * punya entri (0 kalau tidak ada penjualan) supaya grafik tidak bolong.
     */
    protected function perHari($dari, $sampai): array
    {
        $baris = DB::table('penjualan')
            ->whereBetween('tanggal', [$dari, $sampai])
            ->where('status', 1)
            ->selectRaw('DATE(tanggal) as t, SUM(total) as omzet, COUNT(*) as trx')
            ->groupBy(DB::raw('DATE(tanggal)'))
            ->get()
            ->keyBy('t');

        $hasil = [];
        for ($d = $dari->copy(); $d->lte($sampai); $d->addDay()) {
            $tanggal = $d->toDateString();
            $b = $baris->get($tanggal);

            $hasil[] = [
                'tanggal'   => $tanggal,
                'label'     => $d->format('d/m'),
                'omzet'     => (int) ($b->omzet ?? 0),
                'transaksi' => (int) ($b->trx ?? 0),
            ];
        }

        return $hasil;
    }

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
                'obat'    => $r->obat,
                'qty'     => (int) $r->qty,
                'omzet'   => (int) $r->omzet,
            ])
            ->all();
    }

    /**
     * Omzet per kasir — pemilik bisa melihat kontribusi tiingkat kasir.
     */
    protected function perKasir($dari, $sampai): array
    {
        return DB::table('penjualan')
            ->whereBetween('tanggal', [$dari, $sampai])
            ->where('status', 1)
            ->selectRaw('idUser, namaUser, COUNT(*) AS trx, SUM(total) AS omzet')
            ->groupBy('idUser', 'namaUser')
            ->orderByDesc('omzet')
            ->limit(20)
            ->get()
            ->map(fn ($r) => [
                'id_user' => $r->idUser,
                'nama'    => $r->namaUser,
                'transaksi' => (int) $r->trx,
                'omzet'     => (int) $r->omzet,
            ])
            ->all();
    }
}
