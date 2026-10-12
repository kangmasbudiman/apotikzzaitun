<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;

/**
 * Tabel batch obat — dasar untuk penjualan FEFO (First Expired, First Out).
 *
 * Latar belakang: kolom `nobatch` dan `expired` di `detail_pembelian` sudah
 * terisi di ribuan baris (nomor batch asli seperti DB2437, DC2986), tapi
 * tidak pernah dipakai untuk menentukan batch mana yang sebaiknya dijual
 * lebih dulu. Akibatnya apotek bisa menjual obat yang hampir kedaluwarsa
 * padahal masih ada stok lain yang lebih lama.
 *
 * Setiap baris = satu batch fisik obat beserta sisa stoknya.
 * `buku.stock` tetap jadi sumber kebenaran total; tabel ini hanya memecah
 * total itu per batch supaya bisa immersion urut kedaluwarsa.
 */
class CreateBatchObatTable extends Migration
{
    public function up()
    {
        Schema::create('batch_obat', function ($table) {
            $table->id('idBatch');

            $table->string('idBuku', 10);
            $table->string('noBatch', 50)->nullable();
            $table->date('expired')->nullable();

            $table->integer('hargaPokok')->default(0);
            $table->integer('stokMasuk')->default(0);
            $table->integer('stokKeluar')->default(0);

            // Asal data — dipakai untuk penelusuran.
            $table->string('idPembelian', 20)->nullable();
            $table->string('idDetailPembelian', 6)->nullable();

            $table->timestamps();

            // Pencarian FEFO: "batch yang masih ada & paling cepat expired".
            $table->index(['idBuku', 'expired'], 'batch_fefo_index');
        });

        $this->backfill();
    }

    /**
     * Isi tabel batch dari riwayat pembelian, lalu rekonsiliasi supaya
     * jumlah sisa batch persis sama dengan `buku.stock`.
     */
    protected function backfill(): void
    {
        // ---------------------------------------------------------------
        // 1. Kumpulkan setiap kombinasi (obat, batch, expired, harga) dari
        //    pembelian, lalu jumlahkan.
        // ---------------------------------------------------------------
        $baris = DB::table('detail_pembelian')
            ->whereNotNull('expired')
            ->where('expired', '>', '2000-01-01')   // buang tanggal sampah
            ->where('expired', '<', '2100-01-01')
            ->whereNotNull('idBuku')
            ->where('idBuku', '!=', '')
            ->selectRaw('
                idBuku,
                noBatch,
                DATE(expired) AS expired,
                SUM(jumlah) AS jumlah,
                AVG(hargaPokok) AS hargaPokok,
                MAX(idPembelian) AS idPembelian,
                MAX(idDetailPembelian) AS idDetailPembelian
            ')
            ->groupBy('idBuku', 'noBatch', DB::raw('DATE(expired)'))
            ->orderBy('idBuku')
            ->orderByRaw('DATE(expired)')
            ->get();

        if ($baris->isEmpty()) {
            return;
        }

        $stokSekarang = DB::table('buku')
            ->pluck('stock', 'idBuku')
            ->map(fn ($v) => (int) $v);

        // Setiap obat mulai dengan sisa = stok saat ini. WAJIB diinisialisasi untuk
        // SELURUH obat, bukan hanya yang punya riwayat pembelian — kalau tidak,
        // obat yang stoknya berasal dari impor akan punya batch kosong padahal
        // `buku.stock`-nya tidak nol.
        $sisaPerObat = [];

        foreach ($stokSekarang as $idBuku => $stok) {
            $sisaPerObat[$idBuku] = max(0, (int) $stok);
        }

        $sisip = [];
        $waktu = Carbon::now();

        // ---------------------------------------------------------------
        // 2. Alokasikan stok ke batch dengan urutan FEFO.
        //
        //    Batch dengan tanggal expired paling awal harus keluar lebih dulu,
        //    jadi `stokKeluar` diisi mulai dari sana. Kalau seluruh batch
        //    sudah terpakai tapi buku.stock masih menyisakan sisa, sisa itu
        //    dicatat sebagai batch tanpa tanggal kedaluwarsa supaya angka
        //    tetap cocok dengan buku.stock.
        // ---------------------------------------------------------------
        foreach ($baris as $b) {
            $idBuku = $b->idBuku;

            // Obat ini baru pertama kali ditemukan — mulai dari stok
            // yang tercatat di buku.
            if (! array_key_exists($idBuku, $sisaPerObat)) {
                $sisaPerObat[$idBuku] = max(0, $stokSekarang[$idBuku] ?? 0);
            }

            $sisa = $sisaPerObat[$idBuku];
            $masuk = (int) $b->jumlah;

            // Batch ini mewarisi sisa stok yang belum dipakai batch sebelumnya.
            // Kalau batch ini punya 300 unit dan sisa tinggal 210, maka 210 unit
            // dari batch inilah yang masih tersedia — sisanya dianggap sudah
            // terjual.
            $tersisaDiBatchIni = (int) min($masuk, $sisa);
            $sisaPerObat[$idBuku] = $sisa - $tersisaDiBatchIni;

            $sisip[] = [
                'idBuku'            => $idBuku,
                'noBatch'           => $b->noBatch,
                'expired'           => $b->expired,
                'hargaPokok'        => (int) round($b->hargaPokok),
                'stokMasuk'         => $masuk,
                'stokKeluar'        => $masuk - $tersisaDiBatchIni,
                'idPembelian'       => $b->idPembelian,
                'idDetailPembelian' => $b->idDetailPembelian,
                'created_at'        => $waktu,
                'updated_at'        => $waktu,
            ];
        }

        foreach (array_chunk($sisip, 500) as $potongan) {
            DB::table('batch_obat')->insert($potongan);
        }

        // ---------------------------------------------------------------
        // 3. Sisa stok yang tidak bisa ditelusuri ke pembelian
        //    (mis. obat yang stok awalnya dari impor) dicatat sebagai batch
        //    tanpa tanggal kedaluwarsa supaya angka tetap cocok dengan
        //    buku.stock.
        // ---------------------------------------------------------------
        $tambahan = [];
        $nomor = 1;

        foreach ($sisaPerObat as $idBuku => $sisa) {
            if ($sisa <= 0) {
                continue;
            }

            $tambahan[] = [
                'idBuku'     => $idBuku,
                'noBatch'    => 'SISA-' . str_pad((string) $nomor++, 4, '0', STR_PAD_LEFT),
                'expired'    => null,
                'hargaPokok' => 0,
                'stokMasuk'  => $sisa,
                'stokKeluar' => 0,
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ];
        }

        foreach (array_chunk($tambahan, 500) as $potongan) {
            DB::table('batch_obat')->insert($potongan);
        }
    }

    public function down()
    {
        Schema::dropIfExists('batch_obat');
    }
}