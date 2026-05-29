<?php 

namespace App\Services;

use App\Models\{PPN, Transaction};
use App\Repositories\TransactionRepository;

use Yajra\Datatables\Datatables;

class TransactionService extends Service {

	protected $transaction;
	protected $stuff;

	public function __construct(TransactionRepository $transactionRepo, StuffService $stuff)
	{
		$this->repo = $transactionRepo;
		$this->stuff = $stuff;
	}

	public function cancel($id): Void
	{
		$transaction = $this->getOne($id);

		foreach ($transaction->detail as $stuff) {
			if ($stuff->idBuku) {
				$this->stuff->addStock($stuff->idBuku, $stuff->jumlah);
			}
		}

		$transaction->status = 0;
		$transaction->ppn()->delete();
		$transaction->save();
	}

	public function store(array $data, array $books): Object
	{		
		
		$transaction = $this->repo->create($data);
		
		$transaction->stuffs()->attach($books);

	//	$this->createPPN($transaction);
		
		return $transaction;
	}

	public function createPPN(Transaction $transaction)
	{
		$ppn = PPN::create([
			'idUser' => auth()->id(),
			'jenis' => 'PPN Dikeluarkan',
			'nominal' => $transaction->ppn,
			'keterangan' => substr($transaction->idPenjualan, 0, 5)
		]);

		$transaction->ppn()->save($ppn);
	}

	public function filter($dari = null, $sampai = null, $status = null, $user = null): Object
	{
		$admin = auth()->user()->can('isAdmin');

		if (!is_null($status)) {
			return $status ? $this->filterSuccess($dari, $sampai, $user) : $this->filterCancel($dari, $sampai, $user);
		}

		return $admin ? $this->repo->filter($dari, $sampai, $user) : $this->repo->filterByKasir($dari, $sampai, auth()->id());
	}

	public function filterSuccess($dari = null, $sampai = null, $user = null): Object
	{
		return $this->repo->filterSuccess($dari, $sampai, $user);
	}

	public function filterCancel($dari = null, $sampai = null, $user = null): Object
	{
		return $this->repo->filterCancel($dari, $sampai, $user);
	}

	public function deleteData($id): Object
	{
		$transaction = $this->getOne($id);

		foreach ($transaction->detail as $stuff) {
			if ($stuff->idBuku) {
				$this->stuff->addStock($stuff->idBuku, $stuff->jumlah);
			}
		}

		$transaction->delete();

		return $transaction;
	}

	public function removeStock(array $transactions)
	{
		$transactions = collect($transactions);
		$transactions->each(function ($transaction, $key)
		{
			$this->stuff->removeStock($key, $transaction['total']);
		});
	}

	public function getDatatables($dari = null, $sampai = null, $status = null, $user = null): Object
	{
		$admin = auth()->user()->can('isAdmin');
		$datatables = Datatables::of($this->filter($dari, $sampai, $status, $user))
					->addIndexColumn()
					->editColumn('total_bayar', function ($transaction)
					{
						return 'Rp '.number_format($transaction->total_bayar);
					})
					->editColumn('total', function ($transaction)
					{
						return 'Rp '.number_format($transaction->total);
					})
					->editColumn('tanggal', function ($transaction)
					{
						return date('d M Y H:i A', strtotime($transaction->tanggal));
					})
					->addColumn('kembalian', function ($transaction)
					{
						return 'Rp '.number_format($transaction->total_bayar - $transaction->total);
					})
					->addColumn('status_badge', function ($transaction) use ($admin)
					{
						return $transaction->status_badge;
					})
					->addColumn('action', function ($transaction) use ($admin)
					{
						$lewat = date('Y-m-d') > date('Y-m-d', strtotime($transaction->tanggal));
						$disabled = $lewat || !$transaction->status ? 'disabled' : '';

						return '
							<a href="'.route('transaction.detail', $transaction->idPenjualan).'" class="btn btn-sm btn-success">Detail<i class="fa fa-eye"></i></a>
							<button class="btn btn-sm btn-danger" data-action="cancel" '.$disabled.'>Hapus<i class="fa fa-times"></i></button>
						';
					})
					->rawColumns(['status_badge', 'barcode', 'action'])
					->make();

		return $datatables;
	}

}

 ?>