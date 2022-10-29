<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Reopen;
use App\Models\tb_akhir_auto;
use App\Models\tb_bulanan_auto;
use App\Models\MasterLokasi;
use Carbon;

class Start5Controller extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    public function konek()
    {
        $compa = '04';
        if ($compa == '01'){
            $koneksi = 'mysqldepo';
        }else if ($compa == '02'){
            $koneksi = 'mysqlpbm';
        }else if ($compa == '03'){
            $koneksi = 'mysqlemkl';
        }else if ($compa == '04'){
            $koneksi = 'mysqlgut';
        }else if ($compa == '05'){
            $koneksi = 'mysql';
        }
        return $koneksi;
    }

    public function index()
    { 
        $konek = self::konek();
        $Company= Company::get();
        $tanggal = tb_akhir_auto::on($konek)->where('status_periode','Open')->pluck('periode','periode');
        return view('start5', compact('Company', 'tanggal'));
    }

    public function go_to(Request $request)
    {
        $compa = $request->company;
        return redirect()->route('login', ['kode_company'=>$compa]);
    }

    public function change(Request $request)
    {   
         $konek = self::konek();
         $tanggal_tutup = $request->tanggal_awal;
         $tanggal_buka = $request->tanggal_akhir;

         $tahun_tutup = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal_tutup)->year;
         $bulan_tutup = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal_tutup)->month;
         // dd($tanggal_buka);

         $tahun_buka = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal_buka)->year;
         $bulan_buka = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal_buka)->month;
         $hari_buka = '01';
         // dd($hari_buka);

         $tabel_tutup = tb_akhir_auto::on($konek)->whereMonth('periode', $bulan_tutup)->whereYear('periode', $tahun_tutup)->first();
         $tabel_buka = tb_akhir_auto::on($konek)->whereMonth('periode', $bulan_buka)->whereYear('periode', $tahun_buka)->first();
         // dd($tabel_tutup);
         
         $tabel_baru = array();

         $status_baru = 'Open';
         $reopen_baru = 'false';

         if($tabel_buka == null && $tabel_tutup == null){

                $tabel_baru = [ 
                      'periode' => $tanggal_buka,
                      'status_periode' => $status_baru,
                      'reopen_status' => $reopen_baru,
                    ];
                      
                $periode_baru = tb_akhir_auto::on($konek)->create($tabel_baru);

                $message = [
                    'success' => true,
                    'title' => 'Simpan',
                    'message' => 'Open Month: '.$tanggal_buka, 'Berhasil!',
                ];
                return response()->json($message);
          }else if($tabel_buka == null && $tabel_tutup != null){
                $tabel_baru = [ 
                      'periode' => $tanggal_buka,
                      'status_periode' => $status_baru,
                      'reopen_status' => $reopen_baru,
                    ];

                $tb_item_bulanan = tb_bulanan_auto::on($konek)->with('produk')->where('periode', $tanggal_tutup)->get();

                foreach ($tb_item_bulanan as $rowdata){
                    $tb_item_bulanan1 = tb_bulanan_auto::on($konek)->where('kode_produk',$rowdata->kode_produk)->where('partnumber',$rowdata->partnumber)->where('kode_lokasi',$rowdata->kode_lokasi)->where('kode_company',$rowdata->kode_company)->where('no_mesin',$rowdata->no_mesin)->where('periode', $tanggal_tutup)->first();
                    
                    $update_baru = [
                            'periode'=>$tanggal_buka,
                            'kode_produk'=>$tb_item_bulanan1->kode_produk,
                            'partnumber'=>$tb_item_bulanan1->partnumber,
                            'no_mesin'=>$tb_item_bulanan1->no_mesin,
                            'begin_stock'=>$tb_item_bulanan1->ending_stock,
                            'begin_amount'=>$tb_item_bulanan1->ending_amount,
                            'in_stock'=>0,
                            'in_amount'=>0,
                            'out_stock'=>0,
                            'out_amount'=>0,
                            'sale_stock'=>0,
                            'sale_amount'=>0,
                            'trf_in'=>0,
                            'trf_in_amount'=>0,
                            'trf_out'=>0,
                            'trf_out_amount'=>0,
                            'adjustment_stock'=>0,
                            'adjustment_amount'=>0,
                            'stock_opname'=>0,
                            'amount_opname'=>0,
                            'retur_beli_stock'=>0,
                            'retur_beli_amount'=>0,
                            'retur_jual_stock'=>0,
                            'retur_jual_amount'=>0,
                            'retur_pakai_stock'=>0,
                            'retur_pakai_amount'=>0,
                            'ending_stock'=>$tb_item_bulanan1->ending_stock,
                            'ending_amount'=>$tb_item_bulanan1->ending_amount,
                            'hpp'=>$tb_item_bulanan1->hpp,
                            'kode_lokasi'=>$tb_item_bulanan1->kode_lokasi,
                            'kode_company'=>$tb_item_bulanan1->kode_company,
                    ];
                        // dd($update_baru);

                    $tb_item_bulanan2 = tb_bulanan_auto::on($konek)->where('kode_produk',$rowdata->kode_produk)->where('partnumber',$rowdata->partnumber)->where('kode_lokasi',$rowdata->kode_lokasi)->where('kode_company',$rowdata->kode_company)->where('no_mesin',$rowdata->no_mesin)->where('periode', $tanggal_buka)->first();
                    if ($tb_item_bulanan2 == null){
                        $update_item_bulanan = tb_bulanan_auto::on($konek)->create($update_baru);
                    }
                }
                
                $periode_baru = tb_akhir_auto::on($konek)->create($tabel_baru);

                $status = $tabel_tutup->status_periode;
                $re_status = $tabel_tutup->reopen_status;

                if($status == 'Open'){
                    $tabel_tutup->status_periode = "Closed";
                    $tabel_tutup->save(); 

                    $message = [
                    'success' => true,
                    'title' => 'Simpan',
                    'message' => 'End Of Month: '.$tanggal_tutup, 'Berhasil!',
                    ];
                    return response()->json($message);
                }
                else{
                  $message = [
                  'success' => false,
                  'title' => 'Simpan',
                  'message' => 'Gagal End Of Month'.$tanggal_buka, 'Error!',
                  ];
                  return response()->json($message);
                }
             
          }

          else if($tabel_buka != null && $tabel_tutup != null){
              $cek_periode = tb_akhir_auto::on($konek)->where('status_periode','Disable')->first();
              if ($cek_periode != null){
                  $message = [
                      'success' => false,
                      'title' => 'Simpan',
                      'message' => 'Silakan tutup reopen terlebih dahulu', 'Error!',
                  ];
                  return response()->json($message);
              }else {
                  $message = [
                      'success' => false,
                      'title' => 'Errors',
                      'message' => 'Broken!!!', 'Error!',
                  ];
                  return response()->json($message);
              }
          }
          else{
              $message = [
                  'success' => false,
                  'title' => 'Simpan',
                  'message' => 'Gagal End Of Month'.$tanggal_buka, 'Error!',
                  ];
              return response()->json($message);
          }
    }
}
