<?php

namespace App\Http\Controllers;

use App\Services\CashService;
use App\Http\Requests\Cash\CreateCashRequest;
use App\Http\Requests\Cash\UpdateCashRequest;
use App\Http\Requests\Cash\ImportCashRequest;
use App\Exports\DistributorExport;
use App\Imports\DistributorImport;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class DaylicashController extends Controller
{

    protected $cash;

    public function __construct(CashService $cash)
    {
        $this->cash = $cash;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): View
    {
        return view('cash.index');
    }

    public function create(): View
    {
        return view('cash.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateCashRequest $request): RedirectResponse
    {
        $this->cash->storeData($request->all());

        return redirect('daylicash')->withSuccess('Sukses Menambahkan Data Kas Harian');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCashRequest $request, $id): JsonResponse
    {
        $this->cash->updateData($id, $request->all());

        return response()->json(['success' => 'Sukses Mengedit Data Kas Harian']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id): JsonResponse
    {
        
        $this->cash->deleteData($id);
        

        return response()->json(['success' => 'Sukses Menghapus Data Kas Harian']);
    }

    // Get Datatables
    public function datatables(): Object
    {
        return $this->cash->getDatatables();
    }

    // Select Data
    public function select(Request $request): Object
    {
        return $this->distributor->selectData($request->name);
    }

    public function export(DistributorExport $export)
    {
        return $export->download('distributor.xlsx');
    }

    public function import(DistributorImport $import, ImportRackRequest $request)
    {
        $import->import($request->file);

        $failures = count($import->failures());
        $errors = count($import->errors());

        $res = $import->success.' import berhasil, '.$failures.' error '.$errors.' gagal';

        return response()->json(['success' => $res]);
    }
}
