<?php 

namespace App\Graphics;

use App\Repositories\DistributorRepository;

class DistributorGraphic
{

    protected $distributor;

    public function __construct(DistributorRepository $distributorGraphic)
    {
        $this->distributor = $distributorGraphic;
    }

    public function count(string $type): Int
    {
        switch ($type) {
            case 'today':
                $data = $this->distributor->countPerDate(date('Y-m-d'));
                break;

            case 'week':
                $data = $this->distributor->countPerRange(date('Y-m-d', strtotime('-7 days')), date('Y-m-d'));
                break;

            case 'year':
                $data = $this->distributor->countPerYear(date('Y'));
                break;
            
            default:
                $data = $this->distributor->countPerMonth(date('m'), date('Y'));
                break;
        }

        return $data;
    }
    
}

 ?>