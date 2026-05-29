<?php 

namespace App\Repositories;

use App\Models\Rack;

class RackRepository extends Repository {

    public function __construct(Rack $rack)
    {
        $this->model = $rack;
    }

    public function select($name): Object
    {
        return $this->model->where('nama_rak', 'like', '%'.$name.'%')->get(['idRak as id', 'nama_rak as text']);
    }

    public function findOrCreate(string $name): Object
    {
        return $this->model->firstOrCreate(['nama_rak' => $name]);
    }

}


 ?>