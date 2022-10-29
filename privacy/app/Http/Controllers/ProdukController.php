<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Produk;
use App\Models\KategoriProduk;
use App\Models\Unit;
use App\Models\Merek;
use App\Models\Ukuran;
use App\Models\satuan;
use App\Models\Company;
use App\Models\ProdukCounter;
use App\Models\ProdukDetail;
use App\Models\PembelianDetail;
use App\Models\PemakaianDetail;
use App\Models\PemakaianbanDetail;
use App\Models\PenerimaanDetail;
use App\Models\OpnameDetail;
use App\Models\AdjustmentDetail;
use App\Models\PenjualanDetail;
use App\Models\TransferDetail;
use App\Models\TransferInDetail;
use App\Models\LokasiRak;
use App\Models\Konversi;
use App\Models\tb_item_bulanan;
use App\Models\tb_akhir_bulan;
use App\Models\tb_produk_history;
use App\Models\MasterLokasi;
use App\Models\user_history;
use App\Exports\MonthlyExport;
use App\Exports\ListprodukExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon;
use DB;
use PDF;

class ProdukController extends Controller
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
        $create_url = route('produk.create');
        $Kategori = KategoriProduk::pluck('nama_kategori', 'kode_kategori');
        $Merek = Merek::on($konek)->pluck('nama_merek', 'kode_merek');
        $Ukuran= Ukuran::on($konek)->pluck('nama_ukuran', 'kode_ukuran');
        $Unit= Unit::pluck('kode_unit', 'kode_unit');
        $Satuan= satuan::pluck('nama_satuan', 'kode_satuan');
        $Company= Company::pluck('nama_company', 'kode_company');
        $Produk = Produk::on($konek)->pluck('nama_produk','id');
        $lokasi = tb_item_bulanan::on($konek)->pluck('kode_lokasi', 'kode_lokasi');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;
        
        $level = auth()->user()->level;
        
        return view('admin.produk.index',compact('create_url','Unit','Kategori','Merek','Ukuran','Satuan','Company','period','Produk', 'nama_lokasi','lokasi','nama_company','level'));
    }

    public function anyData()
    {
        $konek = self::konek();
        $level = auth()->user()->level;
        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        if ($level == 'sany'){
            return Datatables::of(Produk::on($konek)->select('produk.id','produk.nama_produk','produk.partnumber','merek.nama_merek', DB::raw('SUM(tb_item_bulanan.ending_stock) as totalstock'))->join('tb_item_bulanan', 'produk.id','=','tb_item_bulanan.kode_produk')->join('merek', 'produk.kode_merek','=','merek.kode_merek')->where('tb_item_bulanan.periode', $tgl_jalan2)->where('merek.nama_merek', 'SANY')->groupBy('produk.id'))->make(true);
        }else {
            return Datatables::of(Produk::on($konek)->with('kategoriproduk','merek','satuan','company','ukuran'))->make(true);
        }
    }

    public function showstock()
    {
        $konek = self::konek();
        $kode_produk = request()->id;
        $lokasi = auth()->user()->kode_lokasi;
        $bulan = tb_akhir_bulan::on($konek)->where('status_periode','Open')->orwhere('reopen_status','true')->first();
        if ($lokasi == 'HO'){
            $data = tb_item_bulanan::on($konek)->where('kode_produk',$kode_produk)->where('periode',$bulan->periode)->get();
        }else {
            $data = tb_item_bulanan::on($konek)->where('kode_produk',$kode_produk)->where('kode_lokasi',$lokasi)->where('periode',$bulan->periode)->get();
        }
        
        $output = array();

        if ($data){
            foreach ($data as $row){
                $output[] = array(
                    'kode_produk'=>$row->kode_produk,
                    'partnumber'=>$row->partnumber,
                    'no_mesin'=>$row->no_mesin,
                    'kode_lokasi'=>$row->kode_lokasi,
                    'ending_stock'=>$row->ending_stock,
                    'hpp'=>$row->hpp,
                );
            }
        }else {
            $output = array(
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Maaf Data Terkait Tidak Ada'
            );
        }
        return response()->json($output);
    }
    
    public function exportexcel(){
        $konek = self::konek();
        $kode_company = auth()->user()->kode_company;
        $nama = Company::find($kode_company);
        return Excel::download(new ListprodukExport($kode_company), 'List Produk '.$nama->nama_company.'.xlsx');
    }


    public function Showmonthly()
    {   
        $konek = self::konek();
        $lokasi = auth()->user()->kode_lokasi;
        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;

        $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_jalan2)->year;
        $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_jalan2)->month;

        if ($lokasi == 'HO'){
            $monthly = tb_item_bulanan::on($konek)->where('kode_produk',request()->id)->whereYear('periode', $tahun)->whereMonth('periode', $bulan)->get();
        }
        else{
            $monthly = tb_item_bulanan::on($konek)->where('kode_produk',request()->id)->where('kode_lokasi',$lokasi)->whereYear('periode', $tahun)->whereMonth('periode', $bulan)->get();
        }

        $output = array();

        if($monthly){
            foreach($monthly as $row)
            {
                $periode = $row->periode;
                $nama_bulan = Carbon\Carbon::parse($periode)->format('F Y');
                $partnumber = $row->partnumber;
                $no_mesin = $row->no_mesin;
                $begin_stock = $row->begin_stock;
                $begin_amount = $row->begin_amount;
                $in_stock = $row->in_stock;
                $in_amount = $row->in_amount;
                $out_stock = $row->out_stock;
                $out_amount = $row->out_amount;
                $sale_stock = $row->sale_stock;
                $sale_amount = $row->sale_amount;
                $trf_in = $row->trf_in;
                $trf_in_amount = $row->trf_in_amount;
                $trf_out = $row->trf_out;
                $trf_out_amount = $row->trf_out_amount;
                $adjustment_stock = $row->adjustment_stock;
                $adjustment_amount = $row->adjustment_amount;
                $stock_opname = $row->stock_opname;
                $amount_opname = $row->amount_opname;
                $retur_beli_stock = $row->retur_beli_stock;
                $retur_beli_amount = $row->retur_beli_amount;
                $retur_jual_stock = $row->retur_jual_stock;
                $retur_jual_amount = $row->retur_jual_amount;
                $disassembling_stock = $row->disassembling_stock;
                $disassembling_amount = $row->disassembling_amount;
                $assembling_stock = $row->assembling_stock;
                $assembling_amount = $row->assembling_amount;
                $ending_stock = $row->ending_stock;
                $ending_amount = $row->ending_amount;
                $hpp = $row->hpp;
                $kode_lokasi = $row->kode_lokasi;

                $output[] = array(
                    'periode'=>$nama_bulan,
                    'partnumber'=>$partnumber,
                    'no_mesin'=>$no_mesin,
                    'begin_stock'=>$begin_stock,
                    'begin_amount'=>$begin_amount,
                    'in_stock'=>$in_stock,
                    'in_amount'=>$in_amount,
                    'out_stock'=>$out_stock,
                    'out_amount'=>$out_amount,
                    'sale_stock'=>$sale_stock,
                    'sale_amount'=>$sale_amount,
                    'trf_in'=>$trf_in,
                    'trf_in_amount'=>$trf_in_amount,
                    'trf_out'=>$trf_out,
                    'trf_out_amount'=>$trf_out_amount,
                    'adjustment_stock'=>$adjustment_stock,
                    'adjustment_amount'=>$adjustment_amount,
                    'stock_opname'=>$stock_opname,
                    'amount_opname'=>$amount_opname,
                    'retur_beli_stock'=>$retur_beli_stock,
                    'retur_beli_amount'=>$retur_beli_amount,
                    'retur_jual_stock'=>$retur_jual_stock,
                    'retur_jual_amount'=>$retur_jual_amount,
                    'disassembling_stock'=>$disassembling_stock,
                    'disassembling_amount'=>$disassembling_amount,
                    'assembling_stock'=>$assembling_stock,
                    'assembling_amount'=>$assembling_amount,
                    'ending_stock'=>$ending_stock,
                    'ending_amount'=>$ending_amount,
                    'hpp'=>$hpp,
                    'kode_lokasi'=>$kode_lokasi,
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


    public function store(Request $request)
    {
        $konek = self::konek();
        $nama_produk = $request->nama_produk;
        $cek_produk = Produk::on($konek)->where('nama_produk',$nama_produk)->first();
        if ($cek_produk==null){
            $Produk = Produk::on($konek)->create($request->all());
            $prod = Produk::on($konek)->orderBy('created_at','desc')->first();
            
            $doppel = Produk::on($konek)->select(DB::raw('count(nama_produk) as conto'))->where('nama_produk',$prod->nama_produk)->groupBy('nama_produk')->first();
            
            if ($doppel->conto > 1){
                $delconto = Produk::on($konek)->where('nama_produk',$request->nama_produk)->orderBy('id', 'desc')->delete();
            }
            
            $get_produk = Produk::on($konek)->where('nama_produk',$request->nama_produk)->orderBy('id', 'asc')->first();
            $Satuan = satuan::find($request->kode_satuan);

            $kode_produk = $get_produk->id;
            $kode_satuan = $request->kode_satuan;
            $satuan_terbesar = $Satuan->nama_satuan;
            $nilai_konversi = 1;
            $kode_satuanterkecil = $request->kode_satuan;
            $satuan_terkecil = $Satuan->nama_satuan;

            $data = [
                'kode_produk'=>$kode_produk,
                'kode_satuan'=>$kode_satuan,
                'satuan_terbesar'=>$satuan_terbesar,
                'nilai_konversi'=>$nilai_konversi,
                'kode_satuanterkecil'=>$kode_satuanterkecil,
                'satuan_terkecil'=>$satuan_terkecil,
            ];

            $konversi = Konversi::on($konek)->create($data);

            $cek_tipeproduk = $request->tipe_produk;
            $cek_kategoriproduk = $request->kode_kategori;

            $period = tb_akhir_bulan::on($konek)->where('status_periode','Open')->orwhere('reopen_status','true')->first();

            //MEMBUAT DATA MONTHLY UNTUK PRODUK BARU
            if ($cek_tipeproduk != 'Serial'){
                $tabel_baru = [
                    'periode'=>$period->periode,
                    'kode_produk'=>$kode_produk,
                    'partnumber'=>$request->partnumber,
                    'no_mesin'=>'-',
                    'begin_stock'=>0,
                    'begin_amount'=>0,
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
                    'disassembling_stock'=>0,
                    'disassembling_amount'=>0,
                    'assembling_stock'=>0,
                    'assembling_amount'=>0,
                    'ending_stock'=>0,
                    'ending_amount'=>0,
                    'hpp'=>0,
                    'kode_lokasi'=>auth()->user()->kode_lokasi,
                    'kode_company'=>auth()->user()->kode_company,
                ];

                $create_item_bulanan = tb_item_bulanan::on($konek)->create($tabel_baru);
            }else{
                if($cek_kategoriproduk != 'UNIT' && $cek_kategoriproduk != 'BAN'){
                    $tabel_baru = [
                        'periode'=>$period->periode,
                        'kode_produk'=>$kode_produk,
                        'partnumber'=>$request->partnumber,
                        'no_mesin'=>'-',
                        'begin_stock'=>0,
                        'begin_amount'=>0,
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
                        'disassembling_stock'=>0,
                        'disassembling_amount'=>0,
                        'assembling_stock'=>0,
                        'assembling_amount'=>0,
                        'ending_stock'=>0,
                        'ending_amount'=>0,
                        'hpp'=>0,
                        'kode_lokasi'=>auth()->user()->kode_lokasi,
                        'kode_company'=>auth()->user()->kode_company,
                    ];

                    $create_item_bulanan = tb_item_bulanan::on($konek)->create($tabel_baru);
                }
            }
            
            // $konversi_simbol = Produk::on($konek)->where('nama_produk', 'LIKE', '%&%')->update(['nama_produk' => DB::raw("REPLACE(nama_produk,  '&', 'DAN')")]);
            // $konversi_simbol2 = Produk::on($konek)->where('partnumber', 'LIKE', '%&%')->update(['partnumber' => DB::raw("REPLACE(partnumber,  '&', 'DAN')")]);
            // $konversi_simbol3 = tb_item_bulanan::on($konek)->where('partnumber', 'LIKE', '%&%')->update(['partnumber' => DB::raw("REPLACE(partnumber,  '&', 'DAN')")]);
            
            $message = [
                'success' => true,
                'title' => 'Simpan',
                'message' => 'Data telah di Disimpan.'
            ];
            return response()->json($message);
            
        } else{
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Produk Sudah Ada',
            ];
            return response()->json($message);  
        }
    }

    public function showhistory()
    {
        $konek = self::konek();
        $lokasi = auth()->user()->kode_lokasi;

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;

        $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_jalan2)->year;
        $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_jalan2)->month;

        $kode_produk = request()->id;

        if ($lokasi == 'HO'){
            $data = tb_produk_history::on($konek)->where('kode_produk',$kode_produk)->where('kode_lokasi',$lokasi)->whereYear('tanggal_transaksi', $tahun)->whereMonth('tanggal_transaksi', $bulan)->get();    
        }else{
            $data = tb_produk_history::on($konek)->where('kode_produk',$kode_produk)->where('kode_lokasi',$lokasi)->whereYear('tanggal_transaksi', $tahun)->whereMonth('tanggal_transaksi', $bulan)->get();    
        }

        $output = array();

        if ($data){
            foreach ($data as $row){
                $tanggal_transaksi = $row->tanggal_transaksi;
                $no_transaksi = $row->no_transaksi;
                $qty_transaksi = $row->qty_transaksi;
                $total_transaksi = $row->total_transaksi;
                $created_by = $row->created_by;

                $output[] = array(
                    'tanggal_transaksi'=>$tanggal_transaksi,
                    'no_transaksi'=>$no_transaksi,
                    'qty_transaksi'=>$qty_transaksi,
                    'total_transaksi'=>$total_transaksi,     
                    'created_by'=>$created_by,              
                );
            }
        }else {
            $output = array(
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Maaf Data Terkait Tidak Ada'
            );
        }
        return response()->json($output);

    }
    
    
    public function exportPDF(){
        $konek = self::konek();
        $kode_produk = $_GET['kode_produk'];
        $show = $_GET['show'];
        $tanggal_awal = $_GET['tanggal_awal'];
        $tanggal_akhir = $_GET['tanggal_akhir'];
        $format = $_GET['format_cetak'];

        $get_nama = Produk::on($konek)->find($kode_produk);
        $nama = $get_nama->nama_produk;

        $lokasi2 = auth()->user()->kode_lokasi;
        $get_company = auth()->user()->kode_company;

        $nama_company = Company::find($get_company);
        $nama2 = $nama_company->nama_company;

        if($lokasi2 == 'HO'){
            $lokasi = $_GET['lokasi'];
            if($lokasi != 'SEMUA'){
                $nama_lokasi = MasterLokasi::find($lokasi);
                $nama1 = $nama_lokasi->nama_lokasi;

                $loc = $_GET['lokasi'];
                if($format == 'PDF'){

                    if($show == 'Monthly'){
                        $field = $_GET['item2'];

                        $pemakaian = 'Pemakaian';
                        $penerimaan = 'Penerimaan';
                        $penjualan = 'Penjualan';
                        $adjustment = 'Adjustment';
                        $opname = 'Opname';
                        $transferin = 'Transfer_In';
                        $transferout = 'Transfer_Out';
                        $returbeli = 'Retur_Beli';
                        $returjual = 'Retur_Jual';
                        $disassembling = 'Disassembling';
                        $assembling = 'Assembling';
                        $semua = 'SEMUA';

                        $leng = count($field);

                        $i = 0;
                        for($i = 0; $i < $leng; $i++){
                            if($pemakaian == $_GET['item2'][$i]){
                                $pemakaian = 'true';
                            }else if($penerimaan == $_GET['item2'][$i]){
                                $penerimaan = 'true';
                            }else if($penjualan == $_GET['item2'][$i]){
                                $penjualan = 'true';
                            }else if($adjustment == $_GET['item2'][$i]){
                                $adjustment = 'true';
                            }else if($opname == $_GET['item2'][$i]){
                                $opname = 'true';
                            }else if($opname == $_GET['item2'][$i]){
                                $opname = 'true';
                            }else if($transferin == $_GET['item2'][$i]){
                                $transferin = 'true';
                            }else if($transferout == $_GET['item2'][$i]){
                                $transferout = 'true';
                            }else if($returbeli == $_GET['item2'][$i]){
                                $returbeli = 'true';
                            }else if($returjual == $_GET['item2'][$i]){
                                $returjual = 'true';
                            }else if($disassembling == $_GET['item2'][$i]){
                                $disassembling = 'true';
                            }else if($assembling == $_GET['item2'][$i]){
                                $assembling = 'true';
                            }else if($semua == $_GET['item2'][$i]){
                                $semua = 'true';
                            }
                        }

                        $tahun_awal = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal_awal)->year;
                        $bulan_awal = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal_awal)->month;
                        $hari_awal = '01';

                        $tanggal_awal = Carbon\Carbon::createFromDate($tahun_awal, $bulan_awal, $hari_awal)->toDateString();
                        $awal = Carbon\Carbon::parse($tanggal_awal)->format('F Y');

                        $tahun_akhir = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal_akhir)->year;
                        $bulan_akhir = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal_akhir)->month;
                        $hari_akhir = '01';

                        $tanggal_akhir = Carbon\Carbon::createFromDate($tahun_akhir, $bulan_akhir, $hari_akhir)->toDateString();
                        $akhir = Carbon\Carbon::parse($tanggal_akhir)->format('F Y');

                        $monthly = tb_item_bulanan::on($konek)->where('kode_produk',$kode_produk)->where('kode_lokasi',$loc)->whereBetween('periode', array($tanggal_awal, $tanggal_akhir))->orderBy('periode','asc')->get();

                        $pdf = PDF::loadView('/admin/produk/monthly', compact('monthly','kode_produk', 'awal', 'akhir', 'nama','nama1','nama2','pemakaian','penerimaan','penjualan','adjustment','opname','transferin','transferout','returbeli','returjual','disassembling','assembling','semua','lokasi'));
                        $pdf->setPaper('legal', 'landscape');

                        return $pdf->stream('Laporan Bulanan '.$nama.'.pdf');
                    }
                    else{
                        $data = tb_produk_history::on($konek)->where('kode_produk',$kode_produk)->where('kode_lokasi',$loc)->whereBetween('tanggal_transaksi', array($tanggal_awal, $tanggal_akhir))->orderBy('created_at','asc')->get();

                        $pdf = PDF::loadView('/admin/produk/transaksi', compact('data','kode_produk', 'tanggal_awal', 'tanggal_akhir', 'nama','nama1','nama2','lokasi'));
                        $pdf->setPaper([0, 0, 684, 792], 'potrait');

                        return $pdf->stream('Laporan Bulanan '.$nama.'.pdf'); 
                    }
                }
                else{

                    if($show == 'Monthly'){
                        $tahun_awal = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal_awal)->year;
                        $bulan_awal = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal_awal)->month;
                        $hari_awal = '01';

                        $tanggal_awal = Carbon\Carbon::createFromDate($tahun_awal, $bulan_awal, $hari_awal)->toDateString();
                        $awal = Carbon\Carbon::parse($tanggal_awal)->format('F Y');

                        $tahun_akhir = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal_akhir)->year;
                        $bulan_akhir = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal_akhir)->month;
                        $hari_akhir = '01';

                        $tanggal_akhir = Carbon\Carbon::createFromDate($tahun_akhir, $bulan_akhir, $hari_akhir)->toDateString();
                        $akhir = Carbon\Carbon::parse($tanggal_akhir)->format('F Y');

                        return Excel::download(new MonthlyExport($kode_produk, $lokasi, $tanggal_awal, $tanggal_akhir, $show), 'Laporan Bulanan '.$nama.'.xlsx');
                    }
                    else{
                        return Excel::download(new MonthlyExport($kode_produk, $lokasi, $tanggal_awal, $tanggal_akhir, $show), 'Laporan Bulanan '.$nama.'.xlsx');
                    }

                }
            }else{
                $nama_lokasi = MasterLokasi::find($lokasi2);
                $nama1 = $nama_lokasi->nama_lokasi;
                if($format == 'PDF'){

                    if($show == 'Monthly'){
                        $field = $_GET['item2'];

                        $pemakaian = 'Pemakaian';
                        $penerimaan = 'Penerimaan';
                        $penjualan = 'Penjualan';
                        $adjustment = 'Adjustment';
                        $opname = 'Opname';
                        $transferin = 'Transfer_In';
                        $transferout = 'Transfer_Out';
                        $returbeli = 'Retur_Beli';
                        $returjual = 'Retur_Jual';
                        $disassembling = 'Disassembling';
                        $assembling = 'Assembling';
                        $semua = 'SEMUA';

                        $leng = count($field);

                        $i = 0;
                        for($i = 0; $i < $leng; $i++){
                            if($pemakaian == $_GET['item2'][$i]){
                                $pemakaian = 'true';
                            }else if($penerimaan == $_GET['item2'][$i]){
                                $penerimaan = 'true';
                            }else if($penjualan == $_GET['item2'][$i]){
                                $penjualan = 'true';
                            }else if($adjustment == $_GET['item2'][$i]){
                                $adjustment = 'true';
                            }else if($opname == $_GET['item2'][$i]){
                                $opname = 'true';
                            }else if($opname == $_GET['item2'][$i]){
                                $opname = 'true';
                            }else if($transferin == $_GET['item2'][$i]){
                                $transferin = 'true';
                            }else if($transferout == $_GET['item2'][$i]){
                                $transferout = 'true';
                            }else if($returbeli == $_GET['item2'][$i]){
                                $returbeli = 'true';
                            }else if($returjual == $_GET['item2'][$i]){
                                $returjual = 'true';
                            }else if($disassembling == $_GET['item2'][$i]){
                                $disassembling = 'true';
                            }else if($assembling == $_GET['item2'][$i]){
                                $assembling = 'true';
                            }else if($semua == $_GET['item2'][$i]){
                                $semua = 'true';
                            }
                        }

                        $tahun_awal = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal_awal)->year;
                        $bulan_awal = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal_awal)->month;
                        $hari_awal = '01';

                        $tanggal_awal = Carbon\Carbon::createFromDate($tahun_awal, $bulan_awal, $hari_awal)->toDateString();
                        $awal = Carbon\Carbon::parse($tanggal_awal)->format('F Y');

                        $tahun_akhir = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal_akhir)->year;
                        $bulan_akhir = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal_akhir)->month;
                        $hari_akhir = '01';

                        $tanggal_akhir = Carbon\Carbon::createFromDate($tahun_akhir, $bulan_akhir, $hari_akhir)->toDateString();
                        $akhir = Carbon\Carbon::parse($tanggal_akhir)->format('F Y');

                        $monthly = tb_item_bulanan::on($konek)->where('kode_produk',$kode_produk)->whereBetween('periode', array($tanggal_awal, $tanggal_akhir))->orderBy('periode','asc')->get();

                        $pdf = PDF::loadView('/admin/produk/monthly', compact('monthly','kode_produk', 'awal', 'akhir', 'nama','nama1','nama2','pemakaian','penerimaan','penjualan','adjustment','opname','transferin','transferout','returbeli','returjual','disassembling','assembling','semua','lokasi'));
                        $pdf->setPaper('legal', 'landscape');

                        return $pdf->stream('Laporan Bulanan '.$nama.'.pdf');
                    }
                    else{
                        $data = tb_produk_history::on($konek)->where('kode_produk',$kode_produk)->whereBetween('tanggal_transaksi', array($tanggal_awal, $tanggal_akhir))->orderBy('created_at','asc')->get();

                        $pdf = PDF::loadView('/admin/produk/transaksi', compact('data','kode_produk', 'tanggal_awal', 'tanggal_akhir', 'nama','nama1','nama2','lokasi'));
                        $pdf->setPaper([0, 0, 684, 792], 'potrait');

                        return $pdf->stream('Laporan Bulanan '.$nama.'.pdf'); 
                    }
                }
                else{

                    if($show == 'Monthly'){
                        $tahun_awal = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal_awal)->year;
                        $bulan_awal = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal_awal)->month;
                        $hari_awal = '01';

                        $tanggal_awal = Carbon\Carbon::createFromDate($tahun_awal, $bulan_awal, $hari_awal)->toDateString();
                        $awal = Carbon\Carbon::parse($tanggal_awal)->format('F Y');

                        $tahun_akhir = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal_akhir)->year;
                        $bulan_akhir = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal_akhir)->month;
                        $hari_akhir = '01';

                        $tanggal_akhir = Carbon\Carbon::createFromDate($tahun_akhir, $bulan_akhir, $hari_akhir)->toDateString();
                        $akhir = Carbon\Carbon::parse($tanggal_akhir)->format('F Y');

                        return Excel::download(new MonthlyExport($kode_produk, $lokasi, $tanggal_awal, $tanggal_akhir, $show), 'Laporan Bulanan '.$nama.'.xlsx');
                    }
                    else{
                        return Excel::download(new MonthlyExport($kode_produk, $lokasi, $tanggal_awal, $tanggal_akhir, $show), 'Laporan Bulanan '.$nama.'.xlsx');
                    }

                }
            }
        }else{
            if($format == 'PDF'){
                $nama_lokasi = MasterLokasi::find($lokasi2);
                $nama1 = $nama_lokasi->nama_lokasi;
                if($show == 'Monthly'){
                    $field = $_GET['item2'];

                    $pemakaian = 'Pemakaian';
                    $penerimaan = 'Penerimaan';
                    $penjualan = 'Penjualan';
                    $adjustment = 'Adjustment';
                    $opname = 'Opname';
                    $transferin = 'Transfer_In';
                    $transferout = 'Transfer_Out';
                    $returbeli = 'Retur_Beli';
                    $returjual = 'Retur_Jual';
                    $disassembling = 'Disassembling';
                    $assembling = 'Assembling';
                    $semua = 'SEMUA';

                    $leng = count($field);

                    $i = 0;
                    for($i = 0; $i < $leng; $i++){
                        if($pemakaian == $_GET['item2'][$i]){
                            $pemakaian = 'true';
                        }else if($penerimaan == $_GET['item2'][$i]){
                            $penerimaan = 'true';
                        }else if($penjualan == $_GET['item2'][$i]){
                            $penjualan = 'true';
                        }else if($adjustment == $_GET['item2'][$i]){
                            $adjustment = 'true';
                        }else if($opname == $_GET['item2'][$i]){
                            $opname = 'true';
                        }else if($opname == $_GET['item2'][$i]){
                            $opname = 'true';
                        }else if($transferin == $_GET['item2'][$i]){
                            $transferin = 'true';
                        }else if($transferout == $_GET['item2'][$i]){
                            $transferout = 'true';
                        }else if($returbeli == $_GET['item2'][$i]){
                            $returbeli = 'true';
                        }else if($returjual == $_GET['item2'][$i]){
                            $returjual = 'true';
                        }else if($disassembling == $_GET['item2'][$i]){
                            $disassembling = 'true';
                        }else if($assembling == $_GET['item2'][$i]){
                            $assembling = 'true';
                        }else if($semua == $_GET['item2'][$i]){
                            $semua = 'true';
                        }
                    }

                    $tahun_awal = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal_awal)->year;
                    $bulan_awal = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal_awal)->month;
                    $hari_awal = '01';

                    $tanggal_awal = Carbon\Carbon::createFromDate($tahun_awal, $bulan_awal, $hari_awal)->toDateString();
                    $awal = Carbon\Carbon::parse($tanggal_awal)->format('F Y');

                    $tahun_akhir = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal_akhir)->year;
                    $bulan_akhir = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal_akhir)->month;
                    $hari_akhir = '01';

                    $tanggal_akhir = Carbon\Carbon::createFromDate($tahun_akhir, $bulan_akhir, $hari_akhir)->toDateString();
                    $akhir = Carbon\Carbon::parse($tanggal_akhir)->format('F Y');

                    $monthly = tb_item_bulanan::on($konek)->where('kode_produk',$kode_produk)->where('kode_lokasi',$lokasi2)->whereBetween('periode', array($tanggal_awal, $tanggal_akhir))->orderBy('periode','asc')->get();

                    $lokasi = $lokasi2;
                    $pdf = PDF::loadView('/admin/produk/monthly', compact('monthly','kode_produk', 'awal', 'akhir', 'nama','nama1','nama2','pemakaian','penerimaan','penjualan','adjustment','opname','transferin','transferout','returbeli','returjual','disassembling','assembling','semua','lokasi'));
                    $pdf->setPaper('legal', 'landscape');

                    return $pdf->stream('Laporan Bulanan '.$nama.'.pdf');
                }
                else{
                    $data = tb_produk_history::on($konek)->where('kode_produk',$kode_produk)->where('kode_lokasi',$lokasi2)->whereBetween('tanggal_transaksi', array($tanggal_awal, $tanggal_akhir))->orderBy('created_at','asc')->get();

                    $lokasi = $lokasi2;
                    $pdf = PDF::loadView('/admin/produk/transaksi', compact('data','kode_produk', 'tanggal_awal', 'tanggal_akhir', 'nama','nama1','nama2','lokasi'));
                    $pdf->setPaper([0, 0, 684, 792], 'potrait');

                    return $pdf->stream('Laporan Bulanan '.$nama.'.pdf'); 
                }
            }
            else{
                if($show == 'Monthly'){
                    $tahun_awal = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal_awal)->year;
                    $bulan_awal = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal_awal)->month;
                    $hari_awal = '01';

                    $tanggal_awal = Carbon\Carbon::createFromDate($tahun_awal, $bulan_awal, $hari_awal)->toDateString();
                    $awal = Carbon\Carbon::parse($tanggal_awal)->format('F Y');

                    $tahun_akhir = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal_akhir)->year;
                    $bulan_akhir = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal_akhir)->month;
                    $hari_akhir = '01';

                    $tanggal_akhir = Carbon\Carbon::createFromDate($tahun_akhir, $bulan_akhir, $hari_akhir)->toDateString();
                    $akhir = Carbon\Carbon::parse($tanggal_akhir)->format('F Y');

                    return Excel::download(new MonthlyExport($kode_produk, $lokasi2, $tanggal_awal, $tanggal_akhir, $show), 'Laporan Bulanan '.$nama.'.xlsx');
                }
                else{
                    return Excel::download(new MonthlyExport($kode_produk, $lokasi2, $tanggal_awal, $tanggal_akhir, $show), 'Laporan Bulanan '.$nama.'.xlsx');
                }

            }
        }
    }

    
    public function show_produk()
    {
        $konek = self::konek();
        $kode_produk = request()->id;
        $data = Produk::on($konek)->with('kategoriproduk','merek','satuan','company','ukuran')->find($kode_produk);

        $lokasi = auth()->user()->kode_lokasi;
        $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode', 'Open')->orwhere('reopen_status', 'true')->first();
        $detail = tb_item_bulanan::on($konek)->where('kode_produk',$kode_produk)->where('kode_lokasi',$lokasi)->where('periode',$cek_bulan->periode)->first();

        $cek_tipe = $data->tipe_produk;
        $cek_kategori = $data->kode_kategori;
        $ending_stock = $detail->ending_stock;

        if($cek_tipe == 'Serial' && $detail != null){
            if($cek_kategori == 'UNIT' || $cek_kategori == 'BAN'){
                $total = tb_item_bulanan::on($konek)->where('kode_produk',$kode_produk)->where('kode_lokasi',$lokasi)->where('periode',$cek_bulan->periode)->where('ending_stock',1)->get();
            
                $ending_stock = count($total);
            }
        }
        
        
        if($data->kode_merek == null || $data->kode_merek == '' || $data->kode_merek == "'-")
        {
            $merek = "Not Set";
        }else {
            $merek = $data->merek->nama_merek;
        }
        
        if($data->kode_ukuran == null || $data->kode_ukuran == '' || $data->kode_ukuran == "'-")
        {
            $ukuran = "Not Set";
        }else {
            $ukuran = $data->ukuran->nama_ukuran;
        }
        
        if($data->kode_satuan == null || $data->kode_satuan == '' || $data->kode_satuan == "'-")
        {
            $satuan = "Not Set";
        }else {
            $satuan = $data->satuan->nama_satuan;
        }
        
        $output = array(
            'kode_produk'=> $data->id,
            'nama_produk'=> $data->nama_produk,
            'tipe_produk'=> $data->tipe_produk,
            'kode_kategori'=> $data->kategoriproduk->nama_kategori,
            'kode_merek'=> $merek,
            'kode_ukuran'=> $ukuran,
            'kode_satuan'=> $satuan,
            'kode_company'=> $data->company->nama_company,
            'partnumber'=> $data->partnumber,
            'harga_beli'=> $data->harga_beli,
            'harga_jual'=> $data->harga_jual,
            'hpp'=> $detail->hpp,
            'stok'=> $ending_stock,
            'stat'=> $data->stat,
        );
        return response()->json($output);
    }

    public function edit_produk()
    {
        $konek = self::konek();
        $kode_produk = request()->id;
        $data = Produk::on($konek)->find($kode_produk);
        $output = array(
            'kode_produk'=> $data->id,
            'nama_produk'=> $data->nama_produk,
            'tipe_produk'=> $data->tipe_produk,
            'kode_kategori'=> $data->kode_kategori,
            'kode_unit'=> $data->kode_unit,
            'kode_merek'=> $data->kode_merek,
            'kode_ukuran'=> $data->kode_ukuran,
            'kode_satuan'=> $data->kode_satuan,
            'partnumber'=> $data->partnumber,
            'harga_beli'=> $data->harga_beli,
            'harga_jual'=> $data->harga_jual,
            'stat'=> $data->stat,
            'min_qty'=> $data->min_qty,
            'max_qty'=> $data->max_qty,
        );
        return response()->json($output);
    }


    public function updateAjax(Request $request)
    {
        $konek = self::konek();
        $kode_produk = $request->kode_produk;
        $no_beli = PembelianDetail::on($konek)->where('kode_produk',$kode_produk)->first();
        $no_terima = PenerimaanDetail::on($konek)->where('kode_produk',$kode_produk)->first();
        $no_pakai = PemakaianDetail::on($konek)->where('kode_produk',$kode_produk)->first();
        $no_pakaiban = PemakaianbanDetail::on($konek)->where('kode_produk',$kode_produk)->first();
        $no_op = OpnameDetail::on($konek)->where('kode_produk',$kode_produk)->first();
        $no_adj = AdjustmentDetail::on($konek)->where('kode_produk',$kode_produk)->first();
        $no_jual = PenjualanDetail::on($konek)->where('kode_produk',$kode_produk)->first();
        $no_trfin = TransferInDetail::on($konek)->where('kode_produk',$kode_produk)->first();
        $no_trfout = TransferDetail::on($konek)->where('kode_produk',$kode_produk)->first();

        $no_konversi = Konversi::on($konek)->where('kode_produk',$kode_produk)->get();
        $leng_konversi = count($no_konversi);

        $lokasirak = LokasiRak::on($konek)->where('kode_produk',$kode_produk)->first();
        dd($no_beli,$no_terima,$no_pakai,$no_pakaiban,$no_op,$no_adj,$no_jual,$no_trfin,$no_trfout);
        if ($no_beli != null || $no_terima != null || $no_pakai != null || $no_pakaiban != null || $no_op != null || $no_adj != null || $no_jual != null || $no_trfin != null || $no_trfout != null) {
            $message = [
                'success' => false,
                'title' => 'Update',
                'message' => 'Produk ['.$request->nama_produk.'] sudah ada dalam transaksi.'
            ];
            return response()->json($message);
        }

        $level = auth()->user()->level;
        if($level != 'superadministrator' && $level!= 'user_rince' && $level!= 'user_thomas'){
            if ($no_beli == null && $no_pakai == null && $no_op == null && $no_adj == null && $lokasirak == null && $no_terima == null && $no_pakaiban == null && $no_jual == null && $no_trfin == null && $no_trfout == null && $leng_konversi <= 1){

                $produk = $request->id;
                $nama_produk = $request->nama_produk;
                $cek_kode= substr($produk,0,1);
                $cek_nama = substr($nama_produk,0,1);

                Produk::on($konek)->find($request->kode_produk)->update($request->all());

                $get_satuan = $request->kode_satuan;
                $get_produk = $request->kode_produk;

                $cek_satuan = Konversi::on($konek)->where('kode_produk',$get_produk)->get();
                $get_nama = Satuan::find($get_satuan);
                $nama_satuan = $get_nama->nama_satuan;

                $tabel_baru = [
                    'kode_satuan'=>$get_satuan,
                    'satuan_terbesar'=>$nama_satuan,
                    'kode_satuanterkecil'=>$get_satuan,
                    'satuan_terkecil'=>$nama_satuan,
                ];

                $update_konversi = Konversi::on($konek)->where('kode_produk',$get_produk)->update($tabel_baru);

                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data telah di Update.'
                ];
                return response()->json($message);      
            }
            else{
                $message = [
                    'success' => false,
                    'title' => 'Update',
                    'message' => 'Produk ['.$request->nama_produk.'] dipakai dalam transaksi.'
                ];
                return response()->json($message);
            }
        }else{
            //CEK JIKA ADA KONVERSI SATUAN TERBESAR, MAKA HARUS DI HAPUS TERLEBIH DAHULU
            if ($leng_konversi <= 1){
                $get_produk = Produk::on($konek)->find($request->kode_produk);
                $get_satuan1 = $get_produk->kode_satuan;
                $get_satuan2 = $request->kode_satuan;

                $produk = $request->kode_produk;
                $nama_produk = $request->nama_produk;
                $cek_kode= substr($produk,0,1);
                $cek_nama = substr($nama_produk,0,1);

                Produk::on($konek)->find($request->kode_produk)->update($request->all());

                $get_satuan = $request->kode_satuan;
                $get_produk = $request->kode_produk;

                $cek_satuan = Konversi::on($konek)->where('kode_produk',$get_produk)->get();
                $get_nama = Satuan::find($get_satuan);
                $nama_satuan = $get_nama->nama_satuan;

                $tabel_baru = [
                    'kode_satuan'=>$get_satuan,
                    'satuan_terbesar'=>$nama_satuan,
                    'kode_satuanterkecil'=>$get_satuan,
                    'satuan_terkecil'=>$nama_satuan,
                ];

                $update_konversi = Konversi::on($konek)->where('kode_produk',$get_produk)->update($tabel_baru);

                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data telah di Update.'
                ];
            }else{
                $message = [
                    'success' => false,
                    'title' => 'Update',
                    'message' => 'Produk ['.$request->nama_produk.'] memiliki satuan terbesar. Silahkan hapus satuan terbesar dahulu.'
                ];
                
            }
            
            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Edit Produk  : '.$nama_produk.'.','created_by'=>$nama,'updated_by'=>$nama];
            user_history::on($konek)->create($tmp);
            
            return response()->json($message);
        }   
    }

    public function hapus_produk()
    {   
        $konek = self::konek();
        $kode_produk = request()->id;
        $produk = Produk::on($konek)->find(request()->id);

        $no_beli = PembelianDetail::on($konek)->where('kode_produk',$kode_produk)->first();
        $no_terima = PenerimaanDetail::on($konek)->where('kode_produk',$kode_produk)->first();
        $no_pakai = PemakaianDetail::on($konek)->where('kode_produk',$kode_produk)->first();
        $no_pakaiban = PemakaianbanDetail::on($konek)->where('kode_produk',$kode_produk)->first();
        $no_op = OpnameDetail::on($konek)->where('kode_produk',$kode_produk)->first();
        $no_adj = AdjustmentDetail::on($konek)->where('kode_produk',$kode_produk)->first();
        $no_jual = PenjualanDetail::on($konek)->where('kode_produk',$kode_produk)->first();
        $no_trfin = TransferInDetail::on($konek)->where('kode_produk',$kode_produk)->first();
        $no_trfout = TransferDetail::on($konek)->where('kode_produk',$kode_produk)->first();

        $lokasirak = LokasiRak::on($konek)->where('kode_produk',$kode_produk)->first();

        if ($no_beli == null && $no_pakai == null && $no_op == null && $no_adj == null && $lokasirak == null && $no_terima == null && $no_pakaiban == null && $no_jual == null && $no_trfin == null && $no_trfout == null){
            $bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$kode_produk)->first();
            if($bulanan != null){
                $bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$kode_produk)->delete();
            }
            
            $prefix = strtoupper($produk->kode_produk[0]);
            $produk_index = ProdukCounter::on($konek)->where('index', $prefix)->first();
            $jumlah_final = $produk_index->jumlah - 1;
            $tabel_baru2 = [
                'jumlah'=>$jumlah_final,
            ];
            $update = ProdukCounter::on($konek)->where('index', $prefix)->update($tabel_baru2);
            
            $konversi = Konversi::on($konek)->where('kode_produk',$kode_produk)->delete();
            $produk->delete();

            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data ['.$produk->nama_produk.'] telah dihapus.'
            ];
            
            } else {
                $message = [
                    'success' => false,
                    'title' => 'Update',
                    'message' => 'Data ['.$produk->nama_produk.'] dipakai dalam transaksi.'
                ];
            }
        
            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Hapus Produk  : '.$produk->nama_produk.'.','created_by'=>$nama,'updated_by'=>$nama];
            user_history::on($konek)->create($tmp);
            
            return response()->json($message);
        
    }
}
