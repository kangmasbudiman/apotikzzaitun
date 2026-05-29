<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

use App\Services\RackService;
use App\Http\Requests\Rack\CreateRackRequest;
use App\Http\Requests\Rack\UpdateRackRequest;
use App\Http\Requests\Rack\ImportRackRequest;
use App\Exports\RackExport;
use App\Imports\RackImport;

class RackController extends Controller
{

    protected $rack;

    public function __construct(RackService $rack)
    {
        $this->rack = $rack;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): View
    {
        return view('rack.index');
    }

    public function create(): View
    {
        return view('rack.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateRackRequest $request): JsonResponse
    {
        $this->rack->storeData($request->all());

        return response()->json(['success' => 'Sukses Menambahkan Data Rak']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRackRequest $request, $id): JsonResponse
    {
        $this->rack->updateData($id, $request->all());

        return response()->json(['success' => 'Sukses Mengedit Data Rak']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id): JsonResponse
    {
        $this->rack->deleteData($id);

        return response()->json(['success' => 'Sukses Menghapus Data Rak']);
    }

    // Get Datatables
    public function datatables(): Object
    {
        return $this->rack->getDatatables();
    }

    // Select Data
    public function select(Request $request): Object
    {
        return $this->rack->selectData($request->name);
    }

    public function export(RackExport $export)
    {
        return $export->download('rak.xlsx');
    }

    public function import(RackImport $import, ImportRackRequest $request)
    {
        $import->import($request->file);

        $failures = count($import->failures());
        $errors = count($import->errors());

        $res = $import->success.' import berhasil, '.$failures.' error '.$errors.' gagal';

        return response()->json(['success' => $res]);
    }
}
