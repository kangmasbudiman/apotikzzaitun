<?php 

namespace App\Services;

use App\Models\{Opname, Expend, Stuff};
use App\Repositories\StuffRepository;

use Yajra\Datatables\Datatables;

class OpnameService extends Service {

    public function report($dari, $sampai): Object
    {
        return Opname::when($dari, function ($opname) use ($dari)
        {
            return $opname->where('tanggal', '>=', $dari);
        })->when($sampai, function ($opname) use ($sampai)
        {
            return $opname->where('tanggal', '<=', $sampai);
        })->latest('tanggal')->get();
    }

    public function store(array $data)
    {
        $stuffRepo = app(StuffRepository::class);

        $buku = $stuffRepo->getByCode($data['barcode']);

        $data = array_merge($data, [
            'idBuku' => $buku->idBuku,
            'obat' => $buku->obat,
            'stokSistem' => $buku->stock,
            'hargaPokok' => $buku->hargaPokok,
        ]);

        $opname = Opname::create($data);

        $this->createRelation($opname, $buku);
        
        return $opname;
    }

    public function createRelation(Opname $opname, Stuff $buku = null)
    {
        $this->updateStuff($buku, $opname->stokNyata);
        $this->createExpend($opname);
    }

    public function update(Opname $opname)
    {
        $this->updateStuff($opname->book, $opname->stokNyata);

        $opname->expend()->update([
            'pengeluaran' => $opname->total,
            'keterangan' => $opname->keterangan
        ]);
    }

    public function delete(Opname $opname)
    {
        $this->updateStuff($opname->book, $opname->stokSistem);

        $opname->delete();
    }

    public function updateStuff(Stuff $stuff, int $stock)
    {
        $stuff->stock = $stock;
        $stuff->save();
    }

    public function createExpend(Opname $opname)
    {
        $category = app(CategoryFinanceService::class);
        $opnameCategory = $category->getOpnameCategory();

        $expend = Expend::create([
            'idKategoriPengeluaran' => $opnameCategory->idKategoriPengeluaran,
            'pengeluaran' => $opname->total,
            'tanggal' => date('Y-m-d H:i:s'),
            'namaKategori' => $opnameCategory->nama,
            'keterangan' => $opname->keterangan
        ]);

        $opname->expend()->save($expend);
    }

    public function getDatatables($dari, $sampai): Object
    {
        $datatables = Datatables::of($this->report($dari, $sampai))
                        ->addIndexColumn()
                        ->addColumn('selisih', function ($opname)
                        {
                            return 0 - $opname->selisih;
                        })
                        ->addColumn('total', function ($opname)
                        {
                            return number_format(0 - $opname->total);
                        })
                        ->addColumn('action', function ($opname)
                        {
                            $editBtn = '<a href="'.route('opname.edit', $opname->idOpname).'" class="btn btn-success btn-sm" data-action="edit"><i class="fa fa-edit"></i></a>';
                            $delBtn = '<button class="btn btn-danger btn-sm" data-action="remove"><i class="fa fa-trash"></i></button>';

                            return $editBtn.' '.$delBtn;
                        })
                        ->editColumn('tanggal', function ($opname)
                        {
                            return date('d M Y H:i A', strtotime($opname->tanggal));
                        })
                        ->make();

        return $datatables;
    }

}

 ?>