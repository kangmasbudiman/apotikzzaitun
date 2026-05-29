<?php 

namespace App\Graphics;

use App\Repositories\{StockRepository, ExpendRepository};

class StockGraphic
{

    protected $stock, $expend;

    public function __construct(StockRepository $stockRepo, ExpendRepository $expendRepo)
    {
        $this->stock = $stockRepo;
        $this->expend = $expendRepo;
    }

    public function countData(string $type): Int
    {
        switch ($type) {
            case 'today':
                $data = $this->stock->countPerDate(date('Y-m-d'));
                break;

            case 'week':
                $data = $this->stock->countPerRange(date('Y-m-d', strtotime('-7 days')), date('Y-m-d'));
                break;

            case 'year':
                $data = $this->stock->countPerYear(date('Y'));
                break;
            
            default:
                $data = $this->stock->countPerMonth(date('m'), date('Y'));
                break;
        }

        return $data;
    }

    public function countBuyedBook(string $type): Int
    {
        switch ($type) {
            case 'today':
                $data = $this->stock->countBuyedBookPerDate(date('Y-m-d'));
                break;

            case 'week':
                $data = $this->stock->countBuyedBookPerRange(date('Y-m-d', strtotime('-7 days')), date('Y-m-d'));
                break;

            case 'year':
                $data = $this->stock->countBuyedBookPerYear(date('Y'));
                break;
            
            default:
                $data = $this->stock->countBuyedBookPerMonth(date('m'), date('Y'));
                break;
        }

        return $data;
    }

    public function countIncome(string $type): Int
    {
        switch ($type) {
            case 'today':
                $data = $this->stock->sumIncomePerDate(date('Y-m-d'));
                break;

            case 'week':
                $data = $this->stock->sumIncomePerRange(date('Y-m-d', strtotime('-7 days')), date('Y-m-d'));
                break;

            case 'year':
                $data = $this->stock->sumIncomePerYear(date('Y'));
                break;
            
            default:
                $data = $this->stock->sumIncomePerMonth(date('m'), date('Y'));
                break;
        }

        return $data;
    }

    public function getGraphic(string $type): Array
    {
        switch ($type) {
            case 'today':
                $data = $this->getPerRange(2);
                break;

            case 'week':
                $data = $this->getPerRange(7);
                break;

            case 'year':
                $data = $this->getPerYear(date('Y'));
                break;
            
            default:
                $data = $this->getPerMonth();
                break;
        }

        return $data;
    }

    public function getPerDate()
    {
        $data = [];
        $date = [];

        array_push($data, $this->stock->sumIncomePerDate(date('Y-m-d')) + $this->expend->sumExpendPerDate(date('Y-m-d')));
        array_push($date, date('d'));

        return [
            'data' => $data,
            'date' => $date
        ];
    }

    public function getPerMonth()
    {
        $data = [];
        $date = [];

        for ($i=1; $i <= date('t'); $i++) { 
            $total = $this->expend->sumExpendPerDate(substr(date('Y-m-d'), 0, -2).$i);

            array_push($data, $total);
            array_push($date, substr(date('d'), 0, -2).$i);
        }

        return [
            'data' => $data,
            'date' => $date
        ];
    }

    public function getPerYear()
    {
        $data = [];
        $date = [];

        for ($i=0; $i < 12; $i++) { 
            $total = $this->expend->sumExpendPerMonth($i + 1, date('Y'));

            array_push($data, $total);
            array_push($date, date('M', mktime(0,0,0,$i + 1)));
        }

        return [
            'data' => $data,
            'date' => $date
        ];
    }

    public function getPerRange(int $range)
    {
        $data = [];
        $date = [];

        for ($i=0; $i < $range; $i++) { 
            $total = $this->expend->sumExpendPerDate(date('Y-m-d', strtotime('-'.$i.' days')));

            array_push($data, $total);
            array_push($date, date('d', strtotime('-'.$i.' days')));
        }

        return [
            'data' => array_reverse($data),
            'date' => array_reverse($date)
        ];
    }

}

 ?>