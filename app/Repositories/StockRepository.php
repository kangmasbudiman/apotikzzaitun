<?php 

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\Models\Stock;

class StockRepository extends Repository {

	public function __construct(Stock $stock)
	{
		$this->model = $stock;
	}

	public function get(): Object
	{
		return $this->model->latest('tanggal')->get();
	}

	public function getLastId()
	{
		return $this->model->orderBy('no_faktur', 'desc')->first()->no_faktur ?? 0;
	}

	public function getByIds(array $id): Object
	{
		return $this->model->has('stuff')->with(['stuff', 'distributor'])->whereIn('idPembelian', $id)->latest('tanggal')->get();
	}

	public function getDist(): Object
	{
		return $this->model->select('namaDist')->groupBy('namaDist')->get();
	}

	public function getDistributor($id): Object
	{
		$stock = $this->model->with(['distributor'])->findOrFail($id);
		
		return $stock->distributor;
	}

	public function find($id): Object
	{
		return $this->model->with(['detail.stuff', 'distributor'])->findOrFail($id);
	}

	public function countPerDate($date): Int
	{
		return $this->model->whereDate('tanggal', $date)->count();
	}

	public function countPerMonth(int $month, int $year): Int
	{
		return $this->model->whereMonth('tanggal', $month)
			->whereYear('tanggal', $year)->count();
	}

	public function countPerYear(int $year): Int
	{
		return $this->model->whereYear('tanggal', $year)->count();
	}

	public function countPerRange(string $start, string $end): Int
	{
		return $this->model->whereDate('tanggal', '>=', $start)->whereDate('tanggal', '<=', $end)
			->count();
	}

	public function countBuyedBookPerDate($date): Int
	{
		return $this->model->whereDate('tanggal', $date)->selectRaw('SUM(jumlah) as total')
		->join(DB::raw('(SELECT SUM(jumlah) as jumlah, detail_pembelian.idPembelian FROM detail_pembelian GROUP BY detail_pembelian.idPembelian) detail_pembelian'), function ($join)
		{
			$join->on('pembelian.idPembelian', '=', 'detail_pembelian.idPembelian');
		})->first()->total ?? 0;
	}

	public function countBuyedBookPerMonth(int $month, int $year): Int
	{
		return $this->model->whereMonth('tanggal', $month)
			->whereYear('tanggal', $year)->selectRaw('SUM(jumlah) as total')
		->join(DB::raw('(SELECT SUM(jumlah) as jumlah, detail_pembelian.idPembelian FROM detail_pembelian GROUP BY detail_pembelian.idPembelian) detail_pembelian'), function ($join)
		{
			$join->on('pembelian.idPembelian', '=', 'detail_pembelian.idPembelian');
		})->first()->total ?? 0;
	}

	public function countBuyedBookPerYear(int $year): Int
	{
		return $this->model->whereYear('tanggal', $year)->selectRaw('SUM(jumlah) as total')
		->join(DB::raw('(SELECT SUM(jumlah) as jumlah, detail_pembelian.idPembelian FROM detail_pembelian GROUP BY detail_pembelian.idPembelian) detail_pembelian'), function ($join)
		{
			$join->on('pembelian.idPembelian', '=', 'detail_pembelian.idPembelian');
		})->first()->total ?? 0;
	}

	public function countBuyedBookPerRange(string $start, string $end): Int
	{
		return $this->model->whereDate('tanggal', '>=', $start)->whereDate('tanggal', '<=', $end)->selectRaw('SUM(jumlah) as total')
		->join(DB::raw('(SELECT SUM(jumlah) as jumlah, detail_pembelian.idPembelian FROM detail_pembelian GROUP BY detail_pembelian.idPembelian) detail_pembelian'), function ($join)
		{
			$join->on('pembelian.idPembelian', '=', 'detail_pembelian.idPembelian');
		})->first()->total ?? 0;
	}

	public function sumIncomePerDate($date): Int
	{
		return $this->model->whereDate('tanggal', $date)->sum('total');
	}

	public function sumIncomePerMonth(int $month, int $year): Int
	{
		return $this->model->whereMonth('tanggal', $month)
			->whereYear('tanggal', $year)->sum('total');
	}

	public function sumIncomePerYear(int $year): Int
	{
		return $this->model->whereYear('tanggal', $year)->sum('total');
	}

	public function sumIncomePerRange(string $start, string $end): Int
	{
		return $this->model->whereDate('tanggal', '>=', $start)->whereDate('tanggal', '<=', $end)
			->sum('total');
	}

	public function filter($dari = null, $sampai = null, $distributor = null): Object
	{
		return $this->model->when($dari, function ($stock) use ($dari)
		
//		{
			//return $stock->where('tanggal', '>=', $dari);

	//	})->when($sampai, function ($stock) use ($sampai)
	//	{
			//return $stock->where('tanggal', '<=', date('Y-m-d', strtotime('+1 day', strtotime($sampai))));
	//	})->when($distributor, function ($stock) use ($distributor)
		{
			//return $stock->where('namaDist', $distributor);

		})
		->with(['distributor'])->latest('idPembelian')->get();
	}

	public function filterByUser($dari = null, $sampai = null, $distributor = null, $user): Object
	{
		return $this->model->where('idUser', $user)->when($dari, function ($stock) use ($dari)
		{
			return $stock->where('tanggal', '>=', $dari);
		})->when($sampai, function ($stock) use ($sampai)
		{
			return $stock->where('tanggal', '<=', date('Y-m-d', strtotime('+1 day', strtotime($sampai))));
		})->when($distributor, function ($stock) use ($distributor)
		{
			return $stock->where('namaDist', $distributor);
		})->with(['distributor'])->latest('idPembelian')->get();
	}

	public function getByFactur($factur)
	{
		return $this->model->whereNoFaktur($factur)->with('stuff')->latest('tanggal')->get();
	}

    public function getReport(string $start = null, string $end = null, $distributor = null, $user = null): Object
    {
        return $this->model->when($start, function ($query) use ($start)
        {
            return $query->whereDate('tanggal', '>=', $start);
        })->when($end, function ($query) use ($end)
        {
            return $query->whereDate('tanggal', '<=', $end);
        })->when($distributor, function ($stock) use ($distributor)
		{
			return $stock->where('pembelian.namaDist', $distributor);
		})->when($user, function ($stock) use ($user)
		{
			return $stock->where('idUser', $user);
		})->selectRaw('sum(total) as total, namaDist, DATE(tanggal) as tanggal')->groupBy('namaDist')->groupBy(DB::raw('DATE(tanggal)'))->latest('tanggal')->get();
    }

    public function getReportDaily(string $date, $user = null): Object
    {
        return $this->model->whereDate('tanggal', $date)->latest('tanggal')->latest('tanggal')->with('distributor')->get();
    }

    public function getReportMonthly(int $month, int $year, $user = null): Object
    {
        return $this->model->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->selectRaw('sum(total) as total, namaDist, DATE(tanggal) as tanggal')->groupBy('namaDist')->groupBy(DB::raw('DATE(tanggal)'))->get();
    }

	public function getReportQty(string $start = null, string $end = null, $distributor = null, $user = null): Object
	{
		return $this->model->when($start, function ($query) use ($start)
		{
			return $query->whereDate('tanggal', '>=', $start);
		})->when($end, function ($query) use ($end)
		{
			return $query->whereDate('tanggal', '<=', $end);
		})->when($distributor, function ($stock) use ($distributor)
		{
			return $stock->where('pembelian.namaDist', $distributor);
		})->when($user, function ($stock) use ($user)
		{
			return $stock->where('idUser', $user);
		})->selectRaw('sum(jumlah) as total, DATE(tanggal) as tanggal, obat, namaDist')
		->join('detail_pembelian', 'detail_pembelian.idPembelian', 'pembelian.idPembelian')
		
		->groupBy('obat')->groupBy('namaDist')->groupBy(DB::raw('DATE(tanggal)'))->latest('tanggal')->get();
	}

	public function getReportQtyDaily(string $date = null, $user = null): Object
	{
		return $this->model->whereDate('tanggal', $date)->selectRaw('sum(jumlah) as total, obat')
		->join('detail_pembelian', 'detail_pembelian.idPembelian', 'pembelian.idPembelian')
		->groupBy('obat')->get();
	}

	public function getReportQtyMonthly(int $month, int $year, $user = null): Object
    {
        return $this->model->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->selectRaw('sum(jumlah) as total, obat')
		->join('detail_pembelian', 'detail_pembelian.idPembelian', 'pembelian.idPembelian')
		->groupBy('obat')->get();
	}

	public function latest($dari = null, $sampai = null, $distributor = null): Object
	{
		return $this->model->when($dari, function ($stock) use ($dari)
		{
			return $stock->where('tanggal', '>=', $dari);
		})->when($sampai, function ($stock) use ($sampai)
		{
			return $stock->where('tanggal', '<=', $sampai);
		})->when($distributor, function ($stock) use ($distributor)
		{
			return $stock->where('namaDist', $distributor);
		})->with(['distributor'])->latest('tanggal')->latest('tanggal')->get();
	}

	public function accumulation(string $start = null, string $end = null): Int
	{
		return $this->model->when($start, function ($query) use ($start)
        {
            return $query->whereDate('tanggal', '>=', $start);
        })->when($end, function ($query) use ($end)
        {
            return $query->whereDate('tanggal', '<=', $end);
        })->sum('total');
	}

}

 ?>