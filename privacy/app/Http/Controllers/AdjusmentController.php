<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Adjustment;
use App\Models\AdjustmentDetail;
use App\Models\Company;
use App\Models\Konversi;
use App\Models\satuan;
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
use App\Models\Labarugiberjalan;
use App\Models\Opname;
use App\Models\Costcenter;
use App\Models\SetupFolder;
use Illuminate\Support\Facades\Storage;
use PDF;
use Excel;
use DB;
use Alert;
use Carbon;
use DateTime;

class AdjusmentController extends Controller
{
    public function index()
    {
        $konek = self::konek();
        $produk = Produk::on($konek)->pluck('nama_produk','id');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');

        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;
        
        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        $level = auth()->user()->level;
        
        return view('admin.adjustment.index',compact('produk','period', 'nama_lokasi','nama_company'));
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
        }else if ($compa == '06'){
            $koneksi = 'mysql_finance_inf';
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
            return Datatables::of(Adjustment::on($konek)->with('produk')->orderBy('created_at','desc'))->make(true);
        }else{
            return Datatables::of(Adjustment::on($konek)->with('produk')->orderBy('created_at','desc')->where('kode_lokasi', auth()->user()->kode_lokasi))->make(true);
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

    public function exportPDF(Adjustment $adjustment){
        $request = $_GET['no_penyesuaian'];
        $konek = self::konek();
        $adjustment = Adjustment::on($konek)->where('no_penyesuaian',$request)->first();
        $user = $adjustment->created_by;
        $no_penyesuaian = $adjustment->no_penyesuaian;

        $kode_company = $adjustment->kode_company;
        $adjustmentdetail = AdjustmentDetail::on($konek)->where('no_penyesuaian',$request)->get();

        $company = Company::where('kode_company',$kode_company)->first();
        $nama_company = $company->nama_company;

        $dt = Carbon\Carbon::now();
        $date_now = Carbon\Carbon::parse($dt)->format('d/m/Y H:i:s');

        $tgl = $adjustment->tanggal;
        $date=date_create($tgl);
        $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
        $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
        
        $setupfolder = SetupFolder::find(21);
        $tes_save = $company->kode_company.". ".$company->nama_company."/".$setupfolder->folder."/".$setupfolder->subfolder."/".$tahun."/".$bulan."/".$no_penyesuaian.".pdf";

        $pdf = PDF::loadView('/admin/adjustment/pdf', compact('adjustmentdetail','request','tgl', 'no_penyesuaian','nama_company','date_now','adjustment','user'));
        $pdf->setPaper([0, 0, 684, 792], 'potrait');

        Storage::disk('ftp')->put($tes_save, $pdf->output());
        return $pdf->stream($no_penyesuaian.'.pdf');        
    }

    public function exportPDF3(){
        $konek = self::konek();
        $konek2 = self::konek2();
        $request = $_GET['no_penyesuaian'];
        $no_journal = $_GET['no_journal'];

        $adjustment = Adjustment::on($konek)->find($request);
        $jur = $adjustment->kode_jurnal;
        $jurnal = Jurnal::find($jur);

        $total_qty = 0;
        $total_harga = 0;
        $grand_total = 0;
        $detail = AdjustmentDetail::on($konek)->where('no_penyesuaian',$request)->get();
        foreach ($detail as $row){
            $total_qty += $row->qty;
            $subtotal = $row->harga * $row->qty;
            $total_harga += $subtotal;
            $grand_total = $total_harga;
        }

        $ledger2 = Ledger::on($konek2)->with('coa')->where('no_journal',$no_journal)->first();

        $ledger = Ledger::on($konek2)->select('ledger.*','coa.ac_description')->join('u5611458_gui_general_ledger_laravel.coa','u5611458_gui_general_ledger_laravel.coa.account','=','ledger.account')->where('no_journal', $no_journal)->get();

        $user = $adjustment->created_by;
        $tgl = $adjustment->tanggal;
        $date=date_create($tgl);

        $ttd = $user;

        $get_lokasi = auth()->user()->kode_lokasi;
        $get_company = auth()->user()->kode_company;

        $nama_lokasi = MasterLokasi::find($get_lokasi);
        $nama = $nama_lokasi->nama_lokasi;

        $company = Company::find($get_company);
        $nama2 = $company->nama_company;

        $dt = Carbon\Carbon::now();
        $date_now = Carbon\Carbon::parse($dt)->format('d/m/Y H:i:s');
        $journal_date = Carbon\Carbon::parse($ledger2->journal_date)->format('d/m/Y');

        $pdf = PDF::loadView('/admin/adjustment/pdf2', compact('adjustment','request', 'jurnal','tgl','date', 'ttd','nama_company','date_now','ledger','ledger2','dt','user','nama','nama2','journal_date','grand_total'));
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

    public function Showdetail()
    {
        $konek = self::konek();
        $total_qty = 0;
        $total_harga = 0;
        $grand_total = 0;
        $adjustmentdetail= AdjustmentDetail::on($konek)->with('produk','satuan')->where('no_penyesuaian',request()->id)
        ->orderBy('created_at', 'desc')->get();

        $output = array();

        foreach ($adjustmentdetail as $row){
            $total_qty += $row->qty;
            $subtotal = $row->harga * $row->qty;
            $total_harga += $subtotal;
            $grand_total = number_format($total_harga,2,",",".");
        }

        if($adjustmentdetail){
            foreach($adjustmentdetail as $row)
            {
                $output[] = array(
                    'no_penyesuaian'=>$row->no_penyesuaian,
                    'produk'=>$row->produk->nama_produk,
                    'partnumber'=>$row->partnumber,
                    'satuan'=>$row->satuan->nama_satuan,
                    'qty'=>$row->qty,
                    'harga'=>$row->harga,
                    'subtotal'=>number_format($row->harga * $row->qty,2,",","."),
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

    public function detail($adjustment)
    {   
        $konek = static::konek();
        $adjustment = Adjustment::on($konek)->find($adjustment);
        $tanggal = $adjustment->tanggal;
        $no_penyesuaian = $adjustment->no_penyesuaian;

        $period = tb_akhir_bulan::on($konek)->where('status_periode','Open')->orwhere('reopen_status','true')->first();
        $tgl2 = $period->periode;
        $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl2)->year;
        $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl2)->month;

        $cekbulanan = tb_item_bulanan::on($konek)->whereMonth('periode',$bulan)->whereYear('periode',$tahun)->pluck('kode_produk','kode_produk');
        $cekproduk = Produk::on($konek)->whereNotIn('id',$cekbulanan)->get();

        $validate = $this->periodeChecker($tanggal);
             
        if($validate == true){
            $data = Adjustment::on($konek)->find($no_penyesuaian);

            $total_qty = 0;
            $total_harga = 0;
            $grand_total = 0;

            $adjustmentdetail = AdjustmentDetail::on($konek)->with('produk','satuan')->where('no_penyesuaian', $adjustment->no_penyesuaian)
            ->orderBy('created_at','desc')->get();

            foreach ($adjustmentdetail as $row){
                $total_qty += $row->qty;
                $subtotal = $row->harga * $row->qty;
                $total_harga += $subtotal;
                $grand_total = number_format($total_harga,2,",",".");
            }

            $list_url= route('adjustment.index');

            $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode', 'Open')->orwhere('reopen_status', 'true')->first();

            $Produk = Produk::on($konek)->where('stat','Aktif')->pluck('nama_produk','id');

            $Satuan = satuan::pluck('nama_satuan', 'kode_satuan');
            $Company= Company::pluck('nama_company', 'kode_company');

            $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
            $tgl_jalan2 = $tgl_jalan->periode;
            $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
            $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
            $nama_lokasi = $get_lokasi->nama_lokasi;

            $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
            $nama_company = $get_company->nama_company;

            return view('admin.adjustmentdetail.index', compact('adjustment','adjustmentdetail','list_url','Produk','Satuan','total_qty','grand_total','Satuan','Company','period', 'nama_lokasi','nama_company'));
        }
        else{
            alert()->success('Status POSTED / Periode Telah CLOSED: '.$tanggal,'GAGAL!')->persistent('Close');
            return redirect()->back();
        }
    }

    function produkChecker($no_penyesuaian, $tahun, $bulan, $tanggal_baru, $tgl, $penyesuaian, $koneksi)
    {
        $konek = static::konek();
        $adjustment = AdjustmentDetail::on($konek)->with('produk')->where('no_penyesuaian', request()->id)->get();
        $no_penyesuaian = request()->id;

        $data = array();

        if(!empty($adjustment)){
            foreach ($adjustment as $rowdata){
                $data[] = array(
                    'no_penyesuaian'=>$no_penyesuaian,
                    'kode_produk'=>$rowdata->kode_produk,
                    'kode_satuan'=>$rowdata->kode_satuan,
                    'qty'=>$rowdata->qty,
                    'partnumber'=>$rowdata->partnumber,
                    'harga'=>$rowdata->harga,
                );         
            }

        }

        if(!empty($adjustment)){
            $leng = count($adjustment);
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

                    $produk = Produk::on($konek)->find($data[$i]['kode_produk']);
                    $adjustmentdetail2 = AdjustmentDetail::on($konek)->where('no_penyesuaian', $no_penyesuaian)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                    $harga = $data[$i]['harga'];
                    $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                    $qty_baru = $data[$i]['qty']*$konversi->nilai_konversi;
                    $waktu = $tgl;
                    $barang = $data[$i]['kode_produk'];

                    $adj_stok_new = $stock_adjustment + $qty_baru;
                    if($qty_baru != 0){
                        $adj_amount_new = $amount_adjustment + ($harga * $qty_baru);
                    }else{
                        $adj_amount_new = $amount_adjustment + $harga;
                    }
                    $end_stok_new = $stock_begin + $stok_in - $stock_out - $stock_sale + $stock_trfin - $stock_trfout + $stock_op + $adj_stok_new - $retur_beli_stock + $retur_jual_stock - $disassembling_stock + $assembling_stock;
                    $end_amount_new = $amount_begin + $amount_masuk - $amount_keluar - $amount_sale + $amount_trfin - $amount_trfout + $amount_op + $adj_amount_new - $retur_beli_amount + $retur_jual_amount - $disassembling_amount + $assembling_amount;

                    if($end_stok_new != 0){
                        $hpp = $end_amount_new / $end_stok_new;
                    }else{
                        $hpp = $tb_item_bulanan->hpp;
                        $end_amount_new = 0;
                    }

                    if($end_stok_new < 0){
                        exit();
                    }

                    $tgl_adj1 = $penyesuaian->tanggal;
                    $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_adj1)->year;
                    $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_adj1)->month;

                    $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                    $status_reopen = $reopen->reopen_status;

                    if($status_reopen == 'true'){
                        $tgl_adj = $penyesuaian->tanggal;
                        $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_adj)->year;
                        $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_adj)->month;

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
                            $adjustment = AdjustmentDetail::on($konek)->where('no_penyesuaian', $no_penyesuaian)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                            $hpp = $adjustment->harga;
                            $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                            $produk_adj = $adjustment->kode_produk;
                            $part_adj = $adjustment->partnumber;
                            $stock_adj = $data[$i]['qty']*$konversi->nilai_konversi;
                            if($stock_adj != 0){
                                $amount_adj = $hpp*$stock_adj;
                            }else{
                                $amount_adj = $hpp;
                            }

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

                                $begin_stock1 = $bs + $stock_adj;
                                $begin_amount1 = $ba + $amount_adj;
                                $end_stok1 = $es + $stock_adj;
                                $end_amount1 = $ea + $amount_adj;

                                if($end_stok1 != 0){
                                    $hpp = $end_amount1 / $end_stok1;
                                }else{
                                    $hpp = $tb_item_bulanan2->hpp;
                                    $end_amount1 = 0;
                                }

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

    function produkChecker2($no_penyesuaian, $tahun, $bulan, $tanggal_baru, $tgl, $penyesuaian, $koneksi)
    {
        $konek = self::konek();
        $adjustment = AdjustmentDetail::on($konek)->with('produk')->where('no_penyesuaian', request()->id)->get();
        $no_penyesuaian = request()->id;

        $data = array();

        if(!empty($adjustment)){
            foreach ($adjustment as $rowdata){
                $data[] = array(
                    'no_penyesuaian'=>$no_penyesuaian,
                    'kode_produk'=>$rowdata->kode_produk,
                    'kode_satuan'=>$rowdata->kode_satuan,
                    'qty'=>$rowdata->qty,
                    'partnumber'=>$rowdata->partnumber,
                    'harga'=>$rowdata->harga,
                );        
            }
        }

        if(!empty($adjustment)){
            $leng = count($adjustment);
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

                    $produk = Produk::on($konek)->find($data[$i]['kode_produk']);
                    $adjustmentdetail2 = AdjustmentDetail::on($konek)->where('no_penyesuaian', $no_penyesuaian)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                    $harga = $data[$i]['harga'];
                    $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                    $qty_baru = $data[$i]['qty']*$konversi->nilai_konversi;
                    $waktu = $tgl;
                    $barang = $data[$i]['kode_produk'];

                    $adj_stok_new = $stock_adjustment - $qty_baru;
                    if($qty_baru != 0){
                        $adj_amount_new = $amount_adjustment - ($harga * $qty_baru);
                    }else{
                        $adj_amount_new = $amount_adjustment - $harga;
                    }
                    $end_stok_new = $stock_begin + $stok_in - $stock_out - $stock_sale + $stock_trfin - $stock_trfout + $stock_op + $adj_stok_new - $retur_beli_stock + $retur_jual_stock - $disassembling_stock + $assembling_stock;
                    $end_amount_new = $amount_begin + $amount_masuk - $amount_keluar - $amount_sale + $amount_trfin - $amount_trfout + $amount_op + $adj_amount_new - $retur_beli_amount + $retur_jual_amount - $disassembling_amount + $assembling_amount;

                    if($end_stok_new != 0){
                        $hpp = $end_amount_new / $end_stok_new;
                    }else{
                        $hpp = $tb_item_bulanan->hpp;
                        $end_amount_new = 0;
                    }

                    if($end_stok_new < 0){
                        exit();
                    }

                    $tgl_adj1 = $penyesuaian->tanggal;
                    $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_adj1)->year;
                    $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_adj1)->month;

                    $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                    $status_reopen = $reopen->reopen_status;

                    if($status_reopen == 'true'){
                        $tgl_adj = $penyesuaian->tanggal;
                        $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_adj)->year;
                        $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_adj)->month;

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
                            $adjustment = AdjustmentDetail::on($konek)->where('no_penyesuaian', $no_penyesuaian)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                            $hpp = $adjustment->harga;
                            $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                            $stock_adj = $data[$i]['qty']*$konversi->nilai_konversi;
                            if($stock_adj != 0){
                                $amount_adj = $hpp*$stock_adj;
                            }else{
                                $amount_adj = $hpp;
                            }

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

                                $begin_stock1 = $bs - $stock_adj;
                                $begin_amount1 = $ba - $amount_adj;
                                $end_stok1 = $es - $stock_adj;
                                $end_amount1 = $ea - $amount_adj;

                                if($end_stok1 != 0){
                                    $hpp = $end_amount1 / $end_stok1;
                                }else{
                                    $hpp = $tb_item_bulanan2->hpp;
                                    $end_amount1 = 0;
                                }

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
        $cek_ar = Adjustment::on($konek)->where('no_journal', request()->no_journal)->first();

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
        
        $adjustment = Adjustment::on($konek)->find(request()->id);
        if($cek_bulan == null || $level == 'superadministrator' || $level == 'user_rince' || $level == 'user_thomas' || $level == 'rince_pbm' || $level == 'rince_emkl'){
            if($cek_company == '04' || $cek_company == '0401' || $cek_company == '03' || $cek_company == '05' || $cek_company == '06' || $cek_company == '02'){
                $adjustmentdetail = AdjustmentDetail::on($konek)->where('no_penyesuaian', request()->id)->get();
                $leng = count($adjustmentdetail);
                $data = array();

                $kat1 = 0;
                foreach ($adjustmentdetail as $rowdata){
                    $cek_produk = Produk::on($konek)->where('id', $rowdata->kode_produk)->first();
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
                        }else {
                            if ($adjustment->cost_center != null){
                                $cekcoa = Coa::find($cek_kategori->coabiaya_emkl);
                                if ($cekcoa->cost_center != 'Y'){
                                    $message = [
                                        'success' => false,
                                        'title' => 'Simpan',
                                        'message' => 'Status CC = [FALSE].',
                                    ];
                                    return response()->json($message);
                                }
                            }
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

            $penyesuaian = Adjustment::on($konek)->find(request()->id);
            $cek_status = $penyesuaian->status;
            if($cek_status != 'OPEN'){  
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'POST No. Adjustment: '.$penyesuaian->no_penyesuaian.' sudah dilakukan! Pastikan Anda tidak membuka menu ADJUSTMENT lebih dari 1',
                ];
                return response()->json($message);
            }

            $no_penyesuaian = $penyesuaian->no_penyesuaian;
            $crate_penyesuaian = $penyesuaian->created_at;
            $koneksi = $penyesuaian->kode_lokasi;

            $tgl = $penyesuaian->tanggal;
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

            // $validate_produk = $this->produkChecker($no_penyesuaian, $tahun, $bulan, $tanggal_baru, $tgl, $penyesuaian, $koneksi);
            $validate_produk = 'true';

            if($validate_produk == true){
                $adjustment = AdjustmentDetail::on($konek)->with('produk')->where('no_penyesuaian', request()->id)->get();
                $no_penyesuaian = request()->id;

                $data = array();
                foreach ($adjustment as $rowdata){
                    $data[] = array(
                        'no_penyesuaian'=>$no_penyesuaian,
                        'kode_produk'=>$rowdata->kode_produk,
                        'kode_satuan'=>$rowdata->kode_satuan,
                        'qty'=>$rowdata->qty,
                        'partnumber'=>$rowdata->partnumber,
                        'harga'=>$rowdata->harga,
                    );
                }

                if(!empty($adjustment)){
                    $leng = count($adjustment);
                    
                    //CHECK ENDING STOCK
                    $j = 0;
                    for($j = 0; $j < $leng; $j++){
                        $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$j]['kode_produk'])->where('kode_lokasi',$koneksi)->where('partnumber',$data[$j]['partnumber'])->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                        if ($tb_item_bulanan != null){
                            $produk = Produk::on($konek)->find($data[$j]['kode_produk']);
                            $adjustmentdetail2 = AdjustmentDetail::on($konek)->where('no_penyesuaian', $no_penyesuaian)->where('kode_produk',$data[$j]['kode_produk'])->where('partnumber',$data[$j]['partnumber'])->first();
                            $harga = $data[$j]['harga'];

                            $konversi = konversi::on($konek)->where('kode_produk',$data[$j]['kode_produk'])->where('kode_satuan',$data[$j]['kode_satuan'])->first();
                            $qty_baru = $data[$j]['qty'] * $konversi->nilai_konversi;

                            if($qty_baru <= 0 || $harga <= 0){
                                if($tb_item_bulanan->ending_stock == 0){
                                    $message = [
                                        'success' => false,
                                        'title' => 'Simpan',
                                        'message' => 'Adjustments minus tidak dapat dilakukan karena Ending Stock '.$produk->nama_produk.' = 0',
                                    ];
                                    return response()->json($message);
                                }
                            }
                        }
                    }

                    //UPDATE KE TABEL ITEM BULANAN
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
                            $amount_rpk = $tb_item_bulanan->retur_pakai_amount;
                            $stock_rpk = $tb_item_bulanan->retur_pakai_stock;

                            $produk = Produk::on($konek)->find($data[$i]['kode_produk']);

                            $adjustmentdetail2 = AdjustmentDetail::on($konek)->where('no_penyesuaian', $no_penyesuaian)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                            $harga = $data[$i]['harga'];

                            $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                            $qty_baru = $data[$i]['qty']*$konversi->nilai_konversi;

                            $waktu = $tgl;
                            $barang = $data[$i]['kode_produk'];

                            // if($qty_baru <= 0 || $harga <= 0){
                            //     if($tb_item_bulanan->ending_stock == 0){
                            //         $message = [
                            //             'success' => false,
                            //             'title' => 'Simpan',
                            //             'message' => 'Qty dan HPP tidak boleh kurang dari 0',
                            //         ];
                            //         return response()->json($message);
                            //     }
                            // }

                            $adj_stok_new = $stock_adjustment + $qty_baru;
                            if($qty_baru != 0){
                                $adj_amount_new = $amount_adjustment + ($harga * $qty_baru);
                            }else{
                                $adj_amount_new = $amount_adjustment + $harga;
                            }
                            $end_stok_new = $stock_begin + $stok_in - $stock_out - $stock_sale + $stock_trfin - $stock_trfout + $stock_op + $adj_stok_new - $retur_beli_stock + $retur_jual_stock - $disassembling_stock + $assembling_stock + $stock_rpk;
                            $end_amount_new = $amount_begin + $amount_masuk - $amount_keluar - $amount_sale + $amount_trfin - $amount_trfout + $amount_op + $adj_amount_new - $retur_beli_amount + $retur_jual_amount - $disassembling_amount + $assembling_amount + $amount_rpk;

                            if($end_stok_new != 0){
                                $hpp = $end_amount_new / $end_stok_new;
                            }else{
                                $hpp = $tb_item_bulanan->hpp;
                                $end_amount_new = 0;
                            }

                            $tabel_baru = [
                                'adjustment_stock'=>$adj_stok_new,
                                'adjustment_amount'=>$adj_amount_new,
                                'ending_stock'=>$end_stok_new,
                                'ending_amount'=>$end_amount_new,
                                'hpp'=>$hpp,
                            ];

                            $update_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$produk_awal)->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($tabel_baru);
                        }else {
                            $period = tb_akhir_bulan::on($konek)->where('status_periode','Open')->orwhere('reopen_status','true')->first();
                            $cekproduk = Produk::on($konek)->where('id', $data[$i]['kode_produk'])->first();
                            
                            $adj_amt = $data[$i]['qty']*$data[$i]['harga'];
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
                                'adjustment_stock'=>$data[$i]['qty'],
                                'adjustment_amount'=>$adj_amt,
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
                                'ending_stock'=>$data[$i]['qty'],
                                'ending_amount'=>$adj_amt,
                                'hpp'=>$data[$i]['harga'],
                                'kode_lokasi'=>$koneksi,
                                'kode_company'=>auth()->user()->kode_company,
                            ];
                            $update_item_bulanan = tb_item_bulanan::on($konek)->create($tabel_baru);

                            // Hitung ulang hpp
                            // $item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$koneksi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->get();
                            
                            // $tot_qty = collect($item_bulanan)->sum('ending_stock');
                            // $tot_amt = collect($item_bulanan)->sum('ending_amount');
                            // $tot_hpp = $tot_amt / $tot_qty;

                            // $tabel_baru = [
                            //         'hpp'=>$tot_hpp,
                            //     ];

                            // $update_hpp= tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$koneksi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($tabel_baru);
                        }

                        $tabel_history = [
                            'kode_produk'=>$data[$i]['kode_produk'],
                            'no_transaksi'=>$no_penyesuaian,
                            'tanggal_transaksi'=>$tgl,
                            'jam_transaksi'=>$crate_penyesuaian,
                            'qty_transaksi'=>$data[$i]['qty'],
                            'harga_transaksi'=>$data[$i]['harga'],
                            'total_transaksi'=>$data[$i]['qty']*$data[$i]['harga'],
                            'kode_lokasi'=>$koneksi,
                        ];

                        $update_produk_history = tb_produk_history::on($konek)->create($tabel_history);

                        $tgl_adj1 = $penyesuaian->tanggal;
                        $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_adj1)->year;
                        $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_adj1)->month;

                        $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                        $status_reopen = $reopen->reopen_status;

                        if($status_reopen == 'true'){
                            $tgl_adj = $penyesuaian->tanggal;
                            $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_adj)->year;
                            $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_adj)->month;

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
                                $adjustment = AdjustmentDetail::on($konek)->where('no_penyesuaian', $no_penyesuaian)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                                $hpp = $data[$i]['harga'];

                                $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                                $produk_adj = $adjustment->kode_produk;
                                $part_adj = $adjustment->partnumber;
                                $stock_adj = $data[$i]['qty']*$konversi->nilai_konversi;
                                if($stock_adj != 0){
                                    $amount_adj = $hpp*$stock_adj;
                                }else{
                                    $amount_adj = $hpp;
                                }

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

                                    $begin_stock1 = $bs + $stock_adj;
                                    $begin_amount1 = $ba + $amount_adj;

                                    $end_stok1 = $es + $stock_adj;
                                    $end_amount1 = $ea + $amount_adj;

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
                else{
                    alert()->success('Post', 'GAGAL!')->persistent('Close');
                    return redirect()->back();
                } 

                $penyesuaian = Adjustment::on($konek)->find(request()->id);
                $penyesuaian->status = "POSTED";
                $penyesuaian->save(); 

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Post No. Adjustment: '.$no_penyesuaian.'.','created_by'=>$nama,'updated_by'=>$nama];
                //dd($tmp);
                user_history::on($konek)->create($tmp);

                //UPDATE LEDGER JURNAL
                if($cek_company == '04' || $cek_company == '0401' || $cek_company == '03' || $cek_company == '05' || $cek_company == '06' || $cek_company == '02'){
                    $konek2 = self::konek2();
                    $cek_company = Auth()->user()->kode_company;
                    
                    if ($cek_company == '0401'){
                        $lokasi = 'JKT';
                    }else {
                        $lokasi = 'HO';
                    }
                    
                    $total_qty = 0;
                    $total_harga = 0;
                    $grand_total = 0;
                    
                    if ($cek_company == '04' || $cek_company == '0401'){
                        $compan = 'u5611458_gui_inventory_gut_laravel';
                    }else if ($cek_company == '03'){
                        $compan = 'u5611458_gui_inventory_emkl_laravel';
                    }else if ($cek_company == '02'){
                        $compan = 'u5611458_gui_inventory_pbm_laravel';
                    }else if ($cek_company == '05'){
                        $compan = 'u5611458_gui_inventory_sub_laravel';
                    }else if ($cek_company == '01'){
                        $compan = 'u5611458_gui_inventory_depo_laravel';
                    }else if ($cek_company == '06'){
                        $compan = 'u5611458_gui_inventory_pbminfra_laravel';
                    }
                    
                    $bulan = Carbon\Carbon::parse($penyesuaian->tanggal)->format('m');
                    $tahun = Carbon\Carbon::parse($penyesuaian->tanggal)->format('Y');
                    
                    // $detail = AdjustmentDetail::on($konek)->where('no_penyesuaian',$penyesuaian->no_penyesuaian)->get();
                    $detail = KategoriProduk::join($compan.'.produk','kategori_produk.kode_kategori','=',$compan.'.produk.kode_kategori')->join($compan.'.adjustments_detail',$compan.'.produk.id','=',$compan.'.adjustments_detail.kode_produk')->where($compan.'.adjustments_detail.no_penyesuaian', $penyesuaian->no_penyesuaian)->groupBy('kategori_produk.kode_kategori')->get();
                    foreach ($detail as $row){
                        $total_qty += $row->qty;
                        $subtotal = $row->harga * $row->qty;
                        $total_harga += $subtotal;
                        
                        $totalhpp = AdjustmentDetail::on($konek)->select(DB::raw('SUM('.$compan.'.adjustments_detail.qty *'.$compan.'.adjustments_detail.harga) as total'))->join($compan.'.produk',$compan.'.adjustments_detail.kode_produk','=',$compan.'.produk.id')->where($compan.'.adjustments_detail.no_penyesuaian', $penyesuaian->no_penyesuaian)->where($compan.'.produk.kode_kategori', $row->kode_kategori)->first();
                        $totalhpp = $totalhpp->total;
                        $grand_total = 0;
                        $grand_total += $totalhpp;
                        
                        $kategori = KategoriProduk::where('kode_kategori', $row->kode_kategori)->first();
                        
                        if ($cek_company == '04'){
                            $coa_inventory = Coa::where('kode_coa', $kategori->coa_gut)->first();
                            $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gut)->first();
                            $cc_inv = $kategori->cc_gut_persediaan;
                            $cc_biaya = $kategori->cc_gut;
                        }else if ($cek_company == '0401'){
                            $coa_inventory = Coa::where('kode_coa', $kategori->coa_gutjkt)->first();
                            $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gutjkt)->first();
                            $cc_inv = $kategori->cc_gutjkt_persediaan;
                            $cc_biaya = $kategori->cc_gutjkt;
                        }else if ($cek_company == '03'){
                            $coa_inventory = Coa::where('kode_coa', $kategori->coa_emkl)->first();
                            $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_emkl)->first();
                            $cc_inv = $kategori->cc_emkl_persediaan;
                            $cc_biaya = $kategori->cc_emkl;
                        }else if ($cek_company == '02'){
                            $coa_inventory = Coa::where('kode_coa', $kategori->coa_pbm)->first();
                            $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_pbm)->first();
                            $cc_inv = $kategori->cc_pbm_persediaan;
                            $cc_biaya = $kategori->cc_pbm;
                        }else if ($cek_company == '01'){
                            $coa_inventory = Coa::where('kode_coa', $kategori->coa_depo)->first();
                            $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_depo)->first();
                            $cc_inv = $kategori->cc_depo_persediaan;
                            $cc_biaya = $kategori->cc_depo;
                        }else if ($cek_company == '05'){
                            $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
                            $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_sub)->first();
                            $cc_inv = $kategori->cc_sub_persediaan;
                            $cc_biaya = $kategori->cc_sub;
                        }else if ($cek_company == '06'){
                            $coa_inventory = Coa::where('kode_coa', $kategori->coa_infra)->first();
                            $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_infra)->first();
                            $cc_inv = $kategori->cc_infra_persediaan;
                            $cc_biaya = $kategori->cc_infra;
                        }
                        
                        $cek_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
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
                                        $begin = $cek_setelah->beginning_balance - $grand_total;
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
                                        $begin = $cek_setelah->beginning_balance - $grand_total;
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

                        if($grand_total < 0){
                            $gt = abs($grand_total);
                            $update_ledger = [
                                'tahun'=>$tahun,
                                'periode'=>$bulan,
                                'account'=>$coa_inventory->account,
                                'cost_center'=>$cc_inv,
                                'no_journal'=>$penyesuaian->no_journal,
                                'journal_date'=>$penyesuaian->tanggal,
                                'db_cr'=>'K',
                                'reference'=>$penyesuaian->no_penyesuaian,
                                'kredit'=>$gt,
                                'kode_lokasi'=>$lokasi,
                            ];
                            $update = Ledger::on($konek2)->create($update_ledger);

                            $type = 'Inventory';
                            $transaksi = $penyesuaian;
                            $tgl_trans = $penyesuaian->tanggal;
                            $harga_acc = $grand_total;
                            $dbkr = 'K';
                            $update_accbalance = $this->accbalance_kredit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                            $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                        }else{
                            $update_ledger = [
                                'tahun'=>$tahun,
                                'periode'=>$bulan,
                                'account'=>$coa_inventory->account,
                                'cost_center'=>$cc_inv,
                                'no_journal'=>$penyesuaian->no_journal,
                                'journal_date'=>$penyesuaian->tanggal,
                                'db_cr'=>'D',
                                'reference'=>$penyesuaian->no_penyesuaian,
                                'debit'=>$grand_total,
                                'kode_lokasi'=>$lokasi,
                            ];
                            $update = Ledger::on($konek2)->create($update_ledger);

                            $type = 'Inventory';
                            $transaksi = $penyesuaian;
                            $tgl_trans = $penyesuaian->tanggal;
                            $harga_acc = $grand_total;
                            $dbkr = 'D';
                            $update_accbalance = $this->accbalance_debit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                            $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                        }
                        
                        $cek_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi', $lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
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
                                        $begin = $cek_setelah->beginning_balance + $grand_total;
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
                                        $begin = $cek_setelah->beginning_balance + $grand_total;
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

                        if($grand_total < 0){
                            $gt = abs($grand_total);
                            $update_ledger = [
                                'tahun'=>$tahun,
                                'periode'=>$bulan,
                                'account'=>$coa_biaya->account,
                                'cost_center'=>$cc_biaya,
                                'no_journal'=>$penyesuaian->no_journal,
                                'journal_date'=>$penyesuaian->tanggal,
                                'db_cr'=>'D',
                                'reference'=>$penyesuaian->no_penyesuaian,
                                'debit'=>$gt,
                                'kode_lokasi'=>$lokasi,
                            ];
                            $update = Ledger::on($konek2)->create($update_ledger);

                            $type = 'Inventory';
                            $transaksi = $penyesuaian;
                            $tgl_trans = $penyesuaian->tanggal;
                            $harga_acc = $grand_total;
                            $dbkr = 'D';
                            $update_accbalance = $this->accbalance_debit_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                            $update_lrb = $this->lrb_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                        }else{
                            $update_ledger = [
                                'tahun'=>$tahun,
                                'periode'=>$bulan,
                                'account'=>$coa_biaya->account,
                                'cost_center'=>$cc_biaya,
                                'no_journal'=>$penyesuaian->no_journal,
                                'journal_date'=>$penyesuaian->tanggal,
                                'db_cr'=>'K',
                                'reference'=>$penyesuaian->no_penyesuaian,
                                'kredit'=>$grand_total,
                                'kode_lokasi'=>$lokasi,
                            ];
                            $update = Ledger::on($konek2)->create($update_ledger);

                            $type = 'Inventory';
                            $transaksi = $penyesuaian;
                            $tgl_trans = $penyesuaian->tanggal;
                            $harga_acc = $grand_total;
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
        $lokasi = auth()->user()->kode_lokasi;

        if($cek_bulan == null || $level == 'superadministrator' || $level == 'user_rince' || $level == 'user_thomas' || $level == 'rince_emkl' || $level == 'rince_pbm'){
            $penyesuaian = Adjustment::on($konek)->find(request()->id);
            $cek_status = $penyesuaian->status;
            if($cek_status != 'POSTED'){  
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'UNPOST No. Adjustment: '.$penyesuaian->no_penyesuaian.' sudah dilakukan! Pastikan Anda tidak membuka menu ADJUSTMENT lebih dari 1',
                ];
                return response()->json($message);
            }

            $no_penyesuaian = $penyesuaian->no_penyesuaian;
            $koneksi = $penyesuaian->kode_lokasi;

            $tgl = $penyesuaian->tanggal;
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

            // $validate_produk = $this->produkChecker2($no_penyesuaian, $tahun, $bulan, $tanggal_baru, $tgl, $penyesuaian, $koneksi);
            $validate_produk = 'true';
            
            if($validate_produk == true){
                $adjustment = AdjustmentDetail::on($konek)->with('produk')->where('no_penyesuaian', request()->id)->get();
                $no_penyesuaian = request()->id;

                $data = array();

                if(!empty($adjustment)){
                    foreach ($adjustment as $rowdata){
                        $data[] = array(
                            'no_penyesuaian'=>$no_penyesuaian,
                            'kode_produk'=>$rowdata->kode_produk,
                            'kode_satuan'=>$rowdata->kode_satuan,
                            'qty'=>$rowdata->qty,
                            'partnumber'=>$rowdata->partnumber,
                            'harga'=>$rowdata->harga,
                        );         
                    }

                }

                if(!empty($adjustment)){
                    $leng = count($adjustment);

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

                            $adjustmentdetail2 = AdjustmentDetail::on($konek)->where('no_penyesuaian', $no_penyesuaian)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                            $harga = $data[$i]['harga'];

                            $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                            $qty_baru = $data[$i]['qty']*$konversi->nilai_konversi;

                            $waktu = $tgl;
                            $barang = $data[$i]['kode_produk'];

                            $adj_stok_new = $stock_adjustment - $qty_baru;
                            if($qty_baru != 0){
                                $adj_amount_new = $amount_adjustment - ($harga * $qty_baru);
                            }else{
                                $adj_amount_new = $amount_adjustment - $harga;
                            }

                            $end_stok_new = $stock_begin + $stok_in - $stock_out - $stock_sale + $stock_trfin - $stock_trfout + $stock_op + $adj_stok_new - $retur_beli_stock + $retur_jual_stock - $disassembling_stock + $assembling_stock + $rpk_stock;
                            $end_amount_new = $amount_begin + $amount_masuk - $amount_keluar - $amount_sale + $amount_trfin - $amount_trfout + $amount_op + $adj_amount_new - $retur_beli_amount + $retur_jual_amount - $disassembling_amount + $assembling_amount + $rpk_amount;

                            if($end_stok_new != 0){
                                $hpp = $end_amount_new / $end_stok_new;
                            }else{
                                $hpp = $tb_item_bulanan->hpp;
                                $end_amount_new = 0;
                            }

                            $tabel_baru = [
                                'adjustment_stock'=>$adj_stok_new,
                                'adjustment_amount'=>$adj_amount_new,
                                'ending_stock'=>$end_stok_new,
                                'ending_amount'=>$end_amount_new,
                                'hpp'=>$hpp,
                            ];

                            $update_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$produk_awal)->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($tabel_baru);

                            $update_produk_history = tb_produk_history::on($konek)->where('no_transaksi',$no_penyesuaian)->delete();

                            $tgl_adj1 = $penyesuaian->tanggal;
                            $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_adj1)->year;
                            $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_adj1)->month;

                            $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                            $status_reopen = $reopen->reopen_status;

                            if($status_reopen == 'true'){
                                $tgl_adj = $penyesuaian->tanggal;
                                $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_adj)->year;
                                $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_adj)->month;

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
                                    $adjustment = AdjustmentDetail::on($konek)->where('no_penyesuaian', $no_penyesuaian)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                                    $hpp = $data[$i]['harga'];

                                    $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                                    $produk_adj = $adjustment->kode_produk;
                                    $part_adj = $adjustment->partnumber;
                                    $stock_adj = $data[$i]['qty']*$konversi->nilai_konversi;
                                    if($stock_adj != 0){
                                        $amount_adj = $hpp*$stock_adj;
                                    }else{
                                        $amount_adj = $hpp;
                                    }

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

                                        $begin_stock1 = $bs - $stock_adj;
                                        $begin_amount1 = $ba - $amount_adj;

                                        $end_stok1 = $es - $stock_adj;
                                        $end_amount1 = $ea - $amount_adj;
                                        
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

                $penyesuaian = Adjustment::on($konek)->find(request()->id);
                $penyesuaian->status = "OPEN";
                $penyesuaian->save(); 

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Unpost No. Adjustment: '.$no_penyesuaian.'.','created_by'=>$nama,'updated_by'=>$nama];
                             //dd($tmp);
                user_history::on($konek)->create($tmp);

                $cek_company = Auth()->user()->kode_company;
                if($cek_company == '04' || $cek_company == '0401' || $cek_company == '03' || $cek_company == '05' || $cek_company == '06' || $cek_company == '02'){
                    $konek2 = self::konek2();

                    $get_ledger = Ledger::on($konek2)->where('no_journal',$penyesuaian->no_journal)->get();

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
                            $transaksi = $penyesuaian;
                            $tgl_trans = $penyesuaian->tanggal;
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
                            $transaksi = $penyesuaian;
                            $tgl_trans = $penyesuaian->tanggal;
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

                    $update_ledger = Ledger::on($konek2)->where('no_journal',$penyesuaian->no_journal)->delete();
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
            
            $adjustment = Adjustment::on($konek)->create($request->all());

            $no = Adjustment::on($konek)->orderBy('created_at','desc')->first();
            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Simpan No. Adjustment: '.$no->no_penyesuaian.'.','created_by'=>$nama,'updated_by'=>$nama];
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

    
    public function edit_adjustment()
    {
        $konek = self::konek();
        $no_penyesuaian = request()->id;
        $data = Adjustment::on($konek)->find($no_penyesuaian);
        $output = array(
            'no_penyesuaian'=> $data->no_penyesuaian,
            'tanggal'=> $data->tanggal,
            'kode_produk'=> $data->kode_produk,
            'partnumber'=> $data->partnumber,
            'harga'=> $data->harga,
            'jumlah'=> $data->jumlah,
            'keterangan'=> $data->keterangan,
        );
        return response()->json($output);
    }

    public function updateAdjusment(Request $request)
    {
        $konek = self::konek();
        $tgl = $request->tanggal;
        $jumlah = $request->jumlah;
        $validate = $this->periodeChecker($tgl);
        
        if($validate == true){
            $adjusment = Adjustment::on($konek)->find($request->no_penyesuaian)->update($request->all());

            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Edit No. Adjustment: '.$request->no_penyesuaian.'.','created_by'=>$nama,'updated_by'=>$nama];
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

    public function hapus_adjustment()
    {   
        $konek = self::konek();
        $level = auth()->user()->level;
        $no_penyesuaian = request()->id;
        $data = Adjustment::on($konek)->find($no_penyesuaian);
        $tgl = $data->tanggal;

        $validate = $this->periodeChecker($tgl);
                 
        if($validate == true){
            $data->delete();

            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Hapus No. Adjustment: '.$no_penyesuaian.'.','created_by'=>$nama,'updated_by'=>$nama];

            user_history::on($konek)->create($tmp);

            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data ['.$data->no_penyesuaian.'] telah dihapus.'
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
}
