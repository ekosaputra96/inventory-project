<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Transfer;
use App\Models\TransferDetail;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Pemakaian;
use App\Models\PemakaianDetail;
use App\Models\Permintaan;
use App\Models\Memo;
use App\Models\MemoDetail;
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

class TransferController extends Controller
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
        $login = auth()->user()->kode_lokasi;
        $create_url = route('transfer.create');
        $Satuan= satuan::pluck('nama_satuan', 'kode_satuan');
        $Pembelian = Pembelian::on($konek)->where('status','POSTED')->where('jenis_po','Stock')->orwhere('status','RECEIVED')->pluck('no_pembelian','no_pembelian');
        if($login == 'HO'){
            $Memo =  Memo::on($konek)->where('status', 'REQUESTED')->pluck('no_memo','no_memo');
        }else{
            $Memo =  Memo::on($konek)->where('status', 'REQUESTED')->where('kode_lokasi',auth()->user()->kode_lokasi)->pluck('no_memo','no_memo');
        }
        $Company= Company::pluck('nama_company','kode_company');
        
        $asal = MasterLokasi::where('kode_lokasi', auth()->user()->kode_lokasi)->first();
        if ($asal->level_lokasi == '0') {
            $Lokasi= MasterLokasi::where('kode_lokasi', '<>', auth()->user()->kode_lokasi)->where('level_lokasi', '1')->pluck('nama_lokasi','kode_lokasi');
        }else if ($asal->level_lokasi == '1') {
            $Lokasi= MasterLokasi::where('kode_lokasi', '<>', auth()->user()->kode_lokasi)->where('level_lokasi', '0')->orwhere('level_lokasi', '2')->pluck('nama_lokasi','kode_lokasi');
        }else if ($asal->level_lokasi == '2') {
            $Lokasi= MasterLokasi::where('kode_lokasi', '<>', auth()->user()->kode_lokasi)->where('level_lokasi', '1')->pluck('nama_lokasi','kode_lokasi');
        }
        
        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        $level = auth()->user()->level;
        
        return view('admin.transfer.index',compact('create_url','Memo','Pembelian','Company','Satuan','period','asal','Lokasi', 'nama_lokasi','nama_company'));
    }

    public function anyData()
    {
        $konek = self::konek();
        $lokasi = auth()->user()->kode_lokasi;
        if($lokasi == 'HO'){
            return Datatables::of(Transfer::on($konek)->with('company','Lokasi')->withCount('transferdetail')->orderBy('created_at','desc'))->make(true);
        }
        else{
            return Datatables::of(Transfer::on($konek)->with('company','Lokasi')->withCount('transferdetail')->where('kode_lokasi', auth()->user()->kode_lokasi)->orderBy('created_at','desc'))->make(true);
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

    public function detail($transfer)
    {
        $konek = self::konek();
        $transfer = Transfer::on($konek)->find($transfer);
        $tanggal = $transfer->tanggal_transfer;
        $no_transfer = $transfer->no_transfer;
        $validate = $this->periodeChecker($tanggal);

        if($validate == true){
            $data = Transfer::on($konek)->find($no_transfer);

            $total_qty = 0;
            $total_harga = 0;
            $grand_total = 0;

            $transferdetail = TransferDetail::on($konek)->with('produk','satuan')->where('no_transfer', $transfer->no_transfer)
            ->orderBy('created_at','desc')->get();

            foreach ($transferdetail as $row){
                $total_qty += $row->qty;
                $subtotal = $row->harga * $row->qty;
                $total_harga += $subtotal;
                $grand_total = number_format($total_harga,2,",",".");
            }

            $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode', 'Open')->orwhere('reopen_status', 'true')->first();
            $Produk = Produk::on($konek)->Join('tb_item_bulanan', 'produk.id', '=', 'tb_item_bulanan.kode_produk')->where('ending_stock','>',0)->where('periode',$cek_bulan->periode)->where('kode_lokasi',auth()->user()->kode_lokasi)->pluck('produk.nama_produk','produk.id');

            $Satuan = Satuan::pluck('nama_satuan','kode_satuan');
            $list_url= route('transfer.index');

            $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
            $tgl_jalan2 = $tgl_jalan->periode;
            $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
            $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
            $nama_lokasi = $get_lokasi->nama_lokasi;

            $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
            $nama_company = $get_company->nama_company;

            return view('admin.transferdetail.index', compact('transfer','transferdetail','list_url','Produk','Satuan','total_qty','grand_total','period', 'nama_lokasi','nama_company'));
        }
        else{
            alert()->success('Status POSTED / Periode Telah CLOSED: '.$tanggal,'GAGAL!')->persistent('Close');
            return redirect()->back();
        }
    }

    public function cetakPDF(){ 
        $konek = self::konek();
        $request = $_GET['no_transfer'];

        $total_qty = 0;
        $total_harga = 0;
        $grand_total = 0;

        $transfer = Transfer::on($konek)->find($request);
        $user = $transfer->created_by;

        $kode_company = $transfer->kode_company;
        $company = Company::where('kode_company',$kode_company)->first();
        $nama_company = $company->nama_company;

        $dt = Carbon\Carbon::now();
        $date_now = Carbon\Carbon::parse($dt)->format('d/m/Y H:i:s');

        $tgl = $transfer->tanggal_transfer;
        $date=date_create($tgl);

        $keterangan = substr($transfer->keterangan, 0, 70);

        $transferdetail= TransferDetail::on($konek)->with('produk','satuan')->where('no_transfer',$transfer->no_transfer)->get();
        
        foreach ($transferdetail as $row){
            $total_qty += $row->qty;
            $subtotal = $row->harga * $row->qty;
            $total_harga += $subtotal;
            $grand_total = number_format($total_harga,2,",",".");
        }
        
        $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
        $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
        
        $namapdf = substr($transfer->no_transfer,0,5).$transfer->kode_dari.'-'.$transfer->kode_tujuan.substr($transfer->no_transfer,5,10).'.pdf';
        
        $setupfolder = SetupFolder::find(9);
        $tes_save = $company->kode_company.". ".$company->nama_company."/".$setupfolder->folder."/".$setupfolder->subfolder."/".$tahun."/".$bulan."/".$namapdf;

        $jumlah=count($transferdetail);
        $pdf = PDF::loadView('/admin/transfer/pdf', compact('transfer','transferdetail','jumlah','total_qty','grand_total','nama_company','date_now','keterangan','tgl','user'));
        $pdf->setPaper([0, 0, 684, 792], 'potrait');
        
        $nama = auth()->user()->name;
        $tmp = ['nama' => $nama,'aksi' => 'Print No. Transfer Out : '.$transfer->no_transfer.'.','created_by'=>$nama,'updated_by'=>$nama];
        user_history::on($konek)->create($tmp);
        
        Storage::disk('ftp')->put($tes_save, $pdf->output());
        return $pdf->stream(substr($transfer->no_transfer,0,5).'-'.$transfer->kode_dari.'-'.$transfer->kode_tujuan.'-'.$transfer->no_transfer.'.pdf');
        
    }

    public function exportPDF2(){
        $konek = self::konek();
        $konek2 = self::konek2();
        $request = $_GET['no_transfer'];
        $no_journal = $_GET['no_journal'];

        $transfer = Transfer::on($konek)->find($request);
        $jur = $transfer->kode_jurnal;
        $jurnal = Jurnal::find($jur);

        $total_qty = 0;
        $total_harga = 0;
        $grand_total = 0;
        $detail = TransferDetail::on($konek)->where('no_transfer',$request)->get();
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
        $date_now = Carbon\Carbon::parse($dt)->format('d/m/Y H:i:s');
        $journal_date = Carbon\Carbon::parse($ledger2->journal_date)->format('d/m/Y');
        
        $pdf = PDF::loadView('/admin/transfer/pdf2', compact('transfer','request', 'jurnal','tgl','date', 'ttd','date_now','ledger','ledger2','dt','user','nama','nama2','journal_date','grand_total'));
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
        $transferdetail= TransferDetail::on($konek)->with('produk','satuan')->where('no_transfer',request()->id)
        ->orderBy('created_at', 'desc')->get();

        $output = array();

        if($transferdetail){
            foreach($transferdetail as $row)
            {
                $subtotal =  number_format(($row->hpp * $row->qty) + $row->landedcost,2,",",".");
                $output[] = array(
                    'no_transfer'=>$row->no_transfer,
                    'produk'=>$row->produk->nama_produk,
                    'partnumber'=>$row->partnumber,
                    'satuan'=>$row->satuan->nama_satuan,
                    'qty'=>$row->qty,
                    'harga'=>$row->hpp,
                    'landedcost'=>$row->landedcost,
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

    function produkChecker($no_transfer, $tahun, $bulan, $tanggal_baru, $tgl, $transfer, $koneksi2)
    {   
        $konek = self::konek();         
        $transferdetail = TransferDetail::on($konek)->with('produk','satuan')->where('no_transfer', request()->id)->get();
        $no_transfer = request()->id;

        $data = array();

        if(!empty($transferdetail)){
            foreach ($transferdetail as $rowdata){
                $data[] = array(
                    'no_transfer'=>$no_transfer,
                    'kode_produk'=>$rowdata->kode_produk,
                    'kode_satuan'=>$rowdata->kode_satuan,
                    'qty'=>$rowdata->qty,
                    'partnumber'=>$rowdata->partnumber,
                    'hpp'=>$rowdata->hpp,
                    'amount'=>$rowdata->qty * $rowdata->hpp,
                );           
            }
        }

        $tabel_baru = array();
        $tabel_baru2 = array();
        $tabel_history = array();
        $tabel_kirim = array();

        if(!empty($transferdetail)){
            $transfer = Transfer::on($konek)->find(request()->id);

            $leng = count($transferdetail);
            $i = 0;

            for($i = 0; $i < $leng; $i++){
                $total = $data[$i]['qty'] * $data[$i]['hpp'];

                $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi', $koneksi2)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

                $ending_stock = $tb_item_bulanan->ending_stock;
                $ending_amount = $tb_item_bulanan->ending_amount;
                $trf_out = $tb_item_bulanan->trf_out + $data[$i]['qty'];
                $trf_out_amount = $tb_item_bulanan->trf_out_amount + $total;

                $end_stok = $ending_stock - $data[$i]['qty'];
                $end_amount = $ending_amount - ($data[$i]['qty'] * $data[$i]['hpp']);

                if($end_stok < 0){
                    exit();
                }

                $tgl_trfout1 = $transfer->tanggal_transfer;
                $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_trfout1)->year;
                $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_trfout1)->month;

                $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                $status_reopen = $reopen->reopen_status;

                if ($status_reopen == 'true'){
                    $tgl_trfout = $transfer->tanggal_transfer;
                    $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_trfout)->year;
                    $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_trfout)->month;

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

                        $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi', $koneksi2)->whereMonth('periode', $bulan2)->whereYear('periode', $tahun2)->first();

                        if ($tb_item_bulanan2 != null){
                            $ending_stock = $tb_item_bulanan2->ending_stock;
                            $ending_amount = $tb_item_bulanan2->ending_amount;
                            $begin_stock = $tb_item_bulanan2->begin_stock;
                            $begin_amount = $tb_item_bulanan2->begin_amount;

                            $end_stok = $ending_stock - $data[$i]['qty'];
                            $end_amount = $ending_amount - ($data[$i]['qty'] * $data[$i]['hpp']);
                            $begin_stok = $begin_stock - $data[$i]['qty'];
                            $begin_amt = $begin_amount - ($data[$i]['qty'] * $data[$i]['hpp']);

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
        $data = Ledger::on($konek2)->select('ledger.*','u5611458_gui_general_ledger_laravel.coa.ac_description')->join('u5611458_gui_general_ledger_laravel.coa','u5611458_gui_general_ledger_laravel.coa.account','=','ledger.account')->where('ledger.no_journal',request()->id)->orderBy('ledger.created_at','desc')->get();
        return response()->json($data);
    }

    public function cekjurnal2()
    {
        $konek = self::konek();
        $konek2 = self::konek2();
        $cek = Ledger::on($konek2)->where('no_journal', request()->no_journal)->first();
        $cek_ar = Transfer::on($konek)->where('no_journal', request()->no_journal)->first();

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
        
        $todays = Carbon\Carbon::now();
        $today = $todays->toDateString();
        $pemakaian = Transfer::on($konek)->find(request()->id);
        if ($pemakaian->tanggal_transfer != $today) {
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Tanggal hari ini: '.$today.' Tanggal Transfer berbeda, Posting pemakaian hanya dapat dilakukan di hari yang sama.',
            ];
            return response()->json($message);
        }
        
        if($cek_bulan == null || $level == 'superadministrator' || $level == 'user_rince' || $level == 'user_thomas'){
            if($cek_company == '04' && $pemakaian->kode_tujuan == 'JKT' || $cek_company == '0401' && $pemakaian->kode_tujuan == 'HO'){
                $transferdetail = TransferDetail::on($konek)->where('no_transfer', request()->id)->get();
                $leng = count($transferdetail);
                $data = array();

                foreach ($transferdetail as $rowdata){
                    $kodeP = $rowdata->kode_produk;

                    $data[] = array(
                        'kode_produk'=>$kodeP,
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


            $transfer = Transfer::on($konek)->find(request()->id);

            $cek_status = $transfer->status;
            if($cek_status != 'OPEN'){  
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'POST No. Transfer Out: '.$transfer->no_transfer.' sudah dilakukan! Pastikan Anda tidak membuka menu TRANSFER OUT lebih dari 1',
                ];
                return response()->json($message);
            }

            $no_transfer = $transfer->no_transfer;
            $create_transfer = $transfer->created_at;
            $cek_trf = Transfer::on($konek)->where('no_transfer', $transfer->no_transfer)->first();
            $koneksi = $cek_trf->kode_tujuan;
            $koneksi2 = $cek_trf->kode_lokasi;
            $lokasi = $cek_trf->kode_lokasi;
            // dd($create_transfer);

            $tgl = $transfer->tanggal_transfer;
            $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
            $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
            $tanggal = '01';

            $tujuan = $transfer->kode_tujuan;
            $tb_akhir_bulan = tb_akhir_bulan::on($konek)->where('status_periode','Open')->first();
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

            $validate_produk = $this->produkChecker($no_transfer, $tahun, $bulan, $tanggal_baru, $tgl, $transfer, $koneksi2);
            // dd($validate_produk);
            
            $transfer->status = 'ONGOING';
            $transfer->save();

            if($validate_produk == true){             
                $transferdetail = TransferDetail::on($konek)->with('produk','satuan')->where('no_transfer', request()->id)->orderBy('id')->get();
                $no_transfer = request()->id;

                $data = array();

                if(!empty($transferdetail)){
                    foreach ($transferdetail as $rowdata){
                        $data[] = array(
                           'id'=>$rowdata->id,
                           'no_transfer'=>$no_transfer,
                           'kode_produk'=>$rowdata->kode_produk,
                           'kode_satuan'=>$rowdata->kode_satuan,
                           'qty'=>$rowdata->qty,
                           'partnumber'=>$rowdata->partnumber,
                           'hpp'=>$rowdata->hpp,
                           'amount'=>$rowdata->qty * $rowdata->hpp,
                        );

                        $prod = Produk::on($konek)->find($rowdata->kode_produk);
                        $cek_ending = tb_item_bulanan::on($konek)->where('kode_produk',$rowdata->kode_produk)->where('partnumber',$rowdata->partnumber)->where('kode_lokasi', $koneksi2)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

                        $trfqty = TransferDetail::on($konek)->join('transfer','transfer_detail.no_transfer','=','transfer.no_transfer')
                            ->where('transfer.kode_lokasi', $lokasi)
                            ->whereMonth('transfer.tanggal_transfer', $bulan)
                            ->whereYear('transfer.tanggal_transfer', $tahun)
                            ->where('transfer.status', 'POSTED')
                            ->where('transfer_detail.kode_produk', $rowdata->kode_produk)
                            ->sum('transfer_detail.qty');
                            
                        $pakaiqty = PemakaianDetail::on($konek)->join('pemakaian','pemakaian_detail.no_pemakaian','=','pemakaian.no_pemakaian')
                            ->where('pemakaian.kode_lokasi', $lokasi)
                            ->whereMonth('pemakaian.tanggal_pemakaian', $bulan)
                            ->whereYear('pemakaian.tanggal_pemakaian', $tahun)
                            ->where('pemakaian.status', 'OPEN')
                            ->where('pemakaian_detail.kode_produk', $rowdata->kode_produk)
                            ->sum('pemakaian_detail.qty');
                            
                        if ($trfqty == null){
                            $trfqty = 0;
                        }
                        
                        if ($pakaiqty == null){
                            $pakaiqty = 0;
                        }

                        if (($cek_ending->ending_stock - $trfqty - $pakaiqty) < $rowdata->qty){
                            $sisa = $cek_ending->ending_stock - $trfqty;
                            $message = [
                                'success' => false,
                                'title' => 'Update',
                                'message' => 'Data gagal di POSTING, Produk ['.$prod->nama_produk.'] stock tersisa ['.$sisa.']. Silakan cek history transaksi produk.'
                            ];
                            return response()->json($message);
                        }
                    }
                }

                $tabel_baru = array();
                $tabel_baru2 = array();
                $tabel_history = array();
                $tabel_kirim = array();

                $tabel_kirim = [
                    'no_transfer'=>$no_transfer,
                    'tanggal_transfer'=>$tgl,
                    'kode_lokasi'=>$transfer->kode_tujuan,
                    'total_item'=>$transfer->total_item,
                    'kode_dari'=>$transfer->kode_dari,
                    'transfer_dari'=>$transfer->transfer_dari,
                    'keterangan'=>$transfer->keterangan,
                ];

                if(!empty($transferdetail)){
                    $transfer = Transfer::on($konek)->find(request()->id);

                    $leng = count($transferdetail);
                    $i = 0;
                    
                    $transfer->status = 'ONGOING1';
                    $transfer->save();

                    for($i = 0; $i < $leng; $i++){
                        //UPDATE STOK TABEL PRODUK DAN ITEM BULANAN LOKAL
                        $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi', $koneksi2)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

                        $harga = $tb_item_bulanan->hpp;

                        $transferdetail2 = TransferDetail::on($konek)->where('no_transfer', request()->id)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                        $update_harga = [
                            'hpp'=>$harga,
                        ];

                        $transferdetail2 = TransferDetail::on($konek)->where('no_transfer', request()->id)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->update($update_harga);

                        $total = $data[$i]['qty'] * $harga;
    
                        $ending_stock = $tb_item_bulanan->ending_stock;
                        $ending_amount = $tb_item_bulanan->ending_amount;
                        $trf_out = $tb_item_bulanan->trf_out + $data[$i]['qty'];
                        $trf_out_amount = $tb_item_bulanan->trf_out_amount + $total;

                        $end_stok = $ending_stock - $data[$i]['qty'];
                        $end_amount = $ending_amount - ($data[$i]['qty'] * $harga);

                        if($end_stok > 0){
                            $hpp2 = $end_amount / $end_stok;
                        }else{
                            $hpp2 = $tb_item_bulanan->hpp;
                            $end_amount = 0;
                        }
    
                        if (auth()->user()->kode_company == '77'){
                            $tabel_baru2 = [
                                'ending_stock'=>$end_stok,
                                'ending_amount'=>$end_amount,
                                'trf_out'=>$trf_out,
                                'trf_out_amount'=>$trf_out_amount,
                                'hpp'=>$hpp2,
                            ];
    
                            $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi',$koneksi2)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($tabel_baru2);
                        }
                            
    
                        //CEK REOPEN
                        $tgl_trfout1 = $transfer->tanggal_transfer;
                        $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_trfout1)->year;
                        $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_trfout1)->month;
    
                        $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                        $status_reopen = $reopen->reopen_status;
    
                        if ($status_reopen == 'true'){
                            $tgl_trfout = $transfer->tanggal_transfer;
                            $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_trfout)->year;
                            $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_trfout)->month;

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
                                $transferdetail2 = TransferDetail::on($konek)->where('no_transfer', request()->id)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                                $harga = $transferdetail2->hpp;

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
    
                                $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi', $koneksi2)->whereMonth('periode', $bulan2)->whereYear('periode', $tahun2)->first();
    
                                if ($tb_item_bulanan2 != null){
                                    $ending_stock = $tb_item_bulanan2->ending_stock;
                                    $ending_amount = $tb_item_bulanan2->ending_amount;
                                    $begin_stock = $tb_item_bulanan2->begin_stock;
                                    $begin_amount = $tb_item_bulanan2->begin_amount;

                                    $end_stok = $ending_stock - $data[$i]['qty'];
                                    $end_amount = $ending_amount - ($data[$i]['qty'] * $harga);
                                    $begin_stok = $begin_stock - $data[$i]['qty'];
                                    $begin_amt = $begin_amount - ($data[$i]['qty'] * $harga);

                                    if($end_stok != 0){
                                        $hpp = $end_amount / $end_stok;
                                    }else{
                                        $hpp = $tb_item_bulanan2->hpp;
                                        $end_amount = 0;
                                    }

                                    if (auth()->user()->kode_company == '77'){
                                        $tabel_baru2 = [
                                            'ending_stock'=>$end_stok,
                                            'ending_amount'=>$end_amount,
                                            'begin_stock'=>$begin_stok,
                                            'begin_amount'=>$begin_amt,
                                            'hpp'=>$hpp,
                                        ];

                                        $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi',$koneksi2)->whereMonth('periode', $bulan2)->whereYear('periode', $tahun2)->update($tabel_baru2);
                                    }
                                }
                                $j++;
                            }
    
                        }
                        
                        $tabel_history = [
                            'kode_produk'=>$data[$i]['kode_produk'],
                            'no_transaksi'=>request()->id,
                            'tanggal_transaksi'=>$tgl,
                            'jam_transaksi'=>$tgl,
                            'qty_transaksi'=>0-$data[$i]['qty'],
                            'harga_transaksi'=>$harga,
                            'total_transaksi'=>0-$total,
                            'kode_lokasi'=>$koneksi2,
                        ];
                        $tb_produk_history = tb_produk_history::on($konek)->create($tabel_history);
                    }
                }
            } else{
                $message = [
                    'success' => false,
                    'title' => 'Update',
                    'message' => 'Re-Open [Bulan '.$bulan.'; Tahun '.$tahun.'].'
                ];

                return response()->json($message);
            }
            
            if($transfer->no_memo != null){
                $memo = Memo::on($konek)->where('no_memo',$transfer->no_memo)->first();
                if($memo->status != 'REQUESTED'){
                    $message = [
                        'success' => false,
                        'title' => 'Gagal',
                        'message' => 'No Memo '. $transfer->no_memo.' belum di POST di Request Pembelian'
                    ];
                    return response()->json($message);
                }
                $detail = TransferDetail::on($konek)->where('no_transfer',request()->id)->get();
                foreach($detail as $row){
                    $get_detail = MemoDetail::on($konek)->where('no_memo',$transfer->no_memo)->where('kode_produk',$row->kode_produk)->first();
                    if($get_detail != null){
                        $get_detail->qty_to = $row->qty;
                        if ($get_detail->qty_to >= $get_detail->qty){
                            $get_detail->status_produk = "ON";
                        }
                        $get_detail->save();
                    }
                }
                
                $getstatus = MemoDetail::on($konek)->where('no_memo',$transfer->no_memo)->where('status_produk','OFF')->first();
                if($getstatus == null){
                    $memo->status = "CLOSED";
                    $memo->save();
                }
            }

            $transfer = Transfer::on($konek)->find(request()->id);
            $transfer->status = "POSTED";
            $transfer->save(); 

            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Post No. transfer: '.$no_transfer.'.','created_by'=>$nama,'updated_by'=>$nama];

            user_history::on($konek)->create($tmp);           


            //UPDATE JURNAL
            if($cek_company == '04'  && $pemakaian->kode_tujuan == 'JKT' || $cek_company == '0401'  && $pemakaian->kode_tujuan == 'HO'){
                $konek2 = self::konek2();
                $cek_company = Auth()->user()->kode_company;

                $total_qty = 0;
                $total_harga = 0;
                $totalhpp = 0;
                $grand_total = 0;
                // $detail = TransferDetail::on($konek)->where('no_transfer',$transfer->no_transfer)->get();
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

                $detail = KategoriProduk::join($compan.'.produk','kategori_produk.kode_kategori','=',$compan.'.produk.kode_kategori')->join($compan.'.transfer_detail',$compan.'.produk.id','=',$compan.'.transfer_detail.kode_produk')->where($compan.'.transfer_detail.no_transfer', $no_transfer)->groupBy('kategori_produk.kode_kategori')->get();
                foreach ($detail as $row){
                    $total_qty += $row->qty;
                    $subtotal = $row->hpp * $row->qty;
                    $total_harga += $subtotal;

                    $totalhpp = TransferDetail::on($konek)->select(DB::raw('SUM('.$compan.'.transfer_detail.qty *'.$compan.'.transfer_detail.hpp) as total'))->join($compan.'.produk',$compan.'.transfer_detail.kode_produk','=',$compan.'.produk.id')->where($compan.'.transfer_detail.no_transfer', $no_transfer)->where($compan.'.produk.kode_kategori', $row->kode_kategori)->first();
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
                                    $begin = $cek_setelah->beginning_balance - $totalhpp;
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
                                    $begin = $cek_setelah->beginning_balance - $totalhpp;
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
                        'no_journal'=>$transfer->no_journal,
                        'journal_date'=>$transfer->tanggal_transfer,
                        'db_cr'=>'K',
                        'reference'=>$transfer->no_transfer,
                        'kredit'=>$totalhpp,
                        'kode_lokasi'=>$lokasi,
                    ];
                    $update = Ledger::on($konek2)->create($update_ledger);

                    $type = 'Inventory';
                    $transaksi = $transfer;
                    $tgl_trans = $transfer->tanggal_transfer;
                    $harga_acc = $totalhpp;
                    $dbkr = 'K';
                    $update_accbalance = $this->accbalance_kredit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                    $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                }

                if($cek_company == '04'){
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
                                    $begin = $cek_setelah->beginning_balance + $grand_total;
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
                                    $begin = $cek_setelah->beginning_balance + $grand_total;
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
                        'no_journal'=>$transfer->no_journal,
                        'journal_date'=>$transfer->tanggal_transfer,
                        'db_cr'=>'D',
                        'reference'=>$transfer->no_transfer,
                        'debit'=>$grand_total,
                        'kode_lokasi'=>$lokasi,
                    ];
                    $update = Ledger::on($konek2)->create($update_ledger);

                    $type = 'Inventory';
                    $transaksi = $transfer;
                    $tgl_trans = $transfer->tanggal_transfer;
                    $harga_acc = $grand_total;
                    $dbkr = 'D';
                    $update_accbalance = $this->accbalance_debit_post($coa_piutangcabang, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                    $update_lrb = $this->lrb_post($coa_piutangcabang, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                }else if($cek_company == '0401'){
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
                        'no_journal'=>$transfer->no_journal,
                        'journal_date'=>$transfer->tanggal_transfer,
                        'db_cr'=>'D',
                        'reference'=>$transfer->no_transfer,
                        'debit'=>$grand_total,
                        'kode_lokasi'=>$lokasi,
                    ];
                    $update = Ledger::on($konek2)->create($update_ledger);

                    $type = 'Inventory';
                    $transaksi = $transfer;
                    $tgl_trans = $transfer->tanggal_transfer;
                    $harga_acc = $grand_total;
                    $dbkr = 'D';
                    $update_accbalance = $this->accbalance_debit_post($coa_hutangpusat, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                    $update_lrb = $this->lrb_post($coa_hutangpusat, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                }
            }

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
        $bans = Transfer::on($konek)->find(request()->id);
        if ($bans->tanggal_transfer != $today) {
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Tanggal hari ini: '.$today.' Tanggal Transfer berbeda, Unposting pemakaian hanya dapat dilakukan di hari yang sama.',
            ];
            return response()->json($message);
        }

        if($cek_bulan == null || $level == 'superadministrator' || $level == 'user_rince' || $level == 'user_thomas' || $level == 'rince_pbm'){
            $transfer = Transfer::on($konek)->find(request()->id);

            $cek_status = $transfer->status;
            if($cek_status != 'POSTED'){  
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'UNPOST No. Transfer Out: '.$transfer->no_transfer.' sudah dilakukan! Pastikan Anda tidak membuka menu TRANSFER OUT lebih dari 1',
                ];
                return response()->json($message);
            }
            
            $cektrfin = TransferIn::on($konek)->where('no_transfer', request()->id)->where('status','POSTED')->where('total_item', '>', 0)->first();
            if ($cektrfin != null){
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'UNPOST No. Transfer Out: '.$transfer->no_transfer.' tidak dapat dilakukan, ada transfer in dengan nomor '.$cektrfin->no_trf_in.' harus di UNPOST dahulu.',
                ];
                return response()->json($message);
            }

            $no_transfer = $transfer->no_transfer;
            $create_transfer = $transfer->created_at;
            $cek_trf = Transfer::on($konek)->where('no_transfer', $transfer->no_transfer)->first();
            $koneksi = $cek_trf->kode_tujuan;
            $koneksi2 = $cek_trf->kode_lokasi;
            $lokasi = $cek_trf->kode_lokasi;
                // dd($create_transfer);

            $tgl = $transfer->tanggal_transfer;
            $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
            $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
            $tanggal = '01';

            $tujuan = $transfer->kode_tujuan;
            $tanggal_baru = Carbon\Carbon::createFromDate($tahun, $bulan, $tanggal)->toDateString();

            $validate = $this->periodeChecker($tgl);
                // dd($validate);

            $cekopen = Transfer::on($konek)->where('kode_lokasi', $koneksi2)->where('status','OPEN')->whereMonth('tanggal_transfer', $bulan)->whereYear('tanggal_transfer', $tahun)->first();
            if ($cekopen != null){
                $message = [
                    'success' => false,
                    'title' => 'Gagal',
                    'message' => 'UNPOST No. Transfer Out: '.$transfer->no_transfer.' gagal karena masih ada transfer out OPEN.',
                ];
                return response()->json($message);
            }

            if($validate == true){
                $created = $cek_trf->created_by;
                if($cek_bulan == null || $level == 'superadministrator' || $level == 'user_rince' || $level == 'user_thomas' || $level == 'user_tina'|| $level == 'novita_pbm' || $level == 'merisa_pbm'){
                    $transferdetail = TransferDetail::on($konek)->with('produk','satuan')->where('no_transfer', request()->id)->get();
                    $no_transfer = request()->id;

                    $data = array();

                    if(!empty($transferdetail)){
                        foreach ($transferdetail as $rowdata){
                            $data[] = array(
                               'no_transfer'=>$no_transfer,
                               'kode_produk'=>$rowdata->kode_produk,
                               'kode_satuan'=>$rowdata->kode_satuan,
                               'qty'=>$rowdata->qty,
                               'partnumber'=>$rowdata->partnumber,
                               'hpp'=>$rowdata->hpp,
                               'amount'=>$rowdata->qty * $rowdata->hpp,
                            );
                        }
                    }

                    $tabel_baru = array();
                    $tabel_baru2 = array();
                    $tabel_history = array();
                    $tabel_kirim = array();

                    if(!empty($transferdetail)){
                        $transfer = Transfer::on($konek)->find(request()->id);

                        $leng = count($transferdetail);
                        $i = 0;

                        for($i = 0; $i < $leng; $i++){
                            //SIMPAN DETAIL TRANSFER IN
                            $nomor = TransferIn::on($konek)->where('no_transfer', request()->id)->first();
                            $total = $data[$i]['qty'] * $data[$i]['hpp'];

                            //UPDATE STOK TABEL PRODUK DAN ITEM BULANAN LOKAL
                            $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi', $koneksi2)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();


                            $ending_stock = $tb_item_bulanan->ending_stock;
                            $ending_amount = $tb_item_bulanan->ending_amount;
                            $trf_out = $tb_item_bulanan->trf_out - $data[$i]['qty'];
                            $trf_out_amount = $tb_item_bulanan->trf_out_amount - $total;

                            $end_stok = $ending_stock + $data[$i]['qty'];
                            $end_amount = $ending_amount + ($data[$i]['qty'] * $data[$i]['hpp']);

                            if($end_stok != 0){
                                $hpp = $end_amount / $end_stok;
                            }else{
                                $hpp = $tb_item_bulanan->hpp;
                                $end_amount = 0;
                            }

                            if (auth()->user()->kode_company == '77') {
                                $tabel_baru2 = [
                                    'ending_stock'=>$end_stok,
                                    'ending_amount'=>$end_amount,
                                    'trf_out'=>$trf_out,
                                    'trf_out_amount'=>$trf_out_amount,
                                    'hpp'=>$hpp,
                                ];

                                $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi',$koneksi2)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($tabel_baru2);
                            }

                            $tgl_trfout1 = $transfer->tanggal_transfer;
                            $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_trfout1)->year;
                            $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_trfout1)->month;

                            $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                            $status_reopen = $reopen->reopen_status;

                            if ($status_reopen == 'true'){
                                $tgl_trfout = $transfer->tanggal_transfer;
                                $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_trfout)->year;
                                $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_trfout)->month;

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

                                    $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi', $koneksi2)->whereMonth('periode', $bulan2)->whereYear('periode', $tahun2)->first();

                                    if ($tb_item_bulanan2 != null){
                                        $ending_stock = $tb_item_bulanan2->ending_stock;
                                        $ending_amount = $tb_item_bulanan2->ending_amount;
                                        $begin_stock = $tb_item_bulanan2->begin_stock;
                                        $begin_amount = $tb_item_bulanan2->begin_amount;
                                        //$trf_out = $tb_item_bulanan2->trf_out - $data[$i]['qty'];
                                        //$trf_out_amount = $tb_item_bulanan2->trf_out_amount - $total;

                                        $end_stok = $ending_stock + $data[$i]['qty'];
                                        $end_amount = $ending_amount + ($data[$i]['qty'] * $data[$i]['hpp']);
                                        $begin_stok = $begin_stock + $data[$i]['qty'];
                                        $begin_amt = $begin_amount + ($data[$i]['qty'] * $data[$i]['hpp']);

                                        if($end_stok != 0){
                                            $hpp = $end_amount / $end_stok;
                                        }else{
                                            $hpp = $tb_item_bulanan2->hpp;
                                            $end_amount = 0;
                                        }

                                        if (auth()->user()->kode_company == '77'){
                                            $tabel_baru2 = [
                                                'ending_stock'=>$end_stok,
                                                'ending_amount'=>$end_amount,
                                                'begin_stock'=>$begin_stok,
                                                'begin_amount'=>$begin_amt,
                                                'hpp'=>$hpp,
                                            ];

                                            $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi',$koneksi2)->whereMonth('periode', $bulan2)->whereYear('periode', $tahun2)->update($tabel_baru2);
                                        }
                                    }
                                    $j++;
                                }
                            }

                            $update_produk_history = tb_produk_history::on($konek)->where('no_transaksi',request()->id)->delete();
                        }
                    }
                }else{
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
            
            if($transfer->no_memo != null){
                $memo = Memo::on($konek)->where('no_memo',$transfer->no_memo)->first();
                $detail = TransferDetail::on($konek)->where('no_transfer',request()->id)->get();
                foreach($detail as $row){
                    $get_detail = MemoDetail::on($konek)->where('no_memo',$transfer->no_memo)->where('kode_produk',$row->kode_produk)->first();
                    if($get_detail != null){
                        $updateqty = $get_detail->qty_to - $row->qty;
                        $get_detail->status_produk ='OFF';
                        $get_detail->save();
                    }
                }
                $memo->status ='REQUESTED';
                $memo->save();
            }

            $transfer = Transfer::on($konek)->find(request()->id);
            $transfer->status = "OPEN";
            $transfer->save(); 

            $transfer_in = TransferIn::on($konek)->where('no_transfer',$no_transfer)->first();
            if ($transfer_in != null){
                $hapus_detail = TransferInDetail::on($konek)->where('no_trf_in',$transfer_in->no_trf_in)->delete();
                $transfer_in->total_item = 0;
                $transfer_in->save();
            } 

            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Unpost No. transfer: '.$no_transfer.'.','created_by'=>$nama,'updated_by'=>$nama];

            user_history::on($konek)->create($tmp);      

            //hapus jurnal dari tabel ledger
            $cek_company = Auth()->user()->kode_company;
            if($cek_company == '04'  && $bans->kode_tujuan == 'JKT'|| $cek_company == '0401' && $bans->kode_tujuan == 'HO'){
                $konek2 = self::konek2();

                $get_ledger = Ledger::on($konek2)->where('no_journal',$transfer->no_journal)->get();

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
                        $transaksi = $transfer;
                        $tgl_trans = $transfer->tanggal_transfer;
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
                        $transaksi = $transfer;
                        $tgl_trans = $transfer->tanggal_transfer;
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

                $update_ledger = Ledger::on($konek2)->where('no_journal',$transfer->no_journal)->delete();
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

        $reopen = tb_akhir_bulan::on($konek)->where('reopen_status','true')->first();
        
        $cekopname = Opname::on($konek)->where('kode_lokasi', auth()->user()->kode_lokasi)->where('status','OPEN')->first();
            if ($cekopname != null){
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'Sedang Opname/Ada Transaksi Opname status: OPEN.',
                ];
                return response()->json($message);
            }

        if ($reopen != null){
            $tgl = $reopen->periode;
            $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
            $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
            $trfout = Transfer::on($konek)->whereMonth('tanggal_transfer',$bulan_transaksi)->whereYear('tanggal_transfer',$tahun_transaksi)->where('kode_lokasi', auth()->user()->kode_lokasi)->where('status','OPEN')->get();
            if (count($trfout) >= 1){
                $message = [
                    'success' => false,
                    'title' => 'Gagal',
                    'message' => 'Masih ada transfer out yang belum selesai (OPEN). Harap selesaikan dulu ditarik ke sisi Transfer In.'
                ];
                return response()->json($message);
            }
        }else {
            $tgl = tb_akhir_bulan::on($konek)->where('status_periode','Open')->first();
            $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl->periode)->year;
            $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl->periode)->month;
                //$sekarang = date('Y-m-d');
            $trfout = Transfer::on($konek)->whereMonth('tanggal_transfer',$bulan_transaksi)->whereYear('tanggal_transfer',$tahun_transaksi)->where('kode_lokasi', auth()->user()->kode_lokasi)->where('status','OPEN')->get();
            if (count($trfout) >= 1){
                $message = [
                    'success' => false,
                    'title' => 'Gagal',
                    'message' => 'Masih ada transfer out yang belum selesai (OPEN). Harap selesaikan dulu ditarik ke sisi Transfer In.'
                ];
                return response()->json($message);
            }
        }

        if($validate == true){
            $transfer = Transfer::on($konek)->create($request->all());

            $no = Transfer::on($konek)->orderBy('created_at','desc')->first();
            
            $gettransfer = Transfer::on($konek)->where('no_memo',$request->no_memo)->first();

            if($request->no_memo != null){
                $getmemo = MemoDetail::on($konek)->where('no_memo',$request->no_memo)->get();

                foreach($getmemo as $row){

                    $getproduk = Produk::on($konek)->where('id',$row->kode_produk)->first();
                    $getmonthly = tb_item_bulanan::on($konek)->where('kode_produk',$row->kode_produk)->where('kode_lokasi','HO')->orderBy('periode','desc')->first();
                    $tabel_baru = [
                        'no_transfer'=>$gettransfer->no_transfer,
                        'kode_produk'=>$row->kode_produk,
                        'kode_satuan'=>$getproduk->kode_satuan,
                        'no_mesin'=>$getmonthly->no_mesin,
                        'partnumber'=>$getproduk->partnumber,
                        'qty'=>$row->qty,
                        'hpp'=>$getmonthly->hpp,
                        'kode_company'=>auth()->user()->kode_company,
                    ];
                    TransferDetail::on($konek)->create($tabel_baru);
                }

                $countdetail = TransferDetail::on($konek)->where('no_transfer', $gettransfer->no_transfer)->get();
                $lenger = count($countdetail);
                $gettransfer->total_item = $lenger;
                $gettransfer->save();
            }
            
            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Simpan No. transfer: '.$no->no_transfer.'.','created_by'=>$nama,'updated_by'=>$nama];
                         //dd($tmp);
            user_history::on($konek)->create($tmp);

            $message = [
                'success' => true,
                'title' => 'Simpan',
                'message' => 'Data telah disimpan.',
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


    public function edit_transfer()
    {
        $konek = self::konek();
        $no_transfer = request()->id;
        $data = Transfer::on($konek)->find($no_transfer);
        $output = array(
            'no_transfer'=> $data->no_transfer,
            'no_pembelian'=> $data->no_pembelian,
            'tanggal_transfer'=> $data->tanggal_transfer,
            'status'=> $data->status,
            'kode_tujuan'=> $data->kode_tujuan,
            'keterangan'=> $data->keterangan,
        );
        return response()->json($output);
    }

    public function updateAjax(Request $request)
    {
        $konek = self::konek();
        $tanggal = $request->tanggal_transfer;
        $kode_tujuan = $request->kode_tujuan;

        $validate = $this->periodeChecker($tanggal);

        if($validate == true){
            $cek_lokasi = MasterLokasi::find($kode_tujuan);
            $transfer = Transfer::on($konek)->find($request->no_transfer)->update($request->all());

            $nama_lokasi = $cek_lokasi->nama_lokasi;
            $trans = Transfer::on($konek)->find($request->no_transfer);
            $trans->transfer_tujuan = $nama_lokasi;
            $trans->save();

            $transfer = Transfer::on($konek)->find($request->no_transfer)->update($request->all());

            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Edit No. transfer: '.$request->no_transfer.'.','created_by'=>$nama,'updated_by'=>$nama];
                    
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


    public function hapus_transfer()
    {
        $konek = self::konek();

        $no_transfer = request()->id;
        $data = Transfer::on($konek)->find($no_transfer);
        $tanggal = $data->tanggal_transfer;

        $cek_in = TransferIn::on($konek)->where('no_transfer',$no_transfer)->first();
        if($cek_in != null){
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'No. Transfer Out ini terikat pada No. Transfer In'.$cek_in->no_trf_in,
            ];
            return response()->json($message);
        }

        $validate = $this->periodeChecker($tanggal);

        if($validate == true){
            $cek_detail = TransferDetail::on($konek)->where('no_transfer',$no_transfer)->first();
            if($cek_detail == null){

                $data->delete();

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Hapus No. transfer: '.$no_transfer.'.','created_by'=>$nama,'updated_by'=>$nama];

                user_history::on($konek)->create($tmp);

                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data ['.$data->no_transfer.'] telah dihapus.'
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
