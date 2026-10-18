<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LanggananService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Langganan (masa aktif aplikasi) — status, pilihan paket, aktivasi.
 */
class LanggananController extends Controller
{
    public function __construct(protected LanggananService $langganan)
    {
    }

    /**
     * GET /api/langganan/status — semua user login boleh membaca supaya
     * banner peringatan bisa tampil juga untuk kasir/gudang.
     */
    public function status(): JsonResponse
    {
        return response()->json($this->langganan->status());
    }

    /**
     * GET /api/langganan/paket — daftar pilihan paket yang aktif.
     */
    public function paket(): JsonResponse
    {
        return response()->json(['data' => $this->langganan->daftarPaket()]);
    }

    /**
     * POST /api/langganan/aktivasi {id_paket, id_perangkat?, nama_perangkat?}
     *
     * Hanya admin. Pemilik aplikasi mengatur sendiri paket & harganya di
     * database — aplikasi hanya meneruskan pilihan admin toko.
     */
    public function aktivasi(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_paket'      => 'required|integer',
            'id_perangkat'  => 'nullable|string|max:100',
            'nama_perangkat'=> 'nullable|string|max:150',
        ]);

        $this->langganan->aktivasi(
            $request->user(),
            (int) $data['id_paket'],
            $data['id_perangkat'] ?? null,
            $data['nama_perangkat'] ?? null,
        );

        return response()->json([
            'message' => 'Langganan aktif.',
            'data'    => $this->langganan->status(),
        ]);
    }
}
