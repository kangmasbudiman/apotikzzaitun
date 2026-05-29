<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{ToModel, WithValidation};

use App\Repositories\CategoryRepository;

class CategoryImport extends ImportHandle implements ToModel, WithValidation
{

    public $success = 0;
    protected $category;

    public function __construct(CategoryRepository $category)
    {
        $this->category = $category;
    }

    public function rules(): Array
    {
        return [
            'nama' => 'required|string|unique:kategori,nama_kategori'
        ];
    }

    public function model(array $row)
    {
        $this->success++;

        return $this->category->create([
            'nama_kategori' => $row['nama'],
        ]);
    }
}
