<?php

namespace App\Http\Livewire\Opname;

use Livewire\Component;

use App\Services\OpnameService;

class Create extends Component
{

    public $barcode, $stokSistem, $keterangan;
    public $stokNyata = 0;

    protected $rules = [
        'barcode' => 'required|exists:buku,barcode',
        'stokNyata' => 'required|integer|min:0|lte:stokSistem',
        'keterangan' => 'nullable|string',
    ];

    public function save(OpnameService $opnameService)
    {
        $data = $this->validate();

        $opnameService->store($data);

        session()->flash('success', 'Sukses Menambahkan Data');
        redirect()->route('opname.index');
    }

    public function render()
    {
        return view('livewire.opname.create');
    }
}
