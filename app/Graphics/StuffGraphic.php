<?php 

namespace App\Graphics;

use App\Repositories\StuffRepository;

class StuffGraphic
{

    protected $stuff;

    public function __construct(StuffRepository $stockRepo)
    {
        $this->stuff = $stockRepo;
    }

    public function count(string $type): Int
    {
        $data = $this->stuff->count();

        return $data;
    }

}

 ?>