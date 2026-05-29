<?php 

namespace App\Services;

use App\Repositories\ExpendRepository;

use Yajra\Datatables\Datatables;

class ExpendService extends Service {

    public function __construct(ExpendRepository $expend)
    {
        $this->repo = $expend;
    }

    public function filter($dari = null, $sampai = null, $category = null): Object
    {
        
        return $this->repo->filter($dari, $sampai, $category);
    }

    public function getDatatables($dari = null, $sampai = null, $category = null): Object
    {
        $datatables = Datatables::of($this->filter($dari, $sampai, $category))
                        ->addIndexColumn()
                        ->editColumn('tanggal', function ($expend)
                        {
                            return timeDate($expend->tanggal);
                        })
                        ->editColumn('pengeluaran', function ($expend)
                        {
                            return $expend->pengeluaranConverted;
                        })
                        ->addColumn('action', function ($expend)
                        {
                            $isStock = $expend->idPembelian;
                            $isOpname = $expend->idOpname;
                            $isPPN = $expend->namaKategori === 'Bayar Pajak';

                            $forbidden = $isStock || $isOpname || $isPPN;

                            $editBtn = '<button class="btn btn-success btn-sm" data-action="edit"><i class="fa fa-edit"></i></button>';
                            $delBtn = '<button class="btn btn-danger btn-sm" data-action="remove"><i class="fa fa-trash"></i></button>';

                            $disabled = '<button class="btn btn-success btn-sm" data-action="edit" disabled><i class="fa fa-edit"></i></button> <button class="btn btn-danger btn-sm" data-action="remove" disabled><i class="fa fa-trash"></i></button>';

                            return $forbidden ? $disabled : (auth()->user()->can('isAdmin') ? $editBtn.' '.$delBtn : $editBtn);
                        })
                        ->make();

        return $datatables;
    }

}

 ?>