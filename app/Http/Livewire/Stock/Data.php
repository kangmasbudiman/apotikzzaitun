<?php

namespace App\Http\Livewire\Stock;

use Livewire\Component;

use App\Repositories\{StuffRepository, StockRepository};

class Data extends Component
{

	public $data = [];
	public $edit = false;

	protected $listeners = ['addData', 'updateQty', 'edit-data' => 'edit', 'clear'];

	public function addData($stuff, $jumlah,$nobatch,$expired)
	{
		$idBuku = $stuff['idBuku'];
		$barcode = $stuff['barcode'];
		$hargaPokok = $stuff['hargaPokok'];
		$obat = $stuff['obat'];
		
		
		
		

		$data = collect($this->data);
		
		$contain = $data->contains('idBuku', $idBuku);

		if ($contain) {
			$data = $data->map(function ($buku, $index) use ($idBuku, $jumlah)
			{
				if ($buku['idBuku'] == $idBuku) {
					$buku['jumlah'] += $jumlah;
					$buku['nobatch'] == $nobatch;
				}

				return $buku;
			});
		} else {
			$data->push([
				'idBuku' => $idBuku,
				'barcode' => $barcode,
				'obat' => $obat,
				'hargaPokok' => $hargaPokok,
				'jumlah' => $jumlah,
				'nobatch' => $nobatch,
				'expired' => $expired,
				
			]);
		}

		$this->data = $data->all();

		$this->countTotal();
	}

	public function remove($id)
	{
		$data = collect($this->data);
		$removed = $data->splice($id, 1);

		$this->data = $data->all();

		$this->emit('removeData', $removed[0]);
		$this->countTotal();
	}

	public function increment($id)
	{
		$this->data[$id]['jumlah']++;
		$this->countTotal();
	}

	public function decrement($id)
	{
		if ($this->data[$id]['jumlah'] <= 1) {
			session()->flash('danger', 'Jumlah terlalu sedikit');
		} else {
			$this->data[$id]['jumlah']--;
			$this->countTotal();
		}
	}

	public function updateQty($id, $val)
	{
		$this->data[$id]['jumlah'] = $val;
		$this->countTotal();
	}

	public function clear()
	{
		$this->reset();
		$this->countTotal();
	}

	public function countTotal()
	{
		$this->emit('countTotal', $this->data, $this->edit);
	}

	public function edit(StockRepository $stockRepo, $id)
	{
		$stuffs = $stockRepo->find($id)->detail;

		$this->edit = true;

		foreach ($stuffs as $stuff) {
			$data = [
				'idBuku' => $stuff->idBuku,
				'barcode' => $stuff->stuff->barcode,
				'obat' => $stuff->obat,
				'hargaPokok' => $stuff->hargaPokok,
				'jumlah' => $stuff->jumlah
			];

			array_push($this->data, $data);
		}
	}

	public function mount($id = null)
	{
		if ($id) {
			$this->edit($id);
		}		
	}

  public function render()
  {
    return view('livewire.stock.data');
  }
}
