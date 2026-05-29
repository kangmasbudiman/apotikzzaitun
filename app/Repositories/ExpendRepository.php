<?php 

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\Models\Expend;

class ExpendRepository extends Repository {

    public function __construct(Expend $expend)
    {
        $this->model = $expend;
    }

    public function filter($dari = null, $sampai = null, $category = null): Object
    {
        return $this->model->when($dari, function ($expend) use ($dari)
        {
            return $expend->where('tanggal', '>=', $dari);
            

        })->when($sampai, function ($expend) use ($sampai)
        {
            return $expend->where('tanggal', '<=', date('Y-m-d', strtotime('+1 day', strtotime($sampai))));
        })->when($category, function ($expend) use ($category)
        {
           // return $expend->where('namaKategori', $category);
        })
        ->latest('tanggal')->with('category')->get();
    }

    public function getCategories(): Object
    {
        return $this->model->groupBy('namaKategori')->get(['namaKategori']);
    }

    public function sumExpendPerDate($date): Int
    {
        return $this->model->whereDate('tanggal', $date)->sum('pengeluaran');
    }

    public function sumExpendPerMonth(int $month, int $year): Int
    {
        return $this->model->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->sum('pengeluaran');
    }

    public function sumExpendPerYear(int $year): Int
    {
        return $this->model->whereYear('tanggal', $year)->sum('pengeluaran');
    }

    public function sumExpendPerRange(string $start, string $end): Int
    {
        return $this->model->whereDate('tanggal', '>=', $start)->whereDate('tanggal', '<=', $end)->sum('pengeluaran');
    }

    public function getReport(string $start = null, string $end = null): Object
    {
        return $this->model->when($start, function ($query) use ($start)
        {
            return $query->whereDate('tanggal', '>=', $start);
        })->when($end, function ($query) use ($end)
        {
            return $query->whereDate('tanggal', '<=', $end);
        })->selectRaw('sum(pengeluaran) as total, DATE(tanggal) as tanggal')->groupBy(DB::raw('DATE(tanggal)'))->get();
    }

    // public function accumulation(string $start = null, string $end = null): Object
    // {
    //     return $this->model->when($start, function ($query) use ($start)
    //     {
    //         return $query->whereDate('pengeluaran.tanggal', '>=', $start);
    //     })->when($end, function ($query) use ($end)
    //     {
    //         return $query->whereDate('pengeluaran.tanggal', '<=', $end);
    //     })->selectRaw('sum(pengeluaran) as pengeluaran, pemasukan, pengeluaran.tanggal')
    //     ->leftJoin(DB::raw('(select sum(total) as pemasukan, tanggal from `penjualan` group by `tanggal`) penjualan'), function ($join)
    //     {
    //         $join->on('pengeluaran.tanggal', '=', 'penjualan.tanggal');
    //     })
    //     ->groupBy('pengeluaran.tanggal')->get();
    // }

    public function accumulationOne(string $start = null, string $end = null): Int
    {
        return $this->model->where('namaKategori', '!=', 'Pembelian Buku')->when($start, function ($query) use ($start)
        {
            return $query->whereDate('tanggal', '>=', $start);
        })->when($end, function ($query) use ($end)
        {
            return $query->whereDate('tanggal', '<=', $end);
        })->sum('pengeluaran');
    }

    public function accumulation(string $start = null, string $end = null): Int
    {
        return $this->model->when($start, function ($query) use ($start)
        {
            return $query->whereDate('tanggal', '>=', $start);
        })->when($end, function ($query) use ($end)
        {
            return $query->whereDate('tanggal', '<=', $end);
        })->sum('pengeluaran');
    }

    public function groupAccumulation(string $start = null, string $end = null): Object
    {
        return $this->model->when($start, function ($query) use ($start)
        {
            return $query->whereDate('tanggal', '>=', $start);
        })->when($end, function ($query) use ($end)
        {
            return $query->whereDate('tanggal', '<=', $end);
        })->selectRaw('sum(pengeluaran) as pengeluaran, namaKategori as nama')->groupBy('nama')->orderBy('pengeluaran', 'desc')->get();
    }

    public function groupAccumulationOne(string $start = null, string $end = null): Object
    {
        return $this->model->where('namaKategori', '!=', 'Pembelian Buku')->when($start, function ($query) use ($start)
        {
            return $query->whereDate('tanggal', '>=', $start);
        })->when($end, function ($query) use ($end)
        {
            return $query->whereDate('tanggal', '<=', $end);
        })->selectRaw('sum(pengeluaran) as pengeluaran, namaKategori as nama')->groupBy('nama')->get();
    }

}


 ?>