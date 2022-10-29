<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\Produk;
use App\Models\Jasa;
use App\Models\satuan;
use App\Models\Konversi;
use App\Models\Company;
use App\Models\Catatanpo;
use App\Models\Signature;
use App\Models\tb_akhir_bulan;
use App\Models\tb_produk_history;
use App\Models\tb_item_bulanan;
use App\Models\KategoriProduk;
use App\Models\Merek;
use App\Models\Ukuran;
use App\Models\user_history;
use App\Models\MasterLokasi;
use App\Models\ReturPenjualan;
use App\Models\ReturPenjualanDetail;
use PDF;
use Excel;
use DB;
use Carbon;
use DateTime;


class PenjualanController extends Controller
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
        }
        return $koneksi;
    }

    public function index()
    {
        $konek = self::konek();
        $create_url = route('penjualan.create');
        $Vendor= Vendor::pluck('nama_vendor','id');
        $Customer= Customer::on($konek)->pluck('nama_customer','id');
        $Company= Company::pluck('nama_company','kode_company');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        $level = auth()->user()->level;
        
        return view('admin.penjualan.index',compact('create_url','Vendor','Company','Customer','period', 'nama_lokasi','nama_company'));
    }


    public function getkode(){
        $konek = self::konek();
        $get = Penjualan::on($konek)->join('penjualan_detail','penjualan_detail.no_penjualan','=','penjualan.no_penjualan')->get();
        $leng = count($get);

        $data = array();

        foreach ($get as $rowdata){
            $kode_customer = $rowdata->kode_customer;
            $kode_produk = $rowdata->kode_produk;

            $data[] = array(
                'kode_customer'=>$kode_customer,
                'kode_produk'=>$kode_produk,
             );
        }

        for ($i = 0; $i < $leng; $i++) { 
            //HEADER
            $cek = Customer::on($konek)->where('kode_customer', $data[$i]['kode_customer'])->first();
            if($cek != null){
                $id = $cek->id;

                $tabel_baru = [
                   'kode_customer'=>$id,
                ];
                $update = Penjualan::on($konek)->where('kode_customer', $data[$i]['kode_customer'])->update($tabel_baru);
            }

            //DETAIL
            $cek = Produk::on($konek)->where('kode_produk', $data[$i]['kode_produk'])->first();
            if ($cek != null) {
                $id = $cek->id;

                $tabel_baru = [
                    'kode_produk'=>$id,
                ];
                $update = PenjualanDetail::on($konek)->where('kode_produk', $data[$i]['kode_produk'])->update($tabel_baru);
            }
        }

        $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => 'Kode Berhasil di Update.'
        ];
            
        return response()->json($message);
    }


    public function anyData()
    {
        $konek = self::konek();
        $lokasi = auth()->user()->kode_lokasi;
        if($lokasi == 'HO'){
            return Datatables::of(Penjualan::on($konek)->with('company','penjualandetail','customer','Lokasi')->orderBy('created_at','desc')->withCount('penjualandetail')->orderBy('created_at','desc'))->make(true);
        }
        else{
            return Datatables::of(Penjualan::on($konek)->with('company','penjualandetail','customer','Lokasi')->where('kode_lokasi', auth()->user()->kode_lokasi)->orderBy('created_at','desc')->withCount('penjualandetail')->orderBy('created_at','desc'))->make(true);
        }
    }

    public function exportPDF(){
        $konek = self::konek();
        $request = $_GET['no_penjualan'];

        $penjualan = Penjualan::on($konek)->where('no_penjualan',$request)->first();
        $type_ar = $penjualan->type_ar;

        $no_penjualan = $penjualan->no_penjualan;

        $type = $penjualan->type_ar;
        $kode_customer = $penjualan->kode_customer;

        $kode_company = $penjualan->kode_company;

        $penjualandetail = PenjualanDetail::on($konek)->where('no_penjualan',$request)->get();

        $penjualan = Penjualan::on($konek)->where('no_penjualan',$no_penjualan)->first();

        $company = Company::where('kode_company',$kode_company)->first();
        $nama_company = $company->nama_company;

        $customer = Customer::on($konek)->where('id',$kode_customer)->first();
        $nama_customer = $customer->nama_customer_po;
        $alamat = substr($customer->alamat, 0, 70);

        $dt = Carbon\Carbon::now();
        $date_now = Carbon\Carbon::parse($dt)->format('d/m/Y');

        $tgl = $penjualan->tanggal_penjualan;
        $date=date_create($tgl);
        
        if($type_ar != 'Jasa'){
            $pdf = PDF::loadView('/admin/penjualan/pdf', compact('penjualandetail','request','no_penjualan','tgl','nama_company','date_now','type','nama_customer','alamat','penjualan'));
            $pdf->setPaper([0, 0, 684, 792], 'potrait');

            return $pdf->stream('Laporan Penjualan '.$no_penjualan.'.pdf');   
        }
        else{
            $pdf = PDF::loadView('/admin/penjualan/pdfjasa', compact('penjualandetail','request','no_penjualan','tgl','nama_company','date_now','type','nama_customer','alamat','penjualan'));
            $pdf->setPaper([0, 0, 684, 792], 'potrait');

            return $pdf->stream('Laporan Penjualan '.$no_penjualan.'.pdf');
        }     
    }
    
    public function detail($penjualan)
    {   
        $konek = self::konek();
        $penjualan = Penjualan::on($konek)->find($penjualan);
        $tanggal = $penjualan->tanggal_penjualan;
        $no_penjualan = $penjualan->no_penjualan;
        $type_ar = $penjualan->type_ar;

        $validate = $this->periodeChecker($tanggal);
        // dd($validate);
             
        if($validate == true){
            $data = Penjualan::on($konek)->find($no_penjualan);
            $status = $penjualan->status;

            if($status == 'OPEN'){
                if($type_ar == 'Jasa'){
                    $total_qty = 0;
                    $total_harga = 0;
                    $grand_total = 0;

                    $PenjualanDetail = PenjualanDetail::on($konek)->with('produk','satuan')->where('no_penjualan', $penjualan->no_penjualan)
                    ->orderBy('created_at','desc')->get();

                    foreach ($PenjualanDetail as $row){
                        $total_qty += $row->qty;
                        $subtotal = $row->harga * $row->qty;
                        $total_harga += $subtotal;
                        $grand_total = number_format($total_harga,2,",",".");
                    }

                    $list_url= route('penjualan.index');
                    $Produk = Jasa::pluck('nama_item','id');
                    $Satuan = satuan::pluck('nama_satuan','kode_satuan');
                    $Kategori = KategoriProduk::pluck('nama_kategori', 'kode_kategori');
                    $Merek = Merek::on($konek)->pluck('nama_merek', 'kode_merek');
                    $Ukuran= Ukuran::on($konek)->pluck('nama_ukuran', 'kode_ukuran');

                    $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
                    $tgl_jalan2 = $tgl_jalan->periode;
                    $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
                    $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
                    $nama_lokasi = $get_lokasi->nama_lokasi;

                    $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
                    $nama_company = $get_company->nama_company;
                    
                    return view('admin.penjualandetail.index', compact('penjualan','PenjualanDetail','list_url','Produk','Satuan','total_qty','grand_total','Kategori','Merek','Ukuran','Satuan','Company','period', 'nama_lokasi','nama_company'));
                }else if($type_ar == 'Unit'){
                    $total_qty = 0;
                    $total_harga = 0;
                    $grand_total = 0;

                    $PenjualanDetail = PenjualanDetail::on($konek)->with('produk','satuan')->where('no_penjualan', $penjualan->no_penjualan)
                    ->orderBy('created_at','desc')->get();

                    foreach ($PenjualanDetail as $row){
                        $total_qty += $row->qty;
                        $subtotal = $row->harga * $row->qty;
                        $total_harga += $subtotal;
                        $grand_total = number_format($total_harga,0,",",".");
                    }

                    $list_url= route('penjualan.index');

                    $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode', 'Open')->orwhere('reopen_status', 'true')->first();
                    $Produk = Produk::on($konek)->Join('tb_item_bulanan', 'produk.id', '=', 'tb_item_bulanan.kode_produk')->where('ending_stock','>',0)->where('periode',$cek_bulan->periode)->where('kode_kategori','UNIT')->where('kode_lokasi',auth()->user()->kode_lokasi)->pluck('produk.nama_produk','produk.id');

                    // $Produk = Produk::where('kode_kategori','UNIT')->pluck('nama_produk','kode_produk');

                    $Satuan = satuan::pluck('nama_satuan','kode_satuan');
                    $Kategori = KategoriProduk::pluck('nama_kategori', 'kode_kategori');
                    $Merek = Merek::on($konek)->pluck('nama_merek', 'kode_merek');
                    $Ukuran= Ukuran::on($konek)->pluck('nama_ukuran', 'kode_ukuran');

                    $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
                    $tgl_jalan2 = $tgl_jalan->periode;
                    $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
                    $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
                    $nama_lokasi = $get_lokasi->nama_lokasi;

                    $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
                    $nama_company = $get_company->nama_company;
                    
                    return view('admin.penjualandetail.index', compact('penjualan','PenjualanDetail','list_url','Produk','Satuan','total_qty','grand_total','Kategori','Merek','Ukuran','Satuan','Company','period', 'nama_lokasi','nama_company'));
                }else{
                    $total_qty = 0;
                    $total_harga = 0;
                    $grand_total = 0;

                    $PenjualanDetail = PenjualanDetail::on($konek)->with('produk','satuan')->where('no_penjualan', $penjualan->no_penjualan)
                    ->orderBy('created_at','desc')->get();

                    foreach ($PenjualanDetail as $row){
                        $total_qty += $row->qty;
                        $subtotal = $row->harga * $row->qty;
                        $total_harga += $subtotal;
                        $grand_total = number_format($total_harga,0,",",".");
                    }

                    $list_url= route('penjualan.index');

                    $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode', 'Open')->orwhere('reopen_status', 'true')->first();
                    $Produk = Produk::on($konek)->Join('tb_item_bulanan', 'produk.id', '=', 'tb_item_bulanan.kode_produk')->where('ending_stock','>',0)->where('periode',$cek_bulan->periode)->where('kode_kategori','<>','UNIT')->where('kode_lokasi',auth()->user()->kode_lokasi)->pluck('produk.nama_produk','produk.id');
                    
                    // $Produk2 = Produk::on($konek)->where('kode_kategori','<>','UNIT')->where('periode',$cek_bulan->periode)->where('kode_lokasi',auth()->user()->kode_lokasi)->pluck('nama_produk','kode_produk');
                    // dd($Produk2);
                    
                    $Satuan = satuan::pluck('nama_satuan','kode_satuan');
                    $Kategori = KategoriProduk::pluck('nama_kategori', 'kode_kategori');
                    $Merek = Merek::on($konek)->pluck('nama_merek', 'kode_merek');
                    $Ukuran= Ukuran::on($konek)->pluck('nama_ukuran', 'kode_ukuran');

                    $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
                    $tgl_jalan2 = $tgl_jalan->periode;
                    $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
                    $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
                    $nama_lokasi = $get_lokasi->nama_lokasi;

                    $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
                    $nama_company = $get_company->nama_company;
                    
                    return view('admin.penjualandetail.index', compact('penjualan','PenjualanDetail','list_url','Produk','Satuan','total_qty','grand_total','Kategori','Merek','Ukuran','Satuan','Company','period', 'nama_lokasi','nama_company'));
                }
            }
                
            else if($status != 'OPEN'){
                    alert()->success('Status POSTED/Periode Telah CLOSED: '.$tanggal,'GAGAL!')->persistent('Close');
                    return redirect()->back();
            }
        }
        else{
            alert()->success('Status POSTED / Periode Telah CLOSED: '.$tanggal,'GAGAL!')->persistent('Close');
            return redirect()->back();
        }
    }

    public function Showdetail()
    {
        $konek = self::konek();
        $total_qty = 0;
        $total_harga = 0;
        $grand_total = 0;
        $penjualandetail= PenjualanDetail::on($konek)->with('produk','satuan')->where('no_penjualan',request()->id)
        ->orderBy('created_at', 'desc')->get();
        
        $penjualan = Penjualan::on($konek)->where('no_penjualan',request()->id)->first();
        $jenis = $penjualan->type_ar;

        $output = array();

        foreach ($penjualandetail as $row){
            $total_qty += $row->qty;
            $subtotal = $row->harga_jual * $row->qty;
            $total_harga += $subtotal;
            $grand_total = number_format($total_harga,2,",",".");
        }

        if($penjualandetail){
            if($jenis != 'Jasa'){
                foreach($penjualandetail as $row)
                {
                    $no_penjualan = $row->no_penjualan;
                    $produk = $row->produk->nama_produk;
                    $partnumber = $row->partnumber;
                    $satuan = $row->satuan->nama_satuan;
                    $qty = $row->qty;
                    $qty_retur = $row->qty_retur;
                    $harga = $row->harga_jual;
                    $subtotal =  number_format($row->harga_jual * $row->qty,2,",",".");
                    $output[] = array(
                        'no_penjualan'=>$no_penjualan,
                        'produk'=>$produk,
                        'partnumber'=>$partnumber,
                        'satuan'=>$satuan,
                        'qty'=>$qty,
                        'qty_retur'=>$qty_retur,
                        'harga'=>$harga,
                        'subtotal'=>$subtotal,
                    );
                }
            }else{
                foreach($penjualandetail as $row)
                {
                    $no_penjualan = $row->no_penjualan;
                    $produk = $row->jasa->nama_item;
                    $partnumber = $row->partnumber;
                    $satuan = $row->kode_satuan;
                    $qty = $row->qty;
                    $qty_retur = $row->qty_retur;
                    $harga = $row->harga_jual;
                    $subtotal =  number_format($row->harga_jual * $row->qty,2,",",".");
                    $output[] = array(
                        'no_penjualan'=>$no_penjualan,
                        'produk'=>$produk,
                        'partnumber'=>$partnumber,
                        'satuan'=>$satuan,
                        'qty'=>$qty,
                        'qty_retur'=>0,
                        'harga'=>$harga,
                        'subtotal'=>$subtotal,
                    );
                }
            }
        }else{
            $output = array(
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Maaf Data Terkait Tidak Ada'
            );
        }
        
        return response()->json($output);
    }

    function periodeChecker($tgl)
    {   
        $konek = self::konek();
        $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
        $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;

        $tabel = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

        if($tabel != null)
        {
            $stat = $tabel->status_periode;
            $re_stat = $tabel->reopen_status;
            if($stat =='Open' || $re_stat == 'true')
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    public function produkChecker($no_penjualan, $tahun, $bulan, $tanggal_baru, $tgl, $penjualan, $koneksi)
    {
                $konek = self::konek();
                $penjualandetail = PenjualanDetail::on($konek)->with('produk','satuan')->where('no_penjualan', request()->id)->get();
                $no_penjualan = request()->id;
             
                $data = array();
                $kode_produk = array();
                $kode_satuan = array();
                $qty = array();

                if(!empty($penjualandetail)){
                    foreach ($penjualandetail as $rowdata){

                    $kodeP = $rowdata->kode_produk;
                    $kodeS = $rowdata->kode_satuan;
                    $qtyS = $rowdata->qty;
                    $partS = $rowdata->partnumber;

                       $data[] = array(
                         'no_penjualan'=>$no_penjualan,
                         'kode_produk'=>$kodeP,
                         'kode_satuan'=>$kodeS,
                         'qty'=>$qtyS,
                         'partnumber'=>$partS,
                        );
                       
                        $kode_produk[] = array(
                            'kode_produk'=>$kodeP,
                        );           
                     }

                }

                        if(!empty($kode_produk)){
                            $leng = count($kode_produk);

                            $i = 0;

                            for($i = 0; $i < $leng; $i++){
                                $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                                
                                    // dd($data[0]['kode_produk']);

                                if($tb_item_bulanan != null){
                                    $produk_awal = $tb_item_bulanan->kode_produk;

                                    $stock_begin = $tb_item_bulanan->begin_stock;
                                    $amount_begin = $tb_item_bulanan->begin_amount;

                                    $stok_in = $tb_item_bulanan->in_stock;
                                    $stok_ending = $tb_item_bulanan->ending_stock;

                                    $sale_stock = $tb_item_bulanan->sale_stock;
                                    $sale_amount = $tb_item_bulanan->sale_amount;

                                    $stock_trfin = $tb_item_bulanan->trf_in;
                                    $amount_trfin = $tb_item_bulanan->trf_in_amount;

                                    $stock_trfout = $tb_item_bulanan->trf_out;
                                    $amount_trfout = $tb_item_bulanan->trf_out_amount;

                                    $amount_masuk = $tb_item_bulanan->in_amount;
                                    $amount = $tb_item_bulanan->ending_amount;

                                    $amount_keluar = $tb_item_bulanan->out_amount;

                                    $stock_out = $tb_item_bulanan->out_stock;
                                    $outamount_awal_1 = $tb_item_bulanan->out_amount;
                                    
                                    $amount_adj = $tb_item_bulanan->adjustment_amount;
                                    $amount_op = $tb_item_bulanan->amount_opname;

                                    $retur_beli_amount = $tb_item_bulanan->retur_beli_amount;
                                    $retur_jual_amount = $tb_item_bulanan->retur_jual_amount;

                                    $produk = Produk::on($konek)->find($data[$i]['kode_produk']);

                                    $penjualandetail2 = PenjualanDetail::on($konek)->where('no_penjualan', $no_penjualan)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();
                                    // dd($penjualandetail2);

                                    $hpp = $penjualandetail2->harga;

                                    $harga_jual = $penjualandetail2->harga_jual;
                                
                                        $update_produk = Produk::on($konek)->where('id',$data[$i]['kode_produk'])->first();
                                        $update_produk->harga_jual = $harga_jual;


                                    $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                                    $qty_baru = $data[$i]['qty']*$konversi->nilai_konversi;

                                    $waktu = $tgl;
                                    $barang = $data[$i]['kode_produk'];
                                    $stok_jual = $sale_stock + $qty_baru;
                                    $amount_jual = $sale_amount + ($hpp*$qty_baru);
                                    $end_stok = $stok_ending - $qty_baru;
                                    $end_amount = $amount_begin + $amount_masuk - $amount_keluar - $amount_jual + $amount_trfin - $amount_trfout + $amount_adj + $amount_op - $retur_beli_amount + $retur_jual_amount;

                                    $tabel_baru = [
                                        'sale_stock'=>$stok_jual,
                                        'sale_amount'=>$amount_jual,
                                        'ending_stock'=>$end_stok,
                                        'ending_amount'=>$end_amount,
                                    ];
                                    // dd($tabel_baru);


                                    if($end_stok < 0){
                                        exit();
                                    }

                                    $tgl_jual1 = $penjualan->tanggal_penjualan;
                                    $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_jual1)->year;
                                    $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_jual1)->month;

                                    $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                                    $status_reopen = $reopen->reopen_status;

                                    if($status_reopen == 'true'){
                                        $tgl_jual = $penjualan->tanggal_penjualan;
                                        $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_jual)->year;
                                        $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_jual)->month;

                                        $tb_akhir_bulan2 = tb_akhir_bulan::on($konek)->where('status_periode','Disable')->first();
                                        $periode_berjalan = $tb_akhir_bulan2->periode;

                                        $datetime1 = new DateTime($periode_berjalan);
                                        $datetime2 = new DateTime($tanggal_baru);
                                        $month1 = Carbon\Carbon::parse($periode_berjalan)->format('m');
                                        $month2 = Carbon\Carbon::parse($tanggal_baru)->format('m');
                                        
                                        $diff = $datetime1->diff($datetime2);
                                        $final_date = $diff->d;
                                        $final_year = $diff->y;
                                        $f_month = $diff->m;

                                        $final_month = $f_month;
                                        if($final_month == 0){
                                            $f_month = $month1 - $month2;
                                            $final_month = $f_month;
                                        }

                                        
                                        $bulan2 = 0;
                                        $j = 1;
                                        while($j <= $final_month){
                                            $penjualandetail2 = PenjualanDetail::on($konek)->where('no_penjualan', $no_penjualan)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                                            $hpp = $penjualandetail2->harga;
     
                                            $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                                            $stock_o = $data[$i]['qty']*$konversi->nilai_konversi;
                                            $amount_o = $hpp*$stock_o;

                                            $bulancek = $bulan + $j;
                                            if($bulancek >= 13 && $tahun_transaksi == 2019){
                                                $bulan2 += 1;
                                                $tahun2 = 2020;
                                            }else if($bulancek < 13 && $tahun_transaksi == 2019){
                                                $bulan2 = $bulancek;
                                                $tahun2 = 2019;
                                            }else{
                                                $bulan2 = $bulancek;
                                                $tahun2 = 2020;
                                            }

                                            $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->first();

                                            if($tb_item_bulanan2 != null){
                                                $bs = $tb_item_bulanan2->begin_stock;
                                                $ba = $tb_item_bulanan2->begin_amount;
                                                $is = $tb_item_bulanan2->in_stock;
                                                $ia = $tb_item_bulanan2->in_amount;
                                                $os = $tb_item_bulanan2->out_stock;
                                                $oa = $tb_item_bulanan2->out_amount;
                                                $ss = $tb_item_bulanan2->sale_stock;
                                                $sa = $tb_item_bulanan2->sale_amount;
                                                $es = $tb_item_bulanan2->ending_stock;
                                                $ea = $tb_item_bulanan2->ending_amount;

                                                $begin_stock1 = $bs - $stock_o;
                                                $begin_amount1 = $ba - $amount_o;

                                                $end_stok1 = $es - $stock_o;
                                                $end_amount1 = $ea - $amount_o;

                                                $tabel_baru2 = [
                                                    'begin_stock'=>$begin_stock1,
                                                    'begin_amount'=>$begin_amount1,
                                                    'ending_stock'=>$end_stok1,
                                                    'ending_amount'=>$end_amount1,
                                                ];

                                                    if($end_stok1 < 0){
                                                        exit();
                                                    }

                                            }

                                            $j++;
                                        }

                                        
                                    }

                                    
                                    
                                }
                            }
                        }
                    
                return true;
    }

    public function posting()
    {
        $konek = self::konek();
        $level = auth()->user()->level;
        $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode','Disable')->first();
        if($cek_bulan == null || $level == 'superadministrator' || $level == 'user_rince' || $level == 'user_herry'){
            $penjualan = Penjualan::on($konek)->find(request()->id);

            $cek_status = $penjualan->status;
            if($cek_status != 'OPEN'){  
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'POST No. Penjualan: '.$penjualan->no_penjualan.' sudah dilakukan! Pastikan Anda tidak membuka menu PENJUALAN lebih dari 1',
                ];
                return response()->json($message);
            }

            $no_penjualan = $penjualan->no_penjualan;
            $create_penjualan = $penjualan->created_at;
            $koneksi = $penjualan->kode_lokasi;
            $jenis = $penjualan->type_ar;

            $tgl = $penjualan->tanggal_penjualan;
            $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
            $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
            $tanggal = '01';

            $tanggal_baru = Carbon\Carbon::createFromDate($tahun, $bulan, $tanggal)->toDateString();
            $validate = $this->periodeChecker($tgl);

            if($validate != true){  
                $message = [
                    'success' => false,
                    'title' => 'Update',
                    'message' => 'Data gagal di POSTING, re-open [Bulan '.$bulan.'; Tahun '.$tahun.'].'
                ];
                return response()->json($message);
            }

            $validate_produk = $this->produkChecker($no_penjualan, $tahun, $bulan, $tanggal_baru, $tgl, $penjualan, $koneksi);
            
            if($validate_produk == true){
                if($jenis != 'Jasa'){
                    $penjualandetail = PenjualanDetail::on($konek)->with('produk','satuan')->where('no_penjualan', request()->id)->get();

                    $no_penjualan = request()->id;
                 
                    $data = array();
                    $kode_produk = array();
                    $kode_satuan = array();
                    $qty = array();

                    if(!empty($penjualandetail)){
                        foreach ($penjualandetail as $rowdata){

                        $kodeP = $rowdata->kode_produk;
                        $kodeS = $rowdata->kode_satuan;
                        $qtyS = $rowdata->qty;
                        $partS = $rowdata->partnumber;

                           $data[] = array(
                             'no_penjualan'=>$no_penjualan,
                             'kode_produk'=>$kodeP,
                             'kode_satuan'=>$kodeS,
                             'qty'=>$qtyS,
                             'partnumber'=>$partS,
                            );
                           
                            $kode_produk[] = array(
                                'kode_produk'=>$kodeP,
                            );           
                        }

                    }

                            if(!empty($kode_produk)){
                                $leng = count($kode_produk);

                                $i = 0;

                                for($i = 0; $i < $leng; $i++){
                                    $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                                    
                                        // dd($data[0]['kode_produk']);

                                    if($tb_item_bulanan != null){
                                        $produk_awal = $tb_item_bulanan->kode_produk;

                                        $stock_begin = $tb_item_bulanan->begin_stock;
                                        $amount_begin = $tb_item_bulanan->begin_amount;

                                        $stok_in = $tb_item_bulanan->in_stock;
                                        $stok_ending = $tb_item_bulanan->ending_stock;

                                        $sale_stock = $tb_item_bulanan->sale_stock;
                                        $sale_amount = $tb_item_bulanan->sale_amount;

                                        $stock_trfin = $tb_item_bulanan->trf_in;
                                        $amount_trfin = $tb_item_bulanan->trf_in_amount;

                                        $stock_trfout = $tb_item_bulanan->trf_out;
                                        $amount_trfout = $tb_item_bulanan->trf_out_amount;

                                        $amount_masuk = $tb_item_bulanan->in_amount;
                                        $amount = $tb_item_bulanan->ending_amount;

                                        $amount_keluar = $tb_item_bulanan->out_amount;

                                        $stock_out = $tb_item_bulanan->out_stock;
                                        $outamount_awal_1 = $tb_item_bulanan->out_amount;
                                        
                                        $amount_adj = $tb_item_bulanan->adjustment_amount;
                                        $amount_op = $tb_item_bulanan->amount_opname;

                                        $retur_beli_amount = $tb_item_bulanan->retur_beli_amount;
                                        $retur_jual_amount = $tb_item_bulanan->retur_jual_amount;

                                        $hpp = $tb_item_bulanan->hpp;
                                
                                        $produk = Produk::on($konek)->find($data[$i]['kode_produk']);
                                        $penjualandetail2 = PenjualanDetail::on($konek)->where('no_penjualan', $no_penjualan)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                                        $harga_jual = $penjualandetail2->harga_jual;

                                        $update_harga = [
                                            'harga'=>$hpp,
                                        ];

                                        $penjualandetail2 = PenjualanDetail::on($konek)->where('no_penjualan', $no_penjualan)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->update($update_harga);
                                    
                                            $update_produk = Produk::on($konek)->where('id',$data[$i]['kode_produk'])->first();
                                            $update_produk->harga_jual = $harga_jual;
                                            $update_produk->save();


                                        $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                                        $qty_baru = $data[$i]['qty']*$konversi->nilai_konversi;

                                        $waktu = $tgl;
                                        $barang = $data[$i]['kode_produk'];
                                        $stok_jual = $sale_stock + $qty_baru;
                                        $amount_jual = $sale_amount + ($hpp*$qty_baru);
                                        $end_stok = $stok_ending - $qty_baru;
                                        $end_amount = $amount_begin + $amount_masuk - $amount_keluar - $amount_jual + $amount_trfin - $amount_trfout + $amount_adj + $amount_op - $retur_beli_amount + $retur_jual_amount;

                                        if($end_stok != 0){
                                            $hpp2 = $end_amount / $end_stok;
                                        }else{
                                            $hpp2 = $tb_item_bulanan->hpp;
                                            $end_amount = 0;
                                        }

                                        $tabel_baru = [
                                            'sale_stock'=>$stok_jual,
                                            'sale_amount'=>$amount_jual,
                                            'ending_stock'=>$end_stok,
                                            'ending_amount'=>$end_amount,
                                            'hpp'=>$hpp2,
                                        ];
                                        // dd($tabel_baru);


                                        if($end_stok < 0){
                                            $message = [
                                            'success' => false,
                                            'title' => 'Update',
                                            'message' => 'Data gagal di POSTING, silahkan lakukan Penerimaan pada [Bulan '.$bulan.'; Tahun '.$tahun.'] terlebih dahulu. Stok saat ini tidak cukup untuk di pakai.'
                                            ];
                                            return response()->json($message);
                                        }

                                        $update_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$produk_awal)->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($tabel_baru);

                                        $tabel_history = [
                                            'kode_produk'=>$barang,
                                            'no_transaksi'=>$no_penjualan,
                                            'tanggal_transaksi'=>$waktu,
                                            'jam_transaksi'=>$create_penjualan,
                                            'qty_transaksi'=>0-$qty_baru,
                                            'harga_transaksi'=>$hpp,
                                            'total_transaksi'=>0-($hpp*$qty_baru),
                                            'kode_lokasi'=>$koneksi,
                                        ];

                                        $update_produk_history = tb_produk_history::on($konek)->create($tabel_history);

                                        $tgl_jual1 = $penjualan->tanggal_penjualan;
                                        $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_jual1)->year;
                                        $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_jual1)->month;

                                        $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                                        $status_reopen = $reopen->reopen_status;

                                        if($status_reopen == 'true'){
                                            $tgl_jual = $penjualan->tanggal_penjualan;
                                            $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_jual)->year;
                                            $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_jual)->month;

                                            $tb_akhir_bulan2 = tb_akhir_bulan::on($konek)->where('status_periode','Disable')->first();
                                            $periode_berjalan = $tb_akhir_bulan2->periode;

                                            $datetime1 = new DateTime($periode_berjalan);
                                            $datetime2 = new DateTime($tanggal_baru);
                                            $month1 = Carbon\Carbon::parse($periode_berjalan)->format('m');
                                            $month2 = Carbon\Carbon::parse($tanggal_baru)->format('m');
                                            
                                            $diff = $datetime1->diff($datetime2);
                                            $final_date = $diff->d;
                                            $final_year = $diff->y;
                                            $f_month = $diff->m;

                                            //convert
                                            $timeStart = strtotime($tanggal_baru);
                                            $timeEnd = strtotime($periode_berjalan);
                                                                             
                                            // Menambah bulan ini + semua bulan pada tahun sebelumnya
                                            $numBulan = (date("Y",$timeEnd)-date("Y",$timeStart))*12;
                                            // hitung selisih bulan
                                            $numBulan += date("m",$timeEnd)-date("m",$timeStart);
                                            $final_month = $numBulan;

                                            $bulan3 = 0;
                                            $j = 1;
                                            while($j <= $final_month){
                                                $penjualandetail2 = PenjualanDetail::on($konek)->where('no_penjualan', $no_penjualan)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                                                $hpp = $penjualandetail2->harga;
         
                                                $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                                                $stock_o = $data[$i]['qty']*$konversi->nilai_konversi;
                                                $amount_o = $hpp*$stock_o;

                                                $tahun_berjalan = Carbon\Carbon::createFromFormat('Y-m-d',$periode_berjalan)->year;
                                                $tahun_kemarin = $tahun_berjalan - 1;

                                                $bulancek = $bulan + $j;
                                                if($bulancek >= 13 && $tahun_transaksi == strval($tahun_kemarin)){
                                                  $bulan3 += 1;
                                                  $bulan2 = strval($bulan3);
                                                  $tahun2 = strval($tahun_berjalan);
                                                }else if($bulancek < 13 && $tahun_transaksi == strval($tahun_kemarin)){
                                                  $bulan2 = strval($bulancek);
                                                  $tahun2 = strval($tahun_kemarin);
                                                }else{
                                                  $bulan2 = strval($bulancek);
                                                  $tahun2 = strval($tahun_berjalan);
                                                }

                                                $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->first();

                                                if($tb_item_bulanan2 != null){
                                                    $bs = $tb_item_bulanan2->begin_stock;
                                                    $ba = $tb_item_bulanan2->begin_amount;
                                                    $is = $tb_item_bulanan2->in_stock;
                                                    $ia = $tb_item_bulanan2->in_amount;
                                                    $os = $tb_item_bulanan2->out_stock;
                                                    $oa = $tb_item_bulanan2->out_amount;
                                                    $ss = $tb_item_bulanan2->sale_stock;
                                                    $sa = $tb_item_bulanan2->sale_amount;
                                                    $es = $tb_item_bulanan2->ending_stock;
                                                    $ea = $tb_item_bulanan2->ending_amount;

                                                    $begin_stock1 = $bs - $stock_o;
                                                    $begin_amount1 = $ba - $amount_o;

                                                    $end_stok1 = $es - $stock_o;
                                                    $end_amount1 = $ea - $amount_o;

                                                    if($end_stok1 != 0){
                                                        $hpp = $end_amount1 / $end_stok1;
                                                    }else{
                                                        $hpp = $tb_item_bulanan2->hpp;
                                                        $end_amount1 = 0;
                                                    }

                                                    $tabel_baru2 = [
                                                        'begin_stock'=>$begin_stock1,
                                                        'begin_amount'=>$begin_amount1,
                                                        'ending_stock'=>$end_stok1,
                                                        'ending_amount'=>$end_amount1,
                                                        'hpp'=>$hpp,
                                                    ];

                                                    $update_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->update($tabel_baru2);
                                                }

                                                $j++;
                                            }
                                        }
                                        
                                    }
                                   
                                    else
                                    {
                                        alert()->success('Post', 'GAGAL!')->persistent('Close');
                                        return redirect()->back();
                                    }
                                }
                            }
                }
            }
            else{
                $message = [
                    'success' => false,
                    'title' => 'Update',
                    'message' => 'Re-Open [Bulan '.$bulan.'; Tahun '.$tahun.'].'
                ];

                return response()->json($message);
            }

            $penjualan = Penjualan::on($konek)->find(request()->id);
            $penjualan->status = "POSTED";
            $penjualan->save(); 

            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Post No. Penjualan: '.$no_penjualan.'.','created_by'=>$nama,'updated_by'=>$nama];
                     //dd($tmp);
            user_history::on($konek)->create($tmp);
                    
            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data berhasil di POST.'
            ];

            return response()->json($message);
        }
        else{
            $message = [
                        'success' => false,
                        'title' => 'Simpan',
                        'message' => 'Anda tidak mempunyai akses posting data',
                        ];
            return response()->json($message);
        }
        
    }

    public function unposting()
    {
        $konek = self::konek();
        $level = auth()->user()->level;
        $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode','Disable')->first();

        if($cek_bulan == null || $level == 'superadministrator' || $level == 'user_rince' || $level == 'user_herry'){
            $penjualan = Penjualan::on($konek)->find(request()->id);
            $cek_status = $penjualan->status;
            if($cek_status != 'POSTED'){  
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'UNPOST No. Penjualan: '.$penjualan->no_penjualan.' sudah dilakukan! Pastikan Anda tidak membuka menu PENJUALAN lebih dari 1',
                ];
                return response()->json($message);
            }

            $no_penjualan = $penjualan->no_penjualan;
            $koneksi = $penjualan->kode_lokasi;
            $jenis = $penjualan->type_ar;

            $tgl = $penjualan->tanggal_penjualan;
            $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
            $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
            $tanggal = '01';

            $tanggal_baru = Carbon\Carbon::createFromDate($tahun, $bulan, $tanggal)->toDateString();
            $validate = $this->periodeChecker($tgl);

            if($validate != true){  
                $message = [
                    'success' => false,
                    'title' => 'Update',
                    'message' => 'Data gagal di UNPOSTING, re-open [Bulan '.$bulan.'; Tahun '.$tahun.'].'
                ];
                return response()->json($message);
            }
            
            $cekopen = Penjualan::on($konek)->where('kode_lokasi', $koneksi)->where('status','OPEN')->whereMonth('tanggal_penjualan', $bulan)->whereYear('tanggal_penjualan', $tahun)->first();
            if ($cekopen != null){
                $message = [
                        'success' => false,
                        'title' => 'Gagal',
                        'message' => 'UNPOST No. Penjualan: '.$penjualan->no_penjualan.' gagal karena masih ada penjualan OPEN.',
                ];
                return response()->json($message);
            }

            $returpenjualan = ReturPenjualan::on($konek)->where('no_penjualan', request()->id)->where('status','POSTED')->first();

            if($returpenjualan != null){  
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'UNPOST No. Penjualan: '.$penjualan->no_penjualan.' gagal! Ada RETUR PENJUALAN yang terkait',
                ];
                return response()->json($message);
            }else{
                $cek_detail = ReturPenjualan::on($konek)->where('no_penjualan', request()->id)->first();
                if ($cek_detail != null){
                    $hapus_detail = ReturPenjualanDetail::on($konek)->where('no_penjualan',$cek_detail->no_penjualan)->delete();
                    $cek_detail->total_item = 0;
                    $cek_detail->save();
                }
            }

            if($validate == true){
                if($jenis != 'Jasa'){
                    $penjualandetail = PenjualanDetail::on($konek)->with('produk','satuan')->where('no_penjualan', request()->id)->get();
                    $penjualan = Penjualan::on($konek)->find(request()->id);
                 
                    $data = array();
                    $kode_produk = array();
                    $kode_satuan = array();
                    $qty = array();

                    if(!empty($penjualandetail)){
                        foreach ($penjualandetail as $rowdata){

                        $kodeP = $rowdata->kode_produk;
                        $kodeS = $rowdata->kode_satuan;
                        $qtyS = $rowdata->qty;
                        $partS = $rowdata->partnumber;
                        

                           $data[] = array(
                             'no_penjualan'=>$no_penjualan,
                             'kode_produk'=>$kodeP,
                             'kode_satuan'=>$kodeS,
                             'qty'=>$qtyS,
                             'partnumber'=>$partS,
                            );
                           
                            $kode_produk[] = array(
                                'kode_produk'=>$kodeP,
                            );           
                         }

                    }
                        // var_dump($kode_produk);

                            if(!empty($kode_produk)){
                                $leng = count($kode_produk);

                                $i = 0;

                                for($i = 0; $i < $leng; $i++){
                                    $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                                    
                                    $produk_awal = $tb_item_bulanan->kode_produk;

                                    $stock_begin = $tb_item_bulanan->begin_stock;
                                    $amount_begin = $tb_item_bulanan->begin_amount;
                                    $amount_akhir1 = $tb_item_bulanan->ending_amount;

                                    $stok_in = $tb_item_bulanan->in_stock;
                                    $stok_akhir = $tb_item_bulanan->ending_stock;

                                    $sale_stock = $tb_item_bulanan->sale_stock;
                                    $sale_amount = $tb_item_bulanan->sale_amount;

                                    $stock_trfin = $tb_item_bulanan->trf_in;
                                    $amount_trfin = $tb_item_bulanan->trf_in_amount;

                                    $stock_trfout = $tb_item_bulanan->trf_out;
                                    $amount_trfout = $tb_item_bulanan->trf_out_amount;

                                    $amount_masuk = $tb_item_bulanan->in_amount;
                                    $amount = $tb_item_bulanan->ending_amount;

                                    $amount_keluar = $tb_item_bulanan->out_amount;

                                    $outstok_awal_1 = $tb_item_bulanan->out_stock;
                                    $outamount_awal_1 = $tb_item_bulanan->out_amount;
                                    
                                    $amount_adj = $tb_item_bulanan->adjustment_amount;
                                    $amount_op = $tb_item_bulanan->amount_opname;

                                    $retur_beli_amount = $tb_item_bulanan->retur_beli_amount;
                                    $retur_jual_amount = $tb_item_bulanan->retur_jual_amount;

                                    $produk = Produk::on($konek)->find($data[$i]['kode_produk']);

                                    $penjualandetail = PenjualanDetail::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('no_penjualan',$no_penjualan)->first();

                                    $hpp = $penjualandetail->harga;

                                    $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                                    $qty_baru = $data[$i]['qty']*$konversi->nilai_konversi;

                                        $stock_jual = $sale_stock - $qty_baru;
                                        $amount_jual = $sale_amount - ($hpp*$qty_baru);
                                        $end_stok = $stok_akhir + $qty_baru;
                                        $end_amount = $amount_begin + $amount_masuk - $amount_keluar - $amount_jual + $amount_trfin - $amount_trfout + $amount_adj + $amount_op - $retur_beli_amount + $retur_jual_amount;

                                        if($end_stok != 0){
                                            $hpp = $end_amount / $end_stok;
                                        }else{
                                            $hpp = $tb_item_bulanan->hpp;
                                            $end_amount = 0;
                                        }

                                        $tabel_baru = [
                                            'sale_stock'=>$stock_jual,
                                            'sale_amount'=>$amount_jual,
                                            'ending_stock'=>$end_stok,
                                            'ending_amount'=>$end_amount,
                                            'hpp'=>$hpp,
                                        ];
                                        // dd($tabel_baru);

                                    $update_produk_history = tb_produk_history::on($konek)->where('no_transaksi',$no_penjualan)->delete();

                                    $update_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$produk_awal)->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($tabel_baru);

                                    $tgl_jual1 = $penjualan->tanggal_penjualan;
                                    $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_jual1)->year;
                                    $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_jual1)->month;

                                    $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                                    $status_reopen = $reopen->reopen_status;

                                        if($status_reopen == 'true'){
                                            $tgl_jual = $penjualan->tanggal_penjualan;
                                            $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_jual)->year;
                                            $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_jual)->month;

                                            $tb_akhir_bulan2 = tb_akhir_bulan::on($konek)->where('status_periode','Disable')->first();
                                            $periode_berjalan = $tb_akhir_bulan2->periode;

                                            $datetime1 = new DateTime($periode_berjalan);
                                            $datetime2 = new DateTime($tanggal_baru);
                                            $month1 = Carbon\Carbon::parse($periode_berjalan)->format('m');
                                            $month2 = Carbon\Carbon::parse($tanggal_baru)->format('m');
                                            
                                            $diff = $datetime1->diff($datetime2);
                                            $final_date = $diff->d;
                                            $final_year = $diff->y;
                                            $f_month = $diff->m;

                                            //convert
                                            $timeStart = strtotime($tanggal_baru);
                                            $timeEnd = strtotime($periode_berjalan);
                                                                             
                                            // Menambah bulan ini + semua bulan pada tahun sebelumnya
                                            $numBulan = (date("Y",$timeEnd)-date("Y",$timeStart))*12;
                                            // hitung selisih bulan
                                            $numBulan += date("m",$timeEnd)-date("m",$timeStart);
                                            $final_month = $numBulan;

                                            $bulan3 = 0;
                                            $j = 1;
                                            while($j <= $final_month){
                                                $penjualandetail2 = PenjualanDetail::on($konek)->where('no_penjualan', $no_penjualan)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                                                $hpp = $penjualandetail2->harga;
         
                                                $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                                                $stock_o = $data[$i]['qty']*$konversi->nilai_konversi;
                                                $amount_o = $hpp*$stock_o;

                                                $tahun_berjalan = Carbon\Carbon::createFromFormat('Y-m-d',$periode_berjalan)->year;
                                                $tahun_kemarin = $tahun_berjalan - 1;

                                                $bulancek = $bulan + $j;
                                                if($bulancek >= 13 && $tahun_transaksi == strval($tahun_kemarin)){
                                                  $bulan3 += 1;
                                                  $bulan2 = strval($bulan3);
                                                  $tahun2 = strval($tahun_berjalan);
                                                }else if($bulancek < 13 && $tahun_transaksi == strval($tahun_kemarin)){
                                                  $bulan2 = strval($bulancek);
                                                  $tahun2 = strval($tahun_kemarin);
                                                }else{
                                                  $bulan2 = strval($bulancek);
                                                  $tahun2 = strval($tahun_berjalan);
                                                }

                                                $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->first();

                                                if($tb_item_bulanan2 != null){
                                                    $bs = $tb_item_bulanan2->begin_stock;
                                                    $ba = $tb_item_bulanan2->begin_amount;
                                                    $is = $tb_item_bulanan2->in_stock;
                                                    $ia = $tb_item_bulanan2->in_amount;
                                                    $os = $tb_item_bulanan2->out_stock;
                                                    $oa = $tb_item_bulanan2->out_amount;
                                                    $es = $tb_item_bulanan2->ending_stock;
                                                    $ea = $tb_item_bulanan2->ending_amount;

                                                    $begin_stock1 = $bs + $stock_o;
                                                    $begin_amount1 = $ba + $amount_o;

                                                    $end_stok1 = $es + $stock_o;
                                                    $end_amount1 = $ea + $amount_o;

                                                    if($end_stok1 != 0){
                                                        $hpp = $end_amount1 / $end_stok1;
                                                    }else{
                                                        $hpp = $tb_item_bulanan2->hpp;
                                                        $end_amount1 = 0;
                                                    }

                                                    $tabel_baru2 = [
                                                        'begin_stock'=>$begin_stock1,
                                                        'begin_amount'=>$begin_amount1,
                                                        'ending_stock'=>$end_stok1,
                                                        'ending_amount'=>$end_amount1,
                                                        'hpp'=>$hpp,
                                                    ];
                                                    // dd($tabel_baru2);

                                                    $update_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->update($tabel_baru2);
                                                }

                                                $j++;
                                            }
                                                
                                        }
                                }
                            }
                }
            }
            else{
                $message = [
                        'success' => false,
                        'title' => 'Update',
                        'message' => 'Re-Open [Bulan '.$bulan.'; Tahun '.$tahun.'].'
                        ];

                return response()->json($message);
            }
                     
            $penjualan = Penjualan::on($konek)->find(request()->id);
            $penjualan->status = "OPEN";
            $penjualan->save(); 

            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Unpost No. Penjualan: '.$no_penjualan.'.','created_by'=>$nama,'updated_by'=>$nama];
                     //dd($tmp);
            user_history::on($konek)->create($tmp);
                    
            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data berhasil di UNPOST.'
            ];

            return response()->json($message);

                 
        }else{
        $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Anda tidak mempunyai akses unposting data',
                ];
        return response()->json($message);
        }
        
    }


    public function store(Request $request)
    {
        $konek = self::konek();
        $tanggal = $request->tanggal_penjualan;

        $validate = $this->periodeChecker($tanggal);
        
        $reopen = tb_akhir_bulan::on($konek)->where('reopen_status','true')->first();

        if ($reopen != null){
            $tgl = $reopen->periode;
            $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
            $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
            $jual = Penjualan::on($konek)->whereMonth('tanggal_penjualan',$bulan_transaksi)->whereYear('tanggal_penjualan',$tahun_transaksi)->where('kode_lokasi', auth()->user()->kode_lokasi)->where('status','OPEN')->get();
            if (count($jual) >= 1){
                $message = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Masih ada penjualan yang OPEN.'
                ];
               return response()->json($message);
            }
        }else {
            $tgl = tb_akhir_bulan::on($konek)->where('status_periode','Open')->first();
            $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl->periode)->year;
            $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl->periode)->month;
            //$sekarang = date('Y-m-d');
            $jual = Penjualan::on($konek)->whereMonth('tanggal_penjualan',$bulan_transaksi)->whereYear('tanggal_penjualan',$tahun_transaksi)->where('kode_lokasi', auth()->user()->kode_lokasi)->where('status','OPEN')->get();
            if (count($jual) >= 1){
                $message = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Masih ada penjualan yang OPEN.'
                ];
               return response()->json($message);
            }
        }
        
        if($validate == true){
            $penjualan = Penjualan::on($konek)->create($request->all());

            $no = Penjualan::on($konek)->orderBy('created_at','desc')->first();
            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Simpan No. Penjualan: '.$no->no_penjualan.'.','created_by'=>$nama,'updated_by'=>$nama];
            user_history::on($konek)->create($tmp);

            $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => 'Data telah di Disimpan.'
            ];
            return response()->json($message);
        }else{
            $message = [
            'success' => false,
            'title' => 'Simpan',
            'message' => '<b>Periode</b> ['.$tanggal.'] <b>Telah Ditutup / Belum Dibuka</b>'
            ];
            return response()->json($message);
        }
            
    }
                
    public function edit_penjualan()
    {
        $konek = self::konek();
        $no_penjualan = request()->id;
        $data = Penjualan::on($konek)->find($no_penjualan);
        $status = $data->status;
        if($status == 'OPEN'){
            $output = array(
            'no_penjualan'=> $data->no_penjualan,
            'tanggal_penjualan'=>$data->tanggal_penjualan,
            'kode_customer'=>$data->kode_customer,
            'no_josp'=>$data->no_josp,
            'no_spjb'=>$data->no_spjb,
            'tgl_spjb'=>$data->tgl_spjb,
            'no_bast'=>$data->no_bast,
            'tgl_bast'=>$data->tgl_bast,
            'type_ar'=>$data->type_ar,
            'seri_faktur'=> $data->seri_faktur,
            'top'=> $data->top,
            'due_date'=> $data->due_date,
            'no_sertifikat'=> $data->no_sertifikat,
            'tgl_sertifikat'=> $data->tgl_sertifikat,
            'ppn'=>$data->ppn,
            'diskon_persen'=>$data->diskon_persen,
            'diskon_rp'=>$data->diskon_rp,
            'status'=> $data->status,
            );
            return response()->json($output);
        }
    }

    
    public function updateAjax(Request $request)
    {
        $konek = self::konek();
        $tanggal = $request->tanggal_penjualan;

        $validate = $this->periodeChecker($tanggal);
             
        if($validate == true){
            $penjualan = Penjualan::on($konek)->find($request->no_penjualan);
       
            $penjualandetail = PenjualanDetail::on($konek)->where('no_penjualan', $request->no_penjualan)->get();

                $total_qty = 0;
                $total_harga = 0;
                $grand_total = 0;

                            foreach ($penjualandetail as $row){
                                $total_qty += $row->qty;
                                $subtotal = $row->harga_jual * $row->qty;
                                $total_harga += $subtotal;
                            }

            $penjualan->grand_total = $total_harga;

            $penjualan->save();

            $ppn = $request->ppn;
            $diskonpersen = $request->diskon_persen;
            $diskonrp = $request->diskon_rp;

            if($ppn == 0 && $diskonrp == 0 && $diskonpersen == 0){
                $request->validate([
                    'no_penjualan'=> 'required',
                    'tanggal_penjualan'=> 'required',
                ]);

                $Penjualan = Penjualan::on($konek)->find($request->no_penjualan)->update($request->all());
                     
                     $nama = auth()->user()->name;
                     $tmp = ['nama' => $nama,'aksi' => 'Edit No. Penjualan: '.$request->no_penjualan.'.','created_by'=>$nama,'updated_by'=>$nama];
                     //dd($tmp);
                     user_history::on($konek)->create($tmp);
                  
                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data telah di Update.'
                    ];
                return response()->json($message);
            }
            else{
                $penjualandetail = PenjualanDetail::on($konek)->where('no_penjualan', $request->no_penjualan)->get();
                if($penjualandetail != null){
                    $total_qty = 0;
                    $total_harga = 0;
                    $grand_total = 0;


                    if($diskonpersen == 0 && $diskonrp == 0){
                            $ppn_final = ($ppn)/100;

                            foreach ($penjualandetail as $row){
                                $total_qty += $row->qty;
                                $subtotal = $row->harga_jual * $row->qty;
                                $total_harga += $subtotal;
                            }

                            $penjualan = Penjualan::on($konek)->find($request->no_penjualan);
                                if($total_harga == 0){
                                    $penjualan->grand_total = $total_harga;
                                }else{
                                    $penjualan->grand_total = $total_harga + ($total_harga * $ppn_final);
                                }

                            
                            $penjualan->save(); 

                            $Penjualan = Penjualan::on($konek)->find($request->no_penjualan)->update($request->all());

                            $nama = auth()->user()->name;
                            $tmp = ['nama' => $nama,'aksi' => 'Update No. Penjualan: '.$request->no_penjualan.'.','created_by'=>$nama,'updated_by'=>$nama];
                            //dd($tmp);
                            user_history::on($konek)->create($tmp);

                            $message = [
                            'success' => true,
                            'title' => 'Update',
                            'message' => 'Data telah di Update'
                            ];
                        return response()->json($message);
                    }
                    else if($diskonpersen > 0 && $ppn == 0 && $diskonrp == 0){
                            $diskonpersen_final = ($diskonpersen)/100;

                            foreach ($penjualandetail as $row){
                                $total_qty += $row->qty;
                                $subtotal = $row->harga_jual * $row->qty;
                                $total_harga += $subtotal;
                            }

                            $penjualan = Penjualan::on($konek)->find($request->no_penjualan);
                                if($total_harga == 0){
                                    $penjualan->grand_total = $total_harga;
                                }else{
                                    $penjualan->grand_total = $total_harga - ($total_harga * $diskonpersen_final);
                                }

                            
                            $penjualan->save(); 

                            $Penjualan = Penjualan::on($konek)->find($request->no_penjualan)->update($request->all());

                            $nama = auth()->user()->name;
                            $tmp = ['nama' => $nama,'aksi' => 'Update No. Penjualan: '.$request->no_penjualan.'.','created_by'=>$nama,'updated_by'=>$nama];
                            //dd($tmp);
                            user_history::on($konek)->create($tmp);

                            $message = [
                            'success' => true,
                            'title' => 'Update',
                            'message' => 'Data telah di Update'
                            ];
                        return response()->json($message);
                    } 
                    else if($diskonrp > 0 && $ppn == 0 && $diskonpersen == 0){  
                            $diskonrp_final = $diskonrp;

                            foreach ($penjualandetail as $row){
                                $total_qty += $row->qty;
                                $subtotal = $row->harga_jual * $row->qty;
                                $total_harga += $subtotal;
                            }

                            $penjualan = Penjualan::on($konek)->find($request->no_penjualan);
                                if($total_harga == 0){
                                    $penjualan->grand_total = $total_harga;
                                }else{
                                    $penjualan->grand_total = $total_harga - $diskonrp_final;
                                }

                            
                            $penjualan->save(); 

                            $Penjualan = Penjualan::on($konek)->find($request->no_penjualan)->update($request->all());

                            $nama = auth()->user()->name;
                            $tmp = ['nama' => $nama,'aksi' => 'Update No. Penjualan: '.$request->no_penjualan.'.','created_by'=>$nama,'updated_by'=>$nama];
                            //dd($tmp);
                            user_history::on($konek)->create($tmp);

                            $message = [
                            'success' => true,
                            'title' => 'Update',
                            'message' => 'Data telah di Update'
                            ];
                        return response()->json($message);
                    }
                    else if($diskonrp > 0 && $ppn > 0 && $diskonpersen == 0){  
                            $diskonrp_final = $diskonrp;
                            $ppn_final = ($ppn)/100;

                            foreach ($penjualandetail as $row){
                                $total_qty += $row->qty;
                                $subtotal = $row->harga_jual * $row->qty;
                                $total_harga += $subtotal;
                            }

                            $penjualan = Penjualan::on($konek)->find($request->no_penjualan);
                                if($total_harga == 0){
                                    $penjualan->grand_total = $total_harga;
                                }else{
                                    $penjualan->grand_total = ($total_harga - $diskonrp_final) + (($total_harga - $diskonrp_final) * $ppn_final);
                                }

                            
                            $penjualan->save(); 

                            $Penjualan = Penjualan::on($konek)->find($request->no_penjualan)->update($request->all());

                            $nama = auth()->user()->name;
                            $tmp = ['nama' => $nama,'aksi' => 'Update No. Penjualan: '.$request->no_penjualan.'.','created_by'=>$nama,'updated_by'=>$nama];
                            //dd($tmp);
                            user_history::on($konek)->create($tmp);

                            $message = [
                            'success' => true,
                            'title' => 'Update',
                            'message' => 'Data telah di Update'
                            ];
                        return response()->json($message);
                    }
                    else if($diskonpersen > 0 && $ppn > 0 && $diskonrp == 0){  
                            $diskonpersen_final = ($diskonpersen)/100;
                            $ppn_final = ($ppn)/100;

                            foreach ($penjualandetail as $row){
                                $total_qty += $row->qty;
                                $subtotal = $row->harga_jual * $row->qty;
                                $total_harga += $subtotal;
                            }

                            $penjualan = Penjualan::on($konek)->find($request->no_penjualan);
                                if($total_harga == 0){
                                    $penjualan->grand_total = $total_harga;
                                }else{
                                    $penjualan->grand_total = $total_harga + ($total_harga * $ppn_final) - ($total_harga * $diskonpersen_final);
                                }

                            
                            $penjualan->save(); 

                            $Penjualan = Penjualan::on($konek)->find($request->no_penjualan)->update($request->all());

                            $nama = auth()->user()->name;
                            $tmp = ['nama' => $nama,'aksi' => 'Update No. Penjualan: '.$request->no_penjualan.'.','created_by'=>$nama,'updated_by'=>$nama];
                            //dd($tmp);
                            user_history::on($konek)->create($tmp);

                            $message = [
                            'success' => true,
                            'title' => 'Update',
                            'message' => 'Data telah di Update'
                            ];
                        return response()->json($message);
                    }
                }
                
                
            }
            
        }

        else{
            $message = [
            'success' => false,
            'title' => 'Simpan',
            'message' => 'Re-Open Periode: '.$tanggal,
            ];
            return response()->json($message);
        }
    }


    public function hapus_penjualan()
    {
        $konek = self::konek();
        $level = auth()->user()->level;
        if($level == 'superadministrator' || $level == 'user_andre'){
            $no_penjualan = request()->id;
            $data = Penjualan::on($konek)->find($no_penjualan);
            $tanggal = $data->tanggal_penjualan;

            $validate = $this->periodeChecker($tanggal);
                // dd($tanggal);
            if($validate == true){
                $cek_detail = PenjualanDetail::on($konek)->where('no_penjualan',$no_penjualan)->first();
                if($cek_detail == null){
                    $data = Penjualan::on($konek)->find($no_penjualan);
                    $status = $data->status;

                    if($status == 'OPEN'){
                        try {
                        $data->delete();

                        $nama = auth()->user()->name;
                        $tmp = ['nama' => $nama,'aksi' => 'Hapus No. Penjualan: '.$no_penjualan.'.','created_by'=>$nama,'updated_by'=>$nama];
                        //dd($tmp);
                        user_history::on($konek)->create($tmp);

                        $message = [
                        'success' => true,
                        'title' => 'Update',
                        'message' => 'Data ['.$no_penjualan.'] telah dihapus.'
                        ];
                        return response()->json($message);
                        
                    }catch (\Exception $exception){
                        $message = [
                            'success' => false,
                            'title' => 'Update',
                            'message' => 'Data gagal dihapus.'
                        ];
                        return response()->json($message);
                    }
                }
                else{
                        alert()->success('Input Data Excel','BERHASIL!')->persistent('Close');
                    }
            }

            else if($validate == false){
                $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Re-Open Periode: '.$tanggal,
                ];
                return response()->json($message);
                }
            
            }
        }else{
            $message = [
                        'success' => false,
                        'title' => 'Simpan',
                        'message' => 'Anda tidak mempunyai akses hapus data',
                        ];
            return response()->json($message);
        }
        
    }
}
