<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\{FromView, ShouldAutoSize, Exportable};

use App\Models\DetailTransaction;

class DetailTransactionExport implements FromView, ShouldAutoSize
{

    use Exportable;

    public function view(): View
    {
        return view('transaction.detail_transaction_export', [
            'success' => DetailTransaction::whereHas('transaction', function ($query)
            {
                $query->where('status', 1);
            })->with('transaction.user')->get(),
            'cancel' => DetailTransaction::whereHas('transaction', function ($query)
            {
                $query->where('status', 0);
            })->with('transaction.user')->get(),
        ]);
    }
}
