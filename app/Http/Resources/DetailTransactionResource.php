<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DetailTransactionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'          => $this->idDetailPenjualan,
            'id_buku'     => $this->idBuku,
            'obat'        => $this->obat,
            'harga_pokok' => (int) $this->hargaPokok,
            'harga_jual'  => (int) $this->hargaJual,
            'disc'        => (float) $this->disc,
            'jumlah'      => (int) $this->jumlah,
            'subtotal'    => (int) $this->hargaJual * (int) $this->jumlah,
        ];
    }
}