<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\{ToModel, WithValidation};

use App\Models\Expend;

class FinanceImport extends ImportHandle implements ToModel, WithValidation
{

    public $success = 0;

    public function rules(): Array
    {
        return [
            'id_pengeluaran' => 'required|unique:pengeluaran,idPengeluaran',
            'id_kategori_pengeluaran' => 'nullable|exists:kategori_pengeluaran,idKategoriPengeluaran',
            'id_pembelian' => 'nullable|exists:pembelian,idPembelian',
            'id_opname' => 'nullable|exists:opname,idOpname',
            'id_pajak' => 'nullable|exists:ppn,idPajak',
            'id_user' => 'nullable|exists:user,idUser',
            'nama_user' => 'nullable|string',
            'pengeluaran' => 'required|integer|min:1',
            'keterangan' => 'nullable|string',
            'tanggal' => 'required|date',
            'kategori' => 'required|string'
        ];
    }

    public function model(array $row)
    {
        $this->success++;

        return Expend::create([
            'idPengeluaran' => $row['id_pengeluaran'],
            'idPembelian' => $row['id_pembelian'],
            'idKategoriPengeluaran' => $row['id_kategori_pengeluaran'],
            'idOpname' => $row['id_opname'],
            'idUser' => $row['id_user'],
            'idPajak' => $row['id_pajak'],
            'namaUser' => $row['nama_user'],
            'pengeluaran' => $row['pengeluaran'],
            'keterangan' => $row['keterangan'],
            'tanggal' => $row['tanggal'],
            'namaKategori' => $row['kategori']
        ]);
        
    }
}
