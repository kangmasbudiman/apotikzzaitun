<?php 

namespace App\Services;

use App\Models\{Stock, Expend};
use App\Services\StuffService;
use App\Repositories\StockRepository;

use Yajra\Datatables\Datatables;

class StockService extends Service {

	protected $stuff;

	public function __construct(StockRepository $stock, StuffService $stuff)
	{
		$this->repo = $stock;
		$this->stuff = $stuff;
	}

	// public function getFaktur(): String
	// {
	// 	$lastId = $this->repo->getLastId();
	// 	$id = intval(substr($lastId, -9));

	// 	return 'P'.sprintf("%09d", $id+1);
	// }

	public function filter($dari = null, $sampai = null, $distributor = null): Object
	{
		$admin = auth()->user()->can('isAdmin');

		return $admin ? $this->repo->filter($dari, $sampai, $distributor) : $this->repo->filterByUser($dari, $sampai, $distributor, auth()->id());
	}

	public function latest($dari = null, $sampai = null, $distributor = null): Object
	{
		return $this->repo->latest($dari, $sampai, $distributor);
	}

	// public function store(array $data): Array
	// {
	// 	$stocks = collect();

	// 	foreach ($data as $item) {
	// 		$stock = $this->repo->create($item);

	// 		$stocks->push($stock->idPembelian);
	// 	}

	// 	return $stocks->all();
	// }

	public function store(array $data, array $stuffs): Object
	{
		$stock = $this->repo->create($data);
		
		$stock->stuffs()->attach($stuffs);

		$expend = $this->createExpend($stock);

		$stock->expend()->save($expend);

		return $stock;
	}

	public function update($stock, array $data, array $stuffs): Object
	{
		foreach ($stock->detail as $detail) {
			$stuff = collect($stuffs);

			if (!$stuff->has($detail->idBuku)) {
				$this->stuff->removeStock($detail->idBuku, $detail->jumlah);
			}
		}

		foreach ($stuffs as $idBuku => $data) {
			$detail = collect($stock->detail);

			$search = $detail->firstWhere('idBuku', $idBuku);

			if ($search) {
				$this->stuff->updateStock($idBuku, $data['jumlah'], $search->jumlah);
			}
		}

		$stock->expend->pengeluaran = $stock->total;
		$stock->expend->keterangan = $stock->namaDist;
		$stock->push();

		$stock->stuffs()->sync($stuffs);
		$stock->update($data);

		return $stock;
	}

	public function deleteStock($id): Void
	{
		$stock = $this->repo->find($id);

		foreach ($stock->detail as $stuff) {
			if ($stuff->idBuku) {
				$this->stuff->removeStock($stuff->idBuku, $stuff->jumlah);
			}
		}

		$stock->expend->delete();

		$stock->delete();
	}

	public function createExpend($stock): Expend
	{
		$category = app(CategoryFinanceService::class);
		$buyCategory = $category->getStockCategory();

		return Expend::create([
			'idKategoriPengeluaran' => $buyCategory->idKategoriPengeluaran,
			'pengeluaran' => $stock->total,
			'tanggal' => date('Y-m-d H:i:s'),
			'namaKategori' => $buyCategory->nama,
			'keterangan' => $stock->namaDist
		]);
	}
	//pada object itu yang berhubungan dengan model
	public function getDatatables($dari = null, $sampai = null, $distributor = null): Object

	{
		$datatables = Datatables::of($this->filter($dari, $sampai, $distributor))
						//->addIndexColumn()
						->editColumn('total', function ($stock)
						{
							return 'Rp '.number_format($stock->total);
						})
						->addColumn('date', function ($stock)
						{
							return date('d M Y H:i A', strtotime($stock->tanggal));
						})
						->addColumn('jatuh_tempo', function ($stock)
						{
							return date('d M Y H:i A', strtotime($stock->jatuh_tempo));
						})
						->addColumn('nobatch', function ($stock)
						{
							return $stock->nobatch;
						})
					

						->addColumn('action', function ($stock)
						{
							return '
								<a href="'.route('stock.detail', $stock->idPembelian).'" class="btn btn-sm btn-success"><i class="fa fa-print"></i></a>
							';
						})
						->rawColumns(['type', 'action'])
						->make();

		return $datatables;
	}


}

 ?>