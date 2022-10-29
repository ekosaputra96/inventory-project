<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Checkpenerimaan;
use App\Models\Penerimaan;
use App\Models\PenerimaanDetail;
use App\Models\tb_item_bulanan;
use App\Models\tb_akhir_bulan;
use App\Models\MasterLokasi;
use App\Models\Company;
use App\Models\Konversi;
use PDF;
use Excel;
use DB;
use Carbon;

class CheckpenerimaanController extends Controller
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
        $create_url = route('checkpenerimaan.create');
        $tanggal = tb_akhir_bulan::on($konek)->where('status_periode','Open')->pluck('periode','periode');
        $periode = tb_akhir_bulan::on($konek)->pluck('periode','periode');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        $level = auth()->user()->level;
        if($level == 'superadministrator' || $level == 'user_rince' || $level == 'user_herry'){
            return view('admin.checkpenerimaan.index',compact('create_url','tanggal','period', 'nama_lokasi','periode'));
        }
        else{
            return view('admin.checkpenerimaan.blank',compact('create_url','tanggal','period', 'nama_lokasi','periode'));
        }


    }

    public function change(Request $request)
    {   
        $konek = self::konek();
        $lokasi = auth()->user()->kode_lokasi;

        $periode = $request->periode;

        $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$periode)->year;
        $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$periode)->month;

        $penerimaandetail = PenerimaanDetail::on($konek)
          ->select('penerimaan_detail.*','penerimaan.no_penerimaan','penerimaan.tanggal_penerimaan')
          ->join('penerimaan', 'penerimaan_detail.no_penerimaan', '=', 'penerimaan.no_penerimaan')
          ->whereMonth('penerimaan.tanggal_penerimaan', $bulan)
          ->whereYear('penerimaan.tanggal_penerimaan', $tahun)
          ->where('penerimaan.status','POSTED')
          ->where('penerimaan.kode_lokasi',$lokasi)
          ->orderBy('penerimaan_detail.kode_produk','asc')
          ->get();
        // dd($penerimaandetail);

        $leng = $penerimaandetail->count();
        $data = array();

        if(!empty($penerimaandetail)){
          foreach ($penerimaandetail as $rowdata){
            $no_penerimaan = $rowdata->no_penerimaan;
            $kode_produk = $rowdata->kode_produk;
            $partnumber = $rowdata->partnumber;

            $data[] = array(
              'no_penerimaan'=>$no_penerimaan,
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

              $in_stock = $tb_item_bulanan->in_stock;
              $in_amount = round($tb_item_bulanan->in_amount);

              $penerimaandetail2 = PenerimaanDetail::on($konek)
              ->join('penerimaan', 'penerimaan_detail.no_penerimaan', '=', 'penerimaan.no_penerimaan')
              ->where('penerimaan_detail.kode_produk',$data[$i]['kode_produk'])
              ->where('penerimaan_detail.partnumber',$data[$i]['partnumber'])
              ->where('penerimaan.status','POSTED')
              ->where('penerimaan.kode_lokasi',$lokasi)
              ->whereMonth('penerimaan.tanggal_penerimaan', $bulan)
              ->whereYear('penerimaan.tanggal_penerimaan', $tahun)
              ->orderBy('penerimaan.tanggal_penerimaan','asc')
              ->get();

              if($penerimaandetail2 != null){
                $total_qty = 0;
                $total_harga = 0;
                $grand_total = 0;

                foreach ($penerimaandetail2 as $row){
                  $konversi = Konversi::on($konek)->where('kode_produk',$row->kode_produk)->where('kode_satuan',$row->kode_satuan)->first();

                  $total_qty += $row->qty * $konversi->nilai_konversi;
                  $subtotal = ($row->harga + $row->landedcost) * $row->qty;
                  $total_harga += round($subtotal);
                  $grand_total = number_format($total_harga,2,",",".");
                }  

                if($total_qty != $in_stock){
                  $message = [
                      'success' => false,
                      'title' => 'Simpan',
                      'message' => 'Total QTY Penerimaan Salah. Cek Produk '.$data[$i]['kode_produk'].' pada no. penerimaan '.$data[$i]['no_penerimaan'].'!',
                  ];
                  return response()->json($message);
                }

                if($total_harga != $in_amount){
                  $message = [
                      'success' => false,
                      'title' => 'Simpan',
                      'message' => 'Total Harga Penerimaan Salah. Cek Produk '.$data[$i]['kode_produk'].' pada no. penerimaan '.$data[$i]['no_penerimaan'].'!',
                  ];
                  return response()->json($message);
                }
              }

              $i++;
            }

            $message = [
              'success' => true,
              'title' => 'Simpan',
              'message' => 'Data Penerimaan Benar.',
            ];
            return response()->json($message);
        }
        else if($index == 2){
          $leng_final = floor($leng/3);
          $leng_final2 = $leng - $leng_final;
          $i = $leng_final;

          while($i < $leng_final2){
              $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

              $in_stock = $tb_item_bulanan->in_stock;
              $in_amount = round($tb_item_bulanan->in_amount);

              $penerimaandetail2 = PenerimaanDetail::on($konek)
              ->join('penerimaan', 'penerimaan_detail.no_penerimaan', '=', 'penerimaan.no_penerimaan')
              ->where('penerimaan_detail.kode_produk',$data[$i]['kode_produk'])
              ->where('penerimaan_detail.partnumber',$data[$i]['partnumber'])
              ->where('penerimaan.status','POSTED')
              ->where('penerimaan.kode_lokasi',$lokasi)
              ->whereMonth('penerimaan.tanggal_penerimaan', $bulan)
              ->whereYear('penerimaan.tanggal_penerimaan', $tahun)
              ->orderBy('penerimaan.tanggal_penerimaan','asc')
              ->get();

              if($penerimaandetail2 != null){
                $total_qty = 0;
                $total_harga = 0;
                $grand_total = 0;

                foreach ($penerimaandetail2 as $row){
                  $konversi = Konversi::on($konek)->where('kode_produk',$row->kode_produk)->where('kode_satuan',$row->kode_satuan)->first();

                  $total_qty += $row->qty * $konversi->nilai_konversi;
                  $subtotal = ($row->harga + $row->landedcost) * $row->qty;
                  $total_harga += round($subtotal);
                  $grand_total = number_format($total_harga,2,",",".");
                }  

                if($total_qty != $in_stock){
                  $message = [
                      'success' => false,
                      'title' => 'Simpan',
                      'message' => 'Total QTY Penerimaan Salah. Cek Produk '.$data[$i]['kode_produk'].' pada no. penerimaan '.$data[$i]['no_penerimaan'].'!',
                  ];
                  return response()->json($message);
                }

                if($total_harga != $in_amount){
                  $message = [
                      'success' => false,
                      'title' => 'Simpan',
                      'message' => 'Total Harga Penerimaan Salah. Cek Produk '.$data[$i]['kode_produk'].' pada no. penerimaan '.$data[$i]['no_penerimaan'].'!',
                  ];
                  return response()->json($message);
                }
              }

              $i++;
          }

          $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => 'Data Penerimaan Benar.',
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

              $in_stock = $tb_item_bulanan->in_stock;
              $in_amount = round($tb_item_bulanan->in_amount);

              $penerimaandetail2 = PenerimaanDetail::on($konek)
              ->join('penerimaan', 'penerimaan_detail.no_penerimaan', '=', 'penerimaan.no_penerimaan')
              ->where('penerimaan_detail.kode_produk',$data[$i]['kode_produk'])
              ->where('penerimaan_detail.partnumber',$data[$i]['partnumber'])
              ->where('penerimaan.status','POSTED')
              ->where('penerimaan.kode_lokasi',$lokasi)
              ->whereMonth('penerimaan.tanggal_penerimaan', $bulan)
              ->whereYear('penerimaan.tanggal_penerimaan', $tahun)
              ->orderBy('penerimaan.tanggal_penerimaan','asc')
              ->get();

              if($penerimaandetail2 != null){
                $total_qty = 0;
                $total_harga = 0;
                $grand_total = 0;

                foreach ($penerimaandetail2 as $row){
                  $konversi = Konversi::on($konek)->where('kode_produk',$row->kode_produk)->where('kode_satuan',$row->kode_satuan)->first();

                  $total_qty += $row->qty * $konversi->nilai_konversi;
                  $subtotal = ($row->harga + $row->landedcost) * $row->qty;
                  $total_harga += round($subtotal);
                  $grand_total = number_format($total_harga,2,",",".");
                }  

                if($total_qty != $in_stock){
                  $message = [
                      'success' => false,
                      'title' => 'Simpan',
                      'message' => 'Total QTY Penerimaan Salah. Cek Produk '.$data[$i]['kode_produk'].' pada no. penerimaan '.$data[$i]['no_penerimaan'].'!',
                  ];
                  return response()->json($message);
                }

                if($total_harga != $in_amount){
                  $message = [
                      'success' => false,
                      'title' => 'Simpan',
                      'message' => 'Total Harga Penerimaan Salah. Cek Produk '.$data[$i]['kode_produk'].' pada no. penerimaan '.$data[$i]['no_penerimaan'].'!',
                  ];
                  return response()->json($message);
                }
              }

              $i++;
          }

          $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => 'Data Penerimaan Benar.',
          ];
          return response()->json($message);
        }
    }
}
