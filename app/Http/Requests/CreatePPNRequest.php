<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use App\Services\PPNService;

class CreatePPNRequest extends FormRequest
{

    public function prepareForValidation()
    {
        $this->merge([
            'idUser' => auth()->id(),
            'nominal' => intval(str_replace([',', '.'], '', $this->nominal)),
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
    public function rules(PPNService $ppnService)
    {
        $ppn = $ppnService->count();

        return [
            'idUser' => 'required',
            'jenis' => 'required|string|in:PPN Dikeluarkan,PPN Disetorkan',
            'nominal' => 'required|integer|min:1000|max:'.$ppn,
            'keterangan' => 'nullable|string'
        ];
    }
}
