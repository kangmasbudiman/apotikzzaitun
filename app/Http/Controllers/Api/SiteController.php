<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LogoToko;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Identitas toko/nama sistem + logo apotek.
 *
 * GET publik (dipakai layar login sebelum punya token), PUT/POST/DELETE
 * khusus admin — nama dan logo yang diatur admin di sini yang tampil di
 * layar login semua perangkat.
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
            'wa_pemilik'  => 'nullable|string|max:20',
            'laporan_wa_aktif' => 'nullable|boolean',
            'wa_token'    => 'nullable|string|max:255',
        ]);

        // Token gateway WA hanya ditimpa bila admin mengetik nilai baru.
        // Aplikasi sengaja tidak mengirim field ini saat dibiarkan kosong,
        // tapi nilai kosong dari klien lain pun tidak boleh menghapus token.
        if (array_key_exists('wa_token', $data) && ! filled($data['wa_token'])) {
            unset($data['wa_token']);
        }

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
     * Unggah/ganti logo apotek (admin, multipart field `logo`).
     *
     * Logo disimpan sebagai LONGBLOB (satu baris id=1) — bukan file di
     * storage — supaya bertahan rebuild image Docker di produksi.
     */
    public function simpanLogo(Request $request): JsonResponse
    {
        $request->validate([
            'logo' => 'required|file|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $file = $request->file('logo');

        LogoToko::updateOrCreate(
            ['id' => 1],
            [
                'mime' => $file->getMimeType() ?: 'image/png',
                'data' => file_get_contents($file->getRealPath()),
            ]
        );

        cache()->forget('site');

        return response()->json([
            'message' => 'Logo toko disimpan.',
            'data'    => ['logo_url' => 'site/logo'],
        ]);
    }

    /**
     * Hapus logo — aplikasi kembali memakai ikon bawaan.
     */
    public function hapusLogo(): JsonResponse
    {
        LogoToko::query()->delete();

        cache()->forget('site');

        return response()->json(['message' => 'Logo toko dihapus.']);
    }

    /**
     * Kirim bytes logo — publik (tanpa token) karena dimuat widget gambar
     * di layar login yang belum punya token. Header cache supaya perangkat
     * kasir tidak mengunduh ulang logo yang sama terus-menerus.
     */
    public function tampilkanLogo(): Response
    {
        $logo = LogoToko::find(1);

        if (! $logo) {
            abort(404);
        }

        return response($logo->data, 200, [
            'Content-Type'  => $logo->mime,
            'Cache-Control' => 'public, max-age=3600',
            'ETag'          => '"'.md5('logo'.$logo->updated_at).'"',
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
            'logo_url'    => LogoToko::exists() ? 'site/logo' : null,

            // Laporan WA harian. Nilai token TIDAK PERNAH ikut keluar —
            // payload ini juga dipakai GET /site yang publik (layar login);
            // aplikasi cukup tahu apakah token sudah tersimpan.
            'wa_pemilik'         => $site->wa_pemilik ?? null,
            'laporan_wa_aktif'   => (bool) ($site->laporan_wa_aktif ?? false),
            'wa_token_tersimpan' => ! empty($site->wa_token),
        ];
    }
}
