<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\StuffRepository;

use PDF;

class BarcodeController extends Controller
{

    public function print(StuffRepository $stuffRepo, Request $request)
    {
        $items = json_decode($request->cookie('barcode'));
        $barcodes = [];

        if ($items) {
            foreach ($items as $item) {            
                for ($i=0; $i < $item->jumlah; $i++) {
                    $barcode = $stuffRepo->find($item->idBuku);
                    
                    array_push($barcodes, $barcode);
                }
            }
        } else {
            return redirect()->route('barcode.search');
        }
        
        $pdf = PDF::loadView('stuff.allbarcode', compact('barcodes'));
        $pdf->setPaper('a4');
        
        return $pdf->stream();
    }

}
