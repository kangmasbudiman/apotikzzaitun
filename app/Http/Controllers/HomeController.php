<?php

namespace App\Http\Controllers;

use App\Services\TransactionService;
use App\Services\StockService;
use App\Services\ExpendService;

use App\Repositories\StuffRepository;

use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Models\Stock;
use Carbon\Carbon;


class HomeController extends Controller
{

	public function index(Request $request): View
	{
		$data = $this->data();
		//dd($data);

		return view('home', array_merge($data));
	}

	public function notification(StuffRepository $stuffRepo): View
	{
		$stuffs = $stuffRepo->getLimit();

		return view('notification', compact('stuffs'));
	}

	public function data(): Array
	{
		$transaction = app(TransactionService::class);
		$stockService = app(StockService::class);
		$expendService = app(ExpendService::class);

		$buyActivity = $stockService->latest(date('Y-m-d'));
		$expendActivity = $expendService->filter(date('Y-m-d'));
		$cancelActivity = $transaction->filterCancel(date('Y-m-d'));
		$transactionActivity = $transaction->filterSuccess(date('Y-m-d'));

		$jatuhtempo=Stock::whereRaw('DATEDIFF(jatuh_tempo,current_date) <= nobatch')->get();
		

		$hitung_jatuhtempo=Stock::whereRaw('DATEDIFF(jatuh_tempo,current_date) <= nobatch')->count();
		
		
		
		



		return [
			'buyActivity' => $buyActivity,
			'expendActivity' => $expendActivity,
			'cancelActivity' => $cancelActivity,
			'transactionActivity' => $transactionActivity,
			'mantap' => $jatuhtempo,
			'totaljatuhtempo' => $hitung_jatuhtempo,
		];
	}

}
