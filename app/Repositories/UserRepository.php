<?php 

namespace App\Repositories;

use App\Models\User;

class UserRepository extends Repository {

	public $model;

	public function __construct(User $user)
	{
		$this->model = $user;
	}

	public function get(): Object
	{
		return $this->model->get(['idUser', 'username', 'nama', 'alamat', 'telepon', 'hakAkses']);
	}

	public function getKasir(): Object
	{
		return $this->model->where('hakAkses', 2)->get();
	}

	public function select($name): Object
	{
		return $this->model->where('hakAkses', '!=', 1)->where('nama', 'like', '%'.$name.'%')->get(['idUser as id', 'nama as text']);
	}

}

 ?>