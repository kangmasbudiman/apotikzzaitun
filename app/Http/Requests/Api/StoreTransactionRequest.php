<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    /**
     * Transaksi hanya untuk Admin & Kasir.
     */
    public function authorize()
    {
        return $this->user()->can('isAdminKasir');
    }

    public function rules()
    {
        return [
            'items'             => 'required|array|min:1|max:200',
            'items.*.id_buku'   => 'required|string|distinct|exists:buku,idBuku',
            'items.*.jumlah'    => 'required|integer|min:1|max:9999',

            'diskon'            => 'nullable|integer|min:0',
            'total_bayar'       => 'required|integer|min:0',

            'catatan'           => 'nullable|string|max:200',
        ];
    }

    public function attributes()
    {
        return [
            'items'                => 'item',
            'items.*.id_buku'      => 'obat',
            'items.*.jumlah'       => 'jumlah',
            'total_bayar'          => 'uang pembayaran',
            'diskon'               => 'diskon',
        ];
    }
}