<?php

namespace App\Http\Controllers;

use App\Services\{StockService, DetailStockService};
use App\Repositories\StockRepository;
use App\Exports\{StockExport, DetailStockExport};
use App\Imports\{StockImport, DetailStockImport};

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use App\Http\Requests\Stock\UpdateStockRequest;
use App\Http\Requests\Stock\ImportStockRequest;

use PDF;

class StockController extends Controller
{

	protected $stock;

	public function __construct(StockService $stock)
	{
		$this->stock = $stock;
	}

	public function index(StockRepository $stockRepo): View
	{
		$distributor = $stockRepo->getDist();

		return view('stock.index', compact('distributor'));
	}

	public function detailStock(StockRepository $stockRepo): View
	{
		$distributor = $stockRepo->getDist();

		return view('stock.detail_stock', compact('distributor'));
	}

	public function detail(StockRepository $stockRepo, $id): View
	{
		$stock = $stockRepo->find($id);
		
		return view('stock.detail', compact('stock'));
	}

	public function print(StockRepository $stockRepo, $id)
	{
		$stock = $stockRepo->find($id);

		$pdf = PDF::loadView('stock.print', compact('stock'));
		$pdf->setPaper('a4');

		return $pdf->stream();		
	}

	public function destroy($id): JsonResponse
	{
		$this->stock->deleteStock($id);

		return response()->json(['success' => 'Sukses Menghapus Stok']);
	}

	public function update(UpdateStockRequest $request, $id): JsonResponse
	{
		$this->stock->updateStock($id, $request->jumlah);

		return response()->json(['success' => 'Sukses Mengupdate Stok']);
	}

	public function printall(Request $request)
	{
		$request->validate([
			'dari' => 'date|nullable',
			'sampai' => 'date|nullable',
			'distributor' => 'nullable|exists:pembelian,namaDist'
		]);

		$stocks = $this->stock->filter($request->dari, $request->sampai, $request->distributor);

		$pdf = PDF::loadView('stock.printall', compact('stocks'));
		$pdf->setPaper('a4');

		return $pdf->stream();
	}

	public function detailPrintall(DetailStockService $detailStock, Request $request)
	{
		$request->validate([
			'dari' => 'date|nullable',
			'sampai' => 'date|nullable',
			'distributor' => 'nullable|exists:pembelian,namaDist'
		]);

		$stocks = $detailStock->filter($request->dari, $request->sampai, $request->distributor);

		$pdf = PDF::loadView('stock.detail_stock_printall', compact('stocks'));
		$pdf->setPaper('a4');

		return $pdf->stream();
	}

	public function datatables(Request $request): Object
	{

		
		$dari = $request->dari;
		$sampai = $request->sampai;
		$distributor = $request->distributor;

		return $this->stock->getDatatables($dari, $sampai, $distributor);
	}

	public function detailDatatables(DetailStockService $detailStock, Request $request): Object
	{
		$dari = $request->dari;
		$sampai = $request->sampai;
		$dist = $request->distributor;

		return $detailStock->datatables($dari, $sampai, $dist);
	}

    public function export(StockExport $export)
    {
        return $export->download('stok.xlsx');
    }

    public function detailExport(DetailStockExport $export)
    {
        return $export->download('detail_stok.xlsx');
    }

    public function import(StockImport $import, ImportStockRequest $request)
    {
        $import->import($request->file);

        $failures = count($import->failures());
        $errors = count($import->errors());

        $res = $import->success.' import berhasil, '.$failures.' error '.$errors.' gagal';

        return response()->json(['success' => $res]);
    }

    public function detailImport(DetailStockImport $import, ImportStockRequest $request)
    {
        $import->import($request->file);

        $failures = count($import->failures());
        $errors = count($import->errors());

        $res = $import->success.' import berhasil, '.$failures.' error '.$errors.' gagal';

        return response()->json(['success' => $res]);
    }
}
