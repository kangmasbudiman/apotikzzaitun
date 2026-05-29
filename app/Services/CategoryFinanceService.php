<?php 

namespace App\Services;

use App\Models\ExpendCategory as Category;

use Yajra\Datatables\Datatables;

class CategoryFinanceService extends Service
{

    public function getStockCategory(): Object
    {
        return Category::firstOrCreate(['nama' => 'Pembelian Buku']);
    }

    public function getOpnameCategory(): Object
    {
        return Category::firstOrCreate(['nama' => 'StokOpName']);
    }

    public function getPPNCategory(): Object
    {
        return Category::firstOrCreate(['nama' => 'Bayar Pajak']);
    }

    public function selectData($name): Object
    {
        return Category::where('nama', 'like', '%'.$name.'%')->whereNotIn('nama', ['Pembelian Buku', 'StokOpName', 'Bayar Pajak'])->get(['idKategoriPengeluaran as id', 'nama as text']);
    }

    public function getDatatables(): Object
    {
        $datatables = Datatables::of(Category::get())
                        ->addIndexColumn()
                        ->addColumn('action', function ($expend)
                        {
                            $editBtn = '<button class="btn btn-success btn-sm" data-action="edit"><i class="fa fa-edit"></i></button>';
                            $delBtn = '<button class="btn btn-danger btn-sm" data-action="remove"><i class="fa fa-trash"></i></button>';
                            $editDisabledBtn = '<button class="btn btn-success btn-sm" data-action="edit" disabled><i class="fa fa-edit"></i></button>';
                            $delDisabledBtn = '<button class="btn btn-danger btn-sm" data-action="remove" disabled><i class="fa fa-trash"></i></button>';

                            return $expend->nama === 'Pembelian Buku' || $expend->nama === 'StokOpName' || $expend->nama === 'Gaji Karyawan' || $expend->nama === 'Bayar Pajak' ? $editDisabledBtn.' '.$delDisabledBtn : $editBtn.' '.$delBtn;
                        })
                        ->make();

        return $datatables;
    }

}

 ?>