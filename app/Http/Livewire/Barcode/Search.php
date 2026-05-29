<?php

namespace App\Http\Livewire\Barcode;

use Livewire\Component;

class Search extends Component
{

    public $barcode, $obat, $idBuku;

    protected $rules = [
        'barcode' => 'required|exists:buku',
        'obat' => 'required',
        'idBuku' => 'required',
    ];

    public function setData($data)
    {
        $this->barcode = $data['barcode'];
        $this->obat = $data['obat'];
        $this->idBuku = $data['idBuku'];
    }

    public function submit(): Void
    {
        $data = $this->validate();

        $this->emit('add-barcode', $data);
        $this->reset();
    }

    public function render()
    {
        return view('livewire.barcode.search');
    }
}
