<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Pemakaian;
use App\Models\Pemakaianban;
use App\Models\PemakaianbanDetail;
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
use App\Models\Ledger;
use App\Models\Coa;
use App\Models\AccBalance;
use App\Models\Tb_acc_history;
use App\Models\Jurnal;
use App\Models\Labarugiberjalan;
use App\Models\SetupAkses;
use App\Models\Opname;
use App\Models\Costcenter;
use PDF;
use Excel;
use DB;
use Alert;
use Carbon;
use DateTime;

class PemakaianbanController extends Controller
{
    public function index()
    {
        $konek = self::konek();
        $create_url = route('pemakaianban.create');
        $Mobil = Mobil::on($konek)->select('kode_mobil', DB::raw("concat(nopol,' - ',no_asset_mobil) as mobils"))->pluck('mobils','kode_mobil');
        $Alat = Alat::on($konek)->select('kode_alat', DB::raw("concat(nama_alat,' - ',no_asset_alat) as alats"))->pluck('alats','kode_alat');

        $JenisMobil= JenisMobil::on($konek)->pluck('nama_jenis_mobil','kode_jenis_mobil');
        $Asmobil = Mobil::on($konek)->whereNotNull('no_asset_mobil')->pluck('no_asset_mobil','no_asset_mobil');
        $Asalat = Alat::on($konek)->whereNotNull('no_asset_alat')->pluck('no_asset_alat','no_asset_alat');

        $Company= Company::pluck('nama_company','kode_company');
        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;
        
        $Costcenter = Costcenter::where('kode_company', auth()->user()->kode_company)->pluck('desc','cost_center');
        
        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        $level = auth()->user()->level;
        
        return view('admin.pemakaianban.index',compact('Costcenter','create_url','Company','Mobil','JenisMobil','Asmobil','period','nama_lokasi','Alat','Asalat','nama_company'));
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
        $lokasi = auth()->user()->kode_lokasi;
        if($lokasi == 'HO'){
            return Datatables::of(Pemakaianban::on($konek)->with('alat','mobil','company','Lokasi')->orderBy('created_at','desc')->withCount('pemakaianbandetail'))->make(true);
        }
        else{
            return Datatables::of(Pemakaianban::on($konek)->with('alat','mobil','company','Lokasi')->orderBy('created_at','desc')->withCount('pemakaianbandetail')->where('kode_lokasi', auth()->user()->kode_lokasi))->make(true);
        }
    }
    
    public function grandios()
    {
        $konek = self::konek();
        $detail = PemakaianbanDetail::on($konek)->where('no_pemakaianban', request()->no_pemakaian)->sum(\DB::raw('qty * harga'));
        $output = array(
            'grand_total'=>$detail,
        );
        return response()->json($output);
    }
    
    public function limitos()
    {
        $konek = self::konek();
        $limit = SetupAkses::on($konek)->where('limit_dari', 0)->where('limit_total', 50000000)->first();
        $limit2 = SetupAkses::on($konek)->where('limit_dari', 50000000)->where('limit_total', 500000000)->first();
        $limit3 = SetupAkses::on($konek)->where('limit_dari', 500000000)->where('limit_total','>', 500000000)->first();
        if ($limit != null) {
            $output = array(
                'nama'=>$limit->nama_user,
                'nama2'=>$limit->nama_user2,
                'nama3'=>$limit->nama_user3,
                'grand1'=>$limit->limit_dari,
                'grand2'=>$limit->limit_total,
                'namara'=>$limit2->nama_user,
                'namara2'=>$limit2->nama_user2,
                'namara3'=>$limit2->nama_user3,
                'grandara1'=>$limit2->limit_dari,
                'grandara2'=>$limit2->limit_total,
                'namaga'=>$limit3->nama_user,
                'namaga2'=>$limit3->nama_user2,
                'namaga3'=>$limit3->nama_user3,
                'grandaga1'=>$limit3->limit_dari,
                'grandaga2'=>$limit3->limit_total,
            );
            return response()->json($output);
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

    public function detail($pemakaianban)
    {   
        $konek = self::konek();
        $pemakaianban = Pemakaianban::on($konek)->find($pemakaianban);
        $tanggal = $pemakaianban->tanggal_pemakaianban;
        $no_pemakaianban = $pemakaianban->no_pemakaianban;

        $validate = $this->periodeChecker($tanggal);
             
        if($validate == true){
            $data = Pemakaianban::on($konek)->find($no_pemakaianban);
            $status = $pemakaianban->status;

            $total_qty = 0;
            $total_harga = 0;
            $grand_total = 0;

            $pemakaianbandetail = PemakaianbanDetail::on($konek)->with('produk','satuan')->where('no_pemakaianban', $pemakaianban->no_pemakaianban)
            ->orderBy('created_at','desc')->get();

            foreach ($pemakaianbandetail as $row){
                $total_qty += $row->qty;
                $subtotal = $row->harga * $row->qty;
                $total_harga += $subtotal;
                $grand_total = number_format($total_harga,2,",",".");
            }

            $list_url= route('pemakaianban.index');

            $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode', 'Open')->orwhere('reopen_status', 'true')->first();
            $Produk = Produk::on($konek)->Join('tb_item_bulanan', 'produk.id', '=', 'tb_item_bulanan.kode_produk')->where('ending_stock','>',0)->where('periode',$cek_bulan->periode)->where('kode_lokasi',auth()->user()->kode_lokasi)->where('produk.kode_kategori','BAN')->where('produk.tipe_produk','Serial')->pluck('produk.nama_produk','produk.id');

            $Satuan = satuan::pluck('nama_satuan', 'kode_satuan');
            $Kategori = KategoriProduk::pluck('nama_kategori', 'kode_kategori');
            $Merek = Merek::on($konek)->pluck('nama_merek', 'kode_merek');
            $Ukuran= Ukuran::on($konek)->pluck('nama_ukuran', 'kode_ukuran');
            $Satuan= satuan::pluck('nama_satuan', 'kode_satuan');
            $Company= Company::pluck('nama_company', 'kode_company');

            $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
            $tgl_jalan2 = $tgl_jalan->periode;
            $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
            $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
            $nama_lokasi = $get_lokasi->nama_lokasi;

            $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
            $nama_company = $get_company->nama_company;

            return view('admin.pemakaianbandetail.index', compact('pemakaianban','pemakaianbandetail','list_url','Produk','Satuan','total_qty','grand_total','Kategori','Merek','Ukuran','Satuan','Company','period', 'nama_lokasi','nama_company'));
        }
        else{
            alert()->success('Status POSTED / Periode Telah CLOSED: '.$tanggal,'GAGAL!')->persistent('Close');
            return redirect()->back();
        }
    }

    public function getmobil()
    {   
        $konek = self::konek();
        $mobil = Mobil::on($konek)->where('kode_mobil', request()->id)->first();
        // dd($mobil);

        $output = array(
            'no_asset_mobil'=>$mobil->no_asset_mobil,
        );

        return response()->json($output);
    }

    public function getalat()
    {   
        $konek = self::konek();
        $alat = Alat::on($konek)->where('kode_alat', request()->id)->first();
        // dd($mobil);

        $output = array(
            'no_asset_alat'=>$alat->no_asset_alat,
        );

        return response()->json($output);
    }

    public function getmobil2()
    {   
        $konek = self::konek();
        $mobil = Mobil::on($konek)->where('kode_mobil', request()->id)->first();
        // dd($mobil);

        $output = array(
            'no_asset_mobil'=>$mobil->no_asset_mobil,
        );

        return response()->json($output);
    }

    public function getalat2()
    {   
        $konek = self::konek();
        $alat = Alat::on($konek)->where('kode_alat', request()->id)->first();
        // dd($mobil);

        $output = array(
            'no_asset_alat'=>$alat->no_asset_alat,
        );

        return response()->json($output);
    }

    public function export2(){
        $request = $_GET['no_pemakaianban'];
        $konek = self::konek();
        $pemakaianban = Pemakaianban::on($konek)->where('no_pemakaianban',$request)->first();
        $user = $pemakaianban->created_by;
        $type = $pemakaianban->type;

        $kode_mobil = $pemakaianban->kode_mobil;
        $nopol = Mobil::on($konek)->find($kode_mobil);

        $kode_alat = $pemakaianban->kode_alat;
        $nama_alat = Alat::on($konek)->find($kode_alat);

        $no_pemakaianban = $pemakaianban->no_pemakaianban;
        $kode_company = $pemakaianban->kode_company;

        $pemakaianbandetail = PemakaianbanDetail::on($konek)->where('no_pemakaianban',$request)->get();

        $company = Company::where('kode_company',$kode_company)->first();
        $nama_company = $company->nama_company;

        $dt = Carbon\Carbon::now();
        $date_now = Carbon\Carbon::parse($dt)->format('d/m/Y H:i:s');

        $tgl = $pemakaianban->tanggal_pemakaianban;
        $date=date_create($tgl);

        if($type == 'Mobil'){
            $pdf = PDF::loadView('/admin/pemakaianban/cetak', compact('pemakaianbandetail','request','no_pemakaianban','tgl','nama_company','date_now','pemakaianban','nopol', 'nama_alat','user'));
            $pdf->setPaper([0, 0, 684, 792], 'potrait');
        }else{
            $pdf = PDF::loadView('/admin/pemakaianban/cetak_alat', compact('pemakaianbandetail','request','no_pemakaianban','tgl','nama_company','date_now','pemakaianban','nopol', 'nama_alat','user'));
            $pdf->setPaper([0, 0, 684, 792], 'potrait');
        }
        
        $nama = auth()->user()->name;
        $tmp = ['nama' => $nama,'aksi' => 'Print No. Pemakaian Ban : '.$no_pemakaianban.'.','created_by'=>$nama,'updated_by'=>$nama];
        user_history::on($konek)->create($tmp);
        
        return $pdf->stream('Laporan Pemakaianban '.$no_pemakaianban.'.pdf');
                    
    }


    public function exportPDF2(){
        $konek = self::konek();
        $konek2 = self::konek2();
        $request = $_GET['no_pemakaianban'];
        $no_journal = $_GET['no_journal'];

        $pemakaianban = Pemakaianban::on($konek)->find($request);
        $jur = $pemakaianban->kode_jurnal;
        $jurnal = Jurnal::find($jur);

        $total_qty = 0;
        $total_harga = 0;
        $grand_total = 0;
        $detail = PemakaianbanDetail::on($konek)->where('no_pemakaianban',$request)->get();
        foreach ($detail as $row){
            $total_qty += $row->qty;
            $subtotal = $row->harga * $row->qty;
            $total_harga += $subtotal;
            $grand_total = $total_harga;
        }

        $ledger2 = Ledger::on($konek2)->with('coa')->where('no_journal',$no_journal)->first();

        $ledger = Ledger::on($konek2)->select('ledger.*','coa.ac_description')->join('u5611458_gui_general_ledger_laravel.coa','u5611458_gui_general_ledger_laravel.coa.account','=','ledger.account')->where('no_journal', $no_journal)->get();

        $user = $pemakaianban->created_by;
        $tgl = $pemakaianban->tanggal_pemakaianban;
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

        $pdf = PDF::loadView('/admin/pemakaianban/pdf2', compact('pemakaianban','request', 'jurnal','tgl','date', 'ttd','nama2','date_now','ledger','ledger2','dt','user','nama','nama2','journal_date','grand_total'));
        $pdf->setPaper([0, 0, 684, 792], 'potrait');
        
        $nama = auth()->user()->name;
        $tmp = ['nama' => $nama,'aksi' => 'Print Zoom Jurnal : '.$request.'.','created_by'=>$nama,'updated_by'=>$nama];
        user_history::on($konek)->create($tmp);
        
        return $pdf->stream('Cetak Zoom Jurnal '.$request.'.pdf');
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

    public function lrb_post($coa, $harga, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr)
    {
        $konek = self::konek2();
        if(auth()->user()->kode_company != '04' || auth()->user()->kode_company != '0401'){
            $lokasi = 'HO';
        }
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
        if(auth()->user()->kode_company != '04' || auth()->user()->kode_company != '0401'){
            $lokasi = 'HO';
        }
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
        if(auth()->user()->kode_company != '04' || auth()->user()->kode_company != '0401'){
            $lokasi = 'HO';
        }
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
        if(auth()->user()->kode_company != '04' || auth()->user()->kode_company != '0401'){
            $lokasi = 'HO';
        }
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
        if(auth()->user()->kode_company != '04' || auth()->user()->kode_company != '0401'){
            $lokasi = 'HO';
        }
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
        if(auth()->user()->kode_company != '04' || auth()->user()->kode_company != '0401'){
            $lokasi = 'HO';
        }
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


    function produkChecker($no_pemakaianban, $tahun, $bulan, $tanggal_baru, $tgl, $pemakaianban, $koneksi)
    {   
        $konek = self::konek();
        $pemakaianbandetail = PemakaianbanDetail::on($konek)->with('produk','satuan')->where('no_pemakaianban', $no_pemakaianban)->get();
        $no_pemakaianban = request()->id;
         
        $data = array();
        $kode_produk = array();
        $kode_satuan = array();
        $qty = array();

        if(!empty($pemakaianbandetail)){
            foreach ($pemakaianbandetail as $rowdata){
                $data[] = array(
                    'no_pemakaianban'=>$no_pemakaianban,
                    'kode_produk'=>$rowdata->kode_produk,
                    'kode_satuan'=>$rowdata->kode_satuan,
                    'qty'=>$rowdata->qty,
                    'partnumber'=>$rowdata->partnumber,
                    'partnumberbaru'=>$rowdata->partnumberbaru,
                );      
            }
        }


        if(!empty($pemakaianbandetail)){
            $leng = count($pemakaianbandetail);

            $i = 0;

            for($i = 0; $i < $leng; $i++){
                $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumberbaru'])->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

                if($tb_item_bulanan2 != null){

                    $produk_awal2 = $tb_item_bulanan2->kode_produk;

                    $pemakaianbandetail3 = PemakaianbanDetail::on($konek)->where('no_pemakaianban', $no_pemakaianban)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                    $begin2 = $tb_item_bulanan2->begin_stock;
                    $beginamount2 = $tb_item_bulanan2->begin_amount;

                    $in_awal2 = $tb_item_bulanan2->in_stock;
                    $inamount_awal2 = $tb_item_bulanan2->in_amount;

                    $out_awal2 = $tb_item_bulanan2->out_stock;
                    $outamount_awal2 = $tb_item_bulanan2->out_amount;

                    $sale2 = $tb_item_bulanan2->sale_stock;
                    $saleamount2 = $tb_item_bulanan2->sale_amount;

                    $trfin2 = $tb_item_bulanan2->trf_in;
                    $trfinamount2 = $tb_item_bulanan2->trf_in_amount;

                    $trfout2 = $tb_item_bulanan2->trf_out;
                    $trfoutamount2 = $tb_item_bulanan2->trf_out_amount;

                    $adj2 = $tb_item_bulanan2->adjustment_stock;
                    $adjamount2 = $tb_item_bulanan2->adjustment_amount;

                    $op2 = $tb_item_bulanan2->stock_opname;
                    $opamount2 = $tb_item_bulanan2->amount_opname;

                    $rb2 = $tb_item_bulanan2->retur_beli_stock;
                    $rbamount2 = $tb_item_bulanan2->retur_beli_amount;

                    $rj2 = $tb_item_bulanan2->retur_jual_stock;
                    $rjamount2 = $tb_item_bulanan2->retur_jual_amount;

                    $disassembling_stock = $tb_item_bulanan2->disassembling_stock;
                    $disassembling_amount = $tb_item_bulanan2->disassembling_amount;

                    $assembling_stock = $tb_item_bulanan2->assembling_stock;
                    $assembling_amount = $tb_item_bulanan2->assembling_amount;

                    $harga2 = $outamount_awal2 + $pemakaianbandetail3->harga;
                    $out_akhir2 = $out_awal2 + $data[$i]['qty'];

                    $endingstock2 = $begin2 + $in_awal2 - $out_akhir2 - $sale2 + $trfin2 - $trfout2 + $adj2 + $op2 - $rb2 + $rj2 - $disassembling_stock + $assembling_stock;
                    $endingamount2 = $beginamount2 + $inamount_awal2 - $harga2 - $saleamount2 + $trfinamount2 - $trfoutamount2 + $adjamount2 + $opamount2 - $rbamount2 + $rjamount2 - $disassembling_amount + $assembling_amount;

                    if($endingstock2 != 0){
                        $hpp = $endingamount2 / $endingstock2;
                    }else{
                        $hpp = $tb_item_bulanan2->hpp;
                    }

                    if($endingstock2 < 0){
                        exit();
                    }

                    $tgl_pakai1 = $pemakaianban->tanggal_pemakaianban;
                    $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_pakai1)->year;
                    $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_pakai1)->month;

                    $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                    $status_reopen = $reopen->reopen_status;

                    if($status_reopen == 'true'){
                        $tgl_pakai = $pemakaianban->tanggal_pemakaianban;
                        $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_pakai)->year;
                        $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_pakai)->month;

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
                            $pemakaianbandetail2 = PemakaianbanDetail::on($konek)->where('no_pemakaianban', $no_pemakaianban)->where('kode_produk',$produk_awal2)->where('partnumberbaru',$data[$i]['partnumberbaru'])->first();

                            $hpp = $pemakaianbandetail2->harga;

                            $stock_o = $data[$i]['qty'];
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

                            $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$produk_awal2)->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumberbaru'])->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->first();

                            if($tb_item_bulanan2 != null){
                                $bs = $tb_item_bulanan2->begin_stock;
                                $ba = $tb_item_bulanan2->begin_amount;
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
                                }

                                $tabel_baru2 = [
                                    'begin_stock'=>$begin_stock1,
                                    'begin_amount'=>$begin_amount1,
                                    'ending_stock'=>$end_stok1,
                                    'ending_amount'=>$end_amount1,
                                    'hpp'=>$hpp,
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


    public function getDatajurnal2(){
        $konek2 = self::konek2();
        $data = Ledger::on($konek2)->with('costcenter')->select('ledger.*','u5611458_gui_general_ledger_laravel.coa.ac_description')->join('u5611458_gui_general_ledger_laravel.coa','u5611458_gui_general_ledger_laravel.coa.account','=','ledger.account')->where('ledger.no_journal',request()->id)->orderBy('ledger.created_at','desc')->get();
        return response()->json($data);
    }

    public function cekjurnal2()
    {
        $konek = self::konek();
        $konek2 = self::konek2();
        $cek = Ledger::on($konek2)->where('no_journal', request()->no_journal)->first();
        $cek_ar = Pemakaianban::on($konek)->where('no_journal', request()->no_journal)->first();

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
        $lokasi = auth()->user()->kode_lokasi;
        $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode','Disable')->first();
        
        $todays = Carbon\Carbon::now();
        $today = $todays->toDateString();
        $bans = Pemakaianban::on($konek)->find(request()->id);
        if ($bans->tanggal_pemakaianban != $today) {
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Tanggal hari ini adalah: '.$today.' , Tanggal Pemakaian berbeda. Posting pemakaian ban hanya dapat dilakukan di hari yang sama.',
            ];
            return response()->json($message);
        }

        if($cek_bulan == null || $level == 'superadministrator' || $level == 'user_rince' || $level == 'user_thomas'){
            if($cek_company == '04' || $cek_company == '0401' || $cek_company == '03' || $cek_company == '05' || $cek_company == '06' || $cek_company == '02'){
                $kat1 = 0;
                $cek_kategori = KategoriProduk::where('kode_kategori','BAN')->first();
                if($cek_company == '04'){
                    if ($cek_kategori->coa_gut == null || $cek_kategori->coabiaya_gut == null){
                        $kat1 = 1;
                    }
                }else if($cek_company == '0401'){
                    if ($cek_kategori->coa_gutjkt == null || $cek_kategori->coabiaya_gutjkt == null){
                        $kat1 = 1;
                    }
                }else if($cek_company == '03'){
                    if ($cek_kategori->coa_emkl == null || $cek_kategori->coabiaya_emkl == null) {
                        $kat1 = 1;
                    }else {
                        if ($bans->cost_center != null){
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
                }else if($cek_company == '05'){
                    if ($cek_kategori->coa_sub == null || $cek_kategori->coabiaya_sub == null){
                        $kat1 = 1;
                    }
                }else if ($cek_company == '02'){
                    if ($cek_kategori->coa_pbm == null || $cek_kategori->coabiaya_pbm == null){
                        $kat1 = 1;
                    }
                }else if ($cek_company == '06'){
                    if ($cek_kategori->coa_infra == null || $cek_kategori->coabiaya_infra == null){
                        $kat1 = 1;
                    }
                }else if ($cek_company == '01'){
                    if ($cek_kategori->coa_depo == null || $cek_kategori->coabiaya_depo == null){
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

            $pemakaianban = Pemakaianban::on($konek)->find(request()->id);
            $cek_status = $pemakaianban->status;
            if($cek_status != 'OPEN'){  
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'POST No. Pemakaian Ban: '.$pemakaianban->no_pemakaianban.' sudah dilakukan! Pastikan Anda tidak membuka menu PEMAKAIAN BAN lebih dari 1',
                ];
                return response()->json($message);
            }

            $no_pemakaianban = $pemakaianban->no_pemakaianban;
            $create_pemakaianban = $pemakaianban->created_at;
            $koneksi = $pemakaianban->kode_lokasi;

            $tgl = $pemakaianban->tanggal_pemakaianban;
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

            $validate_produk = $this->produkChecker($no_pemakaianban, $tahun, $bulan, $tanggal_baru, $tgl, $pemakaianban, $koneksi);

            if($validate_produk == true){
                $pemakaianbandetail = PemakaianbanDetail::on($konek)->with('produk','satuan')->where('no_pemakaianban', request()->id)->get();
                $no_pemakaianban = request()->id;
             
                $data = array();

                if(!empty($pemakaianbandetail)){
                    foreach ($pemakaianbandetail as $rowdata){
                        $data[] = array(
                            'no_pemakaianban'=>$no_pemakaianban,
                            'kode_produk'=>$rowdata->kode_produk,
                            'kode_satuan'=>$rowdata->kode_satuan,
                            'qty'=>$rowdata->qty,
                            'partnumber'=>$rowdata->partnumber,
                            'partnumberbaru'=>$rowdata->partnumberbaru,
                            'harga'=>$rowdata->harga,
                        );        
                    }

                }

                if(!empty($pemakaianbandetail)){
                    $leng = count($pemakaianbandetail);

                    $i = 0;
                    for($i = 0; $i < $leng; $i++){
                        $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumberbaru'])->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

                        if($tb_item_bulanan2 != null){
                            $produk_awal2 = $tb_item_bulanan2->kode_produk;

                            $pemakaianbandetail3 = PemakaianbanDetail::on($konek)->where('no_pemakaianban', $no_pemakaianban)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                            $begin2 = $tb_item_bulanan2->begin_stock;
                            $beginamount2 = $tb_item_bulanan2->begin_amount;

                            $in_awal2 = $tb_item_bulanan2->in_stock;
                            $inamount_awal2 = $tb_item_bulanan2->in_amount;

                            $out_awal2 = $tb_item_bulanan2->out_stock;
                            $outamount_awal2 = $tb_item_bulanan2->out_amount;

                            $sale2 = $tb_item_bulanan2->sale_stock;
                            $saleamount2 = $tb_item_bulanan2->sale_amount;

                            $trfin2 = $tb_item_bulanan2->trf_in;
                            $trfinamount2 = $tb_item_bulanan2->trf_in_amount;

                            $trfout2 = $tb_item_bulanan2->trf_out;
                            $trfoutamount2 = $tb_item_bulanan2->trf_out_amount;

                            $adj2 = $tb_item_bulanan2->adjustment_stock;
                            $adjamount2 = $tb_item_bulanan2->adjustment_amount;

                            $op2 = $tb_item_bulanan2->stock_opname;
                            $opamount2 = $tb_item_bulanan2->amount_opname;

                            $rb2 = $tb_item_bulanan2->retur_beli_stock;
                            $rbamount2 = $tb_item_bulanan2->retur_beli_amount;

                            $rj2 = $tb_item_bulanan2->retur_jual_stock;
                            $rjamount2 = $tb_item_bulanan2->retur_jual_amount;

                            $disassembling_stock = $tb_item_bulanan2->disassembling_stock;
                            $disassembling_amount = $tb_item_bulanan2->disassembling_amount;

                            $assembling_stock = $tb_item_bulanan2->assembling_stock;
                            $assembling_amount = $tb_item_bulanan2->assembling_amount;

                            $harga = $tb_item_bulanan2->hpp;

                            $pemakaiandetail2 = PemakaianbanDetail::on($konek)->where('no_pemakaianban', $no_pemakaianban)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                            $update_harga = [
                                'harga'=>$harga,
                            ];

                            $pemakaiandetail2 = PemakaianbanDetail::on($konek)->where('no_pemakaianban', $no_pemakaianban)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->update($update_harga);

                            $harga2 = $outamount_awal2 + $harga;
                            $out_akhir2 = $out_awal2 + $data[$i]['qty'];

                            $endingstock2 = $begin2 + $in_awal2 - $out_akhir2 - $sale2 + $trfin2 - $trfout2 + $adj2 + $op2 - $rb2 + $rj2 - $disassembling_stock + $assembling_stock;
                            $endingamount2 = $beginamount2 + $inamount_awal2 - $harga2 - $saleamount2 + $trfinamount2 - $trfoutamount2 + $adjamount2 + $opamount2 - $rbamount2 + $rjamount2 - $disassembling_amount + $assembling_amount;

                            if($endingstock2 != 0){
                                $hpp2 = $endingamount2 / $endingstock2;
                            }else{
                                $hpp2 = $tb_item_bulanan2->hpp;
                            }


                            $qty_baru2 = $data[$i]['qty'];

                            $waktu2 = $tgl;
                            $barang2 = $data[$i]['kode_produk'];

                            $tabel_baru2 = [
                                'out_stock'=>$out_akhir2,
                                'out_amount'=>$harga2,
                                'ending_stock'=>$endingstock2,
                                'ending_amount'=>$endingamount2,
                                'hpp'=>$hpp2,
                            ];

                            $update_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$produk_awal2)->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumberbaru'])->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($tabel_baru2);

                            $tabel_history2 = [
                                'kode_produk'=>$barang2,
                                'no_transaksi'=>$no_pemakaianban,
                                'tanggal_transaksi'=>$waktu2,
                                'jam_transaksi'=>$create_pemakaianban,
                                'qty_transaksi'=>0-$data[$i]['qty'],
                                'harga_transaksi'=>$harga,
                                'total_transaksi'=>0-$harga,
                                'kode_lokasi'=>$koneksi,
                            ];

                            $update_produk_history = tb_produk_history::on($konek)->create($tabel_history2);

                            $tgl_pakai1 = $pemakaianban->tanggal_pemakaianban;
                            $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_pakai1)->year;
                            $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_pakai1)->month;

                            $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                            $status_reopen = $reopen->reopen_status;

                            if($status_reopen == 'true'){
                                $tgl_pakai = $pemakaianban->tanggal_pemakaianban;
                                $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_pakai)->year;
                                $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_pakai)->month;

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
                                    $pemakaianbandetail2 = PemakaianbanDetail::on($konek)->where('no_pemakaianban', $no_pemakaianban)->where('kode_produk',$produk_awal2)->where('partnumberbaru',$data[$i]['partnumberbaru'])->first();

                                    $hpp = $pemakaianbandetail2->harga;

                                    $stock_o = $data[$i]['qty'];
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

                                    $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$produk_awal2)->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumberbaru'])->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->first();

                                    if($tb_item_bulanan2 != null){
                                        $bs = $tb_item_bulanan2->begin_stock;
                                        $ba = $tb_item_bulanan2->begin_amount;
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
                                        }

                                        $tabel_baru2 = [
                                            'begin_stock'=>$begin_stock1,
                                            'begin_amount'=>$begin_amount1,
                                            'ending_stock'=>$end_stok1,
                                            'ending_amount'=>$end_amount1,
                                            'hpp'=>$hpp,
                                        ];

                                        $update_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$produk_awal2)->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumberbaru'])->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->update($tabel_baru2);
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

                else
                {        
                    alert()->success('Post', 'GAGAL!')->persistent('Close');
                    return redirect()->back();
                }
                     
                $pemakaianban = Pemakaianban::on($konek)->find(request()->id);
                $pemakaianban->status = "POSTED";
                $pemakaianban->save(); 

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Post No. Pemakaianban: '.$no_pemakaianban.'.','created_by'=>$nama,'updated_by'=>$nama];
                     
                user_history::on($konek)->create($tmp);


                //UPDATE LEDGER JURNAL
                if($cek_company == '04' || $cek_company == '0401' || $cek_company == '03' || $cek_company == '05' || $cek_company == '06' || $cek_company == '02'){
                    $konek2 = self::konek2();
                    $cek_company = Auth()->user()->kode_company;

                    $total_qty = 0;
                    $total_harga = 0;
                    $grand_total = 0;
                    $detail = PemakaianbanDetail::on($konek)->where('no_pemakaianban',$no_pemakaianban)->get();
                    foreach ($detail as $row){
                        $total_qty += $row->qty;
                        $subtotal = $row->harga * $row->qty;
                        $total_harga += $subtotal;
                        $grand_total = $total_harga;
                    }

                    $gt_ban = $grand_total;

                    $kategori = KategoriProduk::where('kode_kategori', 'BAN')->first();
                    if ($cek_company == '04') {
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_gut)->first();
                        $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gut)->first();
                    }else if($cek_company == '0401'){
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_gutjkt)->first();
                        $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gutjkt)->first();
                    }else if($cek_company == '03'){
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_emkl)->first();
                        $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_emkl)->first();
                    }else if($cek_company == '02'){
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_pbm)->first();
                        $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_pbm)->first();
                    }else if($cek_company == '01'){
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_depo)->first();
                        $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_depo)->first();
                    }else if($cek_company == '05'){
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
                        $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_sub)->first();
                    }else if($cek_company == '06'){
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

                    $update_ledger = [
                        'tahun'=>$tahun,
                        'periode'=>$bulan,
                        'account'=>$coa_inventory->account,
                        'no_journal'=>$pemakaianban->no_journal,
                        'journal_date'=>$pemakaianban->tanggal_pemakaianban,
                        'db_cr'=>'K',
                        'reference'=>$pemakaianban->no_pemakaianban,
                        'kredit'=>$gt_ban,
                        'kode_lokasi'=>$lokasi,
                    ];
                    $update = Ledger::on($konek2)->create($update_ledger);

                    $type = 'Inventory';
                    $transaksi = $pemakaianban;
                    $tgl_trans = $pemakaianban->tanggal_pemakaianban;
                    $harga_acc = $gt_ban;
                    $dbkr = 'K';
                    $update_accbalance = $this->accbalance_kredit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                    $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);



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

                    $update_ledger = [
                        'tahun'=>$tahun,
                        'periode'=>$bulan,
                        'account'=>$coa_biaya->account,
                        'cost_center'=>$pemakaianban->cost_center,
                        'no_journal'=>$pemakaianban->no_journal,
                        'journal_date'=>$pemakaianban->tanggal_pemakaianban,
                        'db_cr'=>'D',
                        'reference'=>$pemakaianban->no_pemakaianban,
                        'debit'=>$gt_ban,
                        'kode_lokasi'=>$lokasi,
                    ];
                    $update = Ledger::on($konek2)->create($update_ledger);

                    $type = 'Inventory';
                    $transaksi = $pemakaianban;
                    $tgl_trans = $pemakaianban->tanggal_pemakaianban;
                    $harga_acc = $gt_ban;
                    $dbkr = 'D';
                    $update_accbalance = $this->accbalance_debit_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                    $update_lrb = $this->lrb_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
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
        $lokasi = auth()->user()->kode_lokasi;
        $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode','Disable')->first();
        
        $todays = Carbon\Carbon::now();
        $today = $todays->toDateString();
        $bans = Pemakaianban::on($konek)->find(request()->id);
        if ($bans->tanggal_pemakaianban != $today) {
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Tanggal hari ini adalah: '.$today.' , Tanggal Pemakaian berbeda. Unpost pemakaian ban hanya dapat dilakukan di hari yang sama.',
            ];
            return response()->json($message);
        }

        if($cek_bulan == null || $level == 'superadministrator' || $level == 'user_rince' || $level == 'user_thomas'){
            $pemakaianban = Pemakaianban::on($konek)->find(request()->id);
            $cek_status = $pemakaianban->status;
            if($cek_status != 'POSTED'){  
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'UNPOST No. Pemakaian Ban: '.$pemakaianban->no_pemakaianban.' sudah dilakukan! Pastikan Anda tidak membuka menu PEMAKAIAN BAN lebih dari 1',
                ];
                return response()->json($message);
            }

            $no_pemakaianban = $pemakaianban->no_pemakaianban;
            $create_pemakaianban = $pemakaianban->created_at;
            $koneksi = $pemakaianban->kode_lokasi;

            $tgl = $pemakaianban->tanggal_pemakaianban;
            $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
            $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
            $tanggal = '01';

            $tanggal_baru = Carbon\Carbon::createFromDate($tahun, $bulan, $tanggal)->toDateString();

            $validate = $this->periodeChecker($tgl);
            
            $cekopen = Pemakaianban::on($konek)->where('kode_lokasi', $koneksi)->where('status','OPEN')->whereMonth('tanggal_pemakaianban', $bulan)->whereYear('tanggal_pemakaianban', $tahun)->first();
            if ($cekopen != null){
                $message = [
                    'success' => false,
                    'title' => 'Gagal',
                    'message' => 'UNPOST No. Pemakaian Ban: '.$pemakaianban->no_pemakaianban.' gagal karena masih ada pemakaian ban OPEN.',
                ];
                return response()->json($message);
            }

            if($validate == true){
                $pemakaianbandetail = PemakaianbanDetail::on($konek)->with('produk','satuan')->where('no_pemakaianban', request()->id)->get();
                $no_pemakaianban = request()->id;
             
                $data = array();

                if(!empty($pemakaianbandetail)){
                    foreach ($pemakaianbandetail as $rowdata){
                        $data[] = array(
                            'no_pemakaianban'=>$no_pemakaianban,
                            'kode_produk'=>$rowdata->kode_produk,
                            'kode_satuan'=>$rowdata->kode_satuan,
                            'qty'=>$rowdata->qty,
                            'partnumber'=>$rowdata->partnumber,
                            'partnumberbaru'=>$rowdata->partnumberbaru,
                        );          
                    }
                }
                
                if(!empty($pemakaianbandetail)){
                    $leng = count($pemakaianbandetail);

                    $i = 0;
                    for($i = 0; $i < $leng; $i++){
                        $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumberbaru'])->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

                        if($tb_item_bulanan2 != null){
                            $produk_awal2 = $tb_item_bulanan2->kode_produk;

                            $pemakaianbandetail3 = PemakaianbanDetail::on($konek)->where('no_pemakaianban', $no_pemakaianban)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                            $begin2 = $tb_item_bulanan2->begin_stock;
                            $beginamount2 = $tb_item_bulanan2->begin_amount;

                            $in_awal2 = $tb_item_bulanan2->in_stock;
                            $inamount_awal2 = $tb_item_bulanan2->in_amount;

                            $out_awal2 = $tb_item_bulanan2->out_stock;
                            $outamount_awal2 = $tb_item_bulanan2->out_amount;

                            $sale2 = $tb_item_bulanan2->sale_stock;
                            $saleamount2 = $tb_item_bulanan2->sale_amount;

                            $trfin2 = $tb_item_bulanan2->trf_in;
                            $trfinamount2 = $tb_item_bulanan2->trf_in_amount;

                            $trfout2 = $tb_item_bulanan2->trf_out;
                            $trfoutamount2 = $tb_item_bulanan2->trf_out_amount;

                            $adj2 = $tb_item_bulanan2->adjustment_stock;
                            $adjamount2 = $tb_item_bulanan2->adjustment_amount;

                            $op2 = $tb_item_bulanan2->stock_opname;
                            $opamount2 = $tb_item_bulanan2->amount_opname;

                            $rb2 = $tb_item_bulanan2->retur_beli_stock;
                            $rbamount2 = $tb_item_bulanan2->retur_beli_amount;

                            $rj2 = $tb_item_bulanan2->retur_jual_stock;
                            $rjamount2 = $tb_item_bulanan2->retur_jual_amount;

                            $disassembling_stock = $tb_item_bulanan2->disassembling_stock;
                            $disassembling_amount = $tb_item_bulanan2->disassembling_amount;

                            $assembling_stock = $tb_item_bulanan2->assembling_stock;
                            $assembling_amount = $tb_item_bulanan2->assembling_amount;

                            $harga2 = $outamount_awal2 - $pemakaianbandetail3->harga;
                            $out_akhir2 = $out_awal2 - $data[$i]['qty'];

                            $endingstock2 = $begin2 + $in_awal2 - $out_akhir2 - $sale2 + $trfin2 - $trfout2 + $adj2 + $op2 - $rb2 + $rj2 - $disassembling_stock + $assembling_stock;
                            $endingamount2 = $beginamount2 + $inamount_awal2 - $harga2 - $saleamount2 + $trfinamount2 - $trfoutamount2 + $adjamount2 + $opamount2 - $rbamount2 + $rjamount2 - $disassembling_amount + $assembling_amount;

                            if($endingstock2 != 0){
                                $hpp = $endingamount2 / $endingstock2;
                            }else{
                                $hpp = $tb_item_bulanan2->hpp;
                            }

                            $qty_baru2 = $data[$i]['qty'];

                            $waktu2 = $tgl;
                            $barang2 = $data[$i]['kode_produk'];

                            $tabel_baru2 = [
                                'out_stock'=>$out_akhir2,
                                'out_amount'=>$harga2,
                                'ending_stock'=>$endingstock2,
                                'ending_amount'=>$endingamount2,
                                'hpp'=>$hpp,
                            ];

                            $update_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$produk_awal2)->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumberbaru'])->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($tabel_baru2);

                            $update_produk_history = tb_produk_history::on($konek)->where('no_transaksi',$no_pemakaianban)->delete();

                            $tgl_pakai1 = $pemakaianban->tanggal_pemakaianban;
                            $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_pakai1)->year;
                            $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_pakai1)->month;

                            $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                            $status_reopen = $reopen->reopen_status;

                            if($status_reopen == 'true'){
                                $tgl_pakai = $pemakaianban->tanggal_pemakaianban;
                                $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_pakai)->year;
                                $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_pakai)->month;

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
                                    $pemakaianbandetail2 = PemakaianbanDetail::on($konek)->where('no_pemakaianban', $no_pemakaianban)->where('kode_produk',$produk_awal2)->where('partnumberbaru',$data[$i]['partnumberbaru'])->first();

                                    $hpp = $pemakaianbandetail2->harga;

                                    $stock_o = $data[$i]['qty'];
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

                                    $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$produk_awal2)->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumberbaru'])->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->first();

                                    if($tb_item_bulanan2 != null){
                                        $bs = $tb_item_bulanan2->begin_stock;
                                        $ba = $tb_item_bulanan2->begin_amount;
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
                                        }

                                        $tabel_baru2 = [
                                            'begin_stock'=>$begin_stock1,
                                            'begin_amount'=>$begin_amount1,
                                            'ending_stock'=>$end_stok1,
                                            'ending_amount'=>$end_amount1,
                                            'hpp'=>$hpp,
                                        ];

                                        $update_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$produk_awal2)->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumberbaru'])->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->update($tabel_baru2);
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
                     
                $pemakaianban = Pemakaianban::on($konek)->find(request()->id);
                $pemakaianban->status = "OPEN";
                $pemakaianban->save(); 

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Unpost No. Pemakaianban: '.$no_pemakaianban.'.','created_by'=>$nama,'updated_by'=>$nama];
                    
                user_history::on($konek)->create($tmp);
                     
                //UPDATE JURNAL
                $cek_company = Auth()->user()->kode_company;
                if($cek_company == '04' || $cek_company == '0401' || $cek_company == '03' || $cek_company == '0501' || $cek_company == '06'){
                    $konek2 = self::konek2();

                    $get_ledger = Ledger::on($konek2)->where('no_journal',$pemakaianban->no_journal)->get();

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
                            $transaksi = $pemakaianban;
                            $tgl_trans = $pemakaianban->tanggal_pemakaianban;
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
                            $transaksi = $pemakaianban;
                            $tgl_trans = $pemakaianban->tanggal_pemakaianban;
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

                    $update_ledger = Ledger::on($konek2)->where('no_journal',$pemakaianban->no_journal)->delete();
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
                    'message' => 'Data gagal di UNPOST, re-open [Bulan '.$bulan.'; Tahun '.$tahun.'].'
                ];

                return response()->json($message);
            }
        }
        else{
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
        // dd($request);
        $tanggal = $request->tanggal_pemakaianban;
        $konek = self::konek();
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
        
        $reopen = tb_akhir_bulan::on($konek)->where('reopen_status','true')->first();
        if ($reopen != null){
            $tgl = $reopen->periode;
            $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
            $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
            $pakaiban = Pemakaianban::on($konek)->whereMonth('tanggal_pemakaianban',$bulan_transaksi)->whereYear('tanggal_pemakaianban',$tahun_transaksi)->where('kode_lokasi', auth()->user()->kode_lokasi)->where('status','OPEN')->get();
            if (count($pakaiban) >= 1){
                $message = [
                    'success' => false,
                    'title' => 'Gagal',
                    'message' => 'Masih ada pemakaian ban yang OPEN.'
                ];
                return response()->json($message);
            }
        }else {
            $tgl = tb_akhir_bulan::on($konek)->where('status_periode','Open')->first();
            $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl->periode)->year;
            $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl->periode)->month;
            //$sekarang = date('Y-m-d');
            $pakaiban = Pemakaianban::on($konek)->whereMonth('tanggal_pemakaianban',$bulan_transaksi)->whereYear('tanggal_pemakaianban',$tahun_transaksi)->where('kode_lokasi', auth()->user()->kode_lokasi)->where('status','OPEN')->get();
            if (count($pakaiban) >= 1){
                $message = [
                    'success' => false,
                    'title' => 'Gagal',
                    'message' => 'Masih ada pemakaian ban yang OPEN.'
                ];
               return response()->json($message);
            }
        }
        
        $todays = Carbon\Carbon::now();
        $today = $todays->toDateString();
        if ($request->tanggal_pemakaianban != $today){
            $message = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Tanggal pemakaian ban berbeda dgn tanggal hari ini.'
            ];
            return response()->json($message);
        }
        
        if($validate == true){
            $pemakaianban = Pemakaianban::on($konek)->create($request->all());

            $no = Pemakaianban::on($konek)->orderBy('created_at','desc')->first();
            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Simpan No. Pemakaianban: '.$no->no_pemakaianban.'.','created_by'=>$nama,'updated_by'=>$nama];
                
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
        $pemakaianbandetail= PemakaianbanDetail::on($konek)->with('produk','satuan')->where('no_pemakaianban',request()->id)
        ->orderBy('created_at', 'desc')->get();

        $output = array();

        if($pemakaianbandetail){
            foreach($pemakaianbandetail as $row)
            {
                $subtotal =  number_format($row->harga * $row->qty,2,",",".");
                $output[] = array(

                    'no_pemakaianban'=>$row->no_pemakaianban,
                    'produk'=>$row->produk->nama_produk,
                    'partnumber'=>$row->partnumber,
                    'partnumberbaru'=>$row->partnumberbaru,
                    'satuan'=>$row->satuan->nama_satuan,
                    'qty'=>$row->qty,
                    'harga'=>$row->harga,
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
    
    
    public function edit_pemakaianban()
    {   
        $konek = self::konek();
        $no_pemakaianban = request()->id;
        $data = Pemakaianban::on($konek)->find($no_pemakaianban);
        
        $output = array(
            'no_pemakaianban'=> $data->no_pemakaianban,
            'tanggal_pemakaianban'=> $data->tanggal_pemakaianban,
            'kode_mobil'=> $data->kode_mobil,
            'no_asset_mobil'=> $data->no_asset_mobil,
            'kode_alat'=> $data->kode_alat,
            'no_asset_alat'=> $data->no_asset_alat,
            'status'=> $data->status,
            'type'=> $data->type,
            'cost_center'=> $data->cost_center,
        );
        return response()->json($output);
    }


    public function updateAjax(Request $request)
    {
        // dd($request);
        $konek = self::konek();
        $tanggal = $request->tanggal_pemakaianban;
        $validate = $this->periodeChecker($tanggal);
        
        $todays = Carbon\Carbon::now();
        $today = $todays->toDateString();
        if ($tanggal != $today){
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Tanggal pemakaian berbeda dgn tanggal hari ini. ',
            ];
            return response()->json($message);
        }
             
        if($validate == true){
            $Pemakaian = Pemakaianban::on($konek)->find($request->no_pemakaianban)->update($request->all());
                 
            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Edit No. Pemakaianban: '.$request->no_pemakaianban.'.','created_by'=>$nama,'updated_by'=>$nama];
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
    

    public function hapus_pemakaianban()
    {
        $konek = self::konek();
        $level = auth()->user()->level;

        $no_pemakaianban = request()->id;
        $data = Pemakaianban::on($konek)->find($no_pemakaianban);
        $tanggal = $data->tanggal_pemakaianban;
        $validate = $this->periodeChecker($tanggal);

        if($validate == true){
            $cek_detail = PemakaianbanDetail::on($konek)->where('no_pemakaianban',$no_pemakaianban)->first();
            if($cek_detail == null){
                $data->delete();

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Hapus No. Pemakaian: '.$no_pemakaianban.'.','created_by'=>$nama,'updated_by'=>$nama];
                user_history::on($konek)->create($tmp);

                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data ['.$data->no_pemakaianban.'] telah dihapus.'
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
