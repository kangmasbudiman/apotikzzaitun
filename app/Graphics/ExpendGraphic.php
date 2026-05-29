<?php 

namespace App\Graphics;

use App\Repositories\ExpendRepository;

class ExpendGraphic
{

    protected $expend;

    public function __construct(ExpendRepository $expendRepo)
    {
        $this->expend = $expendRepo;
    }

    public function countExpend(string $type): Int
    {
        switch ($type) {
            case 'today':
                $data = $this->expend->sumExpendPerDate(date('Y-m-d'));
                break;

            case 'week':
                $data = $this->expend->sumExpendPerRange(date('Y-m-d', strtotime('-7 days')), date('Y-m-d'));
                break;

            case 'year':
                $data = $this->expend->sumExpendPerYear(date('Y'));
                break;
            
            default:
                $data = $this->expend->sumExpendPerMonth(date('m'), date('Y'));
                break;
        }

        return $data;
    }

}

 ?>