<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Assembling;
use App\Models\AssemblingDetail;
use App\Models\Company;
use App\Models\Produk;
use App\Models\tb_akhir_bulan;
use App\Models\tb_item_bulanan;
use App\Models\tb_produk_history;
use App\Models\user_history;
use App\Models\MasterLokasi;
use App\Models\KategoriProduk;
use App\Models\Ledger;
use App\Models\Coa;
use App\Models\AccBalance;
use App\Models\Tb_acc_history;
use App\Models\Jurnal;
use App\Models\Opname;
use App\Models\Costcenter;
use PDF;
use Excel;
use DB;
use Alert;
use Carbon;
use DateTime;

class AssemblingController extends Controller
{
    public function index()
    {
        $konek = self::konek();
        $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode', 'Open')->orwhere('reopen_status', 'true')->first();
        
        $Produk = Produk::on($konek)->pluck('nama_produk','id');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');

        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;
        
        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        $level = auth()->user()->level;
        
        return view('admin.assembling.index',compact('period', 'nama_lokasi','nama_company','Produk'));
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
        }else if ($compa == '06'){
            $koneksi = 'mysqlinfra';
        }
        return $koneksi;
    }

    public static function konek2()
    {
        $compa = auth()->user()->kode_company;
        if ($compa == '01'){
            $koneksi = 'mysql_finance_depo';
        }else if ($compa == '02'){
            $koneksi = 'mysql_finance_pbm';
        }else if ($compa == '03'){
            $koneksi = 'mysql_finance_emkl';
        }else if ($compa == '04'){
            $koneksi = 'mysql_finance_gut';
        }else if ($compa == '0401'){
            $koneksi = 'mysql_finance_gutjkt';
        }else if ($compa == '05'){
            $koneksi = 'mysql_finance_sub';
        }
        return $koneksi;
    }

    public function anyData()
    {
        $konek = self::konek();
        $lokasi = auth()->user()->kode_lokasi;
        if($lokasi == 'HO'){
            return Datatables::of(Assembling::on($konek)->with('produk')->orderBy('created_at','desc'))->make(true);
        }else{
            return Datatables::of(Assembling::on($konek)->with('produk')->orderBy('created_at','desc')->where('kode_lokasi', auth()->user()->kode_lokasi))->make(true);
        }
    }

    public function exportPDF(Assembling $assembling){
        $request = $_GET['id'];
        $konek = self::konek();
        $assembling = Assembling::on($konek)->where('no_ass',$request)->first();
        $user = $assembling->created_by;
        $id = $assembling->no_ass;

        $kode_company = $assembling->kode_company;
        $assemblingdetail = AssemblingDetail::on($konek)->where('no_ass',$request)->get();

        $company = Company::where('kode_company',$kode_company)->first();
        $nama_company = $company->nama_company;

        $dt = Carbon\Carbon::now();
        $date_now = Carbon\Carbon::parse($dt)->format('d/m/Y');

        $tgl = $assembling->tanggal;
        $date=date_create($tgl);

        $pdf = PDF::loadView('/admin/assembling/pdf', compact('assemblingdetail','request','tgl', 'id','nama_company','date_now','assembling','user'));
        $pdf->setPaper([0, 0, 684, 792], 'potrait');

        return $pdf->stream('Laporan Assembling '.$id.'.pdf');        
    }

    public function exportPDF3(){
        $konek = self::konek();
        $konek2 = self::konek2();
        $request = $_GET['id'];
        $no_journal = $_GET['no_journal'];

        $assembling = Assembling::on($konek)->find($request);
        $grand_total1 = $assembling->qty_assembling * $assembling->hpp;
        $jur = $assembling->kode_jurnal;
        $jurnal = Jurnal::find($jur);

        $total_qty = 0;
        $total_harga = 0;
        $grand_total = 0;
        $detail = AssemblingDetail::on($konek)->where('no_ass',$request)->get();
        foreach ($detail as $row){
            $total_qty += $row->qty;
            $subtotal = $row->hpp * $row->qty;
            $total_harga += $subtotal;
            $grand_total = $total_harga;
        }

        $ledger2 = Ledger::on($konek2)->with('coa')->where('no_journal',$no_journal)->first();

        $ledger = Ledger::on($konek2)->select('ledger.*','coa.ac_description')->join('u5611458_gui_general_ledger_laravel.coa','u5611458_gui_general_ledger_laravel.coa.account','=','ledger.account')->where('no_journal', $no_journal)->get();

        $user = $assembling->created_by;
        $tgl = $assembling->tanggal;
        $date=date_create($tgl);

        $ttd = $user;

        $get_lokasi = auth()->user()->kode_lokasi;
        $get_company = auth()->user()->kode_company;

        $nama_lokasi = MasterLokasi::find($get_lokasi);
        $nama = $nama_lokasi->nama_lokasi;

        $company = Company::find($get_company);
        $nama2 = $company->nama_company;

        $dt = Carbon\Carbon::now();
        $date_now = Carbon\Carbon::parse($dt)->format('d/m/Y');
        $journal_date = Carbon\Carbon::parse($ledger2->journal_date)->format('d/m/Y');

        $pdf = PDF::loadView('/admin/assembling/pdf2', compact('assembling','request', 'jurnal','tgl','date', 'ttd','nama_company','date_now','ledger','ledger2','dt','user','nama','nama2','journal_date','grand_total','grand_total1'));
        $pdf->setPaper([0, 0, 684, 792], 'potrait');
        return $pdf->stream('Cetak Zoom Jurnal '.$request.'.pdf');
    }

    public function getharga()
    {
        $konek = self::konek();
        $lokasi = auth()->user()->kode_lokasi;
        $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode', 'Open')->orwhere('reopen_status', 'true')->first();
        $monthly = tb_item_bulanan::on($konek)->where('kode_produk',request()->id)->where('partnumber',request()->part)->where('kode_lokasi',$lokasi)->where('periode',$cek_bulan->periode)->first();
        if($monthly != null && $monthly->ending_stock > 0){
            $hpp = number_format($monthly->ending_amount/$monthly->ending_stock,2, '.', '');
            $output = array(
                'stok'=>$monthly->ending_stock,
                'hpp'=>$hpp,
            );
        }
        else{
            $output = array(
                'stok'=>0,
                'hpp'=>0,
            );
        }
        return response()->json($output);
    }

    public function selectpart(Request $request)
    {
        $konek = self::konek();
        $produk = Produk::on($konek)->find(request()->kode_produk);
        if($produk != null){
            $cek_tipe = $produk->tipe_produk;
            $cek_kategori = $produk->kode_kategori;

            if($cek_tipe == 'Serial' && $cek_kategori == 'UNIT'){
                $cek_period = tb_akhir_bulan::on($konek)->where('status_periode','Open')->orwhere('status_periode','Disable')->first();
                $tgl_period = $cek_period->periode;

                $states2 = tb_item_bulanan::on($konek)->where('kode_produk',$request->kode_produk)->where('ending_stock', 1)->where('periode',$tgl_period)->pluck("partnumber","partnumber")->all();
                
                return response()->json(['options'=>$states2]);
            }else{
                $cek_period = tb_akhir_bulan::on($konek)->where('status_periode','Open')->orwhere('status_periode','Disable')->first();
                $tgl_period = $cek_period->periode;

                $states2 = tb_item_bulanan::on($konek)->where('kode_produk',$request->kode_produk)->pluck("partnumber","partnumber")->all();
                
                return response()->json(['options'=>$states2]);
            }
        }  
    }

    public function accbalance_kredit_post($coa, $harga, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans)
    {
        $konek = self::konek2();

        $cek_balance = AccBalance::on($konek)->where('account',$coa->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
        
        $begin = $cek_balance->beginning_balance;
        $debit = $cek_balance->debet;
        $kredit_awal = $cek_balance->kredit;

        $kredit_akhir = $kredit_awal + $harga;
        if($coa->normal_balance == 'D'){
            $ending_balance = $begin + $debit - $kredit_akhir;
        }else{
            $ending_balance = $begin - $debit + $kredit_akhir;
        }

        $update_acc = [
            'kredit'=>$kredit_akhir,
            'ending_balance'=>$ending_balance,
        ];

        $update_balance = AccBalance::on($konek)->where('account',$coa->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($update_acc);
    }

    public function accbalance_debit_post($coa, $harga, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans)
    {
        $konek = self::konek2();
        $cek_balance = AccBalance::on($konek)->where('account',$coa->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

        $begin = $cek_balance->beginning_balance;
        $debit_awal = $cek_balance->debet;
        $kredit = $cek_balance->kredit;

        $debit_akhir = $debit_awal + $harga;
        if($coa->normal_balance == 'D'){
            $ending_balance = $begin + $debit_akhir - $kredit;
        }else{
            $ending_balance = $begin - $debit_akhir + $kredit;
        }

        $update_acc = [
            'debet'=>$debit_akhir,
            'ending_balance'=>$ending_balance,
        ];

        $update_balance = AccBalance::on($konek)->where('account',$coa->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($update_acc);
    }

    public function accbalance_kredit_unpost($coa, $harga, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans)
    {
        $konek = self::konek2();
        $cek_balance = AccBalance::on($konek)->where('account',$coa)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

        $begin = $cek_balance->beginning_balance;
        $debit = $cek_balance->debet;
        $kredit_awal = $cek_balance->kredit;

        $get_coa = Coa::on('mysql4')->where('account',$coa)->first();
        $kredit_akhir = $kredit_awal - $harga;
        if($get_coa->normal_balance == 'D'){
            $ending_balance = $begin + $debit - $kredit_akhir;
        }else{
            $ending_balance = $begin - $debit + $kredit_akhir;
        }

        $update_acc = [
            'kredit'=>$kredit_akhir,
            'ending_balance'=>$ending_balance,
        ];

        $update_balance = AccBalance::on($konek)->where('account',$coa)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($update_acc);
    }

    public function accbalance_debit_unpost($coa, $harga, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans)
    {
        $konek = self::konek2();
        $cek_balance = AccBalance::on($konek)->where('account',$coa)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

        $begin = $cek_balance->beginning_balance;
        $debit_awal = $cek_balance->debet;
        $kredit = $cek_balance->kredit;

        $get_coa = Coa::on('mysql4')->where('account',$coa)->first();
        $debit_akhir = $debit_awal - $harga;
        if($get_coa->normal_balance == 'D'){
            $ending_balance = $begin + $debit_akhir - $kredit;
        }else{
            $ending_balance = $begin - $debit_akhir + $kredit;
        }

        $update_acc = [
            'debet'=>$debit_akhir,
            'ending_balance'=>$ending_balance,
        ];

        $update_balance = AccBalance::on($konek)->where('account',$coa)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($update_acc);
    }

    public function Showdetail()
    {
        $konek = self::konek();
        $total_harga = 0;
        $grand_total = 0;
        $assemblingdetail= AssemblingDetail::on($konek)->with('produk')->where('no_ass',request()->id)
        ->orderBy('created_at', 'desc')->get();

        $output = array();

        foreach ($assemblingdetail as $row){
            $subtotal = $row->hpp * $row->qty;
            $total_harga += $subtotal;
            $grand_total = number_format($total_harga,2,",",".");
        }

        if($assemblingdetail){
            foreach($assemblingdetail as $row)
            {
                $output[] = array(
                    'id'=>$row->id_detail,
                    'produk'=>$row->produk->nama_produk,
                    'partnumber'=>$row->partnumber,
                    'qty'=>$row->qty,
                    'hpp'=>$row->hpp,
                    'subtotal'=>number_format($row->hpp * $row->qty,2,",","."),
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

    public function detail($assembling)
    {   
        $konek = static::konek();
        $assembling = Assembling::on($konek)->with('produk')->find($assembling);
        $tanggal = $assembling->tanggal;
        $id = $assembling->no_ass;

        $period = tb_akhir_bulan::on($konek)->where('status_periode','Open')->orwhere('reopen_status','true')->first();
        $tgl2 = $period->periode;
        $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl2)->year;
        $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl2)->month;

        $cekbulanan = tb_item_bulanan::on($konek)->whereMonth('periode',$bulan)->whereYear('periode',$tahun)->pluck('kode_produk','kode_produk');
        $cekproduk = Produk::on($konek)->whereNotIn('id',$cekbulanan)->get();

        $validate = $this->periodeChecker($tanggal);
             
        if($validate == true){
            $data = Assembling::on($konek)->find($id);

            $total_qty = 0;
            $total_harga = 0;
            $grand_total = 0;

            $assemblingdetail = AssemblingDetail::on($konek)->with('produk')->where('no_ass', $assembling->no_ass)
            ->orderBy('created_at','desc')->get();

            foreach ($assemblingdetail as $row){
                $total_qty += $row->qty;
                $subtotal = $row->hpp * $row->qty;
                $total_harga += $subtotal;
                $grand_total = number_format($total_harga,2,",",".");
            }

            $list_url= route('assembling.index');

            $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode', 'Open')->orwhere('reopen_status', 'true')->first();

            $Produk = Produk::on($konek)->Join('tb_item_bulanan', 'produk.id', '=', 'tb_item_bulanan.kode_produk')->where('ending_stock','>',0)->where('periode',$cek_bulan->periode)->where('kode_lokasi',auth()->user()->kode_lokasi)->pluck('produk.nama_produk','produk.id');

            $Company= Company::pluck('nama_company', 'kode_company');

            $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
            $tgl_jalan2 = $tgl_jalan->periode;
            $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
            $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
            $nama_lokasi = $get_lokasi->nama_lokasi;

            $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
            $nama_company = $get_company->nama_company;

            return view('admin.assemblingdetail.index', compact('assembling','assemblingdetail','list_url','Produk','total_qty','grand_total','Company','period', 'nama_lokasi','nama_company'));
        }
        else{
            alert()->success('Status POSTED / Periode Telah CLOSED: '.$tanggal,'GAGAL!')->persistent('Close');
            return redirect()->back();
        }
    }

    function produkChecker($id, $tahun, $bulan, $tanggal_baru, $tgl, $assembling, $koneksi)
    {
        $konek = static::konek();
        $assemblingdetail = AssemblingDetail::on($konek)->with('produk')->where('no_ass', request()->id)->get();

        $id = request()->id;

        $data = array();

        if(!empty($assemblingdetail)){
            foreach ($assemblingdetail as $rowdata){
                $data[] = array(
                    'id'=>$id,
                    'kode_produk'=>$rowdata->kode_produk,
                    'qty'=>$rowdata->qty,
                    'partnumber'=>$rowdata->partnumber,
                    'harga'=>$rowdata->hpp,
                );    
            }

        }

        if(!empty($assemblingdetail)){
            $leng = count($assemblingdetail);
            $i = 0;
            for($i = 0; $i < $leng; $i++){
                $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

                //TAMBAH 1 tb_item_bulanan
                if($tb_item_bulanan != null){
                    $produk_awal = $tb_item_bulanan->kode_produk;
                            
                    $stock_begin = $tb_item_bulanan->begin_stock;
                    $amount_begin = $tb_item_bulanan->begin_amount;
                    $stok_in = $tb_item_bulanan->in_stock;
                    $amount_masuk = $tb_item_bulanan->in_amount;
                    $stock_out = $tb_item_bulanan->out_stock;
                    $amount_keluar = $tb_item_bulanan->out_amount;
                    $stock_sale = $tb_item_bulanan->sale_stock;
                    $amount_sale = $tb_item_bulanan->sale_amount;  
                    $stock_trfin = $tb_item_bulanan->trf_in;
                    $amount_trfin = $tb_item_bulanan->trf_in_amount;
                    $stock_trfout = $tb_item_bulanan->trf_out;
                    $amount_trfout = $tb_item_bulanan->trf_out_amount;
                    $stock_op = $tb_item_bulanan->stock_opname;
                    $amount_op = $tb_item_bulanan->amount_opname;
                    $stok_ending_new = $tb_item_bulanan->ending_stock;
                    $amount_new = $tb_item_bulanan->ending_amount;
                    $stock_adjustment = $tb_item_bulanan->adjustment_stock;
                    $amount_adjustment = $tb_item_bulanan->adjustment_amount;
                    $retur_beli_stock = $tb_item_bulanan->retur_beli_stock;
                    $retur_beli_amount = $tb_item_bulanan->retur_beli_amount;
                    $retur_jual_stock = $tb_item_bulanan->retur_jual_stock;
                    $retur_jual_amount = $tb_item_bulanan->retur_jual_amount;
                    $disassembling_stock = $tb_item_bulanan->disassembling_stock;
                    $disassembling_amount = $tb_item_bulanan->disassembling_amount;
                    $assembling_stock = $tb_item_bulanan->assembling_stock;
                    $assembling_amount = $tb_item_bulanan->assembling_amount;

                    $produk = Produk::on($konek)->find($data[$i]['kode_produk']);
                    $assemblingdetail2 = AssemblingDetail::on($konek)->where('no_ass', $id)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                    //Ambil Nilai Harga dan QTY Pada Detail
                    $harga = $data[$i]['harga'];
                    $qty_baru = $data[$i]['qty'];

                    $dis_stok_new = $disassembling_stock + $qty_baru;
                    $dis_amount_new = $disassembling_amount + ($harga * $qty_baru);

                    $end_stok_new = $stock_begin + $stok_in - $stock_out - $stock_sale + $stock_trfin - $stock_trfout + $stock_op + $stock_adjustment - $retur_beli_stock + $retur_jual_stock - $dis_stok_new + $assembling_stock;
                    $end_amount_new = $amount_begin + $amount_masuk - $amount_keluar - $amount_sale + $amount_trfin - $amount_trfout + $amount_op + $amount_adjustment - $retur_beli_amount + $retur_jual_amount - $dis_amount_new + $assembling_amount;

                    if($end_stok_new != 0){
                        $hpp = $end_amount_new / $end_stok_new;
                    }else{
                        $hpp = $tb_item_bulanan->hpp;
                        $end_amount_new = 0;
                    }

                    $tabel_baru = [
                        'disassembling_stock'=>$dis_stok_new,
                        'disassembling_amount'=>$dis_amount_new,
                        'ending_stock'=>$end_stok_new,
                        'ending_amount'=>$end_amount_new,
                        'hpp'=>$hpp,
                    ];
                    // dd($tabel_baru);

                    if($end_stok_new < 0){
                        exit();
                    }

                    $tgl_ass1 = $assembling->tanggal;
                    $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_ass1)->year;
                    $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_ass1)->month;

                    $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                    $status_reopen = $reopen->reopen_status;

                    if($status_reopen == 'true'){
                        $tgl_ass = $assembling->tanggal;
                        $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_ass)->year;
                        $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_ass)->month;

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
                            $assemblingdetail = AssemblingDetail::on($konek)->where('no_ass', $id)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                            $hpp = $assemblingdetail->hpp;

                            $stock_dis = $data[$i]['qty'];
                            $amount_dis = $hpp*$stock_dis;

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

                            $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi',$koneksi)->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->first();

                            if($tb_item_bulanan2 != null){
                                $bs = $tb_item_bulanan2->begin_stock;
                                $ba = $tb_item_bulanan2->begin_amount;
                                $es = $tb_item_bulanan2->ending_stock;
                                $ea = $tb_item_bulanan2->ending_amount;

                                $begin_stock1 = $bs - $stock_dis;
                                $begin_amount1 = $ba - $amount_dis;
                                $end_stok1 = $es - $stock_dis;
                                $end_amount1 = $ea - $amount_dis;

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

    function produkChecker2($id, $tahun, $bulan, $tanggal_baru, $tgl, $assembling, $koneksi)
    {
        $konek = self::konek();
        $assemblingdetail = AssemblingDetail::on($konek)->with('produk')->where('no_ass', request()->id)->get();
        $id = request()->id;

        $data = array();

        if(!empty($assemblingdetail)){
            foreach ($assemblingdetail as $rowdata){
                $data[] = array(
                    'id'=>$id,
                    'kode_produk'=>$rowdata->kode_produk,
                    'qty'=>$rowdata->qty,
                    'partnumber'=>$rowdata->partnumber,
                    'harga'=>$rowdata->hpp,
                );      
            }
        }

        if(!empty($assemblingdetail)){
            $leng = count($assemblingdetail);
            $i = 0;
            for($i = 0; $i < $leng; $i++){
                $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

                if($tb_item_bulanan != null){
                    //TAMBAH 2 tb_item_bulanan
                    $produk_awal = $tb_item_bulanan->kode_produk;
                            
                    $stock_begin = $tb_item_bulanan->begin_stock;
                    $amount_begin = $tb_item_bulanan->begin_amount;
                    $stok_in = $tb_item_bulanan->in_stock;
                    $amount_masuk = $tb_item_bulanan->in_amount;
                    $stock_out = $tb_item_bulanan->out_stock;
                    $amount_keluar = $tb_item_bulanan->out_amount;
                    $stock_sale = $tb_item_bulanan->sale_stock;
                    $amount_sale = $tb_item_bulanan->sale_amount;  
                    $stock_trfin = $tb_item_bulanan->trf_in;
                    $amount_trfin = $tb_item_bulanan->trf_in_amount;
                    $stock_trfout = $tb_item_bulanan->trf_out;
                    $amount_trfout = $tb_item_bulanan->trf_out_amount;
                    $stock_op = $tb_item_bulanan->stock_opname;
                    $amount_op = $tb_item_bulanan->amount_opname;
                    $stok_ending_new = $tb_item_bulanan->ending_stock;
                    $amount_new = $tb_item_bulanan->ending_amount;
                    $stock_adjustment = $tb_item_bulanan->adjustment_stock;
                    $amount_adjustment = $tb_item_bulanan->adjustment_amount;
                    $retur_beli_stock = $tb_item_bulanan->retur_beli_stock;
                    $retur_beli_amount = $tb_item_bulanan->retur_beli_amount;
                    $retur_jual_stock = $tb_item_bulanan->retur_jual_stock;
                    $retur_jual_amount = $tb_item_bulanan->retur_jual_amount;
                    $disassembling_stock = $tb_item_bulanan->disassembling_stock;
                    $disassembling_amount = $tb_item_bulanan->disassembling_amount;
                    $assembling_stock = $tb_item_bulanan->assembling_stock;
                    $assembling_amount = $tb_item_bulanan->assembling_amount;

                    $produk = Produk::on($konek)->find($data[$i]['kode_produk']);
                    $assemblingdetail2 = AssemblingDetail::on($konek)->where('no_ass', $id)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                    //Ambil Nilai Harga dan QTY Pada Detail
                    $harga = $data[$i]['harga'];
                    $qty_baru = $data[$i]['qty'];

                    $dis_stok_new = $disassembling_stock - $qty_baru;
                    $dis_amount_new = $disassembling_amount - ($harga * $qty_baru);

                    $end_stok_new = $stock_begin + $stok_in - $stock_out - $stock_sale + $stock_trfin - $stock_trfout + $stock_op + $stock_adjustment - $retur_beli_stock + $retur_jual_stock - $dis_stok_new + $assembling_stock;
                    $end_amount_new = $amount_begin + $amount_masuk - $amount_keluar - $amount_sale + $amount_trfin - $amount_trfout + $amount_op + $amount_adjustment - $retur_beli_amount + $retur_jual_amount - $dis_amount_new + $assembling_amount;

                    if($end_stok_new != 0){
                        $hpp = $end_amount_new / $end_stok_new;
                    }else{
                        $hpp = $tb_item_bulanan->hpp;
                        $end_amount_new = 0;
                    }

                    $tabel_baru = [
                        'disassembling_stock'=>$dis_stok_new,
                        'disassembling_amount'=>$dis_amount_new,
                        'ending_stock'=>$end_stok_new,
                        'ending_amount'=>$end_amount_new,
                        'hpp'=>$hpp,
                    ];

                    if($end_stok_new < 0){
                        exit();
                    }

                    $tgl_ass1 = $assembling->tanggal;
                    $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_ass1)->year;
                    $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_ass1)->month;

                    $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                    $status_reopen = $reopen->reopen_status;

                    if($status_reopen == 'true'){
                        $tgl_ass = $assembling->tanggal;
                        $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_ass)->year;
                        $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_ass)->month;

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
                            $assemblingdetail = AssemblingDetail::on($konek)->where('no_ass', $id)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                            $hpp = $assemblingdetail->hpp;

                            $stock_dis = $data[$i]['qty'];
                            $amount_dis = $hpp*$stock_dis;

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

                            $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi',$koneksi)->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->first();

                            if($tb_item_bulanan2 != null){
                                $bs = $tb_item_bulanan2->begin_stock;
                                $ba = $tb_item_bulanan2->begin_amount;
                                $es = $tb_item_bulanan2->ending_stock;
                                $ea = $tb_item_bulanan2->ending_amount;

                                $begin_stock1 = $bs + $stock_dis;
                                $begin_amount1 = $ba + $amount_dis;
                                $end_stok1 = $es + $stock_dis;
                                $end_amount1 = $ea + $amount_dis;

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

    function periodeChecker($tgl)
    {   
        $konek = self::konek();
        $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
        $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;

        $tabel = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
        // dd($tabel);

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

    public function getDatajurnal2(){
        $konek2 = self::konek2();
        $data = Ledger::on($konek2)->select('ledger.*','u5611458_gui_general_ledger_laravel.coa.ac_description')->join('u5611458_gui_general_ledger_laravel.coa','u5611458_gui_general_ledger_laravel.coa.account','=','ledger.account')->where('ledger.no_journal',request()->id)->orderBy('ledger.created_at','desc')->get();
        return response()->json($data);
    }

    public function cekjurnal2()
    {
        $konek = self::konek();
        $konek2 = self::konek2();
        $cek = Ledger::on($konek2)->where('no_journal', request()->no_journal)->first();
        $cek_ar = Assembling::on($konek)->where('no_journal', request()->no_journal)->first();

        $output = array(
            'journal_date'=>Carbon\Carbon::parse($cek->journal_date)->format('d/m/Y'),
            'reference'=>$cek->reference,
            'created_at'=>($cek_ar->created_at)->format('d/m/Y H:i:s'),
            'updated_by'=>$cek->updated_by,
            'status'=>$cek_ar->status,
        );
        return response()->json($output);
    }

    public function posting()
    {
        $konek = self::konek();
        $level = auth()->user()->level;
        $cek_company = Auth()->user()->kode_company;
        $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode','Disable')->first();
        $lokasi = auth()->user()->kode_lokasi;

        if($cek_bulan == null || $level == 'superadministrator' || $level == 'user_rince' || $level == 'user_thomas'){
            if($cek_company == '04' || $cek_company == '0401' || $cek_company == '03' || $cek_company == '05' || $cek_company == '02' || $cek_company == '06'){
                $assemblingdetail = AssemblingDetail::on($konek)->where('no_ass', request()->id)->get();
                $leng = count($assemblingdetail);
                $data = array();

                foreach ($assemblingdetail as $rowdata){
                    $data[] = array(
                        'kode_produk'=>$rowdata->kode_produk,
                    );
                }

                $kat1 = 0;

                for ($i = 0; $i < $leng; $i++) { 
                    $cek_produk = Produk::on($konek)->where('id', $data[$i]['kode_produk'])->first();

                    $cek_kategori = KategoriProduk::where('kode_kategori',$cek_produk->kode_kategori)->first();

                    if ($cek_company == '04') {
                        if ($cek_kategori->coa_gut == null || $cek_kategori->coabiaya_gut == null) {
                            $kat1 = 1;
                        }
                    }else if ($cek_company == '0401') {
                        if ($cek_kategori->coa_gutjkt == null || $cek_kategori->coabiaya_gutjkt == null) {
                            $kat1 = 1;
                        }
                    }else if ($cek_company == '03') {
                        if ($cek_kategori->coa_emkl == null || $cek_kategori->coabiaya_emkl == null) {
                            $kat1 = 1;
                        }
                    }else if ($cek_company == '05') {
                        if ($cek_kategori->coa_sub == null || $cek_kategori->coabiaya_sub == null) {
                            $kat1 = 1;
                        }
                    }else if ($cek_company == '02') {
                        if ($cek_kategori->coa_pbm == null || $cek_kategori->coabiaya_pbm == null) {
                            $kat1 = 1;
                        }
                    }else if ($cek_company == '06') {
                        if ($cek_kategori->coa_infra == null || $cek_kategori->coabiaya_infra == null) {
                            $kat1 = 1;
                        }
                    }

                    if ($kat1 == 1) {
                        $message = [
                            'success' => false,
                            'title' => 'Simpan',
                            'message' => 'Kategori: '.$cek_kategori->kode_kategori.' belum memiliki COA Persediaan / Biaya, silahkan lengkapi terlebih dahulu.',
                        ];
                        return response()->json($message);
                    }
                }
            }

            $assembling = Assembling::on($konek)->find(request()->id);
            $cek_status = $assembling->status;
            if($cek_status != 'OPEN'){  
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'POST Assembling, id : '.$assembling->no_ass.' sudah dilakukan! Pastikan Anda tidak membuka menu ASSEMBLING lebih dari 1',
                ];
                return response()->json($message);
            }

            $id = $assembling->no_ass;
            $crate_assembling = $assembling->created_at;
            $koneksi = $assembling->kode_lokasi;

            $tgl = $assembling->tanggal;
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

            $validate_produk = $this->produkChecker($id, $tahun, $bulan, $tanggal_baru, $tgl, $assembling, $koneksi);

            if($validate_produk == true){
                $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$assembling->kode_produk)->where('kode_lokasi',$koneksi)->where('partnumber',$assembling->partnumber)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

                if($tb_item_bulanan != null){
                    //TAMBAH 3 tb_item_bulanan
                    $produk_awal = $tb_item_bulanan->kode_produk;

                    $stock_begin = $tb_item_bulanan->begin_stock;
                    $amount_begin = $tb_item_bulanan->begin_amount;
                    $stok_in = $tb_item_bulanan->in_stock;
                    $amount_masuk = $tb_item_bulanan->in_amount;
                    $stock_out = $tb_item_bulanan->out_stock;
                    $amount_keluar = $tb_item_bulanan->out_amount;
                    $stock_sale = $tb_item_bulanan->sale_stock;
                    $amount_sale = $tb_item_bulanan->sale_amount;
                    $stock_trfin = $tb_item_bulanan->trf_in;
                    $amount_trfin = $tb_item_bulanan->trf_in_amount;
                    $stock_trfout = $tb_item_bulanan->trf_out;
                    $amount_trfout = $tb_item_bulanan->trf_out_amount;
                    $stock_op = $tb_item_bulanan->stock_opname;
                    $amount_op = $tb_item_bulanan->amount_opname;
                    $stok_ending_new = $tb_item_bulanan->ending_stock;
                    $amount_new = $tb_item_bulanan->ending_amount;
                    $stock_adjustment = $tb_item_bulanan->adjustment_stock;
                    $amount_adjustment = $tb_item_bulanan->adjustment_amount;
                    $retur_beli_stock = $tb_item_bulanan->retur_beli_stock;
                    $retur_beli_amount = $tb_item_bulanan->retur_beli_amount;
                    $retur_jual_stock = $tb_item_bulanan->retur_jual_stock;
                    $retur_jual_amount = $tb_item_bulanan->retur_jual_amount;
                    $disassembling_stock = $tb_item_bulanan->disassembling_stock;
                    $disassembling_amount = $tb_item_bulanan->disassembling_amount;
                    $assembling_stock = $tb_item_bulanan->assembling_stock;
                    $assembling_amount = $tb_item_bulanan->assembling_amount;
                    $rpk_stock = $tb_item_bulanan->retur_pakai_stock;
                    $rpk_amount = $tb_item_bulanan->retur_pakai_amount;

                    $produk = Produk::on($konek)->find($assembling->kode_produk);

                    // $assemblingdetail2 = AssemblingDetail::on($konek)->where('no_ass', $id)->where('kode_produk',$assembling->kode_produk)->where('partnumber',$assembling->partnumber)->first();

                    //Ambil Nilai Harga dan QTY Pada Header
                    $biaya_jasa = $assembling->biaya_jasa;

                    if ($assembling->update_stok == 'Y'){
                        $harga = $assembling->hpp;
                        $qty_baru = $assembling->qty_assembling;
                    }else {
                        $harga = 0;
                        $qty_baru = 0;
                    }
                    
                    $ass_stok_new = $assembling_stock + $qty_baru;
                    $ass_amount_new = $assembling_amount + ($harga * $qty_baru) + $biaya_jasa;
                    $end_stok_new = $stock_begin + $stok_in - $stock_out - $stock_sale + $stock_trfin - $stock_trfout + $stock_op + $stock_adjustment - $retur_beli_stock + $retur_jual_stock - $disassembling_stock + $ass_stok_new + $rpk_stock;
                    $end_amount_new = $amount_begin + $amount_masuk - $amount_keluar - $amount_sale + $amount_trfin - $amount_trfout + $amount_op + $amount_adjustment - $retur_beli_amount + $retur_jual_amount - $disassembling_amount + $ass_amount_new + $rpk_amount;

                    if($end_stok_new != 0){
                        $hpp = $end_amount_new / $end_stok_new;
                    }else{
                        $hpp = $tb_item_bulanan->hpp;
                        $end_amount_new = 0;
                    }

                    $tabel_baru = [
                        'assembling_stock'=>$ass_stok_new,
                        'assembling_amount'=>$ass_amount_new,
                        'ending_stock'=>$end_stok_new,
                        'ending_amount'=>$end_amount_new,
                        'hpp'=>$hpp,
                    ];

                    $update_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$produk_awal)->where('kode_lokasi',$koneksi)->where('partnumber',$assembling->partnumber)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($tabel_baru);
                }

                $tabel_history = [
                    'kode_produk'=>$produk_awal,
                    'no_transaksi'=>'Assembling, id : '.$id,
                    'tanggal_transaksi'=>$tgl,
                    'jam_transaksi'=>$crate_assembling,
                    'qty_transaksi'=>$assembling->qty_assembling,
                    'harga_transaksi'=>$assembling->hpp,
                    'total_transaksi'=>$assembling->qty_assembling*$assembling->hpp,
                    'kode_lokasi'=>$koneksi,
                ];

                $update_produk_history = tb_produk_history::on($konek)->create($tabel_history);

                $tgl_ass1 = $assembling->tanggal;
                $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_ass1)->year;
                $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_ass1)->month;

                $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                $status_reopen = $reopen->reopen_status;

                if($status_reopen == 'true'){
                    $tgl_ass = $assembling->tanggal;
                    $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_ass)->year;
                    $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_ass)->month;

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
                        $assemblingdetail = AssemblingDetail::on($konek)->where('no_ass', $id)->where('kode_produk',$assembling->kode_produk)->where('partnumber',$assembling->partnumber)->first();

                        if ($assembling->update_stok == 'Y'){
                            $hpp = $assembling->hpp;
                            $stock_ass = $assembling->qty_assembling;
                        }else {
                            $hpp = 0;
                            $stock_ass = 0;
                        }

                        $amount_ass = ($hpp*$stock_ass) + $assembling->biaya_jasa;

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

                        $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$assembling->kode_produk)->where('partnumber',$assembling->partnumber)->where('kode_lokasi',$koneksi)->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->first();

                        if($tb_item_bulanan2 != null){
                            $bs = $tb_item_bulanan2->begin_stock;
                            $ba = $tb_item_bulanan2->begin_amount;
                            $es = $tb_item_bulanan2->ending_stock;
                            $ea = $tb_item_bulanan2->ending_amount;

                            $begin_stock1 = $bs + $stock_ass;
                            $begin_amount1 = $ba + $amount_ass;

                            $end_stok1 = $es + $stock_ass;
                            $end_amount1 = $ea + $amount_ass;

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

                            $update_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$assembling->kode_produk)->where('partnumber',$assembling->partnumber)->where('kode_lokasi',$koneksi)->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->update($tabel_baru2);
                        }
                        $j++;
                    }
                }


                $assemblingdetail = AssemblingDetail::on($konek)->with('produk')->where('no_ass', request()->id)->get();
                $id = request()->id;

                $data = array();

                if(!empty($assemblingdetail)){
                    foreach ($assemblingdetail as $rowdata){
                        $data[] = array(
                           'id'=>$id,
                           'kode_produk'=>$rowdata->kode_produk,
                           'qty'=>$rowdata->qty,
                           'partnumber'=>$rowdata->partnumber,
                           'harga'=>$rowdata->hpp,
                        );
                    }
                }

                if(!empty($assemblingdetail)){
                    $leng = count($assemblingdetail);

                    $i = 0;
                    for($i = 0; $i < $leng; $i++){
                        $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

                        if($tb_item_bulanan != null){
                            //TAMBAH 4 tb_item_bulanan
                            $produk_awal = $tb_item_bulanan->kode_produk;
                            
                            $stock_begin = $tb_item_bulanan->begin_stock;
                            $amount_begin = $tb_item_bulanan->begin_amount;
                            $stok_in = $tb_item_bulanan->in_stock;
                            $amount_masuk = $tb_item_bulanan->in_amount;
                            $stock_out = $tb_item_bulanan->out_stock;
                            $amount_keluar = $tb_item_bulanan->out_amount;
                            $stock_sale = $tb_item_bulanan->sale_stock;
                            $amount_sale = $tb_item_bulanan->sale_amount;  
                            $stock_trfin = $tb_item_bulanan->trf_in;
                            $amount_trfin = $tb_item_bulanan->trf_in_amount;
                            $stock_trfout = $tb_item_bulanan->trf_out;
                            $amount_trfout = $tb_item_bulanan->trf_out_amount;
                            $stock_op = $tb_item_bulanan->stock_opname;
                            $amount_op = $tb_item_bulanan->amount_opname;
                            $stok_ending_new = $tb_item_bulanan->ending_stock;
                            $amount_new = $tb_item_bulanan->ending_amount;
                            $stock_adjustment = $tb_item_bulanan->adjustment_stock;
                            $amount_adjustment = $tb_item_bulanan->adjustment_amount;
                            $retur_beli_stock = $tb_item_bulanan->retur_beli_stock;
                            $retur_beli_amount = $tb_item_bulanan->retur_beli_amount;
                            $retur_jual_stock = $tb_item_bulanan->retur_jual_stock;
                            $retur_jual_amount = $tb_item_bulanan->retur_jual_amount;
                            $disassembling_stock = $tb_item_bulanan->disassembling_stock;
                            $disassembling_amount = $tb_item_bulanan->disassembling_amount;
                            $assembling_stock = $tb_item_bulanan->assembling_stock;
                            $assembling_amount = $tb_item_bulanan->assembling_amount;
                            $rpk_stock = $tb_item_bulanan->retur_pakai_stock;
                            $rpk_amount = $tb_item_bulanan->retur_pakai_amount;

                            $produk = Produk::on($konek)->find($data[$i]['kode_produk']);

                            $assemblingdetail2 = AssemblingDetail::on($konek)->where('no_ass', $id)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                            $harga = $data[$i]['harga'];
                            $qty_baru = $data[$i]['qty'];

                            $dis_stok_new = $disassembling_stock + $qty_baru;
                            $dis_amount_new = $disassembling_amount + ($harga * $qty_baru);
                            $end_stok_new = $stock_begin + $stok_in - $stock_out - $stock_sale + $stock_trfin - $stock_trfout + $stock_op + $stock_adjustment - $retur_beli_stock + $retur_jual_stock - $dis_stok_new + $assembling_stock + $rpk_stock;
                            $end_amount_new = $amount_begin + $amount_masuk - $amount_keluar - $amount_sale + $amount_trfin - $amount_trfout + $amount_op + $amount_adjustment - $retur_beli_amount + $retur_jual_amount - $dis_amount_new + $assembling_amount + $rpk_amount;

                            if($end_stok_new != 0){
                                $hpp = $end_amount_new / $end_stok_new;
                            }else{
                                $hpp = $tb_item_bulanan->hpp;
                                $end_amount_new = 0;
                            }

                            $tabel_baru = [
                                'disassembling_stock'=>$dis_stok_new,
                                'disassembling_amount'=>$dis_amount_new,
                                'ending_stock'=>$end_stok_new,
                                'ending_amount'=>$end_amount_new,
                                'hpp'=>$hpp,
                            ];
                            // dd($tabel_baru);

                            $update_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$produk_awal)->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($tabel_baru);
                        }else {
                            $period = tb_akhir_bulan::on($konek)->where('status_periode','Open')->orwhere('reopen_status','true')->first();
                            $cekproduk = Produk::on($konek)->where('id', $data[$i]['kode_produk'])->first();
                            
                            $diss_amt = $data[$i]['qty']*$data[$i]['harga'];
                            $tabel_baru = [
                                'periode'=>$period->periode,
                                'kode_produk'=>$data[$i]['kode_produk'],
                                'partnumber'=>$data[$i]['partnumber'],
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
                                'retur_pakai_stock'=>0,
                                'retur_pakai_amount'=>0,
                                'disassembling_stock'=>$data[$i]['qty'],
                                'disassembling_amount'=>$diss_amt,
                                'assembling_stock'=>0,
                                'assembling_amount'=>0,
                                'ending_stock'=>$data[$i]['qty'],
                                'ending_amount'=>$diss_amt,
                                'hpp'=>$data[$i]['harga'],
                                'kode_lokasi'=>auth()->user()->kode_lokasi,
                                'kode_company'=>auth()->user()->kode_company,
                            ];
                            $update_item_bulanan = tb_item_bulanan::on($konek)->create($tabel_baru);
                        }

                        $tabel_history = [
                            'kode_produk'=>$data[$i]['kode_produk'],
                            'no_transaksi'=>'Assembling, id : '.$id,
                            'tanggal_transaksi'=>$tgl,
                            'jam_transaksi'=>$crate_assembling,
                            'qty_transaksi'=>$data[$i]['qty'],
                            'harga_transaksi'=>$data[$i]['harga'],
                            'total_transaksi'=>$data[$i]['qty']*$data[$i]['harga'],
                            'kode_lokasi'=>$koneksi,
                        ];

                        $update_produk_history = tb_produk_history::on($konek)->create($tabel_history);

                        $tgl_ass1 = $assembling->tanggal;
                        $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_ass1)->year;
                        $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_ass1)->month;

                        $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                        $status_reopen = $reopen->reopen_status;

                        if($status_reopen == 'true'){
                            $tgl_ass = $disassembling->tanggal;
                            $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_ass)->year;
                            $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_ass)->month;

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
                                $assemblingdetail = AssemblingDetail::on($konek)->where('no_ass', $id)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                                $hpp = $data[$i]['harga'];

                                $stock_dis = $data[$i]['qty'];
                                $amount_dis = $hpp*$stock_dis;

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

                                $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi',$koneksi)->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->first();

                                if($tb_item_bulanan2 != null){
                                    $bs = $tb_item_bulanan2->begin_stock;
                                    $ba = $tb_item_bulanan2->begin_amount;
                                    $es = $tb_item_bulanan2->ending_stock;
                                    $ea = $tb_item_bulanan2->ending_amount;

                                    $begin_stock1 = $bs + $stock_dis;
                                    $begin_amount1 = $ba + $amount_dis;

                                    $end_stok1 = $es + $stock_dis;
                                    $end_amount1 = $ea + $amount_dis;

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

                                    $update_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi',$koneksi)->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->update($tabel_baru2);
                                }
                                $j++;
                            }
                        }
                    }
                }
                
                $disassembling = Assembling::on($konek)->find(request()->id);
                $disassembling->status = "POSTED";
                $disassembling->save(); 

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Post Assembling, id : '.$id.'.','created_by'=>$nama,'updated_by'=>$nama];
                user_history::on($konek)->create($tmp);

                $leng = count($assemblingdetail);

                //UPDATE JURNAL
                // if ($leng != 0 && $assembling->update_stok != 'N'){
                //     if($cek_company == '04' || $cek_company == '0401' || $cek_company == '03' || $cek_company == '0501'){
                //         $konek2 = self::konek2();
                //         $cek_company = Auth()->user()->kode_company;

                //         $total_qty = 0;
                //         $total_harga = 0;
                //         $grand_total = 0;
                //         $detail = AssemblingDetail::on($konek)->where('no_ass',$assembling->no_ass)->get();
                //         foreach ($detail as $row){
                //             $total_qty += $row->qty;
                //             $subtotal = $row->hpp * $row->qty;
                //             $total_harga += $subtotal;
                //             $grand_total = $total_harga;
                //         }

                //         $gt_apd = 0;
                //         $gt_ban = 0;
                //         $gt_bbm = 0;
                //         $gt_oli = 0;
                //         $gt_sprt = 0;
                //         $gt_unit = 0;

                //         $gt_apd2 = 0;
                //         $gt_ban2 = 0;
                //         $gt_bbm2 = 0;
                //         $gt_oli2 = 0;
                //         $gt_sprt2 = 0;
                //         $gt_unit2 = 0;

                //         for ($i = 0; $i < $leng; $i++) { 
                //             $cek_bulan2 = tb_akhir_bulan::on($konek)->where('status_periode', 'Open')->orwhere('reopen_status', 'true')->first();
                //             $cek_produk = Produk::on($konek)->where('id', $data[$i]['kode_produk'])->first();
                //             $cek_monthly = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi',$lokasi)->where('periode',$cek_bulan2->periode)->first();

                //             $bulan = Carbon\Carbon::parse($assembling->tanggal)->format('m');
                //             $tahun = Carbon\Carbon::parse($assembling->tanggal)->format('Y');

                //             if($cek_produk->kode_kategori == 'APD'){
                //                 $gt_apd += $data[$i]['qty'] * $data[$i]['harga'];
                //             }

                //             if($cek_produk->kode_kategori == 'BAN'){
                //                 $gt_ban += $data[$i]['qty'] * $data[$i]['harga'];
                //             }

                //             if($cek_produk->kode_kategori == 'BBM'){
                //                 $gt_bbm += $data[$i]['qty'] * $data[$i]['harga'];
                //             }

                //             if($cek_produk->kode_kategori == 'OLI'){
                //                 $gt_oli += $data[$i]['qty'] * $data[$i]['harga'];
                //             }

                //             if($cek_produk->kode_kategori == 'SPRT'){
                //                 $gt_sprt += $data[$i]['qty'] * $data[$i]['harga'];
                //             }

                //             if($cek_produk->kode_kategori == 'UNIT'){
                //                 $gt_unit += $data[$i]['qty'] * $data[$i]['harga'];
                //             }
                //         }

                //         if ($assembling->update_stok == 'Y'){
                //             $qty_ass = $assembling->qty_assembling;
                //             $hpp_ass = $assembling->hpp;
                //         }else {
                //             $qty_ass = 0;
                //             $hpp_ass = 0;
                //         }

                //         $cek_produk2 = Produk::on($konek)->where('id', $assembling->kode_produk)->first();
                //         if($cek_produk2->kode_kategori == 'APD'){
                //             $gt_apd2 += ($qty_ass * $hpp_ass) + $assembling->biaya_jasa;
                //         }

                //         if($cek_produk2->kode_kategori == 'BAN'){
                //             $gt_ban2 += ($qty_ass * $hpp_ass) + $assembling->biaya_jasa;
                //         }

                //         if($cek_produk2->kode_kategori == 'BBM'){
                //             $gt_bbm2 += ($qty_ass * $hpp_ass) + $assembling->biaya_jasa;
                //         }

                //         if($cek_produk2->kode_kategori == 'OLI'){
                //             $gt_oli2 += ($qty_ass * $hpp_ass) + $assembling->biaya_jasa;
                //         }

                //         if($cek_produk2->kode_kategori == 'SPRT'){
                //             $gt_sprt2 += ($qty_ass * $hpp_ass) + $assembling->biaya_jasa;
                //         }

                //         if($cek_produk2->kode_kategori == 'UNIT'){
                //             $gt_unit2 += ($qty_ass * $hpp_ass) + $assembling->biaya_jasa;
                //         }

                //         if($gt_apd != 0 || $gt_apd2 != 0){
                //             if ($cek_company == '04') {
                //                 $kategori = KategoriProduk::where('kode_kategori', 'APD')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_gut)->first();
                //             }else if($cek_company == '0401'){
                //                 $kategori = KategoriProduk::where('kode_kategori', 'APD')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_gutjkt)->first();
                //             }else if($cek_company == '03'){
                //                 $kategori = KategoriProduk::where('kode_kategori', 'APD')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_emkl)->first();
                //             }else if($cek_company == '02'){
                //                 $kategori = KategoriProduk::where('kode_kategori', 'APD')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_pbm)->first();
                //             }else if($cek_company == '01'){
                //                 $kategori = KategoriProduk::where('kode_kategori', 'APD')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_depo)->first();
                //             }else if($cek_company == '0501'){
                //                 $kategori = KategoriProduk::where('kode_kategori', 'APD')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
                //             }

                //             $cek_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                //             if ($cek_balance == null) {
                //                 //CEK SEBELUM
                //                 $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                //                 if($cek_sebelum != null){
                //                     $update_acc = [
                //                         'periode'=>$tanggal_baru,
                //                         'fiscalyear'=>$tahun,
                //                         'account'=>$coa_inventory->account,
                //                         'beginning_balance'=>$cek_sebelum->ending_balance,
                //                         'debet'=>0,
                //                         'kredit'=>0,
                //                         'ending_balance'=>$cek_sebelum->ending_balance,
                //                         'kode_lokasi'=>$lokasi,
                //                     ];

                //                     $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                //                 }else{
                //                     $update_acc = [
                //                         'periode'=>$tanggal_baru,
                //                         'fiscalyear'=>$tahun,
                //                         'account'=>$coa_inventory->account,
                //                         'beginning_balance'=>0,
                //                         'debet'=>0,
                //                         'kredit'=>0,
                //                         'ending_balance'=>0,
                //                         'kode_lokasi'=>$lokasi,
                //                     ];

                //                     $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                //                 }

                //                 //CEK SETELAH
                //                 $i = $bulan;
                //                 $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                //                 if ($cek_setelah != null) {
                //                     for($i = $bulan; $i <= 12; $i++){
                //                         $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                //                         if($cek_setelah != null){
                //                             if($gt_apd != 0){
                //                                 $begin = $cek_setelah->beginning_balance - $gt_apd;
                //                                 $debit = $cek_setelah->debet;
                //                                 $kredit = $cek_setelah->kredit;
                //                                 if($coa_inventory->normal_balance == 'D'){
                //                                     $ending_balance = $begin + $debit - $kredit;
                //                                 }else{
                //                                     $ending_balance = $begin - $debit + $kredit;
                //                                 }

                //                                 $tabel_baru = [
                //                                     'beginning_balance'=>$begin,
                //                                     'ending_balance'=>$ending_balance,
                //                                 ];

                //                                 $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                //                             }

                //                             if($gt_apd2 != 0){
                //                                 $begin = $cek_setelah->beginning_balance + $gt_apd2;
                //                                 $debit = $cek_setelah->debet;
                //                                 $kredit = $cek_setelah->kredit;
                //                                 if($coa_inventory->normal_balance == 'D'){
                //                                     $ending_balance = $begin + $debit - $kredit;
                //                                 }else{
                //                                     $ending_balance = $begin - $debit + $kredit;
                //                                 }

                //                                 $tabel_baru = [
                //                                     'beginning_balance'=>$begin,
                //                                     'ending_balance'=>$ending_balance,
                //                                 ];

                //                                 $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                //                             }
                //                         }
                //                     }
                //                 }
                //             }else{
                //                 //CEK SETELAH
                //                 $i = $bulan;
                //                 $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                //                 if ($cek_setelah != null) {
                //                     for($i = $bulan; $i <= 12; $i++){
                //                         $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                //                         if($cek_setelah != null){
                //                             if($gt_apd != 0){
                //                                 $begin = $cek_setelah->beginning_balance - $gt_apd;
                //                                 $debit = $cek_setelah->debet;
                //                                 $kredit = $cek_setelah->kredit;
                //                                 if($coa_inventory->normal_balance == 'D'){
                //                                     $ending_balance = $begin + $debit - $kredit;
                //                                 }else{
                //                                     $ending_balance = $begin - $debit + $kredit;
                //                                 }

                //                                 $tabel_baru = [
                //                                     'beginning_balance'=>$begin,
                //                                     'ending_balance'=>$ending_balance,
                //                                 ];

                //                                 $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                //                             }

                //                             if($gt_apd2 != 0){
                //                                 $begin = $cek_setelah->beginning_balance + $gt_apd2;
                //                                 $debit = $cek_setelah->debet;
                //                                 $kredit = $cek_setelah->kredit;
                //                                 if($coa_inventory->normal_balance == 'D'){
                //                                     $ending_balance = $begin + $debit - $kredit;
                //                                 }else{
                //                                     $ending_balance = $begin - $debit + $kredit;
                //                                 }

                //                                 $tabel_baru = [
                //                                     'beginning_balance'=>$begin,
                //                                     'ending_balance'=>$ending_balance,
                //                                 ];

                //                                 $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                //                             }
                //                         }
                //                     }
                //                 }
                //             }

                //             if($gt_apd != 0){
                //                 $update_ledger = [
                //                     'tahun'=>$tahun,
                //                     'periode'=>$bulan,
                //                     'account'=>$coa_inventory->account,
                //                     'no_journal'=>$assembling->no_journal,
                //                     'journal_date'=>$assembling->tanggal,
                //                     'db_cr'=>'K',
                //                     'reference'=>$assembling->no_ass,
                //                     'kredit'=>$gt_apd,
                //                     'kode_lokasi'=>$lokasi,
                //                 ];
                //                 $update = Ledger::on($konek2)->create($update_ledger);

                //                 $type = 'Inventory';
                //                 $transaksi = $assembling;
                //                 $tgl_trans = $assembling->tanggal;
                //                 $harga_acc = $gt_apd;
                //                 $dbkr = 'K';
                //                 $update_accbalance = $this->accbalance_kredit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                //             }

                //             if($gt_apd2 != 0){
                //                 $update_ledger = [
                //                     'tahun'=>$tahun,
                //                     'periode'=>$bulan,
                //                     'account'=>$coa_inventory->account,
                //                     'no_journal'=>$assembling->no_journal,
                //                     'journal_date'=>$assembling->tanggal,
                //                     'db_cr'=>'D',
                //                     'reference'=>$assembling->no_ass,
                //                     'debit'=>$gt_apd2,
                //                     'kode_lokasi'=>$lokasi,
                //                 ];
                //                 $update = Ledger::on($konek2)->create($update_ledger);

                //                 $type = 'Inventory';
                //                 $transaksi = $assembling;
                //                 $tgl_trans = $assembling->tanggal;
                //                 $harga_acc = $gt_apd2;
                //                 $dbkr = 'D';
                //                 $update_accbalance = $this->accbalance_debit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                //             }
                //         }

                //         if($gt_ban != 0 || $gt_ban2 != 0){
                //             if ($cek_company == '04') {
                //                 $kategori = KategoriProduk::where('kode_kategori', 'BAN')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_gut)->first();
                //             }else if($cek_company == '0401'){
                //                 $kategori = KategoriProduk::where('kode_kategori', 'BAN')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_gutjkt)->first();
                //             }else if($cek_company == '03'){
                //                 $kategori = KategoriProduk::where('kode_kategori', 'BAN')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_emkl)->first();
                //             }else if($cek_company == '02'){
                //                 $kategori = KategoriProduk::where('kode_kategori', 'BAN')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_pbm)->first();
                //             }else if($cek_company == '01'){
                //                 $kategori = KategoriProduk::where('kode_kategori', 'BAN')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_depo)->first();
                //             }else if($cek_company == '0501'){
                //                 $kategori = KategoriProduk::where('kode_kategori', 'BAN')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
                //             }

                //             $cek_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                //             if ($cek_balance == null) {
                //                 //CEK SEBELUM
                //                 $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                //                 if($cek_sebelum != null){
                //                     $update_acc = [
                //                         'periode'=>$tanggal_baru,
                //                         'fiscalyear'=>$tahun,
                //                         'account'=>$coa_inventory->account,
                //                         'beginning_balance'=>$cek_sebelum->ending_balance,
                //                         'debet'=>0,
                //                         'kredit'=>0,
                //                         'ending_balance'=>$cek_sebelum->ending_balance,
                //                         'kode_lokasi'=>$lokasi,
                //                     ];

                //                     $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                //                 }else{
                //                     $update_acc = [
                //                         'periode'=>$tanggal_baru,
                //                         'fiscalyear'=>$tahun,
                //                         'account'=>$coa_inventory->account,
                //                         'beginning_balance'=>0,
                //                         'debet'=>0,
                //                         'kredit'=>0,
                //                         'ending_balance'=>0,
                //                         'kode_lokasi'=>$lokasi,
                //                     ];

                //                     $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                //                 }

                //                 //CEK SETELAH
                //                 $i = $bulan;
                //                 $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                //                 if ($cek_setelah != null) {
                //                     for($i = $bulan; $i <= 12; $i++){
                //                         $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                //                         if($cek_setelah != null){
                //                             if($gt_ban != 0){
                //                                 $begin = $cek_setelah->beginning_balance - $gt_ban;
                //                                 $debit = $cek_setelah->debet;
                //                                 $kredit = $cek_setelah->kredit;
                //                                 if($coa_inventory->normal_balance == 'D'){
                //                                     $ending_balance = $begin + $debit - $kredit;
                //                                 }else{
                //                                     $ending_balance = $begin - $debit + $kredit;
                //                                 }

                //                                 $tabel_baru = [
                //                                     'beginning_balance'=>$begin,
                //                                     'ending_balance'=>$ending_balance,
                //                                 ];

                //                                 $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                //                             }


                //                             if($gt_ban2 != 0){
                //                                 $begin = $cek_setelah->beginning_balance + $gt_ban2;
                //                                 $debit = $cek_setelah->debet;
                //                                 $kredit = $cek_setelah->kredit;
                //                                 if($coa_inventory->normal_balance == 'D'){
                //                                     $ending_balance = $begin + $debit - $kredit;
                //                                 }else{
                //                                     $ending_balance = $begin - $debit + $kredit;
                //                                 }

                //                                 $tabel_baru = [
                //                                     'beginning_balance'=>$begin,
                //                                     'ending_balance'=>$ending_balance,
                //                                 ];

                //                                 $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                //                             }
                //                         }
                //                     }
                //                 }
                //             }else{
                //                 //CEK SETELAH
                //                 $i = $bulan;
                //                 $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                //                 if ($cek_setelah != null) {
                //                     for($i = $bulan; $i <= 12; $i++){
                //                         $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                //                         if($cek_setelah != null){
                //                             if($gt_ban != 0){
                //                                 $begin = $cek_setelah->beginning_balance - $gt_ban;
                //                                 $debit = $cek_setelah->debet;
                //                                 $kredit = $cek_setelah->kredit;
                //                                 if($coa_inventory->normal_balance == 'D'){
                //                                     $ending_balance = $begin + $debit - $kredit;
                //                                 }else{
                //                                     $ending_balance = $begin - $debit + $kredit;
                //                                 }

                //                                 $tabel_baru = [
                //                                     'beginning_balance'=>$begin,
                //                                     'ending_balance'=>$ending_balance,
                //                                 ];

                //                                 $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                //                             }


                //                             if($gt_ban2 != 0){
                //                                 $begin = $cek_setelah->beginning_balance + $gt_ban2;
                //                                 $debit = $cek_setelah->debet;
                //                                 $kredit = $cek_setelah->kredit;
                //                                 if($coa_inventory->normal_balance == 'D'){
                //                                     $ending_balance = $begin + $debit - $kredit;
                //                                 }else{
                //                                     $ending_balance = $begin - $debit + $kredit;
                //                                 }

                //                                 $tabel_baru = [
                //                                     'beginning_balance'=>$begin,
                //                                     'ending_balance'=>$ending_balance,
                //                                 ];

                //                                 $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                //                             }
                //                         }
                //                     }
                //                 }
                //             }

                //             if($gt_ban != 0){
                //                 $update_ledger = [
                //                     'tahun'=>$tahun,
                //                     'periode'=>$bulan,
                //                     'account'=>$coa_inventory->account,
                //                     'no_journal'=>$assembling->no_journal,
                //                     'journal_date'=>$assembling->tanggal,
                //                     'db_cr'=>'K',
                //                     'reference'=>$assembling->no_ass,
                //                     'kredit'=>$gt_ban,
                //                     'kode_lokasi'=>$lokasi,
                //                 ];
                //                 $update = Ledger::on($konek2)->create($update_ledger);

                //                 $type = 'Inventory';
                //                 $transaksi = $assembling;
                //                 $tgl_trans = $assembling->tanggal;
                //                 $harga_acc = $gt_ban;
                //                 $dbkr = 'K';
                //                 $update_accbalance = $this->accbalance_kredit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                //             }

                //             if($gt_ban2 != 0){
                //                 $update_ledger = [
                //                     'tahun'=>$tahun,
                //                     'periode'=>$bulan,
                //                     'account'=>$coa_inventory->account,
                //                     'no_journal'=>$assembling->no_journal,
                //                     'journal_date'=>$assembling->tanggal,
                //                     'db_cr'=>'D',
                //                     'reference'=>$assembling->no_ass,
                //                     'debit'=>$gt_ban2,
                //                     'kode_lokasi'=>$lokasi,
                //                 ];
                //                 $update = Ledger::on($konek2)->create($update_ledger);

                //                 $type = 'Inventory';
                //                 $transaksi = $assembling;
                //                 $tgl_trans = $assembling->tanggal;
                //                 $harga_acc = $gt_ban2;
                //                 $dbkr = 'D';
                //                 $update_accbalance = $this->accbalance_debit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                //             }
                //         }

                //         if($gt_bbm != 0 || $gt_bbm2 != 0){
                //             if ($cek_company == '04') {
                //                 $kategori = KategoriProduk::where('kode_kategori', 'BBM')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_gut)->first();
                //             }else if($cek_company == '0401'){
                //                 $kategori = KategoriProduk::where('kode_kategori', 'BBM')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_gutjkt)->first();
                //             }else if($cek_company == '03'){
                //                 $kategori = KategoriProduk::where('kode_kategori', 'BBM')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_emkl)->first();
                //             }else if($cek_company == '02'){
                //                 $kategori = KategoriProduk::where('kode_kategori', 'BBM')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_pbm)->first();
                //             }else if($cek_company == '01'){
                //                 $kategori = KategoriProduk::where('kode_kategori', 'BBM')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_depo)->first();
                //             }else if($cek_company == '0501'){
                //                 $kategori = KategoriProduk::where('kode_kategori', 'BBM')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
                //             }

                //             $cek_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                //             if ($cek_balance == null) {
                //                 //CEK SEBELUM
                //                 $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                //                 if($cek_sebelum != null){
                //                     $update_acc = [
                //                         'periode'=>$tanggal_baru,
                //                         'fiscalyear'=>$tahun,
                //                         'account'=>$coa_inventory->account,
                //                         'beginning_balance'=>$cek_sebelum->ending_balance,
                //                         'debet'=>0,
                //                         'kredit'=>0,
                //                         'ending_balance'=>$cek_sebelum->ending_balance,
                //                         'kode_lokasi'=>$lokasi,
                //                     ];

                //                     $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                //                 }else{
                //                     $update_acc = [
                //                         'periode'=>$tanggal_baru,
                //                         'fiscalyear'=>$tahun,
                //                         'account'=>$coa_inventory->account,
                //                         'beginning_balance'=>0,
                //                         'debet'=>0,
                //                         'kredit'=>0,
                //                         'ending_balance'=>0,
                //                         'kode_lokasi'=>$lokasi,
                //                     ];

                //                     $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                //                 }

                //                 //CEK SETELAH
                //                 $i = $bulan;
                //                 $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                //                 if ($cek_setelah != null) {
                //                     if($gt_bbm != 0){
                //                         for($i = $bulan; $i <= 12; $i++){
                //                             $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                //                             if($cek_setelah != null){
                //                                 if($gt_bbm != 0){
                //                                     $begin = $cek_setelah->beginning_balance - $gt_bbm;
                //                                     $debit = $cek_setelah->debet;
                //                                     $kredit = $cek_setelah->kredit;
                //                                     if($coa_inventory->normal_balance == 'D'){
                //                                         $ending_balance = $begin + $debit - $kredit;
                //                                     }else{
                //                                         $ending_balance = $begin - $debit + $kredit;
                //                                     }

                //                                     $tabel_baru = [
                //                                         'beginning_balance'=>$begin,
                //                                         'ending_balance'=>$ending_balance,
                //                                     ];

                //                                     $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                //                                 }


                //                                 if($gt_bbm2 != 0){
                //                                     $begin = $cek_setelah->beginning_balance + $gt_bbm2;
                //                                     $debit = $cek_setelah->debet;
                //                                     $kredit = $cek_setelah->kredit;
                //                                     if($coa_inventory->normal_balance == 'D'){
                //                                         $ending_balance = $begin + $debit - $kredit;
                //                                     }else{
                //                                         $ending_balance = $begin - $debit + $kredit;
                //                                     }

                //                                     $tabel_baru = [
                //                                         'beginning_balance'=>$begin,
                //                                         'ending_balance'=>$ending_balance,
                //                                     ];

                //                                     $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                //                                 }
                //                             }
                //                         }
                //                     }
                //                 }
                //             }else{
                //                 //CEK SETELAH
                //                 $i = $bulan;
                //                 $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                //                 if ($cek_setelah != null) {
                //                     if($gt_bbm != 0){
                //                         for($i = $bulan; $i <= 12; $i++){
                //                             $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                //                             if($cek_setelah != null){
                //                                 if($gt_bbm != 0){
                //                                     $begin = $cek_setelah->beginning_balance - $gt_bbm;
                //                                     $debit = $cek_setelah->debet;
                //                                     $kredit = $cek_setelah->kredit;
                //                                     if($coa_inventory->normal_balance == 'D'){
                //                                         $ending_balance = $begin + $debit - $kredit;
                //                                     }else{
                //                                         $ending_balance = $begin - $debit + $kredit;
                //                                     }

                //                                     $tabel_baru = [
                //                                         'beginning_balance'=>$begin,
                //                                         'ending_balance'=>$ending_balance,
                //                                     ];

                //                                     $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                //                                 }


                //                                 if($gt_bbm2 != 0){
                //                                     $begin = $cek_setelah->beginning_balance + $gt_bbm2;
                //                                     $debit = $cek_setelah->debet;
                //                                     $kredit = $cek_setelah->kredit;
                //                                     if($coa_inventory->normal_balance == 'D'){
                //                                         $ending_balance = $begin + $debit - $kredit;
                //                                     }else{
                //                                         $ending_balance = $begin - $debit + $kredit;
                //                                     }

                //                                     $tabel_baru = [
                //                                         'beginning_balance'=>$begin,
                //                                         'ending_balance'=>$ending_balance,
                //                                     ];

                //                                     $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                //                                 }
                //                             }
                //                         }
                //                     }
                //                 }
                //             }

                //             if($gt_bbm != 0){
                //                 $update_ledger = [
                //                     'tahun'=>$tahun,
                //                     'periode'=>$bulan,
                //                     'account'=>$coa_inventory->account,
                //                     'no_journal'=>$assembling->no_journal,
                //                     'journal_date'=>$assembling->tanggal,
                //                     'db_cr'=>'K',
                //                     'reference'=>$assembling->no_ass,
                //                     'kredit'=>$gt_bbm,
                //                     'kode_lokasi'=>$lokasi,
                //                 ];
                //                 $update = Ledger::on($konek2)->create($update_ledger);

                //                 $type = 'Inventory';
                //                 $transaksi = $assembling;
                //                 $tgl_trans = $assembling->tanggal;
                //                 $harga_acc = $gt_bbm;
                //                 $dbkr = 'K';
                //                 $update_accbalance = $this->accbalance_kredit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                //             }

                //             if($gt_bbm2 != 0){
                //                 $update_ledger = [
                //                     'tahun'=>$tahun,
                //                     'periode'=>$bulan,
                //                     'account'=>$coa_inventory->account,
                //                     'no_journal'=>$assembling->no_journal,
                //                     'journal_date'=>$assembling->tanggal,
                //                     'db_cr'=>'D',
                //                     'reference'=>$assembling->no_ass,
                //                     'debit'=>$gt_bbm2,
                //                     'kode_lokasi'=>$lokasi,
                //                 ];
                //                 $update = Ledger::on($konek2)->create($update_ledger);

                //                 $type = 'Inventory';
                //                 $transaksi = $assembling;
                //                 $tgl_trans = $assembling->tanggal;
                //                 $harga_acc = $gt_bbm2;
                //                 $dbkr = 'D';
                //                 $update_accbalance = $this->accbalance_debit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                //             }
                //         }

                //         if($gt_oli != 0 || $gt_oli2 != 0){
                //             if ($cek_company == '04') {
                //                 $kategori = KategoriProduk::where('kode_kategori', 'OLI')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_gut)->first();
                //             }else if($cek_company == '0401'){
                //                 $kategori = KategoriProduk::where('kode_kategori', 'OLI')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_gutjkt)->first();
                //             }else if($cek_company == '03'){
                //                 $kategori = KategoriProduk::where('kode_kategori', 'OLI')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_emkl)->first();
                //             }else if($cek_company == '02'){
                //                 $kategori = KategoriProduk::where('kode_kategori', 'OLI')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_pbm)->first();
                //             }else if($cek_company == '01'){
                //                 $kategori = KategoriProduk::where('kode_kategori', 'OLI')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_depo)->first();
                //             }else if($cek_company == '0501'){
                //                 $kategori = KategoriProduk::where('kode_kategori', 'OLI')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
                //             }

                //             $cek_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                //             if ($cek_balance == null) {
                //                 //CEK SEBELUM
                //                 $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                //                 if($cek_sebelum != null){
                //                     $update_acc = [
                //                         'periode'=>$tanggal_baru,
                //                         'fiscalyear'=>$tahun,
                //                         'account'=>$coa_inventory->account,
                //                         'beginning_balance'=>$cek_sebelum->ending_balance,
                //                         'debet'=>0,
                //                         'kredit'=>0,
                //                         'ending_balance'=>$cek_sebelum->ending_balance,
                //                         'kode_lokasi'=>$lokasi,
                //                     ];

                //                     $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                //                 }else{
                //                     $update_acc = [
                //                         'periode'=>$tanggal_baru,
                //                         'fiscalyear'=>$tahun,
                //                         'account'=>$coa_inventory->account,
                //                         'beginning_balance'=>0,
                //                         'debet'=>0,
                //                         'kredit'=>0,
                //                         'ending_balance'=>0,
                //                         'kode_lokasi'=>$lokasi,
                //                     ];

                //                     $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                //                 }

                //                 //CEK SETELAH
                //                 $i = $bulan;
                //                 $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                //                 if ($cek_setelah != null) {
                //                     for($i = $bulan; $i <= 12; $i++){
                //                         $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                //                         if($cek_setelah != null){
                //                             if($gt_oli != 0){
                //                                 $begin = $cek_setelah->beginning_balance - $gt_oli;
                //                                 $debit = $cek_setelah->debet;
                //                                 $kredit = $cek_setelah->kredit;
                //                                 if($coa_inventory->normal_balance == 'D'){
                //                                     $ending_balance = $begin + $debit - $kredit;
                //                                 }else{
                //                                     $ending_balance = $begin - $debit + $kredit;
                //                                 }

                //                                 $tabel_baru = [
                //                                     'beginning_balance'=>$begin,
                //                                     'ending_balance'=>$ending_balance,
                //                                 ];

                //                                 $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                //                             }

                //                             if($gt_oli2 != 0){
                //                                 $begin = $cek_setelah->beginning_balance + $gt_oli2;
                //                                 $debit = $cek_setelah->debet;
                //                                 $kredit = $cek_setelah->kredit;
                //                                 if($coa_inventory->normal_balance == 'D'){
                //                                     $ending_balance = $begin + $debit - $kredit;
                //                                 }else{
                //                                     $ending_balance = $begin - $debit + $kredit;
                //                                 }

                //                                 $tabel_baru = [
                //                                     'beginning_balance'=>$begin,
                //                                     'ending_balance'=>$ending_balance,
                //                                 ];

                //                                 $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                //                             }
                //                         }
                //                     }
                //                 }
                //             }else{
                //                 //CEK SETELAH
                //                 $i = $bulan;
                //                 $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                //                 if ($cek_setelah != null) {
                //                     for($i = $bulan; $i <= 12; $i++){
                //                         $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                //                         if($cek_setelah != null){
                //                             if($gt_oli != 0){
                //                                 $begin = $cek_setelah->beginning_balance - $gt_oli;
                //                                 $debit = $cek_setelah->debet;
                //                                 $kredit = $cek_setelah->kredit;
                //                                 if($coa_inventory->normal_balance == 'D'){
                //                                     $ending_balance = $begin + $debit - $kredit;
                //                                 }else{
                //                                     $ending_balance = $begin - $debit + $kredit;
                //                                 }

                //                                 $tabel_baru = [
                //                                     'beginning_balance'=>$begin,
                //                                     'ending_balance'=>$ending_balance,
                //                                 ];

                //                                 $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                //                             }

                //                             if($gt_oli2 != 0){
                //                                 $begin = $cek_setelah->beginning_balance + $gt_oli2;
                //                                 $debit = $cek_setelah->debet;
                //                                 $kredit = $cek_setelah->kredit;
                //                                 if($coa_inventory->normal_balance == 'D'){
                //                                     $ending_balance = $begin + $debit - $kredit;
                //                                 }else{
                //                                     $ending_balance = $begin - $debit + $kredit;
                //                                 }

                //                                 $tabel_baru = [
                //                                     'beginning_balance'=>$begin,
                //                                     'ending_balance'=>$ending_balance,
                //                                 ];

                //                                 $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                //                             }
                //                         }
                //                     }
                //                 }
                //             }

                //             if($gt_oli != 0){
                //                 $update_ledger = [
                //                     'tahun'=>$tahun,
                //                     'periode'=>$bulan,
                //                     'account'=>$coa_inventory->account,
                //                     'no_journal'=>$assembling->no_journal,
                //                     'journal_date'=>$assembling->tanggal,
                //                     'db_cr'=>'K',
                //                     'reference'=>$assembling->no_ass,
                //                     'kredit'=>$gt_oli,
                //                     'kode_lokasi'=>$lokasi,
                //                 ];
                //                 $update = Ledger::on($konek2)->create($update_ledger);

                //                 $type = 'Inventory';
                //                 $transaksi = $assembling;
                //                 $tgl_trans = $assembling->tanggal;
                //                 $harga_acc = $gt_oli;
                //                 $dbkr = 'K';
                //                 $update_accbalance = $this->accbalance_kredit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                //             }

                //             if($gt_oli2 != 0){
                //                 $update_ledger = [
                //                     'tahun'=>$tahun,
                //                     'periode'=>$bulan,
                //                     'account'=>$coa_inventory->account,
                //                     'no_journal'=>$assembling->no_journal,
                //                     'journal_date'=>$assembling->tanggal,
                //                     'db_cr'=>'D',
                //                     'reference'=>$assembling->no_ass,
                //                     'debit'=>$gt_oli2,
                //                     'kode_lokasi'=>$lokasi,
                //                 ];
                //                 $update = Ledger::on($konek2)->create($update_ledger);

                //                 $type = 'Inventory';
                //                 $transaksi = $assembling;
                //                 $tgl_trans = $assembling->tanggal;
                //                 $harga_acc = $gt_oli2;
                //                 $dbkr = 'D';
                //                 $update_accbalance = $this->accbalance_debit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                //             }
                //         }

                //         if($gt_sprt != 0 || $gt_sprt2 != 0){
                //             if ($cek_company == '04') {
                //                 $kategori = KategoriProduk::where('kode_kategori', 'SPRT')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_gut)->first();
                //             }else if($cek_company == '0401'){
                //                 $kategori = KategoriProduk::where('kode_kategori', 'SPRT')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_gutjkt)->first();
                //             }else if($cek_company == '03'){
                //                 $kategori = KategoriProduk::where('kode_kategori', 'SPRT')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_emkl)->first();
                //             }else if($cek_company == '02'){
                //                 $kategori = KategoriProduk::where('kode_kategori', 'SPRT')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_pbm)->first();
                //             }else if($cek_company == '01'){
                //                 $kategori = KategoriProduk::where('kode_kategori', 'SPRT')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_depo)->first();
                //             }else if($cek_company == '0501'){
                //                 $kategori = KategoriProduk::where('kode_kategori', 'SPRT')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
                //             }

                //             $cek_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                //             if ($cek_balance == null) {
                //                 //CEK SEBELUM
                //                 $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                //                 if($cek_sebelum != null){
                //                     $update_acc = [
                //                         'periode'=>$tanggal_baru,
                //                         'fiscalyear'=>$tahun,
                //                         'account'=>$coa_inventory->account,
                //                         'beginning_balance'=>$cek_sebelum->ending_balance,
                //                         'debet'=>0,
                //                         'kredit'=>0,
                //                         'ending_balance'=>$cek_sebelum->ending_balance,
                //                         'kode_lokasi'=>$lokasi,
                //                     ];

                //                     $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                //                 }else{
                //                     $update_acc = [
                //                         'periode'=>$tanggal_baru,
                //                         'fiscalyear'=>$tahun,
                //                         'account'=>$coa_inventory->account,
                //                         'beginning_balance'=>0,
                //                         'debet'=>0,
                //                         'kredit'=>0,
                //                         'ending_balance'=>0,
                //                         'kode_lokasi'=>$lokasi,
                //                     ];

                //                     $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                //                 }

                //                 //CEK SETELAH
                //                 $i = $bulan;
                //                 $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                //                 if ($cek_setelah != null) {
                //                     for($i = $bulan; $i <= 12; $i++){
                //                         $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                //                         if($cek_setelah != null){
                //                             if($gt_sprt != 0){
                //                                 $begin = $cek_setelah->beginning_balance - $gt_sprt;
                //                                 $debit = $cek_setelah->debet;
                //                                 $kredit = $cek_setelah->kredit;
                //                                 if($coa_inventory->normal_balance == 'D'){
                //                                     $ending_balance = $begin + $debit - $kredit;
                //                                 }else{
                //                                     $ending_balance = $begin - $debit + $kredit;
                //                                 }

                //                                 $tabel_baru = [
                //                                     'beginning_balance'=>$begin,
                //                                     'ending_balance'=>$ending_balance,
                //                                 ];

                //                                 $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                //                             }


                //                             if($gt_sprt2 != 0){
                //                                 $begin = $cek_setelah->beginning_balance + $gt_sprt2;
                //                                 $debit = $cek_setelah->debet;
                //                                 $kredit = $cek_setelah->kredit;
                //                                 if($coa_inventory->normal_balance == 'D'){
                //                                     $ending_balance = $begin + $debit - $kredit;
                //                                 }else{
                //                                     $ending_balance = $begin - $debit + $kredit;
                //                                 }

                //                                 $tabel_baru = [
                //                                     'beginning_balance'=>$begin,
                //                                     'ending_balance'=>$ending_balance,
                //                                 ];

                //                                 $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                //                             }
                //                         }
                //                     }
                //                 }
                //             }else{
                //                 //CEK SETELAH
                //                 $i = $bulan;
                //                 $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                //                 if ($cek_setelah != null) {
                //                     for($i = $bulan; $i <= 12; $i++){
                //                         $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                //                         if($cek_setelah != null){
                //                             if($gt_sprt != 0){
                //                                 $begin = $cek_setelah->beginning_balance - $gt_sprt;
                //                                 $debit = $cek_setelah->debet;
                //                                 $kredit = $cek_setelah->kredit;
                //                                 if($coa_inventory->normal_balance == 'D'){
                //                                     $ending_balance = $begin + $debit - $kredit;
                //                                 }else{
                //                                     $ending_balance = $begin - $debit + $kredit;
                //                                 }

                //                                 $tabel_baru = [
                //                                     'beginning_balance'=>$begin,
                //                                     'ending_balance'=>$ending_balance,
                //                                 ];

                //                                 $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                //                             }


                //                             if($gt_sprt2 != 0){
                //                                 $begin = $cek_setelah->beginning_balance + $gt_sprt2;
                //                                 $debit = $cek_setelah->debet;
                //                                 $kredit = $cek_setelah->kredit;
                //                                 if($coa_inventory->normal_balance == 'D'){
                //                                     $ending_balance = $begin + $debit - $kredit;
                //                                 }else{
                //                                     $ending_balance = $begin - $debit + $kredit;
                //                                 }

                //                                 $tabel_baru = [
                //                                     'beginning_balance'=>$begin,
                //                                     'ending_balance'=>$ending_balance,
                //                                 ];

                //                                 $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                //                             }
                //                         }
                //                     }
                //                 }
                //             }

                //             if($gt_sprt != 0){
                //                 $update_ledger = [
                //                     'tahun'=>$tahun,
                //                     'periode'=>$bulan,
                //                     'account'=>$coa_inventory->account,
                //                     'no_journal'=>$assembling->no_journal,
                //                     'journal_date'=>$assembling->tanggal,
                //                     'db_cr'=>'K',
                //                     'reference'=>$assembling->no_ass,
                //                     'kredit'=>$gt_sprt,
                //                     'kode_lokasi'=>$lokasi,
                //                 ];
                //                 $update = Ledger::on($konek2)->create($update_ledger);

                //                 $type = 'Inventory';
                //                 $transaksi = $assembling;
                //                 $tgl_trans = $assembling->tanggal;
                //                 $harga_acc = $gt_sprt;
                //                 $dbkr = 'K';
                //                 $update_accbalance = $this->accbalance_kredit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                //             }

                //             if($gt_sprt2 != 0){
                //                 $update_ledger = [
                //                     'tahun'=>$tahun,
                //                     'periode'=>$bulan,
                //                     'account'=>$coa_inventory->account,
                //                     'no_journal'=>$assembling->no_journal,
                //                     'journal_date'=>$assembling->tanggal,
                //                     'db_cr'=>'D',
                //                     'reference'=>$assembling->no_ass,
                //                     'debit'=>$gt_sprt2,
                //                     'kode_lokasi'=>$lokasi,
                //                 ];
                //                 $update = Ledger::on($konek2)->create($update_ledger);

                //                 $type = 'Inventory';
                //                 $transaksi = $assembling;
                //                 $tgl_trans = $assembling->tanggal;
                //                 $harga_acc = $gt_sprt2;
                //                 $dbkr = 'D';
                //                 $update_accbalance = $this->accbalance_debit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                //             }
                //         }

                //         if($gt_unit != 0 || $gt_unit2 != 0){
                //             if ($cek_company == '04') {
                //                 $kategori = KategoriProduk::where('kode_kategori', 'UNIT')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_gut)->first();
                //             }else if($cek_company == '0401'){
                //                 $kategori = KategoriProduk::where('kode_kategori', 'UNIT')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_gutjkt)->first();
                //             }else if($cek_company == '03'){
                //                 $kategori = KategoriProduk::where('kode_kategori', 'UNIT')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_emkl)->first();
                //             }else if($cek_company == '02'){
                //                 $kategori = KategoriProduk::where('kode_kategori', 'UNIT')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_pbm)->first();
                //             }else if($cek_company == '01'){
                //                 $kategori = KategoriProduk::where('kode_kategori', 'UNIT')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_depo)->first();
                //             }else if($cek_company == '0501'){
                //                 $kategori = KategoriProduk::where('kode_kategori', 'UNIT')->first();
                //                 $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
                //             }

                //             $cek_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                //             if ($cek_balance == null) {
                //                 //CEK SEBELUM
                //                 $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                //                 if($cek_sebelum != null){
                //                     $update_acc = [
                //                         'periode'=>$tanggal_baru,
                //                         'fiscalyear'=>$tahun,
                //                         'account'=>$coa_inventory->account,
                //                         'beginning_balance'=>$cek_sebelum->ending_balance,
                //                         'debet'=>0,
                //                         'kredit'=>0,
                //                         'ending_balance'=>$cek_sebelum->ending_balance,
                //                         'kode_lokasi'=>$lokasi,
                //                     ];

                //                     $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                //                 }else{
                //                     $update_acc = [
                //                         'periode'=>$tanggal_baru,
                //                         'fiscalyear'=>$tahun,
                //                         'account'=>$coa_inventory->account,
                //                         'beginning_balance'=>0,
                //                         'debet'=>0,
                //                         'kredit'=>0,
                //                         'ending_balance'=>0,
                //                         'kode_lokasi'=>$lokasi,
                //                     ];

                //                     $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                //                 }

                //                 //CEK SETELAH
                //                 $i = $bulan;
                //                 $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                //                 if ($cek_setelah != null) {
                //                     for($i = $bulan; $i <= 12; $i++){
                //                         $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                //                         if($cek_setelah != null){
                //                             if($gt_unit != 0){
                //                                 $begin = $cek_setelah->beginning_balance - $gt_unit;
                //                                 $debit = $cek_setelah->debet;
                //                                 $kredit = $cek_setelah->kredit;
                //                                 if($coa_inventory->normal_balance == 'D'){
                //                                     $ending_balance = $begin + $debit - $kredit;
                //                                 }else{
                //                                     $ending_balance = $begin - $debit + $kredit;
                //                                 }

                //                                 $tabel_baru = [
                //                                     'beginning_balance'=>$begin,
                //                                     'ending_balance'=>$ending_balance,
                //                                 ];

                //                                 $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                //                             }
                                            
                //                             if($gt_unit2 != 0){
                //                                 $begin = $cek_setelah->beginning_balance + $gt_unit2;
                //                                 $debit = $cek_setelah->debet;
                //                                 $kredit = $cek_setelah->kredit;
                //                                 if($coa_inventory->normal_balance == 'D'){
                //                                     $ending_balance = $begin + $debit - $kredit;
                //                                 }else{
                //                                     $ending_balance = $begin - $debit + $kredit;
                //                                 }

                //                                 $tabel_baru = [
                //                                     'beginning_balance'=>$begin,
                //                                     'ending_balance'=>$ending_balance,
                //                                 ];

                //                                 $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                //                             }
                //                         }
                //                     }
                //                 }
                //             }else{
                //                 //CEK SETELAH
                //                 $i = $bulan;
                //                 $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                //                 if ($cek_setelah != null) {
                //                     for($i = $bulan; $i <= 12; $i++){
                //                         $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                //                         if($cek_setelah != null){
                //                             if($gt_unit != 0){
                //                                 $begin = $cek_setelah->beginning_balance - $gt_unit;
                //                                 $debit = $cek_setelah->debet;
                //                                 $kredit = $cek_setelah->kredit;
                //                                 if($coa_inventory->normal_balance == 'D'){
                //                                     $ending_balance = $begin + $debit - $kredit;
                //                                 }else{
                //                                     $ending_balance = $begin - $debit + $kredit;
                //                                 }

                //                                 $tabel_baru = [
                //                                     'beginning_balance'=>$begin,
                //                                     'ending_balance'=>$ending_balance,
                //                                 ];

                //                                 $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                //                             }
                                            
                //                             if($gt_unit2 != 0){
                //                                 $begin = $cek_setelah->beginning_balance + $gt_unit2;
                //                                 $debit = $cek_setelah->debet;
                //                                 $kredit = $cek_setelah->kredit;
                //                                 if($coa_inventory->normal_balance == 'D'){
                //                                     $ending_balance = $begin + $debit - $kredit;
                //                                 }else{
                //                                     $ending_balance = $begin - $debit + $kredit;
                //                                 }

                //                                 $tabel_baru = [
                //                                     'beginning_balance'=>$begin,
                //                                     'ending_balance'=>$ending_balance,
                //                                 ];

                //                                 $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                //                             }
                //                         }
                //                     }
                //                 }
                //             }

                //             if($gt_unit != 0){
                //                 $update_ledger = [
                //                     'tahun'=>$tahun,
                //                     'periode'=>$bulan,
                //                     'account'=>$coa_inventory->account,
                //                     'no_journal'=>$assembling->no_journal,
                //                     'journal_date'=>$assembling->tanggal,
                //                     'db_cr'=>'K',
                //                     'reference'=>$assembling->no_ass,
                //                     'kredit'=>$gt_unit,
                //                     'kode_lokasi'=>$lokasi,
                //                 ];
                //                 $update = Ledger::on($konek2)->create($update_ledger);

                //                 $type = 'Inventory';
                //                 $transaksi = $assembling;
                //                 $tgl_trans = $assembling->tanggal;
                //                 $harga_acc = $gt_unit;
                //                 $dbkr = 'K';
                //                 $update_accbalance = $this->accbalance_kredit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                //             }

                //             if($gt_unit2 != 0){
                //                 $update_ledger = [
                //                     'tahun'=>$tahun,
                //                     'periode'=>$bulan,
                //                     'account'=>$coa_inventory->account,
                //                     'no_journal'=>$assembling->no_journal,
                //                     'journal_date'=>$assembling->tanggal,
                //                     'db_cr'=>'D',
                //                     'reference'=>$assembling->no_ass,
                //                     'debit'=>$gt_unit2,
                //                     'kode_lokasi'=>$lokasi,
                //                 ];
                //                 $update = Ledger::on($konek2)->create($update_ledger);

                //                 $type = 'Inventory';
                //                 $transaksi = $assembling;
                //                 $tgl_trans = $assembling->tanggal;
                //                 $harga_acc = $gt_unit2;
                //                 $dbkr = 'D';
                //                 $update_accbalance = $this->accbalance_debit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                //             }
                //         }
                //     }
                // }
                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data berhasil di POST.'
                    ];
                return response()->json($message);
            }else{
                $message = [
                    'success' => false,
                    'title' => 'Update',
                    'message' => 'Re-Open [Bulan '.$bulan.'; Tahun '.$tahun.'].'
                ];
                return response()->json($message);
            }
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
        $lokasi = auth()->user()->kode_lokasi;

        if($cek_bulan == null || $level == 'superadministrator' || $level == 'user_rince' || $level == 'user_thomas'){
            $assembling = Assembling::on($konek)->find(request()->id);
            $cek_status = $assembling->status;
            if($cek_status != 'POSTED'){  
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'UNPOST Assembling, id : '.$assembling->no_ass.' sudah dilakukan! Pastikan Anda tidak membuka menu ASSEMBLING lebih dari 1',
                ];
                return response()->json($message);
            }

            $id = $assembling->no_ass;
            $koneksi = $assembling->kode_lokasi;

            $tgl = $assembling->tanggal;
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

            $validate_produk = $this->produkChecker2($id, $tahun, $bulan, $tanggal_baru, $tgl, $assembling, $koneksi);
            
            if($validate_produk == true){
                $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$assembling->kode_produk)->where('kode_lokasi',$koneksi)->where('partnumber',$assembling->partnumber)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

                if($tb_item_bulanan != null){
                    $produk_awal = $tb_item_bulanan->kode_produk;

                    $stock_begin = $tb_item_bulanan->begin_stock;
                    $amount_begin = $tb_item_bulanan->begin_amount;
                    $stok_in = $tb_item_bulanan->in_stock;
                    $amount_masuk = $tb_item_bulanan->in_amount;
                    $stock_out = $tb_item_bulanan->out_stock;
                    $amount_keluar = $tb_item_bulanan->out_amount;
                    $stock_sale = $tb_item_bulanan->sale_stock;
                    $amount_sale = $tb_item_bulanan->sale_amount;  
                    $stock_trfin = $tb_item_bulanan->trf_in;
                    $amount_trfin = $tb_item_bulanan->trf_in_amount;
                    $stock_trfout = $tb_item_bulanan->trf_out;
                    $amount_trfout = $tb_item_bulanan->trf_out_amount;
                    $stock_op = $tb_item_bulanan->stock_opname;
                    $amount_op = $tb_item_bulanan->amount_opname;
                    $stok_ending_new = $tb_item_bulanan->ending_stock;
                    $amount_new = $tb_item_bulanan->ending_amount;
                    $stock_adjustment = $tb_item_bulanan->adjustment_stock;
                    $amount_adjustment = $tb_item_bulanan->adjustment_amount;
                    $retur_beli_stock = $tb_item_bulanan->retur_beli_stock;
                    $retur_beli_amount = $tb_item_bulanan->retur_beli_amount;
                    $retur_jual_stock = $tb_item_bulanan->retur_jual_stock;
                    $retur_jual_amount = $tb_item_bulanan->retur_jual_amount;
                    $disassembling_stock = $tb_item_bulanan->disassembling_stock;
                    $disassembling_amount = $tb_item_bulanan->disassembling_amount;
                    $assembling_stock = $tb_item_bulanan->assembling_stock;
                    $assembling_amount = $tb_item_bulanan->assembling_amount;
                    $rpk_stock = $tb_item_bulanan->retur_pakai_stock;
                    $rpk_amount = $tb_item_bulanan->retur_pakai_amount;

                    $produk = Produk::on($konek)->find($assembling->kode_produk);

                    // $assemblingdetail2 = AssemblingDetail::on($konek)->where('no_ass', $id)->where('kode_produk',$assembling->kode_produk)->where('partnumber',$assembling->partnumber)->first();

                    $biaya_jasa = $assembling->biaya_jasa;

                    if ($assembling->update_stok == 'Y'){
                        $harga = $assembling->hpp;
                        $qty_baru = $assembling->qty_assembling;
                    }else {
                        $harga = 0;
                        $qty_baru = 0;
                    }

                    $ass_stok_new = $assembling_stock - $qty_baru;
                    $ass_amount_new = $assembling_amount - ($harga * $qty_baru) - $biaya_jasa;
                    $end_stok_new = $stock_begin + $stok_in - $stock_out - $stock_sale + $stock_trfin - $stock_trfout + $stock_op + $stock_adjustment - $retur_beli_stock + $retur_jual_stock - $disassembling_stock + $ass_stok_new + $rpk_stock;
                    $end_amount_new = $amount_begin + $amount_masuk - $amount_keluar - $amount_sale + $amount_trfin - $amount_trfout + $amount_op + $amount_adjustment - $retur_beli_amount + $retur_jual_amount - $disassembling_amount + $ass_amount_new + $rpk_amount;

                    if($end_stok_new != 0){
                        $hpp = $end_amount_new / $end_stok_new;
                    }else{
                        $hpp = $tb_item_bulanan->hpp;
                        $end_amount_new = 0;
                    }

                    $tabel_baru = [
                        'assembling_stock'=>$ass_stok_new,
                        'assembling_amount'=>$ass_amount_new,
                        'ending_stock'=>$end_stok_new,
                        'ending_amount'=>$end_amount_new,
                        'hpp'=>$hpp,
                    ];

                    $update_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$produk_awal)->where('kode_lokasi',$koneksi)->where('partnumber',$assembling->partnumber)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($tabel_baru);
                }

                $tgl_ass1 = $assembling->tanggal;
                $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_ass1)->year;
                $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_ass1)->month;

                $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                $status_reopen = $reopen->reopen_status;

                if($status_reopen == 'true'){
                    $tgl_ass = $assembling->tanggal;
                    $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_ass)->year;
                    $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_ass)->month;

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
                        $assemblingdetail = AssemblingDetail::on($konek)->where('no_ass', $id)->where('kode_produk',$assembling->kode_produk)->where('partnumber',$assembling->partnumber)->first();

                        if ($assembling->update_stok == 'Y'){
                            $hpp = $assembling->hpp;
                            $stock_ass = $assembling->qty_assembling;
                        }else {
                            $hpp = 0;
                            $stock_ass = 0;
                        }

                        $biaya_jasa = $assembling->biaya_jasa;
                        $amount_ass = ($hpp*$stock_ass) + $biaya_jasa;

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

                        $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$assembling->kode_produk)->where('partnumber',$assembling->partnumber)->where('kode_lokasi',$koneksi)->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->first();

                        if($tb_item_bulanan2 != null){
                            $bs = $tb_item_bulanan2->begin_stock;
                            $ba = $tb_item_bulanan2->begin_amount;
                            $es = $tb_item_bulanan2->ending_stock;
                            $ea = $tb_item_bulanan2->ending_amount;

                            $begin_stock1 = $bs - $stock_ass;
                            $begin_amount1 = $ba - $amount_ass;

                            $end_stok1 = $es - $stock_ass;
                            $end_amount1 = $ea - $amount_ass;

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

                            $update_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$assembling->kode_produk)->where('partnumber',$assembling->partnumber)->where('kode_lokasi',$koneksi)->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->update($tabel_baru2);
                        }
                        $j++;
                    }
                }


                $assemblingdetail = AssemblingDetail::on($konek)->with('produk')->where('no_ass', request()->id)->get();
                $id = request()->id;

                $data = array();
                $kode_produk = array();
                $qty = array();

                if(!empty($assemblingdetail)){
                    foreach ($assemblingdetail as $rowdata){
                        $data[] = array(
                            'id'=>$id,
                            'kode_produk'=>$rowdata->kode_produk,
                            'qty'=>$rowdata->qty,
                            'partnumber'=>$rowdata->partnumber,
                            'harga'=>$rowdata->hpp,
                        );          
                    }
                }

                if(!empty($assemblingdetail)){
                    $leng = count($assemblingdetail);

                    $i = 0;
                    for($i = 0; $i < $leng; $i++){
                        $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

                        if($tb_item_bulanan != null){
                            $produk_awal = $tb_item_bulanan->kode_produk;
                            
                            $stock_begin = $tb_item_bulanan->begin_stock;
                            $amount_begin = $tb_item_bulanan->begin_amount;

                            $stok_in = $tb_item_bulanan->in_stock;
                            $amount_masuk = $tb_item_bulanan->in_amount;

                            $stock_out = $tb_item_bulanan->out_stock;
                            $amount_keluar = $tb_item_bulanan->out_amount;

                            $stock_sale = $tb_item_bulanan->sale_stock;
                            $amount_sale = $tb_item_bulanan->sale_amount;  

                            $stock_trfin = $tb_item_bulanan->trf_in;
                            $amount_trfin = $tb_item_bulanan->trf_in_amount;

                            $stock_trfout = $tb_item_bulanan->trf_out;
                            $amount_trfout = $tb_item_bulanan->trf_out_amount;

                            $stock_op = $tb_item_bulanan->stock_opname;
                            $amount_op = $tb_item_bulanan->amount_opname;

                            $stok_ending_new = $tb_item_bulanan->ending_stock;
                            $amount_new = $tb_item_bulanan->ending_amount;

                            $stock_adjustment = $tb_item_bulanan->adjustment_stock;
                            $amount_adjustment = $tb_item_bulanan->adjustment_amount;

                            $retur_beli_stock = $tb_item_bulanan->retur_beli_stock;
                            $retur_beli_amount = $tb_item_bulanan->retur_beli_amount;

                            $retur_jual_stock = $tb_item_bulanan->retur_jual_stock;
                            $retur_jual_amount = $tb_item_bulanan->retur_jual_amount;

                            $disassembling_stock = $tb_item_bulanan->disassembling_stock;
                            $disassembling_amount = $tb_item_bulanan->disassembling_amount;

                            $assembling_stock = $tb_item_bulanan->assembling_stock;
                            $assembling_amount = $tb_item_bulanan->assembling_amount;
                            
                            $rpk_stock = $tb_item_bulanan->retur_pakai_stock;
                            $rpk_amount = $tb_item_bulanan->retur_pakai_amount;

                            $produk = Produk::on($konek)->find($data[$i]['kode_produk']);

                            $assemblingdetail2 = AssemblingDetail::on($konek)->where('no_ass', $id)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                            $harga = $data[$i]['harga'];
                            $qty_baru = $data[$i]['qty'];

                            $dis_stok_new = $disassembling_stock - $qty_baru;
                            $dis_amount_new = $disassembling_amount - ($harga * $qty_baru);

                            $end_stok_new = $stock_begin + $stok_in - $stock_out - $stock_sale + $stock_trfin - $stock_trfout + $stock_op + $stock_adjustment - $retur_beli_stock + $retur_jual_stock - $dis_stok_new + $assembling_stock + $rpk_stock;
                            $end_amount_new = $amount_begin + $amount_masuk - $amount_keluar - $amount_sale + $amount_trfin - $amount_trfout + $amount_op + $amount_adjustment - $retur_beli_amount + $retur_jual_amount - $dis_amount_new + $assembling_amount + $rpk_amount;

                            if($end_stok_new != 0){
                                $hpp = $end_amount_new / $end_stok_new;
                            }else{
                                $hpp = $tb_item_bulanan->hpp;
                                $end_amount_new = 0;
                            }

                            $tabel_baru = [
                                'disassembling_stock'=>$dis_stok_new,
                                'disassembling_amount'=>$dis_amount_new,
                                'ending_stock'=>$end_stok_new,
                                'ending_amount'=>$end_amount_new,
                                'hpp'=>$hpp,
                            ];

                            $update_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$produk_awal)->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($tabel_baru);

                            $update_produk_history = tb_produk_history::on($konek)->where('no_transaksi','Assembling, id : '.$id)->delete();

                            $tgl_ass1 = $assembling->tanggal;
                            $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_ass1)->year;
                            $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_ass1)->month;

                            $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                            $status_reopen = $reopen->reopen_status;

                            if($status_reopen == 'true'){
                                $tgl_ass = $assembling->tanggal;
                                $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_ass)->year;
                                $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_ass)->month;

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
                                    $assemblingdetail = AssemblingDetail::on($konek)->where('no_ass', $id)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                                    $hpp = $data[$i]['harga'];
                                    $stock_dis = $data[$i]['qty'];
                                    $amount_dis = $hpp*$stock_dis;

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

                                    $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi',$koneksi)->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->first();
                                    // dd($tb_item_bulanan2);

                                    if($tb_item_bulanan2 != null){
                                        $bs = $tb_item_bulanan2->begin_stock;
                                        $ba = $tb_item_bulanan2->begin_amount;
                                        $es = $tb_item_bulanan2->ending_stock;
                                        $ea = $tb_item_bulanan2->ending_amount;

                                        $begin_stock1 = $bs + $stock_dis;
                                        $begin_amount1 = $ba + $amount_dis;

                                        $end_stok1 = $es + $stock_dis;
                                        $end_amount1 = $ea + $amount_dis;
                                        
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

                                        $update_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi',$koneksi)->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->update($tabel_baru2);
                                    }

                                    $j++;
                                }
                            }
                        }
                    }
                }

                $assembling = Assembling::on($konek)->find(request()->id);
                $assembling->status = "OPEN";
                $assembling->save(); 

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Unpost Assembling, id : '.$id.'.','created_by'=>$nama,'updated_by'=>$nama];
                             //dd($tmp);
                user_history::on($konek)->create($tmp);

                $cek_company = Auth()->user()->kode_company;
                if($cek_company == '04' || $cek_company == '0401' || $cek_company == '03'){
                    $konek2 = self::konek2();

                    $get_ledger = Ledger::on($konek2)->where('no_journal',$assembling->no_journal)->get();

                    $data = array();

                    foreach ($get_ledger as $rowdata){

                        $account = $rowdata->account;
                        $db_cr = $rowdata->db_cr;
                        $debit = $rowdata->debit;
                        $kredit = $rowdata->kredit;
                                
                        $data[] = array(
                            'account'=>$account,
                            'db_cr'=>$db_cr,
                            'debit'=>$debit,
                            'kredit'=>$kredit,
                        );
                    }

                    $leng = count($get_ledger);

                    $i = 0;

                    for($i = 0; $i < $leng; $i++){
                        if($data[$i]['db_cr'] == 'D'){
                            $account = $data[$i]['account'];
                            $harga = $data[$i]['debit'];

                            $type = 'Inventory';
                            $transaksi = $assembling;
                            $tgl_trans = $assembling->tanggal;
                            $update_accbalance = $this->accbalance_debit_unpost($account, $harga, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                            $cek_acc = Coa::on('mysql4')->where('account',$account)->first();

                            //CEK SETELAH
                            $j = $bulan;
                            $cek_setelah = AccBalance::on($konek2)->where('account',$account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($j + 1))->whereYear('periode', $tahun)->first();
                            if ($cek_setelah != null) {
                                for($j = $bulan; $j <= 12; $j++){
                                    $cek_setelah = AccBalance::on($konek2)->where('account',$account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($j + 1))->whereYear('periode', $tahun)->first();
                                    if ($cek_setelah != null) {
                                        $debit = $cek_setelah->debet;
                                        $kredit = $cek_setelah->kredit;
                                        if($cek_acc->normal_balance == 'D'){
                                            $begin = $cek_setelah->beginning_balance - $harga;
                                            $ending_balance = $begin + $debit - $kredit;
                                        }else{
                                            $begin = $cek_setelah->beginning_balance + $harga;
                                            $ending_balance = $begin - $debit + $kredit;
                                        }
                                         
                                        $tabel_baru = [
                                            'beginning_balance'=>$begin,
                                            'ending_balance'=>$ending_balance,
                                        ];
                                        
                                        $update_balance = AccBalance::on($konek2)->where('account',$account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($j + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                    }
                                }
                            }
                        }else{
                            $account = $data[$i]['account'];
                            $harga = $data[$i]['kredit'];

                            $type = 'Inventory';
                            $transaksi = $assembling;
                            $tgl_trans = $assembling->tanggal;
                            $update_accbalance = $this->accbalance_kredit_unpost($account, $harga, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                            $cek_acc = Coa::on('mysql4')->where('account',$account)->first();

                            //CEK SETELAH
                            $j = $bulan;
                            $cek_setelah = AccBalance::on($konek2)->where('account',$account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($j + 1))->whereYear('periode', $tahun)->first();
                            if ($cek_setelah != null) {
                                for($j = $bulan; $j <= 12; $j++){
                                    $cek_setelah = AccBalance::on($konek2)->where('account',$account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($j + 1))->whereYear('periode', $tahun)->first();
                                    if ($cek_setelah != null) {
                                        $debit = $cek_setelah->debet;
                                        $kredit = $cek_setelah->kredit;
                                        if($cek_acc->normal_balance == 'D'){
                                            $begin = $cek_setelah->beginning_balance + $harga;
                                            $ending_balance = $begin + $debit - $kredit;
                                        }else{
                                            $begin = $cek_setelah->beginning_balance - $harga;
                                            $ending_balance = $begin - $debit + $kredit;
                                        }
                                         
                                        $tabel_baru = [
                                            'beginning_balance'=>$begin,
                                            'ending_balance'=>$ending_balance,
                                        ];
                                            
                                        $update_balance = AccBalance::on($konek2)->where('account',$account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($j + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                    }
                                }
                            }
                        }
                    }

                    $update_ledger = Ledger::on($konek2)->where('no_journal',$assembling->no_journal)->delete();
                }
            
                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data berhasil di UNPOST.'
                    ];

                return response()->json($message);
            }
            else{
                     $message = [
                        'success' => false,
                        'title' => 'Update',
                        'message' => 'Re-Open [Bulan '.$bulan.'; Tahun '.$tahun.'].'
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
        $tgl = $request->tanggal;
        $validate = $this->periodeChecker($tgl);
        
        if($validate == true){
            $cekopname = Opname::on($konek)->where('kode_lokasi', auth()->user()->kode_lokasi)->where('status','OPEN')->first();
            if ($cekopname != null){
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'Sedang Opname/Ada Transaksi Opname status: OPEN.',
                ];
                return response()->json($message);
            }
            
            $assembling = Assembling::on($konek)->create($request->all());

            $no = Assembling::on($konek)->orderBy('created_at','desc')->first();
            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Simpan No. Assembling: '.$no->no_ass.'.','created_by'=>$nama,'updated_by'=>$nama];
            //dd($tmp);
            user_history::on($konek)->create($tmp);

            $message = [
                'success' => true,
                'title' => 'Simpan',
                'message' => 'Data telah di Disimpan.',
            ];
            return response()->json($message);
        }else{
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => '<b>Periode</b> ['.$tgl.'] <b>Telah Ditutup / Belum Dibuka</b>'
            ];
            return response()->json($message);
        }
    }

    
    public function edit_assembling()
    {
        $konek = self::konek();
        $id = request()->id;
        $data = Assembling::on($konek)->find($id);
        $status = $data->status;
        $output = array(
            'id'=> $data->no_ass,
            'tanggal'=> $data->tanggal,
            'kode_produk'=> $data->kode_produk,
            'partnumber'=> $data->partnumber,
            'hpp'=> $data->hpp,
            'qty_assembling'=> $data->qty_assembling,
            'keterangan'=> $data->keterangan,
            'update_stok'=>$data->update_stok,
        );
        return response()->json($output);   
    }

    public function updateAjax(Request $request)
    {
        $konek = self::konek();
        $tgl = $request->tanggal;
        $jumlah = $request->jumlah;
        $validate = $this->periodeChecker($tgl);
        
        if($validate == true){
            $assembling = Assembling::on($konek)->find($request->no_ass)->update($request->all());

            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Edit No. Assembling: '.$request->no_ass.'.','created_by'=>$nama,'updated_by'=>$nama];
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
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Re-Open Periode: '.$tgl,
            ];
            return response()->json($message);
        }
    }

    public function hapus_assembling()
    {   
        $konek = self::konek();
        $level = auth()->user()->level;

        $id = request()->id;
        $data = Assembling::on($konek)->find($id);
        $tgl = $data->tanggal;

        $validate = $this->periodeChecker($tgl);
        
        if($validate == true){
            $data->delete();

            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Hapus No. Assembling: '.$id.'.','created_by'=>$nama,'updated_by'=>$nama];

            user_history::on($konek)->create($tmp);

            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data ['.$data->no_ass.'] telah dihapus.'
            ];
            return response()->json($message);
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