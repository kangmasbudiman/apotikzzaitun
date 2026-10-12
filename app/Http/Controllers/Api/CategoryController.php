<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            CategoryResource::collection(Category::orderBy('nama_kategori')->get())
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nama_kategori' => 'required|string|max:30|unique:kategori,nama_kategori',
        ]);

        $category = Category::create($data);

        return response()->json([
            'message' => 'Kategori berhasil ditambahkan.',
            'data'    => new CategoryResource($category),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        $data = $request->validate([
            'nama_kategori' => [
                'required', 'string', 'max:30',
                Rule::unique('kategori', 'nama_kategori')->ignore($id, 'idKategori'),
            ],
        ]);

        $category->update($data);

        return response()->json([
            'message' => 'Kategori berhasil diperbarui.',
            'data'    => new CategoryResource($category->fresh()),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        // Jangan sampai user dihapus padahal obat masih terrelasi.
        if ($category->stuffs()->exists()) {
            return response()->json([
                'message' => 'Kategori masih dipakai obat. Pindahkan atau hapus obatnya dulu.',
            ], 422);
        }

        $category->delete();

        return response()->json(['message' => 'Kategori berhasil dihapus.']);
    }
}