<?php 

namespace App\Services;

use App\Repositories\DistributorRepository;

use Yajra\Datatables\Datatables;

class DistributorService extends Service {

	public function __construct(DistributorRepository $distributor)
	{
		$this->repo = $distributor;
	}

	public function getDatatables(): Object
	{
		$datatables = Datatables::of($this->getData())
						->addIndexColumn()
						->addColumn('action', function ()
            {
                $editBtn = '<button class="btn btn-success btn-sm" data-action="edit"><i class="fa fa-edit"></i></button>';
                $delBtn = '<button class="btn btn-danger btn-sm" data-action="remove"><i class="fa fa-trash"></i></button>';

                if (auth()->user()->can('isAdmin')) {
                    return $editBtn.' '.$delBtn;
                }

                return $editBtn;
            })
						->make();

		return $datatables;
	}

}

 ?>