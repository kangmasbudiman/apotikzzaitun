<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStuffRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('isAdminGudang');
    }

    public function rules()
    {
        $id = $this->route('id');

        return [
            'idKategori' => 'required|integer|exists:kategori,idKategori',
            'idRak'     => 'required|integer|exists:rak,idRak',
            'barcode'   => ['required', 'string', 'max:30', Rule::unique('buku', 'barcode')->ignore($id, 'idBuku')],
            'obat'      => 'required|string|max:100',
            'noisbn'    => ['nullable', 'string', 'max:50', Rule::unique('buku', 'noisbn')->ignore($id, 'idBuku')],
            'penulis'   => 'nullable|string|max:50',
            'suplier'   => 'required|string|max:50',
            'tahun'     => 'nullable|string|max:4',
            'disc'      => 'nullable|numeric|min:0|max:100',
            'ppn'       => 'nullable|numeric|min:0|max:100',
            'margin'    => 'nullable|numeric|min:0|max:100',
            'hargaPokok'=> 'required|numeric|min:0',
            'hargaJual' => 'required|numeric|min:0|gte:hargaPokok',
        ];
    }

    public function messages()
    {
        return [
            'hargaJual.gte' => 'Harga jual harus lebih besar atau sama dengan harga pokok.',
            // Pesan ini HANYA muncul kalau barcode benar-benar dipakai obat lain —
            // barcode yang tidak berubah saat mengedit diabaikan lewat Rule::ignore.
            'barcode.unique' => 'Barcode sudah dipakai obat lain. Ganti barcode atau periksa kembali.',
            'barcode.required' => 'Barcode wajib diisi.',
        ];
    }
}