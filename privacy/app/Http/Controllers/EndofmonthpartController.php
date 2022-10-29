<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\tb_akhir_bulan;
use App\Models\tb_item_bulanan;
use App\Models\MasterLokasi;
use PDF;
use Excel;
use DB;
use Carbon;

class EndofmonthpartController extends Controller
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
        $create_url = route('endofmonth.create');
        $tanggal = tb_akhir_bulan::on($konek)->where('status_periode','Open')->pluck('periode','periode');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        $tbakhir = tb_akhir_bulan::on($konek)->where('status_periode','Open')->first();
        $item = tb_item_bulanan::on($konek)->where('periode', $tbakhir->periode)->get();
        $total = count($item);
        $part = array();
        $a = 0;

        if ($total > 500) {
            $jumlah = $total / 500;
            $parted = floor($jumlah);

            for ($i = 0; $i <= $parted; $i++) { 
              $a += 1;
              $part[] = 'Part '.$a;
            }
        }

        $level = auth()->user()->level;
        if($level == 'superadministrator' || $level == 'user_rince' || $level == 'user_thomas'){
            return view('admin.endofmonthpart.index',compact('part','create_url','tanggal','period', 'nama_lokasi'));
        }
        else{
            return view('admin.endofmonth.blank',compact('create_url','tanggal','period', 'nama_lokasi'));
        }


    }

    public function change(Request $request)
    {   
      $konek = self::konek();
      $tanggal_tutup = $request->tanggal_awal;
      $tanggal_buka = $request->tanggal_akhir;
      $part = $request->part;

      $tahun_tutup = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal_tutup)->year;
      $bulan_tutup = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal_tutup)->month;
      // dd($tanggal_buka);

      $tahun_buka = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal_buka)->year;
      $bulan_buka = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal_buka)->month;
      $hari_buka = '01';
      // dd($hari_buka);

      $tabel_tutup = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_tutup)->whereYear('periode', $tahun_tutup)->first();
      $tabel_buka = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_buka)->whereYear('periode', $tahun_buka)->first();
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
                      
          $periode_baru = tb_akhir_bulan::on($konek)->create($tabel_baru);
          $message = [
              'success' => true,
              'title' => 'Simpan',
              'message' => 'Open Month: '.$tanggal_buka, 'Berhasil!',
          ];
          return response()->json($message);
      } else if($tabel_buka == null && $tabel_tutup != null) {
          $tabel_baru = [
              'periode' => $tanggal_buka,
              'status_periode' => $status_baru,
              'reopen_status' => $reopen_baru,
          ];

          $get_bulanan = tb_item_bulanan::on($konek)->with('produk')->whereMonth('periode', $bulan_tutup)->whereYear('periode', $tahun_tutup)->get();

          $bagian = 0;
          $total = count($get_bulanan);

          if ($total > 500) {
              $jumlah = $total / 500;
              $parted = floor($jumlah);

              for ($i = 0; $i <= $parted; $i++) { 
                $bagian += 1;
              }
          }

          $parts = $part + 1;
          $urutan = $bagian - $parts;
          $angka = 0;

          if ($urutan == 0) {
              $lewat = ($parts - 1) * 500;
              $sisa = $total - $lewat;
          }else {
              $lewat = ($parts - 1) * 500;
              $sisa = 500;
          }

          $tb_item_bulanan = tb_item_bulanan::on($konek)->with('produk')->where('periode', $tanggal_tutup)->skip($lewat)->take($sisa)->get();

            foreach ($tb_item_bulanan as $rowdata){

                $tb_item_bulanan1 = tb_item_bulanan::on($konek)->where('kode_produk',$rowdata->kode_produk)->where('partnumber',$rowdata->partnumber)->where('kode_lokasi',$rowdata->kode_lokasi)->where('kode_company',$rowdata->kode_company)->where('no_mesin',$rowdata->no_mesin)->where('periode', $tanggal_tutup)->first();
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
                ];
                
                $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$rowdata->kode_produk)->where('partnumber',$rowdata->partnumber)->where('kode_lokasi',$rowdata->kode_lokasi)->where('kode_company',$rowdata->kode_company)->where('no_mesin',$rowdata->no_mesin)->where('periode', $tanggal_buka)->first();

                if ($tb_item_bulanan2 == null){
                    $update_item_bulanan = tb_item_bulanan::on($konek)->create($update_baru);
                }
            }
                
            $get_bulanan2 = tb_item_bulanan::on($konek)->with('produk')->whereMonth('periode', $bulan_buka)->whereYear('periode', $tahun_buka)->get();
            $total2 = count($get_bulanan2);

            if ($total == $total2) {
                $periode_baru = tb_akhir_bulan::on($konek)->create($tabel_baru);
            }

            $status = $tabel_tutup->status_periode;
            $re_status = $tabel_tutup->reopen_status;

                if($status == 'Open'){
                    if ($total == $total2) {
                      $tabel_tutup->status_periode = "Closed";
                      $tabel_tutup->save(); 
                    }

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
              $cek_periode = tb_akhir_bulan::on($konek)->where('status_periode','Disable')->first();

              $status = $tabel_tutup->status_periode;
              $re_status = $tabel_tutup->reopen_status;
              // dd($re_status);
              if($re_status == 'true'){
                  $tabel_tutup->reopen_status = "false";
                  $cek_periode->status_periode = "Open";

                  $tabel_tutup->save();
                  $cek_periode->save(); 

                  $message = [
                  'success' => true,
                  'title' => 'Simpan',
                  'message' => 'End Of Month:'.$tanggal_buka, 'Berhasil!',
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
