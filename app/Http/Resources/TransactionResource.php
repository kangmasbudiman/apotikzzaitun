<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class TransactionResource extends JsonResource
{
    public function toArray($request)
    {
        // `tanggal` belum di-cast di model Transaction, jadi bisa string atau Carbon.
        $tanggal = $this->tanggal
            ? (($this->tanggal instanceof \DateTimeInterface)
                ? $this->tanggal->format('Y-m-d H:i:s')
                : Carbon::parse($this->tanggal)->format('Y-m-d H:i:s'))
            : null;

        return [
            'id'            => $this->idPenjualan,
            'id_user'       => $this->idUser,
            'nama_user'     => $this->namaUser,
            'tanggal'       => $tanggal,
            'status'        => (bool) $this->status,

            'total'         => (int) $this->total,

            // Tabel `penjualan` tidak punya kolom total_bayar (dimakan oleh
            // mass-assignment di versi web), jadi diturunkan dari kembalian:
            // uang yang dibayar = total (tagihan) + kembalian.
            'total_bayar'   => isset($this->kembalian) && $this->kembalian !== ''
                                ? (int) $this->total + (int) $this->kembalian
                                : (int) $this->total,

            'diskon'        => (int) $this->diskon,
            'grandtotal'    => (int) $this->grandtotal,
            'kembalian'     => isset($this->kembalian) ? (int) $this->kembalian : null,

            'jumlah_item'   => $this->whenLoaded('detail', fn () => $this->detail->sum('jumlah')),

            'items'         => DetailTransactionResource::collection($this->whenLoaded('detail')),
        ];
    }
}