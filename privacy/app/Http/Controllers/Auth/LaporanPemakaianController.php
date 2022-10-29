<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LaporanPemakaian;

class LaporanPemakaianController extends Controller
{
    //

    public function index()
    {
        return view('admin.laporanpemakaian.index');
    }

    public function create()
    {
        return view('admin.laporanpemakaian.create');
    }

    public function store(Request $request)
    {
        $laporanpemakaian = new LaporanPemakaian([
          'type' => $request->get('type'),
          'total_pemakaian' => $request->get('total_pemakaian'),
          'tanggal_pemakaian' => $request->get('tanggal_pemakaian'),
        ]);
        $laporanpemakaian->save();

        return view('admin.laporanpemakaian.create');
    }

    public function chart()
      {
        $result = \DB::table('pemakaian')
                    ->where('type','=','Mobil')
                    ->orderBy('tanggal_pemakaian', 'ASC')
                    ->get();
        return response()->json($result);
      } 
}
