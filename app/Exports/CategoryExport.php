<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\{FromView, ShouldAutoSize, Exportable};

use App\Repositories\CategoryRepository;

class CategoryExport implements FromView, ShouldAutoSize
{

    use Exportable;

    protected $category;

    public function __construct(CategoryRepository $category)
    {
        $this->category = $category;
    }

    public function view(): View
    {
        return view('category.export', [
            'categories' => $this->category->get()
        ]);
    }
}
