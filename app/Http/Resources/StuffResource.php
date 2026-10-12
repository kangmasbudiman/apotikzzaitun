<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StuffResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $minStok = (int) site('min_stok');

        return [
            'id'         => $this->idBuku,
            'barcode'    => $this->barcode,
            'obat'       => $this->obat,
            'noisbn'     => $this->noisbn,
            'penulis'    => $this->penulis,
            'suplier'    => $this->suplier,
            'tahun'      => $this->tahun,

            'harga_pokok'=> (int) $this->hargaPokok,
            'harga_jual' => (int) $this->hargaJual,
            'disc'       => (float) $this->disc,
            'ppn'        => (float) $this->ppn,
            'margin'     => (float) $this->margin,

            'stock'      => (int) $this->stock,
            'min_stok'   => $minStok,

            // Helper tampilan di app: 'habis' | 'menipis' | 'aman'
            'stok_status'=> $this->stock <= 0
                                ? 'habis'
                                : ($this->stock <= $minStok ? 'menipis' : 'aman'),

            'id_kategori'=> $this->idKategori,
            'id_rak'     => $this->idRak,

            'kategori'   => $this->whenLoaded('category', fn () => [
                'id'   => $this->category->idKategori,
                'nama' => $this->category->nama_kategori,
            ]),

            'rak'        => $this->whenLoaded('rack', fn () => [
                'id'   => $this->rack->idRak,
                'nama' => $this->rack->nama_rak,
            ]),
        ];
    }
}