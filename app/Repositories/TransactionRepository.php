<?php 

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\Models\{Transaction, DetailTransaction};

class TransactionRepository extends Repository {

	public function __construct(Transaction $transaction)
	{
		$this->model = $transaction;
	}

	public function find($id): Object
	{
		return $this->model->with(['user', 'detail'])->findOrFail($id);
	}

	public function countPPN(): Int
	{
		return $this->model->sum('ppn');
	}

	public function countToday(): Int
	{
		$count = $this->model->whereDate('tanggal', today())->count();

		return $count;
	}

	public function countSelledBookPerDate($date): Int
	{
		return $this->model->whereStatus(1)->whereDate('tanggal', $date)->selectRaw('SUM(jumlah) as total')
		->join(DB::raw('(SELECT SUM(jumlah) as jumlah, detail_penjualan.idPenjualan FROM detail_penjualan GROUP BY detail_penjualan.idPenjualan) detail_penjualan'), function ($join)
		{
			$join->on('penjualan.idPenjualan', '=', 'detail_penjualan.idPenjualan');
		})->first()->total ?? 0;
	}

	public function countSelledBookPerMonth(int $month, int $year): Int
	{
		return $this->model->whereStatus(1)->whereMonth('tanggal', $month)
			->whereYear('tanggal', $year)->selectRaw('SUM(jumlah) as total')
		->join(DB::raw('(SELECT SUM(jumlah) as jumlah, detail_penjualan.idPenjualan FROM detail_penjualan GROUP BY detail_penjualan.idPenjualan) detail_penjualan'), function ($join)
		{
			$join->on('penjualan.idPenjualan', '=', 'detail_penjualan.idPenjualan');
		})->first()->total ?? 0;
	}

	public function countSelledBookPerYear(int $year): Int
	{
		return $this->model->whereStatus(1)->whereYear('tanggal', $year)->selectRaw('SUM(jumlah) as total')
		->join(DB::raw('(SELECT SUM(jumlah) as jumlah, detail_penjualan.idPenjualan FROM detail_penjualan GROUP BY detail_penjualan.idPenjualan) detail_penjualan'), function ($join)
		{
			$join->on('penjualan.idPenjualan', '=', 'detail_penjualan.idPenjualan');
		})->first()->total ?? 0;
	}

	public function countSelledBookPerRange(string $start, string $end): Int
	{
		return $this->model->whereStatus(1)->whereDate('tanggal', '>=', $start)->whereDate('tanggal', '<=', $end)->selectRaw('SUM(jumlah) as total')
		->join(DB::raw('(SELECT SUM(jumlah) as jumlah, detail_penjualan.idPenjualan FROM detail_penjualan GROUP BY detail_penjualan.idPenjualan) detail_penjualan'), function ($join)
		{
			$join->on('penjualan.idPenjualan', '=', 'detail_penjualan.idPenjualan');
		})->first()->total ?? 0;
	}

	public function countPerDate($date): Int
	{
		return $this->model->whereDate('tanggal', $date)->whereStatus(1)->count();
	}

	public function countPerMonth(int $month, int $year): Int
	{
		return $this->model->whereMonth('tanggal', $month)
			->whereYear('tanggal', $year)->whereStatus(1)->count();
	}

	public function countPerYear(int $year): Int
	{
		return $this->model->whereYear('tanggal', $year)->whereStatus(1)->count();
	}

	public function countPerRange(string $start, string $end): Int
	{
		return $this->model->whereDate('tanggal', '>=', $start)->whereDate('tanggal', '<=', $end)
			->whereStatus(1)->count();
	}

	public function countCancelPerDate($date): Int
	{
		return $this->model->whereDate('tanggal', $date)->whereStatus(0)->count();
	}

	public function countCancelPerMonth(int $month, int $year): Int
	{
		return $this->model->whereMonth('tanggal', $month)
			->whereYear('tanggal', $year)->whereStatus(0)->count();
	}

	public function countCancelPerYear(int $year): Int
	{
		return $this->model->whereYear('tanggal', $year)->whereStatus(0)->count();
	}

	public function countCancelPerRange(string $start, string $end): Int
	{
		return $this->model->whereDate('tanggal', '>=', $start)->whereDate('tanggal', '<=', $end)
			->whereStatus(0)->count();
	}

	public function sumIncomePerDate($date): Int
	{
		return $this->model->whereStatus(1)->whereDate('tanggal', $date)->sum('total');
	}

	public function sumIncomePerMonth(int $month, int $year): Int
	{
		return $this->model->whereStatus(1)->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->sum('total');
	}

	public function sumIncomePerYear(int $year): Int
	{
		return $this->model->whereStatus(1)->whereYear('tanggal', $year)->sum('total');
	}

	public function sumIncomePerRange(string $start, string $end): Int
	{
		return $this->model->whereStatus(1)->whereDate('tanggal', '>=', $start)->whereDate('tanggal', '<=', $end)->sum('total');
	}

	public function get(): Object
	{
		return $this->model->with(['user'])->latest('tanggal')->get();
	}

	public function filterByKasir($dari = null, $sampai = null, $kasir): Object
	{
		return $this->model->where('idUser', $kasir)->when($dari, function ($transaction) use ($dari)
		{
			return $transaction->where('tanggal', '>=', $dari);
		})->when($sampai, function ($transaction) use ($sampai)
		{
			return $transaction->where('tanggal', '<=', date('Y-m-d', strtotime('+1 day', strtotime($sampai))));
		})->with(['user'])->latest('idPenjualan')->get();
	}

	public function filter($dari = null, $sampai = null, $user = null): Object
	{
		return $this->model->when($dari, function ($transaction) use ($dari)
		{
			return $transaction->where('tanggal', '>=', $dari);
		})->when($sampai, function ($transaction) use ($sampai)
		{
			return $transaction->where('tanggal', '<=', date('Y-m-d', strtotime('+1 day', strtotime($sampai))));
		})->when($user, function ($transaction) use ($user)
		{
			return $transaction->where('idUser', $user);
		})->with(['user'])->latest('idPenjualan')->get();
	}

	public function filterSuccess($dari = null, $sampai = null, $user = null): Object
	{
		return $this->model->when($dari, function ($transaction) use ($dari)
		{
			return $transaction->where('tanggal', '>=', $dari);
		})->when($sampai, function ($transaction) use ($sampai)
		{
			return $transaction->where('tanggal', '<=', date('Y-m-d', strtotime('+1 day', strtotime($sampai))));
		})->when($user, function ($transaction) use ($user)
		{
			return $transaction->where('idUser', $user);
		})->whereStatus(1)->with(['user'])->latest('tanggal')->get();
	}

	public function filterCancel($dari = null, $sampai = null, $user = null): Object
	{
		return $this->model->when($dari, function ($transaction) use ($dari)
		{
			return $transaction->where('tanggal', '>=', $dari);
		})->when($sampai, function ($transaction) use ($sampai)
		{
			return $transaction->where('tanggal', '<=', date('Y-m-d', strtotime('+1 day', strtotime($sampai))));
		})->when($user, function ($transaction) use ($user)
		{
			return $transaction->where('idUser', $user);
		})->whereStatus(0)->with(['user'])->latest('tanggal')->get();
	}

	public function getReport(string $start = null, string $end = null, $user = null): Object
	{
		return $this->model->whereStatus(1)->when($start, function ($query) use ($start)
		{
			return $query->whereDate('tanggal', '>=', $start);
		})->when($end, function ($query) use ($end)
		{
			return $query->whereDate('tanggal', '<=', $end);
		})->when($user, function ($transaction) use ($user)
		{
			return $transaction->where('idUser', $user);
		})->selectRaw('sum(total) as total, DATE(tanggal) as tanggal')->groupBy(DB::raw('DATE(tanggal)'))->get();
	}

	public function getReportDaily(string $date = null, $user = null): Object
	{
		return $this->model->whereStatus(1)->whereDate('tanggal', $date)->latest('tanggal')->get();
	}

	public function getReportMonthly(int $month = null, int $year = null, $user = null): Object
	{
		return $this->model->whereStatus(1)->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->selectRaw('sum(total) as total, DATE(tanggal) as tanggal')->groupBy(DB::raw('DATE(tanggal)'))->get();
	}

	public function getReportQty(string $start = null, string $end = null, $user = null): Object
	{
		return $this->model->where('penjualan.status', 1)->when($start, function ($query) use ($start)
		{
			return $query->whereDate('tanggal', '>=', $start);
		})->when($end, function ($query) use ($end)
		{
			return $query->whereDate('tanggal', '<=', $end);
		})->when($user, function ($transaction) use ($user)
		{
			return $transaction->where('idUser', $user);
		})->selectRaw('sum(jumlah) as total, obat, DATE(tanggal) as tanggal')
		->join('detail_penjualan', 'detail_penjualan.idPenjualan', 'penjualan.idPenjualan')
		->groupBy(DB::raw('DATE(tanggal)'))->groupBy('obat')->latest('tanggal')->get();
	}

	public function getReportQtyDaily(string $date = null, $user = null): Object
	{
		return $this->model->where('penjualan.status', 1)->whereDate('tanggal', $date)->selectRaw('sum(jumlah) as total, obat')
		->join('detail_penjualan', 'detail_penjualan.idPenjualan', 'penjualan.idPenjualan')
		->groupBy('obat')->get();
	}

	public function getReportQtyMonthly(int $month = null, int $year = null, $user = null): Object
	{
		return $this->model->where('penjualan.status', 1)->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->selectRaw('sum(jumlah) as total, obat')
		->join('detail_penjualan', 'detail_penjualan.idPenjualan', 'penjualan.idPenjualan')
		->groupBy('obat')->get();
	}

	public function accumulation(string $start = null, string $end = null): Object
	{
		return $this->model->when($start, function ($query) use ($start)
		{
			return $query->whereDate('penjualan.tanggal', '>=', $start);
		})->when($end, function ($query) use ($end)
		{
			return $query->whereDate('penjualan.tanggal', '<=', $end);
		})->whereStatus(1)->selectRaw('SUM(hargaJual) as hargaJual, SUM(hargaPokok) as hargaPokok, SUM(disc) as disc, SUM(ppn) as ppn')
		->join(DB::raw('(SELECT SUM(hargaJual * jumlah) as hargaJual, SUM(hargaPokok * jumlah) as hargaPokok, SUM(detail_penjualan.disc) as disc, detail_penjualan.idPenjualan FROM detail_penjualan GROUP BY detail_penjualan.idPenjualan) detail_penjualan'), function ($join)
		{
			$join->on('penjualan.idPenjualan', '=', 'detail_penjualan.idPenjualan');
		})
		->first();
	}

}

 ?>