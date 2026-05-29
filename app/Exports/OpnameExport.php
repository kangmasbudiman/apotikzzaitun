<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\{FromView, ShouldAutoSize, Exportable};

use App\Models\Opname;

class OpnameExport implements FromView, ShouldAutoSize
{

    use Exportable;

    public function view(): View
    {
        return view('opname.export', [
            'data' => Opname::all()
        ]);
    }
}
