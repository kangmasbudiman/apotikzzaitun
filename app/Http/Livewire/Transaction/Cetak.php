<?php

namespace App\Http\Livewire\Transaction;

use Livewire\Component;

use App\Services\TransactionService;

class Cetak extends Component
{
    
    public $transaction, $bayar, $kembali,$grandtotal,$diskon;

    protected $listeners = ['open-print' => 'open'];

    public function open(TransactionService $transactionService, $id, int $bayar, int $kembali, int $grandtotal,int $diskon)
    {
        $this->bayar = $bayar;
        $this->kembali = $kembali;
        $this->grandtotal= $grandtotal;
        $this->diskon= $diskon;
        $this->transaction = $transactionService->getOne($id);

        $this->dispatchBrowserEvent('open-print');
    }
    
    public function render()
    {
        return view('livewire.transaction.cetak');
    }
}
