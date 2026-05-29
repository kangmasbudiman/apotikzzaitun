<?php

namespace App\Http\Livewire\Stock;

use Livewire\Component;

use App\Services\StockService;

class Cetak extends Component
{

    public $stock, $bayar, $kembali;

    protected $listeners = [
        'open-print' => 'open'
    ];

    public function open(StockService $stockService, $idStock, $bayar, $kembali)
    {
        $this->stock = $stockService->getOne($idStock);
        $this->bayar = $bayar;
        $this->kembali = $kembali;

        $this->dispatchBrowserEvent('open-print');
    }

    public function edit()
    {
        $this->emit('edit-payment', $this->stock->idPembelian);

        $this->dispatchBrowserEvent('close-print');
    }

    public function render()
    {
        return view('livewire.stock.cetak');
    }
}
