<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use PDF;

use App\Repositories\{TransactionRepository, ExpendRepository, StockRepository, UserRepository, DistributorRepository};
use App\Services\FinanceService;

class ReportController extends Controller
{

    public function accumulation(FinanceService $finance, ExpendRepository $expendRepo, Request $request)
    {
        $request->validate([
            'dari' => 'nullable|date',
            'sampai' => 'nullable|date'
        ]);

        $reports = [];
        $expend = [];

        if ($request->filled('dari') || $request->filled('sampai')) {
            $reports = $finance->accumulation($request->dari, $request->sampai);
            $expend = $expendRepo->groupAccumulationOne($request->dari, $request->sampai);
        }

        return view('report.accumulation', compact('reports', 'expend'));   
    }

    public function accumulationNew(FinanceService $finance, ExpendRepository $expendRepo, Request $request)
    {
        $request->validate([
            'dari' => 'nullable|date',
            'sampai' => 'nullable|date'
        ]);

        $reports = [];
        $expend = [];

        if ($request->filled('dari') || $request->filled('sampai')) {
            $reports = $finance->accumulation($request->dari, $request->sampai);
            $expend = $expendRepo->groupAccumulation($request->dari, $request->sampai);
        }

        return view('report.accumulation-new', compact('reports', 'expend'));   
    }

    public function transaction(TransactionRepository $transactionRepo, UserRepository $userRepo, Request $request): View
    {
        $request->validate([
            'dari' => 'nullable|date',
            'sampai' => 'nullable|date',
            'user' => 'nullable|exists:user,idUser'
        ]);

        $user = $userRepo->getKasir();
        $reports = [];

        if ($request->filled('dari') || $request->filled('sampai') || $request->filled('user')) {
            $isAdmin = auth()->user()->can('isAdmin');

            $reports = $isAdmin ? $transactionRepo->getReport($request->dari, $request->sampai, $request->user) : $transactionRepo->getReport($request->dari, $request->sampai, auth()->id());
        }

        return view('report.transaction', compact('reports', 'user'));
    }

    public function transactionToday(TransactionRepository $transactionRepo): View
    {
        $isAdmin = auth()->user()->can('isAdmin');

        $reports = $isAdmin ? $transactionRepo->getReportDaily(date('Y-m-d')) : $transactionRepo->getReportDaily(date('Y-m-d'), auth()->id());

        return view('report.transaction-today', compact('reports'));
    }

    public function transactionMonth(TransactionRepository $transactionRepo): View
    {
        $isAdmin = auth()->user()->can('isAdmin');

        $reports = $isAdmin ? $transactionRepo->getReportMonthly(date('m'), date('Y')) : $transactionRepo->getReportMonthly(date('m'), date('Y'), auth()->id());

        return view('report.transaction-month', compact('reports'));
    }

    public function expend(ExpendRepository $expendRepo, Request $request): View
    {
        $request->validate([
            'dari' => 'nullable|date',
            'sampai' => 'nullable|date'
        ]);

        $reports = [];

        if ($request->filled('dari') || $request->filled('sampai')) {
            $reports = $expendRepo->getReport($request->dari, $request->sampai);
        }

        return view('report.expend', compact('reports'));
    }

    public function sell(TransactionRepository $transactionRepo, UserRepository $userRepo, Request $request): View
    {
        $request->validate([
            'dari' => 'nullable|date',
            'sampai' => 'nullable|date',
            'user' => 'nullable|exists:user,idUser'
        ]);

        $user = $userRepo->getKasir();
        $reports = [];

        if ($request->filled('dari') || $request->filled('sampai') || $request->filled('user')) {
            $isAdmin = auth()->user()->can('isAdmin');

            $reports = $isAdmin ? $transactionRepo->getReportQty($request->dari, $request->sampai, $request->user) : $transactionRepo->getReportQty($request->dari, $request->sampai, auth()->id());
        }

        return view('report.sell', compact('reports', 'user'));
    }

    public function sellToday(TransactionRepository $transactionRepo, Request $request): View
    {
        $isAdmin = auth()->user()->can('isAdmin');

        $reports = $isAdmin ? $transactionRepo->getReportQtyDaily(date('Y-m-d')) : $transactionRepo->getReportQtyDaily(date('Y-m-d'), auth()->id());

        return view('report.sell-today', compact('reports'));
    }

    public function sellMonth(TransactionRepository $transactionRepo, Request $request): View
    {
        $isAdmin = auth()->user()->can('isAdmin');

        $reports = $isAdmin ? $transactionRepo->getReportQtyMonthly(date('m'), date('Y')) : $transactionRepo->getReportQtyMonthly(date('m'), date('Y'), auth()->id());

        return view('report.sell-month', compact('reports'));
    }

    public function buy(StockRepository $stockRepo, Request $request): View
    {
        $request->validate([
            'dari' => 'nullable|date',
            'sampai' => 'nullable|date',
            'distributor' => 'nullable|exists:pembelian,namaDist',
        ]);

        $distributor = $stockRepo->getDist();
        $reports = [];

        $isAdmin = auth()->user()->can('isAdmin');
     
        if ($isAdmin) {
            if ($request->filled('dari') || $request->filled('sampai') || $request->filled('distributor')) {
                $reports = $stockRepo->getReport($request->dari, $request->sampai, $request->distributor);
            }
        } else {
            $reports = $stockRepo->getReport(date('Y-m-d', strtotime('first day of this month')), date('Y-m-d', strtotime('last day of this month')), null, auth()->id());
        }

        return view('report.buy', compact('reports', 'distributor'));
    }

    public function buyToday(StockRepository $stockRepo, Request $request): View
    {
        $isAdmin = auth()->user()->can('isAdmin');

        $reports = $isAdmin ? $stockRepo->getReportDaily(date('Y-m-d')) : $stockRepo->getReportDaily(date('Y-m-d'), auth()->id());

        return view('report.buy-today', compact('reports'));
    }

    public function buyMonth(StockRepository $stockRepo, Request $request): View
    {
        $isAdmin = auth()->user()->can('isAdmin');

        $reports = $isAdmin ? $stockRepo->getReportMonthly(date('m'), date('Y')) : $stockRepo->getReportMonthly(date('m'), date('Y'), auth()->id());

        return view('report.buy-month', compact('reports'));
    }

    public function stock(StockRepository $stockRepo, Request $request): View
    {
        $request->validate([
            'dari' => 'nullable|date',
            'sampai' => 'nullable|date',
            'distributor' => 'nullable|exists:pembelian,namaDist',
        ]);

        $distributor = $stockRepo->getDist();
        $reports = [];

        if ($request->filled('dari') || $request->filled('sampai') || $request->filled('distributor')) {
            $isAdmin = auth()->user()->can('isAdmin');

            $reports = $isAdmin ? $stockRepo->getReportQty($request->dari, $request->sampai, $request->distributor) : $stockRepo->getReportQty($request->dari, $request->sampai, null, auth()->id());
        }

        return view('report.stock', compact('reports', 'distributor'));
    }

    public function stockToday(StockRepository $stockRepo, Request $request): View
    {
        $isAdmin = auth()->user()->can('isAdmin');

        $reports = $isAdmin ? $stockRepo->getReportQtyDaily(date('Y-m-d')) : $stockRepo->getReportQtyDaily(date('Y-m-d'), auth()->id());

        return view('report.stock-today', compact('reports'));
    }

    public function stockMonth(StockRepository $stockRepo, Request $request): View
    {
        $isAdmin = auth()->user()->can('isAdmin');

        $reports = $isAdmin ? $stockRepo->getReportQtyMonthly(date('m'), date('Y')) : $stockRepo->getReportQtyMonthly(date('m'), date('Y'), auth()->id());

        return view('report.stock-month', compact('reports'));
    }

    public function printTransaction(TransactionRepository $transactionRepo, Request $request)
    {
        $request->validate([
            'dari' => 'nullable|date',
            'sampai' => 'nullable|date',
            'user' => 'nullable|exists:user,idUser'
        ]);

        $reports = [];

        if ($request->filled('dari') || $request->filled('sampai') || $request->filled('user')) {
            $isAdmin = auth()->user()->can('isAdmin');

            $reports = $isAdmin ? $transactionRepo->getReport($request->dari, $request->sampai, $request->user) : $transactionRepo->getReport($request->dari, $request->sampai, auth()->id());
        }

        $pdf = PDF::loadView('report.print-transaction', compact('reports'));

        return $pdf->stream();
    }

    public function printTransactionToday(TransactionRepository $transactionRepo)
    {
        $isAdmin = auth()->user()->can('isAdmin');

        $reports = $isAdmin ? $transactionRepo->getReportDaily(date('Y-m-d')) : $transactionRepo->getReportDaily(date('Y-m-d'), auth()->id());

        $pdf = PDF::loadView('report.print-transaction-today', compact('reports'));

        return $pdf->stream();
    }

    public function printTransactionMonth(TransactionRepository $transactionRepo)
    {
        $isAdmin = auth()->user()->can('isAdmin');

        $reports = $isAdmin ? $transactionRepo->getReportMonthly(date('m'), date('Y')) : $transactionRepo->getReportMonthly(date('m'), date('Y'), auth()->id());

        $pdf = PDF::loadView('report.print-transaction-month', compact('reports'));

        return $pdf->stream();
    }

    public function printExpend(ExpendRepository $expendRepo, Request $request)
    {
        $request->validate([
            'dari' => 'nullable|date',
            'sampai' => 'nullable|date'
        ]);

        $reports = [];

        if ($request->filled('dari') || $request->filled('sampai')) {
            $reports = $expendRepo->getReport($request->dari, $request->sampai);
        }

        $pdf = PDF::loadView('report.print-expend', compact('reports'));

        return $pdf->stream();
    }

    public function printSell(TransactionRepository $transactionRepo, Request $request)
    {
        $request->validate([
            'dari' => 'nullable|date',
            'sampai' => 'nullable|date',
            'user' => 'nullable|exists:user,idUser'
        ]);

        $reports = [];

        if ($request->filled('dari') || $request->filled('sampai') || $request->filled('user')) {
            $isAdmin = auth()->user()->can('isAdmin');

            $reports = $isAdmin ? $transactionRepo->getReportQty($request->dari, $request->sampai, $request->user) : $transactionRepo->getReportQty($request->dari, $request->sampai, auth()->id());
        }

        $pdf = PDF::loadView('report.print-sell', compact('reports'));

        return $pdf->stream();
    }

    public function printSellToday(TransactionRepository $transactionRepo, Request $request)
    {
        $isAdmin = auth()->user()->can('isAdmin');

        $reports = $isAdmin ? $transactionRepo->getReportQtyDaily(date('Y-m-d')) : $transactionRepo->getReportQtyDaily(date('Y-m-d'), auth()->id());

        $pdf = PDF::loadView('report.print-sell-today', compact('reports'));

        return $pdf->stream();
    }

    public function printSellMonth(TransactionRepository $transactionRepo, Request $request)
    {
        $isAdmin = auth()->user()->can('isAdmin');

        $reports = $isAdmin ? $transactionRepo->getReportQtyMonthly(date('m'), date('Y')) : $transactionRepo->getReportQtyMonthly(date('m'), date('Y'), auth()->id());

        $pdf = PDF::loadView('report.print-sell-month', compact('reports'));

        return $pdf->stream();
    }

    public function printBuy(StockRepository $stockRepo, Request $request)
    {
        $request->validate([
            'dari' => 'nullable|date',
            'sampai' => 'nullable|date',
            'distributor' => 'nullable|exists:pembelian,namaDist',
        ]);

        $distributor = $stockRepo->getDist();
        $reports = [];

        if ($request->filled('dari') || $request->filled('sampai') || $request->filled('distributor')) {
            $isAdmin = auth()->user()->can('isAdmin');

            $reports = $isAdmin ? $stockRepo->getReport($request->dari, $request->sampai, $request->distributor) : $stockRepo->getReport($request->dari, $request->sampai, null, auth()->id());
        }

        $pdf = PDF::loadView('report.print-buy', compact('reports'));

        return $pdf->stream();
    }

    public function printBuyToday(StockRepository $stockRepo, Request $request)
    {
        $isAdmin = auth()->user()->can('isAdmin');

        $reports = $isAdmin ? $stockRepo->getReportDaily(date('Y-m-d')) : $stockRepo->getReportDaily(date('Y-m-d'), auth()->id());

        $pdf = PDF::loadView('report.print-buy-today', compact('reports'));

        return $pdf->stream();
    }

    public function printBuyMonth(StockRepository $stockRepo, Request $request)
    {
        $isAdmin = auth()->user()->can('isAdmin');

        $reports = $isAdmin ? $stockRepo->getReportMonthly(date('m'), date('Y')) : $stockRepo->getReportMonthly(date('m'), date('Y'), auth()->id());

        $pdf = PDF::loadView('report.print-buy-month', compact('reports'));

        return $pdf->stream();
    }

    public function printStock(StockRepository $stockRepo, Request $request)
    {
        $request->validate([
            'dari' => 'nullable|date',
            'sampai' => 'nullable|date',
            'distributor' => 'nullable|exists:pembelian,namaDist',
        ]);

        $distributor = $stockRepo->getDist();
        $reports = [];

        if ($request->filled('dari') || $request->filled('sampai') || $request->filled('distributor')) {
            $isAdmin = auth()->user()->can('isAdmin');

            $reports = $isAdmin ? $stockRepo->getReportQty($request->dari, $request->sampai, $request->distributor) : $stockRepo->getReportQty($request->dari, $request->sampai, null, auth()->id());
        }

        $pdf = PDF::loadView('report.print-stock', compact('reports'));

        return $pdf->stream();
    }

    public function printStockToday(StockRepository $stockRepo, Request $request)
    {
        $isAdmin = auth()->user()->can('isAdmin');

        $reports = $isAdmin ? $stockRepo->getReportQtyDaily(date('Y-m-d')) : $stockRepo->getReportQtyDaily(date('Y-m-d'), auth()->id());

        $pdf = PDF::loadView('report.print-stock-today', compact('reports'));

        return $pdf->stream();
    }

    public function printStockMonth(StockRepository $stockRepo, Request $request)
    {
        $isAdmin = auth()->user()->can('isAdmin');

        $reports = $isAdmin ? $stockRepo->getReportQtyMonthly(date('m'), date('Y')) : $stockRepo->getReportQtyMonthly(date('m'), date('Y'), auth()->id());

        $pdf = PDF::loadView('report.print-stock-month', compact('reports'));

        return $pdf->stream();
    }

    public function printAccumulation(FinanceService $finance, ExpendRepository $expendRepo, Request $request)
    {
        $request->validate([
            'dari' => 'nullable|date',
            'sampai' => 'nullable|date'
        ]);

        $reports = [];
        $expend = [];

        if ($request->filled('dari') || $request->filled('sampai')) {
            $reports = $finance->accumulation($request->dari, $request->sampai);
            $expend = $expendRepo->groupAccumulationOne($request->dari, $request->sampai);
        }

        $pdf = PDF::loadView('report.print-accumulation', compact('reports', 'expend'));

        return $pdf->stream();
    }

    public function printAccumulationNew(FinanceService $finance, ExpendRepository $expendRepo, Request $request)
    {
        $request->validate([
            'dari' => 'nullable|date',
            'sampai' => 'nullable|date'
        ]);

        $reports = [];
        $expend = [];

        if ($request->filled('dari') || $request->filled('sampai')) {
            $reports = $finance->accumulation($request->dari, $request->sampai);
            $expend = $expendRepo->groupAccumulation($request->dari, $request->sampai);
        }

        $pdf = PDF::loadView('report.print-accumulation-new', compact('reports', 'expend'));

        return $pdf->stream();
    }

}
