<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Transfer;
use App\Models\TransferDetail;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Permintaan;
use App\Models\Produk;
use App\Models\Konversi;
use App\Models\satuan;
use App\Models\Company;
use App\Models\MasterLokasi;
use App\Models\tb_akhir_bulan;
use App\Models\tb_item_bulanan;
use App\Models\tb_item_bulanan2;
use App\Models\tb_produk_history;
use App\Models\user_history;
use App\Models\TransferIn;
use App\Models\TransferInDetail;
use App\Models\KategoriProduk;
use App\Models\Ledger;
use App\Models\Coa;
use App\Models\AccBalance;
use App\Models\Tb_acc_history;
use App\Models\Jurnal;
use App\Models\Labarugiberjalan;
use App\Models\Opname;
use App\Models\SetupFolder;
use Illuminate\Support\Facades\Storage;
use PDF;
use Excel;
use DB;
use Carbon;
use DateTime;

class TransferInController extends Controller
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

    public function index()
    {
        $konek = self::konek();
        $create_url = route('transferin.create');
        $Satuan= satuan::pluck('nama_satuan', 'kode_satuan');
        
        $Company= Company::pluck('nama_company','kode_company');
        $Lokasi= MasterLokasi::where('kode_lokasi', '<>', auth()->user()->kode_lokasi)->pluck('nama_lokasi','kode_lokasi');
        $asal = MasterLokasi::where('kode_lokasi', auth()->user()->kode_lokasi)->first();

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;
        
        $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_jalan2)->month;
        $Transfer = Transfer::on($konek)->where('status','POSTED')->where('kode_tujuan', auth()->user()->kode_lokasi)->pluck('no_transfer','no_transfer');

        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        $level = auth()->user()->level;
        
        return view('admin.transferin.index',compact('create_url','Company','Satuan','period','asal','Lokasi','Transfer', 'nama_lokasi','nama_company'));
    }

    public function anyData()
    {
        $konek = self::konek();
        $lokasi = auth()->user()->kode_lokasi;
        if($lokasi == 'HO'){
            return Datatables::of(TransferIn::on($konek)->with('company','Lokasi')->withCount('transferindetail')->orderBy('created_at','desc'))->make(true);
        }
        else{
            return Datatables::of(TransferIn::on($konek)->with('company','Lokasi')->withCount('transferindetail')->where('kode_lokasi', auth()->user()->kode_lokasi)->orderBy('created_at','desc'))->make(true);
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

    public function detail($transferin)
    {
        $konek = self::konek();
        $transferin = TransferIn::on($konek)->find($transferin);
        $tanggal = $transferin->tanggal_transfer;
        $no_transferin = $transferin->no_transfer;

        $validate = $this->periodeChecker($tanggal);

        if($validate == true){
            $data = Transfer::on($konek)->find($no_transferin);

            $total_qty = 0;
            $total_harga = 0;
            $grand_total = 0;

            $transferindetail = TransferInDetail::on($konek)->with('produk','satuan')->where('no_trf_in', $transferin->no_trf_in)
            ->orderBy('created_at','desc')->get();

            foreach ($transferindetail as $row){
                $total_qty += $row->qty;
                $subtotal = $row->hpp * $row->qty;
                $total_harga += $subtotal;
                $grand_total = number_format($total_harga,2,",",".");
            }

            $Produk = TransferDetail::on($konek)->where('no_transfer', $data->no_transfer)
            ->leftJoin('produk', 'transfer_detail.kode_produk', '=', 'produk.id')
            ->pluck('produk.nama_produk','produk.id','transfer_detail.qty');

            $Satuan = Satuan::pluck('nama_satuan','kode_satuan');
            $list_url= route('transferin.index');

            $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
            $tgl_jalan2 = $tgl_jalan->periode;
            $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
            $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
            $nama_lokasi = $get_lokasi->nama_lokasi;

            $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
            $nama_company = $get_company->nama_company;

            return view('admin.transferindetail.index', compact('transferin','transferindetail','list_url','Produk','Satuan','total_qty','grand_total','period', 'nama_lokasi','nama_company'));
        }
        else{
            alert()->success('Status POSTED / Periode Telah CLOSED: '.$tanggal,'GAGAL!')->persistent('Close');
            return redirect()->back();
        }
    }

    public function cetakPDF(){ 
        $konek = self::konek();
        $request = $_GET['no_transferin'];

        $total_qty = 0;
        $total_harga = 0;
        $grand_total = 0;

        $transferin = TransferIn::on($konek)->find($request);
        $user = $transferin->created_by;

        $kode_company = $transferin->kode_company;
        $company = Company::where('kode_company',$kode_company)->first();
        $nama_company = $company->nama_company;

        $dt = Carbon\Carbon::now();
        $date_now = Carbon\Carbon::parse($dt)->format('d/m/Y H:m:s');

        $tgl = $transferin->tanggal_transfer;
        $date=date_create($tgl);

        $keterangan = substr($transferin->keterangan, 0, 70);

        $transferindetail= TransferInDetail::on($konek)->with('produk','satuan')->where('no_trf_in',$transferin->no_trf_in)->get();
        
        foreach ($transferindetail as $row){
            $total_qty += $row->qty;
            $subtotal = $row->hpp * $row->qty;
            $total_harga += $subtotal;
            $grand_total = number_format($total_harga,2,",",".");
        }
        
        $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
        $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
        
        $namapdf = substr($transferin->no_trf_in,0,5).$transferin->kode_lokasi.'-'.$transferin->kode_dari.substr($transferin->no_trf_in,5,9).'.pdf';
        
        $setupfolder = SetupFolder::find(10);
        $tes_save = $company->kode_company.". ".$company->nama_company."/".$setupfolder->folder."/".$setupfolder->subfolder."/".$tahun."/".$bulan."/".$namapdf;

        $jumlah=count($transferindetail);
        $pdf = PDF::loadView('/admin/transferin/pdf', compact('transferin','transferindetail','jumlah','total_qty','grand_total','nama_company','date_now','keterangan','tgl','user'));
        $pdf->setPaper([0, 0, 684, 792], 'potrait');
        
        $nama = auth()->user()->name;
        $tmp = ['nama' => $nama,'aksi' => 'Print No. Transfer In : '.$transferin->no_trf_in.'.','created_by'=>$nama,'updated_by'=>$nama];
        user_history::on($konek)->create($tmp);
        
        Storage::disk('ftp')->put($tes_save, $pdf->output());
        return $pdf->stream(substr($transferin->no_trf_in,0,5).$transferin->kode_lokasi.'-'.$transferin->kode_dari.'-'.substr($transferin->no_trf_in,5).'.pdf');
    }


    public function exportPDF2(){
        $konek = self::konek();
        $konek2 = self::konek2();
        $request = $_GET['no_transferin'];
        $no_journal = $_GET['no_journal'];

        $transfer = TransferIn::on($konek)->find($request);
        $jur = $transfer->kode_jurnal;
        $jurnal = Jurnal::find($jur);

        $total_qty = 0;
        $total_harga = 0;
        $grand_total = 0;
        $detail = TransferInDetail::on($konek)->where('no_trf_in',$request)->get();
        foreach ($detail as $row){
            $total_qty += $row->qty;
            $subtotal = $row->hpp * $row->qty;
            $total_harga += $subtotal;
            $grand_total = $total_harga;
        }

        $ledger2 = Ledger::on($konek2)->with('coa')->where('no_journal',$no_journal)->first();

        $ledger = Ledger::on($konek2)->select('ledger.*','coa.ac_description')->join('u5611458_gui_general_ledger_laravel.coa','u5611458_gui_general_ledger_laravel.coa.account','=','ledger.account')->where('no_journal', $no_journal)->get();

        $user = $transfer->created_by;
        $tgl = $transfer->tanggal_transfer;
        $date=date_create($tgl);

        $ttd = $user;

        $get_lokasi = auth()->user()->kode_lokasi;
        $get_company = auth()->user()->kode_company;

        $nama_lokasi = MasterLokasi::find($get_lokasi);
        $nama = $nama_lokasi->nama_lokasi;

        $company = Company::find($get_company);
        $nama2 = $company->nama_company;

        $dt = Carbon\Carbon::now();
        $date_now = Carbon\Carbon::parse($dt)->format('d/m/Y H:m:s');
        $journal_date = Carbon\Carbon::parse($ledger2->journal_date)->format('d/m/Y');

        $pdf = PDF::loadView('/admin/transferin/pdf2', compact('transfer','request', 'jurnal','tgl','date', 'ttd','date_now','ledger','ledger2','dt','user','nama','nama2','journal_date','grand_total'));
        $pdf->setPaper([0, 0, 684, 792], 'potrait');
        
        $nama = auth()->user()->name;
        $tmp = ['nama' => $nama,'aksi' => 'Print Zoom Jurnal : '.$request.'.','created_by'=>$nama,'updated_by'=>$nama];
        user_history::on($konek)->create($tmp);
        
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


    public function getnama()
    {   
        $lokasi = MasterLokasi::find(request()->lokasi);

        $output = array(
            'nama_lokasi'=>$lokasi->nama_lokasi,
        );

        return response()->json($output);
    }


    public function Showdetail()
    {
        $konek = self::konek();
        $total_qty = 0;
        $total_harga = 0;
        $grand_total = 0;
        $transferindetail= TransferInDetail::on($konek)->with('produk','satuan')->where('no_trf_in',request()->id)
        ->orderBy('created_at', 'desc')->get();

        $output = array();

        foreach ($transferindetail as $row){
            $total_qty += $row->qty;
            $subtotal = $row->hpp * $row->qty;
            $total_harga += $subtotal;
            $grand_total = number_format($total_harga,2,",",".");
        }

        if($transferindetail){
            foreach($transferindetail as $row)
            {
                $output[] = array(
                    'no_trf_in'=>$row->no_trf_in,
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
            if($stat == 'Open' && $re_stat == 'false' || $re_stat == 'true')
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


    function produkChecker($no_transferin, $tahun, $bulan, $tanggal_baru, $tgl, $transferin, $koneksi)
    {   
        $konek = self::konek();
        $transferindetail = TransferInDetail::on($konek)->with('produk')->where('no_trf_in', request()->id)->get();
        $no_transferin = request()->id;

        $data = array();

        if(!empty($transferindetail)){
            foreach ($transferindetail as $rowdata){
                $data[] = array(
                    'no_transfer'=>$no_transferin,
                    'kode_produk'=>$rowdata->kode_produk,
                    'qty'=>$rowdata->qty,
                    'partnumber'=>$rowdata->partnumber,
                    'hpp'=>$rowdata->hpp,
                );
            }
        }

        $tabel_baru = array();
        $tabel_baru2 = array();
        $tabel_history = array();
        $tabel_kirim = array();

        if(!empty($transferindetail)){
            $transferin = TransferIn::on($konek)->find(request()->id);

            $leng = count($transferindetail);
            $i = 0;

            for($i = 0; $i < $leng; $i++){
                //SIMPAN DETAIL TRANSFER IN
                $nomor = TransferIn::on($konek)->where('no_trf_in', request()->id)->first();
                $total = $data[$i]['qty'] * $data[$i]['hpp'];

                //UPDATE STOK TABEL PRODUK DAN ITEM BULANAN LOKAL
                $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi', $koneksi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();


                $ending_stock = $tb_item_bulanan->ending_stock;
                $ending_amount = $tb_item_bulanan->ending_amount;
                $trf_in = $tb_item_bulanan->trf_in - $data[$i]['qty'];
                $trf_in_amount = $tb_item_bulanan->trf_in_amount - $total;

                $end_stok = $ending_stock - $data[$i]['qty'];
                $end_amount = $ending_amount - ($data[$i]['qty'] * $data[$i]['hpp']);
                if ($end_stok != 0){
                    $hpp = $end_amount / $end_stok;
                }else {
                    $hpp = 0;
                }

                if($end_stok < 0){
                    exit();
                }

                $tgl_trfin1 = $transferin->tanggal_transfer;
                $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_trfin1)->year;
                $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_trfin1)->month;

                $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                $status_reopen = $reopen->reopen_status;

                if ($status_reopen == 'true'){
                    $tgl_trfin = $transferin->tanggal_transfer;
                    $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_trfin)->year;
                    $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_trfin)->month;

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

                        $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi', $koneksi)->whereMonth('periode', $bulan2)->whereYear('periode', $tahun2)->first();

                        if ($tb_item_bulanan2 != null){
                            $ending_stock = $tb_item_bulanan2->ending_stock;
                            $ending_amount = $tb_item_bulanan2->ending_amount;
                            $begin_stock = $tb_item_bulanan2->begin_stock;
                            $begin_amount = $tb_item_bulanan2->begin_amount;

                            $end_stok = $ending_stock - $data[$i]['qty'];
                            $end_amount = $ending_amount - $data[$i]['hpp'];
                            $begin_stok = $begin_stock - $data[$i]['qty'];
                            $begin_amt = $begin_amount - $data[$i]['hpp'];
                            if ($end_stok != 0){
                                $hpp = $end_amount / $end_stok;
                            }else {
                                $hpp = 0;
                            }

                            if($end_stok < 0){
                                exit();
                            }

                        }
                        $j++;
                    }
                }
            }
        }
        return true;
    }


    public function getDatajurnal2(){
        $konek2 = self::konek2();
        if (substr(request()->id,4,4) == '0401') {
            $konek2 = 'mysql_finance_gutjkt';
        }else {
            $konek2 = self::konek2();
        }
        $data = Ledger::on($konek2)->select('ledger.*','u5611458_gui_general_ledger_laravel.coa.ac_description')->join('u5611458_gui_general_ledger_laravel.coa','u5611458_gui_general_ledger_laravel.coa.account','=','ledger.account')->where('ledger.no_journal',request()->id)->orderBy('ledger.created_at','desc')->get();
        return response()->json($data);
    }

    public function cekjurnal2()
    {
        $konek = self::konek();
        $konek2 = self::konek2();
        if (substr(request()->no_journal,4,4) == '0401') {
            $konek2 = 'mysql_finance_gutjkt';
        }else {
            $konek2 = self::konek2();
        }
        dd(request()->no_journal);
        $cek = Ledger::on($konek2)->where('no_journal', request()->no_journal)->first();
        $cek_ar = TransferIn::on($konek)->where('no_journal', request()->no_journal)->first();

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
        // dd($cek_bulan);
        
        $todays = Carbon\Carbon::now();
        $today = $todays->toDateString();
        $bans = TransferIn::on($konek)->find(request()->id);
        if ($bans->tanggal_transfer != $today) {
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Tanggal hari ini: '.$today.' Tanggal Transfer berbeda, posting hanya dapat dilakukan di hari yang sama.',
            ];
            return response()->json($message);
        }
        
        
        if($cek_bulan == null || $level == 'superadministrator' || $level == 'user_rince' || $level == 'user_thomas'){
            if($cek_company == '04' && $bans->kode_dari == 'JKT'|| $cek_company == '0401' && $bans->kode_dari == 'HO'){
                $transferdetail = TransferInDetail::on($konek)->where('no_trf_in', request()->id)->get();
                $leng = count($transferdetail);
                $data = array();

                foreach ($transferdetail as $rowdata){
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

            $transferin = TransferIn::on($konek)->find(request()->id);
            $cek_status = $transferin->status;
            if($cek_status != 'OPEN'){  
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'POST No. Transfer In: '.$transferin->no_trf_in.' sudah dilakukan! Pastikan Anda tidak membuka menu TRANSFER IN lebih dari 1',
                ];
                return response()->json($message);
            }

            $koneksi = $transferin->kode_lokasi;
            $lokasi = $transferin->kode_lokasi;
            $kode_dari = $transferin->kode_dari;
            $no_transferin = $transferin->no_trf_in;
            $no_transferout = $transferin->no_transfer;
            $create_transferin = $transferin->created_at;
            $cek_trf = TransferIn::on($konek)->where('no_trf_in', $transferin->no_trf_in)->first();
            $cek_count = TransferDetail::on($konek)->where('no_transfer',$transferin->no_transfer)->get();
            $leng = count($cek_count);
            
            
            
            if ($cek_trf->total_item < $leng){
                $message = [
                    'success' => false,
                    'title' => 'Update',
                    'message' => 'Semua barang yang di transfer harus di input.'
                ];
                return response()->json($message);
            }
            
            foreach ($cek_count as $row){
                $indetail = TransferInDetail::on($konek)->where('no_trf_in', request()->id)->where('kode_produk', $row->kode_produk)->first();
                if ($indetail != null){
                    if ($indetail->qty > $row->qty) {
                        $message = [
                            'success' => false,
                            'title' => 'Update',
                            'message' => 'Sesuaikan qty produk ['.$indetail->kode_produk.'] dengan transfer out.'
                        ];
                        return response()->json($message);
                    }
                }else {
                    $message = [
                        'success' => false,
                        'title' => 'Update',
                        'message' => 'Semua barang yang di transfer harus di input.'
                    ];
                    return response()->json($message);
                }
                       
            }
            
            $detail_in = TransferInDetail::on($konek)->where('no_trf_in', request()->id)->get();
            foreach ($detail_in as $row){
                $cek_produk = TransferDetail::on($konek)->where('no_transfer',$transferin->no_transfer)->where('kode_produk', $row->kode_produk)->first();
                if ($cek_produk == null) {
                    $message = [
                        'success' => false,
                        'title' => 'Update',
                        'message' => 'Sesuaikan kode produk dengan Transfer Out !!!.'
                    ];
                    return response()->json($message);
                }
            }

            $tgl = $transferin->tanggal_transfer;
            $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
            $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
            $tanggal = '01';

            $tb_akhir_bulan = tb_akhir_bulan::on($konek)->where('status_periode','Open')->first();
            $tanggal_baru = Carbon\Carbon::createFromDate($tahun, $bulan, $tanggal)->toDateString();
            $transferindetail = TransferInDetail::on($konek)->where('no_trf_in', request()->id)->first();
            if ($transferindetail == null){
                $message = [
                    'success' => false,
                    'title' => 'Update',
                    'message' => 'Detail Transfer blm di input.'
                ];
                return response()->json($message);
            }
            
            $bans->status = 'ONGOING';
            $bans->save();

            $validate = $this->periodeChecker($tgl);
            if($validate == true){
                $transferindetail = TransferInDetail::on($konek)->with('produk')->where('no_trf_in', request()->id)->orderBy('id','asc')->get();
                if ($transferindetail == null){
                    $message = [
                        'success' => false,
                        'title' => 'Update',
                        'message' => 'Detail Transfer blm di input.'
                    ];
                    return response()->json($message);
                }

                $no_transferin = request()->id;

                $data = array();

                if(!empty($transferindetail)){
                    foreach ($transferindetail as $rowdata){
                        $data[] = array(
                           'id'=>$rowdata->id,
                           'no_trf_in'=>$no_transferin,
                           'kode_produk'=>$rowdata->kode_produk,                    
                           'qty'=>$rowdata->qty,
                           'partnumber'=>$rowdata->partnumber,
                           'no_mesin'=>$rowdata->no_mesin,
                           'hpp'=>$rowdata->hpp,
                        );        
                    }
                }

                $tabel_baru = array();
                $tabel_baru2 = array();
                $tabel_history = array();
                $tabel_kirim = array();                             
                
                $transferin->status = 'ONGOING1';
                $transferin->save();

                if(!empty($transferindetail)){
                    $transferin = TransferIn::on($konek)->find(request()->id);

                    $leng = count($transferindetail);
                    $i = 0;

                    for($i = 0; $i < $leng; $i++){
                        //UPDATE KE TABEL ITEM BULANAN LOKASI TUJUAN
                        $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi', $koneksi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
    
                        if ($tb_item_bulanan2 == null){
                            $trf_in = $data[$i]['qty'];
                            $trf_in_amt = $data[$i]['hpp'] * $data[$i]['qty'];

                            $tabel_baru2 = [
                                'periode'=>$tanggal_baru,
                                'kode_produk'=>$data[$i]['kode_produk'],
                                'partnumber'=>$data[$i]['partnumber'],
                                'no_mesin'=>$data[$i]['no_mesin'],
                                'begin_stock'=>0,
                                'begin_amount'=>0,
                                'in_stock'=>0,
                                'in_amount'=>0,
                                'out_stock'=>0,
                                'out_amount'=>0,
                                'sale_stock'=>0,
                                'sale_amount'=>0,
                                'trf_in'=>$trf_in,
                                'trf_in_amount'=>$trf_in_amt,
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
                                'disassembling_stock'=>0,
                                'disassembling_amount'=>0,
                                'assembling_stock'=>0,
                                'assembling_amount'=>0,
                                'ending_stock'=>$trf_in,
                                'ending_amount'=>$trf_in_amt,
                                'hpp'=>$data[$i]['hpp'],
                                'kode_lokasi'=>$koneksi,
                                'kode_company'=>auth()->user()->kode_company,
                            ];
                            $insert_item_bulanan = tb_item_bulanan::on($konek)->create($tabel_baru2);

                        } else {
                            $ending_stock = $tb_item_bulanan2->ending_stock;
                            $ending_amount = $tb_item_bulanan2->ending_amount;
                            $trf_in = $tb_item_bulanan2->trf_in + $data[$i]['qty'];
                            $trf_in_amount = $tb_item_bulanan2->trf_in_amount + ($data[$i]['hpp'] * $data[$i]['qty']);

                            $end_stok = $ending_stock + $data[$i]['qty'];
                            $end_amount = $ending_amount + ($data[$i]['hpp'] * $data[$i]['qty']);

                            if($end_stok != 0){
                                $hpp = $end_amount / $end_stok;
                            }else{
                                $hpp = $tb_item_bulanan2->hpp;
                                $end_amount = 0;
                            }

                            $tabel_baru2 = [
                                'partnumber'=>$data[$i]['partnumber'],
                                'ending_stock'=>$end_stok,
                                'ending_amount'=>$end_amount,
                                'trf_in'=>$trf_in,
                                'trf_in_amount'=>$trf_in_amount,
                                'hpp'=>$hpp,
                            ];

                            $update_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi',$koneksi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($tabel_baru2);
                        }

                        //UPDATE KE TRANSFER OUT TABEL ITEM BULANAN LOKASI ASAL
                        $tb_item_bulanan3 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi', $kode_dari)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

                        $total = $data[$i]['qty'] * $data[$i]['hpp'];
    
                        $ending_stock = $tb_item_bulanan3->ending_stock;
                        $ending_amount = $tb_item_bulanan3->ending_amount;
                        $trf_out = $tb_item_bulanan3->trf_out + $data[$i]['qty'];
                        $trf_out_amount = $tb_item_bulanan3->trf_out_amount + $total;
                        $end_stok3 = $ending_stock - $data[$i]['qty'];
                        $end_amount3 = $ending_amount - ($data[$i]['qty'] * $data[$i]['hpp']);

                        if($end_stok3 > 0){
                            $hpp3 = $end_amount3 / $end_stok3;
                        }else{
                            $hpp3 = $tb_item_bulanan3->hpp;
                            $end_amount3 = 0;
                        }

                        $tabel_baru3 = [
                            'ending_stock'=>$end_stok3,
                            'ending_amount'=>$end_amount3,
                            'trf_out'=>$trf_out,
                            'trf_out_amount'=>$trf_out_amount,
                            'hpp'=>$hpp3,
                        ];

                        $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi',$kode_dari)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($tabel_baru3);

    
                            $tgl_trfin1 = $transferin->tanggal_transfer;
                            $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_trfin1)->year;
                            $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_trfin1)->month;
    
                            $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                            $status_reopen = $reopen->reopen_status;
    
                            if ($status_reopen == 'true'){
                                $tgl_trfin = $transferin->tanggal_transfer;
                                $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_trfin)->year;
                                $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_trfin)->month;
    
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
    
                                    $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi', $koneksi)->whereMonth('periode', $bulan2)->whereYear('periode', $tahun2)->first();
    
                                    if ($tb_item_bulanan2 != null){
                                        $ending_stock = $tb_item_bulanan2->ending_stock;
                                        $ending_amount = $tb_item_bulanan2->ending_amount;
                                        $begin_stock = $tb_item_bulanan2->begin_stock;
                                        $begin_amount = $tb_item_bulanan2->begin_amount;
    
                                        $end_stok = $ending_stock + $data[$i]['qty'];
                                        $end_amount = $ending_amount + ($data[$i]['qty'] * $data[$i]['hpp']);
                                        $bgn_stok = $begin_stock + $data[$i]['qty'];
                                        $bgn_amount = $begin_amount + ($data[$i]['qty'] * $data[$i]['hpp']);
    
                                        if($end_stok != 0){
                                            $hpp = $end_amount / $end_stok;
                                        }else{
                                            $hpp = $tb_item_bulanan2->hpp;
                                            $end_amount = 0;
                                        }
    
                                        $tabel_baru2 = [
                                            'partnumber'=>$data[$i]['partnumber'],
                                            'ending_stock'=>$end_stok,
                                            'ending_amount'=>$end_amount,
                                            'begin_stock'=>$bgn_stok,
                                            'begin_amount'=>$bgn_amount,
                                            'hpp'=>$hpp,
                                        ];
    
                                        $update_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi',$koneksi)->whereMonth('periode', $bulan2)->whereYear('periode', $tahun2)->update($tabel_baru2);
                                    } else {
                                        $tanggal_buka = '01';
                                        $bulan_buka = $bulan2;
                                        $tahun_buka = $tahun2;
    
                                        $tanggal_baru2 = Carbon\Carbon::createFromDate($tahun_buka, $bulan_buka, $tanggal_buka)->toDateString();
    
                                        $trf_in = $data[$i]['qty'];
                                        $trf_in_amt = $data[$i]['qty']*$data[$i]['hpp'];
    
                                        $tabel_baru2 = [
                                            'periode'=>$tanggal_baru2,
                                            'kode_produk'=>$data[$i]['kode_produk'],
                                            'partnumber'=>$data[$i]['partnumber'],
                                            'no_mesin'=>$data[$i]['no_mesin'],
                                            'begin_stock'=>$trf_in,
                                            'begin_amount'=>$trf_in_amt,
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
                                            'disassembling_stock'=>0,
                                            'disassembling_amount'=>0,
                                            'assembling_stock'=>0,
                                            'assembling_amount'=>0,
                                            'ending_stock'=>$trf_in,
                                            'ending_amount'=>$trf_in_amt,
                                            'hpp'=>$data[$i]['hpp'],
                                            'kode_company'=>auth()->user()->kode_company,
                                            'kode_lokasi'=>$koneksi,
                                        ];
                                        $insert_item_bulanan = tb_item_bulanan::on($konek)->create($tabel_baru2);
                                    }
                                    $j++;
                                }
                            }
    
                            $tabel_history = [
                                'kode_produk'=>$data[$i]['kode_produk'],
                                'no_transaksi'=>request()->id,
                                'tanggal_transaksi'=>$tgl,
                                'kode_lokasi'=>$koneksi,
                                'jam_transaksi'=>$tgl,
                                'qty_transaksi'=>$data[$i]['qty'],
                                'harga_transaksi'=>$data[$i]['hpp'],
                                'total_transaksi'=>($data[$i]['hpp'] * $data[$i]['qty']),
                                'kode_lokasi'=>$koneksi,
                            ];
                            $tb_produk_history = tb_produk_history::on($konek)->create($tabel_history);
                        
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

            $transferin = TransferIn::on($konek)->find(request()->id);
            $transferin->status = "POSTED";
            $transferin->save();

            $transfer = Transfer::on($konek)->where('no_transfer',$transferin->no_transfer)->first();
            $transfer->status = "CLOSED";
            $transfer->save();

            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Post No. Transfer In: '.$no_transferin.'.','created_by'=>$nama,'updated_by'=>$nama];

            user_history::on($konek)->create($tmp);          


            //UPDATE JURNAL
            if($cek_company == '04' && $bans->kode_dari == 'JKT'|| $cek_company == '0401' && $bans->kode_dari == 'HO'){
                $konek2 = self::konek2();
                $cek_company = Auth()->user()->kode_company;

                $total_qty = 0;
                $total_harga = 0;
                $totalhpp = 0;
                $grand_total = 0;
                // $detail = TransferInDetail::on($konek)->where('no_trf_in',$transferin->no_trf_in)->get();
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
                
                $detail = KategoriProduk::join($compan.'.produk','kategori_produk.kode_kategori','=',$compan.'.produk.kode_kategori')->join($compan.'.transfer_in_detail',$compan.'.produk.id','=',$compan.'.transfer_in_detail.kode_produk')->where($compan.'.transfer_in_detail.no_trf_in', $no_transferin)->groupBy('kategori_produk.kode_kategori')->get();
                foreach ($detail as $row){
                    $total_qty += $row->qty;
                    $subtotal = $row->hpp * $row->qty;
                    $total_harga += $subtotal;
                    
                    $totalhpp = TransferInDetail::on($konek)->select(DB::raw('SUM('.$compan.'.transfer_in_detail.qty *'.$compan.'.transfer_in_detail.hpp) as total'))->join($compan.'.produk',$compan.'.transfer_in_detail.kode_produk','=',$compan.'.produk.id')->where($compan.'.transfer_in_detail.no_trf_in', $no_transferin)->where($compan.'.produk.kode_kategori', $row->kode_kategori)->first();
                    $totalhpp = $totalhpp->total;
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
                                    $begin = $cek_setelah->beginning_balance + $totalhpp;
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
                                    $begin = $cek_setelah->beginning_balance + $totalhpp;
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

                    $update_ledger = [
                        'tahun'=>$tahun,
                        'periode'=>$bulan,
                        'account'=>$coa_inventory->account,
                        'cost_center'=>$cc_inv,
                        'no_journal'=>$transferin->no_journal,
                        'journal_date'=>$transferin->tanggal_transfer,
                        'db_cr'=>'D',
                        'reference'=>$transferin->no_trf_in,
                        'debit'=>$totalhpp,
                        'kode_lokasi'=>$lokasi,
                    ];
                    $update = Ledger::on($konek2)->create($update_ledger);

                    $type = 'Inventory';
                    $transaksi = $transferin;
                    $tgl_trans = $transferin->tanggal_transfer;
                    $harga_acc = $totalhpp;
                    $dbkr = 'D';
                    $update_accbalance = $this->accbalance_debit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                    $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                }

                if($cek_company == '0401'){
                    $coa_hutangpusat = Coa::where('kode_coa', '178')->first();
                    $cek_balance2 = AccBalance::on($konek2)->where('account',$coa_hutangpusat->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                    if ($cek_balance2 == null) {
                        //CEK SEBELUM
                        $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_hutangpusat->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                        if($cek_sebelum != null){
                            $update_acc = [
                                'periode'=>$tanggal_baru,
                                'fiscalyear'=>$tahun,
                                'account'=>$coa_hutangpusat->account,
                                'beginning_balance'=>$cek_sebelum->ending_balance,
                                'debet'=>0,
                                'kredit'=>0,
                                'ending_balance'=>$cek_sebelum->ending_balance,
                                'kode_lokasi'=>$lokasi,
                            ];

                            $update_balance = AccBalance::on($konek2)->where('account',$coa_hutangpusat->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                        }else{
                            $update_acc = [
                                'periode'=>$tanggal_baru,
                                'fiscalyear'=>$tahun,
                                'account'=>$coa_hutangpusat->account,
                                'beginning_balance'=>0,
                                'debet'=>0,
                                'kredit'=>0,
                                'ending_balance'=>0,
                                'kode_lokasi'=>$lokasi,
                            ];

                            $update_balance = AccBalance::on($konek2)->where('account',$coa_hutangpusat->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                        }

                        //CEK SETELAH
                        $i = $bulan;
                        $cek_setelah = AccBalance::on($konek2)->where('account',$coa_hutangpusat->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                        if ($cek_setelah != null) {
                            for($i = $bulan; $i <= 12; $i++){
                                $cek_setelah = AccBalance::on($konek2)->where('account',$coa_hutangpusat->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                if($cek_setelah != null){
                                    $begin = $cek_setelah->beginning_balance + $grand_total;
                                    $debit = $cek_setelah->debet;
                                    $kredit = $cek_setelah->kredit;
                                    if($coa_hutangpusat->normal_balance == 'D'){
                                        $ending_balance = $begin + $debit - $kredit;
                                    }else{
                                        $ending_balance = $begin - $debit + $kredit;
                                    }

                                    $tabel_baru = [
                                        'beginning_balance'=>$begin,
                                        'ending_balance'=>$ending_balance,
                                    ];

                                    $update_balance = AccBalance::on($konek2)->where('account',$coa_hutangpusat->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                }
                            }
                        }
                    }else{
                        //CEK SETELAH
                        $i = $bulan;
                        $cek_setelah = AccBalance::on($konek2)->where('account',$coa_hutangpusat->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                        if ($cek_setelah != null) {
                            for($i = $bulan; $i <= 12; $i++){
                                $cek_setelah = AccBalance::on($konek2)->where('account',$coa_hutangpusat->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                if($cek_setelah != null){
                                    $begin = $cek_setelah->beginning_balance + $grand_total;
                                    $debit = $cek_setelah->debet;
                                    $kredit = $cek_setelah->kredit;
                                    if($coa_hutangpusat->normal_balance == 'D'){
                                        $ending_balance = $begin + $debit - $kredit;
                                    }else{
                                        $ending_balance = $begin - $debit + $kredit;
                                    }

                                    $tabel_baru = [
                                        'beginning_balance'=>$begin,
                                        'ending_balance'=>$ending_balance,
                                    ];

                                    $update_balance = AccBalance::on($konek2)->where('account',$coa_hutangpusat->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                }
                            }
                        }
                    }

                    $update_ledger = [
                        'tahun'=>$tahun,
                        'periode'=>$bulan,
                        'account'=>$coa_hutangpusat->account,
                        'no_journal'=>$transferin->no_journal,
                        'journal_date'=>$transferin->tanggal_transfer,
                        'db_cr'=>'K',
                        'reference'=>$transferin->no_trf_in,
                        'kredit'=>$grand_total,
                        'kode_lokasi'=>$lokasi,
                    ];
                    $update = Ledger::on($konek2)->create($update_ledger);

                    $type = 'Inventory';
                    $transaksi = $transferin;
                    $tgl_trans = $transferin->tanggal_transfer;
                    $harga_acc = $grand_total;
                    $dbkr = 'K';
                    $update_accbalance = $this->accbalance_kredit_post($coa_hutangpusat, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                    $update_lrb = $this->lrb_post($coa_hutangpusat, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                }else if($cek_company == '04'){
                    $coa_piutangcabang = Coa::where('kode_coa', '081')->first();
                    $cek_balance2 = AccBalance::on($konek2)->where('account',$coa_piutangcabang->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                    if ($cek_balance2 == null) {
                        //CEK SEBELUM
                        $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_piutangcabang->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                        if($cek_sebelum != null){
                            $update_acc = [
                                'periode'=>$tanggal_baru,
                                'fiscalyear'=>$tahun,
                                'account'=>$coa_piutangcabang->account,
                                'beginning_balance'=>$cek_sebelum->ending_balance,
                                'debet'=>0,
                                'kredit'=>0,
                                'ending_balance'=>$cek_sebelum->ending_balance,
                                'kode_lokasi'=>$lokasi,
                            ];

                            $update_balance = AccBalance::on($konek2)->where('account',$coa_piutangcabang->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                        }else{
                            $update_acc = [
                                'periode'=>$tanggal_baru,
                                'fiscalyear'=>$tahun,
                                'account'=>$coa_piutangcabang->account,
                                'beginning_balance'=>0,
                                'debet'=>0,
                                'kredit'=>0,
                                'ending_balance'=>0,
                                'kode_lokasi'=>$lokasi,
                            ];

                            $update_balance = AccBalance::on($konek2)->where('account',$coa_piutangcabang->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                        }

                        //CEK SETELAH
                        $i = $bulan;
                        $cek_setelah = AccBalance::on($konek2)->where('account',$coa_piutangcabang->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                        if ($cek_setelah != null) {
                            for($i = $bulan; $i <= 12; $i++){
                                $cek_setelah = AccBalance::on($konek2)->where('account',$coa_piutangcabang->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                if($cek_setelah != null){
                                    $begin = $cek_setelah->beginning_balance - $grand_total;
                                    $debit = $cek_setelah->debet;
                                    $kredit = $cek_setelah->kredit;
                                    if($coa_piutangcabang->normal_balance == 'D'){
                                        $ending_balance = $begin + $debit - $kredit;
                                    }else{
                                        $ending_balance = $begin - $debit + $kredit;
                                    }

                                    $tabel_baru = [
                                        'beginning_balance'=>$begin,
                                        'ending_balance'=>$ending_balance,
                                    ];

                                    $update_balance = AccBalance::on($konek2)->where('account',$coa_piutangcabang->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                }
                            }
                        }
                    }else{
                        //CEK SETELAH
                        $i = $bulan;
                        $cek_setelah = AccBalance::on($konek2)->where('account',$coa_piutangcabang->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                        if ($cek_setelah != null) {
                            for($i = $bulan; $i <= 12; $i++){
                                $cek_setelah = AccBalance::on($konek2)->where('account',$coa_piutangcabang->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                if($cek_setelah != null){
                                    $begin = $cek_setelah->beginning_balance - $grand_total;
                                    $debit = $cek_setelah->debet;
                                    $kredit = $cek_setelah->kredit;
                                    if($coa_piutangcabang->normal_balance == 'D'){
                                        $ending_balance = $begin + $debit - $kredit;
                                    }else{
                                        $ending_balance = $begin - $debit + $kredit;
                                    }

                                    $tabel_baru = [
                                        'beginning_balance'=>$begin,
                                        'ending_balance'=>$ending_balance,
                                    ];

                                    $update_balance = AccBalance::on($konek2)->where('account',$coa_piutangcabang->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                }
                            }
                        }
                    }

                    $update_ledger = [
                        'tahun'=>$tahun,
                        'periode'=>$bulan,
                        'account'=>$coa_piutangcabang->account,
                        'no_journal'=>$transferin->no_journal,
                        'journal_date'=>$transferin->tanggal_transfer,
                        'db_cr'=>'K',
                        'reference'=>$transferin->no_trf_in,
                        'kredit'=>$grand_total,
                        'kode_lokasi'=>$lokasi,
                    ];
                    $update = Ledger::on($konek2)->create($update_ledger);

                    $type = 'Inventory';
                    $transaksi = $transferin;
                    $tgl_trans = $transferin->tanggal_transfer;
                    $harga_acc = $grand_total;
                    $dbkr = 'K';
                    $update_accbalance = $this->accbalance_kredit_post($coa_piutangcabang, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                    $update_lrb = $this->lrb_post($coa_piutangcabang, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                }
            }                                                                                     

            $cekho = Ledger::on('mysql_finance_gut')->where(DB::raw('LEFT(reference,4)'),'0401')->delete();

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
        $name = auth()->user()->name;
        $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode','Disable')->first();
        
        $todays = Carbon\Carbon::now();
        $today = $todays->toDateString();
        $bans = TransferIn::on($konek)->find(request()->id);
        if ($bans->tanggal_transfer != $today) {
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Tanggal hari ini: '.$today.' Tanggal Transfer berbeda, unposting hanya dapat dilakukan di hari yang sama.',
            ];
            return response()->json($message);
        }
        

        if($cek_bulan == null || $level == 'superadministrator' || $level == 'user_rince' || $level == 'user_thomas' || $level == 'rince_pbm'){
            $transferin = TransferIn::on($konek)->find(request()->id);
            $cek_status = $transferin->status;
            if($cek_status != 'POSTED'){  
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'UNPOST No. Transfer In: '.$transferin->no_trf_in.' sudah dilakukan! Pastikan Anda tidak membuka menu TRANSFER IN lebih dari 1',
                ];
                return response()->json($message);
            }

            $no_transferin = $transferin->no_trf_in;
            $create_transferin = $transferin->created_at;
            $cek_trf = TransferIn::on($konek)->where('no_trf_in', $transferin->no_trf_in)->first();
            $koneksi = $cek_trf->kode_lokasi;
            $lokasi = $cek_trf->kode_lokasi;
                // dd($create_transfer);

            $tgl = $transferin->tanggal_transfer;
            $kode_dari = $transferin->kode_dari;
            $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
            $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
            $tanggal = '01';

            $tujuan = $transferin->kode_tujuan;
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
            
            //CEK PRODUK STOK CUKUP ATAU TIDAK
            $transferindetail = TransferInDetail::on($konek)->with('produk')->where('no_trf_in', request()->id)->get();
            $no_transferin = request()->id;
            $data = array();

            foreach ($transferindetail as $rowdata){
                $data[] = array(
                    'no_transfer'=>$no_transferin,
                    'kode_produk'=>$rowdata->kode_produk,
                    'qty'=>$rowdata->qty,
                    'partnumber'=>$rowdata->partnumber,
                    'hpp'=>$rowdata->hpp,
                );
            }

            $tabel_baru = array();
            $tabel_baru2 = array();
            $tabel_history = array();
            $tabel_kirim = array();

            $transferin = TransferIn::on($konek)->find(request()->id);
            $leng = count($transferindetail);
            $i = 0;

            for($i = 0; $i < $leng; $i++){
                $nomor = TransferIn::on($konek)->where('no_trf_in', request()->id)->first();
                $total = $data[$i]['qty'] * $data[$i]['hpp'];

                $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi', $koneksi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

                $ending_stock = $tb_item_bulanan->ending_stock;
                $ending_amount = $tb_item_bulanan->ending_amount;
                $trf_in = $tb_item_bulanan->trf_in - $data[$i]['qty'];
                $trf_in_amount = $tb_item_bulanan->trf_in_amount - $total;

                $end_stok = $ending_stock - $data[$i]['qty'];
                $end_amount = $ending_amount - ($data[$i]['qty'] * $data[$i]['hpp']);
                if ($end_stok != 0){
                    $hpp = $end_amount / $end_stok;
                }else {
                    $hpp = 0;
                }

                if($end_stok < 0){
                    // exit();
                    $produk = Produk::on($konek)->find($data[$i]['kode_produk']);
                    $message = [
                        'success' => false,
                        'title' => 'Simpan',
                        'message' => 'Unpost Gagal!! Produk '.'['.$produk->kode_produk.']'.$produk->nama_produk.' Stok sudah dipakai/ada trf out.',
                    ];
                    return response()->json($message);
                }

                $tgl_trfin1 = $transferin->tanggal_transfer;
                $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_trfin1)->year;
                $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_trfin1)->month;

                $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                $status_reopen = $reopen->reopen_status;

                if ($status_reopen == 'true'){
                    $tgl_trfin = $transferin->tanggal_transfer;
                    $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_trfin)->year;
                    $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_trfin)->month;

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

                            $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi', $koneksi)->whereMonth('periode', $bulan2)->whereYear('periode', $tahun2)->first();

                            if ($tb_item_bulanan2 != null){
                                $ending_stock = $tb_item_bulanan2->ending_stock;
                                $ending_amount = $tb_item_bulanan2->ending_amount;
                                $begin_stock = $tb_item_bulanan2->begin_stock;
                                $begin_amount = $tb_item_bulanan2->begin_amount;

                                $end_stok = $ending_stock - $data[$i]['qty'];
                                $end_amount = $ending_amount - $data[$i]['hpp'];
                                $begin_stok = $begin_stock - $data[$i]['qty'];
                                $begin_amt = $begin_amount - $data[$i]['hpp'];
                                if ($end_stok != 0){
                                    $hpp = $end_amount / $end_stok;
                                }else {
                                    $hpp = 0;
                                }

                                if($end_stok < 0){
                                    // exit();
                                    $produk = Produk::on($konek)->find($data[$i]['kode_produk']);
                                    $message = [
                                            'success' => false,
                                            'title' => 'Simpan',
                                            'message' => 'Unpost Gagal!! Produk '.'['.$produk->kode_produk.']'.$produk->nama_produk.' Stok sudah dipakai/ada trf out.',
                                    ];
                                    return response()->json($message);
                                }

                            }
                            $j++;
                        }
                }
            }

            // $validate_produk = $this->produkChecker($no_transferin, $tahun, $bulan, $tanggal_baru, $tgl, $transferin, $koneksi);
            $validate_produk = 'true';

            if($level != 'user_rince' && $level != 'superadministrator' && $level != 'user_thomas' && $level != 'rince_pbm'){
                $cekopen = TransferIn::on($konek)->where('kode_lokasi', $koneksi)->where('status','OPEN')->whereMonth('tanggal_transfer', $bulan)->whereYear('tanggal_transfer', $tahun)->first();
                if ($cekopen != null){
                    $message = [
                        'success' => false,
                        'title' => 'Gagal',
                        'message' => 'UNPOST No. Trasnfer In: '.$transferin->no_trf_in.' gagal karena masih ada transfer in OPEN.',
                    ];
                    return response()->json($message);
                }
            }

            if($validate_produk == true){
                $created = $transferin->created_by;
                if($cek_bulan == null || $level == 'superadministrator' || $level == 'user_rince' || $level == 'user_thomas' || $level == 'user_tina' || $level == 'rince_pbm'){
                    $transferindetail = TransferInDetail::on($konek)->with('produk')->where('no_trf_in', request()->id)->get();
                    $no_transferin = request()->id;

                    $data = array();

                    if(!empty($transferindetail)){
                        foreach ($transferindetail as $rowdata){
                            $data[] = array(
                               'no_transfer'=>$no_transferin,
                               'kode_produk'=>$rowdata->kode_produk,
                               'qty'=>$rowdata->qty,
                               'partnumber'=>$rowdata->partnumber,
                               'hpp'=>$rowdata->hpp,
                            );
                        }
                    }

                    $tabel_baru = array();
                    $tabel_baru2 = array();
                    $tabel_history = array();
                    $tabel_kirim = array();

                    if(!empty($transferindetail)){
                        $transferin = TransferIn::on($konek)->find(request()->id);

                        $leng = count($transferindetail);
                        $i = 0;

                        for($i = 0; $i < $leng; $i++){
                                    //SIMPAN DETAIL TRANSFER IN
                            $nomor = TransferIn::on($konek)->where('no_trf_in', request()->id)->first();
                            $total = $data[$i]['qty'] * $data[$i]['hpp'];

                            //UPDATE STOK TABEL PRODUK DAN ITEM BULANAN LOKAL
                            $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi', $koneksi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

                            $ending_stock = $tb_item_bulanan->ending_stock;
                            $ending_amount = $tb_item_bulanan->ending_amount;
                            $trf_in = $tb_item_bulanan->trf_in - $data[$i]['qty'];
                            $trf_in_amount = $tb_item_bulanan->trf_in_amount - $total;

                            $end_stok = $ending_stock - $data[$i]['qty'];
                            $end_amount = $ending_amount - ($data[$i]['qty'] * $data[$i]['hpp']);

                            if ($end_stok != 0){
                                $hpp = $end_amount / $end_stok;
                            }else {
                                $hpp = $tb_item_bulanan->hpp;
                                $end_amount = 0;
                            }

                            $tabel_baru2 = [
                                'ending_stock'=>$end_stok,
                                'ending_amount'=>$end_amount,
                                'trf_in'=>$trf_in,
                                'trf_in_amount'=>$trf_in_amount,
                                'hpp'=>$hpp,
                            ];
                                        // dd($lokasi);


                            $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi',$koneksi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($tabel_baru2);

                            //UPDATE TBL ITEM BULANAN LOKASI ASAL TRF OUT
                            $tb_item_bulanan3 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi', $kode_dari)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

                            $total = $data[$i]['qty'] * $data[$i]['hpp'];
        
                            $ending_stock = $tb_item_bulanan3->ending_stock;
                            $ending_amount = $tb_item_bulanan3->ending_amount;
                            $trf_out = $tb_item_bulanan3->trf_out - $data[$i]['qty'];
                            $trf_out_amount = $tb_item_bulanan3->trf_out_amount - $total;
                            $end_stok3 = $ending_stock + $data[$i]['qty'];
                            $end_amount3 = $ending_amount + ($data[$i]['qty'] * $data[$i]['hpp']);

                            if($end_stok3 > 0){
                                $hpp3 = $end_amount3 / $end_stok3;
                            }else{
                                $hpp3 = $tb_item_bulanan3->hpp;
                                $end_amount3 = 0;
                            }

                            $tabel_baru3 = [
                                'ending_stock'=>$end_stok3,
                                'ending_amount'=>$end_amount3,
                                'trf_out'=>$trf_out,
                                'trf_out_amount'=>$trf_out_amount,
                                'hpp'=>$hpp3,
                            ];

                            $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi',$kode_dari)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($tabel_baru3);

                            $tgl_trfin1 = $transferin->tanggal_transfer;
                            $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_trfin1)->year;
                            $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_trfin1)->month;

                            $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                            $status_reopen = $reopen->reopen_status;

                            if ($status_reopen == 'true'){
                                $tgl_trfin = $transferin->tanggal_transfer;
                                $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_trfin)->year;
                                $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_trfin)->month;

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

                                    $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi', $koneksi)->whereMonth('periode', $bulan2)->whereYear('periode', $tahun2)->first();

                                    if ($tb_item_bulanan2 != null){
                                        $ending_stock = $tb_item_bulanan2->ending_stock;
                                        $ending_amount = $tb_item_bulanan2->ending_amount;
                                        $begin_stock = $tb_item_bulanan2->begin_stock;
                                        $begin_amount = $tb_item_bulanan2->begin_amount;

                                        $end_stok = $ending_stock - $data[$i]['qty'];
                                        $end_amount = $ending_amount - ($data[$i]['qty'] * $data[$i]['hpp']);
                                        $begin_stok = $begin_stock - $data[$i]['qty'];
                                        $begin_amt = $begin_amount - ($data[$i]['qty'] * $data[$i]['hpp']);

                                        if ($end_stok != 0){
                                            $hpp = $end_amount / $end_stok;
                                        }else {
                                            $hpp = 0;
                                        }

                                        $tabel_baru2 = [
                                            'ending_stock'=>$end_stok,
                                            'ending_amount'=>$end_amount,
                                            'begin_stock'=>$begin_stok,
                                            'begin_amount'=>$begin_amt,
                                            'hpp'=>$hpp,
                                        ];

                                        $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi',$koneksi)->whereMonth('periode', $bulan2)->whereYear('periode', $tahun2)->update($tabel_baru2);
                                    }
                                    $j++;
                                }
                            }


                            $update_produk_history = tb_produk_history::on($konek)->where('no_transaksi',request()->id)->delete();
                        }
                    }
                }
                else{
                    $message = [
                        'success' => false,
                        'title' => 'Simpan',
                        'message' => 'Anda tidak dapat UNPOST transaksi yang dibuat oleh user lain',
                    ];
                    return response()->json($message);
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

            $transferin = TransferIn::on($konek)->find(request()->id);
            $transferin->status = "OPEN";
            $transferin->save(); 

            $transfer = Transfer::on($konek)->where('no_transfer',$transferin->no_transfer)->first();
            $transfer->status = "POSTED";
            $transfer->save();

            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Unpost No. Transfer In: '.$no_transferin.'.','created_by'=>$nama,'updated_by'=>$nama];

            user_history::on($konek)->create($tmp);     

            $cek_company = Auth()->user()->kode_company;
            if($cek_company == '04' && $bans->kode_dari == 'JKT'|| $cek_company == '0401' && $bans->kode_dari == 'HO'){
                $konek2 = self::konek2();

                $get_ledger = Ledger::on($konek2)->where('no_journal',$transferin->no_journal)->get();

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
                        $transaksi = $transferin;
                        $tgl_trans = $transferin->tanggal_transfer;
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
                        $transaksi = $transferin;
                        $tgl_trans = $transferin->tanggal_transfer;
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

                $update_ledger = Ledger::on($konek2)->where('no_journal',$transferin->no_journal)->delete();
            }                                                                                          

            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data berhasil di Unpost.'
            ];

            return response()->json($message);
        }
        else{
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Anda tidak mempunyai akses Unposting data',
            ];
            return response()->json($message);
        }
    }

    public function store(Request $request)
    {
        $konek = self::konek();
        $tanggal = $request->tanggal_transfer;
        $validate = $this->periodeChecker($tanggal);
        
        $cekopname = Opname::on($konek)->where('kode_lokasi', auth()->user()->kode_lokasi)->where('status','OPEN')->first();
            if ($cekopname != null){
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'Sedang Opname/Ada Transaksi Opname status: OPEN.',
                ];
                return response()->json($message);
            }
        
        $todays = Carbon\Carbon::now();
        $today = $todays->toDateString();
        if ($tanggal < $today){
            $message = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Tanggal transfer in tidak sesuai dengan tanggal sekarang.'
            ];
            return response()->json($message);
        }

        $reopen = tb_akhir_bulan::on($konek)->where('reopen_status','true')->first();

        if ($reopen != null){
            $tgl = $reopen->periode;
            $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
            $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
            $trfin = TransferIn::on($konek)->whereMonth('tanggal_transfer',$bulan_transaksi)->whereYear('tanggal_transfer',$tahun_transaksi)->where('kode_lokasi', auth()->user()->kode_lokasi)->where('status','OPEN')->get();
            if (count($trfin) >= 1){
                $message = [
                    'success' => false,
                    'title' => 'Gagal',
                    'message' => 'Masih ada transfer in yang OPEN.'
                ];
                return response()->json($message);
            }
        }else {
            $tgl = tb_akhir_bulan::on($konek)->where('status_periode','Open')->first();
            $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl->periode)->year;
            $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl->periode)->month;
                //$sekarang = date('Y-m-d');
            $trfin = TransferIn::on($konek)->whereMonth('tanggal_transfer',$bulan_transaksi)->whereYear('tanggal_transfer',$tahun_transaksi)->where('kode_lokasi', auth()->user()->kode_lokasi)->where('status','OPEN')->get();
            if (count($trfin) >= 1){
                $message = [
                    'success' => false,
                    'title' => 'Gagal',
                    'message' => 'Masih ada transfer in yang OPEN.'
                ];
                return response()->json($message);
            }
        }

        if($validate == true){
            $cek_trf = TransferIn::on($konek)->where('no_transfer',$request->no_transfer)->first();
            if ($cek_trf != null){
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'Nomor sudah ada.',
                ];
                return response()->json($message);
            }
            $transferin = TransferIn::on($konek)->create($request->all());

            $no = TransferIn::on($konek)->orderBy('created_at','desc')->first();
            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Simpan No. transferin: '.$no->no_transferin.'.','created_by'=>$nama,'updated_by'=>$nama];
                         //dd($tmp);
            user_history::on($konek)->create($tmp);

            $message = [
                'success' => true,
                'title' => 'Simpan',
                'message' => 'Data telah disimpan.',
            ];
            return response()->json($message);
                        //}
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

    public function qtyProduk()
    {
        $konek = self::konek();
        $transfer = Transfer::on($konek)->where('no_transfer',request()->id)->first();

        $output = array(
            'tgl_transfer'=>$transfer->tgl_transfer,
            'total_item'=>0,
            'kode_dari'=>$transfer->kode_dari,
            'transfer_dari'=>$transfer->transfer_dari,
            'kode_lokasi'=>$transfer->kode_tujuan,
            'status'=>'OPEN',
        );

        return response()->json($output);
    }

    public function edit_transferin()
    {
        $konek = self::konek();
        $no_transferin = request()->id;
        $data = TransferIn::on($konek)->find($no_transferin);
        $no_transferin = $data->no_trf_in;
        $data = TransferIn::on($konek)->find($no_transferin);
        $output = array(
            'no_trf_in'=> $data->no_trf_in,
            'no_transfer'=> $data->no_transfer,
            'tanggal_transfer'=> $data->tanggal_transfer,
            'keterangan'=> $data->keterangan,
        );
        return response()->json($output);
    }

    public function updateAjax(Request $request)
    {
        $konek = self::konek();
        $tanggal = $request->tanggal_transfer;

        $validate = $this->periodeChecker($tanggal);

        if($validate == true){
            $transferin = TransferIn::on($konek)->find($request->no_trf_in)->update($request->all());

            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Edit No. transferin: '.$request->no_trf_in.'.','created_by'=>$nama,'updated_by'=>$nama];
                     
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
                'message' => 'Re-Open Periode: '.$tanggal,
            ];
            return response()->json($message);
        }
    }

    public function hapus_transferin()
    {
        $konek = self::konek();

        $no_transferin = request()->id;
        $data = TransferIn::on($konek)->find($no_transferin);
        $tanggal = $data->tanggal_transfer;
        $validate = $this->periodeChecker($tanggal);

        if($validate == true){
            $cek_detail = TransferInDetail::on($konek)->where('no_trf_in',$no_transferin)->first();
            if($cek_detail == null){
                $data->delete();

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Hapus No.Transfer In: '.$no_transferin.'.','created_by'=>$nama,'updated_by'=>$nama];
                            //dd($tmp);
                user_history::on($konek)->create($tmp);

                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data ['.$data->no_trf_in.'] telah dihapus.'
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
    }
}
