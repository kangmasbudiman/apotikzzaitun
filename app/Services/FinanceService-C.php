<?php 

namespace App\Services;

use App\Repositories\{TransactionRepository, ExpendRepository};

class FinanceService
{

    public function __construct(TransactionRepository $transactionRepo, ExpendRepository $expendRepo)
    {
        $this->transactionRepo = $transactionRepo;
        $this->expendRepo = $expendRepo;
    }

    public function accumulation(string $start = null, string $end = null): Object
    {
        $reports = $this->transactionRepo->accumulation($start, $end);
        $reports->pengeluaran = $this->expendRepo->accumulation($start, $end);
        $reports->pemasukan = $reports->hargaJual - $reports->disc + $reports->ppn;

        $reports->labaKotor = $reports->pemasukan - $reports->hargaPokok;
        $reports->laba = $reports->labaKotor - $reports->pengeluaran;

        return $reports;
    }

}

 ?>