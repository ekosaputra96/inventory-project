<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Checkpemakaian;
use App\Models\Pemakaian;
use App\Models\PemakaianDetail;
use App\Models\tb_item_bulanan;
use App\Models\tb_akhir_bulan;
use App\Models\MasterLokasi;
use App\Models\Company;
use App\Models\Konversi;
use PDF;
use Excel;
use DB;
use Carbon;

class CheckpemakaianController extends Controller
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
        $create_url = route('checkpemakaian.create');
        $tanggal = tb_akhir_bulan::on($konek)->where('status_periode','Open')->pluck('periode','periode');
        $periode = tb_akhir_bulan::on($konek)->pluck('periode','periode');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        $level = auth()->user()->level;
        if($level == 'superadministrator' || $level == 'user_rince' || $level == 'user_herry'){
            return view('admin.checkpemakaian.index',compact('create_url','tanggal','period', 'nama_lokasi','periode'));
        }
        else{
            return view('admin.checkpemakaian.blank',compact('create_url','tanggal','period', 'nama_lokasi','periode'));
        }


    }

    public function change(Request $request)
    {   
        $konek = self::konek();
        $lokasi = auth()->user()->kode_lokasi;

        $periode = $request->periode;

        $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$periode)->year;
        $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$periode)->month;

        $pemakaiandetail = PemakaianDetail::on($konek)
          ->select('pemakaian_detail.*','pemakaian.no_pemakaian','pemakaian.tanggal_pemakaian')
          ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
          ->where('pemakaian.status','POSTED')
          ->where('pemakaian.kode_lokasi',$lokasi)
          ->whereMonth('pemakaian.tanggal_pemakaian', $bulan)
          ->whereYear('pemakaian.tanggal_pemakaian', $tahun)
          ->orderBy('pemakaian_detail.kode_produk','asc')
          ->get();
        // dd($pemakaiandetail);

        $leng = $pemakaiandetail->count();
        $data = array();

        if(!empty($pemakaiandetail)){
          foreach ($pemakaiandetail as $rowdata){
            $no_pemakaian = $rowdata->no_pemakaian;
            $kode_produk = $rowdata->kode_produk;
            $partnumber = $rowdata->partnumber;

            $data[] = array(
              'no_pemakaian'=>$no_pemakaian,
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

              $out_stock = $tb_item_bulanan->out_stock;
              $out_amount = $tb_item_bulanan->out_amount;

              $pemakaiandetail2 = PemakaianDetail::on($konek)
              ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
              ->where('pemakaian_detail.kode_produk',$data[$i]['kode_produk'])
              ->where('pemakaian_detail.partnumber',$data[$i]['partnumber'])
              ->where('pemakaian.status','POSTED')
              ->where('pemakaian.kode_lokasi',$lokasi)
              ->whereMonth('pemakaian.tanggal_pemakaian', $bulan)
              ->whereYear('pemakaian.tanggal_pemakaian', $tahun)
              ->orderBy('pemakaian.tanggal_pemakaian','asc')
              ->get();

              if($pemakaiandetail2 != null){
                $total_qty = 0;
                $total_harga = 0;
                $grand_total = 0;

                foreach ($pemakaiandetail2 as $row){
                  $total_qty += $row->qty;
                  $subtotal = $row->harga * $row->qty;
                  $total_harga += $subtotal;
                  $grand_total = number_format($total_harga,2,",",".");
                }  

                if($total_qty != $out_stock){
                  $message = [
                      'success' => false,
                      'title' => 'Simpan',
                      'message' => 'Total QTY Pemakaian Salah. Cek Produk '.$data[$i]['kode_produk'].' pada no. pemakaian '.$data[$i]['no_pemakaian'].'!',
                  ];
                  return response()->json($message);
                }

                $cek_amount = abs(bcsub($total_harga, $out_amount, 2));

                // if($total_harga != $out_amount){
                if($cek_amount > 15){
                  $message = [
                      'success' => false,
                      'title' => 'Simpan',
                      'message' => 'Total Harga Pemakaian Salah. Cek Produk '.$data[$i]['kode_produk'].' pada no. pemakaian '.$data[$i]['no_pemakaian'].'!',
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
              $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

              $out_stock = $tb_item_bulanan->out_stock;
              $out_amount = $tb_item_bulanan->out_amount;

              $pemakaiandetail2 = PemakaianDetail::on($konek)
              ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
              ->where('pemakaian_detail.kode_produk',$data[$i]['kode_produk'])
              ->where('pemakaian_detail.partnumber',$data[$i]['partnumber'])
              ->where('pemakaian.status','POSTED')
              ->where('pemakaian.kode_lokasi',$lokasi)
              ->whereMonth('pemakaian.tanggal_pemakaian', $bulan)
              ->whereYear('pemakaian.tanggal_pemakaian', $tahun)
              ->orderBy('pemakaian.tanggal_pemakaian','asc')
              ->get();

              if($pemakaiandetail2 != null){
                $total_qty = 0;
                $total_harga = 0;
                $grand_total = 0;

                foreach ($pemakaiandetail2 as $row){
                  $total_qty += $row->qty;
                  $subtotal = $row->harga * $row->qty;
                  $total_harga += $subtotal;
                  $grand_total = number_format($total_harga,2,",",".");
                }  

                if($total_qty != $out_stock){
                  $message = [
                      'success' => false,
                      'title' => 'Simpan',
                      'message' => 'Total QTY Pemakaian Salah. Cek Produk '.$data[$i]['kode_produk'].' pada no. pemakaian '.$data[$i]['no_pemakaian'].'!',
                  ];
                  return response()->json($message);
                }

                $cek_amount = abs(bcsub($total_harga, $out_amount, 2));
                // if($total_harga != $out_amount){
                if($cek_amount > 15){
                  $message = [
                      'success' => false,
                      'title' => 'Simpan',
                      'message' => 'Total Harga Pemakaian Salah. Cek Produk '.$data[$i]['kode_produk'].' pada no. pemakaian '.$cek_amount.'!',
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
              $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

              $out_stock = $tb_item_bulanan->out_stock;
              $out_amount = $tb_item_bulanan->out_amount;

              $pemakaiandetail2 = PemakaianDetail::on($konek)
              ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
              ->where('pemakaian_detail.kode_produk',$data[$i]['kode_produk'])
              ->where('pemakaian_detail.partnumber',$data[$i]['partnumber'])
              ->where('pemakaian.status','POSTED')
              ->where('pemakaian.kode_lokasi',$lokasi)
              ->whereMonth('pemakaian.tanggal_pemakaian', $bulan)
              ->whereYear('pemakaian.tanggal_pemakaian', $tahun)
              ->orderBy('pemakaian.tanggal_pemakaian','asc')
              ->get();

              if($pemakaiandetail2 != null){
                $total_qty = 0;
                $total_harga = 0;
                $grand_total = 0;

                foreach ($pemakaiandetail2 as $row){
                  $total_qty += $row->qty;
                  $subtotal = $row->harga * $row->qty;
                  $total_harga += $subtotal;
                  $grand_total = number_format($total_harga,2,",",".");
                }  

                if($total_qty != $out_stock){
                  $message = [
                      'success' => false,
                      'title' => 'Simpan',
                      'message' => 'Total QTY Pemakaian Salah. Cek Produk '.$data[$i]['kode_produk'].' pada no. pemakaian '.$data[$i]['no_pemakaian'].'!',
                  ];
                  return response()->json($message);
                }

                $cek_amount = abs(bcsub($total_harga, $out_amount, 2));
                // if($total_harga != $out_amount){
                if($cek_amount > 15){
                  $message = [
                      'success' => false,
                      'title' => 'Simpan',
                      'message' => 'Total Harga Pemakaian Salah. Cek Produk '.$data[$i]['kode_produk'].' pada no. pemakaian '.$data[$i]['no_pemakaian'].'!',
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
    }
}
