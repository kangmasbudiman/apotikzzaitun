<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\{FromView, ShouldAutoSize, Exportable};

use App\Repositories\StockRepository;

class StockExport implements FromView, ShouldAutoSize
{

    use Exportable;

    protected $stock;

    public function __construct(StockRepository $stock)
    {
        $this->stock = $stock;
    }

    public function view(): View
    {
        return view('stock.export', [
            'stocks' => $this->stock->get()
        ]);
    }
}
