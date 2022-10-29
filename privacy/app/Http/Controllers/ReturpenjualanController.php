<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\ReturPenjualan;
use App\Models\ReturPenjualanDetail;
use App\Models\Produk;
use App\Models\Konversi;
use App\Models\satuan;
use App\Models\Company;
use App\Models\KategoriProduk;
use App\Models\Merek;
use App\Models\Ukuran;
use App\Models\Mobil;
use App\Models\JenisMobil;
use App\Models\Alat;
use App\Models\Kapal;
use App\Models\tb_akhir_bulan;
use App\Models\tb_item_bulanan;
use App\Models\tb_produk_history;
use App\Models\user_history;
use App\Models\MasterLokasi;
use App\Models\Customer;
use PDF;
use Excel;
use DB;
use Alert;
use Carbon;
use DateTime;

class ReturpenjualanController extends Controller
{
    public function index()
    {
        $konek = self::konek();
        $create_url = route('returjual.create');
        $Company= Company::pluck('nama_company','kode_company');
        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;
        
        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_jalan2)->year;
        $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_jalan2)->month;

        $Penjualan = Penjualan::on($konek)->where('status','POSTED')->where('kode_lokasi',auth()->user()->kode_lokasi)->orderBy('created_at','desc')->pluck('no_penjualan','no_penjualan');

        $level = auth()->user()->level;
        
        return view('admin.returjual.index',compact('create_url','Company','Mobil','JenisMobil','Alat','Asmobil','Asalat','period','Kapal','Askapal', 'nama_lokasi','nama_company','Penjualan'));
    }

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

    public function getkode(){
        $konek = self::konek();
        $get = ReturPenjualan::on($konek)->join('retur_jual_detail','retur_jual_detail.no_retur_jual','=','retur_jual.no_retur_jual')->get();
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
                $update = ReturPenjualan::on($konek)->where('kode_customer', $data[$i]['kode_customer'])->update($tabel_baru);
            }

            //DETAIL
            $cek = Produk::on($konek)->where('kode_produk', $data[$i]['kode_produk'])->first();
            if ($cek != null) {
                $id = $cek->id;

                $tabel_baru = [
                    'kode_produk'=>$id,
                ];
                $update = ReturPenjualanDetail::on($konek)->where('kode_produk', $data[$i]['kode_produk'])->update($tabel_baru);
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
            return Datatables::of(ReturPenjualan::on($konek)->with('company','Lokasi','customer')->orderBy('created_at','desc'))->make(true);
        }
        else{
            return Datatables::of(ReturPenjualan::on($konek)->with('company','Lokasi','customer')->orderBy('created_at','desc')->where('kode_lokasi', auth()->user()->kode_lokasi))->make(true);
        }
    }

    public function exportPDF(){
        $konek = self::konek();
        $request = $_GET['no_retur_jual'];

        $returpenjualan = ReturPenjualan::on($konek)->where('no_retur_jual',$request)->first();
        $no_retur_jual = $returpenjualan->no_retur_jual;
        $no_penjualan = $returpenjualan->no_penjualan;
        $penjualan = Penjualan::on($konek)->find($no_penjualan);

        $type_ar = $penjualan->type_ar;

        $type = $penjualan->type_ar;
        $kode_customer = $penjualan->kode_customer;

        $kode_company = $penjualan->kode_company;

        $returpenjualandetail = ReturPenjualanDetail::on($konek)->where('no_retur_jual',$request)->get();

        $company = Company::where('kode_company',$kode_company)->first();
        $nama_company = $company->nama_company;

        $customer = Customer::on($konek)->where('id',$kode_customer)->first();
        $nama_customer = $customer->nama_customer_po;
        $alamat = substr($customer->alamat, 0, 70);

        $dt = Carbon\Carbon::now();
        $date_now = Carbon\Carbon::parse($dt)->format('d/m/Y');

        $tgl = $returpenjualan->tgl_retur_jual;
        $date=date_create($tgl);
        
        if($type_ar != 'Jasa'){
            $pdf = PDF::loadView('/admin/returjual/pdf', compact('returpenjualandetail','request','no_penjualan','no_retur_jual','tgl','nama_company','date_now','type','nama_customer','alamat','returpenjualan','penjualan'));
            $pdf->setPaper([0, 0, 684, 792], 'potrait');

            return $pdf->stream('Laporan Retur Penjualan '.$no_retur_jual.'.pdf');   
        }
        else{
            $pdf = PDF::loadView('/admin/returjual/pdfjasa', compact('returpenjualandetail','request','no_penjualan','no_retur_jual','tgl','nama_company','date_now','type','nama_customer','alamat','returpenjualan','penjualan'));
            $pdf->setPaper([0, 0, 684, 792], 'potrait');

            return $pdf->stream('Laporan Retur Penjualan '.$no_retur_jual.'.pdf');
        }     
    }

    public function getcustomer()
    {
        $konek = self::konek();
        $penjualan = Penjualan::on($konek)->with('customer')->where('no_penjualan',request()->id)->first();
        $output = array(
            'customer'=>$penjualan->kode_customer,
        );
        return response()->json($output);
    }

    public function getcustomer2()
    {
        $konek = self::konek();
        $penjualan = Penjualan::on($konek)->with('customer')->where('no_penjualan',request()->id)->first();
        $output = array(
            'customer'=>$penjualan->kode_customer,
        );
        return response()->json($output);
    }

    public function detail($returpenjualan)
    {   
        $konek = self::konek();
        $returpenjualan = ReturPenjualan::on($konek)->find($returpenjualan);
        $tanggal = $returpenjualan->tgl_retur_jual;
        $no_retur_jual = $returpenjualan->no_retur_jual;
        $validate = $this->periodeChecker($tanggal);
             
        if($validate == true){
            $data = ReturPenjualan::on($konek)->find($no_retur_jual);

            $list_url= route('returjual.index');
                
            $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode', 'Open')->orwhere('reopen_status', 'true')->first();

            $Produk = PenjualanDetail::on($konek)->Join('produk','produk.id','=','penjualan_detail.kode_produk')->where('penjualan_detail.no_penjualan',$returpenjualan->no_penjualan)->where(DB::raw('penjualan_detail.qty-penjualan_detail.qty_retur'),'>',0)->pluck('produk.nama_produk','produk.id');
                    
            $Parts = PenjualanDetail::on($konek)->where('no_penjualan', $data->no_penjualan)
                ->pluck('partnumber','partnumber');

            $Satuan = satuan::pluck('nama_satuan', 'kode_satuan');
               
            $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
            $tgl_jalan2 = $tgl_jalan->periode;
            $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
            $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
            $nama_lokasi = $get_lokasi->nama_lokasi;
        
            $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
            $nama_company = $get_company->nama_company;
                
            return view('admin.returjualdetail.index', compact('returpenjualan','list_url','Produk','Satuan','Company','period', 'nama_lokasi','nama_company','no_retur_jual','Parts'));
        }
    }

    function periodeChecker($tgl)
    {   
        $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
        $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
        $konek = self::konek();
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

    function produkChecker($no_returPenjualan, $tahun, $bulan, $tanggal_baru, $tgl, $returPenjualan, $koneksi)
    {   
        $konek = self::konek();
        $returPenjualandetail = ReturPenjualanDetail::on($konek)->where('no_retur_jual', $no_returPenjualan)->get();
        $data = array();
        $kode_produk = array();

        if(!empty($returPenjualandetail)){
            foreach ($returPenjualandetail as $rowdata){
                $kodeP = $rowdata->kode_produk;
                $kodeS = $rowdata->kode_satuan;
                $qtyS = $rowdata->qty_retur;
                $partS = $rowdata->partnumber;
                $mesinS = $rowdata->no_mesin;

                $data[] = array(
                    'kode_produk'=>$kodeP,
                    'kode_satuan'=>$kodeS,
                    'qty_retur'=>$qtyS,
                    'partnumber'=>$partS,
                    'no_mesin'=>$mesinS,
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
                $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi',$koneksi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

                $amount_begin = $tb_item_bulanan->begin_amount;
                $amount_masuk = $tb_item_bulanan->in_amount;
                $amount_out = $tb_item_bulanan->out_amount;
                $amount_sale = $tb_item_bulanan->sale_amount;
                $amount_adj = $tb_item_bulanan->adjustment_amount;
                $amount_op = $tb_item_bulanan->amount_opname;
                $amount_returbeli = $tb_item_bulanan->retur_beli_amount;
                $amount_returjual = $tb_item_bulanan->retur_jual_amount;
                $amount_trfin = $tb_item_bulanan->trf_in_amount;
                $amount_trfout = $tb_item_bulanan->trf_out_amount;

                $stock_ending = $tb_item_bulanan->ending_stock;
                $amount_akhir1 = $tb_item_bulanan->ending_amount;

                $returPenjualandetail2 = ReturPenjualanDetail::on($konek)->where('no_retur_jual', request()->id)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                $harga1 = $returPenjualandetail2->harga;
                $qty1 = $returPenjualandetail2->qty_retur;
                $harga_final = $harga1 / $konversi->nilai_konversi;

                $stok_awal_retur = $tb_item_bulanan->retur_jual_stock;
                $amount_awal_retur = $tb_item_bulanan->retur_jual_amount;

                $qty_baru = $data[$i]['qty_retur']*$konversi->nilai_konversi;
                $qty_baru2 = $data[$i]['qty_retur'];
                $partnumber = $data[$i]['partnumber'];
                $no_mesin = $data[$i]['no_mesin'];

                $stok_retur_jual = $stok_awal_retur - $qty_baru;
                $amount_returjual = $amount_awal_retur - ($harga_final*$qty_baru);

                $end_stok = $stock_ending - $qty_baru;

                $end_amount = $amount_begin + $amount_masuk - $amount_out - $amount_sale + $amount_trfin - $amount_trfout + $amount_adj + $amount_op - $amount_returbeli + $amount_returjual;

                $produks = $data[$i]['kode_produk'];
                $cek_produk = Produk::on($konek)->find($produks);
                $nama_produk = $cek_produk->nama_produk;

                if($end_stok < 0){
                    exit("Barang $nama_produk tidak memiliki cukup stok. Silahkan UNPOST RETUR PEMBELIAN terlebih dahulu.");
                }

                $tgl_returbeli1 = $returPenjualan->tgl_retur_jual;
                $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_returbeli1 )->year;
                $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_returbeli1 )->month;

                $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                $status_reopen = $reopen->reopen_status;

                if($status_reopen == 'true'){
                    $tgl_returbeli = $returPenjualan->tgl_retur_jual;
                    $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_returbeli)->year;
                    $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_returbeli)->month;

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
                        $returPenjualandetail2 = ReturPenjualanDetail::on($konek)->where('no_retur_jual', request()->id)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                        $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                        $harga1 = $returPenjualandetail2->harga;
                        $qty1 = $returPenjualandetail2->qty_retur;
                        $harga_final = $harga1 / $konversi->nilai_konversi;
                                                   
                        $stok_masuk = $data[$i]['qty_retur']*$konversi->nilai_konversi;
                                                
                        $amount_masuk = $harga_final*$stok_masuk;

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

                        $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$koneksi)->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->where('partnumber',$data[$i]['partnumber'])->first();
                                
                        $bs = $tb_item_bulanan2->begin_stock;
                        $ba = $tb_item_bulanan2->begin_amount;   
                        $es = $tb_item_bulanan2->ending_stock;
                        $ea = $tb_item_bulanan2->ending_amount;

                        $begin_stock1 = $bs - $stok_masuk;
                        $begin_amount1 = $ba - $amount_masuk;

                        $end_stok1 = $es - $stok_masuk;
                        $end_amount1 = $ea - $amount_masuk;

                        if($end_stok1 < 0){
                            exit("Barang $nama_produk tidak memiliki cukup stok. Silahkan UNPOST RETUR PEMBELIAN terlebih dahulu.");
                        }
                        
                        $j++;
                    }
                                            
                }
            }
        }

        return true;
    }

    public function Posting()
    {
        $konek = self::konek();
        $level = auth()->user()->level;
        $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode','Disable')->first();

        if($cek_bulan == null || $level == 'superadministrator' || $level == 'user_rince' || $level == 'user_herry'){
            $returPenjualan = ReturPenjualan::on($konek)->find(request()->id);
            $cek_status = $returPenjualan->status;
            if($cek_status != 'OPEN'){
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'POST No. Retur Penjualan: '.$returPenjualan->no_retur_jual.' sudah dilakukan! Pastikan Anda tidak membuka menu RETUR PENJUALAN lebih dari 1',
                ];
                return response()->json($message);
            }

            $tgl = $returPenjualan->tgl_retur_jual;
            $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
            $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
            $tanggal = '01';

            $tanggal_baru = Carbon\Carbon::createFromDate($tahun, $bulan, $tanggal)->toDateString();
            $lokasi = $returPenjualan->kode_lokasi;
            $validate = $this->periodeChecker($tgl);

            if($validate == true){
                $no_returPenjualan = request()->id;
                $returPenjualandetail = ReturPenjualanDetail::on($konek)->where('no_retur_jual', $no_returPenjualan)->get();
                $data = array();
                $kode_produk = array();

                if(!empty($returPenjualandetail)){
                    foreach ($returPenjualandetail as $rowdata){
                        $kodeP = $rowdata->kode_produk;
                        $kodeS = $rowdata->kode_satuan;
                        $qtyS = $rowdata->qty_retur;
                        $partS = $rowdata->partnumber;
                        $mesinS = $rowdata->no_mesin;

                        $data[] = array(
                            'kode_produk'=>$kodeP,
                            'kode_satuan'=>$kodeS,
                            'qty_retur'=>$qtyS,
                            'partnumber'=>$partS,
                            'no_mesin'=>$mesinS,
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
                        $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

                        $amount_begin = $tb_item_bulanan->begin_amount;
                        $amount_masuk = $tb_item_bulanan->in_amount;
                        $amount_out = $tb_item_bulanan->out_amount;
                        $amount_sale = $tb_item_bulanan->sale_amount;     
                        $amount_adj = $tb_item_bulanan->adjustment_amount;
                        $amount_op = $tb_item_bulanan->amount_opname;
                        $amount_returbeli = $tb_item_bulanan->retur_beli_amount;
                        $amount_returjual = $tb_item_bulanan->retur_jual_amount;
                        $amount_trfin = $tb_item_bulanan->trf_in_amount;
                        $amount_trfout = $tb_item_bulanan->trf_out_amount;

                        $stock_ending = $tb_item_bulanan->ending_stock;
                        $amount_akhir1 = $tb_item_bulanan->ending_amount;

                        $returPenjualandetail2 = ReturPenjualanDetail::on($konek)->where('no_retur_jual', request()->id)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                        $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                        $harga1 = $returPenjualandetail2->harga;
                        $qty1 = $returPenjualandetail2->qty_retur;
                        $harga_final = $harga1 / $konversi->nilai_konversi;

                        $stok_awal_retur = $tb_item_bulanan->retur_jual_stock;
                        $amount_awal_retur = $tb_item_bulanan->retur_jual_amount;

                        $qty_baru = $data[$i]['qty_retur']*$konversi->nilai_konversi;
                        $qty_baru2 = $data[$i]['qty_retur'];
                        $partnumber = $data[$i]['partnumber'];
                        $no_mesin = $data[$i]['no_mesin'];

                        $stok_retur_jual = $stok_awal_retur + $qty_baru;
                        $amount_returjual = $amount_awal_retur + ($harga_final*$qty_baru);

                        $end_stok = $stock_ending + $qty_baru;

                        $end_amount = $amount_begin + $amount_masuk - $amount_out - $amount_sale + $amount_trfin - $amount_trfout + $amount_adj + $amount_op - $amount_returbeli + $amount_returjual;

                        if($end_stok != 0){
                            $hpp = $end_amount / $end_stok;
                        }else{
                            $hpp = $tb_item_bulanan->hpp;
                        }

                        $tabel_baru = [
                            'retur_jual_stock'=>$stok_retur_jual,
                            'retur_jual_amount'=>$amount_returjual,
                            'ending_stock'=>$end_stok,
                            'ending_amount'=>$end_amount,
                            'hpp'=>$hpp,
                        ];

                        $produk_awal = $tb_item_bulanan->kode_produk;
                        $update_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$produk_awal)->where('kode_lokasi',$lokasi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($tabel_baru); 

                        $barang = $data[$i]['kode_produk'];
                        $create_returPenjualan = $returPenjualan->created_at;
                        $tabel_history = [
                            'kode_produk'=>$barang,
                            'no_transaksi'=>$no_returPenjualan,
                            'kode_lokasi'=>auth()->user()->kode_lokasi,
                            'tanggal_transaksi'=>$tgl,
                            'jam_transaksi'=>$create_returPenjualan,
                            'qty_transaksi'=>$qty_baru,
                            'harga_transaksi'=>$harga_final,
                            'total_transaksi'=>($harga_final*$qty_baru),
                            'kode_lokasi'=>$lokasi,
                        ];

                        $update_produk_history = tb_produk_history::on($konek)->create($tabel_history);

                        $no_penjualan = request()->no_penjualan;
                        $penjualandetail1 = PenjualanDetail::on($konek)->where('no_penjualan', $no_penjualan)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();
                        $qty_rec = $penjualandetail1->qty_retur;
                        $penjualandetail1->qty_retur = $qty_rec + $qty_baru2;
                        $penjualandetail1->save();

                        $tgl_returbeli1 = $returPenjualan->tgl_retur_jual;
                        $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_returbeli1)->year;
                        $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_returbeli1)->month;

                        $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                        $status_reopen = $reopen->reopen_status;

                        if($status_reopen == 'true'){
                            $tgl_returbeli = $returPenjualan->tgl_retur_jual;
                            $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_returbeli)->year;
                            $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_returbeli)->month;

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
                                $returPenjualandetail2 = ReturPenjualanDetail::on($konek)->where('no_retur_jual', request()->id)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                                $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                                $harga1 = $returPenjualandetail2->harga;
                                $qty1 = $returPenjualandetail2->qty_retur;
                                $harga_final = $harga1 / $konversi->nilai_konversi;

                                $stok_retur_jual = $data[$i]['qty_retur']*$konversi->nilai_konversi; 
                                $amount_returjual = $harga_final*$stok_retur_jual;

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

                                $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$lokasi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->first();

                                $bs = $tb_item_bulanan2->begin_stock;
                                $ba = $tb_item_bulanan2->begin_amount;

                                $es = $tb_item_bulanan2->ending_stock;
                                $ea = $tb_item_bulanan2->ending_amount;

                                $begin_stock1 = $bs + $stok_retur_jual;
                                $begin_amount1 = $ba + $amount_returjual;

                                $end_stok1 = $es + $stok_retur_jual;
                                $end_amount1 = $ea + $amount_returjual;

                                if($end_stok1 != 0){
                                    $hpp = $end_amount1 / $end_stok1;
                                }else{
                                    $hpp = $tb_item_bulanan2->hpp;
                                }

                                $tabel_baru2 = [
                                    'begin_stock'=>$begin_stock1,
                                    'begin_amount'=>$begin_amount1,
                                    'ending_stock'=>$end_stok1,
                                    'ending_amount'=>$end_amount1,
                                    'hpp'=>$hpp,
                                ];

                                $update_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$lokasi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->update($tabel_baru2);

                                $j++;
                            }
                        }
                    }
                }

                $returPenjualan->status = "POSTED";
                $returPenjualan->save();

                $hitung = ReturPenjualanDetail::on($konek)->where('no_retur_jual', request()->id)->get();
                $leng_retur = count($hitung);

                $penjualan = Penjualan::on($konek)->find($no_penjualan);
                $penjualan->total_retur = $leng_retur;
                $penjualan->save(); 

                $cek_jual = PenjualanDetail::on($konek)->where('no_penjualan', $no_penjualan)->where('qty_retur',0)->first();
                if($cek_jual == null){
                    $penjualan = Penjualan::on($konek)->find($no_penjualan);
                    $penjualan->status = "RETUR";
                    $penjualan->save(); 
                }

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Post Retur Penjualan: '.$returPenjualan->no_retur_jual.'.','created_by'=>$nama,'updated_by'=>$nama];
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
                    'title' => 'Update',
                    'message' => 'Data gagal di POST, re-open [Bulan '.$bulan.'; Tahun '.$tahun.'].'
                ];
                return response()->json($message);
            }
        }else{
            $message = [
                        'success' => false,
                        'title' => 'Simpan',
                        'message' => 'Anda tidak mempunyai akses posting data',
                        ];
            return response()->json($message);
        }
        
    }

    public function Unposting()
    {
        $konek = self::konek();
        $level = auth()->user()->level;
        $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode','Disable')->first();
        $returPenjualan = ReturPenjualan::on($konek)->find(request()->id);
        $cek_open = ReturPenjualan::on($konek)->where('status','OPEN')->first();
        if ($cek_open != null){
            $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'UNPOST No. Retur Penjualan: '.$returPenjualan->no_retur_jual.' gagal, masih ada retur Penjualan yang OPEN.',
            ];
            return response()->json($message);
        }

        if($cek_bulan == null || $level == 'superadministrator' || $level == 'user_rince' || $level == 'user_herry'){
            $cek_status = $returPenjualan->status;
            if($cek_status != 'POSTED'){
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'UNPOST No. Retur Penjualan: '.$returPenjualan->no_retur_jual.' sudah dilakukan! Pastikan Anda tidak membuka menu RETUR PENJUALAN lebih dari 1',
                ];
                return response()->json($message);
            }

            $no_returPenjualan = $returPenjualan->no_retur_jual;
            $koneksi = $returPenjualan->kode_lokasi;
            
            $tgl = $returPenjualan->tgl_retur_jual;
            $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
            $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
            $tanggal = '01';

            $tanggal_baru = Carbon\Carbon::createFromDate($tahun, $bulan, $tanggal)->toDateString();
            $lokasi = $returPenjualan->kode_lokasi;
            $validate = $this->periodeChecker($tgl);

            if($validate != true){  
                $message = [
                    'success' => false,
                    'title' => 'Update',
                    'message' => 'Data gagal di UNPOSTING, re-open [Bulan '.$bulan.'; Tahun '.$tahun.'].'
                ];
                return response()->json($message);
            }

            $validate_produk = $this->produkChecker($no_returPenjualan, $tahun, $bulan, $tanggal_baru, $tgl, $returPenjualan, $koneksi);

            if($validate_produk == true){
                $no_returPenjualan = request()->id;
                $returPenjualandetail = ReturPenjualanDetail::on($konek)->where('no_retur_jual', $no_returPenjualan)->get();
                $data = array();
                $kode_produk = array();

                if(!empty($returPenjualandetail)){
                    foreach ($returPenjualandetail as $rowdata){
                        $kodeP = $rowdata->kode_produk;
                        $kodeS = $rowdata->kode_satuan;
                        $qtyS = $rowdata->qty_retur;
                        $partS = $rowdata->partnumber;
                        $mesinS = $rowdata->no_mesin;

                        $data[] = array(
                            'kode_produk'=>$kodeP,
                            'kode_satuan'=>$kodeS,
                            'qty_retur'=>$qtyS,
                            'partnumber'=>$partS,
                            'no_mesin'=>$mesinS,
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
                        $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

                        $amount_begin = $tb_item_bulanan->begin_amount;
                        $amount_masuk = $tb_item_bulanan->in_amount;
                        $amount_out = $tb_item_bulanan->out_amount;
                        $amount_sale = $tb_item_bulanan->sale_amount;
                        $amount_adj = $tb_item_bulanan->adjustment_amount;
                        $amount_op = $tb_item_bulanan->amount_opname;
                        $amount_returbeli = $tb_item_bulanan->retur_beli_amount;
                        $amount_returjual = $tb_item_bulanan->retur_jual_amount;
                        $amount_trfin = $tb_item_bulanan->trf_in_amount;
                        $amount_trfout = $tb_item_bulanan->trf_out_amount;

                        $stock_ending = $tb_item_bulanan->ending_stock;
                        $amount_akhir1 = $tb_item_bulanan->ending_amount;

                        $returPenjualandetail2 = ReturPenjualanDetail::on($konek)->where('no_retur_jual', request()->id)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                        $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                        $harga1 = $returPenjualandetail2->harga;
                        $qty1 = $returPenjualandetail2->qty_retur;
                        $harga_final = $harga1 / $konversi->nilai_konversi;

                        $stok_awal_retur = $tb_item_bulanan->retur_jual_stock;
                        $amount_awal_retur = $tb_item_bulanan->retur_jual_amount;

                        $qty_baru = $data[$i]['qty_retur']*$konversi->nilai_konversi;
                        $qty_baru2 = $data[$i]['qty_retur'];
                        $partnumber = $data[$i]['partnumber'];
                        $no_mesin = $data[$i]['no_mesin'];

                        $stok_retur_jual = $stok_awal_retur - $qty_baru;
                        $amount_returjual = $amount_awal_retur - ($harga_final*$qty_baru);

                        $end_stok = $stock_ending - $qty_baru;

                        $end_amount = $amount_begin + $amount_masuk - $amount_out - $amount_sale + $amount_trfin - $amount_trfout + $amount_adj + $amount_op - $amount_returbeli + $amount_returjual;

                        if($end_stok != 0){
                            $hpp = $end_amount / $end_stok;
                        }else{
                            $hpp = $tb_item_bulanan->hpp;
                        }

                        $tabel_baru = [
                            'retur_jual_stock'=>$stok_retur_jual,
                            'retur_jual_amount'=>$amount_returjual,
                            'ending_stock'=>$end_stok,
                            'ending_amount'=>$end_amount,
                            'hpp'=>$hpp,
                        ];

                        $produk_awal = $tb_item_bulanan->kode_produk;
                        $update_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$produk_awal)->where('kode_lokasi',$lokasi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($tabel_baru); 

                        $update_produk_history = tb_produk_history::on($konek)->where('no_transaksi',$no_returPenjualan)->delete();

                        $no_penjualan = request()->no_penjualan;

                        $penjualandetail1 = PenjualanDetail::on($konek)->where('no_penjualan', $no_penjualan)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();
                        $qty_rec = $penjualandetail1->qty_retur;
                        $penjualandetail1->qty_retur = $qty_rec - $qty_baru2;
                        $penjualandetail1->save();

                        $tgl_returbeli1 = $returPenjualan->tgl_retur_jual;
                        $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_returbeli1 )->year;
                        $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_returbeli1 )->month;

                        $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                        $status_reopen = $reopen->reopen_status;

                        if($status_reopen == 'true'){
                            $tgl_returbeli = $returPenjualan->tgl_retur_jual;
                            $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_returbeli)->year;
                            $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_returbeli)->month;

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
                                $returPenjualandetail2 = ReturPenjualanDetail::on($konek)->where('no_retur_jual', request()->id)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                                $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                                $harga1 = $returPenjualandetail2->harga;
                                $qty1 = $returPenjualandetail2->qty_retur;
                                $harga_final = $harga1 / $konversi->nilai_konversi;
                                                   
                                $stok_masuk = $data[$i]['qty_retur']*$konversi->nilai_konversi;
                                $amount_masuk = $harga_final*$stok_masuk;

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

                                $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$lokasi)->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->where('partnumber',$data[$i]['partnumber'])->first();
                                
                                    $bs = $tb_item_bulanan2->begin_stock;
                                    $ba = $tb_item_bulanan2->begin_amount;   
                                    $es = $tb_item_bulanan2->ending_stock;
                                    $ea = $tb_item_bulanan2->ending_amount;

                                    $begin_stock1 = $bs - $stok_masuk;
                                    $begin_amount1 = $ba - $amount_masuk;

                                    $end_stok1 = $es - $stok_masuk;
                                    $end_amount1 = $ea - $amount_masuk;

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

                                    $update_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$lokasi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->update($tabel_baru2);
                                
                                $j++;
                            }
                                            
                        }
                    }
                }

                $returPenjualan->status = "OPEN";
                $returPenjualan->save();    

                $penjualan = Penjualan::on($konek)->find($no_penjualan);
                $penjualan->total_retur = 0;
                $penjualan->save(); 

                $penjualan->status = "POSTED";
                $penjualan->save(); 

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Unpost No. Retur Penjualan: '.$returPenjualan->no_retur_jual.'.','created_by'=>$nama,'updated_by'=>$nama];

                user_history::on($konek)->create($tmp);

                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data berhasil di UNPOST.'
                ];
                return response()->json($message);
            }
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
        $tanggal = $request->tgl_retur_jual;
        $validate = $this->periodeChecker($tanggal);
        
        $reopen = tb_akhir_bulan::on($konek)->where('reopen_status','true')->first();

        if ($reopen != null){
            $tgl = $reopen->periode;
            $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
            $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
            $jual = ReturPenjualan::on($konek)->whereMonth('tgl_retur_jual',$bulan_transaksi)->whereYear('tgl_retur_jual',$tahun_transaksi)->where('status','OPEN')->get();
            if (count($jual) >= 1){
                $message = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Masih ada Retur Penjualan yang OPEN.'
                ];
               return response()->json($message);
            }
        }else {
            $tgl = tb_akhir_bulan::on($konek)->where('status_periode','Open')->first();
            $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl->periode)->year;
            $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl->periode)->month;
            $jual = ReturPenjualan::on($konek)->whereMonth('tgl_retur_jual',$bulan_transaksi)->whereYear('tgl_retur_jual',$tahun_transaksi)->where('status','OPEN')->get();
            if (count($jual) >= 1){
                $message = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Masih ada Retur Penjualan yang OPEN.'
                ];
               return response()->json($message);
            }
        }
             
        if($validate == true){
            if ($request->keterangan == ''){
               $message = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Keterangan harus diisi.'
                ];
               return response()->json($message);                    
            }
            
            $returPenjualan = ReturPenjualan::on($konek)->create($request->all());

            $no = ReturPenjualan::on($konek)->orderBy('created_at','desc')->first();
            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Simpan No. Retur Penjualan: '.$no->no_retur_jual.'.','created_by'=>$nama,'updated_by'=>$nama];
            //dd($tmp);
            user_history::on($konek)->create($tmp);

            $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => 'Data telah di Disimpan.'
            ];
            return response()->json($message);
        }

        else{
            $message = [
            'success' => false,
            'title' => 'Simpan',
            'message' => '<b>Periode</b> ['.$tanggal.'] <b>Telah Ditutup / Belum Dibuka</b>'
            ];
            return response()->json($message);
        }
    }

    public function Showdetail()
    {
        $konek = self::konek();
        $total_qty = 0;
        $total_harga = 0;
        $grand_total = 0;
        $Penjualandetail= ReturPenjualanDetail::on($konek)->with('produk','satuan')->where('no_retur_jual',request()->id)
        ->orderBy('created_at', 'desc')->get();

        $output = array();

        foreach ($Penjualandetail as $row){
            $total_qty += $row->qty_retur;
            $subtotal = $row->harga * $row->qty_retur;
            $total_harga += $subtotal;
            $grand_total = number_format($total_harga,2,",",".");
        }

        if($Penjualandetail){
            foreach($Penjualandetail as $row)
            {

                $no_Penjualan = $row->no_Penjualan;
                $produk = $row->produk->nama_produk;
                $partnumber = $row->partnumber;
                $satuan = $row->satuan->nama_satuan;
                $qty_retur = $row->qty_retur;
                $harga = $row->harga_jual;
                $subtotal =  number_format($row->harga_jual * $row->qty_retur,0,",",".");

                $output[] = array(
                    'no_Penjualan'=>$no_Penjualan,
                    'produk'=>$produk,
                    'partnumber'=>$partnumber,
                    'satuan'=>$satuan,
                    'qty_retur'=>$qty_retur,
                    'harga'=>$harga,
                    'subtotal'=>$subtotal,
                );
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

    public function edit_retur_jual()
    {   
        $konek = self::konek();
        $no_retur_jual = request()->id;
        $data = ReturPenjualan::on($konek)->find($no_retur_jual);
        $status = $data->status;
        // dd($status);
        if($status == 'OPEN'){
             $output = array(
                'no_retur_jual'=> $data->no_retur_jual,
                'tgl_retur_jual'=>$data->tgl_retur_jual,
                'no_penjualan'=>$data->no_penjualan,
                'kode_customer'=>$data->kode_customer,
                'keterangan'=>$data->keterangan,
                'total_item'=>$data->total_item,
                'kode_lokasi'=>$data->kode_lokasi,
                'kode_company'=>$data->kode_company,
            );

        return response()->json($output);
        }
    }

    public function updateAjax(Request $request)
    {
        $konek = self::konek();
        $tanggal = $request->tgl_retur_jual;
        
        $Penjualan = ReturPenjualan::on($konek)->find($request->no_retur_jual)->update($request->all());
        $nama = auth()->user()->name;
        $tmp = ['nama' => $nama,'aksi' => 'Edit No. Retur Penjualan: '.$request->no_retur_jual.'.','created_by'=>$nama,'updated_by'=>$nama];
        //dd($tmp);
        user_history::on($konek)->create($tmp);
              
        $message = [
            'success' => true,
            'title' => 'Update',
            'message' => 'Data telah di Update.'
        ];
        return response()->json($message);
    }


    public function hapus_penjualan()
    {
        $konek = self::konek();
        $level = auth()->user()->level;
        if($level == 'superadministrator' || $level == 'administrator'){
            $no_retur_jual = request()->id;
            $data = ReturPenjualan::on($konek)->find($no_retur_jual);
            $tanggal = $data->tgl_retur_jual;

            $validate = $this->periodeChecker($tanggal);
                 
            if($validate == true){
                $status = $data->status;

                if($status == 'OPEN'){
                    $data->delete();

                    $nama = auth()->user()->name;
                    $tmp = ['nama' => $nama,'aksi' => 'Hapus No. Retur Penjualan: '.$no_retur_jual.'.','created_by'=>$nama,'updated_by'=>$nama];
                        //dd($tmp);
                    user_history::on($konek)->create($tmp);

                    $message = [
                        'success' => true,
                        'title' => 'Update',
                        'message' => 'Data ['.$data->no_retur_jual.'] telah dihapus.'
                    ];
                    return response()->json($message);
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
