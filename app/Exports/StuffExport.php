<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\{FromView, Exportable, ShouldAutoSize, WithStyles};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use App\Repositories\StuffRepository;

class StuffExport implements FromView, ShouldAutoSize, WithStyles
{

    use Exportable;

    protected $stuff;

    public function __construct(StuffRepository $stuff)
    {
        $this->stuff = $stuff;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            'A' => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT]],
            'B' => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT]],
        ];
    }

    public function view(): View
    {
        return view('stuff.export', [
            'stuffs' => $this->stuff->get()
        ]);
    }
}
