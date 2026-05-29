<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF;

use App\Models\PPN;
use App\Repositories\TransactionRepository;
use App\Services\PPNService;
use App\Exports\PPNExport;
use App\Imports\PPNImport;
use App\Http\Requests\CreatePPNRequest;
use App\Http\Requests\Rack\ImportRackRequest;

class PpnController extends Controller
{

    public function index(PPNService $ppnService)
    {
        $ppn = $ppnService->count();

        return view('ppn.index', ['ppn' => number_format($ppn)]);
    }

    public function store(PPNService $ppnService, CreatePPNRequest $request)
    {
        $ppnService->store($request->all());

        return response()->json(['success' => 'Sukses Menambahkan Data']);
    }

    public function destroy(PPN $ppn)
    {
        $ppn->delete();

        return response()->json(['success' => 'Sukses Menghapus Data']);
    }

    public function export(PPNExport $export)
    {
        return $export->download('ppn.xlsx');
    }

    public function import(PPNImport $import, ImportRackRequest $request)
    {
        $import->import($request->file);

        $failures = count($import->failures());
        $errors = count($import->errors());

        $res = $import->success.' import berhasil, '.$failures.' error '.$errors.' gagal';

        return response()->json(['success' => $res]);
    }

    public function print(PPNService $ppnService, Request $request)
    {
        $request->validate([
            'dari' => 'nullable|date',
            'sampai' => 'nullable|date',
            'jenis' => 'nullable|in:PPN Dikeluarkan,PPN Disetorkan'
        ]);

        $data = [];
        $jenis = $request->filled('jenis');

        if ($jenis) {
            $data = $ppnService->report($request->dari, $request->sampai, $request->jenis);
        } else {
            $data = [
                'ppn-in' => $ppnService->report($request->dari, $request->sampai, 'PPN Dikeluarkan'),
                'ppn-out' => $ppnService->report($request->dari, $request->sampai, 'PPN Disetorkan')
            ];
        }

        $pdf = PDF::loadView('ppn.print', compact('data', 'jenis'));
        $pdf->setPaper('a4');

        return $pdf->stream();
    }

    public function datatables(PPNService $ppnService, Request $request)
    {
        return $ppnService->getDatatables($request->dari, $request->sampai, $request->jenis);
    }

    public function count(PPNService $ppnService)
    {
        return response()->json($ppnService->count());
    }

}
