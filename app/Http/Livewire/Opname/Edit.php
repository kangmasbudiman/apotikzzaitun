<?php

namespace App\Http\Livewire\Opname;

use Livewire\Component;

use App\Models\Opname;
use App\Services\OpnameService;

class Edit extends Component
{

    public $opname, $keterangan;

    protected $rules = [
        'opname.stokNyata' => 'required|integer|min:0|lt:opname.stokSistem',
        'opname.keterangan' => 'nullable|string',
    ];

    public function save(OpnameService $opnameService)
    {
        $this->validate();

        $this->opname->save();

        $opnameService->update($this->opname);

        session()->flash('success', 'Sukses Memperbarui Data');
        redirect()->route('opname.index');
    }

    public function mount($id)
    {
        $opname = Opname::findOrfail($id);

        $this->opname = $opname;        
    }

    public function render()
    {
        return view('livewire.opname.edit');
    }
}
