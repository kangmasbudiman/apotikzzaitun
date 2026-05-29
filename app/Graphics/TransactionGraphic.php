<?php 

namespace App\Graphics;

use App\Repositories\TransactionRepository;

class TransactionGraphic
{

    protected $transaction;

    public function __construct(TransactionRepository $transactionRepo)
    {
        $this->transaction = $transactionRepo;
    }

    public function countData(string $type): Int
    {
        switch ($type) {
            case 'today':
                $data = $this->transaction->countPerDate(date('Y-m-d'));
                break;

            case 'week':
                $data = $this->transaction->countPerRange(date('Y-m-d', strtotime('-7 days')), date('Y-m-d'));
                break;

            case 'year':
                $data = $this->transaction->countPerYear(date('Y'));
                break;
            
            default:
                $data = $this->transaction->countPerMonth(date('m'), date('Y'));
                break;
        }

        return $data;
    }

    public function countCancel(string $type): Int
    {
        switch ($type) {
            case 'today':
                $data = $this->transaction->countCancelPerDate(date('Y-m-d'));
                break;

            case 'week':
                $data = $this->transaction->countCancelPerRange(date('Y-m-d', strtotime('-7 days')), date('Y-m-d'));
                break;

            case 'year':
                $data = $this->transaction->countCancelPerYear(date('Y'));
                break;
            
            default:
                $data = $this->transaction->countCancelPerMonth(date('m'), date('Y'));
                break;
        }

        return $data;
    }

    public function countSelledBook(string $type): Int
    {
        switch ($type) {
            case 'today':
                $data = $this->transaction->countSelledBookPerDate(date('Y-m-d'));
                break;

            case 'week':
                $data = $this->transaction->countSelledBookPerRange(date('Y-m-d', strtotime('-7 days')), date('Y-m-d'));
                break;

            case 'year':
                $data = $this->transaction->countSelledBookPerYear(date('Y'));
                break;
            
            default:
                $data = $this->transaction->countSelledBookPerMonth(date('m'), date('Y'));
                break;
        }

        return $data;
    }

    public function countToday(): Int
    {
        return $this->transaction->countToday();
    }

    public function countIncome(string $type): Int
    {
        switch ($type) {
            case 'today':
                $data = $this->transaction->sumIncomePerDate(date('Y-m-d'));
                break;

            case 'week':
                $data = $this->transaction->sumIncomePerRange(date('Y-m-d', strtotime('-7 days')), date('Y-m-d'));
                break;

            case 'year':
                $data = $this->transaction->sumIncomePerYear(date('Y'));
                break;
            
            default:
                $data = $this->transaction->sumIncomePerMonth(date('m'), date('Y'));
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
                $data = $this->getPerYear();
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

        array_push($data, $this->transaction->sumIncomePerDate(date('Y-m-d')));
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
            $total = $this->transaction->sumIncomePerDate(substr(date('Y-m-d'), 0, -2).$i);

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
            $total = $this->transaction->sumIncomePerMonth($i + 1, date('Y'));

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
            $total = $this->transaction->sumIncomePerDate(date('Y-m-d', strtotime('-'.$i.' days')));

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