<?php 

namespace App\Repositories;

use App\Models\Category;

class CategoryRepository extends Repository {

    public function __construct(Category $category)
    {
        $this->model = $category;
    }

    public function select($name): Object
    {
        return $this->model->where('nama_kategori', 'like', '%'.$name.'%')->get(['idKategori as id', 'nama_kategori as text']);
    }

    public function findOrCreate(string $name): Object
    {
        return $this->model->firstOrCreate(['nama_kategori' => $name]);
    }

}


 ?>