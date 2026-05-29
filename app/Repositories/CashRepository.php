<?php 

namespace App\Repositories;

use App\Models\Cash;

class CashRepository extends Repository {

	public function __construct(Cash $cash)
	{
		$this->model = $cash;
	}

	public function select($name): Object
	{
		return $this->model->where('tanggal', 'like', '%'.$tanggal.'%')->get(['idCash as id', 'tanggal as text', 'total']);
	}

	public function countPerDate($date): Int
	{
		return $this->model->whereDate('created_at', $date)->count();
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

}


 ?>