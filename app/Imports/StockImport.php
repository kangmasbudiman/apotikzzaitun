<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{ToModel, WithValidation};

use App\Repositories\StockRepository;
use App\Services\StockService;
use App\Models\Expend;

class StockImport extends ImportHandle implements ToModel, WithValidation
{

    public $success = 0;
    protected $stock;
    protected $stockService;

    public function __construct(StockRepository $stock, StockService $stockService)
    {
        $this->stock = $stock;
        $this->stockService = $stockService;
    }

    public function rules(): Array
    {
        return [
            'id_pembelian' => 'required|unique:pembelian,idPembelian',
            'id_user' => 'nullable|exists:user,idUser',
            'id_distributor' => 'nullable|exists:distributor,idDist',
            'tanggal' => 'required|date',
            'distributor' => 'required',
            'user' => 'required',
            'total' => 'required|integer|min:1'
        ];
    }

    public function model(array $row)
    {
        $this->success++;

        $stock = $this->stock->create([
            'idPembelian' => $row['id_pembelian'],
            'idUser' => $row['id_user'],
            'idDist' => $row['id_distributor'],
            'tanggal' => $row['tanggal'],
            'total' => $row['total'],
            'namaDist' => $row['distributor'],
            'namaUser' => $row['user'],
        ]);

        // if (!Expend::where('idPembelian', $stock->idPembelian)->exists()) {
        //     $expend = $this->stockService->createExpend($stock);
            
        //     $stock->expend()->save($expend);
        // }

        return $stock;
    }
}
