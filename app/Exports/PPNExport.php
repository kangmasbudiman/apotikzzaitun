<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\{FromView, ShouldAutoSize, Exportable};

use App\Services\PPNService;

class PPNExport implements FromView, ShouldAutoSize
{

    use Exportable;

    protected $ppnService;

    public function __construct(PPNService $ppnService)
    {
        $this->ppnService = $ppnService;
    }

    public function view(): View
    {
        return view('ppn.export', [
            'data' => $this->ppnService->report()
        ]);
    }
}
