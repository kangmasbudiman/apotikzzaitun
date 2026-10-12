<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'   => $this->idKategori,
            'nama' => $this->nama_kategori,
        ];
    }
}