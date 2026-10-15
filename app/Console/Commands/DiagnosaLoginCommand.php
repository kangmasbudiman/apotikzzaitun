<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Cek cepat kenapa /api/auth/login membalas HTTP 500.
 *
 * Jalankan dari folder backend di server produksi:
 *
 *   php artisan pos:diagnosa-login
 *
 * Script ini hanya MEMBACA — tidak mengubah data apa pun, jadi aman
 * dijalankan saat toko sedang buka.
 *
 * Yang diperiksa, dari yang paling sering jadi penyebab:
 *
 *  1. `personal_access_tokens` ada?  Tabel ini dibuat 12 Oktober 2025
 *     bersamaan dengan fitur REST API. Kalau server produksi belum pernah
 *     `php artisan migrate` sejak saat itu, `createToken()` meledak tepat
 *     setelah password cocok — gejalanya: password salah -> 422, password
 *     benar -> 500. Persis yang сейчас terjadi.
 *
 *  2. `storage/` bisa ditulis user web server?
 *
 *  3. Cache & session bisa ditulis?
 *
 *  4. Koneksi database hidup?
 *
 *  5. Apakah tabel yang dipakai User benar-benar ada?
 */

class DiagnosaLoginCommand extends Command
{
    protected $signature = 'pos:diagnosa-login';

    protected $description = 'Periksa penyebab /api/auth/login membalas 500 (hanya baca)';

    public function handle()
    {
        $this->line('');
        $this->line('  Diagnosa login Apotikdear');
        $this->line('  ' . str_repeat('-', 52));
        $this->line('');

        $adaMasalah = false;

        // ------------------------------------------------ 1. koneksi database
        try {
            $db = DB::connection()->getDatabaseName();
            $this->ok("Koneksi database: '$db'");
        } catch (\Throwable $e) {
            $this->gagal('Koneksi database', $e->getMessage());
            $this->line('');
            $this->petunjuk('Database tidak bisa dijangkau. Perbaiki kredensial .env dulu,');
            $this->petunjuk('lalu jalankan ulang script ini.');
            return 1;
        }

        // ------------------------------------------ 2. personal_access_tokens
        //
        // Ini penyebab paling mungkin. Tabel ini wajib ada karena login
        // menyimpan token Sanctum; tanpa itu createToken() meledak.
        if (Schema::hasTable('personal_access_tokens')) {
            $kolom = Schema::getColumnListing('personal_access_tokens');

            $this->ok('Tabel `personal_access_tokens` ADA');

            // Tanpa kolom device_*, AuthController gagal saat menyimpan info
            // perangkat — perlu kolom tambahan.
            $butuh = ['tokenable_id', 'tokenable_type', 'name', 'token', 'device_name', 'device_platform'];

            $kurang = array_values(array_diff($butuh, $kolom));

            if ($kurang === []) {
                $this->ok('  Semua kolom yang dibutuhkan tersedia');
            } else {
                $adaMasalah = true;
                $this->gagal(
                    'Kolom personal_access_tokens kurang',
                    implode(', ', $kurang)
                );
                $this->line('');
                $this->petunjuk('   Jalankan: php artisan migrate --force');
            }
        } else {
            $adaMasalah = true;
            $this->gagal('Tabel `personal_access_tokens` TIDAK ADA', '');
            $this->line('');
            $this->petunjuk('   >>> Ini hampir pasti penyebab HTTP 500 Anda. <<<');
            $this->petunjuk('   Jalankan: php artisan migrate --force');
        }

        // ------------------------------------------------------ 3. tabel user
        if (Schema::hasTable('user')) {
            $this->ok('Tabel `user` ada');

            $kolomUser = Schema::getColumnListing('user');
            if (! in_array('username', $kolomUser, true)) {
                $adaMasalah = true;
                $this->gagal('Kolom `username` tidak ada di tabel `user`', '');
            }
            if (! in_array('password', $kolomUser, true)) {
                $adaMasalah = true;
                $this->gagal('Kolom `password` tidak ada di tabel `user`', '');
            }
        } else {
            $adaMasalah = true;
            $this->gagal('Tabel `user` TIDAK ADA', '');
        }

        // ------------------------------------------------------ 4. storage/
        //
        // Kalau tidak bisa ditulis, Laravel gagal menulis log. Error aslinya
        // jadi hilang dan yang terlihat cuma "Terjadi kesalahan di server".
        foreach (['storage/app', 'storage/framework/cache', 'storage/framework/sessions', 'storage/logs'] as $dir) {
            $path = base_path($dir);

            if (! is_dir($path)) {
                $adaMasalah = true;
                $this->gagal("Folder $dir tidak ada", '');
                continue;
            }

            if (is_writable($path)) {
                $this->ok("$dir bisa ditulis");
            } else {
                $adaMasalah = true;
                $this->gagal("$dir TIDAK bisa ditulis", 'permission denied');
                $this->line('');
                $this->petunjuk('   Perbaiki (ganti <user-web> dengan user PHP-FPM/apache):');
                $this->petunjuk("   sudo chown -R <user-web>:<grup> storage bootstrap/cache");
            }
        }

        // ------------------------------------------- 5. log error terakhir
        $this->line('');
        $this->line('  Isi storage/logs/laravel.log (5 baris terakhir):');
        $this->line('  ' . str_repeat('-', 52));

        try {
            // Path langsung dari base_path — disk 'local' di project ini
            // tidak selalu menunjuk ke storage/logs, sehingga log yang
            // sebenarnya ada ikut terbaca "kosong".
            $path = base_path('storage/logs/laravel.log');

            $log = is_file($path) && is_readable($path)
                ? (string) file_get_contents($path)
                : '';

            $baris = array_filter(explode("\n", $log), fn($b) => trim($b) !== '');

            if ($baris === []) {
                $this->petunjuk('  (log kosong — ini sendiri menandakan storage/ tidak bisa ditulis)');
            } else {
                // Hanya baris bertanda ERROR/CRITICAL yang berguna; sisanya
                // stack trace panjang dan hanya membuat output penuh.
                $penting = array_values(array_filter(
                    $baris,
                    fn($b) => str_contains($b, 'ERROR')
                        || str_contains($b, 'CRITICAL')
                        || str_contains($b, 'ALERT')
                        || str_contains($b, 'EMERGENCY')
                ));

                $tampil = $penting === [] ? array_slice($baris, -5) : array_slice($penting, -3);

                foreach ($tampil as $b) {
                    $this->line('  ' . mb_substr(trim($b), 0, 400));
                }
            }
        } catch (\Throwable $e) {
            $this->petunjuk('  Gagal membaca log: ' . $e->getMessage());
        }

        // -------------------------------------------------------------- hasil
        $this->line('');
        $this->line('  ' . str_repeat('-', 52));

        if ($adaMasalah) {
            $this->error('  ADA MASALAH. Lihat baris merah di atas.');
            $this->line('');

            return 1;
        }

        $this->info('  Semua pemeriksaan lolos.');
        $this->line('');
        $this->petunjuk('  Kalau login masih 500 padahal semua ini lolos, buka baris');
        $this->petunjuk('  "ERROR" di storage/logs/laravel.log dan kirimkan ke developer.');
        $this->line('');

        return 0;
    }

    protected function ok(string $pesan)
    {
        $this->line("  [OK]     $pesan");
    }

    protected function gagal(string $pesan, string $detail)
    {
        $this->line("  [MASALAH] $pesan");
        if ($detail !== '') {
            $this->line("            -> $detail");
        }
    }

    protected function petunjuk(string $pesan)
    {
        $this->line("            $pesan");
    }
}
