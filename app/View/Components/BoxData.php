<?php

namespace App\View\Components;

use Illuminate\View\Component;

use App\Graphics\StuffGraphic;
use App\Graphics\StockGraphic;
use App\Graphics\TransactionGraphic;
use App\Graphics\ExpendGraphic;
use App\Graphics\FinanceGraphic;
use App\Graphics\OpnameGraphic;

class BoxData extends Component
{

    public $type;

    public $totalStuff, $totalStock, $selledBook, $buyedBook, $totalTransaction, $totalCancel, $totalIncome, $totalBuy, $totalExpend, $totalOpname, $profitOne, $profit, $incomeGraphic, $stockGraphic;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(string $type = 'month')
    {
        $this->type = $type;

        $this->getData();
    }

    public function getData()
    {
        $stuffGraphic = app(StuffGraphic::class);
        $stockGraphic = app(StockGraphic::class);
        $transactionGraphic = app(TransactionGraphic::class);
        $expendGraphic = app(ExpendGraphic::class);
        $opnameGraphic = app(OpnameGraphic::class);
        $finance = app(FinanceGraphic::class);

        $income = $transactionGraphic->countIncome($this->type);
        $stock = $stockGraphic->countIncome($this->type);
        $expend = $expendGraphic->countExpend($this->type);
        $opname = $opnameGraphic->countOpname($this->type);

        $this->totalStuff = $stuffGraphic->count($this->type);
        $this->totalStock = $stockGraphic->countData($this->type);
        $this->selledBook = $transactionGraphic->countSelledBook($this->type);
        $this->buyedBook = $stockGraphic->countBuyedBook($this->type);
        $this->totalTransaction = $transactionGraphic->countData($this->type);
        $this->totalCancel = $transactionGraphic->countCancel($this->type);
        $this->totalIncome = 'Rp '.number_format($income);
        $this->totalBuy = 'Rp '.number_format($stock);
        $this->totalExpend = 'Rp '.number_format($expend);
        $this->totalOpname = 'Rp '.number_format($opname);
        $this->profitOne = 'Rp '.number_format($finance->accumulation($this->type)->labaOne);
        $this->profit = 'Rp '.number_format($finance->accumulation($this->type)->labaKotor);

        $this->incomeGraphic = $transactionGraphic->getGraphic($this->type);
        $this->stockGraphic = $stockGraphic->getGraphic($this->type);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('components.box-data');
    }
}
