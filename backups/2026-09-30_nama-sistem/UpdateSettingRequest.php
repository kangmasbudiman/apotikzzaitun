<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{

    public function prepareForValidation()
    {
        $this->merge([
            'idUser' => auth()->id(),
            'pakaiLogo' => isset($this->pakaiLogo)
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
            'nama_toko' => 'required|string',
            'alamat_toko' => 'required|string',
            'ppn' => 'required|numeric|between:0,99',
            'min_stok' => 'required|numeric|min:1',
            'telepon_toko' => 'required',
            'logo' => 'nullable|image',
            'pakaiLogo' => 'nullable|boolean'
        ];
    }
}
