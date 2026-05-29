<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\{FromView, ShouldAutoSize, Exportable};

use App\Repositories\RackRepository;

class RackExport implements FromView, ShouldAutoSize
{

    use Exportable;

    protected $rack;

    public function __construct(RackRepository $rack)
    {
        $this->rack = $rack;
    }

    public function view(): View
    {
        return view('rack.export', [
            'racks' => $this->rack->get()
        ]);
    }
}
