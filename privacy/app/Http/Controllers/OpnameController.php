<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Opname;
use App\Models\OpnameDetail;
use App\Models\Memo;
use App\Models\MemoDetail;
use App\Models\Vendor;
use App\Models\Produk;
use App\Models\satuan;
use App\Models\Company;
use App\Models\tb_akhir_bulan;
use App\Models\tb_item_bulanan;
use App\Models\tb_produk_history;
use App\Exports\OpnameExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\user_history;
use App\Models\MasterLokasi;
use App\Models\KategoriProduk;
use App\Models\Ledger;
use App\Models\Coa;
use App\Models\AccBalance;
use App\Models\Tb_acc_history;
use App\Models\Jurnal;
use App\Models\Labarugiberjalan;
use App\Models\SetupFolder;
use Illuminate\Support\Facades\Storage;
use PDF;
use DB;
use Alert;
use Carbon;
use DateTime;

class OpnameController extends Controller
{
    public function index()
    {
        $konek = self::konek();
        $create_url = route('opname.create');
        $Company= Company::pluck('nama_company','kode_company');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;
        
        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        $level = auth()->user()->level;
        return view('admin.opname.index',compact('create_url','Company','period', 'nama_lokasi','nama_company'));
    
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
        }else if ($compa == '06'){
            $koneksi = 'mysql_finance_inf';
        }
        return $koneksi;
    }

    public function anyData()
    {
        $konek = self::konek();
        return Datatables::of(Opname::on($konek)->with('company')->withCount('opnamedetail')->orderBy('created_at','desc')->where('kode_lokasi', auth()->user()->kode_lokasi))->make(true);
    }

    public function detail($opname)
    {
        $konek = self::konek();
        $opname = Opname::on($konek)->find($opname);
        $tgl = $opname->tanggal_opname;

        $validate = $this->periodeChecker($tgl);

        if($validate == true){
            $no_opname = $opname->no_opname;
            $data = Opname::on($konek)->find($no_opname);

            $total_qty = 0;
            $total_harga = 0;
            $grand_total = 0;

            $opnamedetail = OpnameDetail::on($konek)->with('produk','satuan')->where('no_opname', $opname->no_opname)->orderBy('created_at','desc')->get();

            foreach ($opnamedetail as $row){
                $total_qty += $row->qty;
                $subtotal = $row->harga * $row->qty;
                $total_harga += $subtotal;
                $grand_total = number_format($total_harga,2,",",".");
            }

            $list_url= route('opname.index');
            $Produk = Produk::on($konek)->pluck('nama_produk','id');
            $Satuan = satuan::pluck('nama_satuan','kode_satuan');

            $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
            $tgl_jalan2 = $tgl_jalan->periode;
            $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
            $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
            $nama_lokasi = $get_lokasi->nama_lokasi;

            $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
            $nama_company = $get_company->nama_company;

            return view('admin.opnamedetail.index', compact('opname','opnamedetail','list_url','Produk','Satuan',
                'total_qty','grand_total','period', 'nama_lokasi','nama_company'));
        }
        else{
            alert()->success('Status POSTED / Periode Telah CLOSED: '.$tgl,'GAGAL!')->persistent('Close');
            return redirect()->back();
        }   
    }

    public function historia()
    {
        $konek = self::konek();
        $post = user_history::on($konek)->where('aksi', 'like', '%'.request()->id.'%')->where('aksi','like','Post No.%')->orderBy('created_at','desc')->first();
        if ($post != null) {
            $nama1 = $post->nama;
        }else {
            $nama1 = 'None';
        }

        $unpost = user_history::on($konek)->where('aksi', 'like', '%'.request()->id.'%')->where('aksi','like','Unpost No.%')->orderBy('created_at','desc')->first();
        if ($unpost != null) {
            $nama2 = $unpost->nama;
        }else {
            $nama2 = 'None';
        }

        $output = array(
            'post'=>$nama1,
            'unpost'=>$nama2,
        );
        return response()->json($output);
    }

    public function Showdetail()
    {   
        $ending_stock = 0;
        $konek = self::konek();
        $opnamedetail= OpnameDetail::on($konek)->with('produk','satuan')->where('no_opname',request()->id)
        ->orderBy('created_at', 'desc')->get();
        $output = array();

        foreach ($opnamedetail as $row){
            $ending_stock = $row->stok * $row->stock_opname;
        }

        if($opnamedetail){
            foreach($opnamedetail as $row)
            {
                $output[] = array(
                    'no_opname'=>$row->no_opname,
                    'produk'=>$row->produk->nama_produk,
                    'partnumber'=>$row->partnumber,
                    'satuan'=>$row->satuan->nama_satuan,
                    'stok'=>$row->stok,
                    'hpp'=>$row->hpp,
                    'stock_opname'=>$row->stock_opname,
                    'amount_opname'=>$row->amount_opname,
                    'qty_checker1'=>$row->qty_checker1,
                    'qty_checker2'=>$row->qty_checker2,
                    'qty_checker3'=>$row->qty_checker3,

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

    public function exportPDF(){
        $request = $_GET['no_opname'];
        $konek = self::konek();
        $opname = Opname::on($konek)->where('no_opname',$request)->first();
        $status = $opname->status;
        $no_opname = $opname->no_opname;

        $kode_company = $opname->kode_company;

        $opnamedetail = OpnameDetail::on($konek)->where('no_opname',$request)->get();
        $company = Company::where('kode_company',$kode_company)->first();
        
        $ttd = auth()->user()->name;
        $get_lokasi = auth()->user()->kode_lokasi;
        $get_company = auth()->user()->kode_company;

        $nama_lokasi = MasterLokasi::find($get_lokasi);
        $nama = $nama_lokasi->nama_lokasi;

        $nama_company = Company::find($get_company);
        $nama2 = $nama_company->nama_company;

        $dt = Carbon\Carbon::now();
        $date_now = Carbon\Carbon::parse($dt)->format('d/m/Y H:i:s');

        $tgl = $opname->tanggal_opname;
        $date=date_create($tgl);
        $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
        $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
        
        $setupfolder = SetupFolder::find(22);
        $tes_save = $company->kode_company.". ".$company->nama_company."/".$setupfolder->folder."/".$setupfolder->subfolder."/".$tahun."/".$bulan."/".$no_opname.".pdf";
        
        $nama = auth()->user()->name;
        $tmp = ['nama' => $nama,'aksi' => 'Print No. Opname : '.$no_opname.'.','created_by'=>$nama,'updated_by'=>$nama];
        user_history::on($konek)->create($tmp);
        
        $pdf = PDF::loadView('/admin/opname/pdfop', compact('opnamedetail','status','request','tgl', 'no_opname','nama_company','date_now','opname','nama','nama2','dt','ttd'));
        $pdf->setPaper([0, 0, 684, 792], 'potrait');

        Storage::disk('ftp')->put($tes_save, $pdf->output());
        return $pdf->stream($no_opname.'.pdf');        
    }

    public function exportPDF3(){
        $konek = self::konek();
        $konek2 = self::konek2();
        $request = $_GET['no_pembelian'];
        $no_journal = $_GET['no_journal'];

        $opname = Opname::on($konek)->find($request);
        $jur = $opname->kode_jurnal;
        $jurnal = Jurnal::find($jur);

        $total_qty = 0;
        $total_harga = 0;
        $grand_total = 0;
        $detail = OpnameDetail::on($konek)->where('no_opname',$request)->get();
        foreach ($detail as $row){
            $total_harga += $row->amount_opname;
            $grand_total = $total_harga;
        }
    
        $ledger2 = Ledger::on($konek2)->with('coa')->where('no_journal',$no_journal)->first();

        $ledger = Ledger::on($konek2)->select('ledger.*','coa.ac_description')->join('u5611458_gui_general_ledger_laravel.coa','u5611458_gui_general_ledger_laravel.coa.account','=','ledger.account')->where('no_journal', $no_journal)->get();

        $user = $opname->created_by;
        $tgl = $opname->tanggal_pemakaian;
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

        $pdf = PDF::loadView('/admin/opname/pdf2', compact('opname','request', 'jurnal','tgl','date', 'ttd','date_now','ledger','ledger2','dt','user','nama','nama2','journal_date','grand_total'));
        $pdf->setPaper([0, 0, 684, 792], 'potrait');
        return $pdf->stream('Cetak Zoom Jurnal '.$request.'.pdf');
    }

    public function lrb_post($coa, $harga, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr)
    {
        $konek = self::konek2();
        //UPDATE LABA RUGI BERJALAN
        if($coa->account_type == '5' || $coa->account_type == '4' || $coa->account_type == '6'){
            $cek_lrb = Labarugiberjalan::on($konek)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
            if($cek_lrb != null){
                $begin_awal = $cek_lrb->beginning_balance;
                $debit_awal = $cek_lrb->debit;
                $kredit_awal = $cek_lrb->kredit;

                if($dbkr == 'D'){
                    $debit_akhir = $debit_awal + $harga;
                    $kredit_akhir = $kredit_awal;
                    $end = $begin_awal - $debit_akhir + $kredit_awal;
                }else{
                    $debit_akhir = $debit_awal;
                    $kredit_akhir = $kredit_awal + $harga;
                    $end = $begin_awal - $debit_awal + $kredit_akhir;
                }
                       
                $update_saldo = [
                    'debit'=>$debit_akhir,
                    'kredit'=>$kredit_akhir,
                    'ending_balance'=>$end,
                ];

                $update = Labarugiberjalan::on($konek)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($update_saldo);

                //CEK SETELAH
                $i = $bulan;
                $cek_setelah = Labarugiberjalan::on($konek)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                if ($cek_setelah != null) {
                    for($i = $bulan; $i <= 12; $i++){
                        $cek_setelah = Labarugiberjalan::on($konek)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                        if ($cek_setelah != null) {
                            if($dbkr == 'D'){
                                $begin = $cek_setelah->beginning_balance - $harga;
                            }else{
                                $begin = $cek_setelah->beginning_balance + $harga;
                            }
                            $debit = $cek_setelah->debit;
                            $kredit = $cek_setelah->kredit;
                            $ending_balance = $begin - $debit + $kredit;

                            $tabel_baru = [
                                'beginning_balance'=>$begin,
                                'ending_balance'=>$ending_balance,
                            ];

                            $update_balance = Labarugiberjalan::on($konek)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                        }
                    }
                }


                $cek_coa = Coa::on('mysql4')->where('account','3.2.00.000.00.002')->first();
                if($coa->normal_balance == 'D'){
                    $cek_balance = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
        
                    $begin = $cek_balance->beginning_balance;
                    $debit_awal = $cek_balance->debet;
                    $kredit = $cek_balance->kredit;

                    $debit_akhir = $debit_awal + $harga;
                    $ending_balance = $begin - $debit_akhir + $kredit;

                    $update_acc = [
                        'debet'=>$debit_akhir,
                        'ending_balance'=>$ending_balance,
                    ];

                    $update_balance = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($update_acc);

                    //CEK SETELAH
                    $i = $bulan;
                    $cek_setelah = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                    if ($cek_setelah != null) {
                        for($i = $bulan; $i <= 12; $i++){
                            $cek_setelah = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                            if ($cek_setelah != null) {
                                if($coa->normal_balance == 'D'){
                                    $begin = $cek_setelah->beginning_balance - $harga;
                                }else{
                                    $begin = $cek_setelah->beginning_balance + $harga;
                                }
                                $debit = $cek_setelah->debet;
                                $kredit = $cek_setelah->kredit;
                                $ending_balance = $begin - $debit + $kredit;

                                $tabel_baru = [
                                    'beginning_balance'=>$begin,
                                    'ending_balance'=>$ending_balance,
                                ];

                                $update_balance = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                            }
                        }
                    }
                }else{
                    $cek_balance = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
        
                    $begin = $cek_balance->beginning_balance;
                    $debit = $cek_balance->debet;
                    $kredit_awal = $cek_balance->kredit;

                    $kredit_akhir = $kredit_awal + $harga;
                    $ending_balance = $begin - $debit + $kredit_akhir;

                    $update_acc = [
                        'kredit'=>$kredit_akhir,
                        'ending_balance'=>$ending_balance,
                    ];

                    $update_balance = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($update_acc);

                    //CEK SETELAH
                    $i = $bulan;
                    $cek_setelah = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                    if ($cek_setelah != null) {
                        for($i = $bulan; $i <= 12; $i++){
                            $cek_setelah = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                            if ($cek_setelah != null) {
                                if($coa->normal_balance == 'D'){
                                    $begin = $cek_setelah->beginning_balance - $harga;
                                }else{
                                    $begin = $cek_setelah->beginning_balance + $harga;
                                }
                                $debit = $cek_setelah->debet;
                                $kredit = $cek_setelah->kredit;
                                $ending_balance = $begin - $debit + $kredit;

                                $tabel_baru = [
                                    'beginning_balance'=>$begin,
                                    'ending_balance'=>$ending_balance,
                                ];

                                $update_balance = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                            }
                        }
                    }
                }
                
            }else{
                //CEK SEBELUM
                $cek_sebelum = Labarugiberjalan::on($konek)->whereMonth('periode', ($bulan-1))->whereYear('periode', $tahun)->first();
                if($cek_sebelum != null){
                    $ending_sebelum = $cek_sebelum->ending_balance;
                }else{
                    $ending_sebelum = 0;
                }

                $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
                $tgl_jalan2 = $tgl_jalan->periode;

                if($dbkr == 'D'){
                    $debit_akhir = $harga;
                    $kredit_akhir = 0;
                    $end = $ending_sebelum - $debit_akhir;
                }else{
                    $debit_akhir = 0;
                    $kredit_akhir = $harga;
                    $end = $ending_sebelum + $kredit_akhir;
                }

                $update_saldo = [
                    'periode'=>$tanggal_baru,
                    'beginning_balance'=>$ending_sebelum,
                    'debit'=>$debit_akhir,
                    'kredit'=>$kredit_akhir,
                    'ending_balance'=>$end,
                    'kode_lokasi'=>$lokasi,
                ];

                $update = Labarugiberjalan::on($konek)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_saldo);


                $cek_coa = Coa::on('mysql4')->where('account','3.2.00.000.00.002')->first();
                if($coa->normal_balance == 'D'){
                    $cek_balance = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                    if ($cek_balance == null) {
                        $update_acc = [
                            'periode'=>$tanggal_baru,
                            'fiscalyear'=>$tahun,
                            'account'=>$cek_coa->account,
                            'beginning_balance'=>0,
                            'debet'=>0 - $harga,
                            'kredit'=>0,
                            'ending_balance'=>$harga,
                            'kode_lokasi'=>$lokasi,
                        ];

                        $create_balance = AccBalance::on($konek)->create($update_acc);
                    }
                }else{
                    $cek_balance = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                    if ($cek_balance == null) {
                        $update_acc = [
                            'periode'=>$tanggal_baru,
                            'fiscalyear'=>$tahun,
                            'account'=>$cek_coa->account,
                            'beginning_balance'=>0,
                            'debet'=>0,
                            'kredit'=>$harga,
                            'ending_balance'=>$harga,
                            'kode_lokasi'=>$lokasi,
                        ];

                        $create_balance = AccBalance::on($konek)->create($update_acc);
                    }
                }
            }
        }
    }

    public function lrb_unpost($coa, $harga, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $dbkr)
    {
        $konek = self::konek2();
        //UPDATE LABA RUGI BERJALAN
        if($coa->account_type == '5' || $coa->account_type == '4' || $coa->account_type == '6'){
            $cek_lrb = Labarugiberjalan::on($konek)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                        
            $begin_awal = $cek_lrb->beginning_balance;
            $debit_awal = $cek_lrb->debit;
            $kredit_awal = $cek_lrb->kredit;

            if($dbkr == 'D'){
                $debit_akhir = $debit_awal - $harga;
                $kredit_akhir = $kredit_awal;
                $end = $begin_awal - $debit_akhir + $kredit_awal;
            }else{
                $debit_akhir = $debit_awal;
                $kredit_akhir = $kredit_awal - $harga;
                $end = $begin_awal - $debit_awal + $kredit_akhir;
            }
                            
            $update_saldo = [
                'debit'=>$debit_akhir,
                'kredit'=>$kredit_akhir,
                'ending_balance'=>$end,
            ];

            $update = Labarugiberjalan::on($konek)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($update_saldo);

            //CEK SETELAH
            $i = $bulan;
            $cek_setelah = Labarugiberjalan::on($konek)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
            if ($cek_setelah != null) {
                for($i = $bulan; $i <= 12; $i++){
                    $cek_setelah = Labarugiberjalan::on($konek)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                    if ($cek_setelah != null) {
                        if($dbkr == 'D'){
                            $begin = $cek_setelah->beginning_balance + $harga;
                        }else{
                            $begin = $cek_setelah->beginning_balance - $harga;
                        }
                        $debit = $cek_setelah->debit;
                        $kredit = $cek_setelah->kredit;
                        $ending_balance = $begin - $debit + $kredit;

                        $tabel_baru = [
                            'beginning_balance'=>$begin,
                            'ending_balance'=>$ending_balance,
                        ];

                        $update_balance = Labarugiberjalan::on($konek)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                    }
                }
            }


            $cek_coa = Coa::on('mysql4')->where('account','3.2.00.000.00.002')->first();
            if($coa->normal_balance == 'D'){
                $cek_balance = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                if($cek_balance != null){
                    $begin = $cek_balance->beginning_balance;
                    $debit_awal = $cek_balance->debet;
                    $kredit = $cek_balance->kredit;

                    $debit_akhir = $debit_awal - $harga;
                    $ending_balance = $begin - $debit_akhir + $kredit;

                    $update_acc = [
                        'debet'=>$debit_akhir,
                        'ending_balance'=>$ending_balance,
                    ];

                    $update_balance = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($update_acc);
                }

                //CEK SETELAH
                $i = $bulan;
                $cek_setelah = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                if ($cek_setelah != null) {
                    for($i = $bulan; $i <= 12; $i++){
                        $cek_setelah = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                        if ($cek_setelah != null) {
                            if($coa->normal_balance == 'D'){
                                $begin = $cek_setelah->beginning_balance + $harga;
                            }else{
                                $begin = $cek_setelah->beginning_balance - $harga;
                            }
                            $debit = $cek_setelah->debet;
                            $kredit = $cek_setelah->kredit;
                            $ending_balance = $begin - $debit + $kredit;

                            $tabel_baru = [
                                'beginning_balance'=>$begin,
                                'ending_balance'=>$ending_balance,
                            ];

                            $update_balance = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                        }
                    }
                }
            }else{
                $cek_balance = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                if($cek_balance != null){
                    $begin = $cek_balance->beginning_balance;
                    $debit = $cek_balance->debet;
                    $kredit_awal = $cek_balance->kredit;

                    $kredit_akhir = $kredit_awal - $harga;
                    $ending_balance = $begin - $debit + $kredit_akhir;

                    $update_acc = [
                        'kredit'=>$kredit_akhir,
                        'ending_balance'=>$ending_balance,
                    ];

                    $update_balance = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($update_acc);
                }

                //CEK SETELAH
                $i = $bulan;
                $cek_setelah = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                if ($cek_setelah != null) {
                    for($i = $bulan; $i <= 12; $i++){
                        $cek_setelah = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                        if ($cek_setelah != null) {
                            if($coa->normal_balance == 'D'){
                                $begin = $cek_setelah->beginning_balance + $harga;
                            }else{
                                $begin = $cek_setelah->beginning_balance - $harga;
                            }
                            $debit = $cek_setelah->debet;
                            $kredit = $cek_setelah->kredit;
                            $ending_balance = $begin - $debit + $kredit;

                            $tabel_baru = [
                                'beginning_balance'=>$begin,
                                'ending_balance'=>$ending_balance,
                            ];

                            $update_balance = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                        }
                    }
                }
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

    function periodeChecker($tgl)
    {   
        $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
        $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
        $konek = self::konek();
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
        $cek_ar = Opname::on($konek)->where('no_journal', request()->no_journal)->first();

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

        if($cek_bulan == null || $level == 'superadministrator' || $level == 'user_rince' || $level == 'user_thomas'){
            if($cek_company == '04' || $cek_company == '0401' || $cek_company == '03' || $cek_company == '05' || $cek_company == '06' || $cek_company == '02'){
                
                $cekduplikat = OpnameDetail::on($konek)->where('no_opname', request()->id)->groupBy('kode_produk')->havingRaw('COUNT(kode_produk) > 1')->first();
                if ($cekduplikat != null){
                    $message = [
                        'success' => false,
                        'title' => 'Simpan',
                        'message' => 'Kode Produk: ['.$cekduplikat->kode_produk.'] memiliki duplikat.',
                    ];
                    return response()->json($message);
                }
                
                $opnamedetail = OpnameDetail::on($konek)->where('no_opname', request()->id)->get();
                $leng = count($opnamedetail);
                $kat1 = 0;

                foreach ($opnamedetail as $rowdata){
                    $cek_produk = Produk::on($konek)->find($rowdata->kode_produk);
                    // $cek_produkkategori = tb_item_bulanan::on($konek)->where('kode_produk',$rowdata->kode_produk)->where('kode_lokasi',auth()->user()->kode_lokasi)->orderBy('periode','desc')->first();
                    
                    if ($cek_produk->tipe_produk != 'Serial'){
                        if ($rowdata->partnumber != $cek_produk->partnumber) {
                            $message = [
                                'success' => false,
                                'title' => 'Simpan',
                                'message' => 'Kode Produk: '.$cek_produk->id.' - '.$cek_produk->nama_produk.' partnumber berbeda dengan tabel MASTER PRODUK.',
                            ];
                            return response()->json($message);
                        }
                    }
                    
                    
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

            $opname = Opname::on($konek)->find(request()->id);
            $cek_status = $opname->status;
            if($cek_status != 'OPEN'){  
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'POST No. Opname: '.$opname->no_opname.' sudah dilakukan! Pastikan Anda tidak membuka menu OPNAME lebih dari 1',
                ];
                return response()->json($message);
            }

            $crate_opname = $opname->created_at;

            $tgl = $opname->tanggal_opname;
            $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
            $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
            $tanggal = '01';

            $tanggal_baru = Carbon\Carbon::createFromDate($tahun, $bulan, $tanggal)->toDateString();
            $lokasi = $opname->kode_lokasi;
            $validate = $this->periodeChecker($tgl);

            if($validate == true){
                $opnamedetail = OpnameDetail::on($konek)->where('no_opname', request()->id)->get();
                $no_opname = request()->id;

                foreach ($opnamedetail as $rowdata){
                    $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$rowdata->kode_produk)->where('kode_lokasi',$lokasi)->where('partnumber',$rowdata->partnumber)->where('periode', $tanggal_baru)->first();

                    if($tb_item_bulanan != null) {
                            $produk_awal = $tb_item_bulanan->kode_produk;

                            $stock_begin = $tb_item_bulanan->begin_stock;
                            $amount_begin = $tb_item_bulanan->begin_amount;

                            $stok_in = $tb_item_bulanan->in_stock;

                            $amount_masuk = $tb_item_bulanan->in_amount;
                            $amount_keluar = $tb_item_bulanan->out_amount;
                            $amount_sale = $tb_item_bulanan->sale_amount;

                            $stock_out = $tb_item_bulanan->out_stock;
                            $stock_sale = $tb_item_bulanan->sale_stock;

                            $stock_trfin = $tb_item_bulanan->trf_in;
                            $amount_trfin = $tb_item_bulanan->trf_in_amount;

                            $stock_trfout = $tb_item_bulanan->trf_out;
                            $amount_trfout = $tb_item_bulanan->trf_out_amount;

                            $op_stock = $tb_item_bulanan->stock_opname;
                            $op_amount = $tb_item_bulanan->amount_opname;

                            $stock_a = $tb_item_bulanan->adjustment_stock;
                            $amount_a = $tb_item_bulanan->adjustment_amount;

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

                            $produk = Produk::on($konek)->find($rowdata->kode_produk);

                            $opnamedetail2 = OpnameDetail::on($konek)->where('no_opname', $no_opname)->where('kode_produk',$rowdata->kode_produk)->where('partnumber',$rowdata->partnumber)->first();

                            $stock_op = $opnamedetail2->stock_opname;
                            $amount_op = $opnamedetail2->amount_opname;
                            $hpp = $opnamedetail2->hpp;

                            $waktu = $tgl;
                            $barang = $rowdata->kode_produk;
                            $so = $op_stock + $stock_op;
                            $ao = $op_amount + $amount_op;
                            $end_stok = $stock_begin + $stok_in - $stock_out - $stock_sale + $stock_trfin - $stock_trfout + $stock_a + $so - $retur_beli_stock + $retur_jual_stock - $disassembling_stock + $assembling_stock + $rpk_stock;
                            $end_amount = $amount_begin + $amount_masuk - $amount_keluar - $amount_sale + $amount_trfin - $amount_trfout + $amount_a + $ao - $retur_beli_amount + $retur_jual_amount - $disassembling_amount + $assembling_amount + $rpk_amount;

                            $partnumber = $rowdata->partnumber;
                            $no_mesin = $rowdata->no_mesin;

                            if($end_stok != 0){
                                $hpp2 = $end_amount / $end_stok;
                            }else{
                                $hpp2 = $tb_item_bulanan->hpp;
                            }

                            $tabel_baru = [
                                'periode'=>$tanggal_baru,
                                'partnumber'=>$partnumber,
                                'no_mesin'=>$no_mesin,
                                'stock_opname'=>$so,
                                'amount_opname'=>$ao,
                                'ending_stock'=>$end_stok,
                                'ending_amount'=>$end_amount,
                                'hpp'=>$hpp2,
                            ];

                            $update_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$rowdata->kode_produk)->where('partnumber',$rowdata->partnumber)->where('kode_lokasi',$lokasi)->where('periode', $tanggal_baru)->update($tabel_baru);

                            $tabel_history = [
                                'kode_produk'=>$barang,
                                'no_transaksi'=>$no_opname,
                                'tanggal_transaksi'=>$waktu,
                                'jam_transaksi'=>$crate_opname,
                                'qty_transaksi'=>$stock_op,
                                'harga_transaksi'=>$hpp,
                                'total_transaksi'=>$amount_op,
                                'kode_lokasi'=>$lokasi,
                            ];

                            $update_produk_history = tb_produk_history::on($konek)->create($tabel_history);

                            $tgl_op1 = $opname->tanggal_opname;
                            $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_op1)->year;
                            $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_op1)->month;

                            $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                            $status_reopen = $reopen->reopen_status;

                            if($status_reopen == 'true'){
                                $tgl_op = $opname->tanggal_opname;
                                $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_op)->year;
                                $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_op)->month;

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
                                    $opnamedetail1 = OpnameDetail::on($konek)->where('no_opname', $no_opname)->where('kode_produk',$rowdata->kode_produk)->where('partnumber',$rowdata->partnumber)->first();

                                    $hpp = $opnamedetail1->hpp;
                                    $qty_op = $opnamedetail1->stock_opname;
                                    $amount_op = $opnamedetail1->amount_opname;

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

                                    $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$rowdata->kode_produk)->where('partnumber',$rowdata->partnumber)->where('kode_lokasi',$lokasi)->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->first();

                                    if($tb_item_bulanan2 != null){
                                        $bs = $tb_item_bulanan2->begin_stock;
                                        $ba = $tb_item_bulanan2->begin_amount;
                                        $es = $tb_item_bulanan2->ending_stock;
                                        $ea = $tb_item_bulanan2->ending_amount;
                                        $partnumber = $rowdata->partnumber;
                                        $no_mesin = $rowdata->no_mesin;

                                        $begin_stock1 = $bs + $qty_op;
                                        $begin_amount1 = $ba + $amount_op;

                                        $end_stok1 = $es + $qty_op;
                                        $end_amount1 = $ea + $amount_op;

                                        if($end_stok1 != 0){
                                            $hpp = $end_amount1 / $end_stok1;
                                        }else{
                                            $hpp = $tb_item_bulanan2->hpp;
                                        }

                                        $tabel_baru2 = [
                                            'partnumber'=>$partnumber,
                                            'no_mesin'=>$no_mesin,
                                            'begin_stock'=>$begin_stock1,
                                            'begin_amount'=>$begin_amount1,
                                            'ending_stock'=>$end_stok1,
                                            'ending_amount'=>$end_amount1,
                                            'hpp'=>$hpp,
                                        ];
                                                // dd($tabel_baru2);

                                        $update_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$rowdata->kode_produk)->where('partnumber',$rowdata->partnumber)->where('kode_lokasi',$lokasi)->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->update($tabel_baru2);
                                    }

                                    $j++;
                                }
                            }
                        }else{
                            $opnamedetail2 = OpnameDetail::on($konek)->where('no_opname', $no_opname)->where('kode_produk',$rowdata->kode_produk)->where('partnumber',$rowdata->partnumber)->first();

                            $stock_op = $opnamedetail2->stock_opname;
                            $amount_op = $opnamedetail2->amount_opname;
                            $hpp = $opnamedetail2->hpp;

                            $part = Produk::on($konek)->find($rowdata->kode_produk);
                            $waktu = $tanggal_baru;
                            $barang = $rowdata->kode_produk;
                            $partnumber = $part->partnumber;
                            $no_mesin = $rowdata->no_mesin;
                            $end_stok = $stock_op;
                            $end_amount = $amount_op;

                            $tabel_baru = [
                                'periode'=>$waktu,
                                'kode_produk'=>$barang,
                                'partnumber'=>$partnumber,
                                'no_mesin'=>$no_mesin,
                                'begin_stock'=>0,
                                'begin_amount'=>0,
                                'in_stock'=>0,
                                'in_amount'=>0,
                                'out_stock'=>0,
                                'out_amount'=>0,
                                'adjustment_stock'=>0,
                                'adjustment_amount'=>0,
                                'trf_in'=>0,
                                'trf_in_amount'=>0,
                                'trf_out'=>0,
                                'trf_out_amount'=>0,
                                'sale_stock'=>0,
                                'sale_amount'=>0,
                                'stock_opname'=>$stock_op,
                                'amount_opname'=>$amount_op,
                                'retur_beli_stock'=>0,
                                'retur_beli_amount'=>0,
                                'retur_jual_stock'=>0,
                                'retur_jual_amount'=>0,
                                'retur_pakai_stock'=>0,
                                'retur_pakai_amount'=>0,
                                'disassembling_stock'=>0,
                                'disassembling_amount'=>0,
                                'assembling_stock'=>0,
                                'assembling_amount'=>0,
                                'ending_stock'=>$end_stok,
                                'ending_amount'=>$end_amount,
                                'hpp'=>$hpp,
                                'kode_lokasi'=>$lokasi,
                            ];
                                    // dd($tabel_baru);

                            $update_item_bulanan = tb_item_bulanan::on($konek)->create($tabel_baru);

                            $tabel_history = [
                                'kode_produk'=>$barang,
                                'no_transaksi'=>$no_opname,
                                'tanggal_transaksi'=>$waktu,
                                'jam_transaksi'=>$crate_opname,
                                'qty_transaksi'=>$stock_op,
                                'harga_transaksi'=>$hpp,
                                'total_transaksi'=>$hpp*$stock_op,
                                'kode_lokasi'=>$lokasi,
                            ];

                            $update_produk_history = tb_produk_history::on($konek)->create($tabel_history);

                            $tgl_op1 = $opname->tanggal_opname;
                            $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_op1)->year;
                            $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_op1)->month;

                            $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                            $status_reopen = $reopen->reopen_status;

                            if($status_reopen == 'true'){
                                $tgl_op = $opname->tanggal_opname;
                                $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_op)->year;
                                $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_op)->month;

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
                                    $opnamedetail1 = OpnameDetail::on($konek)->where('no_opname', $no_opname)->where('kode_produk',$rowdata->kode_produk)->where('partnumber',$rowdata->partnumber)->first();

                                    $hpp = $opnamedetail1->hpp;
                                    $qty_op = $opnamedetail1->stock_opname;
                                    $amount_op = $opnamedetail1->amount_opname;

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

                                    $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$rowdata->kode_produk)->where('partnumber',$rowdata->partnumber)->where('kode_lokasi',$lokasi)->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->first();

                                    if($tb_item_bulanan2 != null){
                                        $bs = $tb_item_bulanan2->begin_stock;
                                        $ba = $tb_item_bulanan2->begin_amount;
                                        $es = $tb_item_bulanan2->ending_stock;
                                        $ea = $tb_item_bulanan2->ending_amount;
                                        $partnumber = $rowdata->partnumber;
                                        $no_mesin = $rowdata->no_mesin;

                                        $begin_stock1 = $bs + $qty_op;
                                        $begin_amount1 = $ba + $amount_op;

                                        $end_stok1 = $es + $qty_op;
                                        $end_amount1 = $ea + $amount_op;

                                        if($end_stok1 != 0){
                                            $hpp = $end_amount1 / $end_stok1;
                                        }else{
                                            $hpp = $tb_item_bulanan2->hpp;
                                        }

                                        $tabel_baru2 = [
                                            'partnumber'=>$partnumber,
                                            'no_mesin'=>$no_mesin,
                                            'begin_stock'=>$begin_stock1,
                                            'begin_amount'=>$begin_amount1,
                                            'ending_stock'=>$end_stok1,
                                            'ending_amount'=>$end_amount1,
                                            'hpp'=>$hpp,
                                        ];
                                                // dd($tabel_baru2);

                                        $update_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$rowdata->kode_produk)->where('partnumber',$rowdata->partnumber)->where('kode_lokasi',$lokasi)->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->update($tabel_baru2);
                                    }else{
                                        $tanggal_buka = '01';
                                        $bulan_buka = $bulan2;
                                        $tahun_buka = $tahun2;

                                        $tanggal_baru2 = Carbon\Carbon::createFromDate($tahun_buka, $bulan_buka, $tanggal_buka)->toDateString();

                                        $waktu = $tanggal_baru2;
                                        $barang = $rowdata->kode_produk;
                                        $partnumber = $rowdata->partnumber;
                                        $no_mesin = $rowdata->no_mesin;
                                        $bs = $qty_op;
                                        $ba = $amount_op;
                                        $es = $qty_op;
                                        $ea = $amount_op;

                                        $tabel_baru2 = [
                                            'periode'=>$waktu,
                                            'kode_produk'=>$barang,
                                            'partnumber'=>$partnumber,
                                            'no_mesin'=>$no_mesin,
                                            'begin_stock'=>$bs,
                                            'begin_amount'=>$ba,
                                            'in_stock'=>0,
                                            'in_amount'=>0,
                                            'out_stock'=>0,
                                            'out_amount'=>0,
                                            'trf_in'=>0,
                                            'trf_in_amount'=>0,
                                            'trf_out'=>0,
                                            'trf_out_amount'=>0,
                                            'sale_stock'=>0,
                                            'sale_amount'=>0,
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
                                            'disassembling_stock'=>0,
                                            'disassembling_amount'=>0,
                                            'assembling_stock'=>0,
                                            'assembling_amount'=>0,
                                            'ending_stock'=>$es,
                                            'ending_amount'=>$ea,
                                            'hpp'=>$hpp,
                                            'kode_lokasi'=>$lokasi,
                                        ];

                                        $update_item_bulanan2 = tb_item_bulanan::on($konek)->create($tabel_baru2);
                                    }

                                    $j++;
                                }
                            }
                        }
                }

                $opname = Opname::on($konek)->find(request()->id);
                $opname->status = "POSTED";
                $opname->save(); 

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Post No. Opname: '.$no_opname.'.','created_by'=>$nama,'updated_by'=>$nama];
                //dd($tmp);
                user_history::on($konek)->create($tmp);

                    //UPDATE JURNAL
                    if($cek_company == '04' || $cek_company == '0401' || $cek_company == '03' || $cek_company == '05' || $cek_company == '06' || $cek_company == '02'){
                        $konek2 = self::konek2();
                        $cek_company = Auth()->user()->kode_company;

                        $total_qty = 0;
                        $total_harga = 0;
                        $grand_total = 0;
                        
                        $gt_apd = 0;
                        $gt_ban = 0;
                        $gt_bbm = 0;
                        $gt_oli = 0;
                        $gt_sprt = 0;
                        $gt_unit = 0;
                        $gt_sldg = 0;

                        $gt_apd_detail = 0;
                        $gt_ban_detail = 0;
                        $gt_bbm_detail = 0;
                        $gt_oli_detail = 0;
                        $gt_sprt_detail = 0;
                        $gt_unit_detail = 0;
                        $gt_sldg_detail = 0;
                        
                        $detail = OpnameDetail::on($konek)->where('no_opname',$opname->no_opname)->get();
                        foreach ($detail as $row){
                            $total_qty += $row->stock_opname;
                            $total_harga += $row->amount_opname;
                            $grand_total = $total_harga;
                            
                            $cek_produk = Produk::on($konek)->where('id', $row->kode_produk)->first();

                            $bulan = Carbon\Carbon::parse($opname->tanggal_opname)->format('m');
                            $tahun = Carbon\Carbon::parse($opname->tanggal_opname)->format('Y');

                            if($cek_produk->kode_kategori == 'APD'){
                                $gt_apd += $row->amount_opname;
                                $gt_apd_detail += $row->amount_opname;
                            }

                            if($cek_produk->kode_kategori == 'BAN'){
                                $gt_ban += $row->amount_opname;
                                $gt_ban_detail += $row->amount_opname;
                            }

                            if($cek_produk->kode_kategori == 'BBM'){
                                $gt_bbm += $row->amount_opname;
                                $gt_bbm_detail += $row->amount_opname;
                            }

                            if($cek_produk->kode_kategori == 'OLI'){
                                $gt_oli += $row->amount_opname;
                                $gt_oli_detail += $row->amount_opname;
                            }

                            if($cek_produk->kode_kategori == 'SPRT'){
                                $gt_sprt += $row->amount_opname;
                                $gt_sprt_detail += $row->amount_opname;
                            }

                            if($cek_produk->kode_kategori == 'UNIT'){
                                $gt_unit += $row->amount_opname;
                                $gt_unit_detail += $row->amount_opname;
                            }
                            
                            if($cek_produk->kode_kategori == 'SLDG'){
                                $gt_sldg += $row->amount_opname;
                                $gt_sldg_detail += $row->amount_opname;
                            }
                        }
                        
                        $lokasi = 'HO';

                        if($gt_apd != 0){
                            if ($cek_company == '04') {
                                $kategori = KategoriProduk::where('kode_kategori', 'APD')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_gut)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gut)->first();
                            }else if($cek_company == '0401'){
                                $kategori = KategoriProduk::where('kode_kategori', 'APD')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_gutjkt)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gutjkt)->first();
                            }else if($cek_company == '03'){
                                $kategori = KategoriProduk::where('kode_kategori', 'APD')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_emkl)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_emkl)->first();
                            }else if($cek_company == '02'){
                                $kategori = KategoriProduk::where('kode_kategori', 'APD')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_pbm)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_pbm)->first();
                            }else if($cek_company == '01'){
                                $kategori = KategoriProduk::where('kode_kategori', 'APD')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_depo)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_depo)->first();
                            }else if($cek_company == '05'){
                                $kategori = KategoriProduk::where('kode_kategori', 'APD')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_sub)->first();
                            }else if($cek_company == '06'){
                                $kategori = KategoriProduk::where('kode_kategori', 'APD')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_infra)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_infra)->first();
                            }

                            $cek_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                            if ($cek_balance == null) {
                                //CEK SEBELUM
                                $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                                if($cek_sebelum != null){
                                    $update_acc = [
                                        'periode'=>$tanggal_baru,
                                        'fiscalyear'=>$tahun,
                                        'account'=>$coa_inventory->account,
                                        'beginning_balance'=>$cek_sebelum->ending_balance,
                                        'debet'=>0,
                                        'kredit'=>0,
                                        'ending_balance'=>$cek_sebelum->ending_balance,
                                        'kode_lokasi'=>$lokasi,
                                    ];

                                    $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                                }else{
                                    $update_acc = [
                                        'periode'=>$tanggal_baru,
                                        'fiscalyear'=>$tahun,
                                        'account'=>$coa_inventory->account,
                                        'beginning_balance'=>0,
                                        'debet'=>0,
                                        'kredit'=>0,
                                        'ending_balance'=>0,
                                        'kode_lokasi'=>$lokasi,
                                    ];

                                    $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                                }

                                //CEK SETELAH
                                $i = $bulan;
                                $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                if ($cek_setelah != null) {
                                    for($i = $bulan; $i <= 12; $i++){
                                        $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                        if($cek_setelah != null){
                                            $begin = $cek_setelah->beginning_balance - $gt_apd;
                                            $debit = $cek_setelah->debet;
                                            $kredit = $cek_setelah->kredit;
                                            if($coa_inventory->normal_balance == 'D'){
                                                $ending_balance = $begin + $debit - $kredit;
                                            }else{
                                                $ending_balance = $begin - $debit + $kredit;
                                            }

                                            $tabel_baru = [
                                                'beginning_balance'=>$begin,
                                                'ending_balance'=>$ending_balance,
                                            ];

                                            $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                        }
                                    }
                                }
                            }else{
                                //CEK SETELAH
                                $i = $bulan;
                                $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                if ($cek_setelah != null) {
                                    for($i = $bulan; $i <= 12; $i++){
                                        $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                        if($cek_setelah != null){
                                            $begin = $cek_setelah->beginning_balance - $gt_apd;
                                            $debit = $cek_setelah->debet;
                                            $kredit = $cek_setelah->kredit;
                                            if($coa_inventory->normal_balance == 'D'){
                                                $ending_balance = $begin + $debit - $kredit;
                                            }else{
                                                $ending_balance = $begin - $debit + $kredit;
                                            }

                                            $tabel_baru = [
                                                'beginning_balance'=>$begin,
                                                'ending_balance'=>$ending_balance,
                                            ];

                                            $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                        }
                                    }
                                }
                            }

                            if($gt_apd_detail < 0){
                                $update_ledger = [
                                    'tahun'=>$tahun,
                                    'periode'=>$bulan,
                                    'account'=>$coa_inventory->account,
                                    'no_journal'=>$opname->no_journal,
                                    'journal_date'=>$opname->tanggal_opname,
                                    'db_cr'=>'K',
                                    'reference'=>$opname->no_opname,
                                    'kredit'=>$gt_apd,
                                    'kode_lokasi'=>$lokasi,
                                ];
                                $update = Ledger::on($konek2)->create($update_ledger);

                                $type = 'Inventory';
                                $transaksi = $opname;
                                $tgl_trans = $opname->tanggal_opname;
                                $harga_acc = $gt_apd;
                                $dbkr = 'K';
                                $update_accbalance = $this->accbalance_kredit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                                $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                            }else{
                                $update_ledger = [
                                    'tahun'=>$tahun,
                                    'periode'=>$bulan,
                                    'account'=>$coa_inventory->account,
                                    'no_journal'=>$opname->no_journal,
                                    'journal_date'=>$opname->tanggal_opname,
                                    'db_cr'=>'D',
                                    'reference'=>$opname->no_opname,
                                    'debit'=>$gt_apd,
                                    'kode_lokasi'=>$lokasi,
                                ];
                                $update = Ledger::on($konek2)->create($update_ledger);

                                $type = 'Inventory';
                                $transaksi = $opname;
                                $tgl_trans = $opname->tanggal_opname;
                                $harga_acc = $gt_apd;
                                $dbkr = 'D';
                                $update_accbalance = $this->accbalance_debit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                                $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                            }


                            $cek_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                            if ($cek_balance == null) {
                                //CEK SEBELUM
                                $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                                if($cek_sebelum != null){
                                    $update_acc = [
                                        'periode'=>$tanggal_baru,
                                        'fiscalyear'=>$tahun,
                                        'account'=>$coa_biaya->account,
                                        'beginning_balance'=>$cek_sebelum->ending_balance,
                                        'debet'=>0,
                                        'kredit'=>0,
                                        'ending_balance'=>$cek_sebelum->ending_balance,
                                        'kode_lokasi'=>$lokasi,
                                    ];

                                    $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                                }else{
                                    $update_acc = [
                                        'periode'=>$tanggal_baru,
                                        'fiscalyear'=>$tahun,
                                        'account'=>$coa_biaya->account,
                                        'beginning_balance'=>0,
                                        'debet'=>0,
                                        'kredit'=>0,
                                        'ending_balance'=>0,
                                        'kode_lokasi'=>$lokasi,
                                    ];

                                    $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                                }

                                //CEK SETELAH
                                $i = $bulan;
                                $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                if ($cek_setelah != null) {
                                    for($i = $bulan; $i <= 12; $i++){
                                        $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                        if($cek_setelah != null){
                                            $begin = $cek_setelah->beginning_balance + $gt_apd;
                                            $debit = $cek_setelah->debet;
                                            $kredit = $cek_setelah->kredit;
                                            if($coa_biaya->normal_balance == 'D'){
                                                $ending_balance = $begin + $debit - $kredit;
                                            }else{
                                                $ending_balance = $begin - $debit + $kredit;
                                            }

                                            $tabel_baru = [
                                                'beginning_balance'=>$begin,
                                                'ending_balance'=>$ending_balance,
                                            ];

                                            $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                        }
                                    }
                                }
                            }else{
                                //CEK SETELAH
                                $i = $bulan;
                                $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                if ($cek_setelah != null) {
                                    for($i = $bulan; $i <= 12; $i++){
                                        $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                        if($cek_setelah != null){
                                            $begin = $cek_setelah->beginning_balance + $gt_apd;
                                            $debit = $cek_setelah->debet;
                                            $kredit = $cek_setelah->kredit;
                                            if($coa_biaya->normal_balance == 'D'){
                                                $ending_balance = $begin + $debit - $kredit;
                                            }else{
                                                $ending_balance = $begin - $debit + $kredit;
                                            }

                                            $tabel_baru = [
                                                'beginning_balance'=>$begin,
                                                'ending_balance'=>$ending_balance,
                                            ];

                                            $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                        }
                                    }
                                }
                            }

                            if($gt_apd_detail < 0){
                                $update_ledger = [
                                    'tahun'=>$tahun,
                                    'periode'=>$bulan,
                                    'account'=>$coa_biaya->account,
                                    'no_journal'=>$opname->no_journal,
                                    'journal_date'=>$opname->tanggal_opname,
                                    'db_cr'=>'D',
                                    'reference'=>$opname->no_opname,
                                    'debit'=>$gt_apd,
                                    'kode_lokasi'=>$lokasi,
                                ];
                                $update = Ledger::on($konek2)->create($update_ledger);

                                $type = 'Inventory';
                                $transaksi = $opname;
                                $tgl_trans = $opname->tanggal_opname;
                                $harga_acc = $gt_apd;
                                $dbkr = 'D';
                                $update_accbalance = $this->accbalance_debit_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                                $update_lrb = $this->lrb_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                            }else{
                                $update_ledger = [
                                    'tahun'=>$tahun,
                                    'periode'=>$bulan,
                                    'account'=>$coa_biaya->account,
                                    'no_journal'=>$opname->no_journal,
                                    'journal_date'=>$opname->tanggal_opname,
                                    'db_cr'=>'K',
                                    'reference'=>$opname->no_opname,
                                    'kredit'=>$gt_apd,
                                    'kode_lokasi'=>$lokasi,
                                ];
                                $update = Ledger::on($konek2)->create($update_ledger);

                                $type = 'Inventory';
                                $transaksi = $opname;
                                $tgl_trans = $opname->tanggal_opname;
                                $harga_acc = $gt_apd;
                                $dbkr = 'K';
                                $update_accbalance = $this->accbalance_kredit_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                                $update_lrb = $this->lrb_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                            }
                        }

                        if($gt_ban != 0){
                            if ($cek_company == '04') {
                                $kategori = KategoriProduk::where('kode_kategori', 'BAN')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_gut)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gut)->first();
                            }else if($cek_company == '0401'){
                                $kategori = KategoriProduk::where('kode_kategori', 'BAN')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_gutjkt)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gutjkt)->first();
                            }else if($cek_company == '03'){
                                $kategori = KategoriProduk::where('kode_kategori', 'BAN')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_emkl)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_emkl)->first();
                            }else if($cek_company == '02'){
                                $kategori = KategoriProduk::where('kode_kategori', 'BAN')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_pbm)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_pbm)->first();
                            }else if($cek_company == '01'){
                                $kategori = KategoriProduk::where('kode_kategori', 'BAN')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_depo)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_depo)->first();
                            }else if($cek_company == '05'){
                                $kategori = KategoriProduk::where('kode_kategori', 'BAN')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_sub)->first();
                            }else if($cek_company == '06'){
                                $kategori = KategoriProduk::where('kode_kategori', 'BAN')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_infra)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_infra)->first();
                            }

                            $cek_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                            if ($cek_balance == null) {
                                //CEK SEBELUM
                                $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                                if($cek_sebelum != null){
                                    $update_acc = [
                                        'periode'=>$tanggal_baru,
                                        'fiscalyear'=>$tahun,
                                        'account'=>$coa_inventory->account,
                                        'beginning_balance'=>$cek_sebelum->ending_balance,
                                        'debet'=>0,
                                        'kredit'=>0,
                                        'ending_balance'=>$cek_sebelum->ending_balance,
                                        'kode_lokasi'=>$lokasi,
                                    ];

                                    $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                                }else{
                                    $update_acc = [
                                        'periode'=>$tanggal_baru,
                                        'fiscalyear'=>$tahun,
                                        'account'=>$coa_inventory->account,
                                        'beginning_balance'=>0,
                                        'debet'=>0,
                                        'kredit'=>0,
                                        'ending_balance'=>0,
                                        'kode_lokasi'=>$lokasi,
                                    ];

                                    $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                                }

                                //CEK SETELAH
                                $i = $bulan;
                                $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                if ($cek_setelah != null) {
                                    for($i = $bulan; $i <= 12; $i++){
                                        $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                        if($cek_setelah != null){
                                            $begin = $cek_setelah->beginning_balance - $gt_ban;
                                            $debit = $cek_setelah->debet;
                                            $kredit = $cek_setelah->kredit;
                                            if($coa_inventory->normal_balance == 'D'){
                                                $ending_balance = $begin + $debit - $kredit;
                                            }else{
                                                $ending_balance = $begin - $debit + $kredit;
                                            }

                                            $tabel_baru = [
                                                'beginning_balance'=>$begin,
                                                'ending_balance'=>$ending_balance,
                                            ];

                                            $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                        }
                                    }
                                }
                            }else{
                                //CEK SETELAH
                                $i = $bulan;
                                $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                if ($cek_setelah != null) {
                                    for($i = $bulan; $i <= 12; $i++){
                                        $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                        if($cek_setelah != null){
                                            $begin = $cek_setelah->beginning_balance - $gt_ban;
                                            $debit = $cek_setelah->debet;
                                            $kredit = $cek_setelah->kredit;
                                            if($coa_inventory->normal_balance == 'D'){
                                                $ending_balance = $begin + $debit - $kredit;
                                            }else{
                                                $ending_balance = $begin - $debit + $kredit;
                                            }

                                            $tabel_baru = [
                                                'beginning_balance'=>$begin,
                                                'ending_balance'=>$ending_balance,
                                            ];

                                            $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                        }
                                    }
                                }
                            }

                            if($gt_ban_detail < 0){
                                $update_ledger = [
                                    'tahun'=>$tahun,
                                    'periode'=>$bulan,
                                    'account'=>$coa_inventory->account,
                                    'no_journal'=>$opname->no_journal,
                                    'journal_date'=>$opname->tanggal_opname,
                                    'db_cr'=>'K',
                                    'reference'=>$opname->no_opname,
                                    'kredit'=>$gt_ban,
                                    'kode_lokasi'=>$lokasi,
                                ];
                                $update = Ledger::on($konek2)->create($update_ledger);

                                $type = 'Inventory';
                                $transaksi = $opname;
                                $tgl_trans = $opname->tanggal_opname;
                                $harga_acc = $gt_ban;
                                $dbkr = 'K';
                                $update_accbalance = $this->accbalance_kredit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                                $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                            }else{
                                $update_ledger = [
                                    'tahun'=>$tahun,
                                    'periode'=>$bulan,
                                    'account'=>$coa_inventory->account,
                                    'no_journal'=>$opname->no_journal,
                                    'journal_date'=>$opname->tanggal_opname,
                                    'db_cr'=>'D',
                                    'reference'=>$opname->no_opname,
                                    'debit'=>$gt_ban,
                                    'kode_lokasi'=>$lokasi,
                                ];
                                $update = Ledger::on($konek2)->create($update_ledger);

                                $type = 'Inventory';
                                $transaksi = $opname;
                                $tgl_trans = $opname->tanggal_opname;
                                $harga_acc = $gt_ban;
                                $dbkr = 'D';
                                $update_accbalance = $this->accbalance_debit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                                $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                            }


                            $cek_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                            if ($cek_balance == null) {
                                //CEK SEBELUM
                                $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                                if($cek_sebelum != null){
                                    $update_acc = [
                                        'periode'=>$tanggal_baru,
                                        'fiscalyear'=>$tahun,
                                        'account'=>$coa_biaya->account,
                                        'beginning_balance'=>$cek_sebelum->ending_balance,
                                        'debet'=>0,
                                        'kredit'=>0,
                                        'ending_balance'=>$cek_sebelum->ending_balance,
                                        'kode_lokasi'=>$lokasi,
                                    ];

                                    $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                                }else{
                                    $update_acc = [
                                        'periode'=>$tanggal_baru,
                                        'fiscalyear'=>$tahun,
                                        'account'=>$coa_biaya->account,
                                        'beginning_balance'=>0,
                                        'debet'=>0,
                                        'kredit'=>0,
                                        'ending_balance'=>0,
                                        'kode_lokasi'=>$lokasi,
                                    ];

                                    $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                                }

                                //CEK SETELAH
                                $i = $bulan;
                                $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                if ($cek_setelah != null) {
                                    for($i = $bulan; $i <= 12; $i++){
                                        $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                        if($cek_setelah != null){
                                            $begin = $cek_setelah->beginning_balance + $gt_ban;
                                            $debit = $cek_setelah->debet;
                                            $kredit = $cek_setelah->kredit;
                                            if($coa_biaya->normal_balance == 'D'){
                                                $ending_balance = $begin + $debit - $kredit;
                                            }else{
                                                $ending_balance = $begin - $debit + $kredit;
                                            }

                                            $tabel_baru = [
                                                'beginning_balance'=>$begin,
                                                'ending_balance'=>$ending_balance,
                                            ];

                                            $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                        }
                                    }
                                }
                            }else{
                                //CEK SETELAH
                                $i = $bulan;
                                $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                if ($cek_setelah != null) {
                                    for($i = $bulan; $i <= 12; $i++){
                                        $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                        if($cek_setelah != null){
                                            $begin = $cek_setelah->beginning_balance + $gt_ban;
                                            $debit = $cek_setelah->debet;
                                            $kredit = $cek_setelah->kredit;
                                            if($coa_biaya->normal_balance == 'D'){
                                                $ending_balance = $begin + $debit - $kredit;
                                            }else{
                                                $ending_balance = $begin - $debit + $kredit;
                                            }

                                            $tabel_baru = [
                                                'beginning_balance'=>$begin,
                                                'ending_balance'=>$ending_balance,
                                            ];

                                            $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                        }
                                    }
                                }
                            }

                            if($gt_ban_detail < 0){
                                $update_ledger = [
                                    'tahun'=>$tahun,
                                    'periode'=>$bulan,
                                    'account'=>$coa_biaya->account,
                                    'no_journal'=>$opname->no_journal,
                                    'journal_date'=>$opname->tanggal_opname,
                                    'db_cr'=>'D',
                                    'reference'=>$opname->no_opname,
                                    'debit'=>$gt_ban,
                                    'kode_lokasi'=>$lokasi,
                                ];
                                $update = Ledger::on($konek2)->create($update_ledger);

                                $type = 'Inventory';
                                $transaksi = $opname;
                                $tgl_trans = $opname->tanggal_opname;
                                $harga_acc = $gt_ban;
                                $dbkr = 'D';
                                $update_accbalance = $this->accbalance_debit_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                                $update_lrb = $this->lrb_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                            }else{
                                $update_ledger = [
                                    'tahun'=>$tahun,
                                    'periode'=>$bulan,
                                    'account'=>$coa_biaya->account,
                                    'no_journal'=>$opname->no_journal,
                                    'journal_date'=>$opname->tanggal_opname,
                                    'db_cr'=>'K',
                                    'reference'=>$opname->no_opname,
                                    'kredit'=>$gt_ban,
                                    'kode_lokasi'=>$lokasi,
                                ];
                                $update = Ledger::on($konek2)->create($update_ledger);

                                $type = 'Inventory';
                                $transaksi = $opname;
                                $tgl_trans = $opname->tanggal_opname;
                                $harga_acc = $gt_ban;
                                $dbkr = 'K';
                                $update_accbalance = $this->accbalance_kredit_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                                $update_lrb = $this->lrb_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                            }
                        }

                        if($gt_bbm != 0){
                            if ($cek_company == '04') {
                                $kategori = KategoriProduk::where('kode_kategori', 'BBM')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_gut)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gut)->first();
                            }else if($cek_company == '0401'){
                                $kategori = KategoriProduk::where('kode_kategori', 'BBM')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_gutjkt)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gutjkt)->first();
                            }else if($cek_company == '03'){
                                $kategori = KategoriProduk::where('kode_kategori', 'BBM')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_emkl)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_emkl)->first();
                            }else if($cek_company == '02'){
                                $kategori = KategoriProduk::where('kode_kategori', 'BBM')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_pbm)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_pbm)->first();
                            }else if($cek_company == '01'){
                                $kategori = KategoriProduk::where('kode_kategori', 'BBM')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_depo)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_depo)->first();
                            }else if($cek_company == '05'){
                                $kategori = KategoriProduk::where('kode_kategori', 'BBM')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_sub)->first();
                            }else if($cek_company == '06'){
                                $kategori = KategoriProduk::where('kode_kategori', 'BBM')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_infra)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_infra)->first();
                            }

                            $cek_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                            if ($cek_balance == null) {
                                //CEK SEBELUM
                                $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                                if($cek_sebelum != null){
                                    $update_acc = [
                                        'periode'=>$tanggal_baru,
                                        'fiscalyear'=>$tahun,
                                        'account'=>$coa_inventory->account,
                                        'beginning_balance'=>$cek_sebelum->ending_balance,
                                        'debet'=>0,
                                        'kredit'=>0,
                                        'ending_balance'=>$cek_sebelum->ending_balance,
                                        'kode_lokasi'=>$lokasi,
                                    ];

                                    $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                                }else{
                                    $update_acc = [
                                        'periode'=>$tanggal_baru,
                                        'fiscalyear'=>$tahun,
                                        'account'=>$coa_inventory->account,
                                        'beginning_balance'=>0,
                                        'debet'=>0,
                                        'kredit'=>0,
                                        'ending_balance'=>0,
                                        'kode_lokasi'=>$lokasi,
                                    ];

                                    $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                                }

                                //CEK SETELAH
                                $i = $bulan;
                                $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                if ($cek_setelah != null) {
                                    for($i = $bulan; $i <= 12; $i++){
                                        $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                        if($cek_setelah != null){
                                            $begin = $cek_setelah->beginning_balance - $gt_bbm;
                                            $debit = $cek_setelah->debet;
                                            $kredit = $cek_setelah->kredit;
                                            if($coa_inventory->normal_balance == 'D'){
                                                $ending_balance = $begin + $debit - $kredit;
                                            }else{
                                                $ending_balance = $begin - $debit + $kredit;
                                            }

                                            $tabel_baru = [
                                                'beginning_balance'=>$begin,
                                                'ending_balance'=>$ending_balance,
                                            ];

                                            $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                        }
                                    }
                                }
                            }else{
                                //CEK SETELAH
                                $i = $bulan;
                                $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                if ($cek_setelah != null) {
                                    for($i = $bulan; $i <= 12; $i++){
                                        $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                        if($cek_setelah != null){
                                            $begin = $cek_setelah->beginning_balance - $gt_bbm;
                                            $debit = $cek_setelah->debet;
                                            $kredit = $cek_setelah->kredit;
                                            if($coa_inventory->normal_balance == 'D'){
                                                $ending_balance = $begin + $debit - $kredit;
                                            }else{
                                                $ending_balance = $begin - $debit + $kredit;
                                            }

                                            $tabel_baru = [
                                                'beginning_balance'=>$begin,
                                                'ending_balance'=>$ending_balance,
                                            ];

                                            $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                        }
                                    }
                                }
                            }

                            if($gt_bbm_detail < 0){
                                $update_ledger = [
                                    'tahun'=>$tahun,
                                    'periode'=>$bulan,
                                    'account'=>$coa_inventory->account,
                                    'no_journal'=>$opname->no_journal,
                                    'journal_date'=>$opname->tanggal_opname,
                                    'db_cr'=>'K',
                                    'reference'=>$opname->no_opname,
                                    'kredit'=>$gt_bbm,
                                    'kode_lokasi'=>$lokasi,
                                ];
                                $update = Ledger::on($konek2)->create($update_ledger);

                                $type = 'Inventory';
                                $transaksi = $opname;
                                $tgl_trans = $opname->tanggal_opname;
                                $harga_acc = $gt_bbm;
                                $dbkr = 'K';
                                $update_accbalance = $this->accbalance_kredit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                                $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                            }else{
                                $update_ledger = [
                                    'tahun'=>$tahun,
                                    'periode'=>$bulan,
                                    'account'=>$coa_inventory->account,
                                    'no_journal'=>$opname->no_journal,
                                    'journal_date'=>$opname->tanggal_opname,
                                    'db_cr'=>'D',
                                    'reference'=>$opname->no_opname,
                                    'debit'=>$gt_bbm,
                                    'kode_lokasi'=>$lokasi,
                                ];
                                $update = Ledger::on($konek2)->create($update_ledger);

                                $type = 'Inventory';
                                $transaksi = $opname;
                                $tgl_trans = $opname->tanggal_opname;
                                $harga_acc = $gt_bbm;
                                $dbkr = 'D';
                                $update_accbalance = $this->accbalance_debit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                                $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                            }
                            

                            $cek_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                            if ($cek_balance == null) {
                                //CEK SEBELUM
                                $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                                if($cek_sebelum != null){
                                    $update_acc = [
                                        'periode'=>$tanggal_baru,
                                        'fiscalyear'=>$tahun,
                                        'account'=>$coa_biaya->account,
                                        'beginning_balance'=>$cek_sebelum->ending_balance,
                                        'debet'=>0,
                                        'kredit'=>0,
                                        'ending_balance'=>$cek_sebelum->ending_balance,
                                        'kode_lokasi'=>$lokasi,
                                    ];

                                    $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                                }else{
                                    $update_acc = [
                                        'periode'=>$tanggal_baru,
                                        'fiscalyear'=>$tahun,
                                        'account'=>$coa_biaya->account,
                                        'beginning_balance'=>0,
                                        'debet'=>0,
                                        'kredit'=>0,
                                        'ending_balance'=>0,
                                        'kode_lokasi'=>$lokasi,
                                    ];

                                    $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                                }

                                //CEK SETELAH
                                $i = $bulan;
                                $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                if ($cek_setelah != null) {
                                    for($i = $bulan; $i <= 12; $i++){
                                        $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                        if($cek_setelah != null){
                                            $begin = $cek_setelah->beginning_balance + $gt_bbm;
                                            $debit = $cek_setelah->debet;
                                            $kredit = $cek_setelah->kredit;
                                            if($coa_biaya->normal_balance == 'D'){
                                                $ending_balance = $begin + $debit - $kredit;
                                            }else{
                                                $ending_balance = $begin - $debit + $kredit;
                                            }

                                            $tabel_baru = [
                                                'beginning_balance'=>$begin,
                                                'ending_balance'=>$ending_balance,
                                            ];

                                            $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                        }
                                    }
                                }
                            }else{
                                //CEK SETELAH
                                $i = $bulan;
                                $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                if ($cek_setelah != null) {
                                    for($i = $bulan; $i <= 12; $i++){
                                        $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                        if($cek_setelah != null){
                                            $begin = $cek_setelah->beginning_balance + $gt_bbm;
                                            $debit = $cek_setelah->debet;
                                            $kredit = $cek_setelah->kredit;
                                            if($coa_biaya->normal_balance == 'D'){
                                                $ending_balance = $begin + $debit - $kredit;
                                            }else{
                                                $ending_balance = $begin - $debit + $kredit;
                                            }

                                            $tabel_baru = [
                                                'beginning_balance'=>$begin,
                                                'ending_balance'=>$ending_balance,
                                            ];

                                            $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                        }
                                    }
                                }
                            }

                            if($gt_bbm_detail < 0){
                                $update_ledger = [
                                    'tahun'=>$tahun,
                                    'periode'=>$bulan,
                                    'account'=>$coa_biaya->account,
                                    'no_journal'=>$opname->no_journal,
                                    'journal_date'=>$opname->tanggal_opname,
                                    'db_cr'=>'D',
                                    'reference'=>$opname->no_opname,
                                    'debit'=>$gt_bbm,
                                    'kode_lokasi'=>$lokasi,
                                ];
                                $update = Ledger::on($konek2)->create($update_ledger);

                                $type = 'Inventory';
                                $transaksi = $opname;
                                $tgl_trans = $opname->tanggal_opname;
                                $harga_acc = $gt_bbm;
                                $dbkr = 'D';
                                $update_accbalance = $this->accbalance_debit_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                                $update_lrb = $this->lrb_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                            }else{
                                $update_ledger = [
                                    'tahun'=>$tahun,
                                    'periode'=>$bulan,
                                    'account'=>$coa_biaya->account,
                                    'no_journal'=>$opname->no_journal,
                                    'journal_date'=>$opname->tanggal_opname,
                                    'db_cr'=>'K',
                                    'reference'=>$opname->no_opname,
                                    'kredit'=>$gt_bbm,
                                    'kode_lokasi'=>$lokasi,
                                ];
                                $update = Ledger::on($konek2)->create($update_ledger);

                                $type = 'Inventory';
                                $transaksi = $opname;
                                $tgl_trans = $opname->tanggal_opname;
                                $harga_acc = $gt_bbm;
                                $dbkr = 'K';
                                $update_accbalance = $this->accbalance_kredit_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                                $update_lrb = $this->lrb_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                            }
                        }

                        if($gt_oli != 0){
                            if ($cek_company == '04') {
                                $kategori = KategoriProduk::where('kode_kategori', 'OLI')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_gut)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gut)->first();
                            }else if($cek_company == '0401'){
                                $kategori = KategoriProduk::where('kode_kategori', 'OLI')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_gutjkt)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gutjkt)->first();
                            }else if($cek_company == '03'){
                                $kategori = KategoriProduk::where('kode_kategori', 'OLI')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_emkl)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_emkl)->first();
                            }else if($cek_company == '02'){
                                $kategori = KategoriProduk::where('kode_kategori', 'OLI')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_pbm)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_pbm)->first();
                            }else if($cek_company == '01'){
                                $kategori = KategoriProduk::where('kode_kategori', 'OLI')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_depo)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_depo)->first();
                            }else if($cek_company == '05'){
                                $kategori = KategoriProduk::where('kode_kategori', 'OLI')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_sub)->first();
                            }else if($cek_company == '06'){
                                $kategori = KategoriProduk::where('kode_kategori', 'OLI')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_infra)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_infra)->first();
                            }

                            $cek_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                            if ($cek_balance == null) {
                                //CEK SEBELUM
                                $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                                if($cek_sebelum != null){
                                    $update_acc = [
                                        'periode'=>$tanggal_baru,
                                        'fiscalyear'=>$tahun,
                                        'account'=>$coa_inventory->account,
                                        'beginning_balance'=>$cek_sebelum->ending_balance,
                                        'debet'=>0,
                                        'kredit'=>0,
                                        'ending_balance'=>$cek_sebelum->ending_balance,
                                        'kode_lokasi'=>$lokasi,
                                    ];

                                    $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                                }else{
                                    $update_acc = [
                                        'periode'=>$tanggal_baru,
                                        'fiscalyear'=>$tahun,
                                        'account'=>$coa_inventory->account,
                                        'beginning_balance'=>0,
                                        'debet'=>0,
                                        'kredit'=>0,
                                        'ending_balance'=>0,
                                        'kode_lokasi'=>$lokasi,
                                    ];

                                    $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                                }

                                //CEK SETELAH
                                $i = $bulan;
                                $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                if ($cek_setelah != null) {
                                    for($i = $bulan; $i <= 12; $i++){
                                        $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                        if($cek_setelah != null){
                                            $begin = $cek_setelah->beginning_balance - $gt_oli;
                                            $debit = $cek_setelah->debet;
                                            $kredit = $cek_setelah->kredit;
                                            if($coa_inventory->normal_balance == 'D'){
                                                $ending_balance = $begin + $debit - $kredit;
                                            }else{
                                                $ending_balance = $begin - $debit + $kredit;
                                            }

                                            $tabel_baru = [
                                                'beginning_balance'=>$begin,
                                                'ending_balance'=>$ending_balance,
                                            ];

                                            $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                        }
                                    }
                                }
                            }else{
                                //CEK SETELAH
                                $i = $bulan;
                                $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                if ($cek_setelah != null) {
                                    for($i = $bulan; $i <= 12; $i++){
                                        $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                        if($cek_setelah != null){
                                            $begin = $cek_setelah->beginning_balance - $gt_oli;
                                            $debit = $cek_setelah->debet;
                                            $kredit = $cek_setelah->kredit;
                                            if($coa_inventory->normal_balance == 'D'){
                                                $ending_balance = $begin + $debit - $kredit;
                                            }else{
                                                $ending_balance = $begin - $debit + $kredit;
                                            }

                                            $tabel_baru = [
                                                'beginning_balance'=>$begin,
                                                'ending_balance'=>$ending_balance,
                                            ];

                                            $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                        }
                                    }
                                }
                            }

                            if($gt_oli_detail < 0){
                                $update_ledger = [
                                    'tahun'=>$tahun,
                                    'periode'=>$bulan,
                                    'account'=>$coa_inventory->account,
                                    'no_journal'=>$opname->no_journal,
                                    'journal_date'=>$opname->tanggal_opname,
                                    'db_cr'=>'K',
                                    'reference'=>$opname->no_opname,
                                    'kredit'=>$gt_oli,
                                    'kode_lokasi'=>$lokasi,
                                ];
                                $update = Ledger::on($konek2)->create($update_ledger);

                                $type = 'Inventory';
                                $transaksi = $opname;
                                $tgl_trans = $opname->tanggal_opname;
                                $harga_acc = $gt_oli;
                                $dbkr = 'K';
                                $update_accbalance = $this->accbalance_kredit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                                $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                            }else{
                                $update_ledger = [
                                    'tahun'=>$tahun,
                                    'periode'=>$bulan,
                                    'account'=>$coa_inventory->account,
                                    'no_journal'=>$opname->no_journal,
                                    'journal_date'=>$opname->tanggal_opname,
                                    'db_cr'=>'D',
                                    'reference'=>$opname->no_opname,
                                    'debit'=>$gt_oli,
                                    'kode_lokasi'=>$lokasi,
                                ];
                                $update = Ledger::on($konek2)->create($update_ledger);

                                $type = 'Inventory';
                                $transaksi = $opname;
                                $tgl_trans = $opname->tanggal_opname;
                                $harga_acc = $gt_oli;
                                $dbkr = 'D';
                                $update_accbalance = $this->accbalance_debit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                                $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                            }


                            $cek_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                            if ($cek_balance == null) {
                                //CEK SEBELUM
                                $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                                if($cek_sebelum != null){
                                    $update_acc = [
                                        'periode'=>$tanggal_baru,
                                        'fiscalyear'=>$tahun,
                                        'account'=>$coa_biaya->account,
                                        'beginning_balance'=>$cek_sebelum->ending_balance,
                                        'debet'=>0,
                                        'kredit'=>0,
                                        'ending_balance'=>$cek_sebelum->ending_balance,
                                        'kode_lokasi'=>$lokasi,
                                    ];

                                    $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                                }else{
                                    $update_acc = [
                                        'periode'=>$tanggal_baru,
                                        'fiscalyear'=>$tahun,
                                        'account'=>$coa_biaya->account,
                                        'beginning_balance'=>0,
                                        'debet'=>0,
                                        'kredit'=>0,
                                        'ending_balance'=>0,
                                        'kode_lokasi'=>$lokasi,
                                    ];

                                    $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                                }

                                //CEK SETELAH
                                $i = $bulan;
                                $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                if ($cek_setelah != null) {
                                    for($i = $bulan; $i <= 12; $i++){
                                        $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                        if($cek_setelah != null){
                                            $begin = $cek_setelah->beginning_balance + $gt_oli;
                                            $debit = $cek_setelah->debet;
                                            $kredit = $cek_setelah->kredit;
                                            if($coa_biaya->normal_balance == 'D'){
                                                $ending_balance = $begin + $debit - $kredit;
                                            }else{
                                                $ending_balance = $begin - $debit + $kredit;
                                            }

                                            $tabel_baru = [
                                                'beginning_balance'=>$begin,
                                                'ending_balance'=>$ending_balance,
                                            ];

                                            $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                        }
                                    }
                                }
                            }else{
                                //CEK SETELAH
                                $i = $bulan;
                                $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                if ($cek_setelah != null) {
                                    for($i = $bulan; $i <= 12; $i++){
                                        $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                        if($cek_setelah != null){
                                            $begin = $cek_setelah->beginning_balance + $gt_oli;
                                            $debit = $cek_setelah->debet;
                                            $kredit = $cek_setelah->kredit;
                                            if($coa_biaya->normal_balance == 'D'){
                                                $ending_balance = $begin + $debit - $kredit;
                                            }else{
                                                $ending_balance = $begin - $debit + $kredit;
                                            }

                                            $tabel_baru = [
                                                'beginning_balance'=>$begin,
                                                'ending_balance'=>$ending_balance,
                                            ];

                                            $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                        }
                                    }
                                }
                            }

                            if($gt_oli_detail < 0){
                                $update_ledger = [
                                    'tahun'=>$tahun,
                                    'periode'=>$bulan,
                                    'account'=>$coa_biaya->account,
                                    'no_journal'=>$opname->no_journal,
                                    'journal_date'=>$opname->tanggal_opname,
                                    'db_cr'=>'D',
                                    'reference'=>$opname->no_opname,
                                    'debit'=>$gt_oli,
                                    'kode_lokasi'=>$lokasi,
                                ];
                                $update = Ledger::on($konek2)->create($update_ledger);

                                $type = 'Inventory';
                                $transaksi = $opname;
                                $tgl_trans = $opname->tanggal_opname;
                                $harga_acc = $gt_oli;
                                $dbkr = 'D';
                                $update_accbalance = $this->accbalance_debit_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                                $update_lrb = $this->lrb_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                            }else{
                                $update_ledger = [
                                    'tahun'=>$tahun,
                                    'periode'=>$bulan,
                                    'account'=>$coa_biaya->account,
                                    'no_journal'=>$opname->no_journal,
                                    'journal_date'=>$opname->tanggal_opname,
                                    'db_cr'=>'K',
                                    'reference'=>$opname->no_opname,
                                    'kredit'=>$gt_oli,
                                    'kode_lokasi'=>$lokasi,
                                ];
                                $update = Ledger::on($konek2)->create($update_ledger);

                                $type = 'Inventory';
                                $transaksi = $opname;
                                $tgl_trans = $opname->tanggal_opname;
                                $harga_acc = $gt_oli;
                                $dbkr = 'K';
                                $update_accbalance = $this->accbalance_kredit_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                                $update_lrb = $this->lrb_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                            }
                        }

                        if($gt_sprt != 0){
                            if ($cek_company == '04') {
                                $kategori = KategoriProduk::where('kode_kategori', 'SPRT')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_gut)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gut)->first();
                            }else if($cek_company == '0401'){
                                $kategori = KategoriProduk::where('kode_kategori', 'SPRT')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_gutjkt)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gutjkt)->first();
                            }else if($cek_company == '03'){
                                $kategori = KategoriProduk::where('kode_kategori', 'SPRT')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_emkl)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_emkl)->first();
                            }else if($cek_company == '02'){
                                $kategori = KategoriProduk::where('kode_kategori', 'SPRT')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_pbm)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_pbm)->first();
                            }else if($cek_company == '01'){
                                $kategori = KategoriProduk::where('kode_kategori', 'SPRT')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_depo)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_depo)->first();
                            }else if($cek_company == '05'){
                                $kategori = KategoriProduk::where('kode_kategori', 'SPRT')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_sub)->first();
                            }else if($cek_company == '06'){
                                $kategori = KategoriProduk::where('kode_kategori', 'SPRT')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_infra)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_infra)->first();
                            }

                            $cek_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                            if ($cek_balance == null) {
                                //CEK SEBELUM
                                $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                                if($cek_sebelum != null){
                                    $update_acc = [
                                        'periode'=>$tanggal_baru,
                                        'fiscalyear'=>$tahun,
                                        'account'=>$coa_inventory->account,
                                        'beginning_balance'=>$cek_sebelum->ending_balance,
                                        'debet'=>0,
                                        'kredit'=>0,
                                        'ending_balance'=>$cek_sebelum->ending_balance,
                                        'kode_lokasi'=>$lokasi,
                                    ];

                                    $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                                }else{
                                    $update_acc = [
                                        'periode'=>$tanggal_baru,
                                        'fiscalyear'=>$tahun,
                                        'account'=>$coa_inventory->account,
                                        'beginning_balance'=>0,
                                        'debet'=>0,
                                        'kredit'=>0,
                                        'ending_balance'=>0,
                                        'kode_lokasi'=>$lokasi,
                                    ];

                                    $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                                }

                                //CEK SETELAH
                                $i = $bulan;
                                $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                if ($cek_setelah != null) {
                                    for($i = $bulan; $i <= 12; $i++){
                                        $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                        if($cek_setelah != null){
                                            $begin = $cek_setelah->beginning_balance - $gt_sprt;
                                            $debit = $cek_setelah->debet;
                                            $kredit = $cek_setelah->kredit;
                                            if($coa_inventory->normal_balance == 'D'){
                                                $ending_balance = $begin + $debit - $kredit;
                                            }else{
                                                $ending_balance = $begin - $debit + $kredit;
                                            }

                                            $tabel_baru = [
                                                'beginning_balance'=>$begin,
                                                'ending_balance'=>$ending_balance,
                                            ];

                                            $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                        }
                                    }
                                }
                            }else{
                                //CEK SETELAH
                                $i = $bulan;
                                $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                if ($cek_setelah != null) {
                                    for($i = $bulan; $i <= 12; $i++){
                                        $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                        if($cek_setelah != null){
                                            $begin = $cek_setelah->beginning_balance - $gt_sprt;
                                            $debit = $cek_setelah->debet;
                                            $kredit = $cek_setelah->kredit;
                                            if($coa_inventory->normal_balance == 'D'){
                                                $ending_balance = $begin + $debit - $kredit;
                                            }else{
                                                $ending_balance = $begin - $debit + $kredit;
                                            }

                                            $tabel_baru = [
                                                'beginning_balance'=>$begin,
                                                'ending_balance'=>$ending_balance,
                                            ];

                                            $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                        }
                                    }
                                }
                            }

                            if($gt_sprt_detail < 0){
                                $update_ledger = [
                                    'tahun'=>$tahun,
                                    'periode'=>$bulan,
                                    'account'=>$coa_inventory->account,
                                    'no_journal'=>$opname->no_journal,
                                    'journal_date'=>$opname->tanggal_opname,
                                    'db_cr'=>'K',
                                    'reference'=>$opname->no_opname,
                                    'kredit'=>$gt_sprt,
                                    'kode_lokasi'=>$lokasi,
                                ];
                                $update = Ledger::on($konek2)->create($update_ledger);

                                $type = 'Inventory';
                                $transaksi = $opname;
                                $tgl_trans = $opname->tanggal_opname;
                                $harga_acc = $gt_sprt;
                                $dbkr = 'K';
                                $update_accbalance = $this->accbalance_kredit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                                $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                            }else{
                                $update_ledger = [
                                    'tahun'=>$tahun,
                                    'periode'=>$bulan,
                                    'account'=>$coa_inventory->account,
                                    'no_journal'=>$opname->no_journal,
                                    'journal_date'=>$opname->tanggal_opname,
                                    'db_cr'=>'D',
                                    'reference'=>$opname->no_opname,
                                    'debit'=>$gt_sprt,
                                    'kode_lokasi'=>$lokasi,
                                ];
                                $update = Ledger::on($konek2)->create($update_ledger);

                                $type = 'Inventory';
                                $transaksi = $opname;
                                $tgl_trans = $opname->tanggal_opname;
                                $harga_acc = $gt_sprt;
                                $dbkr = 'D';
                                $update_accbalance = $this->accbalance_debit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                                $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                            }

                            $cek_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                            if ($cek_balance == null) {
                                //CEK SEBELUM
                                $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                                if($cek_sebelum != null){
                                    $update_acc = [
                                        'periode'=>$tanggal_baru,
                                        'fiscalyear'=>$tahun,
                                        'account'=>$coa_biaya->account,
                                        'beginning_balance'=>$cek_sebelum->ending_balance,
                                        'debet'=>0,
                                        'kredit'=>0,
                                        'ending_balance'=>$cek_sebelum->ending_balance,
                                        'kode_lokasi'=>$lokasi,
                                    ];

                                    $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                                }else{
                                    $update_acc = [
                                        'periode'=>$tanggal_baru,
                                        'fiscalyear'=>$tahun,
                                        'account'=>$coa_biaya->account,
                                        'beginning_balance'=>0,
                                        'debet'=>0,
                                        'kredit'=>0,
                                        'ending_balance'=>0,
                                        'kode_lokasi'=>$lokasi,
                                    ];

                                    $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                                }

                                //CEK SETELAH
                                $i = $bulan;
                                $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                if ($cek_setelah != null) {
                                    for($i = $bulan; $i <= 12; $i++){
                                        $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                        if($cek_setelah != null){
                                            $begin = $cek_setelah->beginning_balance + $gt_sprt;
                                            $debit = $cek_setelah->debet;
                                            $kredit = $cek_setelah->kredit;
                                            if($coa_biaya->normal_balance == 'D'){
                                                $ending_balance = $begin + $debit - $kredit;
                                            }else{
                                                $ending_balance = $begin - $debit + $kredit;
                                            }

                                            $tabel_baru = [
                                                'beginning_balance'=>$begin,
                                                'ending_balance'=>$ending_balance,
                                            ];

                                            $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                        }
                                    }
                                }
                            }else{
                                //CEK SETELAH
                                $i = $bulan;
                                $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                if ($cek_setelah != null) {
                                    for($i = $bulan; $i <= 12; $i++){
                                        $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                        if($cek_setelah != null){
                                            $begin = $cek_setelah->beginning_balance + $gt_sprt;
                                            $debit = $cek_setelah->debet;
                                            $kredit = $cek_setelah->kredit;
                                            if($coa_biaya->normal_balance == 'D'){
                                                $ending_balance = $begin + $debit - $kredit;
                                            }else{
                                                $ending_balance = $begin - $debit + $kredit;
                                            }

                                            $tabel_baru = [
                                                'beginning_balance'=>$begin,
                                                'ending_balance'=>$ending_balance,
                                            ];

                                            $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                        }
                                    }
                                }
                            }

                            if($gt_sprt_detail < 0){
                                $update_ledger = [
                                    'tahun'=>$tahun,
                                    'periode'=>$bulan,
                                    'account'=>$coa_biaya->account,
                                    'no_journal'=>$opname->no_journal,
                                    'journal_date'=>$opname->tanggal_opname,
                                    'db_cr'=>'D',
                                    'reference'=>$opname->no_opname,
                                    'debit'=>$gt_sprt,
                                    'kode_lokasi'=>$lokasi,
                                ];
                                $update = Ledger::on($konek2)->create($update_ledger);

                                $type = 'Inventory';
                                $transaksi = $opname;
                                $tgl_trans = $opname->tanggal_opname;
                                $harga_acc = $gt_sprt;
                                $dbkr = 'D';
                                $update_accbalance = $this->accbalance_debit_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                                $update_lrb = $this->lrb_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                            }else{
                                $update_ledger = [
                                    'tahun'=>$tahun,
                                    'periode'=>$bulan,
                                    'account'=>$coa_biaya->account,
                                    'no_journal'=>$opname->no_journal,
                                    'journal_date'=>$opname->tanggal_opname,
                                    'db_cr'=>'K',
                                    'reference'=>$opname->no_opname,
                                    'kredit'=>$gt_sprt,
                                    'kode_lokasi'=>$lokasi,
                                ];
                                $update = Ledger::on($konek2)->create($update_ledger);

                                $type = 'Inventory';
                                $transaksi = $opname;
                                $tgl_trans = $opname->tanggal_opname;
                                $harga_acc = $gt_sprt;
                                $dbkr = 'K';
                                $update_accbalance = $this->accbalance_kredit_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                                $update_lrb = $this->lrb_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                            }
                        }

                        if($gt_unit != 0){
                            if ($cek_company == '04') {
                                $kategori = KategoriProduk::where('kode_kategori', 'UNIT')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_gut)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gut)->first();
                            }else if($cek_company == '0401'){
                                $kategori = KategoriProduk::where('kode_kategori', 'UNIT')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_gutjkt)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gutjkt)->first();
                            }else if($cek_company == '03'){
                                $kategori = KategoriProduk::where('kode_kategori', 'UNIT')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_emkl)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_emkl)->first();
                            }else if($cek_company == '02'){
                                $kategori = KategoriProduk::where('kode_kategori', 'UNIT')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_pbm)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_pbm)->first();
                            }else if($cek_company == '01'){
                                $kategori = KategoriProduk::where('kode_kategori', 'UNIT')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_depo)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_depo)->first();
                            }else if($cek_company == '05'){
                                $kategori = KategoriProduk::where('kode_kategori', 'UNIT')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_sub)->first();
                            }else if($cek_company == '06'){
                                $kategori = KategoriProduk::where('kode_kategori', 'UNIT')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_infra)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_infra)->first();
                            }

                            $cek_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                            if ($cek_balance == null) {
                                //CEK SEBELUM
                                $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                                if($cek_sebelum != null){
                                    $update_acc = [
                                        'periode'=>$tanggal_baru,
                                        'fiscalyear'=>$tahun,
                                        'account'=>$coa_inventory->account,
                                        'beginning_balance'=>$cek_sebelum->ending_balance,
                                        'debet'=>0,
                                        'kredit'=>0,
                                        'ending_balance'=>$cek_sebelum->ending_balance,
                                        'kode_lokasi'=>$lokasi,
                                    ];

                                    $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                                }else{
                                    $update_acc = [
                                        'periode'=>$tanggal_baru,
                                        'fiscalyear'=>$tahun,
                                        'account'=>$coa_inventory->account,
                                        'beginning_balance'=>0,
                                        'debet'=>0,
                                        'kredit'=>0,
                                        'ending_balance'=>0,
                                        'kode_lokasi'=>$lokasi,
                                    ];

                                    $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                                }

                                //CEK SETELAH
                                $i = $bulan;
                                $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                if ($cek_setelah != null) {
                                    for($i = $bulan; $i <= 12; $i++){
                                        $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                        if($cek_setelah != null){
                                            $begin = $cek_setelah->beginning_balance - $gt_unit;
                                            $debit = $cek_setelah->debet;
                                            $kredit = $cek_setelah->kredit;
                                            if($coa_inventory->normal_balance == 'D'){
                                                $ending_balance = $begin + $debit - $kredit;
                                            }else{
                                                $ending_balance = $begin - $debit + $kredit;
                                            }

                                            $tabel_baru = [
                                                'beginning_balance'=>$begin,
                                                'ending_balance'=>$ending_balance,
                                            ];

                                            $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                        }
                                    }
                                }
                            }else{
                                //CEK SETELAH
                                $i = $bulan;
                                $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                if ($cek_setelah != null) {
                                    for($i = $bulan; $i <= 12; $i++){
                                        $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                        if($cek_setelah != null){
                                            $begin = $cek_setelah->beginning_balance - $gt_unit;
                                            $debit = $cek_setelah->debet;
                                            $kredit = $cek_setelah->kredit;
                                            if($coa_inventory->normal_balance == 'D'){
                                                $ending_balance = $begin + $debit - $kredit;
                                            }else{
                                                $ending_balance = $begin - $debit + $kredit;
                                            }

                                            $tabel_baru = [
                                                'beginning_balance'=>$begin,
                                                'ending_balance'=>$ending_balance,
                                            ];

                                            $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                        }
                                    }
                                }
                            }

                            if($gt_unit_detail < 0){
                                $update_ledger = [
                                    'tahun'=>$tahun,
                                    'periode'=>$bulan,
                                    'account'=>$coa_inventory->account,
                                    'no_journal'=>$opname->no_journal,
                                    'journal_date'=>$opname->tanggal_opname,
                                    'db_cr'=>'K',
                                    'reference'=>$opname->no_opname,
                                    'kredit'=>$gt_unit,
                                    'kode_lokasi'=>$lokasi,
                                ];
                                $update = Ledger::on($konek2)->create($update_ledger);

                                $type = 'Inventory';
                                $transaksi = $opname;
                                $tgl_trans = $opname->tanggal_opname;
                                $harga_acc = $gt_unit;
                                $dbkr = 'K';
                                $update_accbalance = $this->accbalance_kredit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                                $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                            }else{
                                $update_ledger = [
                                    'tahun'=>$tahun,
                                    'periode'=>$bulan,
                                    'account'=>$coa_inventory->account,
                                    'no_journal'=>$opname->no_journal,
                                    'journal_date'=>$opname->tanggal_opname,
                                    'db_cr'=>'D',
                                    'reference'=>$opname->no_opname,
                                    'debit'=>$gt_unit,
                                    'kode_lokasi'=>$lokasi,
                                ];
                                $update = Ledger::on($konek2)->create($update_ledger);

                                $type = 'Inventory';
                                $transaksi = $opname;
                                $tgl_trans = $opname->tanggal_opname;
                                $harga_acc = $gt_unit;
                                $dbkr = 'D';
                                $update_accbalance = $this->accbalance_debit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                                $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                            }


                            $cek_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                            if ($cek_balance == null) {
                                //CEK SEBELUM
                                $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                                if($cek_sebelum != null){
                                    $update_acc = [
                                        'periode'=>$tanggal_baru,
                                        'fiscalyear'=>$tahun,
                                        'account'=>$coa_biaya->account,
                                        'beginning_balance'=>$cek_sebelum->ending_balance,
                                        'debet'=>0,
                                        'kredit'=>0,
                                        'ending_balance'=>$cek_sebelum->ending_balance,
                                        'kode_lokasi'=>$lokasi,
                                    ];

                                    $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                                }else{
                                    $update_acc = [
                                        'periode'=>$tanggal_baru,
                                        'fiscalyear'=>$tahun,
                                        'account'=>$coa_biaya->account,
                                        'beginning_balance'=>0,
                                        'debet'=>0,
                                        'kredit'=>0,
                                        'ending_balance'=>0,
                                        'kode_lokasi'=>$lokasi,
                                    ];

                                    $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                                }

                                //CEK SETELAH
                                $i = $bulan;
                                $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                if ($cek_setelah != null) {
                                    for($i = $bulan; $i <= 12; $i++){
                                        $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                        if($cek_setelah != null){
                                            $begin = $cek_setelah->beginning_balance + $gt_unit;
                                            $debit = $cek_setelah->debet;
                                            $kredit = $cek_setelah->kredit;
                                            if($coa_biaya->normal_balance == 'D'){
                                                $ending_balance = $begin + $debit - $kredit;
                                            }else{
                                                $ending_balance = $begin - $debit + $kredit;
                                            }

                                            $tabel_baru = [
                                                'beginning_balance'=>$begin,
                                                'ending_balance'=>$ending_balance,
                                            ];

                                            $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                        }
                                    }
                                }
                            }else{
                                //CEK SETELAH
                                $i = $bulan;
                                $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                if ($cek_setelah != null) {
                                    for($i = $bulan; $i <= 12; $i++){
                                        $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                        if($cek_setelah != null){
                                            $begin = $cek_setelah->beginning_balance + $gt_unit;
                                            $debit = $cek_setelah->debet;
                                            $kredit = $cek_setelah->kredit;
                                            if($coa_biaya->normal_balance == 'D'){
                                                $ending_balance = $begin + $debit - $kredit;
                                            }else{
                                                $ending_balance = $begin - $debit + $kredit;
                                            }

                                            $tabel_baru = [
                                                'beginning_balance'=>$begin,
                                                'ending_balance'=>$ending_balance,
                                            ];

                                            $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                        }
                                    }
                                }
                            }


                            if($gt_unit_detail < 0){
                                $update_ledger = [
                                    'tahun'=>$tahun,
                                    'periode'=>$bulan,
                                    'account'=>$coa_biaya->account,
                                    'no_journal'=>$opname->no_journal,
                                    'journal_date'=>$opname->tanggal_opname,
                                    'db_cr'=>'D',
                                    'reference'=>$opname->no_opname,
                                    'debit'=>$gt_unit,
                                    'kode_lokasi'=>$lokasi,
                                ];
                                $update = Ledger::on($konek2)->create($update_ledger);

                                $type = 'Inventory';
                                $transaksi = $opname;
                                $tgl_trans = $opname->tanggal_opname;
                                $harga_acc = $gt_unit;
                                $dbkr = 'D';
                                $update_accbalance = $this->accbalance_debit_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                                $update_lrb = $this->lrb_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                            }else{
                                $update_ledger = [
                                    'tahun'=>$tahun,
                                    'periode'=>$bulan,
                                    'account'=>$coa_biaya->account,
                                    'no_journal'=>$opname->no_journal,
                                    'journal_date'=>$opname->tanggal_opname,
                                    'db_cr'=>'K',
                                    'reference'=>$opname->no_opname,
                                    'kredit'=>$gt_unit,
                                    'kode_lokasi'=>$lokasi,
                                ];
                                $update = Ledger::on($konek2)->create($update_ledger);

                                $type = 'Inventory';
                                $transaksi = $opname;
                                $tgl_trans = $opname->tanggal_opname;
                                $harga_acc = $gt_unit;
                                $dbkr = 'K';
                                $update_accbalance = $this->accbalance_kredit_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                                $update_lrb = $this->lrb_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                            }
                        }
                        
                        if($gt_sldg != 0){
                            if ($cek_company == '04') {
                                $kategori = KategoriProduk::where('kode_kategori', 'SLDG')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_gut)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gut)->first();
                            }else if($cek_company == '0401'){
                                $kategori = KategoriProduk::where('kode_kategori', 'SLDG')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_gutjkt)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gutjkt)->first();
                            }else if($cek_company == '03'){
                                $kategori = KategoriProduk::where('kode_kategori', 'SLDG')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_emkl)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_emkl)->first();
                            }else if($cek_company == '02'){
                                $kategori = KategoriProduk::where('kode_kategori', 'SLDG')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_pbm)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_pbm)->first();
                            }else if($cek_company == '01'){
                                $kategori = KategoriProduk::where('kode_kategori', 'SLDG')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_depo)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_depo)->first();
                            }else if($cek_company == '05'){
                                $kategori = KategoriProduk::where('kode_kategori', 'SLDG')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_sub)->first();
                            }else if($cek_company == '06'){
                                $kategori = KategoriProduk::where('kode_kategori', 'SLDG')->first();
                                $coa_inventory = Coa::where('kode_coa', $kategori->coa_infra)->first();
                                $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_infra)->first();
                            }

                            $cek_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                            if ($cek_balance == null) {
                                //CEK SEBELUM
                                $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                                if($cek_sebelum != null){
                                    $update_acc = [
                                        'periode'=>$tanggal_baru,
                                        'fiscalyear'=>$tahun,
                                        'account'=>$coa_inventory->account,
                                        'beginning_balance'=>$cek_sebelum->ending_balance,
                                        'debet'=>0,
                                        'kredit'=>0,
                                        'ending_balance'=>$cek_sebelum->ending_balance,
                                        'kode_lokasi'=>$lokasi,
                                    ];

                                    $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                                }else{
                                    $update_acc = [
                                        'periode'=>$tanggal_baru,
                                        'fiscalyear'=>$tahun,
                                        'account'=>$coa_inventory->account,
                                        'beginning_balance'=>0,
                                        'debet'=>0,
                                        'kredit'=>0,
                                        'ending_balance'=>0,
                                        'kode_lokasi'=>$lokasi,
                                    ];

                                    $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                                }

                                //CEK SETELAH
                                $i = $bulan;
                                $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                if ($cek_setelah != null) {
                                    for($i = $bulan; $i <= 12; $i++){
                                        $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                        if($cek_setelah != null){
                                            $begin = $cek_setelah->beginning_balance - $gt_sldg;
                                            $debit = $cek_setelah->debet;
                                            $kredit = $cek_setelah->kredit;
                                            if($coa_inventory->normal_balance == 'D'){
                                                $ending_balance = $begin + $debit - $kredit;
                                            }else{
                                                $ending_balance = $begin - $debit + $kredit;
                                            }

                                            $tabel_baru = [
                                                'beginning_balance'=>$begin,
                                                'ending_balance'=>$ending_balance,
                                            ];

                                            $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                        }
                                    }
                                }
                            }else{
                                //CEK SETELAH
                                $i = $bulan;
                                $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                if ($cek_setelah != null) {
                                    for($i = $bulan; $i <= 12; $i++){
                                        $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                        if($cek_setelah != null){
                                            $begin = $cek_setelah->beginning_balance - $gt_sldg;
                                            $debit = $cek_setelah->debet;
                                            $kredit = $cek_setelah->kredit;
                                            if($coa_inventory->normal_balance == 'D'){
                                                $ending_balance = $begin + $debit - $kredit;
                                            }else{
                                                $ending_balance = $begin - $debit + $kredit;
                                            }

                                            $tabel_baru = [
                                                'beginning_balance'=>$begin,
                                                'ending_balance'=>$ending_balance,
                                            ];

                                            $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                        }
                                    }
                                }
                            }

                            if($gt_sldg_detail < 0){
                                $update_ledger = [
                                    'tahun'=>$tahun,
                                    'periode'=>$bulan,
                                    'account'=>$coa_inventory->account,
                                    'no_journal'=>$opname->no_journal,
                                    'journal_date'=>$opname->tanggal_opname,
                                    'db_cr'=>'K',
                                    'reference'=>$opname->no_opname,
                                    'kredit'=>$gt_sldg,
                                    'kode_lokasi'=>$lokasi,
                                ];
                                $update = Ledger::on($konek2)->create($update_ledger);

                                $type = 'Inventory';
                                $transaksi = $opname;
                                $tgl_trans = $opname->tanggal_opname;
                                $harga_acc = $gt_sldg;
                                $dbkr = 'K';
                                $update_accbalance = $this->accbalance_kredit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                                $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                            }else{
                                $update_ledger = [
                                    'tahun'=>$tahun,
                                    'periode'=>$bulan,
                                    'account'=>$coa_inventory->account,
                                    'no_journal'=>$opname->no_journal,
                                    'journal_date'=>$opname->tanggal_opname,
                                    'db_cr'=>'D',
                                    'reference'=>$opname->no_opname,
                                    'debit'=>$gt_sldg,
                                    'kode_lokasi'=>$lokasi,
                                ];
                                $update = Ledger::on($konek2)->create($update_ledger);

                                $type = 'Inventory';
                                $transaksi = $opname;
                                $tgl_trans = $opname->tanggal_opname;
                                $harga_acc = $gt_sldg;
                                $dbkr = 'D';
                                $update_accbalance = $this->accbalance_debit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                                $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                            }


                            $cek_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                            if ($cek_balance == null) {
                                //CEK SEBELUM
                                $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                                if($cek_sebelum != null){
                                    $update_acc = [
                                        'periode'=>$tanggal_baru,
                                        'fiscalyear'=>$tahun,
                                        'account'=>$coa_biaya->account,
                                        'beginning_balance'=>$cek_sebelum->ending_balance,
                                        'debet'=>0,
                                        'kredit'=>0,
                                        'ending_balance'=>$cek_sebelum->ending_balance,
                                        'kode_lokasi'=>$lokasi,
                                    ];

                                    $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                                }else{
                                    $update_acc = [
                                        'periode'=>$tanggal_baru,
                                        'fiscalyear'=>$tahun,
                                        'account'=>$coa_biaya->account,
                                        'beginning_balance'=>0,
                                        'debet'=>0,
                                        'kredit'=>0,
                                        'ending_balance'=>0,
                                        'kode_lokasi'=>$lokasi,
                                    ];

                                    $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                                }

                                //CEK SETELAH
                                $i = $bulan;
                                $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                if ($cek_setelah != null) {
                                    for($i = $bulan; $i <= 12; $i++){
                                        $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                        if($cek_setelah != null){
                                            $begin = $cek_setelah->beginning_balance + $gt_sldg;
                                            $debit = $cek_setelah->debet;
                                            $kredit = $cek_setelah->kredit;
                                            if($coa_biaya->normal_balance == 'D'){
                                                $ending_balance = $begin + $debit - $kredit;
                                            }else{
                                                $ending_balance = $begin - $debit + $kredit;
                                            }

                                            $tabel_baru = [
                                                'beginning_balance'=>$begin,
                                                'ending_balance'=>$ending_balance,
                                            ];

                                            $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                        }
                                    }
                                }
                            }else{
                                //CEK SETELAH
                                $i = $bulan;
                                $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                if ($cek_setelah != null) {
                                    for($i = $bulan; $i <= 12; $i++){
                                        $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                        if($cek_setelah != null){
                                            $begin = $cek_setelah->beginning_balance + $gt_sldg;
                                            $debit = $cek_setelah->debet;
                                            $kredit = $cek_setelah->kredit;
                                            if($coa_biaya->normal_balance == 'D'){
                                                $ending_balance = $begin + $debit - $kredit;
                                            }else{
                                                $ending_balance = $begin - $debit + $kredit;
                                            }

                                            $tabel_baru = [
                                                'beginning_balance'=>$begin,
                                                'ending_balance'=>$ending_balance,
                                            ];

                                            $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                        }
                                    }
                                }
                            }


                            if($gt_sldg_detail < 0){
                                $update_ledger = [
                                    'tahun'=>$tahun,
                                    'periode'=>$bulan,
                                    'account'=>$coa_biaya->account,
                                    'no_journal'=>$opname->no_journal,
                                    'journal_date'=>$opname->tanggal_opname,
                                    'db_cr'=>'D',
                                    'reference'=>$opname->no_opname,
                                    'debit'=>$gt_sldg,
                                    'kode_lokasi'=>$lokasi,
                                ];
                                $update = Ledger::on($konek2)->create($update_ledger);

                                $type = 'Inventory';
                                $transaksi = $opname;
                                $tgl_trans = $opname->tanggal_opname;
                                $harga_acc = $gt_sldg;
                                $dbkr = 'D';
                                $update_accbalance = $this->accbalance_debit_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                                $update_lrb = $this->lrb_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                            }else{
                                $update_ledger = [
                                    'tahun'=>$tahun,
                                    'periode'=>$bulan,
                                    'account'=>$coa_biaya->account,
                                    'no_journal'=>$opname->no_journal,
                                    'journal_date'=>$opname->tanggal_opname,
                                    'db_cr'=>'K',
                                    'reference'=>$opname->no_opname,
                                    'kredit'=>$gt_sldg,
                                    'kode_lokasi'=>$lokasi,
                                ];
                                $update = Ledger::on($konek2)->create($update_ledger);

                                $type = 'Inventory';
                                $transaksi = $opname;
                                $tgl_trans = $opname->tanggal_opname;
                                $harga_acc = $gt_sldg;
                                $dbkr = 'K';
                                $update_accbalance = $this->accbalance_kredit_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                                $update_lrb = $this->lrb_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                            }
                        }
                    }
            
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

        if($cek_bulan == null || $level == 'superadministrator' || $level == 'user_rince' || $level == 'user_thomas'){
            $opname = Opname::on($konek)->find(request()->id);
            $cek_status = $opname->status;
            if($cek_status != 'POSTED'){  
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'UNPOST No. Opname: '.$opname->no_opname.' sudah dilakukan! Pastikan Anda tidak membuka menu OPNAME lebih dari 1',
                ];
                return response()->json($message);
            }

            $crate_opname = $opname->created_at;

            $tgl = $opname->tanggal_opname;
            $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
            $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
            $tanggal = '01';

            $tanggal_baru = Carbon\Carbon::createFromDate($tahun, $bulan, $tanggal)->toDateString();
            $lokasi = $opname->kode_lokasi;
            $validate = $this->periodeChecker($tgl);

            if($validate == true){
                $opnamedetail = OpnameDetail::on($konek)->with('produk')->where('no_opname', request()->id)->get();
                $no_opname = request()->id;
                $data = array();

                foreach ($opnamedetail as $rowdata){
                        $data[] = array(
                            'no_opname'=>$no_opname,
                            'kode_produk'=>$rowdata->kode_produk,
                            'partnumber'=>$rowdata->partnumber,
                        );    

                    $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$rowdata->kode_produk)->where('kode_lokasi',$lokasi)->where('partnumber',$rowdata->partnumber)->where('periode', $tanggal_baru)->first();

                    $produk_awal = $tb_item_bulanan->kode_produk;
                    $part_awal = $tb_item_bulanan->partnumber;
                    $opname_stock = $tb_item_bulanan->stock_opname;
                    $opname_amount = $tb_item_bulanan->amount_opname;

                    $stock_begin = $tb_item_bulanan->begin_stock;
                    $amount_begin = $tb_item_bulanan->begin_amount;

                    $stok_in = $tb_item_bulanan->in_stock;

                    $amount_masuk = $tb_item_bulanan->in_amount;
                    $amount_keluar = $tb_item_bulanan->out_amount;
                    $amount_sale = $tb_item_bulanan->sale_amount;

                    $stock_trfin = $tb_item_bulanan->trf_in;
                    $amount_trfin = $tb_item_bulanan->trf_in_amount;

                    $stock_trfout = $tb_item_bulanan->trf_out;
                    $amount_trfout = $tb_item_bulanan->trf_out_amount;

                    $stock_a = $tb_item_bulanan->adjustment_stock;
                    $amount_a = $tb_item_bulanan->adjustment_amount;

                    $stock_out = $tb_item_bulanan->out_stock;
                    $stock_sale = $tb_item_bulanan->sale_stock;

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

                    $produk = Produk::on($konek)->find($rowdata->kode_produk);

                    $opnamedetail2 = OpnameDetail::on($konek)->where('no_opname', $no_opname)->where('kode_produk',$rowdata->kode_produk)->where('partnumber',$rowdata->partnumber)->first();

                    $stock_op = $opnamedetail2->stock_opname;
                    $amount_op = $opnamedetail2->amount_opname;

                    $hpp = $opnamedetail2->hpp;

                    $waktu = $tgl;
                    $barang = $rowdata->kode_produk;
                    $so = $opname_stock - $stock_op;
                    $ao = $opname_amount - $amount_op;
                    $end_stok = $stock_begin + $stok_in - $stock_out - $stock_sale + $stock_trfin - $stock_trfout + $stock_a + $so - $retur_beli_stock + $retur_jual_stock - $disassembling_stock + $assembling_stock + $rpk_stock;
                    $end_amount = $amount_begin + $amount_masuk - $amount_keluar - $amount_sale + $amount_trfin - $amount_trfout + $amount_a + $ao - $retur_beli_amount + $retur_jual_amount - $disassembling_amount + $assembling_amount + $rpk_amount;

                    if($end_stok != 0){
                        $hpp2 = $end_amount / $end_stok;
                    }else{
                        $hpp2 = $tb_item_bulanan->hpp;
                    }

                    $tabel_baru = [
                            'stock_opname'=>$so,
                            'amount_opname'=>$ao,
                            'ending_stock'=>$end_stok,
                            'ending_amount'=>$end_amount,
                            'hpp'=>$hpp2,
                    ];
                                                                                        
                    $update_produk_history = tb_produk_history::on($konek)->where('no_transaksi',$no_opname)->delete();

                    $update_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$rowdata->kode_produk)->where('partnumber',$rowdata->partnumber)->where('kode_lokasi',$lokasi)->where('periode', $tanggal_baru)->update($tabel_baru);

                    $tabel_history = [
                            'kode_produk'=>$barang,
                            'no_transaksi'=>$no_opname,
                            'tanggal_transaksi'=>$waktu,
                            'jam_transaksi'=>$crate_opname,
                            'qty_transaksi'=>$stock_op,
                            'harga_transaksi'=>$hpp,
                            'total_transaksi'=>$hpp*$stock_op,
                    ];

                    $tgl_op1 = $opname->tanggal_opname;
                    $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_op1)->year;
                    $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_op1)->month;

                    $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                    $status_reopen = $reopen->reopen_status;

                    if($status_reopen == 'true'){
                            $tgl_op = $opname->tanggal_opname;
                            $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_op)->year;
                            $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_op)->month;

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
                                $opnamedetail1 = OpnameDetail::on($konek)->where('no_opname', $no_opname)->where('kode_produk',$rowdata->kode_produk)->where('partnumber',$rowdata->partnumber)->first();

                                $hpp = $opnamedetail1->harga;
                                $qty_op = $opnamedetail1->stock_opname;
                                $amount_op = $opnamedetail1->amount_opname;

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

                                $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$rowdata->kode_produk)->where('partnumber',$rowdata->partnumber)->where('kode_lokasi',$lokasi)->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->first();

                                if($tb_item_bulanan2 != null){
                                    $bs = $tb_item_bulanan2->begin_stock;
                                    $ba = $tb_item_bulanan2->begin_amount;
                                    $es = $tb_item_bulanan2->ending_stock;
                                    $ea = $tb_item_bulanan2->ending_amount;

                                    $begin_stock1 = $bs - $qty_op;
                                    $begin_amount1 = $ba - $amount_op;

                                    $end_stok1 = $es - $qty_op;
                                    $end_amount1 = $ea - $amount_op;

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
                                                // dd($tabel_baru2);

                                    $update_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$rowdata->kode_produk)->where('partnumber',$rowdata->partnumber)->where('kode_lokasi',$lokasi)->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->update($tabel_baru2);
                                }

                                $j++;
                            }
                        }
                }
            
                $leng = count($opnamedetail);
                $i = 0;
                
                $opname = Opname::on($konek)->find(request()->id);
                $opname->status = "OPEN";
                $opname->save(); 

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Unpost No. Opname: '.$no_opname.'.','created_by'=>$nama,'updated_by'=>$nama];

                user_history::on($konek)->create($tmp);

                $cek_company = Auth()->user()->kode_company;
                if($cek_company == '04' || $cek_company == '0401' || $cek_company == '03' || $cek_company == '05' || $cek_company == '06' || $cek_company == '02'){
                    $konek2 = self::konek2();

                    $get_ledger = Ledger::on($konek2)->where('no_journal',$opname->no_journal)->get();

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
                            $transaksi = $opname;
                            $tgl_trans = $opname->tanggal_opname;
                            $update_accbalance = $this->accbalance_debit_unpost($account, $harga, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                            $cek_acc = Coa::on('mysql4')->where('account',$account)->first();
                            $update_lrb = $this->lrb_unpost($cek_acc, $harga, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $data[$i]['db_cr']);

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
                            $transaksi = $opname;
                            $tgl_trans = $opname->tanggal_opname;
                            $update_accbalance = $this->accbalance_kredit_unpost($account, $harga, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                            $cek_acc = Coa::on('mysql4')->where('account',$account)->first();
                            $update_lrb = $this->lrb_unpost($cek_acc, $harga, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $data[$i]['db_cr']);

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

                    $update_ledger = Ledger::on($konek2)->where('no_journal',$opname->no_journal)->delete();
                }
            
                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data berhasil di UNPOST.'
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
        $tanggal = $request->tanggal_opname;
        $konek = self::konek();
        $validate = $this->periodeChecker($tanggal);
             
        if($validate == true){
                $opname = Opname::on($konek)->create($request->all());

                $no = Opname::on($konek)->orderBy('created_at','desc')->first();
                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Simpan No. Opname: '.$no->no_opname.'.','created_by'=>$nama,'updated_by'=>$nama];

                user_history::on($konek)->create($tmp);

                $message = [
                    'success' => true,
                    'title' => 'Simpan',
                    'message' => 'Data telah Disimpan.',
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

    public function edit_opname()
    {       
        $konek = self::konek();
        $no_opname = request()->id;
        $data = Opname::on($konek)->find($no_opname);

        $output = array(
            'no_opname'=> $data->no_opname,
            'tanggal_opname'=> $data->tanggal_opname,
            'status'=> $data->status,
        );
        return response()->json($output);
    }

    public function updateAjax(Request $request)
    {
        $tgl = $request->tanggal_opname;
        $konek = self::konek();
        $validate = $this->periodeChecker($tgl);

        if($validate == true){
            $Opname = Opname::on($konek)->find($request->no_opname)->update($request->all());

            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Edit No. Opname: '.$request->no_opname.'.','created_by'=>$nama,'updated_by'=>$nama];

            user_history::on($konek)->create($tmp);
       
            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data telah di Update.'
            ];
            return response()->json($message);
        }else{
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Re-Open Periode: '.$tgl,
            ];
            return response()->json($message);
        }
    }


    public function hapus_opname()
    {
        $konek = self::konek();
        $level = auth()->user()->level;

        $no_opname = request()->id;
        $data = Opname::on($konek)->find($no_opname);
        $tgl = $data->tanggal_opname;

        $validate = $this->periodeChecker($tgl);

        if($validate == true){
            $cek_detail = OpnameDetail::on($konek)->where('no_opname',$no_opname)->first();
            if($cek_detail == null){
                $data_detail = OpnameDetail::on($konek)->where('no_opname',$no_opname)->delete();
                $data->delete();

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Hapus No. Opname: '.$no_opname.'.','created_by'=>$nama,'updated_by'=>$nama];
                        
                user_history::on($konek)->create($tmp);

                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data ['.$data->no_opname.'] telah dihapus.'
                ];
                return response()->json($message);
            }
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
}
