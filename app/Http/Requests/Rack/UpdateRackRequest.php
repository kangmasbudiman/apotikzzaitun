<?php

namespace App\Http\Requests\Rack;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRackRequest extends FormRequest
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
            'nama_rak' => 'required|string|unique:rak,nama_rak,'.$this->rack.',idRak'
        ];
    }
}
