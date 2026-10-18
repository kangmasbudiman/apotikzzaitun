<?php

namespace App\Http\Middleware;

use App\Services\LanggananService;
use Closure;
use Illuminate\Http\Request;

/**
 * Mengunci fitur transaksi saat langganan kedaluwarsa.
 *
 * Login, profil, dan status langganan TIDAK dikunci — supaya aplikasi
 * tetap bisa masuk, menampilkan pesan kedaluwarsa, dan admin bisa
 * mengaktifkan ulang paket tanpa harus lewat database.
 */
class EnsureLanggananAktif
{
    public function __construct(protected LanggananService $langganan)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $status = $this->langganan->status();

        if ($status['status'] === LanggananService::BELUM) {
            return response()->json([
                'message' => 'Belum ada langganan aktif. Pilih paket di '
                    .'menu Langganan untuk membuka transaksi.',
                'langganan' => $status,
            ], 402);
        }

        if ($status['status'] === LanggananService::KEDALUWARSA) {
            return response()->json([
                'message' => 'Langganan kedaluwarsa — transaksi dikunci. '
                    .'Aktifkan paket di menu Langganan atau hubungi pemilik aplikasi.',
                'langganan' => $status,
            ], 402);
        }

        return $next($request);
    }
}
