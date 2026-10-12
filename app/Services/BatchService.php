<?php

namespace App\Services;

use App\Models\BatchObat;
use Illuminate\Support\Facades\DB;

/**
 * Layanan batch obat.
 *
 * Tanggung jawabnya: memastikan penjualan selalu mengambil stok dari batch yang
 * paling cepat kedaluwarsanya (FEFO), dan stok tiap batch tetap sinkron dengan
 * `buku.stock`.
 */
class BatchService
{
    /**
     * Daftar batch yang masih punya stok, urutan FEFO.
     */
    public function sisaBatch(string $idBuku)
    {
        return BatchObat::query()
            ->where('idBuku', $idBuku)
            ->tersisa()
            ->urutFefo()
            ->get();
    }

    /**
     * Ringkasan batch untuk satu obat — dipakai API.
     */
    public function ringkasan(string $idBuku): array
    {
        $batch = $this->sisaBatch($idBuku);

        $terdekat = $batch->first(fn ($b) => $b->expired !== null);

        return [
            'jumlah_batch'  => $batch->count(),
            'batch_terdekat'=> $terdekat?->noBatch,
            'kedaluwarsa_terdekat' => $terdekat?->expired?->toDateString(),
            'hari_lagi_terdekat'   => $terdekat?->hari_lagi,
            'sisa_per_batch' => $batch->map(fn ($b) => [
                'no_batch'   => $b->noBatch,
                'expired'    => $b->expired?->toDateString(),
                'hari_lagi'  => $b->hari_lagi,
                'stok'       => $b->stokTersisa,
                'harga_pokok'=> $b->hargaPokok,
            ])->values()->all(),
        ];
    }

    /**
     * Kurangi stok batch sesuai FEFO.
     *
     * Dipanggil di dalam transaksi DB yang sama dengan penyimpanan penjualan,
     * supaya `buku.stock` (dikurangi trigger MySQL) dan `batch_obat.stokKeluar`
     * tidak pernah berbeda.
     *
     * @param  array  $items  peta idBuku => jumlah terjual
     * @return array detail batch yang terpakai
     */
    public function kurangiFefo(array $items): array
    {
        $dipakai = [];

        foreach ($items as $idBuku => $jumlah) {
            $sisa = (int) $jumlah;

            $batch = $this->sisaBatch($idBuku);

            foreach ($batch as $b) {
                if ($sisa <= 0) {
                    break;
                }

                $ambil = min($sisa, $b->stokTersisa);

                if ($ambil <= 0) {
                    continue;
                }

                $b->increment('stokKeluar', $ambil);
                $sisa -= $ambil;

                $dipakai[] = [
                    'id_batch' => $b->idBatch,
                    'id_buku'  => $idBuku,
                    'no_batch' => $b->noBatch,
                    'expired'  => $b->expired?->toDateString(),
                    'ambil'    => $ambil,
                ];
            }
        }

        return $dipakai;
    }

    /**
     * Kembalikan stok ke batch (dipakai saat transaksi dibatalkan).
     *
     * Batch yang di-return dipilih dari yang paling cepat kedaluwarsa, supaya
     * lokasi fisik obat tidak berantakan.
     */
    public function kembalikanFefo(array $items): void
    {
        foreach ($items as $idBuku => $jumlah) {
            $sisa = (int) $jumlah;

            $batch = BatchObat::query()
                ->where('idBuku', $idBuku)
                ->urutFefo()
                ->get();

            foreach ($batch as $b) {
                if ($sisa <= 0) {
                    break;
                }

                // Jangan sampai stokKeluar jadi negatif.
                $bisa = min($sisa, $b->stokKeluar);
                if ($bisa <= 0) {
                    continue;
                }

                $b->decrement('stokKeluar', $bisa);
                $sisa -= $bisa;
            }
        }
    }

    /**
     * Catat batch baru saat pembelian (dipanggil modul pembelian nanti).
     */
    public function catatBatch(array $data): BatchObat
    {
        return BatchObat::create([
            'idBuku'     => $data['id_buku'],
            'noBatch'    => $data['no_batch'] ?? null,
            'expired'    => $data['expired'] ?? null,
            'hargaPokok' => $data['harga_pokok'] ?? 0,
            'stokMasuk'  => $data['jumlah'] ?? 0,
            'stokKeluar' => 0,
        ]);
    }

    /**
     * Cek konsistensi: total sisa batch harus sama dengan `buku.stock`.
     *
     * Dipakai sebagai Endpoint `/api/batch/cek` supaya masalah ketahuan cepat.
     */
    public function cekKonsistensi(int $limit = 50): array
    {
        $beda = DB::select(
            'SELECT b.idBuku, b.obat, b.stock,
                    COALESCE(SUM(bt.stokMasuk - bt.stokKeluar), 0) AS sisaBatch
             FROM buku b
             LEFT JOIN batch_obat bt ON bt.idBuku = b.idBuku
             GROUP BY b.idBuku, b.obat, b.stock
             HAVING b.stock <> COALESCE(SUM(bt.stokMasuk - bt.stokKeluar), 0)
             LIMIT ' . (int) $limit
        );

        return array_map(fn ($r) => [
            'id'         => $r->idBuku,
            'obat'       => $r->obat,
            'stok_buku'  => (int) $r->stock,
            'sisa_batch' => (int) $r->sisaBatch,
            'selisih'    => (int) $r->stock - (int) $r->sisaBatch,
        ], $beda);
    }
}