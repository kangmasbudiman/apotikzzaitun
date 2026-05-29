<?php 

namespace App\Graphics;

use App\Services\FinanceService;

class FinanceGraphic
{

    protected $finance;

    public function __construct(FinanceService $financeService)
    {
        $this->finance = $financeService;
    }

    public function accumulation(string $type): Object
    {
        switch ($type) {
            case 'today':
                $start = date('Y-m-d');
                $end = date('Y-m-d');
                break;

            case 'week':
                $start = date('Y-m-d', strtotime('-7 days'));
                $end = date('Y-m-d');
                break;

            case 'year':
                $start = date('Y-m-d', strtotime('first day of January'));
                $end = date('Y-m-d', strtotime('last day of december'));
                break;
            
            default:
                $start = date('Y-m-d', strtotime('first day of this month'));
                $end = date('Y-m-d', strtotime('last day of this month'));
                break;
        }

        return $this->finance->accumulation($start, $end);
    }

}

 ?>