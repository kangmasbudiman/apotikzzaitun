<?php 

namespace App\Graphics;

use App\Models\Opname;

class OpnameGraphic
{

    public function sumPerDate($date)
    {
        return Opname::whereDate('tanggal', $date)->selectRaw('SUM((stokSistem - stokNyata) * hargaPokok) as result')->first()->result;
    }

    public function sumPerMonth(int $month, int $year)
    {
        return Opname::whereMonth('tanggal', $month)->whereYear('tanggal', $year)->selectRaw('SUM((stokSistem - stokNyata) * hargaPokok) as result')->first()->result;
    }

    public function sumPerYear(int $year)
    {
        return Opname::whereYear('tanggal', $year)->selectRaw('SUM((stokSistem - stokNyata) * hargaPokok) as result')->first()->result;
    }

    public function sumPerRange(string $start, string $end)
    {
        return Opname::whereDate('tanggal', '>=', $start)->whereDate('tanggal', '<=', $end)->selectRaw('SUM((stokSistem - stokNyata) * hargaPokok) as result')->first()->result;
    }

    public function countOpname(string $type)
    {
        switch ($type) {
            case 'today':
                $data = $this->sumPerDate(date('Y-m-d'));
                break;

            case 'week':
                $data = $this->sumPerRange(date('Y-m-d', strtotime('-7 days')), date('Y-m-d'));
                break;

            case 'year':
                $data = $this->sumPerYear(date('Y'));
                break;
            
            default:
                $data = $this->sumPerMonth(date('m'), date('Y'));
                break;
        }

        return $data;
    }

}

 ?>