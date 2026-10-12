<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RackResource;
use App\Models\Rack;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RackController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            RackResource::collection(Rack::orderBy('nama_rak')->get())
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nama_rak' => 'required|string|max:50|unique:rak,nama_rak',
        ]);

        $rack = Rack::create($data);

        return response()->json([
            'message' => 'Rak berhasil ditambahkan.',
            'data'    => new RackResource($rack),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $rack = Rack::findOrFail($id);

        $data = $request->validate([
            'nama_rak' => [
                'required', 'string', 'max:50',
                Rule::unique('rak', 'nama_rak')->ignore($id, 'idRak'),
            ],
        ]);

        $rack->update($data);

        return response()->json([
            'message' => 'Rak berhasil diperbarui.',
            'data'    => new RackResource($rack->fresh()),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $rack = Rack::findOrFail($id);

        if ($rack->stuffs()->exists()) {
            return response()->json([
                'message' => 'Rak masih dipakai obat. Pindahkan atau hapus obatnya dulu.',
            ], 422);
        }

        $rack->delete();

        return response()->json(['message' => 'Rak berhasil dihapus.']);
    }
}