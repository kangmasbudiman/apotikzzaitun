<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{ToModel, WithValidation};

use App\Models\Opname;
use App\Services\OpnameService;

class OpnameImport extends ImportHandle implements ToModel, WithValidation
{

    public $success = 0;
    protected $opnameService;

    public function __construct(OpnameService $opnameService)
    {
        $this->opnameService = $opnameService;
    }

    public function rules(): Array
    {
        return [
            'id_opname' => 'required|unique:opname,idOpname',
            'id_buku' => 'required|exists:buku,idBuku',
            'nama_buku' => 'required|string|exists:buku,judul',
            'tanggal' => 'nullable|date',
            'stok_sistem' => 'required|integer|min:1',
            'stok_nyata' => 'required|integer|min:1|lt:*.stok_sistem',
            'harga_pokok' => 'required|integer|min:1000',
            'keterangan' => 'nullable|string'
        ];
    }

    public function model(array $row)
    {
        $this->success++;

        $opname = Opname::create([
            'idOpname' => $row['id_opname'],
            'idBuku' => $row['id_buku'],
            'judul' => $row['nama_buku'],
            'tanggal' => $row['tanggal'],
            'stokSistem' => $row['stok_sistem'],
            'stokNyata' => $row['stok_nyata'],
            'hargaPokok' => $row['harga_pokok'],
            'keterangan' => $row['keterangan'],
        ]);

        // $this->opnameService->createRelation($opname, $opname->book);

        return $opname;
    }
}
