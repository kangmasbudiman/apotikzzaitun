<?php

namespace App\Http\Controllers;

use App\Repositories\StuffRepository;
use App\Services\StuffService;
use App\Http\Requests\Stuff\CreateStuffRequest;
use App\Http\Requests\Stuff\UpdateStuffRequest;
use App\Http\Requests\Stuff\ImportStuffRequest;
use App\Exports\StuffExport;
use App\Imports\StuffImport;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

use PDF;
use DNS1D;

class StuffController extends Controller
{

    protected $stuff;

    public function __construct(StuffService $stuff)
    {
        $this->stuff = $stuff;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(StuffRepository $stuffRepo): View
    {
        $penerbit = $stuffRepo->getPenerbit();
        $tahun = $stuffRepo->getTahun();

        return view('stuff.index', compact('penerbit', 'tahun'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(): View
    {
        $this->authorize('isAdminGudang');

        return view('stuff.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateStuffRequest $request): RedirectResponse
    {
       // dd($request->all());
        $this->stuff->storeData($request->all());


        return redirect('stuff')->with('success', 'Sukses Menambahkan Data Obat');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id): View
    {
        $stuff = $this->stuff->getOne($id);

        return view('stuff.show', compact('stuff'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateStuffRequest $request, $id): JsonResponse
    {
       // dd($request->all());
        $this->stuff->updateData($id, $request->all());

        return response()->json(['success' => 'Sukses Mengedit Data Obat']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id): JsonResponse
    {
        $this->authorize('isAdminGudang');
        $this->stuff->deleteData($id);

        return response()->json(['success' => 'Sukses Menghapus Data Obat']);
    }

    public function destroyBatch(StuffRepository $stuffRepo, Request $request): JsonResponse
    {
        $request->validate(['stuffs' => 'required|array']);

        $stuffRepo->deleteBatch($request->stuffs);

        return response()->json(['success' => 'Sukses Menghapus Data Buku']);
    }

    // Get Datatables
    public function datatables(Request $request): Object
    {
        return $this->stuff->getDatatables($request->penerbit, $request->tahun);
    }

    // Select Data
    public function select(Request $request): Object
    {
        return $this->stuff->selectData($request->barcode);
    }

    public function printall(StuffRepository $stuffRepo, Request $request)
    {
        $request->validate(['suplier' => 'nullable|string', 'tahun' => 'nullable|date_format:Y']);

        $stuffs = $stuffRepo->filter($request->penerbit, $request->tahun);

        $pdf = PDF::loadView('stuff.printall', compact('stuffs'));
        $pdf->setPaper('a4');

        return $pdf->stream();      
    }

    public function barcode($stuff): View
    {
        $stuff = $this->stuff->getOne($stuff);

        $img = DNS1D::getBarcodePNG(substr($stuff->barcode, 0, 13), 'EAN13', 2, 60);
        
        return view('stuff.barcode', compact('stuff', 'img'));
    }

    public function printBarcode($stuff)
    {
        $stuff = $this->stuff->getOne($stuff);

        $pdf = PDF::loadView('stuff.printbarcode', compact('stuff'));
        $pdf->setPaper('a6');
        
        return $pdf->stream();
    }

    public function printAllBarcode(StuffRepository $stuffRepo, Request $request)
    {
        $request->validate(['show' => 'nullable|integer']);

        $barcodes = $stuffRepo->getTake($request->show ?? 10);
        $pdf = PDF::loadView('stuff.allbarcode', compact('barcodes'));
        $pdf->setPaper('a4');
        
        return $pdf->stream();
    }

    public function export(StuffExport $export)
    {
        return $export->download('buku.xlsx');
    }

    public function import(StuffImport $import, ImportStuffRequest $request)
    {
        $import->import($request->file);

        $failures = count($import->failures());
        $errors = count($import->errors());

        $res = $import->success.' import berhasil, '.$failures.' error '.$errors.' gagal';

        return response()->json(['success' => $res]);
    }
}
