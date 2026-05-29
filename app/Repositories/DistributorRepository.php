<?php 

namespace App\Repositories;

use App\Models\Distributor;

class DistributorRepository extends Repository {

	public function __construct(Distributor $distributor)
	{
		$this->model = $distributor;
	}

	public function select($name): Object
	{
		return $this->model->where('namaDist', 'like', '%'.$name.'%')->get(['idDist as id', 'namaDist as text', 'alamat', 'telepon']);
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