<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Itembulanan;
use App\Models\Company;
use PDF;
use Excel;
use DB;

class ItembulananController extends Controller
{
    public function show()
    {
        $itembulanan = Itembulanan::find(request()->id);
        
        if($itembulanan){
            $output = array(

                'periode'=>$itembulanan->periode,
                'kode_produk'=>$itembulanan->kode_produk,
                'satuan'=>$itembulanan->satuan,
                'begin_stock'=>$itembulanan->begin_stock,
                'begin_amount'=>$itembulanan->begin_amount,
                'in_stock'=>$itembulanan->in_stock,
                'in_amount'=>$itembulanan->in_amount,
            );
        }else{
            $output = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Maaf Data Terkait Tidak Ada'
                ];
        }
        
            
        return response()->json($output);
    }
}
