<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GambarObat;
use App\Models\Stuff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Upload/hapus/tampilkan foto produk obat.
 *
 * GET gambar-obat/{id} sengaja publik (tanpa token): foto dimuat oleh
 * widget Image di aplikasi yang tidak membawa header Authorization.
 * Kontennya hanya foto produk — bukan data sensitif.
 */
class GambarObatController extends Controller
{
    /**
     * Simpan/ganti foto satu obat (multipart field `gambar`).
     */
    public function store(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'gambar' => 'required|file|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // findOrFail sekaligus memastikan obatnya benar-benar ada —
        // tidak menerima foto untuk id yang tidak terdaftar.
        Stuff::findOrFail($id);

        $file = $request->file('gambar');

        GambarObat::updateOrCreate(
            ['idBuku' => $id],
            [
                'mime' => $file->getMimeType() ?: 'image/jpeg',
                'data' => file_get_contents($file->getRealPath()),
            ]
        );

        return response()->json([
            'message' => 'Gambar obat disimpan.',
            'data'    => ['gambar_url' => "gambar-obat/$id"],
        ]);
    }

    /**
     * Hapus foto satu obat (kembali memakai inisial di aplikasi).
     */
    public function destroy(string $id): JsonResponse
    {
        $gambar = GambarObat::find($id);

        if ($gambar) {
            $gambar->delete();
        }

        return response()->json(['message' => 'Gambar obat dihapus.']);
    }

    /**
     * Kirim bytes foto + header cache supaya HP kasir tidak mengunduh
     * ulang foto yang sama setiap membuka daftar obat.
     */
    public function show(string $id): Response
    {
        $gambar = GambarObat::find($id);

        if (! $gambar) {
            abort(404);
        }

        return response($gambar->data, 200, [
            'Content-Type'  => $gambar->mime,
            'Cache-Control' => 'public, max-age=3600',
            'ETag'          => '"'.md5($id.$gambar->updated_at).'"',
        ]);
    }
}
