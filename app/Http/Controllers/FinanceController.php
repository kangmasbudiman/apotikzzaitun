<?php

namespace App\Http\Controllers;

use App\Models\ExpendCategory as Category;
use App\Repositories\ExpendRepository;
use App\Services\ExpendService;
use App\Exports\FinanceExport;
use App\Imports\FinanceImport;
use App\Http\Requests\Expend\CreateExpendRequest;
use App\Http\Requests\Expend\UpdateExpendRequest;
use App\Http\Requests\Stock\ImportStockRequest;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

use PDF;

class FinanceController extends Controller
{

    protected $expend;

    public function __construct(ExpendService $expend)
    {
        $this->expend = $expend;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ExpendRepository $expendRepo): View
    {
        $categories = $expendRepo->getCategories();

        return view('expend.index', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateExpendRequest $request): JsonResponse
    {
        $request->merge([
            'namaKategori' => Category::findOrfail($request->idKategoriPengeluaran)->nama
        ]);

        $this->expend->storeData($request->all());

        return response()->json(['success' => 'Sukses Menambahkan Data']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateExpendRequest $request, $id): JsonResponse
    {
        $request->merge([
            'namaKategori' => Category::findOrfail($request->idKategoriPengeluaran)->nama
        ]);
        
        $this->expend->updateData($id, $request->all());

        return response()->json(['success' => 'Sukses Mengedit Data']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id): JsonResponse
    {
        $this->expend->deleteData($id);

        return response()->json(['success' => 'Sukses Menghapus Data']);
    }

    public function printall(Request $request)
    {
        $request->validate([
            'dari' => 'date|nullable',
            'sampai' => 'date|nullable',
            'category' => 'nullable|exists:pengeluaran,namaKategori'
        ]);

        $expends = $this->expend->filter($request->dari, $request->sampai, $request->category);
        
        $pdf = PDF::loadView('expend.printall', compact('expends'));

        return $pdf->stream();
    }

    // Get Datatables
    public function datatables(Request $request): Object
    {
        $dari = $request->dari;
        $sampai = $request->sampai;
        $category = $request->category;

        return $this->expend->getDatatables($dari, $sampai, $category);
    }

    public function export(FinanceExport $export)
    {
        return $export->download('pengeluaran.xlsx');
    }

    public function import(FinanceImport $import, ImportStockRequest $request)
    {
        $import->import($request->file);

        $failures = count($import->failures());
        $errors = count($import->errors());

        $res = $import->success.' import berhasil, '.$failures.' error '.$errors.' gagal';

        return response()->json(['success' => $res]);
    }
}
