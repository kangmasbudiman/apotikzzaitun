<?php

namespace App\Http\Livewire\Transaction;

use App\Transaction;
use App\Services\TransactionService;

use Livewire\Component;

class Payment extends Component
{

	public $total;
	public $money;
	public $return;
	public $ppn;
	public $subtotal;
	public $user;
	public $stuffs;
	public $diskon;
	public $grandtotal;
	public $kembalian;
    public $namapasien;
    public $tgl_lahir;
	protected $listeners = [
		'count-total' => 'count',
		'open-payment' => 'open'
	];

	public function open()
	{
		$this->money = 0;
		$this->return = 0;
		$this->diskon = 0;
		$this->grandtotal = max(intval(str_replace([',', '.'], '', $this->subtotal)) , 0);

		$this->resetValidation();
		$this->dispatchBrowserEvent('open-payment');
	}

	public function store(TransactionService $transactionService)
	{
		$this->money = intval(str_replace([',', '.'], '', $this->money ?: 0));
		$this->diskon = intval(str_replace([',', '.'], '', $this->diskon ?: 0));

		$this->validate([
			'money' => 'required|integer|min:'.$this->subtotal,

		]);

		$data = [
			'idUser' => $this->user->idUser,
			'namaUser' => $this->user->nama,
			'total' => $this->grandtotal,
			//'ppn' => $this->ppn,
			'total_bayar' => $this->money,
			'tanggal' => date('Y-m-d H:i:s'),
			'diskon' => $this->diskon,
			'grandtotal' => $this->subtotal,
			'kembalian' => $this->kembalian,
			'namapasien' => $this->namapasien,
			'tgl_lahir' => $this->tgl_lahir,
		];

	
		
		//dd($this->stuffs);
		$transaction = $transactionService->store($data, $this->stuffs);

		$this->emit('clear-transaction');
		$this->emit('reset-id');
		$this->emit('open-print', $transaction->idPenjualan, $this->money, $this->return,$this->grandtotal,$this->diskon,$this->namapasien);
		$this->dispatchBrowserEvent('close-payment');
		$this->dispatchBrowserEvent('reset-stuff');
	}

	public function count(array $stuffs, int $total)
	{		
		$ppn = site('ppn') / 100;

		$this->stuffs = $stuffs;
		$this->total = $total;
		$this->ppn = 0;
		$this->subtotal = $this->total;
	//	$this->subtotal = $this->total + $this->ppn;
	}

	public function updatedMoney($money)
	{
		$this->return = max(intval(str_replace([',', '.'], '', $money)) - $this->grandtotal, 0);
	}
	public function updatedDiskon($diskon)
	{

		
	
		$diskon = intval(str_replace([',', '.'], '', $diskon ?: 0));
			$this->grandtotal = max(intval(str_replace([',', '.'], '', $this->subtotal)) - $diskon , 0);
		
		
	
	
}
	

	public function mount()
	{
		$this->user = auth()->user();
	}

    public function render()
    {
        return view('livewire.transaction.payment');
    }
}
