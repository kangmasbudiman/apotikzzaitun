<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreStuffRequest;
use App\Http\Requests\Api\UpdateStuffRequest;
use App\Http\Resources\StuffResource;
use App\Models\Stuff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StuffController extends Controller
{
    /**
     * Daftar obat — dipaginasi & bisa dicari.
     *
     * Dipakai juga untuk layar "pilih obat" di aplikasi kasir.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 25), 100);

        $stuffs = Stuff::with(['category', 'rack'])
            ->when($request->filled('search'), function ($query) use ($request)
            {
                $s = $request->query('search');

                $query->where(function ($q) use ($s)
                {
                    $q->where('obat', 'like', "%{$s}%")
                        ->orWhere('barcode', 'like', "%{$s}%")
                        ->orWhere('noisbn', 'like', "%{$s}%")
                        ->orWhere('suplier', 'like', "%{$s}%")
                        ->orWhere('penulis', 'like', "%{$s}%");
                });
            })
            ->when($request->filled('kategori_id'), fn ($q) => $q->where('idKategori', $request->query('kategori_id')))
            ->when($request->filled('rak_id'), fn ($q) => $q->where('idRak', $request->query('rak_id')))
            ->when($request->filled('stok'), function ($query) use ($request)
            {
                // 'habis' | 'menipis' | 'aman'
                $min = (int) site('min_stok');

                $query->when($request->query('stok') === 'habis', fn ($q) => $q->where('stock', '<=', 0))
                    ->when($request->query('stok') === 'menipis', fn ($q) => $q->where('stock', '>', 0)->where('stock', '<=', $min))
                    ->when($request->query('stok') === 'aman', fn ($q) => $q->where('stock', '>', $min));
            })
            ->when($request->filled('suplier'), fn ($q) => $q->where('suplier', 'like', '%'.$request->query('suplier').'%'))
            ->when($request->query('sort') === 'stok', fn ($q) => $q->orderBy('stock', $request->query('direction') === 'desc' ? 'desc' : 'asc'))
            ->when($request->query('sort') === 'harga_jual', fn ($q) => $q->orderBy('hargaJual', $request->query('direction') === 'desc' ? 'desc' : 'asc'))
            ->when($request->query('sort') === 'obat', fn ($q) => $q->orderBy('obat', $request->query('direction') === 'desc' ? 'desc' : 'asc'))
            ->when(!$request->filled('sort'), fn ($q) => $q->orderBy('obat', 'asc'))
            ->paginate($perPage);

        // ->response() menjaga blok `meta` paginator (total, current_page,
        // last_page). response()->json() melepasnya, sehingga aplikasi mobile
        // tidak pernah tahu ada berapa obat selain yang sedang dimuat.
        return StuffResource::collection($stuffs)->response();
    }

    /**
     * Detail satu obat.
     */
    public function show(string $id): JsonResponse
    {
        $stuff = Stuff::with(['category', 'rack'])->findOrFail($id);

        return response()->json(['data' => new StuffResource($stuff)]);
    }

    /**
     * Pencarian cepat untuk scan barcode di kasir.
     *
     * Parameter: ?q=<barcode|idBuku|obat>
     * Kalau q persis cocok barcode/idBuku, hasil tunggal dikembalikan di
     * field `exact` supaya app bisa langsung tambah ke keranjang.
     */
    public function find(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        if ($q === '') {
            return response()->json(['data' => null, 'exact' => null, 'suggestions' => []]);
        }

        $exact = Stuff::with(['category', 'rack'])
            ->where('barcode', $q)
            ->orWhere('idBuku', $q)
            ->first();

        if ($exact) {
            return response()->json([
                'data'        => new StuffResource($exact),
                'exact'       => new StuffResource($exact),
                'suggestions' => [],
            ]);
        }

        // Tidak ketemu persis -> beri saran (cocok sebagian pada nama obat).
        $suggestions = Stuff::with(['category', 'rack'])
            ->where('obat', 'like', "%{$q}%")
            ->orderBy('obat')
            ->limit(10)
            ->get();

        return response()->json([
            'data'        => null,
            'exact'       => null,
            'suggestions' => StuffResource::collection($suggestions),
        ]);
    }

    /**
     * Tambah obat baru.
     */
    public function store(StoreStuffRequest $request): JsonResponse
    {
        // Whitelist eksplisit: model `Stuff` sengaja dibuat longgar
        // ($guarded = ['idBuku']), jadi field TIDAK boleh langsung dari request.
        $stuff = Stuff::create($request->safe()->only([
            'idKategori', 'idRak', 'barcode', 'obat', 'noisbn',
            'penulis', 'suplier', 'tahun', 'disc', 'ppn',
            'margin', 'hargaPokok', 'hargaJual',
        ]) + [
            'stock' => 0,
            'disc'  => $request->disc ?? 0,
            'ppn'   => $request->ppn ?? 0,
        ]);

        return response()->json([
            'message' => 'Obat berhasil ditambahkan.',
            'data'    => new StuffResource($stuff->load(['category', 'rack'])),
        ], 201);
    }

    /**
     * Ubah obat.
     */
    public function update(UpdateStuffRequest $request, string $id): JsonResponse
    {
        $stuff = Stuff::findOrFail($id);

        // 'stock' sengaja tidak ikut di-update — stok hanya lewat pembelian.
        $stuff->update($request->safe()->only([
            'idKategori', 'idRak', 'barcode', 'obat', 'noisbn',
            'penulis', 'suplier', 'tahun', 'disc', 'ppn',
            'margin', 'hargaPokok', 'hargaJual',
        ]));

        return response()->json([
            'message' => 'Obat berhasil diperbarui.',
            'data'    => new StuffResource($stuff->fresh()->load(['category', 'rack'])),
        ]);
    }

    /**
     * Hapus obat.
     */
    public function destroy(string $id): JsonResponse
    {
        $stuff = Stuff::findOrFail($id);
        $stuff->delete();

        return response()->json(['message' => 'Obat berhasil dihapus.']);
    }
}