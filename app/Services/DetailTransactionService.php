<?php 

namespace App\Services;

use App\Models\DetailTransaction;

use Yajra\Datatables\Datatables;

class DetailTransactionService extends Service
{

    public function filter($dari = null, $sampai = null, $status = null, $user = null): Object
    {
        return DetailTransaction::when($dari, function ($transaction) use ($dari)
        {
            return $transaction->whereHas('transaction', function ($query) use ($dari)
            {
                $query->where('tanggal', '>=', $dari);
            });
        })->when($sampai, function ($transaction) use ($sampai)
        {
            return $transaction->whereHas('transaction', function ($query) use ($sampai)
            {
                $query->where('tanggal', '<=', date('Y-m-d', strtotime('+1 day', strtotime($sampai))));
            });
        })->when($user, function ($transaction) use ($user)
        {
            return $transaction->whereHas('transaction', function ($query) use ($user)
            {
                $query->where('idUser', $user);
            });
        })->when(!is_null($status), function ($transaction) use ($status)
        {
            return $transaction->whereHas('transaction', function ($query) use ($status)
            {
                $query->where('status', $status);
            });
        })->with(['transaction.user'])->latest('idDetailPenjualan')->get();
    }

    public function datatables($dari = null, $sampai = null, $status = null, $user = null): Object
    {
        $datatables = Datatables::of($this->filter($dari, $sampai, $status, $user))
                        ->addIndexColumn()
                        ->editColumn('idDetail', function ($stock)
                        {
                            return substr($stock->idPenjualan, 0, 21);
                            //return substr($stock->idPenjualan);
                        })
                        ->editColumn('total', function ($transaction)
                        {
                            return 'Rp '.number_format($transaction->jumlah * $transaction->hargaJual - round($transaction->disc) + $transaction->ppn);
                        })
                        ->editColumn('disc', function ($transaction)
                        {
                            return number_format(round($transaction->disc));
                        })
                        ->addColumn('status_badge', function ($transaction)
                        {
                            return $transaction->transaction->status_badge;
                        })
                        ->addColumn('date', function ($transaction)
                        {
                            return localDate($transaction->transaction->tanggal);
                        })
                        ->rawColumns(['status_badge'])
                        ->make();

        return $datatables;
    }

}

 ?>