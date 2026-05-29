<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{ToModel, WithValidation};

use App\Repositories\TransactionRepository;

class TransactionImport extends ImportHandle implements ToModel, WithValidation
{

    public $success = 0;
    protected $transaction;

    public function __construct(TransactionRepository $transaction)
    {
        $this->transaction = $transaction;
    }

    public function prepareForValidation($data, $index)
    {
        $data['status'] = $data['status'] === 'berhasil';

        return $data;
    }

    public function rules(): Array
    {
        return [
            'no_faktur' => 'required|unique:penjualan,idPenjualan',
            'tanggal' => 'required|date',
            'id_kasir' => 'nullable|exists:user,idUser',
            'kasir' => 'required|string',
            'status' => 'nullable|boolean',
            'ppn' => 'nullable|integer|min:1',
            'total' => 'required|integer|min:1'
        ];
    }

    public function model(array $row)
    {
        $this->success++;
        
        return $this->transaction->create([
            'idPenjualan' => $row['no_faktur'],
            'tanggal' => $row['tanggal'],
            'status' => $row['status'],
            'ppn' => $row['ppn'],
            'total' => $row['total'],
            'idUser' => isset($row['id_kasir']) ? $row['id_kasir'] : null,
            'namaUser' => $row['kasir'],
        ]);
    }
}
