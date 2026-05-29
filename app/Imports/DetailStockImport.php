<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{ToModel, WithValidation};

use App\Models\DetailStock;

class DetailStockImport extends ImportHandle implements ToModel, WithValidation
{

    public $success = 0;

    public function rules(): Array
    {
        return [
            'id_pembelian' => 'required|exists:pembelian,idPembelian',
            'id_buku' => 'nullable|exists:buku,idBuku',
            'judul' => 'required|string',
            'harga_pokok' => 'required|integer|min:1',
            'jumlah' => 'required|integer|min:1'
        ];
    }

    public function model(array $row)
    {
        $this->success++;
        
        return DetailStock::create([
            'idPembelian' => $row['id_pembelian'],
            'idBuku' => isset($row['id_buku']) ? $row['id_buku'] : null,
            'judul' => $row['judul'],
            'hargaPokok' => $row['harga_pokok'],
            'jumlah' => $row['jumlah'],
        ]);
    }
}
