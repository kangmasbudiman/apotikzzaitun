<?php 

namespace App\Services;

use App\Models\DetailStock;

use Yajra\Datatables\Datatables;

class DetailStockService extends Service
{

    public function filter($dari = null, $sampai = null, $dist = null): Object
    {
        return DetailStock::when($dari, function ($stock) use ($dari)
        {
            return $stock->whereHas('stock', function ($query) use ($dari)
            {
                $query->where('tanggal', '>=', $dari);
            });
        })->when($sampai, function ($stock) use ($sampai)
        {
            return $stock->whereHas('stock', function ($query) use ($sampai)
            {
                $query->where('tanggal', '<=', date('Y-m-d', strtotime('+1 day', strtotime($sampai))));
            });
        })->when($dist, function ($stock) use ($dist)
        {
            return $stock->whereHas('stock', function ($stock) use ($dist)
            {
                return $stock->where('namaDist', $dist);
            });
        })->with(['stock', 'stuff'])->latest('idDetailPembelian')->get();
    }

    public function datatables($dari = null, $sampai = null, $dist = null): Object
    {
        $datatables = Datatables::of($this->filter($dari, $sampai, $dist))
                        ->addIndexColumn()
                        ->editColumn('idDetail', function ($stock)
                        {
                            return substr($stock->idPembelian, 0, 5);
                        })
                        ->editColumn('total', function ($stock)
                        {
                            return 'Rp '.number_format($stock->jumlah * $stock->hargaPokok);
                        })
                        ->addColumn('date', function ($stock)
                        {
                            return timeDate($stock->stock->tanggal);
                        })
                      
                        ->make();

        return $datatables;
    }

}

 ?>