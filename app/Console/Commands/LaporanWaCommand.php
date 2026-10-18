<?php

namespace App\Console\Commands;

use App\Services\LaporanWaService;
use Illuminate\Console\Command;

/**
 * Kirim laporan penjualan harian ke WhatsApp pemilik (untuk cron).
 *
 * BUKAN satu-satunya pemicu: aplikasi juga memanggil POST /laporan-wa/kirim
 * setiap dibuka. Command ini berguna kalau pemilik memasang cron supaya
 * laporan masuk dini hari, bukan menunggu kasir buka aplikasi.
 */
class LaporanWaCommand extends Command
{
    protected $signature = 'laporan:wa';

    protected $description = 'Kirim laporan penjualan kemarin ke WhatsApp pemilik (idempoten)';

    public function handle(LaporanWaService $laporanWa): int
    {
        $hasil = $laporanWa->kirimJikaPerlu();

        $hasil['terkirim']
            ? $this->info($hasil['pesan'])
            : $this->line($hasil['pesan']);

        return self::SUCCESS;
    }
}
