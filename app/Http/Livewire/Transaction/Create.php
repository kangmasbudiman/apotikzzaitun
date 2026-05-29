<?php

namespace App\Http\Livewire\Transaction;

use App\Services\StuffService;
use App\Transaction;
use App\Models\Transaction as TransactionModel;

use Livewire\Component;

use DNS1D;

class Create extends Component
{

	public $barcode;
	public $latestId;
	public $total = 1;
	public $stuff;

	protected $listeners = [
		'transaction-added' => 'stored',
		'error-limit' => 'limit',
		'reset-id' => 'setId'
	];

	protected $validationAttributes = [
		'barcode' => 'barang',
		'total' => 'jumlah'
	];

	public function store(StuffService $stuffService)
	{
		if ($this->stuff) {
			$this->validate([
				'barcode' => 'required|exists:buku',
				'total' => 'required|integer|min:1|max:'.$this->stuff->stock
			]);

			$stuff = $this->stuff;

			$this->create($stuff);
		} else {
			$this->validate([
				'barcode' => 'required|exists:buku',
				'total' => 'required|integer|min:1|min:1'
			]);

			$stuff = $stuffService->getByCode($this->barcode);

			if ($this->total > $stuff->stock) {
				$this->addError('total', 'jumlah terlalu banyak');
			} else {
				$this->create($stuff);
			}
		}
	}

	public function create($stuff)
	{
		$data = [
			'id' => $stuff->idBuku,
			'total' => $this->total,
			'obat' => $stuff->obat,
			'stock' => $stuff->stock,
			'hargaJual' => $stuff->hargaJual,
			'hargaPokok' => $stuff->hargaPokok,
			'disc' => $stuff->disc
		];

		$this->emit('store-transaction', $data);
	}

	public function stored(array $stuffs, int $total)
	{
		$this->emit('count-total', $stuffs, $total);
		$this->barcode = null;
		$this->stuff = null;
		$this->total = 1;
		$this->dispatchBrowserEvent('reset');
	}

	public function limit()
	{
			$this->addError('total', 'jumlah terlalu banyak');
	}

	public function search(StuffService $stuff)
	{
		$this->validate(
			[ 'barcode' => 'required|exists:buku' ],
			[
				'barcode.exists' => 'barcode tidak ditemukan'
			]
		);

		$this->stuff = $stuff->getByCode($this->barcode);
	}

	public function setId()
	{
		$lastId = TransactionModel::latest('idPenjualan')->first()->idPenjualan ?? 0;
		$lastId = (int)substr($lastId, 1, 4);

		$this->latestId = sprintf("T%04d-%d-%06d", $lastId+1, auth()->id(), date('dmy'));
	}

	public function getFilledProperty()
	{
		return is_null($this->stuff);
	}

	public function mount()
	{
		$this->setid();
	}

  public function render()
  {
      return view('livewire.transaction.create');
  }
}
