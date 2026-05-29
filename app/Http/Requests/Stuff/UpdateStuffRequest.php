<?php

namespace App\Http\Requests\Stuff;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStuffRequest extends FormRequest
{

    public function prepareForValidation()
    {
        $this->merge([
            'hargaPokok' => intval(str_replace([',', '.'], '', $this->hargaPokok)),
            'hargaJual' => intval(str_replace([',', '.'], '', $this->hargaJual))
        ]);
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('isAdminGudang');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'idKategori' => 'required|exists:kategori,idKategori',
            'idRak' => 'required|exists:rak,idRak',
            'barcode' => 'required|string|unique:buku,barcode,'.$this->stuff.',idBuku',
            'noisbn' => 'nullable|unique:buku,noisbn,'.$this->stuff.',idBuku',
            'obat' => 'required|string',
            'penulis' => 'nullable|string',
            'suplier' => 'required|string',
            'tahun' => 'required|date_format:Y',
            'hargaPokok' => 'required|numeric|min:1|digits_between:0,9|max:'.$this->hargaJual,
            'hargaJual' => 'required|numeric|digits_between:0,9',
           // 'disc' => 'required|numeric|max:100'
        ];
    }
}
