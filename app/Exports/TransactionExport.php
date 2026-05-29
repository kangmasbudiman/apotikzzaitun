<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\{FromView, ShouldAutoSize, Exportable};

use App\Repositories\TransactionRepository;

class TransactionExport implements FromView, ShouldAutoSize
{

    use Exportable;

    protected $transaction;

    public function __construct(TransactionRepository $transaction)
    {
        $this->transaction = $transaction;
    }

    public function view(): View
    {
        return view('transaction.export', [
            'success' => $this->transaction->filterSuccess(),
            'cancel' => $this->transaction->filterCancel()
        ]);
    }
}
