<?php 

namespace App;

use Illuminate\Support\Facades\Cookie;
use App\Services\{StuffService, TransactionService, DetailTransactionService};

trait Transaction
{

	public $transactions = [];
	public $subtotal;

	public function create(array $data): Void
	{
		$transactions = collect($this->transactions);
		$transaction = $transactions->firstWhere('id', $data['id']);

		if ($transaction) {
			if ($data['total'] > ($data['stock'] - $transaction['total'])) {
				$this->emit('error-limit');
			} else {
				$transactions = $this->update($transaction, $data);
				
				$this->store(collect($transactions));
			}
		} else {
			$transactions->push($data);
	
			$this->store($transactions);
		}
	}

	public function update(array $transaction, array $data)
	{
		$total = $transaction['total'] + $data['total'];
		
		if ($total > $data['stock']) {
			$this->emit('error-limit');
		} else {
			$transactions = $this->transactions;
			$index = $this->searchIndex($transaction['id']);
			
			$transactions[$index]['total'] = $total;

			return $transactions;
		}
	}

	public function updateStock($id, int $total)
	{
		$transactions = $this->transactions;		
		$index = $this->searchIndex($id);

		$transactions[$index]['total'] = $total;

		$this->store(collect($transactions));
	}

	private function searchIndex($id): Int
	{
		return collect($this->transactions)->search(function ($item) use ($id)
		{
			return $item['id'] === $id;
		});
	}

	public function delete($id)
	{
		$transactions = collect($this->transactions);
		$transactions->splice($this->searchIndex($id), 1);

		$this->store($transactions);
	}

	public function increment($id)
	{
		$stuffService = app(StuffService::class);
		$transactions = $this->transactions;

		$stuff = $stuffService->getOne($id);
		$index = $this->searchIndex($id);

		if ($transactions[$index]['total'] >= $stuff->stock) {
			$this->error('Stok melebihi batas');
		} else {
			$transactions[$index]['total']++;
		}

		$this->store(collect($transactions));
	}

	public function decrement($id)
	{
		$transactions = $this->transactions;

		$index = $this->searchIndex($id);

		if ($transactions[$index]['total'] <= 1) {
			$this->error('Stok terlalu sedikit');
		} else {
			$transactions[$index]['total']--;
		}

		$this->store(collect($transactions));
	}

	public function clear()
	{
		$this->transactions = [];
	}

	public function total(): Int
	{
		$transactions = collect($this->transactions);

		$total = $transactions->sum(function ($transaction)
		{
			$disc = $transaction['total'] * ($transaction['hargaJual'] * $transaction['disc'] / 100);
			$total = $transaction['total'] * $transaction['hargaJual'] - $disc;

			return $total;
		});

		return $total;
	}

	public function result(): Array
	{
		$data = [];

		foreach ($this->transactions as $transaction) {
			$disc = $transaction['total'] * ($transaction['hargaJual'] * $transaction['disc'] / 100);
			$ppn = site('ppn') / 100 * ($transaction['total'] * $transaction['hargaJual'] - $disc);
			
			$data[$transaction['id']] = [
				'obat' => $transaction['obat'],
				'hargaPokok' => $transaction['hargaPokok'],
				'hargaJual' => $transaction['hargaJual'],
				'jumlah' => $transaction['total'],
				'ppn' => $ppn,
				'disc' => $disc,
			];
		}

		return $data;
	}

	public function store(object $transactions)
	{
		$stuffService = app(StuffService::class);

		$transactions = $transactions->map(function ($transaction) use ($stuffService)
		{
			if ($stuffService->search($transaction['id'])) {
				$transaction['stuff'] = $stuffService->search($transaction['id']);

				return $transaction;
			}
		})->reject(function ($transaction)
		{
			return is_null($transaction);
		})->values();

		$this->transactions = $transactions->all();
		$this->subtotal = $this->total();
		$this->emit('transaction-added', $this->result(), $this->total());
	}

}

 ?>