<?php 

namespace App\Services;

use App\Repositories\StuffRepository;

use Yajra\Datatables\Datatables;

class StuffService extends Service {

	public function __construct(StuffRepository $stuff)
	{
		$this->repo = $stuff;
	}

	public function addStock($id, int $stock): Object
	{
		$stuff = $this->repo->find($id);
		$this->repo->addStock($stuff, $stock);

		return $stuff;
	}

	public function removeStock($id, int $stock): Object
	{
		$stuff = $this->repo->find($id);
		$this->repo->removeStock($stuff, $stock);

		return $stuff;
	}

	public function updateStock($id, int $newStock, int $oldStock): Void
	{
		$stuff = $this->repo->find($id);

		$this->repo->removeStock($stuff, $oldStock);
		$this->repo->addStock($stuff, $newStock);
	}

	public function getByCode($code): Object
	{
		return $this->repo->getByCode($code);
	}

	public function getByISBN($isbn): Object
	{
		return $this->repo->getByISBN($isbn);
	}

	public function selectByCode($code): Object
	{
		return $this->repo->selectByCode($code);
	}

	public function getStock($id): Int
	{
		return $this->repo->getStock($id);
	}

	public function filter($penerbit = null, $tahun = null): Object
	{
		return $this->repo->filter($penerbit, $tahun);
	}

	public function countStock(): Int
	{
		return $this->repo->countStock();
	}

	public function search($id)
	{
		return $this->repo->search($id);
	}

	public function getDatatables($penerbit = null, $tahun = null): Object
	{
		$admin = auth()->user()->can('isAdmin');
		$gudang = auth()->user()->can('isAdminGudang');
		$datatables = Datatables::of($this->filter($penerbit, $tahun))
						->addIndexColumn()
						->editColumn('price', function ($stuff)
						{
							return 'Rp '.number_format($stuff->hargaJual);
						})
						->editColumn('purchase_price', function ($stuff)
						{
							return 'Rp '.number_format($stuff->hargaPokok);
						})
						->addColumn('barcode_generate', function ($stuff) use ($gudang)
						{
							return $gudang ? '<span>'.$stuff->barcode.'</span><br><a class="btn btn-sm btn-warning" href="'.route('stuff.barcode', $stuff->idBuku).'">Generate</a>' : $stuff->barcode;
						})
						->addColumn('action', function ($stuff) use ($admin, $gudang)
						{
							if ($admin) {
								$button = '<button class="btn btn-success btn-sm" data-action="edit"><i class="fa fa-edit"></i></button>
								<button class="btn btn-info btn-sm" data-action="show"><i class="fa fa-eye"></i></button>
								<button class="btn btn-danger btn-sm" data-action="remove"><i class="fa fa-trash"></i></button>';
							} else if ($gudang) {
								$button = '<button class="btn btn-success btn-sm" data-action="edit"><i class="fa fa-edit"></i></button>
								<button class="btn btn-info btn-sm" data-action="show"><i class="fa fa-eye"></i></button>';
							} else {
								$button = '<button class="btn btn-info btn-sm" data-action="show"><i class="fa fa-eye"></i></button>';
							}

							return $button;
						})
						->rawColumns(['barcode_generate', 'action'])
						->make();

		return $datatables;
	}

}

 ?>