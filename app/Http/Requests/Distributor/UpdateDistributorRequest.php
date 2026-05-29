<?php

namespace App\Http\Requests\Distributor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDistributorRequest extends FormRequest
{
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
            'namaDist' => 'required|string',
            'alamat' => 'required|string',
            'telepon' => 'required|numeric'
        ];
    }
}
