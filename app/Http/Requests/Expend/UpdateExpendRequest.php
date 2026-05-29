<?php

namespace App\Http\Requests\Expend;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpendRequest extends FormRequest
{

    public function prepareForValidation()
    {
        $this->merge([
            'pengeluaran' => intval(str_replace([',', '.'], '', $this->pengeluaran))
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
        return [
            'pengeluaran' => 'required|integer|min:1',
            'idUser' => 'nullable|exists:user,idUser',
            'namaUser' => 'nullable|exists:user,nama',
            'idKategoriPengeluaran' => 'required|exists:kategori_pengeluaran,idKategoriPengeluaran',
            'keterangan' => 'nullable|string'
        ];
    }
}
