<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // Daftar eksplisit. `load()` di bawah saja kadang tidak cukup di
        // Laravel 8 kalau autoloader sudah di-cache sebagai classmap.
        \App\Console\Commands\DiagnosaLoginCommand::class,
        \App\Console\Commands\LaporanWaCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Laporan WA kemarin dini hari — hanya berjalan bila host memasang
        // cron `schedule:run`; tanpa cron, aplikasi tetap memicu laporan
        // yang sama saat dibuka (idempoten, tidak dobel).
        $schedule->command('laporan:wa')->dailyAt('01:05');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
