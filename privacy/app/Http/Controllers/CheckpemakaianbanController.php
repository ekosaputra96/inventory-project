<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Checkpemakaianban;
use App\Models\Pemakaianban;
use App\Models\PemakaianbanDetail;
use App\Models\tb_item_bulanan;
use App\Models\tb_akhir_bulan;
use App\Models\MasterLokasi;
use App\Models\Company;
use App\Models\Konversi;
use PDF;
use Excel;
use DB;
use Carbon;

class CheckpemakaianbanController extends Controller
{
    public function konek()
    {
        $compa2 = auth()->user()->kode_company;
        $compa = substr($compa2,0,2);
        if ($compa == '01'){
            $koneksi = 'mysqldepo';
        }else if ($compa == '02'){
            $koneksi = 'mysqlpbm';
        }else if ($compa == '99'){
            $koneksi = 'mysqlpbmlama';
        }else if ($compa == '03'){
            $koneksi = 'mysqlemkl';
        }else if ($compa == '22'){
            $koneksi = 'mysqlskt';
        }else if ($compa == '04'){
            $koneksi = 'mysqlgut';
        }else if ($compa == '05'){
            $koneksi = 'mysql';
        }else if ($compa == '06'){
            $koneksi = 'mysqlinfra';
        }
        return $koneksi;
    }

    public function index()
    {
        $konek = self::konek();
        $create_url = route('checkpemakaianban.create');
        $tanggal = tb_akhir_bulan::on($konek)->where('status_periode','Open')->pluck('periode','periode');
        $periode = tb_akhir_bulan::on($konek)->pluck('periode','periode');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        $level = auth()->user()->level;
        if($level == 'superadministrator' || $level == 'user_rince' || $level == 'user_herry'){
            return view('admin.checkpemakaianban.index',compact('create_url','tanggal','period', 'nama_lokasi','periode'));
        }
        else{
            return view('admin.checkpemakaianban.blank',compact('create_url','tanggal','period', 'nama_lokasi','periode'));
        }


    }

    public function change(Request $request)
    {   
        $konek = self::konek();
        $lokasi = auth()->user()->kode_lokasi;

        $periode = $request->periode;

        $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$periode)->year;
        $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$periode)->month;

        $pemakaianbandetail = PemakaianbanDetail::on($konek)
          ->select('pemakaianban_detail.*','pemakaianban.no_pemakaianban','pemakaianban.tanggal_pemakaianban')
          ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
          ->where('pemakaianban.status','POSTED')
          ->where('pemakaianban.kode_lokasi',$lokasi)
          ->whereMonth('pemakaianban.tanggal_pemakaianban', $bulan)
          ->whereYear('pemakaianban.tanggal_pemakaianban', $tahun)
          ->orderBy('pemakaianban_detail.kode_produk','asc')
          ->get();
        // dd($pemakaianbandetail);

        $leng = $pemakaianbandetail->count();
        $data = array();

        if(!empty($pemakaianbandetail)){
          foreach ($pemakaianbandetail as $rowdata){
            $no_pemakaianban = $rowdata->no_pemakaianban;
            $kode_produk = $rowdata->kode_produk;
            $partnumberbaru = $rowdata->partnumberbaru;

            $data[] = array(
              'no_pemakaianban'=>$no_pemakaianban,
              'kode_produk'=>$kode_produk,
              'partnumberbaru'=>$partnumberbaru,
            );
                               
          }
        }  

        $index = $request->index;
        if($index == 1){
            $leng_final = floor($leng/3);
            $i = 0;

            while($i < $leng_final){
              $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumberbaru'])->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
              // dd($tb_item_bulanan);

              $out_stock = $tb_item_bulanan->out_stock;
              $out_amount = $tb_item_bulanan->out_amount;

              $pemakaianbandetail2 = PemakaianbanDetail::on($konek)
              ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
              ->where('pemakaianban_detail.kode_produk',$data[$i]['kode_produk'])
              ->where('pemakaianban_detail.partnumberbaru',$data[$i]['partnumberbaru'])
              ->where('pemakaianban.status','POSTED')
              ->where('pemakaianban.kode_lokasi',$lokasi)
              ->whereMonth('pemakaianban.tanggal_pemakaianban', $bulan)
              ->whereYear('pemakaianban.tanggal_pemakaianban', $tahun)
              ->orderBy('pemakaianban.tanggal_pemakaianban','asc')
              ->get();

              if($pemakaianbandetail2 != null){
                $total_qty = 0;
                $total_harga = 0;
                $grand_total = 0;

                foreach ($pemakaianbandetail2 as $row){
                  $total_qty += $row->qty;
                  $subtotal = $row->harga * $row->qty;
                  $total_harga += $subtotal;
                  $grand_total = number_format($total_harga,2,",",".");
                }  

                if($total_qty != $out_stock){
                  $message = [
                      'success' => false,
                      'title' => 'Simpan',
                      'message' => 'Total QTY Pemakaian Ban Salah. Cek Produk '.$data[$i]['kode_produk'].' pada no. pemakaian ban '.$data[$i]['no_pemakaianban'].'!',
                  ];
                  return response()->json($message);
                }

                $cek_amount = abs(bcsub($total_harga, $out_amount, 2));

                // if($total_harga != $out_amount){
                if($cek_amount > 15){
                  $message = [
                      'success' => false,
                      'title' => 'Simpan',
                      'message' => 'Total Harga Pemakaian Ban Salah. Cek Produk '.$data[$i]['kode_produk'].' pada no. pemakaian ban '.$data[$i]['no_pemakaianban'].'!',
                  ];
                  return response()->json($message);
                }
              }

              $i++;
            }

            $message = [
              'success' => true,
              'title' => 'Simpan',
              'message' => 'Data Pemakaian Benar.',
            ];
            return response()->json($message);
        }
        else if($index == 2){
          $leng_final = floor($leng/3);
          $leng_final2 = $leng - $leng_final;
          $i = $leng_final;

          while($i < $leng_final2){
              $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumberbaru'])->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

              $out_stock = $tb_item_bulanan->out_stock;
              $out_amount = $tb_item_bulanan->out_amount;

              $pemakaianbandetail2 = PemakaianbanDetail::on($konek)
              ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
              ->where('pemakaianban_detail.kode_produk',$data[$i]['kode_produk'])
              ->where('pemakaianban_detail.partnumberbaru',$data[$i]['partnumberbaru'])
              ->where('pemakaianban.status','POSTED')
              ->where('pemakaianban.kode_lokasi',$lokasi)
              ->whereMonth('pemakaianban.tanggal_pemakaianban', $bulan)
              ->whereYear('pemakaianban.tanggal_pemakaianban', $tahun)
              ->orderBy('pemakaianban.tanggal_pemakaianban','asc')
              ->get();

              if($pemakaianbandetail2 != null){
                $total_qty = 0;
                $total_harga = 0;
                $grand_total = 0;

                foreach ($pemakaianbandetail2 as $row){
                  $total_qty += $row->qty;
                  $subtotal = $row->harga * $row->qty;
                  $total_harga += $subtotal;
                  $grand_total = number_format($total_harga,2,",",".");
                }  

                if($total_qty != $out_stock){
                  $message = [
                      'success' => false,
                      'title' => 'Simpan',
                      'message' => 'Total QTY Pemakaian Ban Salah. Cek Produk '.$data[$i]['kode_produk'].' pada no. pemakaian ban '.$data[$i]['no_pemakaianban'].'!',
                  ];
                  return response()->json($message);
                }

                $cek_amount = abs(bcsub($total_harga, $out_amount, 2));
                // if($total_harga != $out_amount){
                if($cek_amount > 15){
                  $message = [
                      'success' => false,
                      'title' => 'Simpan',
                      'message' => 'Total Harga Pemakaian Ban Salah. Cek Produk '.$data[$i]['kode_produk'].' pada no. pemakaian ban '.$data[$i]['no_pemakaianban'].'!',
                  ];
                  return response()->json($message);
                }
              }

              $i++;
          }

          $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => 'Data Pemakaian Benar.',
          ];
          return response()->json($message);
        }
        else{
          $leng_final = floor($leng/2);
          $leng_final2 = $leng - $leng_final;
          $i = $leng_final2;
          // $cek_produk = $data[19]['kode_produk'];
          // dd($cek_produk);

          while($i < $leng){
              $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumberbaru'])->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

              $out_stock = $tb_item_bulanan->out_stock;
              $out_amount = $tb_item_bulanan->out_amount;

              $pemakaianbandetail2 = PemakaianbanDetail::on($konek)
              ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
              ->where('pemakaianban_detail.kode_produk',$data[$i]['kode_produk'])
              ->where('pemakaianban_detail.partnumberbaru',$data[$i]['partnumberbaru'])
              ->where('pemakaianban.status','POSTED')
              ->where('pemakaianban.kode_lokasi',$lokasi)
              ->whereMonth('pemakaianban.tanggal_pemakaianban', $bulan)
              ->whereYear('pemakaianban.tanggal_pemakaianban', $tahun)
              ->orderBy('pemakaianban.tanggal_pemakaianban','asc')
              ->get();

              if($pemakaianbandetail2 != null){
                $total_qty = 0;
                $total_harga = 0;
                $grand_total = 0;

                foreach ($pemakaianbandetail2 as $row){
                  $total_qty += $row->qty;
                  $subtotal = $row->harga * $row->qty;
                  $total_harga += $subtotal;
                  $grand_total = number_format($total_harga,2,",",".");
                }  

                if($total_qty != $out_stock){
                  $message = [
                      'success' => false,
                      'title' => 'Simpan',
                      'message' => 'Total QTY Pemakaian Ban Salah. Cek Produk '.$data[$i]['kode_produk'].' pada no. pemakaian '.$data[$i]['no_pemakaianban'].'!',
                  ];
                  return response()->json($message);
                }

                $cek_amount = abs(bcsub($total_harga, $out_amount, 2));
                // if($total_harga != $out_amount){
                if($cek_amount > 15){
                  $message = [
                      'success' => false,
                      'title' => 'Simpan',
                      'message' => 'Total Harga Pemakaian Ban Salah. Cek Produk '.$data[$i]['kode_produk'].' pada no. pemakaian '.$data[$i]['no_pemakaianban'].'!',
                  ];
                  return response()->json($message);
                }
              }

              $i++;
          }

          $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => 'Data Pemakaian Ban Benar.',
          ];
          return response()->json($message);
        }
    }
}
