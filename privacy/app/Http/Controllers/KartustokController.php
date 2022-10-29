<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\tb_akhir_bulan;
use App\Models\Kartustok;
use App\Models\tb_item_bulanan;
use App\Models\tb_produk_history;
use App\Models\MasterLokasi;
use App\Models\Produk;
use App\Models\Signature;
use App\Models\Company;
use App\Models\KategoriProduk;
use App\Exports\KartustokExport;
use PDF;
use Excel;
use DB;
use Carbon;

class KartustokController extends Controller
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
        $create_url = route('kartustok.create');
        $tanggal = tb_produk_history::on($konek)->orderBy('tanggal_transaksi','desc')->pluck('tanggal_transaksi','tanggal_transaksi');

        $Produk = Produk::on($konek)->Join('tb_item_bulanan', 'produk.id', '=', 'tb_item_bulanan.kode_produk')->where('kode_lokasi',auth()->user()->kode_lokasi)->pluck('produk.nama_produk','produk.id');
        
        $kategori = KategoriProduk::select('kode_kategori', DB::raw("concat(kode_kategori,' - ',nama_kategori) as kategori"))->pluck('kategori','kode_kategori');

        $lokasi = tb_item_bulanan::on($konek)->pluck('kode_lokasi', 'kode_lokasi');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        return view('admin.kartustok.index',compact('create_url','Produk','tanggal','period', 'nama_lokasi','lokasi','kategori'));
    }

    public function exportPDF(){
        $konek = self::konek();
        $request1 = '01';
        $request2 = $_GET['tanggal_akhir'];
        $cetak = $_GET['format_cetak'];
        $pilih = $_GET['show'];
        
        if(isset($_GET['ttd'])){
            $format_ttd = $_GET['ttd'];
        }else{
            $format_ttd = 0;
        }

        $level = auth()->user()->level;
        if($level != 'hse'){
            $limit3 = Signature::on($konek)->where('jabatan','MANAGER OPERASIONAL')->first();
            if($limit3 == null){
                $limit3 = Signature::on($konek)->where('jabatan','DIREKTUR')->where('kode_lokasi',auth()->user()->kode_lokasi)->first();
            }
        }else{
            $limit3 = Signature::on($konek)->where('jabatan','MANAGER HSE')->first();
        }

        $tanggal_baru = $request2;
        $bulan = Carbon\Carbon::parse($tanggal_baru)->format('m');
        $tahun = Carbon\Carbon::parse($tanggal_baru)->format('Y');
        $nama_bulan = Carbon\Carbon::parse($tanggal_baru)->format('F');

        $dt = Carbon\Carbon::now();
        $date=date_create($dt);
    
        $ttd = auth()->user()->name;
        $lokasi2 = auth()->user()->kode_lokasi;
        $get_company = auth()->user()->kode_company;
        
        if($lokasi2 == 'HO'){
            $lokasi = $_GET['lokasi'];
            if($lokasi != 'SEMUA'){
                $nama_lokasi = MasterLokasi::find($lokasi);
                $nama = $nama_lokasi->nama_lokasi;
            }
            else{
                $nama_lokasi = MasterLokasi::find($lokasi2);
                $nama = $nama_lokasi->nama_lokasi;
            }
        }else{
            $nama_lokasi = MasterLokasi::find($lokasi2);
            $nama = $nama_lokasi->nama_lokasi;
        }
        
        $nama_company = Company::find($get_company);
        $nama2 = $nama_company->nama_company;

        $tanggal_awal = tb_item_bulanan::on($konek)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
        
        if($tanggal_awal == null ){
            alert()->success('Tidak ada transaksi di Periode: '.$nama_bulan. ' ' .$tahun,'GAGAL!')->persistent('Close');
            return redirect()->back();
        }

        if($pilih == 'Produk'){
            $request = $_GET['kode_produk'];
            $produk = Produk::on($konek)->where('id',$request)->first();
            $nama_produk = $produk->nama_produk;
            $kode_satuan = $produk->kode_satuan;
            $tipe_produk = $produk->tipe_produk;
            $kode_kategori = $produk->kode_kategori;
            $total_begin = 0;
            $total_ending = 0;
            $total_amount_begin = 0;
            $total_amount_ending = 0;

            
            if($lokasi2 == 'HO'){
                $lokasi = $_GET['lokasi'];
                if($lokasi != 'SEMUA'){
                    $request4 = $_GET['lokasi'];
                    $tanggal = $tanggal_awal->periode;

                    $get_lokasi = auth()->user()->kode_lokasi;

                    $kartustok_cetak = tb_produk_history::on($konek)->orderBy('created_at','asc')->where('kode_produk',$request)->where('kode_lokasi',$request4)->whereMonth('tanggal_transaksi', $bulan)->whereYear('tanggal_transaksi', $tahun)->where('tanggal_transaksi', '<=', $request2)->get();

                    $kartustok_saldo = tb_item_bulanan::on($konek)->where('kode_produk',$request)->where('kode_lokasi',$request4)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

                    $get_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$request)->where('kode_lokasi',$request4)->whereMonth('periode', $request2)->whereYear('periode', $request2)->get();
                    foreach ($get_bulanan as $row){
                        $total_begin += $row->begin_stock;
                        $total_ending += $row->ending_stock;
                        $total_amount_begin += $row->begin_amount;
                        $total_amount_ending += $row->ending_amount;
                    }

                    if($kartustok_cetak == null || $kartustok_saldo == null){
                        alert()->success('Tidak ada transaksi','GAGAL!')->persistent('Close');
                        return redirect()->back();
                    }else{
                        if ($cetak == 'PDF') {
                            $pdf = PDF::loadView('/admin/kartustok/pdf', compact('kartustok_cetak','kartustok_saldo','request', 'request2','tanggal','nama_bulan','nama_produk','kode_satuan','date','ttd','limit3','nama','nama2','lokasi','tipe_produk','kode_kategori','total_begin','total_ending','total_amount_begin','total_amount_ending','dt','format_ttd','tahun','bulan'));
                            $pdf->setPaper('a4', 'landscape');
                            return $pdf->stream('Kartu Stok Barang '.$request.' Periode '.$bulan.'-'.$tahun.'.pdf');
                        }else {
                            return Excel::download(new KartustokExport($request2, $cetak, $pilih, $request, $lokasi, $total_begin, $total_ending, $total_amount_begin, $total_amount_ending), 'Laporan Kartu Stock '.$request.' Tahun '.$tahun.'.xlsx');
                        }
                    } 
                }
                else{
                    $tanggal = $tanggal_awal->periode;

                    $get_lokasi = auth()->user()->kode_lokasi;

                    $kartustok_cetak = tb_produk_history::on($konek)->orderBy('created_at','asc')->where('kode_produk',$request)->whereMonth('tanggal_transaksi', $bulan)->whereYear('tanggal_transaksi', $tahun)->where('tanggal_transaksi', '<=', $request2)->get();

                    $kartustok_saldo = tb_item_bulanan::on($konek)->where('kode_produk',$request)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

                    $get_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$request)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->get();
                    foreach ($get_bulanan as $row){
                        $total_begin += $row->begin_stock;
                        $total_ending += $row->ending_stock;
                        $total_amount_begin += $row->begin_amount;
                        $total_amount_ending += $row->ending_amount;
                    }

                    if($kartustok_cetak == null || $kartustok_saldo == null){
                        alert()->success('Tidak ada transaksi','GAGAL!')->persistent('Close');
                        return redirect()->back();
                    }else{
                        if ($cetak == 'PDF') {
                            $pdf = PDF::loadView('/admin/kartustok/pdf', compact('kartustok_cetak','kartustok_saldo','request', 'request2','tanggal','nama_bulan','nama_produk','kode_satuan','date','ttd','limit3','nama','nama2','lokasi','tipe_produk','kode_kategori','total_begin','total_ending','total_amount_begin','total_amount_ending','dt','format_ttd','tahun','bulan'));
                            $pdf->setPaper('a4', 'landscape');
                            return $pdf->stream('Kartu Stok Barang '.$request.' Periode '.$bulan.'-'.$tahun.'.pdf'); 
                        }else {
                            return Excel::download(new KartustokExport($request2, $cetak, $pilih, $request, $lokasi, $total_begin, $total_ending, $total_amount_begin, $total_amount_ending), 'Laporan Kartu Stock '.$request.' Tahun '.$tahun.'.xlsx');
                        }
                    } 
                }
            }
            else{
                $tanggal = $tanggal_awal->periode;

                $get_lokasi = auth()->user()->kode_lokasi;

                $kartustok_cetak = tb_produk_history::on($konek)->orderBy('created_at','asc')->where('kode_produk',$request)->where('kode_lokasi',$lokasi2)->whereMonth('tanggal_transaksi', $bulan)->whereYear('tanggal_transaksi', $tahun)->where('tanggal_transaksi', '<=', $request2)->get();

                $kartustok_saldo = tb_item_bulanan::on($konek)->where('kode_produk',$request)->where('kode_lokasi',$lokasi2)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

                $get_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$request)->where('kode_lokasi',$lokasi2)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->get();
                foreach ($get_bulanan as $row){
                    $total_begin += $row->begin_stock;
                    $total_ending += $row->ending_stock;
                    $total_amount_begin += $row->begin_amount;
                    $total_amount_ending += $row->ending_amount;
                }

                $lokasi = $lokasi2;
                if($kartustok_cetak == null || $kartustok_saldo == null){
                    alert()->success('Tidak ada transaksi','GAGAL!')->persistent('Close');
                    return redirect()->back();
                }else{
                    if ($cetak == 'PDF') {
                        $pdf = PDF::loadView('/admin/kartustok/pdf', compact('kartustok_cetak','kartustok_saldo','request', 'request2','tanggal','nama_bulan','nama_produk','kode_satuan','date','ttd','limit3','nama','nama2','lokasi','tipe_produk','kode_kategori','total_begin','total_ending','total_amount_begin','total_amount_ending','dt','format_ttd','tahun','bulan'));
                        $pdf->setPaper('a4', 'landscape');
                        return $pdf->stream('Kartu Stok Barang '.$request.' Periode '.$bulan.'-'.$tahun.'.pdf');
                    }else {
                        return Excel::download(new KartustokExport($request2, $cetak, $pilih, $request, $lokasi, $total_begin, $total_ending, $total_amount_begin, $total_amount_ending), 'Laporan Kartu Stock '.$request.' Tahun '.$tahun.'.xlsx');
                    }
                }
            }
            
        }else{
            $kode_kategori = $_GET['kode_kategori'];

            if($lokasi2 == 'HO'){
                $lokasi = $_GET['lokasi'];
                if($lokasi != 'SEMUA'){
                    $request4 = $_GET['lokasi'];
                    $tanggal = $tanggal_awal->periode;

                    $get_lokasi = auth()->user()->kode_lokasi;

                    $kartustok_cetak = tb_produk_history::on($konek)
                    ->select('tb_produk_history.*','produk.id','produk.nama_produk','produk.kode_kategori','produk.kode_satuan')
                    ->join('produk','tb_produk_history.kode_produk', '=', 'produk.id')
                    ->where('produk.kode_kategori', $kode_kategori)
                    ->where('tb_produk_history.kode_lokasi', $request4)
                    ->whereMonth('tb_produk_history.tanggal_transaksi', $bulan)
                    ->whereYear('tb_produk_history.tanggal_transaksi', $tahun)
                    ->where('tb_produk_history.tanggal_transaksi', '<=', $request2)
                    ->orderBy('produk.id','asc')
                    ->orderBy('tb_produk_history.created_at','asc')
                    ->get();

                    $kartustok_saldo = tb_item_bulanan::on($konek)
                    ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori','produk.kode_satuan')
                    ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                    ->where('produk.kode_kategori', $kode_kategori)
                    ->where('tb_item_bulanan.kode_lokasi', $request4)
                    ->whereMonth('tb_item_bulanan.periode', $bulan)
                    ->whereYear('tb_item_bulanan.periode', $tahun)
                    ->orderBy('produk.id','asc')
                    ->get();

                    if($kartustok_cetak == null){
                        alert()->success('Tidak ada transaksi','GAGAL!')->persistent('Close');
                        return redirect()->back();
                    }else{
                        if ($cetak == 'PDF') {
                            $pdf = PDF::loadView('/admin/kartustok/pdf2', compact('kartustok_cetak','request2','tanggal','nama_bulan','date','ttd','limit3','nama','nama2','kode_kategori','kartustok_saldo','lokasi','dt','format_ttd','tahun','bulan'));
                            $pdf->setPaper('a4', 'landscape');
                            return $pdf->stream('Kartu Stok Kategori '.$kode_kategori.' Periode '.$bulan.'-'.$tahun.'.pdf'); 
                        }else {
                            $total_begin = 0;
                            $total_ending = 0;
                            $total_amount_begin = 0;
                            $total_amount_ending = 0;
                            return Excel::download(new KartustokExport($request2, $cetak, $pilih, $kode_kategori, $lokasi, $total_begin, $total_ending, $total_amount_begin, $total_amount_ending), 'Laporan Kartu Stock '.$kode_kategori.' Tahun '.$tahun.'.xlsx');
                        }
                    } 
                }else{
                    $tanggal = $tanggal_awal->periode;

                    $get_lokasi = auth()->user()->kode_lokasi;

                    $kartustok_cetak = tb_produk_history::on($konek)
                    ->select('tb_produk_history.*','produk.id','produk.nama_produk','produk.kode_kategori','produk.kode_satuan')
                    ->join('produk','tb_produk_history.kode_produk', '=', 'produk.id')
                    ->where('produk.kode_kategori', $kode_kategori)
                    ->whereMonth('tb_produk_history.tanggal_transaksi', $bulan)
                    ->whereYear('tb_produk_history.tanggal_transaksi', $tahun)
                    ->where('tb_produk_history.tanggal_transaksi', '<=', $request2)
                    ->orderBy('produk.id','asc')
                    ->orderBy('tb_produk_history.created_at','asc')
                    ->get();

                    $kartustok_saldo = tb_item_bulanan::on($konek)
                    ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori','produk.kode_satuan')
                    ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                    ->where('produk.kode_kategori', $kode_kategori)
                    ->whereMonth('tb_item_bulanan.periode', $bulan)
                    ->whereYear('tb_item_bulanan.periode', $tahun)
                    ->orderBy('produk.id','asc')
                    ->get();

                    if($kartustok_cetak == null){
                        alert()->success('Tidak ada transaksi','GAGAL!')->persistent('Close');
                        return redirect()->back();
                    }else{
                        if ($cetak == 'PDF') {
                            $pdf = PDF::loadView('/admin/kartustok/pdf2', compact('kartustok_cetak','request', 'request2','tanggal','nama_bulan','nama_produk','kode_satuan','date','ttd','limit3','nama','nama2','kode_kategori','kartustok_saldo','lokasi','dt','format_ttd','tahun','bulan'));
                            $pdf->setPaper('a4', 'landscape');
                            return $pdf->stream('Kartu Stok Kategori '.$kode_kategori.' Periode '.$bulan.'-'.$tahun.'.pdf'); 
                        }else {
                            $total_begin = 0;
                            $total_ending = 0;
                            $total_amount_begin = 0;
                            $total_amount_ending = 0;
                            return Excel::download(new KartustokExport($request2, $cetak, $pilih, $kode_kategori, $lokasi, $total_begin, $total_ending, $total_amount_begin, $total_amount_ending), 'Laporan Kartu Stock '.$kode_kategori.' Tahun '.$tahun.'.xlsx');
                        }
                    } 
                }
            }else{
                $tanggal = $tanggal_awal->periode;

                $get_lokasi = auth()->user()->kode_lokasi;

                $kartustok_cetak = tb_produk_history::on($konek)
                ->select('tb_produk_history.*','produk.id','produk.nama_produk','produk.kode_kategori','produk.kode_satuan')
                ->join('produk','tb_produk_history.kode_produk', '=', 'produk.id')
                ->where('produk.kode_kategori', $kode_kategori)
                ->where('tb_produk_history.kode_lokasi', $get_lokasi)
                ->whereMonth('tb_produk_history.tanggal_transaksi', $bulan)
                ->whereYear('tb_produk_history.tanggal_transaksi', $tahun)
                ->where('tb_produk_history.tanggal_transaksi', '<=', $request2)
                ->orderBy('produk.id','asc')
                ->orderBy('tb_produk_history.created_at','asc')
                ->get();

                $kartustok_saldo = tb_item_bulanan::on($konek)
                ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori','produk.kode_satuan')
                ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                ->where('produk.kode_kategori', $kode_kategori)
                ->where('tb_item_bulanan.kode_lokasi', $get_lokasi)
                ->whereMonth('tb_item_bulanan.periode', $bulan)
                ->whereYear('tb_item_bulanan.periode', $tahun)
                ->orderBy('produk.id','asc')
                ->get();

                $lokasi = $lokasi2;
                if($kartustok_cetak == null || $kartustok_saldo == null){
                    alert()->success('Tidak ada transaksi','GAGAL!')->persistent('Close');
                    return redirect()->back();
                }else{
                    if ($cetak == 'PDF') {
                        $pdf = PDF::loadView('/admin/kartustok/pdf2', compact('kartustok_cetak','kartustok_saldo', 'request2','tanggal','nama_bulan','date','ttd','limit3','nama','nama2','kartustok_saldo','lokasi','kode_kategori','dt','format_ttd','tahun','bulan'));
                        $pdf->setPaper('a4', 'landscape');
                        return $pdf->stream('Kartu Stok Kategori '.$kode_kategori.' Periode '.$bulan.'-'.$tahun.'.pdf'); 
                    }else {
                        $total_begin = 0;
                        $total_ending = 0;
                        $total_amount_begin = 0;
                        $total_amount_ending = 0;
                        return Excel::download(new KartustokExport($request2, $cetak, $pilih, $kode_kategori, $lokasi, $total_begin, $total_ending, $total_amount_begin, $total_amount_ending), 'Laporan Kartu Stock '.$kode_kategori.' Tahun '.$tahun.'.xlsx');
                    }
                }
            }
        }

    }
}
