<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\{FromView, ShouldAutoSize, Exportable};

use App\Repositories\ExpendRepository;

class FinanceExport implements FromView, ShouldAutoSize
{

    use Exportable;

    protected $expend;

    public function __construct(ExpendRepository $expend)
    {
        $this->expend = $expend;
    }

    public function view(): View
    {
        return view('expend.export', [
            'expends' => $this->expend->get()
        ]);
    }
}
