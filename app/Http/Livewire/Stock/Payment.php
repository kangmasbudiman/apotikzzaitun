<?php

namespace App\Http\Livewire\Stock;

use Livewire\Component;

use App\Repositories\{StuffRepository, StockRepository};
use App\Services\{StockService, DetailStockService};
use App\Models\Distributor;

class Payment extends Component
{

	public $subtotal, $bayar, $kembali, $data, $distributor,$faktur,$jatuhtempo,$tempo;
	public $distributorName;
	public $stuffs = [];
	public $stock = null;
	// public $detailId = null;

	protected $listeners = [
		'countTotal',
		'removeData',
		'open-payment' => 'open',
		'edit-payment' => 'edit',
	];

	public function open()
	{
		$this->bayar = 0;
		$this->kembali = 0;

		$this->resetValidation();
		
		if (!$this->distributorName) {
			$this->distributor = null;
		
			$this->dispatchBrowserEvent('open-payment');
		} else {
			$this->dispatchBrowserEvent('open-payment', ['edit' => true]);
		}
	}

	public function openPrint()
	{		
		$this->dispatchBrowserEvent('open-print');
	}

	public function countTotal(array $data = null, bool $edit = false)
	{
		$detailService = app(DetailStockService::class);

		$subtotal = 0;

		if ($data) {
			foreach ($data as $stuff) {
				$subtotal += $stuff['hargaPokok'] * $stuff['jumlah'];

				// $idDetail = $edit ? $this->detailId : $detailService->getLatestId();

				$this->stuffs[$stuff['idBuku']] = [
					// 'idDetail' => $idDetail,
					'idBuku' => $stuff['idBuku'],
					'obat' => $stuff['obat'],
					'jumlah' => $stuff['jumlah'],
					'hargaPokok' => $stuff['hargaPokok'],
					'nobatch' => $stuff['nobatch'],
					'expired' => $stuff['expired'],
				];
			}
		}

		$this->subtotal = $subtotal;
		$this->data = $data;
	}

	public function removeData($id)
	{
		$stuffs = collect($this->stuffs);
		$stuffs->forget($id);

		$this->stuffs = $stuffs->all();
	}

	public function countFine()
	{
		$this->kembali = max(intval(str_replace([',', '.'], '', $this->bayar)) - $this->subtotal, 0);
	}

	public function updatedBayar()
	{
		$this->countFine();
	}

	public function submit()
	{
		$this->bayar = intval(str_replace([',', '.'], '', $this->bayar));

		$this->validate([
			'subtotal' => 'min:1',
			'bayar' => 'required|integer|min:'.$this->subtotal,
			'distributor' => 'required',
			'data' => 'required|array|min:1',
			'faktur' => 'required|min:1',
			'jatuhtempo' => 'required|min:1',
			'tempo' => 'required|min:1',
		]);

		$pasok = $this->stock ? $this->update() : $this->store();

		$this->dispatchBrowserEvent('close-payment');
		$this->dispatchBrowserEvent('reset-stuff');
		$this->emit('reset-id');
		$this->emit('open-print', $this->stock->idPembelian, $this->bayar, $this->kembali);
		$this->clear();
	}

	public function clear()
	{
		$this->stuffs = [];
		$this->data = null;
		$this->stock = null;

		$this->emit('clear');
	}

	public function store()
	{
		$stockService = app(StockService::class);
		$distributor = Distributor::find($this->distributor);
		
		$data = [
			'idDist' => $distributor->idDist,
			'idUser' => auth()->id(),
			'namaDist' => $distributor->namaDist,
			'namaUser' => auth()->user()->nama,
			'total_bayar' => $this->bayar,
			'total' => $this->subtotal,
			'tanggal' => date('Y-m-d H:i:s'),
			'faktur' => $this->faktur,
			'jatuh_tempo' => $this->jatuhtempo,
			'tempo' => $this->tempo,
		];

		$stock = $stockService->store($data, $this->stuffs);

		$this->stock = $stock;
	}

	public function update(): Object
	{
		$stock = app(StockService::class);
		$distributor = Distributor::find($this->distributor);

		$data = [
			'idDist' => $distributor->idDist,
			'namaDist' => $distributor->namaDist,
			'total_bayar' => $this->bayar,
			'total' => $this->subtotal
		];

		$this->stock->update($data);

		return $stock->update($this->stock, $data, $this->stuffs);
	}

	public function setDistributor($distributor)
	{
		$this->distributor = $distributor->idDist;
		$this->distributorName = $distributor->namaDist;
	}

	public function edit(StockRepository $stockRepo, $id)
	{
		$stock = $stockRepo->find($id);

		$data = collect($stock->detail)->map(function ($stuff)
		{
			return [
				// 'idDetail' => $stuff->idDetail,
				'idBuku' => $stuff->idBuku,
				'barcode' => $stuff->barcode,
				'obat' => $stuff->obat,
				'hargaPokok' => $stuff->hargaPokok,
				'jumlah' => $stuff->jumlah
			];;
		});

		// $this->detailId = $stock->detail[0]->idDetail;
		
		$this->setDistributor($stock->distributor);
		$this->countTotal($data->all(), true);

		$this->countFine();

		$this->stock = $stock;

		$this->emit('edit-data', $stock->idPembelian);
		$this->emit('reset-id', $stock->idPembelian);
	}

	public function mount($id = null)
	{
		if ($id) {
			$this->edit($id);	
		}
	}

  public function render()
  {
    return view('livewire.stock.payment');
  }
}
