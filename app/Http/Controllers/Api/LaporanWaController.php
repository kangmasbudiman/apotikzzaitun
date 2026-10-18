<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LaporanWaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Pemicu laporan WhatsApp harian dari aplikasi.
 *
 * `kirim` dipanggil aplikasi diam-diam setiap kali dibuka (user mana pun —
 * kasir biasanya yang duluan). Idempotensi & syarat pengiriman ditahan
 * server (kolom laporan_wa_terakhir), jadi endpoint ini selalu aman
 * dipanggil berulang dan balasannya tidak berisi angka sensitif.
 */
class LaporanWaController extends Controller
{
    public function __construct(protected LaporanWaService $laporanWa) {}

    /**
     * POST /api/laporan-wa/kirim — kirim laporan kemarin bila perlu.
     */
    public function kirim(Request $request): JsonResponse
    {
        $hasil = $this->laporanWa->kirimJikaPerlu();

        return response()->json([
            'message'  => $hasil['pesan'],
            'terkirim' => $hasil['terkirim'],
        ]);
    }

    /**
     * POST /api/laporan-wa/uji — pesan uji ke nomor pemilik (admin).
     */
    public function uji(Request $request): JsonResponse
    {
        $hasil = $this->laporanWa->kirimUji();

        return response()->json([
            'message'  => $hasil['pesan'],
            'terkirim' => $hasil['terkirim'],
        ]);
    }
}
