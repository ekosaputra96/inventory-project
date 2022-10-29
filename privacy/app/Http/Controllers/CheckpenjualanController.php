<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Checkpenjualan;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\tb_item_bulanan;
use App\Models\tb_akhir_bulan;
use App\Models\MasterLokasi;
use App\Models\Company;
use App\Models\Konversi;
use PDF;
use Excel;
use DB;
use Carbon;

class CheckpenjualanController extends Controller
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
        $create_url = route('checkpenjualan.create');
        $tanggal = tb_akhir_bulan::on($konek)->where('status_periode','Open')->pluck('periode','periode');
        $periode = tb_akhir_bulan::on($konek)->pluck('periode','periode');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        $level = auth()->user()->level;
        if($level == 'superadministrator' || $level == 'user_rince' || $level == 'user_herry'){
            return view('admin.checkpenjualan.index',compact('create_url','tanggal','period', 'nama_lokasi','periode'));
        }
        else{
            return view('admin.checkpenjualan.blank',compact('create_url','tanggal','period', 'nama_lokasi','periode'));
        }


    }

    public function change(Request $request)
    {   
        $konek = self::konek();
        $lokasi = auth()->user()->kode_lokasi;

        $periode = $request->periode;

        $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$periode)->year;
        $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$periode)->month;

        $penjualandetail = PenjualanDetail::on($konek)
          ->select('penjualan_detail.*','penjualan.no_penjualan','penjualan.tanggal_penjualan')
          ->join('penjualan', 'penjualan_detail.no_penjualan', '=', 'penjualan.no_penjualan')
          ->where('penjualan.status','POSTED')
          ->where('penjualan.kode_lokasi',$lokasi)
          ->whereMonth('penjualan.tanggal_penjualan', $bulan)
          ->whereYear('penjualan.tanggal_penjualan', $tahun)
          ->orderBy('penjualan_detail.kode_produk','asc')
          ->get();
        // dd($penjualandetail);

        $leng = $penjualandetail->count();
        $data = array();

        if(!empty($penjualandetail)){
          foreach ($penjualandetail as $rowdata){
            $no_penjualan = $rowdata->no_penjualan;
            $kode_produk = $rowdata->kode_produk;
            $partnumber = $rowdata->partnumber;

            $data[] = array(
              'no_penjualan'=>$no_penjualan,
              'kode_produk'=>$kode_produk,
              'partnumber'=>$partnumber,
            );
                               
          }
        }  

        $index = $request->index;
        if($index == 1){
            $leng_final = floor($leng/3);
            $i = 0;

            while($i < $leng_final){
              $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

              $sale_stock = $tb_item_bulanan->sale_stock;
              $sale_amount = $tb_item_bulanan->sale_amount;

              $penjualandetail2 = PenjualanDetail::on($konek)
              ->join('penjualan', 'penjualan_detail.no_penjualan', '=', 'penjualan.no_penjualan')
              ->where('penjualan_detail.kode_produk',$data[$i]['kode_produk'])
              ->where('penjualan_detail.partnumber',$data[$i]['partnumber'])
              ->where('penjualan.status','POSTED')
              ->where('penjualan.kode_lokasi',$lokasi)
              ->whereMonth('penjualan.tanggal_penjualan', $bulan)
              ->whereYear('penjualan.tanggal_penjualan', $tahun)
              ->orderBy('penjualan.tanggal_penjualan','asc')
              ->get();

              if($penjualandetail2 != null){
                $total_qty = 0;
                $total_harga = 0;
                $grand_total = 0;

                foreach ($penjualandetail2 as $row){
                  $total_qty += $row->qty;
                  $subtotal = $row->harga * $row->qty;
                  $total_harga += $subtotal;
                  $grand_total = number_format($total_harga,2,",",".");
                }  

                if($total_qty != $sale_stock){
                  $message = [
                      'success' => false,
                      'title' => 'Simpan',
                      'message' => 'Total QTY Penjualan Salah. Cek Produk '.$data[$i]['kode_produk'].' pada no. penjualan '.$data[$i]['no_penjualan'].'!',
                  ];
                  return response()->json($message);
                }

                $cek_amount = abs(bcsub($total_harga, $sale_amount, 2));

                // if($total_harga != $out_amount){
                if($cek_amount > 15){
                  $message = [
                      'success' => false,
                      'title' => 'Simpan',
                      'message' => 'Total Harga Penjualan Salah. Cek Produk '.$data[$i]['kode_produk'].' pada no. penjualan '.$data[$i]['no_penjualan'].'!',
                  ];
                  return response()->json($message);
                }
              }

              $i++;
            }

            $message = [
              'success' => true,
              'title' => 'Simpan',
              'message' => 'Data Penjualan Benar.',
            ];
            return response()->json($message);
        }
        else if($index == 2){
          $leng_final = floor($leng/3);
          $leng_final2 = $leng - $leng_final;
          $i = $leng_final;

          while($i < $leng_final2){
              $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

              $sale_stock = $tb_item_bulanan->sale_stock;
              $sale_amount = $tb_item_bulanan->sale_amount;

              $penjualandetail2 = PenjualanDetail::on($konek)
              ->join('penjualan', 'penjualan_detail.no_penjualan', '=', 'penjualan.no_penjualan')
              ->where('penjualan_detail.kode_produk',$data[$i]['kode_produk'])
              ->where('penjualan_detail.partnumber',$data[$i]['partnumber'])
              ->where('penjualan.status','POSTED')
              ->where('penjualan.kode_lokasi',$lokasi)
              ->whereMonth('penjualan.tanggal_penjualan', $bulan)
              ->whereYear('penjualan.tanggal_penjualan', $tahun)
              ->orderBy('penjualan.tanggal_penjualan','asc')
              ->get();

              if($penjualandetail2 != null){
                $total_qty = 0;
                $total_harga = 0;
                $grand_total = 0;

                foreach ($penjualandetail2 as $row){
                  $total_qty += $row->qty;
                  $subtotal = $row->harga * $row->qty;
                  $total_harga += $subtotal;
                  $grand_total = number_format($total_harga,2,",",".");
                }  

                if($total_qty != $sale_stock){
                  $message = [
                      'success' => false,
                      'title' => 'Simpan',
                      'message' => 'Total QTY Penjualan Salah. Cek Produk '.$data[$i]['kode_produk'].' pada no. penjualan '.$data[$i]['no_penjualan'].'!',
                  ];
                  return response()->json($message);
                }

                $cek_amount = abs(bcsub($total_harga, $sale_amount, 2));
                // if($total_harga != $out_amount){
                if($cek_amount > 15){
                  $message = [
                      'success' => false,
                      'title' => 'Simpan',
                      'message' => 'Total Harga Penjualan Salah. Cek Produk '.$data[$i]['kode_produk'].' pada no. penjualan '.$cek_amount.'!',
                  ];
                  return response()->json($message);
                }
              }

              $i++;
          }

          $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => 'Data Penjualan Benar.',
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
              $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

              $sale_stock = $tb_item_bulanan->sale_stock;
              $sale_amount = $tb_item_bulanan->sale_amount;

              $penjualandetail2 = PenjualanDetail::on($konek)
              ->join('penjualan', 'penjualan_detail.no_penjualan', '=', 'penjualan.no_penjualan')
              ->where('penjualan_detail.kode_produk',$data[$i]['kode_produk'])
              ->where('penjualan_detail.partnumber',$data[$i]['partnumber'])
              ->where('penjualan.status','POSTED')
              ->where('penjualan.kode_lokasi',$lokasi)
              ->whereMonth('penjualan.tanggal_penjualan', $bulan)
              ->whereYear('penjualan.tanggal_penjualan', $tahun)
              ->orderBy('penjualan.tanggal_penjualan','asc')
              ->get();

              if($penjualandetail2 != null){
                $total_qty = 0;
                $total_harga = 0;
                $grand_total = 0;

                foreach ($penjualandetail2 as $row){
                  $total_qty += $row->qty;
                  $subtotal = $row->harga * $row->qty;
                  $total_harga += $subtotal;
                  $grand_total = number_format($total_harga,2,",",".");
                }  

                if($total_qty != $sale_stock){
                  $message = [
                      'success' => false,
                      'title' => 'Simpan',
                      'message' => 'Total QTY Penjualan Salah. Cek Produk '.$data[$i]['kode_produk'].' pada no. penjualan '.$data[$i]['no_penjualan'].'!',
                  ];
                  return response()->json($message);
                }

                $cek_amount = abs(bcsub($total_harga, $sale_amount, 2));
                // if($total_harga != $out_amount){
                if($cek_amount > 15){
                  $message = [
                      'success' => false,
                      'title' => 'Simpan',
                      'message' => 'Total Harga Penjualan Salah. Cek Produk '.$data[$i]['kode_produk'].' pada no. penjualan '.$data[$i]['no_penjualan'].'!',
                  ];
                  return response()->json($message);
                }
              }

              $i++;
          }

          $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => 'Data Penjualan Benar.',
          ];
          return response()->json($message);
        }
    }
}
