<?php

namespace App\Http\Livewire\Transaction;

use App\Transaction;

use Livewire\Component;

class Data extends Component
{

	use Transaction;

	protected $listeners = [
		'store-transaction' => 'create',
		'clear-transaction' => 'clear',
		'update-stock' => 'updateStock'
	];

	public function error(string $msg)
	{
		session()->flash('stock-error', $msg);
	}


    public function render()
    {
    	$transactions = $this->transactions;
     
        return view('livewire.transaction.data', compact('transactions'));
    }
}
