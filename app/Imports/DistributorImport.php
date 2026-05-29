<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\{ToModel, WithValidation};

use App\Repositories\DistributorRepository;

class DistributorImport extends ImportHandle implements ToModel, WithValidation
{

    public $success = 0;
    protected $distributor;

    public function __construct(DistributorRepository $distributor)
    {
        $this->distributor = $distributor;
    }

    public function rules(): Array
    {
        return [
            'nama' => 'required|string|unique:distributor,namaDist',
            'alamat' => 'required|string',
            'telepon' => 'required|numeric'
        ];
    }

    public function model(array $row)
    {
        $this->success++;
        
        return $this->distributor->create([
            'namaDist' => $row['nama'],
            'alamat' => $row['alamat'],
            'telepon' => $row['telepon']
        ]);
    }
}
