<?php

namespace App\Services;

use App\Models\Site;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Angka-angka ringkasan penjualan — sumber tunggal untuk dashboard,
 * laporan periode, dan laporan WhatsApp harian.
 *
 * Sebelumnya query yang sama terduplikasi di DashboardController dan
 * LaporanController; keduanya mendelegasikan ke sini supaya laporan WA
 * selalu menampilkan angka yang identik dengan layar aplikasi.
 */
class RingkasanService
{
    /**
     * Kartu angka utama untuk satu rentang tanggal.
     *
     * $kasirId diisi hanya untuk kasir (dashboard membatasi ke miliknya
     * sendiri); pengeluaran operasional sengaja tidak difilter kasir —
     * itu biaya toko, bukan kontribusi kasir.
     */
    public function ringkasan(Carbon $dari, Carbon $sampai, ?string $kasirId = null): array
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
            'omzet'              => (int) $omzet,
            'transaksi'          => (int) $trx,
            'transaksi_batal'    => (int) $batal,
            'item_terjual'       => (int) ($laba->qty ?? 0),
            'laba_kotor'         => $labaKotor,
            'pengeluaran'        => (int) $operasional,
            'laba_bersih'        => $labaBersih,
            'rata_per_transaksi' => $trx > 0 ? (int) round($omzet / $trx) : 0,
        ];
    }

    /**
     * Obat paling laku pada rentang tanggal.
     */
    public function topObat(Carbon $dari, Carbon $sampai, int $limit = 10): array
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
            ->limit($limit)
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
     * Obat yang stoknya sudah di bawah batas minimum toko.
     */
    public function stokKritis(int $limit = 20): array
    {
        $min = $this->batasStokMinimum();

        return DB::table('buku')
            ->where('stock', '<=', $min)
            ->orderBy('stock', 'asc')
            ->limit($limit)
            ->get(['idBuku', 'obat', 'stock', 'barcode'])
            ->map(fn ($r) => [
                'id'          => $r->idBuku,
                'obat'        => $r->obat,
                'stock'       => (int) $r->stock,
                'barcode'     => $r->barcode,
                'stok_status' => $r->stock <= 0 ? 'habis' : 'menipis',
            ])
            ->all();
    }

    /**
     * Cukup jumlahnya (untuk laporan WA) — tanpa memuat daftar obatnya.
     */
    public function jumlahStokMenipis(): int
    {
        return (int) DB::table('buku')
            ->where('stock', '<=', $this->batasStokMinimum())
            ->count();
    }

    protected function batasStokMinimum(): int
    {
        return (int) (Site::first()->min_stok ?? 0);
    }
}
