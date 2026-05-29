<?php 

namespace App\Services;

use Illuminate\Support\Facades\DB;

use App\Models\{PPN, Expend};

use Yajra\Datatables\Datatables;

class PPNService extends Service {

    public function store(array $data)
    {
        $ppn = PPN::create($data);

        $this->createExpend($ppn);
    }

    public function createExpend(PPN $ppn)
    {
        $category = app(CategoryFinanceService::class);
        $ppnCategory = $category->getPPNCategory();

        $expend = Expend::create([
            'idKategoriPengeluaran' => $ppnCategory->idKategoriPengeluaran,
            'pengeluaran' => $ppn->nominal,
            'tanggal' => date('Y-m-d H:i:s'),
            'namaKategori' => $ppnCategory->nama,
            'keterangan' => $ppn->keterangan
        ]);

        $ppn->expend()->save($expend);
    }

    public function count(): Int
    {
        $ppnKeluar = PPN::whereJenis('PPN Dikeluarkan')->sum('nominal');
        $ppnSetor = PPN::whereJenis('PPN Disetorkan')->sum('nominal');

        return $ppnKeluar - $ppnSetor;
    }

    public function report($dari = null, $sampai = null, $jenis = null): Object
    {
        return PPN::when($dari, function ($ppn) use ($dari)
        {
            return $ppn->where('tanggal', '>=', $dari);
        })->when($sampai, function ($ppn) use ($sampai)
        {
            return $ppn->where('tanggal', '<=', $sampai);
        })->when($jenis, function ($ppn) use ($jenis)
        {
            return $ppn->where('jenis', $jenis);
        })->latest('tanggal')->with('user')->get();
    }

    public function getDatatables($dari, $sampai, $jenis): Object
    {
        $datatables = Datatables::of($this->report($dari, $sampai, $jenis))
                        ->addIndexColumn()
                        ->editColumn('nominal', function ($ppn)
                        {
                            return number_format($ppn->nominal);
                        })
                        ->editColumn('tanggal', function ($ppn)
                        {
                            return date('d M Y H:i A', strtotime($ppn->tanggal));
                        })
                        ->addColumn('action', function ($opname)
                        {
                            $delBtn = '<button class="btn btn-danger btn-sm" data-action="remove"><i class="fa fa-trash"></i></button>';
                            $delBtnDisabled = '<button class="btn btn-danger btn-sm" data-action="remove" disabled><i class="fa fa-trash"></i></button>';

                            return $opname->jenis === 'PPN Dikeluarkan' ? $delBtnDisabled : $delBtn;
                        })
                        ->make();

        return $datatables;
    }

}

 ?>