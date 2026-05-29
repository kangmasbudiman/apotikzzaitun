<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\{ToModel, WithValidation};

use App\Repositories\RackRepository;

class RackImport extends ImportHandle implements ToModel, WithValidation
{

    public $success = 0;
    protected $rack;

    public function __construct(RackRepository $rack)
    {
        $this->rack = $rack;
    }

    public function rules(): Array
    {
        return [
            'nama' => 'required|string|unique:rak,nama_rak'
        ];
    }

    public function model(array $row)
    {
        $this->success++;
        
        return $this->rack->create([
            'nama_rak' => $row['nama'],
        ]);
    }
}
