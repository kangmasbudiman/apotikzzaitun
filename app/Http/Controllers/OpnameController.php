<?php

namespace App\Http\Controllers;

use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Contracts\View\View;
use PDF;

use App\Models\Opname;
use App\Services\OpnameService;
use App\Exports\OpnameExport;
use App\Imports\OpnameImport;
use App\Http\Requests\Rack\ImportRackRequest;

class OpnameController extends Controller
{

    public function index(): View
    {
        return view('opname.index');
    }

    public function create(): View
    {
        return view('opname.create');
    }

    public function edit($id): View
    {
        return view('opname.edit', ['id' => $id]);
    }

    public function destroy(OpnameService $opnameService, Opname $opname): JsonResponse
    {
        $opnameService->delete($opname);

        return response()->json(['success' => 'Sukses Menghapus data']);
    }

    public function import(OpnameImport $import, ImportRackRequest $request)
    {
        $import->import($request->file);

        $failures = count($import->failures());
        $errors = count($import->errors());

        $res = $import->success.' import berhasil, '.$failures.' error '.$errors.' gagal';

        return response()->json(['success' => $res]);
    }

    public function export(OpnameExport $export)
    {
        return $export->download('opname.xlsx');
    }

    public function print(OpnameService $opnameService, Request $request)
    {
        $request->validate([
            'dari' => 'nullable|date',
            'sampai' => 'nullable|date',
        ]);

        $data = $opnameService->report($request->dari, $request->sampai);

        $pdf = PDF::loadView('opname.print', compact('data'));
        $pdf->setPaper('a4');

        return $pdf->stream();
    }

    public function datatables(OpnameService $service, Request $request): Object
    {
        return $service->getDatatables($request->dari, $request->sampai);
    }

}
