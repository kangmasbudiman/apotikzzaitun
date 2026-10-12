<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RackResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'   => $this->idRak,
            'nama' => $this->nama_rak,
        ];
    }
}