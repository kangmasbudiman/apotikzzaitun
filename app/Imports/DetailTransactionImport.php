<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{ToModel, WithValidation};

use App\Models\DetailTransaction;

class DetailTransactionImport extends ImportHandle implements ToModel, WithValidation
{

    public $success = 0;

    public function prepareForValidation($data, $index)
    {
        $data['harga_pokok'] = $data['harga_pokok'] ?? round($data['harga'] / 2);

        return $data;
    }

    public function rules(): Array
    {
        return [
            'id_penjualan' => 'required|exists:penjualan,idPenjualan',
            'id_buku' => 'nullable|exists:buku,idBuku',
            'judul_buku' => 'required|string',
            'harga_pokok' => 'required|integer|min:1',
            'harga' => 'required|integer|min:1',
            'jumlah' => 'required|integer|min:1',
            'ppn' => 'required|integer',
            'disc' => 'nullable|integer',
        ];
    }

    public function model(array $row)
    {
        $this->success++;
        
        return DetailTransaction::create([
            'idPenjualan' => $row['id_penjualan'],
            'idBuku' => isset($row['id_buku']) ? $row['id_buku'] : null,
            'judul' => $row['judul_buku'],
            'hargaPokok' => $row['harga_pokok'],
            'hargaJual' => $row['harga'],
            'jumlah' => $row['jumlah'],
            'ppn' => $row['ppn'],
            'disc' => $row['disc'],
        ]);
    }
}
