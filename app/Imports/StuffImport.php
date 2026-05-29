<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\{ToModel, WithValidation, WithCalculatedFormulas};
use Illuminate\Support\Str;

use App\Repositories\StuffRepository;

class StuffImport extends ImportHandle implements ToModel, WithValidation, WithCalculatedFormulas
{
    
    public $success = 0;
    protected $stuff;

    public function __construct(StuffRepository $stuff)
    {
        $this->stuff = $stuff;
    }

    public function prepareForValidation($data, $index)
    {
        $data = array_merge($data, [
            'barcode' => $data['barcode'] ? (string)$data['barcode'] : (string)intval(rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9)),
            'judul' => $data['judul'] ?? Str::random(2),
            'penerbit' => $data['penerbit'] ?? Str::random(2),
            'harga_jual' => $data['harga_jual'] <= 1 ? 5000 : $data['harga_jual'],
            'disc' => preg_replace('/%/i', '', $data['disc']),
            'stock' => isset($data['stock']) ? max($data['stock'], 0) : 0,
            'tahun' => strlen($data['tahun']) < 4 ? date('Y') : substr($data['tahun'], 0, 4) 
        ]);

        $data['harga_pokok'] = round($data['harga_jual'] / 2);

        return $data;
    }

    public function rules(): Array
    {
        return [
            'kategori' => 'nullable|exists:kategori,idKategori',
            'rak' => 'nullable|exists:rak,idRak',
            'barcode' => 'required|string|unique:buku',
            'isbn' => 'nullable|unique:buku,noisbn',
            'judul' => 'required|string',
            'penulis' => 'nullable|string',
            'penerbit' => 'required|string',
            'tahun' => 'required|digits:4',
            'harga_pokok' => 'required|numeric|min:1|digits_between:0,9|lte:*.harga_jual',
            'harga_jual' => 'required|numeric|min:1|digits_between:0,9',
            'disc' => 'required|numeric|max:100'
        ];
    }

    public function model(array $row)
    {
        $this->success++;

        return $this->stuff->create([
            'idKategori' => $row['kategori'],
            'idRak' => $row['rak'],
            'barcode' => $row['barcode'],
            'noisbn' => $row['isbn'] ?? null,
            'judul' => $row['judul'],
            'penulis' => $row['penulis'] ?? null,
            'penerbit' => $row['penerbit'],
            'tahun' => $row['tahun'],
            'hargaPokok' => $row['harga_pokok'],
            'hargaJual' => $row['harga_jual'],
            'disc' => $row['disc'],
            'stock' => $row['stock'],
        ]);
    }
}
