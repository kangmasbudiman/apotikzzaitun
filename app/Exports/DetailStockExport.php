<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\{FromView, ShouldAutoSize, Exportable};

use App\Models\DetailStock;

class DetailStockExport implements FromView, ShouldAutoSize
{

    use Exportable;

    public function view(): View
    {
        return view('stock.detail_stock_export', [
            'stocks' => DetailStock::with('stock.distributor')->get()
        ]);
    }
}
