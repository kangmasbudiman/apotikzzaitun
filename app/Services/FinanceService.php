<?php 

namespace App\Services;

use App\Repositories\{TransactionRepository, ExpendRepository, StockRepository};

class FinanceService
{

    public function __construct(TransactionRepository $transactionRepo, ExpendRepository $expendRepo, StockRepository $stockRepo)
    {
        $this->transactionRepo = $transactionRepo;
        $this->expendRepo = $expendRepo;
        $this->stockRepo = $stockRepo;
    }

    public function accumulation(string $start = null, string $end = null): Object
    {
        $reports = $this->transactionRepo->accumulation($start, $end);
        $reports->pengeluaranOne = $this->expendRepo->accumulationOne($start, $end);
        $reports->pengeluaran = $this->expendRepo->accumulation($start, $end);
        $reports->pembelian = $this->stockRepo->accumulation($start, $end);
        $reports->pemasukan = $reports->hargaJual - $reports->disc + $reports->ppn;

        $reports->labaKotor = $reports->pemasukan - $reports->pengeluaran;
        $reports->labaKotorOne = $reports->pemasukan - $reports->hargaPokok;
        $reports->laba = $reports->labaKotor - $reports->pengeluaran;
        $reports->labaOne = $reports->labaKotorOne - $reports->pengeluaranOne;

        return $reports;
    }

}

 ?>