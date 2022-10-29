<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Checkmonthly;
use App\Models\tb_item_bulanan;
use App\Models\tb_akhir_bulan;
use App\Models\MasterLokasi;
use App\Models\Company;
use App\Models\Penerimaan;
use App\Models\PenerimaanDetail;
use App\Models\Pemakaian;
use App\Models\PemakaianDetail;
use App\Models\Transfer;
use App\Models\TransferDetail;
use App\Models\TransferIn;
use App\Models\TransferInDetail;
use App\Models\Adjustment;
use App\Models\AdjustmentDetail;
use App\Models\Opname;
use App\Models\OpnameDetail;
use App\Models\Returpembelian;
use App\Models\ReturpembelianDetail;
use App\Models\ReturPemakaian;
use App\Models\ReturpemakaianDetail;
use App\Models\Produk;
use PDF;
use Excel;
use DB;
use Carbon;

class CheckmonthlyController extends Controller
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
        $create_url = route('checkmonthly.create');
        $tanggal = tb_akhir_bulan::on($konek)->where('status_periode','Open')->pluck('periode','periode');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;
        
        $Lokasi = MasterLokasi::pluck('kode_lokasi','kode_lokasi');

        $level = auth()->user()->level;
        if($level == 'superadministrator' || $level == 'user_rince' || $level == 'user_herry'){
            return view('admin.checkmonthly.index',compact('create_url','tanggal','period', 'nama_lokasi','Lokasi'));
        }
        else{
            return view('admin.checkmonthly.blank',compact('create_url','tanggal','period', 'nama_lokasi','Lokasi'));
        }


    }

    public function change(Request $request)
    {   
        $konek = self::konek();
        $kode_lokasi = auth()->user()->kode_lokasi;

        $tanggal1 = '01';
        $kode_company = auth()->user()->kode_company;
        $bulan1 = $request->month;
        $tahun1 = $request->year;
        $lokasi = $request->lokasi;
        
        $bln = $request->month - 1;
        $tanggals = Carbon\Carbon::createFromDate($tahun1, $bln, $tanggal1)->toDateString();
        $bulanbeda = Carbon\Carbon::parse($tanggals)->format('m');
        
        $tanggal_baru1 = Carbon\Carbon::createFromDate($tahun1, $bulan1, $tanggal1)->toDateString();
        $bulans = Carbon\Carbon::parse($tanggal_baru1)->format('m');

        $tb_item_bulanan = tb_item_bulanan::on($konek)->where('periode', $tanggal_baru1)->where('kode_lokasi', $lokasi)->orderBy('kode_produk','asc')->get();

        $leng = $tb_item_bulanan->count();
        $data = array();
        
        foreach ($tb_item_bulanan as $rowdata){
            $kode_produk = $rowdata->kode_produk;
            $partnumber = $rowdata->partnumber;
            $begin_stock = $rowdata->begin_stock;
            $begin_amount = $rowdata->begin_amount;
            $lokasi = $rowdata->kode_lokasi;
            $thn = substr($tahun1, 2,2);
            
            $produk = Produk::on($konek)->find($rowdata->kode_produk);
            
            if ($produk->tipe_produk != 'Serial' && $produk->kode_kategori != 'BAN') {

                $penerimaan = PenerimaanDetail::on($konek)->select(DB::raw('SUM(penerimaan_detail.qty) as qty'),DB::raw('SUM(penerimaan_detail.qty*penerimaan_detail.harga) as amount'))->join('penerimaan','penerimaan.no_penerimaan','=','penerimaan_detail.no_penerimaan')->where('penerimaan_detail.kode_produk', $kode_produk)->whereMonth('penerimaan.tanggal_penerimaan', $bulan1)->whereYear('penerimaan.tanggal_penerimaan', $tahun1)->where('penerimaan.kode_lokasi', $lokasi)->first();
                $in_qty = $penerimaan->qty;
                $in_amount = $penerimaan->amount;

                $pemakaian = PemakaianDetail::on($konek)->select(DB::raw('SUM(pemakaian_detail.qty) as qty'),DB::raw('SUM(pemakaian_detail.qty*pemakaian_detail.harga) as amount'))->join('pemakaian','pemakaian.no_pemakaian','=','pemakaian_detail.no_pemakaian')->where('pemakaian_detail.kode_produk', $kode_produk)->whereMonth('pemakaian.tanggal_pemakaian', $bulan1)->whereYear('pemakaian.tanggal_pemakaian', $tahun1)->where('pemakaian.kode_lokasi', $lokasi)->first();
                $out_qty = $pemakaian->qty;
                $out_amount = $pemakaian->amount;
    
                $adjustment = AdjustmentDetail::on($konek)->select(DB::raw('SUM(adjustments_detail.qty) as qty'),DB::raw('SUM(adjustments_detail.qty*adjustments_detail.harga) as amount'))->join('adjustments','adjustments.no_penyesuaian','=','adjustments_detail.no_penyesuaian')->where('adjustments_detail.kode_produk', $kode_produk)->whereMonth('adjustments.tanggal', $bulan1)->whereYear('adjustments.tanggal', $tahun1)->where('adjustments.kode_lokasi', $lokasi)->first();
                $adj_qty = $adjustment->qty;
                $adj_amount = $adjustment->amount;
    
                $opname = OpnameDetail::on($konek)->select(DB::raw('SUM(opname_detail.stock_opname) as qty'),DB::raw('SUM(opname_detail.stock_opname*opname_detail.hpp) as amount'))->join('opname','opname.no_opname','=','opname_detail.no_opname')->where('opname_detail.kode_produk', $kode_produk)->whereMonth('opname.tanggal_opname', $bulan1)->whereYear('opname.tanggal_opname', $tahun1)->where('opname.kode_lokasi', $lokasi)->first();
                $opn_qty = $opname->qty;
                $opn_amount = $opname->amount;
                
                $transfer = TransferInDetail::on($konek)->select(DB::raw('SUM(transfer_in_detail.qty) as qty'),DB::raw('SUM(transfer_in_detail.qty*transfer_in_detail.hpp) as amount'))->join('transfer_in', 'transfer_in.no_trf_in','=','transfer_in_detail.no_trf_in')->where('transfer_in_detail.kode_produk', $kode_produk)->where('transfer_in.no_transfer','like',$kode_company.'TRO'.$bulans.$thn.'%')->whereMonth('transfer_in.tanggal_transfer', $bulan1)->whereYear('transfer_in.tanggal_transfer', $tahun1)->where('transfer_in.kode_dari',$lokasi)->first();
                $transferbeda = TransferInDetail::on($konek)->select(DB::raw('SUM(transfer_in_detail.qty) as qty'),DB::raw('SUM(transfer_in_detail.qty*transfer_in_detail.hpp) as amount'))->join('transfer_in', 'transfer_in.no_trf_in','=','transfer_in_detail.no_trf_in')->where('transfer_in_detail.kode_produk', $kode_produk)->where('transfer_in.no_transfer','like',$kode_company.'TRO'.$bulanbeda.$thn.'%')->whereMonth('transfer_in.tanggal_transfer', $bulan1)->whereYear('transfer_in.tanggal_transfer', $tahun1)->where('transfer_in.kode_dari',$lokasi)->first();
                
                $trfout_qty = $transfer->qty + $transferbeda->qty;
                $trfout_amount = $transfer->amount + $transferbeda->amount;
    
                $transferin = TransferInDetail::on($konek)->select(DB::raw('SUM(transfer_in_detail.qty) as qty'),DB::raw('SUM(transfer_in_detail.qty*transfer_in_detail.hpp) as amount'))->join('transfer_in', 'transfer_in.no_trf_in','=','transfer_in_detail.no_trf_in')->where('transfer_in_detail.kode_produk', $kode_produk)->whereMonth('transfer_in.tanggal_transfer', $bulan1)->whereYear('transfer_in.tanggal_transfer', $tahun1)->where('transfer_in.kode_lokasi',$lokasi)->first();
                $trfin_qty = $transferin->qty;
                $trfin_amount = $transferin->amount;
    
                $returpembelian = ReturpembelianDetail::on($konek)->select(DB::raw('SUM(returpembelian_detail.qty) as qty'),DB::raw('SUM(returpembelian_detail.qty*returpembelian_detail.harga) as amount'))->join('retur_pembelian', 'retur_pembelian.no_returpembelian','=','returpembelian_detail.no_returpembelian')->where('returpembelian_detail.kode_produk', $kode_produk)->whereMonth('retur_pembelian.tanggal_returpembelian', $bulan1)->whereYear('retur_pembelian.tanggal_returpembelian', $tahun1)->where('retur_pembelian.kode_lokasi', $lokasi)->first();
                $returbeli_qty = $returpembelian->qty;
                $returbeli_amount = $returpembelian->amount;
    
                $returpemakaian = ReturpemakaianDetail::on($konek)->select(DB::raw('SUM(retur_pemakaian_detail.qty) as qty'),DB::raw('SUM(retur_pemakaian_detail.qty*retur_pemakaian_detail.harga) as amount'))->join('retur_pemakaian','retur_pemakaian.no_retur_pemakaian','=','retur_pemakaian_detail.no_retur_pemakaian')->where('retur_pemakaian_detail.kode_produk', $kode_produk)->whereMonth('retur_pemakaian.tgl_retur_pemakaian', $bulan1)->whereYear('retur_pemakaian.tgl_retur_pemakaian', $tahun1)->where('retur_pemakaian.kode_lokasi', $lokasi)->first();
                $returpakai_qty = $returpemakaian->qty;
                $returpakai_amount = $returpemakaian->amount;

                if ($bulan1 != 1){
                    $bulan_sebelum = tb_item_bulanan::on($konek)->where('kode_lokasi', $lokasi)->whereMonth('periode', $bulan1 -1)->whereYear('periode', $tahun1)->where('kode_produk', $kode_produk)->first();
    
                    if ($bulan_sebelum != null){
                        $begin_stock = $bulan_sebelum->ending_stock;
                        $begin_amount = $bulan_sebelum->ending_amount;
                    }else {
                        $begin_stock = 0;
                        $begin_amount = 0;
                    }
                }else {
                    $bulan_sebelum = tb_item_bulanan::on($konek)->where('kode_lokasi', $lokasi)->whereMonth('periode', $bulan1)->whereYear('periode', $tahun1)->where('kode_produk', $kode_produk)->first();
                    if ($bulan_sebelum != null){
                        $begin_stock = $bulan_sebelum->begin_stock;
                        $begin_amount = $bulan_sebelum->begin_amount;
                    }else {
                        $begin_stock = 0;
                        $begin_amount = 0;
                    }
                }

                $endstok = $begin_stock + $in_qty - $out_qty + $adj_qty + $opn_qty - $trfout_qty + $trfin_qty - $returbeli_qty + $returpakai_qty;
                $endamount = $begin_amount + $in_amount - $out_amount + $adj_amount + $opn_amount - $trfout_amount + $trfin_amount - $returbeli_amount + $returpakai_amount;

                if ($endstok > 0){
                    $hpp = $endamount / $endstok;
                }else {
                    $hpp = $rowdata->hpp;
                }

                $simpan_update = [
                      'begin_stock'=>$begin_stock,
                      'begin_amount'=>$begin_amount,
                      'in_stock'=>$in_qty,
                      'in_amount'=>$in_amount,
                      'out_stock'=>$out_qty,
                      'out_amount'=>$out_amount,
                      'trf_in'=>$trfin_qty,
                      'trf_in_amount'=>$trfin_amount,
                      'trf_out'=>$trfout_qty,
                      'trf_out_amount'=>$trfout_amount,
                      'adjustment_stock'=>$adj_qty,
                      'adjustment_amount'=>$adj_amount,
                      'stock_opname'=>$opn_qty,
                      'amount_opname'=>$opn_amount,
                      'retur_beli_stock'=>$returbeli_qty,
                      'retur_beli_amount'=>$returbeli_amount,
                      'retur_pakai_stock'=>$returpakai_qty,
                      'retur_pakai_amount'=>$returpakai_amount,
                      'ending_stock'=>$endstok,
                      'ending_amount'=>$endamount,
                      'hpp'=>$hpp,
                ];

                tb_item_bulanan::on($konek)->where('kode_lokasi', $lokasi)->where('periode', $tanggal_baru1)->where('kode_produk', $kode_produk)->update($simpan_update);
            }
            
        }

        $message = [
          'success' => true,
          'title' => 'Simpan',
          'message' => 'Data telah di Disimpan.'
        ];
        return response()->json($message);
        
    }
}
