<?php

namespace App\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

class CreateStockRequest extends FormRequest
{
    public function prepareForValidation()
    {
        $this->merge([
            'tanggal' => date('Y-m-d')
        ]);
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'idBuku' => 'required|exists:buku,idBuku',
            'idDist' => 'required|exists:distributor,idDist',
            'jumlah' => 'required|integer'
        ];

        return $rules;
    }
}
