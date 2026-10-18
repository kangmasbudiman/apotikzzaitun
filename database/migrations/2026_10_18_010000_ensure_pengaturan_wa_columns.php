<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom untuk laporan penjualan harian otomatis ke WhatsApp pemilik.
 *
 * Token gateway & nomor tujuan disimpan di database (bukan .env) supaya
 * tiap deployment diatur sendiri oleh admin toko dari aplikasi, tanpa
 * perlu mengedit file di server. `laporan_wa_terakhir` adalah penanda
 * idempotensi: tanggal laporan terakhir yang berhasil terkirim.
 */
class EnsurePengaturanWaColumns extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('pengaturan')) {
            return;
        }

        Schema::table('pengaturan', function (Blueprint $table) {
            if (! Schema::hasColumn('pengaturan', 'wa_pemilik')) {
                $table->string('wa_pemilik', 20)->nullable();
            }
            if (! Schema::hasColumn('pengaturan', 'laporan_wa_aktif')) {
                $table->boolean('laporan_wa_aktif')->default(false);
            }
            if (! Schema::hasColumn('pengaturan', 'wa_token')) {
                $table->string('wa_token', 255)->nullable();
            }
            if (! Schema::hasColumn('pengaturan', 'laporan_wa_terakhir')) {
                $table->date('laporan_wa_terakhir')->nullable();
            }
        });
    }

    public function down()
    {
        // Kolom milik fitur laporan WA — tidak perlu dihapus saat rollback.
    }
}
