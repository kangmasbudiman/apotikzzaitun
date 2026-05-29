<?php

namespace App\Http\Controllers;

use App\Services\DistributorService;
use App\Http\Requests\Distributor\CreateDistributorRequest;
use App\Http\Requests\Distributor\UpdateDistributorRequest;
use App\Http\Requests\Rack\ImportRackRequest;
use App\Exports\DistributorExport;
use App\Imports\DistributorImport;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class DistributorController extends Controller
{

    protected $distributor;

    public function __construct(DistributorService $distributor)
    {
        $this->distributor = $distributor;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): View
    {
        return view('distributor.index');
    }

    public function create(): View
    {
        return view('distributor.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateDistributorRequest $request): RedirectResponse
    {
        $this->distributor->storeData($request->all());

        return redirect('distributor')->withSuccess('Sukses Menambahkan Data Distributor');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDistributorRequest $request, $id): JsonResponse
    {
        $this->distributor->updateData($id, $request->all());

        return response()->json(['success' => 'Sukses Mengedit Data Distributor']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id): JsonResponse
    {
        $this->distributor->deleteData($id);

        return response()->json(['success' => 'Sukses Menghapus Data Distributor']);
    }

    // Get Datatables
    public function datatables(): Object
    {
        return $this->distributor->getDatatables();
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
