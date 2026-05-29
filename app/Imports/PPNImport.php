<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{ToModel, WithValidation};

use App\Models\PPN;

class PPNImport extends ImportHandle implements ToModel, WithValidation
{

    public $success = 0;

    public function rules(): Array
    {
        return [
            'id_pajak' => 'required|unique:ppn,idPajak',
            'id_user' => 'nullable|exists:user,idUser',
            'jenis' => 'required|string|in:PPN Dikeluarkan,PPN Disetorkan',
            'tanggal' => 'nullable|date',
            'nominal' => 'required|integer',
            'keterangan' => 'nullable|string'
        ];
    }

    public function model(array $row)
    {
        $this->success++;

        $ppn = PPN::create([
            'idPajak' => $row['id_pajak'],
            'idUser' => $row['id_user'],
            'tanggal' => $row['tanggal'],
            'nominal' => $row['nominal'],
            'jenis' => $row['jenis'],
            'keterangan' => $row['keterangan'],
        ]);

        return $ppn;
    }
}
