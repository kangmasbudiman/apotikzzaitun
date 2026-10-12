<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreStuffRequest extends FormRequest
{
    /**
     * Master data obat hanya untuk Admin & Gudang.
     */
    public function authorize()
    {
        return $this->user()->can('isAdminGudang');
    }

    public function rules()
    {
        return [
            'idKategori' => 'required|integer|exists:kategori,idKategori',
            'idRak'     => 'required|integer|exists:rak,idRak',
            'barcode'   => 'required|string|max:30|unique:buku,barcode',
            'obat'      => 'required|string|max:100',
            'noisbn'    => 'nullable|string|max:50',
            'penulis'   => 'nullable|string|max:50',
            'suplier'   => 'required|string|max:50',
            'tahun'     => 'nullable|string|max:4',
            'disc'      => 'nullable|numeric|min:0|max:100',
            'ppn'       => 'nullable|numeric|min:0|max:100',
            'margin'    => 'nullable|numeric|min:0|max:100',
            'hargaPokok'=> 'required|numeric|min:0',
            'hargaJual' => 'required|numeric|min:0|gte:hargaPokok',
            // Stok awal TIDAK boleh diisi dari API. Stok hanya bertambah lewat
            // pembelian (trigger detail_pembelian), supaya tidak bisa disalahgunakan.
        ];
    }

    public function messages()
    {
        return [
            'hargaJual.gte' => 'Harga jual harus lebih besar atau sama dengan harga pokok.',
        ];
    }
}