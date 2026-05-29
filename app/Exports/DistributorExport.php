<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\{FromView, ShouldAutoSize, Exportable};

use App\Repositories\DistributorRepository;

class DistributorExport implements FromView, ShouldAutoSize
{

    use Exportable;

    protected $distributor;

    public function __construct(DistributorRepository $distributor)
    {
        $this->distributor = $distributor;
    }

    public function view(): View
    {
        return view('distributor.export', [
            'distributors' => $this->distributor->get()
        ]);
    }
}
