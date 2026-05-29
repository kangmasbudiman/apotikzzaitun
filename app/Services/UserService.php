<?php 

namespace App\Services;

use App\Repositories\UserRepository;

use Yajra\Datatables\Datatables;

use Auth;

class UserService {

	protected $model;

	public function __construct(UserRepository $user)
	{
		return $this->model = $user;
	}

	public function storeData(object $data): Object
	{
		return $this->model->create($data->all());
	}

	public function updateData($id, object $data): Object
	{
		return $this->model->update($id, $data->all());
	}

	public function deleteData($id): Object
	{
		return $this->model->delete($id);
	}

	public function countData(): Int
	{
		return $this->model->count();
	}

	public function uploadPhoto(object $file): String
	{
		$filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
		$filename = $filename.'_'.time().'.'.$file->extension();

		$file->storeAs('public/images', $filename);

		return $filename;
	}

	public function updatePassword(string $password): Object
	{
		$user = Auth::user();
		$user->password = $password;
		$user->save();

		return $user;
	}

	public function getDatatables(): Object
	{
		$data = Datatables::of($this->model->get())
							->addIndexColumn()
							->addColumn('action', '
								<button class="btn btn-sm btn-success" data-action="edit"><i class="fa fa-edit"></i></button>
								<button class="btn btn-sm btn-danger" data-action="remove"><i class="fa fa-trash"></i></button>
							')
							->rawColumns(['photoSrc', 'action'])
							->make();

		return $data;
	}

}

 ?>