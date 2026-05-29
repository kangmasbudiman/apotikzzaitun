<?php 

namespace App\Repositories;

use App\Models\Stuff;

class StuffRepository extends Repository {

	public function __construct(Stuff $stuff)
	{
		$this->model = $stuff;
	}

	public function find($id): Object
	{
		return $this->model->with(['category', 'rack'])->findOrFail($id);
	}

	public function deleteBatch(array $data): Void
	{
		$this->model->whereIn('idBuku', $data)->delete();
	}

	public function search($id)
	{
		return $this->model->with(['category', 'rack'])->find($id);
	}

	public function countStock(): Int
	{
		return $this->model->sum('stock');
	}

	public function countLimit(): Int
	{
		return $this->model->where('stock', '<', site('min_stok'))->count();
	}

	public function countPerDate($date): Int
	{
		return $this->model->count();
	}

	public function countPerMonth(int $month, int $year): Int
	{
		return $this->model->whereMonth('created_at', $month)
			->whereYear('created_at', $year)->count();
	}

	public function countPerYear(int $year): Int
	{
		return $this->model->whereYear('created_at', $year)->count();
	}

	public function countPerRange(string $start, string $end): Int
	{
		return $this->model->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end)
			->count();
	}

	public function addStock(object $stuff, int $stock): Object
	{
		$stuff->increment('stock', $stock);

		return $stuff;
	}

	public function removeStock(object $stuff, int $stock): Object
	{
		if ($stock <= $stuff->stock) {
			$stuff->decrement('stock', $stock);
		} else {
			$stuff->stock = 0;
			$stuff->save();
		}

		return $stuff;
	}

	public function select($barcode): Object
	{
		return $this->model->where('barcode', 'like', '%'.$barcode.'%')->get(['idBuku as id', 'obat as text', 'barcode', 'noisbn', 'penulis', 'suplier', 'tahun', 'hargaPokok', 'stock']);
	}

	public function selectByCode($code): Object
	{
		return $this->model->where('code', 'like', '%'.$code.'%')->get(['id', 'code as text', 'obat', 'price', 'stock']);
	}

	public function getByISBN($isbn): Object
	{
		return $this->findWhere('noisbn', $isbn);
	}

	public function getByCode($code): Object
	{
		return $this->findWhere('barcode', $code);
	}

	public function getStock($id): Int
	{
		return $this->model->findOrFail($id)->value('stock');
	}

	public function getByIds(array $id): Object
	{
		return $this->model->whereIn('idBuku', $id)->get();
	}

	public function getByCodes(array $codes): Object
	{
		return $this->model->whereIn('barcode', $codes)->get();
	}

	public function getLimit(): Object
	{
		return $this->model->where('stock', '<', site('min_stok'))->orderBy('stock', 'asc')->paginate(10);
	}

	public function getPenerbit(): Object
	{
		return $this->model->groupBy('suplier')->get('suplier');
	}

	public function getTahun(): Object
	{
		return $this->model->groupBy('tahun')->get('tahun');
	}

	public function get(): Object
	{
		return $this->model->with(['category', 'rack'])->latest('idBuku')->get();
	}

	public function getTake(int $take): Object
	{
		return $this->model->take($take)->orderBy('idBuku', 'asc')->get();
	}

	public function getById(array $id): Object
	{
		return $this->model->whereIn('idBuku', $id)->orderBy('idBuku', 'asc')->get();
	}

	public function filter(string $penerbit = null, int $year = null): Object
	{
		return $this->model->where('suplier', 'like', '%'.$penerbit.'%')->when($year, function ($query) use ($year)
		{
			return $query->where('tahun', $year);
		})->with(['category', 'rack'])->latest('idBuku')->get();
	}

}


 ?>