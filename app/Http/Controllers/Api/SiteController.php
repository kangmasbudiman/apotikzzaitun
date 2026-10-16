<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Identitas toko/nama sistem.
 *
 * GET publik (dipakai layar login sebelum punya token), PUT khusus admin —
 * nama yang diatur admin di sini yang tampil di layar login semua perangkat.
 */
class SiteController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json(['data' => self::payload(cache('site') ?: Site::first())]);
    }

    /**
     * Ubah identitas toko (admin).
     *
     * Hanya field yang dikirim yang diubah, jadi aplikasi boleh mengirim
     * sebagian field saja. Cache `site` wajib dibuang — kalau tidak, semua
     * perangkat terus menampilkan nama lama.
     */
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nama_sistem' => 'nullable|string|max:100',
            'nama_toko'   => 'nullable|string|max:100',
            'alamat_toko' => 'nullable|string|max:255',
            'telepon_toko'=> 'nullable|string|max:50',
            'ppn'         => 'nullable|numeric|min:0',
            'min_stok'    => 'nullable|integer|min:0',
        ]);

        $site = Site::first();

        if (! $site) {
            $site = Site::create($request->only(array_keys($data)));
        } else {
            $site->update($request->only(array_keys($data)));
        }

        cache()->forget('site');

        return response()->json([
            'message' => 'Pengaturan toko disimpan.',
            'data'    => self::payload($site->fresh()),
        ]);
    }

    /**
     * Bentuk payload yang sama untuk GET & PUT supaya aplikasi bisa langsung
     * memakai respons PUT tanpa memanggil ulang GET site.
     */
    protected static function payload(?Site $site): array
    {
        return [
            'nama_sistem' => $site->nama_sistem ?? null,
            'nama_toko'   => $site->nama_toko   ?? null,
            'alamat_toko' => $site->alamat_toko ?? null,
            'telepon_toko'=> $site->telepon_toko ?? null,
            'ppn'         => (float) ($site->ppn ?? 0),
            'min_stok'    => (int) ($site->min_stok ?? 0),
        ];
    }
}
