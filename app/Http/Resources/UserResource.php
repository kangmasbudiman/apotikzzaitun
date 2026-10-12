<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'         => $this->idUser,
            'nama'       => $this->nama,
            'username'   => $this->username,
            'alamat'     => $this->alamat,
            'telepon'    => $this->telepon,
            'hak_akses'  => (int) $this->hakAkses,
            'role'       => $this->role(),
            'is_admin'   => $this->can('isAdmin'),

            'bisa_transaksi' => $this->can('isAdminKasir'),
            'bisa_master'    => $this->can('isAdminGudang'),
        ];
    }
}