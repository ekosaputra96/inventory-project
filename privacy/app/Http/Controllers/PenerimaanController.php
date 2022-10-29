<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Penerimaan;
use App\Models\PenerimaanDetail;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Permintaan;
use App\Models\Produk;
use App\Models\Vendor;
use App\Models\Konversi;
use App\Models\satuan;
use App\Models\Company;
use App\Models\tb_akhir_bulan;
use App\Models\tb_item_bulanan;
use App\Models\tb_produk_history;
use App\Models\user_history;
use App\Models\MasterLokasi;
use App\Models\Returpembelian;
use App\Models\ReturpembelianDetail;
use App\Models\KategoriProduk;
use App\Models\Ledger;
use App\Models\Coa;
use App\Models\AccBalance;
use App\Models\Tb_acc_history;
use App\Models\Jurnal;
use App\Models\Labarugiberjalan;
use App\Models\SetupAkses;
use App\Models\Opname;
use App\Models\Costcenter;
use App\Models\SetupFolder;
use Illuminate\Support\Facades\Storage;

use PDF;
use Excel;
use DB;
use Carbon;
use DateTime;

class PenerimaanController extends Controller
{
    public function index()
    {
        $konek = self::konek();
        $create_url = route('penerimaan.create');
        $Satuan= satuan::pluck('nama_satuan', 'kode_satuan');
        $Pembelian = Pembelian::on($konek)->where('status','POSTED')->where('jenis_po','Stock')->orwhere('status','RECEIVED')->pluck('no_pembelian','no_pembelian');
        $Company= Company::pluck('nama_company','kode_company');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;
        
        return view('admin.penerimaan.index',compact('create_url','Pembelian','Company','Satuan','period', 'nama_lokasi','nama_company'));
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
        return Datatables::of(Penerimaan::on($konek)->with('company')->orderBy('created_at','desc')->where('kode_lokasi', auth()->user()->kode_lokasi)->withCount('penerimaandetail'))->make(true);
    }
    
    public function grandios()
    {
        $konek = self::konek();
        $detail = PenerimaanDetail::on($konek)->where('no_penerimaan', request()->no_penerimaan)->sum(\DB::raw('qty * harga'));
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
        $limit3 = SetupAkses::on($konek)->where('limit_dari', 500000000)->first();
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

    public function exportPDF(){
        $request = $_GET['no_penerimaan'];
        $konek = self::konek();
        $penerimaan = Penerimaan::on($konek)->where('no_penerimaan',$request)->first();
        $user = $penerimaan->created_by;
        $no_penerimaan = $penerimaan->no_penerimaan;

        $no_pembelian = $penerimaan->no_pembelian;
        $kode_company = $penerimaan->kode_company;
        // dd($tipe);
        $penerimaandetail = PenerimaanDetail::on($konek)->where('no_penerimaan',$request)->get();
        $pembelian = Pembelian::on($konek)->where('no_pembelian',$no_pembelian)->first();

        $kode_vendor = $pembelian->kode_vendor;
        $vendor = Vendor::where('id',$kode_vendor)->first();
        $nama_vendor = $vendor->nama_vendor;

        $company = Company::where('kode_company',$kode_company)->first();
        $nama_company = $company->nama_company;

        $dt = Carbon\Carbon::now();
        $date_now = Carbon\Carbon::parse($dt)->format('d/m/Y');

        $tgl = $penerimaan->tanggal_penerimaan;
        $date=date_create($tgl);
        $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
        $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
        
        $setupfolder = SetupFolder::find(5);
        $tes_save = $company->kode_company.". ".$company->nama_company."/".$setupfolder->folder."/".$setupfolder->subfolder."/".$tahun."/".$bulan."/".$request.".pdf";
        
        $pdf = PDF::loadView('/admin/penerimaan/pdf', compact('penerimaandetail','request', 'nama_vendor','no_pembelian','tgl', 'no_penerimaan','nama_company','date_now','penerimaan','user'));
        $pdf->setPaper([0, 0, 684, 792], 'potrait');
        
        $nama = auth()->user()->name;
        $tmp = ['nama' => $nama,'aksi' => 'Print No. Penerimaan : '.$no_penerimaan.'.','created_by'=>$nama,'updated_by'=>$nama];
        user_history::on($konek)->create($tmp);
        
        Storage::disk('ftp')->put($tes_save, $pdf->output());
        return $pdf->stream($no_penerimaan.'.pdf');        
    }

    //CETAK ZOOM JURNAL
    public function exportPDF2(){
        $konek = self::konek();
        $konek2 = self::konek2();
        $request = $_GET['no_penerimaan'];
        $no_journal = $_GET['no_journal'];

        $penerimaan = Penerimaan::on($konek)->find($request);
        $jur = $penerimaan->kode_jurnal;
        $jurnal = Jurnal::find($jur);

        $total_harga = 0;
        $detail = PenerimaanDetail::on($konek)->where('no_penerimaan',$request)->get();
        foreach ($detail as $row){
            $subtotal = ($row->harga + $row->landedcost) * $row->qty;
            $total_harga += $subtotal;
            $grand_total = $total_harga;
        }

        $ledger2 = Ledger::on($konek2)->with('coa')->where('no_journal',$no_journal)->first();

        $ledger = Ledger::on($konek2)->select('ledger.*','coa.ac_description')->join('u5611458_gui_general_ledger_laravel.coa','u5611458_gui_general_ledger_laravel.coa.account','=','ledger.account')->where('no_journal', $no_journal)->get();

        $user = $penerimaan->created_by;
        $tgl = $penerimaan->tanggal_penerimaan;
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

        $pdf = PDF::loadView('/admin/penerimaan/pdf2', compact('penerimaan','request', 'jurnal','tgl','date', 'ttd','date_now','ledger','ledger2','dt','user','nama','nama2','journal_date','grand_total'));
        $pdf->setPaper([0, 0, 684, 792], 'potrait');
        
        $nama = auth()->user()->name;
        $tmp = ['nama' => $nama,'aksi' => 'Print Zoom Jurnal : '.$request.'.','created_by'=>$nama,'updated_by'=>$nama];
        user_history::on($konek)->create($tmp);
        
        return $pdf->stream('Cetak Zoom Jurnal '.$request.'.pdf');
    }


    public function detail($penerimaan)
    {
        $konek = self::konek();
        $penerimaan = Penerimaan::on($konek)->find($penerimaan);
        $tanggal = $penerimaan->tanggal_penerimaan;
        $no_penerimaan = $penerimaan->no_penerimaan;

        $validate = $this->periodeChecker($tanggal);
             
        if($validate == true){
            $data = Penerimaan::on($konek)->find($no_penerimaan);

            $total_harga = 0;

            $penerimaandetail = PenerimaanDetail::on($konek)->with('produk','satuan')->where('no_penerimaan', $penerimaan->no_penerimaan)
            ->orderBy('created_at','desc')->get();

            foreach ($penerimaandetail as $row){
                $subtotal = $row->harga * $row->qty;
                $total_harga += $subtotal;
                $grand_total = number_format($total_harga,2,",",".");
            }

            $Produk = PembelianDetail::on($konek)->where('pembelian_detail.no_pembelian', $data->no_pembelian)
            ->Join('produk', 'pembelian_detail.kode_produk', '=', 'produk.id')->where(DB::raw('qty-qty_received'),'>',0)
            ->pluck('produk.nama_produk','produk.id','pembelian_detail.qty');

            $Satuan = satuan::pluck('nama_satuan','kode_satuan');
            $list_url= route('penerimaan.index');

            $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
            $tgl_jalan2 = $tgl_jalan->periode;
            $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
            $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
            $nama_lokasi = $get_lokasi->nama_lokasi;

            $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
            $nama_company = $get_company->nama_company;

            return view('admin.penerimaandetail.index', compact('penerimaan','penerimaandetail','list_url','Produk','Satuan','period', 'nama_lokasi','nama_company'));
        }
        else{
            alert()->success('Status POSTED / Periode Telah CLOSED: '.$tanggal,'GAGAL!')->persistent('Close');
            return redirect()->back();
        }
    }

    public function Showdetail()
    {
        $konek = self::konek();
        $penerimaandetail= PenerimaanDetail::on($konek)->with('produk','satuan')->where('no_penerimaan',request()->id)
        ->orderBy('created_at', 'desc')->get();

        $output = array();

        if($penerimaandetail){
            foreach($penerimaandetail as $row)
            {
                $subtotal =  number_format(($row->harga * $row->qty) + ($row->landedcost*$row->qty),2,",",".");
                $output[] = array(
                    'no_penerimaan'=>$row->no_penerimaan,
                    'produk'=>$row->produk->nama_produk,
                    'satuan'=>$row->satuan->nama_satuan,
                    'partnumber'=>$row->partnumber,
                    'no_mesin'=>$row->no_mesin,
                    'qty'=>$row->qty,
                    'qty_retur'=>$row->qty_retur,
                    'harga'=>$row->harga,
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

    public function lrb_post($coa, $harga, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr)
    {
        $konek = self::konek2();
        //INI UNTUK MENGARAHKAN TRANSAKSI DILOKASI AGAR UPDATE KE LEDGER DAN BALANCE HO (HEAD OFFICE), KECUALI KODE COMPANY GUT (04 / 0401) KARENA LEDGER DAN BALANCE GUT HO DAN GUT JKT TERPISAH
        if(auth()->user()->kode_company != '04' || auth()->user()->kode_company != '0401'){
            $lokasi = 'HO';
        }

        //UPDATE LABA RUGI BERJALAN PADA TABEL LABA RUGI BERJALAN
        if($coa->account_type == '5' || $coa->account_type == '4' || $coa->account_type == '6'){
            $cek_lrb = Labarugiberjalan::on($konek)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
            //JIKA LABARUGI BERJALAN PERIODE TRANSAKSI SUDAH ADA
            if($cek_lrb != null){
                $begin_awal = $cek_lrb->beginning_balance;
                $debit_awal = $cek_lrb->debit;
                $kredit_awal = $cek_lrb->kredit;

                //APBILA NORMAL BALANCE ACCOUNT DEBIT, MAKA PADA TABEL LABA RUGI BERJALAN AKAN BERTAMBAH DI SISI DEBIT
                if($dbkr == 'D'){
                    $debit_akhir = $debit_awal + $harga;
                    $kredit_akhir = $kredit_awal;
                    $end = $begin_awal - $debit_akhir + $kredit_awal;
                }
                //APBILA NORMAL BALANCE ACCOUNT KREDIT, MAKA PADA TABEL LABA RUGI BERJALAN AKAN BERTAMBAH DI SISI KREDIT
                else{
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

                //CEK SETELAH, APABILA ADA PERIODE LAIN SETELAH PERIODE TRANSAKSI
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

                //UPDATE LABA RUGI BERJALAN PADA ACCOUNT BALANCE
                $cek_coa = Coa::on('mysql4')->where('account','3.2.00.000.00.002')->first();
                //APBILA NORMAL BALANCE ACCOUNT DEBIT, MAKA PADA ACCOUNT BALANCE LABA RUGI BERJALAN AKAN BERTAMBAH DI SISI DEBIT
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

                    //CEK SETELAH, APABILA ADA PERIODE LAIN SETELAH PERIODE TRANSAKSI
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
                //APBILA NORMAL BALANCE ACCOUNT KREDIT, MAKA PADA ACCOUNT BALANCE LABA RUGI BERJALAN AKAN BERTAMBAH DI SISI KREDIT
                else{
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

                    //CEK SETELAH, APABILA ADA PERIODE LAIN SETELAH PERIODE TRANSAKSI
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
                //JIKA LABARUGI BERJALAN PERIODE TRANSAKSI BELUM ADA
                //CEK SEBELUM, UNTUK MENGAMBIL NILAI ENDING PADA PERIODE SEBELUMNYA
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

                //CREATE ACCOUNT LABA RUGI BERJALAN PADA ACCOUNT BALANCE
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
        //INI UNTUK MENGARAHKAN TRANSAKSI DILOKASI AGAR UPDATE KE LEDGER DAN BALANCE HO (HEAD OFFICE)
        // KECUALI KODE COMPANY GUT (04 / 0401) KARENA LEDGER DAN BALANCE GUT HO DAN GUT JKT TERPISAH
        if(auth()->user()->kode_company != '04' || auth()->user()->kode_company != '0401'){
            $lokasi = 'HO';
        }

        //UPDATE LABA RUGI BERJALAN PADA TABEL LABA RUGI BERJALAN
        //LABA RUGI BERJALAN HANYA UPDATE APABILA TIPE ACCOUNT 5 / 4 / 6 (BISA DILIHAT DI MASTER COA SISTEM GL)
        if($coa->account_type == '5' || $coa->account_type == '4' || $coa->account_type == '6'){
            $cek_lrb = Labarugiberjalan::on($konek)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                        
            $begin_awal = $cek_lrb->beginning_balance;
            $debit_awal = $cek_lrb->debit;
            $kredit_awal = $cek_lrb->kredit;

            //APBILA NORMAL BALANCE ACCOUNT DEBIT, MAKA PADA TABEL LABA RUGI BERJALAN AKAN BERKURANG DI SISI DEBIT
            if($dbkr == 'D'){
                $debit_akhir = $debit_awal - $harga;
                $kredit_akhir = $kredit_awal;
                $end = $begin_awal - $debit_akhir + $kredit_awal;
            }
            //APBILA NORMAL BALANCE ACCOUNT KREDIT, MAKA PADA TABEL LABA RUGI BERJALAN AKAN BERKURANG DI SISI KREDIT
            else{
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

            //CEK SETELAH, APABILA ADA PERIODE LAIN SETELAH PERIODE TRANSAKSI
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

            //UPDATE ACCOUNT BALANCE LABA RUGI BERJALAN
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

                //CEK SETELAH, APABILA ADA PERIODE LAIN SETELAH PERIODE TRANSAKSI
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

                //CEK SETELAH, APABILA ADA PERIODE LAIN SETELAH PERIODE TRANSAKSI
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

    //UNTUK CEK APAKAH TRANSAKSI YANG DIBUAT SESUAI DENGAN PERIODE AKTIF
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

    //UNTUK CEK APABILA UNPOSTING DILAKUKAN STOK INVENTORY JADI MINUS / TIDAK, JIKA MINUS MAKA PROSES AKAN DIBATALKAN
    function produkChecker($no_penerimaan, $tahun, $bulan, $tanggal_baru, $tgl, $penerimaan, $lokasi)
    {   
        $konek = self::konek();
        $penerimaandetail = PenerimaanDetail::on($konek)->with('produk','satuan')->where('no_penerimaan', request()->id)->get();
        $no_penerimaan = request()->id;
             
        $data = array();

        if(!empty($penerimaandetail)){
            foreach ($penerimaandetail as $rowdata){
                $data[] = array(
                    'no_penerimaan'=>$no_penerimaan,
                    'kode_produk'=>$rowdata->kode_produk,
                    'kode_satuan'=>$rowdata->kode_satuan,
                    'qty'=>$rowdata->qty,
                    'partnumber'=>$rowdata->partnumber,
                );         
            }
        }

        $no_pembelian = $penerimaan->no_pembelian;
        $pembelian = Pembelian::on($konek)->find($no_pembelian);
        $jenis_po = $pembelian->jenis_po;
        // dd($jenis_po);
        if($jenis_po == 'Stock'){
            if(!empty($penerimaandetail)){
                $leng = count($penerimaandetail);

                $i = 0;
                for($i = 0; $i < $leng; $i++){
                    $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$lokasi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

                    $cek_tipe = Produk::on($konek)->where('id',$data[$i]['kode_produk'])->first();

                    $produk_awal = $tb_item_bulanan->kode_produk;

                    $amount_begin = $tb_item_bulanan->begin_amount;
                    $stok_awal_1 = $tb_item_bulanan->in_stock;
                    $amount_awal_1 = $tb_item_bulanan->in_amount;
                    $amount_out = $tb_item_bulanan->out_amount;
                    $amount_sale = $tb_item_bulanan->sale_amount;

                    if ($cek_tipe->tipe_produk == 'Serial'){
                        if($cek_tipe->kode_kategori == 'UNIT' || $cek_tipe->kode_kategori == 'BAN'){

                            $tot_in_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$lokasi)->where('in_stock',1)->get();

                            $tot_op_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$lokasi)->where('stock_opname',1)->get();

                            $tot_out_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$lokasi)->where('sale_stock',1)->get();

                            $tot_sale_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$lokasi)->where('out_stock',1)->get();

                            $tot_adj_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$lokasi)->where('adjustment_stock',1)->get();

                            $tot_trfin_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$lokasi)->where('trf_in',1)->get();

                            $tot_trfout_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$lokasi)->where('trf_out',1)->get();

                            $tot_rb_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$lokasi)->where('retur_beli_stock',1)->get();

                            $tot_rj_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$lokasi)->where('retur_jual_stock',1)->get();

                            $tot_dis_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$lokasi)->where('disassembling_stock',1)->get();

                            $tot_ass_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$lokasi)->where('assembling_stock',1)->get();

                            if ($tot_adj_bulanan == null){
                                $tot_adj_qty = 0;
                            }else {
                                $tot_adj_qty = count($tot_adj_bulanan);
                            }

                            if ($tot_in_bulanan == null){
                                $tot_in_qty = 0;
                            }else {
                                $tot_in_qty = count($tot_in_bulanan);
                            }

                            if ($tot_out_bulanan == null){
                                $tot_out_qty = 0;
                            }else {
                                $tot_out_qty = count($tot_out_bulanan);
                            }

                            if ($tot_op_bulanan == null){
                                $tot_op_qty = 0;
                            }else {
                                $tot_op_qty = count($tot_op_bulanan);
                            }

                            if ($tot_sale_bulanan == null){
                                $tot_sale_qty = 0;
                            }else {
                                $tot_sale_qty = count($tot_sale_bulanan);
                            }

                            if ($tot_trfin_bulanan == null){
                                $tot_trfin_qty = 0;
                            }else {
                                $tot_trfin_qty = count($tot_trfin_bulanan);
                            }

                            if ($tot_trfout_bulanan == null){
                                $tot_trfout_qty = 0;
                            }else {
                                $tot_trfout_qty = count($tot_trfout_bulanan);
                            }

                            if ($tot_rb_bulanan == null){
                                $tot_rb_qty = 0;
                            }else {
                                $tot_rb_qty = count($tot_rb_bulanan);
                            }

                            if ($tot_rj_bulanan == null){
                                $tot_rj_qty = 0;
                            }else {
                                $tot_rj_qty = count($tot_rj_bulanan);
                            }

                            if ($tot_dis_bulanan == null){
                                $tot_dis_qty = 0;
                            }else {
                                $tot_dis_qty = count($tot_dis_bulanan);
                            }

                            if ($tot_ass_bulanan == null){
                                $tot_ass_qty = 0;
                            }else {
                                $tot_ass_qty = count($tot_ass_bulanan);
                            }

                            $stock_ending = $tot_in_qty - $tot_out_qty - $tot_sale_qty - $tot_trfout_qty + $tot_op_qty + $tot_adj_qty + $tot_trfin_qty - $tot_rb_qty + $tot_rj_qty - $tot_dis_qty + $tot_ass_qty;
                        }else{
                            $stock_ending = $tb_item_bulanan->ending_stock;
                        } 
                        }else{
                            $stock_ending = $tb_item_bulanan->ending_stock;
                        } 
                        
                        $stock_out = $tb_item_bulanan->out_stock;
                                    
                        $amount_adj = $tb_item_bulanan->adjustment_amount;
                        $amount_op = $tb_item_bulanan->amount_opname;
                        $stock_trfin = $tb_item_bulanan->trf_in;
                        $amount_trfin = $tb_item_bulanan->trf_in_amount;
                        $stock_trfout = $tb_item_bulanan->trf_out;
                        $amount_trfout = $tb_item_bulanan->trf_out_amount;
                        $retur_beli_amount = $tb_item_bulanan->retur_beli_amount;
                        $retur_jual_amount = $tb_item_bulanan->retur_jual_amount;
                        $disassembling_amount = $tb_item_bulanan->disassembling_amount;
                        $assembling_amount = $tb_item_bulanan->assembling_amount;

                        $produk = Produk::on($konek)->find($data[$i]['kode_produk']);
                        $penerimaandetail2 = PenerimaanDetail::on($konek)->where('no_penerimaan', request()->id)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();
                        $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                        $harga1 = $penerimaandetail2->harga;
                        $qty1 = $penerimaandetail2->qty;
                        $landed = $penerimaandetail2->landedcost;
                        $landed_final = $landed;
                        $harga_final = ($harga1 + $landed_final) / $konversi->nilai_konversi;
                                    
                        $qty_baru = $data[$i]['qty']*$konversi->nilai_konversi;
                        $stok_masuk = $stok_awal_1 - $qty_baru;
                        $amount_masuk = $amount_awal_1 - ($harga_final*$qty_baru);
                        if ($cek_tipe->tipe_produk == 'Serial'){
                            if($cek_tipe->kode_kategori == 'UNIT' || $cek_tipe->kode_kategori == 'BAN'){
                                $end_stok2 = 0;
                            }else{
                                $end_stok2 = $stock_ending - $qty_baru;
                            }
                        }else {
                            $end_stok2 = $stock_ending - $qty_baru;
                        }

                        $end_stok = $stock_ending - $qty_baru;
                        $end_amount = $amount_begin + $amount_masuk - $amount_out - $amount_sale + $amount_trfin - $amount_trfout + $amount_adj + $amount_op - $retur_beli_amount + $retur_jual_amount - $disassembling_amount + $assembling_amount;
                        
                        if($end_stok < 0){
                            exit();
                        }
                    else{
                        $qty_baru2 = $data[$i]['qty'];

                        $penerimaan = Penerimaan::on($konek)->find(request()->id);
                        $pembelian = Pembelian::on($konek)->find($penerimaan->no_pembelian);
                        $pembeliandetail1 = PembelianDetail::on($konek)->where('no_pembelian', $pembelian->no_pembelian)->where('kode_produk',$data[$i]['kode_produk'])->first();

                        $qty_rec = $pembeliandetail1->qty_received;
                        $pembeliandetail1->qty_received = $qty_rec - $qty_baru2;

                        $tgl_terima1 = $penerimaan->tanggal_penerimaan;
                        $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_terima1)->year;
                        $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_terima1)->month;

                        $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                        $status_reopen = $reopen->reopen_status;

                        if($status_reopen == 'true'){
                            $tgl_terima = $penerimaan->tanggal_penerimaan;
                            $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_terima)->year;
                            $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_terima)->month;

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
                                $penerimaandetail2 = PenerimaanDetail::on($konek)->where('no_penerimaan', request()->id)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                                $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                                $harga1 = $penerimaandetail2->harga;
                                $qty1 = $penerimaandetail2->qty;
                                $landed = $penerimaandetail2->landedcost;
                                $landed_final = $landed;
                                $harga_final = ($harga1 + $landed_final) / $konversi->nilai_konversi;
                                               
                                $stok_masuk = $data[$i]['qty']*$konversi->nilai_konversi;
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

                                if($tb_item_bulanan2 != null){
                                    $bs = $tb_item_bulanan2->begin_stock;
                                    $ba = $tb_item_bulanan2->begin_amount;
                                    $es = $tb_item_bulanan2->ending_stock;
                                    $ea = $tb_item_bulanan2->ending_amount;

                                    $begin_stock1 = $bs - $stok_masuk;
                                    $begin_amount1 = $ba - $amount_masuk;
                                    $end_stok1 = $es - $stok_masuk;
                                    $end_amount1 = $ea - $amount_masuk;

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
        }
        return true;
    }
    
    //UNTUK MENAMPILKAN DATA ZOOM JURNAL
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
        //AMBIL TRANSAKSI TERKAI NO JURNAL
        $cek_ar = Penerimaan::on($konek)->where('no_journal', request()->no_journal)->first();

        $output = array(
            'journal_date'=>Carbon\Carbon::parse($cek->journal_date)->format('d/m/Y'),
            'reference'=>$cek->reference,
            'created_at'=>($cek_ar->created_at)->format('d/m/Y H:i:s'),
            'updated_by'=>$cek->updated_by,
            'status'=>$cek_ar->status,
        );
        return response()->json($output);
    }

    public function hitungjurnal()
    {
        $konek = self::konek();
        $cek_company = Auth()->user()->kode_company;
        $lokasi = auth()->user()->kode_lokasi;

        $penerimaan_header = Penerimaan::on($konek)->where('tanggal_penerimaan','>=','2022-01-01')->where('tanggal_penerimaan','<','2022-07-01')->get();

        foreach ($penerimaan_header as $row){
            $penerimaan = Penerimaan::on($konek)->find($row->no_penerimaan);
            $penerimaandetail = PenerimaanDetail::on($konek)->where('no_penerimaan', $row->no_penerimaan)->get();
            $leng = count($penerimaandetail);
            $data = array();

            //AMBIL SEMUA PRODUK PADA DETAIL TRANSAKSI
            foreach ($penerimaandetail as $rowdata){
                $kodeP = $rowdata->kode_produk;

                $data[] = array(
                    'kode_produk'=>$kodeP,
                );
            }

            //CEK APAKAH KATEGORI PADA PRODUK DETAIL SUDAH DI SETUP COA / BELUM
            for ($i = 0; $i < $leng; $i++) { 
                $cek_produk = Produk::on($konek)->where('id', $data[$i]['kode_produk'])->first();
                //CEK COA PADA MASTER KATEGORI
                if($cek_produk->kode_kategori == 'APD'){
                    if($cek_company == '04'){
                        $cek_kategori = KategoriProduk::where('kode_kategori','APD')->where('coa_gut',null)->first();
                    }else if($cek_company == '0401'){
                        $cek_kategori = KategoriProduk::where('kode_kategori','APD')->where('coa_gutjkt',null)->first();
                    }else if($cek_company == '03'){
                        $cek_kategori = KategoriProduk::where('kode_kategori','APD')->where('coa_emkl',null)->first();
                    }else if($cek_company == '05'){
                        $cek_kategori = KategoriProduk::where('kode_kategori','APD')->where('coa_sub',null)->first();
                    }else if($cek_company == '02'){
                        $cek_kategori = KategoriProduk::where('kode_kategori','APD')->where('coa_pbm',null)->first();
                    }
                }

                if($cek_produk->kode_kategori == 'BAN'){
                    if($cek_company == '04'){
                        $cek_kategori = KategoriProduk::where('kode_kategori','BAN')->where('coa_gut',null)->first();
                    }else if($cek_company == '0401'){
                        $cek_kategori = KategoriProduk::where('kode_kategori','BAN')->where('coa_gutjkt',null)->first();
                    }else if($cek_company == '03'){
                        $cek_kategori = KategoriProduk::where('kode_kategori','BAN')->where('coa_emkl',null)->first();
                    }else if($cek_company == '05'){
                        $cek_kategori = KategoriProduk::where('kode_kategori','BAN')->where('coa_sub',null)->first();
                    }else if($cek_company == '02'){
                        $cek_kategori = KategoriProduk::where('kode_kategori','BAN')->where('coa_pbm',null)->first();
                    }
                }

                if($cek_produk->kode_kategori == 'BBM'){
                    if($cek_company == '04'){
                        $cek_kategori = KategoriProduk::where('kode_kategori','BBM')->where('coa_gut',null)->first();
                    }else if($cek_company == '0401'){
                        $cek_kategori = KategoriProduk::where('kode_kategori','BBM')->where('coa_gutjkt',null)->first();
                    }else if($cek_company == '03'){
                        $cek_kategori = KategoriProduk::where('kode_kategori','BBM')->where('coa_emkl',null)->first();
                    }else if($cek_company == '05'){
                        $cek_kategori = KategoriProduk::where('kode_kategori','BBM')->where('coa_sub',null)->first();
                    }else if($cek_company == '02'){
                        $cek_kategori = KategoriProduk::where('kode_kategori','BBM')->where('coa_pbm',null)->first();
                    }
                }

                if($cek_produk->kode_kategori == 'OLI'){
                    if($cek_company == '04'){
                        $cek_kategori = KategoriProduk::where('kode_kategori','OLI')->where('coa_gut',null)->first();
                    }else if($cek_company == '0401'){
                        $cek_kategori = KategoriProduk::where('kode_kategori','OLI')->where('coa_gutjkt',null)->first();
                    }else if($cek_company == '03'){
                        $cek_kategori = KategoriProduk::where('kode_kategori','OLI')->where('coa_emkl',null)->first();
                    }else if($cek_company == '05'){
                        $cek_kategori = KategoriProduk::where('kode_kategori','OLI')->where('coa_sub',null)->first();
                    }else if($cek_company == '02'){
                        $cek_kategori = KategoriProduk::where('kode_kategori','OLI')->where('coa_pbm',null)->first();
                    }
                }

                if($cek_produk->kode_kategori == 'SPRT'){
                    if($cek_company == '04'){
                        $cek_kategori = KategoriProduk::where('kode_kategori','SPRT')->where('coa_gut',null)->first();
                    }else if($cek_company == '0401'){
                        $cek_kategori = KategoriProduk::where('kode_kategori','SPRT')->where('coa_gutjkt',null)->first();
                    }else if($cek_company == '03'){
                        $cek_kategori = KategoriProduk::where('kode_kategori','SPRT')->where('coa_emkl',null)->first();
                    }else if($cek_company == '05'){
                        $cek_kategori = KategoriProduk::where('kode_kategori','SPRT')->where('coa_sub',null)->first();
                    }else if($cek_company == '02'){
                        $cek_kategori = KategoriProduk::where('kode_kategori','SPRT')->where('coa_pbm',null)->first();
                    }
                }

                // if($cek_produk->kode_kategori == 'UNIT'){
                //     if($cek_company == '04'){
                //         $cek_kategori = KategoriProduk::where('kode_kategori','UNIT')->where('coa_gut',null)->first();
                //     }else if($cek_company == '0401'){
                //         $cek_kategori = KategoriProduk::where('kode_kategori','UNIT')->where('coa_gutjkt',null)->first();
                //     }else if($cek_company == '03'){
                //         $cek_kategori = KategoriProduk::where('kode_kategori','UNIT')->where('coa_emkl',null)->first();
                //     }else if($cek_company == '05'){
                //         $cek_kategori = KategoriProduk::where('kode_kategori','UNIT')->where('coa_sub',null)->first();
                //     }else if($cek_company == '02'){
                //         $cek_kategori = KategoriProduk::where('kode_kategori','UNIT')->where('coa_pbm',null)->first();
                //     }
                // }
                
                if($cek_produk->kode_kategori == 'SLDG'){
                    if($cek_company == '04'){
                        $cek_kategori = KategoriProduk::where('kode_kategori','SLDG')->where('coa_gut',null)->first();
                    }else if($cek_company == '0401'){
                        $cek_kategori = KategoriProduk::where('kode_kategori','SLDG')->where('coa_gutjkt',null)->first();
                    }else if($cek_company == '03'){
                        $cek_kategori = KategoriProduk::where('kode_kategori','SLDG')->where('coa_emkl',null)->first();
                    }else if($cek_company == '05'){
                        $cek_kategori = KategoriProduk::where('kode_kategori','SLDG')->where('coa_sub',null)->first();
                    }else if($cek_company == '02'){
                        $cek_kategori = KategoriProduk::where('kode_kategori','SLDG')->where('coa_pbm',null)->first();
                    }
                }

                //JIKA ADA KATEGORI YANG BELUM DI SETUP COA MAKA PROSES AKAN DIHENTIKAN
                if($cek_kategori != null){
                    $message = [
                        'success' => false,
                        'title' => 'Simpan',
                        'message' => 'Kategori: '.$cek_kategori->kode_kategori.' belum memiliki COA Persediaan, silahkan lengkapi terlebih dahulu.',
                    ];
                    return response()->json($message);
                }
            }

            //KONVERSI TANGGAL TRANSAKSI MENJADI TANGGAL BARU, AGAR DAPAT DI UPATE KE TABEL ITEM BULANAN
            $tgl = $penerimaan->tanggal_penerimaan;
            $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
            $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
            $tanggal = '01';

            $tanggal_baru = Carbon\Carbon::createFromDate($tahun, $bulan, $tanggal)->toDateString();

            //UPDATE JURNAL
            $penerimaandetail = PenerimaanDetail::on($konek)->with('produk','satuan')->where('no_penerimaan', $row->no_penerimaan)->get();
            $no_penerimaan = $row->no_penerimaan;
            $data = array();

            foreach ($penerimaandetail as $rowdata){
                $data[] = array(
                    'no_penerimaan'=>$no_penerimaan,
                    'kode_produk'=>$rowdata->kode_produk,
                    'kode_satuan'=>$rowdata->kode_satuan,
                    'qty'=>$rowdata->qty,
                    'partnumber'=>$rowdata->partnumber,
                    'no_mesin'=>$rowdata->no_mesin,
                    'harga'=>$rowdata->harga,
                    'landedcost'=>$rowdata->landedcost,
                );
            }
            $leng = count($penerimaandetail);
            
            if($cek_company == '04' || $cek_company == '0401' || $cek_company == '03' || $cek_company == '05' || $cek_company == '02'){
                $konek2 = self::konek2();

                $total_qty = 0;
                $total_harga = 0;
                $grand_total = 0;
                $detail = PenerimaanDetail::on($konek)->where('no_penerimaan',$penerimaan->no_penerimaan)->get();
                foreach ($detail as $row){
                    $total_qty += $row->qty;
                    $subtotal = ($row->harga + $row->landedcost) * $row->qty;
                    $total_harga += $subtotal;
                    $grand_total = $total_harga;
                }

                $gt_apd = 0;
                $gt_ban = 0;
                $gt_bbm = 0;
                $gt_oli = 0;
                $gt_sprt = 0;
                $gt_unit = 0;
                $gt_sldg = 0;

                //UNTUK MENJUMLAHKAN NILAI BARANG PADA DETAIL BERDASARKAN KATEGORI
                for ($i = 0; $i < $leng; $i++) { 
                    $cek_produk = Produk::on($konek)->where('id', $data[$i]['kode_produk'])->first();

                    $bulan = Carbon\Carbon::parse($penerimaan->tanggal_penerimaan)->format('m');
                    $tahun = Carbon\Carbon::parse($penerimaan->tanggal_penerimaan)->format('Y');

                    if($cek_produk->kode_kategori == 'APD'){
                        $gt_apd += $data[$i]['qty'] * ($data[$i]['harga'] + $data[$i]['landedcost']);
                    }

                    if($cek_produk->kode_kategori == 'BAN'){
                        $gt_ban += $data[$i]['qty'] * ($data[$i]['harga'] + $data[$i]['landedcost']);
                    }

                    if($cek_produk->kode_kategori == 'BBM'){
                        $gt_bbm += $data[$i]['qty'] * ($data[$i]['harga'] + $data[$i]['landedcost']);
                    }

                    if($cek_produk->kode_kategori == 'OLI'){
                        $gt_oli += $data[$i]['qty'] * ($data[$i]['harga'] + $data[$i]['landedcost']);
                    }

                    if($cek_produk->kode_kategori == 'SPRT'){
                        $gt_sprt += $data[$i]['qty'] * ($data[$i]['harga'] + $data[$i]['landedcost']);
                    }

                    if($cek_produk->kode_kategori == 'UNIT'){
                        $gt_unit += $data[$i]['qty'] * ($data[$i]['harga'] + $data[$i]['landedcost']);
                    }
                    
                    if($cek_produk->kode_kategori == 'SLDG'){
                        $gt_sldg += $data[$i]['qty'] * ($data[$i]['harga'] + $data[$i]['landedcost']);
                    }
                }

                //UPDATE ACCOUNT BALANCE SESUA COA YANG ADA PADA KATEGORI
                if($gt_apd > 0){
                    if ($cek_company == '04') {
                        $kategori = KategoriProduk::where('kode_kategori', 'APD')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_gut)->first();
                    }else if($cek_company == '0401'){
                        $kategori = KategoriProduk::where('kode_kategori', 'APD')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_gutjkt)->first();
                    }else if($cek_company == '03'){
                        $kategori = KategoriProduk::where('kode_kategori', 'APD')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_emkl)->first();
                    }else if($cek_company == '02'){
                        $kategori = KategoriProduk::where('kode_kategori', 'APD')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_pbm)->first();
                    }else if($cek_company == '01'){
                        $kategori = KategoriProduk::where('kode_kategori', 'APD')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_depo)->first();
                    }else if($cek_company == '05'){
                        $kategori = KategoriProduk::where('kode_kategori', 'APD')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
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
                                    $begin = $cek_setelah->beginning_balance + $gt_apd;
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
                                    $begin = $cek_setelah->beginning_balance + $gt_apd;
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
                        'no_journal'=>$penerimaan->no_journal,
                        'journal_date'=>$penerimaan->tanggal_penerimaan,
                        'db_cr'=>'D',
                        'reference'=>$penerimaan->no_penerimaan,
                        'debit'=>round($gt_apd),
                        'kode_lokasi'=>$lokasi,
                    ];
                    $update = Ledger::on($konek2)->create($update_ledger);

                    $type = 'Inventory';
                    $transaksi = $penerimaan;
                    $tgl_trans = $penerimaan->tanggal_penerimaan;
                    $harga_acc = round($gt_apd);
                    $dbkr = 'D';
                    $update_accbalance = $this->accbalance_debit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                    $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                }

                if($gt_ban > 0){
                    if ($cek_company == '04') {
                        $kategori = KategoriProduk::where('kode_kategori', 'BAN')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_gut)->first();
                    }else if($cek_company == '0401'){
                        $kategori = KategoriProduk::where('kode_kategori', 'BAN')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_gutjkt)->first();
                    }else if($cek_company == '03'){
                        $kategori = KategoriProduk::where('kode_kategori', 'BAN')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_emkl)->first();
                    }else if($cek_company == '02'){
                        $kategori = KategoriProduk::where('kode_kategori', 'BAN')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_pbm)->first();
                    }else if($cek_company == '01'){
                        $kategori = KategoriProduk::where('kode_kategori', 'BAN')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_depo)->first();
                    }else if($cek_company == '05'){
                        $kategori = KategoriProduk::where('kode_kategori', 'BAN')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
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
                                    $begin = $cek_setelah->beginning_balance + $gt_ban;
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
                                    $begin = $cek_setelah->beginning_balance + $gt_ban;
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
                        'no_journal'=>$penerimaan->no_journal,
                        'journal_date'=>$penerimaan->tanggal_penerimaan,
                        'db_cr'=>'D',
                        'reference'=>$penerimaan->no_penerimaan,
                        'debit'=>round($gt_ban),
                        'kode_lokasi'=>$lokasi,
                    ];
                    $update = Ledger::on($konek2)->create($update_ledger);

                    $type = 'Inventory';
                    $transaksi = $penerimaan;
                    $tgl_trans = $penerimaan->tanggal_penerimaan;
                    $harga_acc = round($gt_ban);
                    $dbkr = 'D';
                    $update_accbalance = $this->accbalance_debit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                    $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                }

                if($gt_bbm > 0){
                    if ($cek_company == '04') {
                        $kategori = KategoriProduk::where('kode_kategori', 'BBM')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_gut)->first();
                    }else if($cek_company == '0401'){
                        $kategori = KategoriProduk::where('kode_kategori', 'BBM')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_gutjkt)->first();
                    }else if($cek_company == '03'){
                        $kategori = KategoriProduk::where('kode_kategori', 'BBM')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_emkl)->first();
                    }else if($cek_company == '02'){
                        $kategori = KategoriProduk::where('kode_kategori', 'BBM')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_pbm)->first();
                    }else if($cek_company == '01'){
                        $kategori = KategoriProduk::where('kode_kategori', 'BBM')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_depo)->first();
                    }else if($cek_company == '05'){
                        $kategori = KategoriProduk::where('kode_kategori', 'BBM')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
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
                                    $begin = $cek_setelah->beginning_balance + $gt_bbm;
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
                                    $begin = $cek_setelah->beginning_balance + $gt_bbm;
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
                        'no_journal'=>$penerimaan->no_journal,
                        'journal_date'=>$penerimaan->tanggal_penerimaan,
                        'db_cr'=>'D',
                        'reference'=>$penerimaan->no_penerimaan,
                        'debit'=>round($gt_bbm),
                        'kode_lokasi'=>$lokasi,
                    ];
                    $update = Ledger::on($konek2)->create($update_ledger);

                    $type = 'Inventory';
                    $transaksi = $penerimaan;
                    $tgl_trans = $penerimaan->tanggal_penerimaan;
                    $harga_acc = round($gt_bbm);
                    $dbkr = 'D';
                    $update_accbalance = $this->accbalance_debit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                    $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                }

                if($gt_oli > 0){
                    if ($cek_company == '04') {
                        $kategori = KategoriProduk::where('kode_kategori', 'OLI')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_gut)->first();
                    }else if($cek_company == '0401'){
                        $kategori = KategoriProduk::where('kode_kategori', 'OLI')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_gutjkt)->first();
                    }else if($cek_company == '03'){
                        $kategori = KategoriProduk::where('kode_kategori', 'OLI')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_emkl)->first();
                    }else if($cek_company == '02'){
                        $kategori = KategoriProduk::where('kode_kategori', 'OLI')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_pbm)->first();
                    }else if($cek_company == '01'){
                        $kategori = KategoriProduk::where('kode_kategori', 'OLI')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_depo)->first();
                    }else if($cek_company == '05'){
                        $kategori = KategoriProduk::where('kode_kategori', 'OLI')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
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
                                    $begin = $cek_setelah->beginning_balance + $gt_oli;
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
                                    $begin = $cek_setelah->beginning_balance + $gt_oli;
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
                        'no_journal'=>$penerimaan->no_journal,
                        'journal_date'=>$penerimaan->tanggal_penerimaan,
                        'db_cr'=>'D',
                        'reference'=>$penerimaan->no_penerimaan,
                        'debit'=>round($gt_oli),
                        'kode_lokasi'=>$lokasi,
                    ];
                    $update = Ledger::on($konek2)->create($update_ledger);

                    $type = 'Inventory';
                    $transaksi = $penerimaan;
                    $tgl_trans = $penerimaan->tanggal_penerimaan;
                    $harga_acc = round($gt_oli);
                    $dbkr = 'D';
                    $update_accbalance = $this->accbalance_debit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                    $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                }

                if($gt_sprt > 0){
                    if ($cek_company == '04') {
                        $kategori = KategoriProduk::where('kode_kategori', 'SPRT')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_gut)->first();
                    }else if($cek_company == '0401'){
                        $kategori = KategoriProduk::where('kode_kategori', 'SPRT')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_gutjkt)->first();
                    }else if($cek_company == '03'){
                        $kategori = KategoriProduk::where('kode_kategori', 'SPRT')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_emkl)->first();
                    }else if($cek_company == '02'){
                        $kategori = KategoriProduk::where('kode_kategori', 'SPRT')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_pbm)->first();
                    }else if($cek_company == '01'){
                        $kategori = KategoriProduk::where('kode_kategori', 'SPRT')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_depo)->first();
                    }else if($cek_company == '05'){
                        $kategori = KategoriProduk::where('kode_kategori', 'SPRT')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
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
                                    $begin = $cek_setelah->beginning_balance + $gt_sprt;
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
                                    $begin = $cek_setelah->beginning_balance + $gt_sprt;
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
                        'no_journal'=>$penerimaan->no_journal,
                        'journal_date'=>$penerimaan->tanggal_penerimaan,
                        'db_cr'=>'D',
                        'reference'=>$penerimaan->no_penerimaan,
                        'debit'=>round($gt_sprt),
                        'kode_lokasi'=>$lokasi,
                    ];
                    $update = Ledger::on($konek2)->create($update_ledger);

                    $type = 'Inventory';
                    $transaksi = $penerimaan;
                    $tgl_trans = $penerimaan->tanggal_penerimaan;
                    $harga_acc = round($gt_sprt);
                    $dbkr = 'D';
                    $update_accbalance = $this->accbalance_debit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                    $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                }

                if($gt_unit > 0){
                    if ($cek_company == '04') {
                        $kategori = KategoriProduk::where('kode_kategori', 'UNIT')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_gut)->first();
                    }else if($cek_company == '0401'){
                        $kategori = KategoriProduk::where('kode_kategori', 'UNIT')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_gutjkt)->first();
                    }else if($cek_company == '03'){
                        $kategori = KategoriProduk::where('kode_kategori', 'UNIT')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_emkl)->first();
                    }else if($cek_company == '02'){
                        $kategori = KategoriProduk::where('kode_kategori', 'UNIT')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_pbm)->first();
                    }else if($cek_company == '01'){
                        $kategori = KategoriProduk::where('kode_kategori', 'UNIT')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_depo)->first();
                    }else if($cek_company == '05'){
                        $kategori = KategoriProduk::where('kode_kategori', 'UNIT')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
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
                                    $begin = $cek_setelah->beginning_balance + $gt_unit;
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
                                    $begin = $cek_setelah->beginning_balance + $gt_unit;
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
                        'no_journal'=>$penerimaan->no_journal,
                        'journal_date'=>$penerimaan->tanggal_penerimaan,
                        'db_cr'=>'D',
                        'reference'=>$penerimaan->no_penerimaan,
                        'debit'=>round($gt_unit),
                        'kode_lokasi'=>$lokasi,
                    ];
                    $update = Ledger::on($konek2)->create($update_ledger);

                    $type = 'Inventory';
                    $transaksi = $penerimaan;
                    $tgl_trans = $penerimaan->tanggal_penerimaan;
                    $harga_acc = round($gt_unit);
                    $dbkr = 'D';
                    $update_accbalance = $this->accbalance_debit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                    $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                }
                
                if($gt_sldg > 0){
                    if ($cek_company == '04') {
                        $kategori = KategoriProduk::where('kode_kategori', 'SLDG')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_gut)->first();
                    }else if($cek_company == '0401'){
                        $kategori = KategoriProduk::where('kode_kategori', 'SLDG')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_gutjkt)->first();
                    }else if($cek_company == '03'){
                        $kategori = KategoriProduk::where('kode_kategori', 'SLDG')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_emkl)->first();
                    }else if($cek_company == '02'){
                        $kategori = KategoriProduk::where('kode_kategori', 'SLDG')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_pbm)->first();
                    }else if($cek_company == '01'){
                        $kategori = KategoriProduk::where('kode_kategori', 'SLDG')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_depo)->first();
                    }else if($cek_company == '05'){
                        $kategori = KategoriProduk::where('kode_kategori', 'SLDG')->first();
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
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
                                    $begin = $cek_setelah->beginning_balance + $gt_sldg;
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
                                    $begin = $cek_setelah->beginning_balance + $gt_sldg;
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
                        'no_journal'=>$penerimaan->no_journal,
                        'journal_date'=>$penerimaan->tanggal_penerimaan,
                        'db_cr'=>'D',
                        'reference'=>$penerimaan->no_penerimaan,
                        'debit'=>round($gt_sldg),
                        'kode_lokasi'=>$lokasi,
                    ];
                    $update = Ledger::on($konek2)->create($update_ledger);

                    $type = 'Inventory';
                    $transaksi = $penerimaan;
                    $tgl_trans = $penerimaan->tanggal_penerimaan;
                    $harga_acc = round($gt_sldg);
                    $dbkr = 'D';
                    $update_accbalance = $this->accbalance_debit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                    $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                }
                
                //UPDATE ACCOUNT BALANCE COA HUTANG PEMBELIAN YANG BELUM DITAGIH
                $coa_hutang = Coa::where('kode_coa', '140')->first();
                $cek_balance2 = AccBalance::on($konek2)->where('account',$coa_hutang->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                if ($cek_balance2 == null) {
                    //CEK SEBELUM
                    $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_hutang->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                    if($cek_sebelum != null){
                        $update_acc = [
                            'periode'=>$tanggal_baru,
                            'fiscalyear'=>$tahun,
                            'account'=>$coa_hutang->account,
                            'beginning_balance'=>$cek_sebelum->ending_balance,
                            'debet'=>0,
                            'kredit'=>0,
                            'ending_balance'=>$cek_sebelum->ending_balance,
                            'kode_lokasi'=>$lokasi,
                        ];

                        $update_balance = AccBalance::on($konek2)->where('account',$coa_hutang->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                    }else{
                        $update_acc = [
                            'periode'=>$tanggal_baru,
                            'fiscalyear'=>$tahun,
                            'account'=>$coa_hutang->account,
                            'beginning_balance'=>0,
                            'debet'=>0,
                            'kredit'=>0,
                            'ending_balance'=>0,
                            'kode_lokasi'=>$lokasi,
                        ];

                        $update_balance = AccBalance::on($konek2)->where('account',$coa_hutang->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                    }

                    //CEK SETELAH
                    $i = $bulan;
                    $cek_setelah = AccBalance::on($konek2)->where('account',$coa_hutang->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                    if ($cek_setelah != null) {
                        for($i = $bulan; $i <= 12; $i++){
                            $cek_setelah = AccBalance::on($konek2)->where('account',$coa_hutang->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                            if($cek_setelah != null){
                                $begin = $cek_setelah->beginning_balance + $grand_total;
                                $debit = $cek_setelah->debet;
                                $kredit = $cek_setelah->kredit;
                                if($coa_hutang->normal_balance == 'D'){
                                    $ending_balance = $begin + $debit - $kredit;
                                }else{
                                    $ending_balance = $begin - $debit + $kredit;
                                }

                                $tabel_baru = [
                                    'beginning_balance'=>$begin,
                                    'ending_balance'=>$ending_balance,
                                ];

                                $update_balance = AccBalance::on($konek2)->where('account',$coa_hutang->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                            }
                        }
                    }
                }else{
                    //CEK SETELAH
                    $i = $bulan;
                    $cek_setelah = AccBalance::on($konek2)->where('account',$coa_hutang->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                    if ($cek_setelah != null) {
                        for($i = $bulan; $i <= 12; $i++){
                            $cek_setelah = AccBalance::on($konek2)->where('account',$coa_hutang->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                            if($cek_setelah != null){
                                $begin = $cek_setelah->beginning_balance + $grand_total;
                                $debit = $cek_setelah->debet;
                                $kredit = $cek_setelah->kredit;
                                if($coa_hutang->normal_balance == 'D'){
                                    $ending_balance = $begin + $debit - $kredit;
                                }else{
                                    $ending_balance = $begin - $debit + $kredit;
                                }

                                $tabel_baru = [
                                    'beginning_balance'=>$begin,
                                    'ending_balance'=>$ending_balance,
                                ];

                                $update_balance = AccBalance::on($konek2)->where('account',$coa_hutang->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                            }
                        }
                    }
                }

                //UPDATE LEDGER COA HUTANG PEMBELIAN YANG BELUM DI TAGIH
                $update_ledger = [
                    'tahun'=>$tahun,
                    'periode'=>$bulan,
                    'account'=>$coa_hutang->account,
                    'no_journal'=>$penerimaan->no_journal,
                    'journal_date'=>$penerimaan->tanggal_penerimaan,
                    'db_cr'=>'K',
                    'reference'=>$penerimaan->no_penerimaan,
                    'kredit'=>$grand_total,
                    'kode_lokasi'=>$lokasi,
                ];
                $update = Ledger::on($konek2)->create($update_ledger);
                
                //UPDATE ACCOUNT BALANCE DAN LABA RUGI BERJALAN SESUAI PERIODE TRANSAKSI
                $type = 'Inventory';
                $transaksi = $penerimaan;
                $tgl_trans = $penerimaan->tanggal_penerimaan;
                $harga_acc = $grand_total;
                $dbkr = 'K';
                $update_accbalance = $this->accbalance_kredit_post($coa_hutang, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                $update_lrb = $this->lrb_post($coa_hutang, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
            }

        }
    }

    public function posting()
    {
        $konek = self::konek();
        $konek2 = self::konek2();
        $level = auth()->user()->level;
        $cek_company = Auth()->user()->kode_company;
        
        //UNTUK CEK APAKAH PERIODE SISTEM SEDANG REOPEN
        $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode','Disable')->first();
        //APABILA REOPEN, MAKA YANG BISA POSTING / UNPOSTING HANYA USER LEVEL YANG DI IJINKAN
        
        $todays = Carbon\Carbon::now();
        $today = $todays->toDateString();
        $terima = Penerimaan::on($konek)->find(request()->id);
        // if ($terima->tanggal_penerimaan != $today) {
        //     $message = [
        //         'success' => false,
        //         'title' => 'Simpan',
        //         'message' => 'Tanggal hari ini: '.$today.' Tanggal Penerimaan berbeda, Posting penerimaan hanya dapat dilakukan di hari yang sama.',
        //     ];
        //     return response()->json($message);
        // }
        
        if($cek_bulan == null || $level == 'superadministrator' || $level == 'user_rince' || $level == 'user_thomas'){
            if($cek_company == '04' || $cek_company == '0401' || $cek_company == '03' || $cek_company == '05' || $cek_company == '06' || $cek_company == '02'){
                $penerimaandetail = PenerimaanDetail::on($konek)->where('no_penerimaan', request()->id)->get();
                $leng = count($penerimaandetail);
                $data = array();

                $kat1 = 0;
                //AMBIL SEMUA PRODUK PADA DETAIL TRANSAKSI
                foreach ($penerimaandetail as $rowdata){
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

            //UNTUK MENCEGAH KETIDAKSAMAAN DATA APABILA USER MEMBUKA DUA TAB SISTEM YANG SAMA
            $penerimaan = Penerimaan::on($konek)->find(request()->id);
            $cek_status = $penerimaan->status;
            if($cek_status != 'OPEN'){
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'POST No. Penerimaan: '.$penerimaan->no_penerimaan.' sudah dilakukan! Pastikan Anda tidak membuka menu PENERIMAAN lebih dari 1',
                ];
                return response()->json($message);
            }

            $no_penerimaan = $penerimaan->no_penerimaan;
            $create_penerimaan = $penerimaan->created_at;

            //KONVERSI TANGGAL TRANSAKSI MENJADI TANGGAL BARU, AGAR DAPAT DI UPDATE KE TABEL ITEM BULANAN
            $tgl = $penerimaan->tanggal_penerimaan;
            $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
            $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
            $tanggal = '01';

            $tanggal_baru = Carbon\Carbon::createFromDate($tahun, $bulan, $tanggal)->toDateString();

            $lokasi = auth()->user()->kode_lokasi;
            $validate = $this->periodeChecker($tgl);

            if($validate == true){
                $penerimaandetail = PenerimaanDetail::on($konek)->with('produk','satuan')->where('no_penerimaan', request()->id)->get();
                $no_penerimaan = request()->id;
                     
                $data = array();

                if(!empty($penerimaandetail)){
                    foreach ($penerimaandetail as $rowdata){
                        $data[] = array(
                            'no_penerimaan'=>$no_penerimaan,
                            'kode_produk'=>$rowdata->kode_produk,
                            'kode_satuan'=>$rowdata->kode_satuan,
                            'qty'=>$rowdata->qty,
                            'partnumber'=>$rowdata->partnumber,
                            'no_mesin'=>$rowdata->no_mesin,
                            'harga'=>$rowdata->harga,
                            'landedcost'=>$rowdata->landedcost,
                        );
                    }
                }
                      
                $no_pembelian = $penerimaan->no_pembelian;
                $pembelian = Pembelian::on($konek)->find($no_pembelian);
                $jenis_po = $pembelian->jenis_po;

                
                $tabel_baru = array();
                $tabel_baru2 = array();
                $tabel_history = array();
                
                $penerimaan->status = 'ONGOING';
                $penerimaan->save();

                if(!empty($penerimaandetail)){
                    $leng = count($penerimaandetail);

                    //MENGHITUNG NILAI BARANG SETELAH DISKON
                    $k = 0;
                    while($k < $leng){
                        $cek_po = PembelianDetail::on($konek)->where('no_pembelian',$no_pembelian)->where('kode_produk',$data[$k]['kode_produk'])->first();

                            //HITUNG DISKON PERSEN
                        $get_diskon = $pembelian->diskon_persen;
                        if($get_diskon > 0){
                            $get_diskon = $pembelian->diskon_persen/100;
                        }

                            //HIRUNG DISKON NILAI
                        $get_diskonrp = $pembelian->diskon_rp/$cek_po->qty;

                            //CEK APAKAH DETAIL LEBIH DARI 1
                        $pembeliandetail_leng = PembelianDetail::on($konek)->where('no_pembelian',$no_pembelian)->get();
                        $leng2 = count($pembeliandetail_leng);

                            //JIKA DETAIL HANYA ADA 1 ITEM, MAKA ITEM TERSEBUT BISA LANGSUNG DI POTONG DISKON
                        if($get_diskonrp > 0 && $leng2 == 1){
                            $harga_po = $cek_po->harga;

                            $tabel_baru2 = [
                                'harga'=>$harga_po,
                            ];  
                        }
                            //JIKA DETAIL LEBIH DARI 1 ITEM, MAKA DISKON NILAI TIDAK AKAN MEMOTONG HARGA BARANG, HANYA DISKON PERSEN YANG BISA MEMOTONG HARGA BARANG
                        else{
                            $harga_po = $cek_po->harga;

                            $tabel_baru2 = [
                                'harga'=>$harga_po,
                            ];  
                        }

                            //UPDATE HARGA BARU SETELAH DISKON
                        $update = PenerimaanDetail::on($konek)->where('no_penerimaan', $no_penerimaan)->where('kode_produk',$data[$k]['kode_produk'])->update($tabel_baru2);

                        $k++;
                    }
                    
                    $penerimaan->status = 'ONGOING1';
                    $penerimaan->save();

                    //UPDATE TABEL ITEM BULANAN
                    $i = 0;
                    for($i = 0; $i < $leng; $i++){

                        $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

                        //JIKA ITEM BULANAN PRODUK BELUM TERBENTUK
                        if($tb_item_bulanan == null)
                        {
                            $produk = Produk::on($konek)->find($data[$i]['kode_produk']);
                            $penerimaandetail2 = PenerimaanDetail::on($konek)->where('no_penerimaan', request()->id)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();
                            $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                            $harga1 = $penerimaandetail2->harga;
                            $qty1 = $penerimaandetail2->qty;
                            $landed = $penerimaandetail2->landedcost;
                            $landed_final = $landed;
                            $harga_final = ($harga1 + $landed_final) / $konversi->nilai_konversi;

                            $update_produk = Produk::on($konek)->where('id',$data[$i]['kode_produk'])->first();
                            $update_produk->harga_beli = $harga_final;
                            $update_produk->save();

                            $waktu = $tanggal_baru;
                            $barang = $data[$i]['kode_produk'];
                            $partnumber = $data[$i]['partnumber'];
                            $no_mesin = $data[$i]['no_mesin'];
                            $stok_masuk = $data[$i]['qty']*$konversi->nilai_konversi;
                            $amount_masuk = $harga_final*$stok_masuk;
                            $end_stok = $stok_masuk;
                            $end_amount = $harga_final*$end_stok;

                            $qty_baru2 = $data[$i]['qty'];
                            $hpp = $end_amount / $end_stok;

                            $tabel_baru = [
                                'periode'=>$waktu,
                                'kode_lokasi'=>$lokasi,
                                'kode_produk'=>$barang,
                                'partnumber'=>$partnumber,
                                'no_mesin'=>$no_mesin,
                                'begin_stock'=>0,
                                'begin_amount'=>0,
                                'in_stock'=>$stok_masuk,
                                'in_amount'=>$amount_masuk,
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
                                'ending_stock'=>$end_stok,
                                'ending_amount'=>$end_amount,
                                'hpp'=>$end_amount/$end_stok,
                                'kode_lokasi'=>auth()->user()->kode_lokasi,
                                'kode_company'=>auth()->user()->kode_company,
                            ];
                            // dd($tabel_baru);

                            $update_item_bulanan = tb_item_bulanan::on($konek)->create($tabel_baru);
                            
                            $penerimaan = Penerimaan::on($konek)->find(request()->id);
                            $pembelian = Pembelian::on($konek)->find($penerimaan->no_pembelian);
                            $pembeliandetail1 = PembelianDetail::on($konek)->where('no_pembelian', $pembelian->no_pembelian)->where('kode_produk',$data[$i]['kode_produk'])->first();
                            $qty_rec = $pembeliandetail1->qty_received;
                            $pembeliandetail1->qty_received = $qty_rec + $qty_baru2;
                            $pembeliandetail1->save();

                            $tgl_terima1 = $penerimaan->tanggal_penerimaan;
                            $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_terima1)->year;
                            $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_terima1)->month;

                            //CEK APAKAH PERIODE SEDANG REOPEN
                            $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                            $status_reopen = $reopen->reopen_status;

                            if($status_reopen == 'true'){
                                $tgl_terima = $penerimaan->tanggal_penerimaan;
                                $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_terima)->year;
                                $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_terima)->month;

                                //BULAN BERJALAN
                                $tb_akhir_bulan2 = tb_akhir_bulan::on($konek)->where('status_periode','Disable')->first();
                                $periode_berjalan = $tb_akhir_bulan2->periode;

                                $datetime1 = new DateTime($periode_berjalan);
                                $datetime2 = new DateTime($tanggal_baru);
                                $month1 = Carbon\Carbon::parse($periode_berjalan)->format('m');
                                $month2 = Carbon\Carbon::parse($tanggal_baru)->format('m');
                                
                                //UNTUK MENGHITUNG SELISIH PERIODE REOPEN DAN PERIODE BERJALAN (CODING INI BISA DI COPY PASTE)
                                $diff = $datetime1->diff($datetime2);
                                $final_date = $diff->d;
                                $final_year = $diff->y;
                                $f_month = $diff->m;

                                $final_month = $f_month;
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
                                    $penerimaandetail2 = PenerimaanDetail::on($konek)->where('no_penerimaan', request()->id)->where('kode_produk',$data[$i]['kode_produk'])->first();
                                    $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                                    $harga1 = $penerimaandetail2->harga;
                                    $qty1 = $penerimaandetail2->qty;
                                    $landed = $penerimaandetail2->landedcost;
                                    $landed_final = $landed;
                                    $harga_final = ($harga1 + $landed_final) / $konversi->nilai_konversi;

                                    $update_produk = Produk::on($konek)->where('id',$data[$i]['kode_produk'])->first();
                                    $update_produk->harga_beli = $harga_final;
                                    $update_produk->save();

                                    $stok_masuk = $data[$i]['qty']*$konversi->nilai_konversi;
                                    $amount_masuk = $harga_final*$stok_masuk;

                                    $tahun_berjalan = Carbon\Carbon::createFromFormat('Y-m-d',$periode_berjalan)->year;
                                    $tahun_kemarin = $tahun_berjalan - 1;

                                    //MENCEGAH UPDATE SAAT INDEX BULAN SUDAH MENCAPAI ANGKA 13 DAN MENGUBAH TAHUN MENJADI PERIODE SELANJUTNYA
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

                                  $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi',$lokasi)->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->first();

                                  if($tb_item_bulanan2 != null){
                                    $bs = $tb_item_bulanan2->begin_stock;
                                    $ba = $tb_item_bulanan2->begin_amount;
                                    $es = $tb_item_bulanan2->ending_stock;
                                    $ea = $tb_item_bulanan2->ending_amount;

                                    $partnumber = $data[$i]['partnumber'];
                                    $no_mesin = $data[$i]['no_mesin'];

                                    $begin_stock1 = $stok_masuk + $bs;
                                    $begin_amount1 = $amount_masuk + $ba;
                                    $end_stok1 = $es + $stok_masuk;
                                    $end_amount1 = $ea + $amount_masuk;

                                    $tabel_baru2 = [
                                        'partnumber'=>$partnumber,
                                        'no_mesin'=>$no_mesin,
                                        'begin_stock'=>$begin_stock1,
                                        'begin_amount'=>$begin_amount1,
                                        'ending_stock'=>$end_stok1,
                                        'ending_amount'=>$end_amount1,
                                        'hpp'=>$end_amount1/$end_stok1,
                                    ];
                                        // dd($tabel_baru2);

                                    $update_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi',$lokasi)->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->update($tabel_baru2);
                                }else{
                                    $tanggal_buka = '01';
                                    $bulan_buka = $bulan2;
                                    $tahun_buka = $tahun2;

                                    $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
                                    $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;

                                    $tanggal_baru2 = Carbon\Carbon::createFromDate($tahun_buka, $bulan_buka, $tanggal_buka)->toDateString();

                                    $waktu = $tanggal_baru2;
                                    $barang = $data[$i]['kode_produk'];
                                    $partnumber = $data[$i]['partnumber'];
                                    $no_mesin = $data[$i]['no_mesin'];
                                    $bs = $stok_masuk;
                                    $ba = $amount_masuk;
                                    $es = $stok_masuk;
                                    $ea = $amount_masuk;
                                    $hpp = $ea / $es;

                                    $tabel_baru2 = [
                                        'periode'=>$waktu,
                                        'kode_lokasi'=>$lokasi,
                                        'kode_produk'=>$barang,
                                        'partnumber'=>$partnumber,
                                        'no_mesin'=>$no_mesin,
                                        'begin_stock'=>$bs,
                                        'begin_amount'=>$ba,
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
                                        'ending_stock'=>$es,
                                        'ending_amount'=>$ea,
                                        'hpp'=>$ea/$es,
                                        'kode_lokasi'=>auth()->user()->kode_lokasi,
                                        'kode_company'=>auth()->user()->kode_company,
                                    ];
                                        // dd($tabel_baru2);

                                    $update_item_bulanan2 = tb_item_bulanan::on($konek)->create($tabel_baru2);
                                } 

                                $j++;
                            }

                            $tabel_history = [
                                'kode_produk'=>$barang,
                                'no_transaksi'=>$no_penerimaan,
                                'tanggal_transaksi'=>$tgl,
                                'kode_lokasi'=>auth()->user()->kode_lokasi,
                                'jam_transaksi'=>$create_penerimaan,
                                'qty_transaksi'=>$data[$i]['qty']*$konversi->nilai_konversi,
                                'harga_transaksi'=>$harga_final,
                                'total_transaksi'=>$harga_final*($data[$i]['qty']*$konversi->nilai_konversi),
                                'kode_lokasi'=>$lokasi,
                            ];

                            $update_produk_history = tb_produk_history::on($konek)->create($tabel_history);

                        }
                        else{
                            $tabel_history = [
                                'kode_produk'=>$barang,
                                'no_transaksi'=>$no_penerimaan,
                                'tanggal_transaksi'=>$tgl,
                                'kode_lokasi'=>auth()->user()->kode_lokasi,
                                'jam_transaksi'=>$create_penerimaan,
                                'qty_transaksi'=>$data[$i]['qty']*$konversi->nilai_konversi,
                                'harga_transaksi'=>$harga_final,
                                'total_transaksi'=>$harga_final*($data[$i]['qty']*$konversi->nilai_konversi),
                                'kode_lokasi'=>$lokasi,
                            ];

                            $update_produk_history = tb_produk_history::on($konek)->create($tabel_history);
                        }

                    }
                    else{
                        $stock_begin = $tb_item_bulanan->begin_stock;
                        $amount_begin = $tb_item_bulanan->begin_amount;
                        $stock_out = $tb_item_bulanan->out_stock;
                        $amount_out = $tb_item_bulanan->out_amount;
                        $amount_sale = $tb_item_bulanan->sale_amount;     
                        $amount_adj = $tb_item_bulanan->adjustment_amount;
                        $amount_op = $tb_item_bulanan->amount_opname;
                        $amount_rba = $tb_item_bulanan->retur_beli_amount;
                        $amount_rja = $tb_item_bulanan->retur_jual_amount;
                        $amount_dis = $tb_item_bulanan->disassembling_amount;
                        $amount_ass = $tb_item_bulanan->assembling_amount;
                        $amount_rpk = $tb_item_bulanan->retur_pakai_amount;

                        $stock_trfin = $tb_item_bulanan->trf_in;
                        $amount_trfin = $tb_item_bulanan->trf_in_amount;

                        $stock_trfout = $tb_item_bulanan->trf_out;
                        $amount_trfout = $tb_item_bulanan->trf_out_amount;

                        $stock_ending = $tb_item_bulanan->ending_stock;

                        $produk = Produk::on($konek)->find($data[$i]['kode_produk']);
                        $penerimaandetail2 = PenerimaanDetail::on($konek)->where('no_penerimaan', request()->id)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                        $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                        $harga1 = $penerimaandetail2->harga;
                        $qty1 = $penerimaandetail2->qty;
                        $landed = $penerimaandetail2->landedcost;
                        $landed_final = $landed;
                        $harga_final = ($harga1 + $landed_final) / $konversi->nilai_konversi;

                        // $update_produk = Produk::on($konek)->where('id',$data[$i]['kode_produk'])->first();
                        // $update_produk->harga_beli = $harga_final;
                        // $update_produk->save();

                        $stok_awal_1 = $tb_item_bulanan->in_stock;
                        $amount_awal_1 = $tb_item_bulanan->in_amount;
                        $produk_awal = $tb_item_bulanan->kode_produk;
                        $amount_akhir1 = $tb_item_bulanan->ending_amount;
                        $qty_baru = $data[$i]['qty']*$konversi->nilai_konversi;
                        $qty_baru2 = $data[$i]['qty'];
                        $partnumber = $data[$i]['partnumber'];
                        $no_mesin = $data[$i]['no_mesin'];

                        $waktu = $tgl;
                        $barang = $data[$i]['kode_produk'];
                        $stok_masuk = $stok_awal_1 + $qty_baru;
                        $amount_masuk = $amount_awal_1 + ($harga_final*$qty_baru);
                        $stok_keluar = $stock_out;
                        $end_stok = $stock_ending + $qty_baru;

                        $end_amount = $amount_begin + $amount_masuk - $amount_out - $amount_sale + $amount_trfin - $amount_trfout + $amount_adj + $amount_op - $amount_rba + $amount_rja - $amount_dis + $amount_ass + $amount_rpk;
                        $hpp = $end_amount + $end_stok;

                        $tabel_baru = [
                            'partnumber'=>$partnumber,
                            'no_mesin'=>$no_mesin,
                            'in_stock'=>$stok_masuk,
                            'in_amount'=>$amount_masuk,
                            'ending_stock'=>$end_stok,
                            'ending_amount'=>$end_amount,
                            'hpp'=>$end_amount/$end_stok,
                        ];
                            // dd($tabel_baru);

                        $update_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$produk_awal)->where('kode_lokasi',$lokasi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($tabel_baru);

                        $tabel_history = [
                            'kode_produk'=>$barang,
                            'no_transaksi'=>$no_penerimaan,
                            'kode_lokasi'=>auth()->user()->kode_lokasi,
                            'tanggal_transaksi'=>$tgl,
                            'jam_transaksi'=>$create_penerimaan,
                            'qty_transaksi'=>$qty_baru,
                            'harga_transaksi'=>$harga_final,
                            'total_transaksi'=>$harga_final*$qty_baru,
                            'kode_lokasi'=>$lokasi,
                        ];
                            // dd($tabel_history);

                        $update_produk_history = tb_produk_history::on($konek)->create($tabel_history);
                        $penerimaan = Penerimaan::on($konek)->find(request()->id);
                        $pembelian = Pembelian::on($konek)->find($penerimaan->no_pembelian);
                        $pembeliandetail1 = PembelianDetail::on($konek)->where('no_pembelian', $pembelian->no_pembelian)->where('kode_produk',$data[$i]['kode_produk'])->first();

                        $qty_rec = $pembeliandetail1->qty_received;
                        $pembeliandetail1->qty_received = $qty_rec + $qty_baru2;
                        $pembeliandetail1->save();

                        $tgl_terima1 = $penerimaan->tanggal_penerimaan;
                        $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_terima1)->year;
                        $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_terima1)->month;

                        $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                        $status_reopen = $reopen->reopen_status;

                        if($status_reopen == 'true'){
                            $tgl_terima = $penerimaan->tanggal_penerimaan;
                            $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_terima)->year;
                            $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_terima)->month;

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
                                $penerimaandetail2 = PenerimaanDetail::on($konek)->where('no_penerimaan', request()->id)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                                $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                                $harga1 = $penerimaandetail2->harga;
                                $qty1 = $penerimaandetail2->qty;
                                $landed = $penerimaandetail2->landedcost;
                                $landed_final = $landed;
                                $harga_final = ($harga1 + $landed_final) / $konversi->nilai_konversi;

                                $update_produk = Produk::on($konek)->where('id',$data[$i]['kode_produk'])->first();
                                $update_produk->harga_beli = $harga_final;
                                $update_produk->save();

                                $stok_masuk = $data[$i]['qty']*$konversi->nilai_konversi; 
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

                                $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$lokasi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->first();

                                if($tb_item_bulanan2 != null){
                                    $bs = $tb_item_bulanan2->begin_stock;
                                    $ba = $tb_item_bulanan2->begin_amount;
                                    $es = $tb_item_bulanan2->ending_stock;
                                    $ea = $tb_item_bulanan2->ending_amount;
                                    $partnumber = $data[$i]['partnumber'];
                                    $no_mesin = $data[$i]['no_mesin'];

                                    $begin_stock1 = $stok_masuk + $bs;
                                    $begin_amount1 = $amount_masuk + $ba;

                                    $end_stok1 = $es + $stok_masuk;
                                    $end_amount1 = $ea + $amount_masuk;
                                    $hpp = $end_amount1 + $end_stok1;

                                    $tabel_baru2 = [
                                        'partnumber'=>$partnumber,
                                        'no_mesin'=>$no_mesin,
                                        'begin_stock'=>$begin_stock1,
                                        'begin_amount'=>$begin_amount1,
                                        'ending_stock'=>$end_stok1,
                                        'ending_amount'=>$end_amount1,
                                        'hpp'=>$end_amount1/$end_stok1,
                                    ];
                                                        // dd($tabel_baru2);

                                    $update_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$lokasi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->update($tabel_baru2);
                                }else{
                                    $tanggal_buka = '01';
                                    $bulan_buka = $bulan2;
                                    $tahun_buka = $tahun2;

                                    $tanggal_baru2 = Carbon\Carbon::createFromDate($tahun_buka, $bulan_buka, $tanggal_buka)->toDateString();

                                    $waktu = $tanggal_baru2;
                                    $barang = $data[$i]['kode_produk'];
                                    $partnumber = $data[$i]['partnumber'];
                                    $no_mesin = $data[$i]['no_mesin'];
                                    $bs = $stok_masuk;
                                    $ba = $amount_masuk;
                                    $es = $stok_masuk;
                                    $ea = $amount_masuk;

                                    $tabel_baru2 = [
                                        'periode'=>$waktu,
                                        'kode_lokasi'=>$lokasi,
                                        'kode_produk'=>$barang,
                                        'partnumber'=>$partnumber,
                                        'no_mesin'=>$no_mesin,
                                        'begin_stock'=>$bs,
                                        'begin_amount'=>$ba,
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
                                        'ending_stock'=>$es,
                                        'ending_amount'=>$ea,
                                        'hpp'=>$ea/$es,
                                        'kode_lokasi'=>auth()->user()->kode_lokasi,
                                        'kode_company'=>auth()->user()->kode_company,
                                    ];
                                        // dd($tabel_baru2);
                                    $update_item_bulanan2 = tb_item_bulanan::on($konek)->create($tabel_baru2);
                                }

                                $j++;
                            }

                        }

                    }
                }
            }               
                        
            $penerimaan = Penerimaan::on($konek)->find(request()->id);
            $penerimaan->status = "POSTED";
            $penerimaan->save(); 

            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Post No. Penerimaan: '.$no_penerimaan.'.','created_by'=>$nama,'updated_by'=>$nama];
            //dd($tmp);
            user_history::on($konek)->create($tmp);

            $total_qty_po = 0;
            $total_qty_rec = 0;

            $pembelian = Pembelian::on($konek)->find($penerimaan->no_pembelian);
            $pembeliandetail = PembelianDetail::on($konek)->where('no_pembelian', $pembelian->no_pembelian)->get();

            foreach ($pembeliandetail as $row){
                $total_qty_po += $row->qty;
                $total_qty_rec += $row->qty_received;
            }
                            
            $qty_total_po = $total_qty_po;
            $qty_total_rec = $total_qty_rec;

            if($qty_total_rec == $qty_total_po){
                $pembelian->status = "CLOSED";
                $pembelian->save();
            }else{
                $pembelian->status = "RECEIVED";
                $pembelian->save();
            }

            //UPDATE LEDGER JURNAL
            if($cek_company == '04' || $cek_company == '0401' || $cek_company == '03' || $cek_company == '05' || $cek_company == '06' || $cek_company == '02'){
                $konek2 = self::konek2();

                $total_qty = 0;
                $total_harga = 0;
                $grand_total = 0;
                // $detail = PenerimaanDetail::on($konek)->where('no_penerimaan',$penerimaan->no_penerimaan)->get();
                $gt_total = 0;

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
                
                $detail = KategoriProduk::join($compan.'.produk','kategori_produk.kode_kategori','=',$compan.'.produk.kode_kategori')->join($compan.'.penerimaan_detail',$compan.'.produk.id','=',$compan.'.penerimaan_detail.kode_produk')->where($compan.'.penerimaan_detail.no_penerimaan', $no_penerimaan)->groupBy('kategori_produk.kode_kategori')->get();

                foreach ($detail as $row){
                    $total_qty += $row->qty;
                    $subtotal = ($row->harga + $row->landedcost) * $row->qty;
                    $total_harga += $subtotal;

                    $totalhpp = PenerimaanDetail::on($konek)->select(DB::raw('SUM('.$compan.'.penerimaan_detail.qty * ('.$compan.'.penerimaan_detail.harga + '.$compan.'.penerimaan_detail.landedcost)) as total'))->join($compan.'.produk',$compan.'.penerimaan_detail.kode_produk','=',$compan.'.produk.id')->where($compan.'.penerimaan_detail.no_penerimaan', $no_penerimaan)->where($compan.'.produk.kode_kategori', $row->kode_kategori)->first();
                    $totalhpp = $totalhpp->total;

                    $grand_total += $totalhpp;

                    $kategori = KategoriProduk::where('kode_kategori', $row->kode_kategori)->first();

                    if ($cek_company == '04'){
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_gut)->first();
                        $cc_inv = $kategori->cc_gut_persediaan;
                    }else if ($cek_company == '0401'){
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_gutjkt)->first();
                        $cc_inv = $kategori->cc_gutjkt_persediaan;
                    }else if ($cek_company == '03'){
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_emkl)->first();
                        $cc_inv = $kategori->cc_emkl_persediaan;
                    }else if ($cek_company == '02'){
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_pbm)->first();
                        $cc_inv = $kategori->cc_pbm_persediaan;
                    }else if ($cek_company == '01'){
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_depo)->first();
                        $cc_inv = $kategori->cc_depo_persediaan;
                    }else if ($cek_company == '05'){
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
                        $cc_inv = $kategori->cc_sub_persediaan;
                    }else if ($cek_company == '06'){
                        $coa_inventory = Coa::where('kode_coa', $kategori->coa_infra)->first();
                        $cc_inv = $kategori->cc_infra_persediaan;
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
                        'no_journal'=>$penerimaan->no_journal,
                        'journal_date'=>$penerimaan->tanggal_penerimaan,
                        'db_cr'=>'D',
                        'reference'=>$penerimaan->no_penerimaan,
                        'debit'=>round($totalhpp),
                        'kode_lokasi'=>$lokasi,
                    ];
                    $update = Ledger::on($konek2)->create($update_ledger);

                    $type = 'Inventory';
                    $transaksi = $penerimaan;
                    $tgl_trans = $penerimaan->tanggal_penerimaan;
                    $harga_acc = round($totalhpp);
                    $dbkr = 'D';
                    $update_accbalance = $this->accbalance_debit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                    $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                }

                //UPDATE ACCOUNT BALANCE COA HUTANG PEMBELIAN YANG BELUM DITAGIH
                $coa_hutang = Coa::where('kode_coa', '140')->first();
                $cek_balance2 = AccBalance::on($konek2)->where('account',$coa_hutang->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                if ($cek_balance2 == null) {
                    //CEK SEBELUM
                    $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_hutang->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                    if($cek_sebelum != null){
                        $update_acc = [
                            'periode'=>$tanggal_baru,
                            'fiscalyear'=>$tahun,
                            'account'=>$coa_hutang->account,
                            'beginning_balance'=>$cek_sebelum->ending_balance,
                            'debet'=>0,
                            'kredit'=>0,
                            'ending_balance'=>$cek_sebelum->ending_balance,
                            'kode_lokasi'=>$lokasi,
                        ];

                        $update_balance = AccBalance::on($konek2)->where('account',$coa_hutang->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                    }else{
                        $update_acc = [
                            'periode'=>$tanggal_baru,
                            'fiscalyear'=>$tahun,
                            'account'=>$coa_hutang->account,
                            'beginning_balance'=>0,
                            'debet'=>0,
                            'kredit'=>0,
                            'ending_balance'=>0,
                            'kode_lokasi'=>$lokasi,
                        ];

                        $update_balance = AccBalance::on($konek2)->where('account',$coa_hutang->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                    }

                    //CEK SETELAH
                    $i = $bulan;
                    $cek_setelah = AccBalance::on($konek2)->where('account',$coa_hutang->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                    if ($cek_setelah != null) {
                        for($i = $bulan; $i <= 12; $i++){
                            $cek_setelah = AccBalance::on($konek2)->where('account',$coa_hutang->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                            if($cek_setelah != null){
                                $begin = $cek_setelah->beginning_balance + $grand_total;
                                $debit = $cek_setelah->debet;
                                $kredit = $cek_setelah->kredit;
                                if($coa_hutang->normal_balance == 'D'){
                                    $ending_balance = $begin + $debit - $kredit;
                                }else{
                                    $ending_balance = $begin - $debit + $kredit;
                                }

                                $tabel_baru = [
                                    'beginning_balance'=>$begin,
                                    'ending_balance'=>$ending_balance,
                                ];

                                $update_balance = AccBalance::on($konek2)->where('account',$coa_hutang->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                            }
                        }
                    }
                }else{
                    //CEK SETELAH
                    $i = $bulan;
                    $cek_setelah = AccBalance::on($konek2)->where('account',$coa_hutang->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                    if ($cek_setelah != null) {
                        for($i = $bulan; $i <= 12; $i++){
                            $cek_setelah = AccBalance::on($konek2)->where('account',$coa_hutang->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                            if($cek_setelah != null){
                                $begin = $cek_setelah->beginning_balance + $grand_total;
                                $debit = $cek_setelah->debet;
                                $kredit = $cek_setelah->kredit;
                                if($coa_hutang->normal_balance == 'D'){
                                    $ending_balance = $begin + $debit - $kredit;
                                }else{
                                    $ending_balance = $begin - $debit + $kredit;
                                }

                                $tabel_baru = [
                                    'beginning_balance'=>$begin,
                                    'ending_balance'=>$ending_balance,
                                ];

                                $update_balance = AccBalance::on($konek2)->where('account',$coa_hutang->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                            }
                        }
                    }
                }

                //UPDATE LEDGER COA HUTANG PEMBELIAN YANG BELUM DI TAGIH
                $update_ledger = [
                    'tahun'=>$tahun,
                    'periode'=>$bulan,
                    'account'=>$coa_hutang->account,
                    'no_journal'=>$penerimaan->no_journal,
                    'journal_date'=>$penerimaan->tanggal_penerimaan,
                    'db_cr'=>'K',
                    'reference'=>$penerimaan->no_penerimaan,
                    'kredit'=>round($grand_total),
                    'kode_lokasi'=>$lokasi,
                ];
                $update = Ledger::on($konek2)->create($update_ledger);
                
                //UPDATE ACCOUNT BALANCE DAN LABA RUGI BERJALAN SESUAI PERIODE TRANSAKSI
                $type = 'Inventory';
                $transaksi = $penerimaan;
                $tgl_trans = $penerimaan->tanggal_penerimaan;
                $harga_acc = round($grand_total);
                $dbkr = 'K';
                $update_accbalance = $this->accbalance_kredit_post($coa_hutang, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                $update_lrb = $this->lrb_post($coa_hutang, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
            }
            
            
            //RE-BALANCING !!!
            if($cek_company == '04' || $cek_company == '0401' || $cek_company == '03' || $cek_company == '05' || $cek_company == '06'){
                $ledgeran = Ledger::on($konek2)->where('reference', request()->id)->get();
                foreach ($ledgeran as $row) {
                    $led = Ledger::on($konek2)->find($row->id);
                    $led->debit = round($led->debit);
                    $led->kredit = round($led->kredit);
                    $led->save();

                    $account = AccBalance::on($konek2)->where('account', $led->account)->whereMonth('periode', $led->periode)->whereYear('periode', $led->tahun)->where('kode_lokasi', $led->kode_lokasi)->first();
                    $begin = $account->beginning_balance;
                    if ($led->db_cr == 'D') {
                        $debit = $account->debet - $led->debit + round($led->debit);
                        $kredit = $account->kredit;
                    }else if ($led->db_cr == 'K') {
                        $kredit = $account->kredit - $led->kredit + round($led->kredit);
                        $debit = $account->debet;
                    }
                    
                    $coa = Coa::where('account', $led->account)->first();
                    if($coa->normal_balance == 'D'){
                        $ending_balance = $begin + $debit - $kredit;
                    }else{
                        $ending_balance = $begin - $debit + $kredit;
                    }

                    $update_acc = [
                        'debet'=>$debit,
                        'kredit'=>$kredit,
                        'ending_balance'=>$ending_balance,
                    ];

                    AccBalance::on($konek2)->where('account',$coa->account)->where('kode_lokasi',$led->kode_lokasi)->whereMonth('periode', $led->periode)->whereYear('periode', $led->tahun)->update($update_acc);
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
                'title' => 'Update',
                'message' => 'Data gagal di POST, re-open [Bulan '.$bulan.'; Tahun '.$tahun.'].'
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
        
        $todays = Carbon\Carbon::now();
        $today = $todays->toDateString();
        $terima = Penerimaan::on($konek)->find(request()->id);
        // if ($terima->tanggal_penerimaan != $today) {
        //     $message = [
        //         'success' => false,
        //         'title' => 'Simpan',
        //         'message' => 'Tanggal hari ini: '.$today.' Unposting penerimaan hanya dapat dilakukan di hari yang sama.',
        //     ];
        //     return response()->json($message);
        // }

        if($cek_bulan == null || $level == 'superadministrator' || $level == 'user_rince' || $level == 'user_thomas'){
            $penerimaan = Penerimaan::on($konek)->find(request()->id);
            $cek_status = $penerimaan->status;
            if($cek_status != 'POSTED'){  
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'UNPOST No. Penerimaan: '.$penerimaan->no_penerimaan.' sudah dilakukan! Pastikan Anda tidak membuka menu PENERIMAAN lebih dari 1',
                ];
                return response()->json($message);
            }

            $no_penerimaan = $penerimaan->no_penerimaan;

            $tgl = $penerimaan->tanggal_penerimaan;
            $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
            $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
            $tanggal = '01';

            $tanggal_baru = Carbon\Carbon::createFromDate($tahun, $bulan, $tanggal)->toDateString();

            $validate = $this->periodeChecker($tgl);
            $lokasi = auth()->user()->kode_lokasi;
            
            if($level != 'user_rince' && $level != 'superadministrator' && $level != 'user_thomas'){
                $cekopen = Penerimaan::on($konek)->where('status','OPEN')->whereMonth('tanggal_penerimaan', $bulan)->whereYear('tanggal_penerimaan', $tahun)->first();
                if ($cekopen != null){
                    $message = [
                            'success' => false,
                            'title' => 'Gagal',
                            'message' => 'UNPOST No. Penerimaan: '.$penerimaan->no_penerimaan.' gagal karena masih ada penerimaan OPEN.',
                    ];
                    return response()->json($message);
                }
            }

            if($validate != true){  
                $message = [
                    'success' => false,
                    'title' => 'Update',
                    'message' => 'Data gagal di UNPOSTING, re-open [Bulan '.$bulan.'; Tahun '.$tahun.'].'
                ];
                return response()->json($message);
            }

            $validate_produk = $this->produkChecker($no_penerimaan, $tahun, $bulan, $tanggal_baru, $tgl, $penerimaan, $lokasi);


            $returpembelian = Returpembelian::on($konek)->where('no_penerimaan', request()->id)->where('status','POSTED')->first();

            if($returpembelian != null){  
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'UNPOST No. Penerimaan: '.$penerimaan->no_penerimaan.' gagal! Ada RETUR PEMBELIAN yang terkait',
                ];
                return response()->json($message);
            }else{
                $cek_detail = Returpembelian::on($konek)->where('no_penerimaan', request()->id)->first();
                if ($cek_detail != null){
                    $hapus_detail = ReturpembelianDetail::on($konek)->where('no_penerimaan',$cek_detail->no_penerimaan)->delete();
                    $cek_detail->total_item = 0;
                    $cek_detail->save();
                }
            }

            if($validate_produk == true){

                $penerimaandetail = PenerimaanDetail::on($konek)->with('produk','satuan')->where('no_penerimaan', request()->id)->get();
                $no_penerimaan = request()->id;
             
                $data = array();

                foreach ($penerimaandetail as $rowdata){
                    $data[] = array(
                        'no_penerimaan'=>$no_penerimaan,
                        'kode_produk'=>$rowdata->kode_produk,
                        'kode_satuan'=>$rowdata->kode_satuan,
                        'qty'=>$rowdata->qty,
                        'partnumber'=>$rowdata->partnumber,
                    );
                }
                

                $no_pembelian = $penerimaan->no_pembelian;
                $pembelian = Pembelian::on($konek)->find($no_pembelian);
                $jenis_po = $pembelian->jenis_po;
                // dd($jenis_po);
                if($jenis_po == 'Stock'){
                    if(!empty($penerimaandetail)){
                        $leng = count($penerimaandetail);

                        $i = 0;
                        for($i = 0; $i < $leng; $i++){
                            $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$lokasi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

                            $cek_tipe = Produk::on($konek)->where('id',$data[$i]['kode_produk'])->first();

                            $produk_awal = $tb_item_bulanan->kode_produk;

                            $amount_begin = $tb_item_bulanan->begin_amount;
                            $stok_awal_1 = $tb_item_bulanan->in_stock;
                            $amount_awal_1 = $tb_item_bulanan->in_amount;
                            $amount_out = $tb_item_bulanan->out_amount;
                            $amount_sale = $tb_item_bulanan->sale_amount;
                            $amount_rb = $tb_item_bulanan->retur_beli_amount;
                            $amount_rj = $tb_item_bulanan->retur_jual_amount;
                            $amount_dis = $tb_item_bulanan->disassembling_amount;
                            $amount_ass = $tb_item_bulanan->assembling_amount;
                            $amount_rpk = $tb_item_bulanan->retur_pakai_amount;

                            //UNTUK MENGHITUNG ENDING STOCK PRODUK SERIAL PADA TABEL BULANAN
                            if ($cek_tipe->tipe_produk == 'Serial'){
                                if($cek_tipe->kode_kategori == 'UNIT' || $cek_tipe->kode_kategori == 'BAN'){

                                    $tot_in_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$lokasi)->where('in_stock',1)->get();

                                    $tot_op_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$lokasi)->where('stock_opname',1)->get();

                                    $tot_out_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$lokasi)->where('sale_stock',1)->get();

                                    $tot_sale_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$lokasi)->where('out_stock',1)->get();

                                    $tot_adj_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$lokasi)->where('adjustment_stock',1)->get();

                                    $tot_trfin_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$lokasi)->where('trf_in',1)->get();

                                    $tot_trfout_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$lokasi)->where('trf_out',1)->get();

                                    $tot_rb_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$lokasi)->where('retur_beli_stock',1)->get();

                                    $tot_rj_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$lokasi)->where('retur_jual_stock',1)->get();

                                    $tot_dis_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$lokasi)->where('disassembling_stock',1)->get();

                                    $tot_ass_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$lokasi)->where('assembling_stock',1)->get();
                                    
                                    $tot_rpk_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$lokasi)->where('retur_pakai_stock',1)->get();

                                    if ($tot_in_bulanan == null){
                                        $tot_in_qty = 0;
                                    }else {
                                        $tot_in_qty = count($tot_in_bulanan);
                                    }

                                    if ($tot_out_bulanan == null){
                                        $tot_out_qty = 0;
                                    }else {
                                        $tot_out_qty = count($tot_out_bulanan);
                                    }

                                    if ($tot_op_bulanan == null){
                                        $tot_op_qty = 0;
                                    }else {
                                        $tot_op_qty = count($tot_op_bulanan);
                                    }

                                    if ($tot_sale_bulanan == null){
                                        $tot_sale_qty = 0;
                                    }else {
                                        $tot_sale_qty = count($tot_sale_bulanan);
                                    }

                                    if ($tot_adj_bulanan == null){
                                        $tot_adj_qty = 0;
                                    }else {
                                        $tot_adj_qty = count($tot_adj_bulanan);
                                    }

                                    if ($tot_trfin_bulanan == null){
                                        $tot_trfin_qty = 0;
                                    }else {
                                        $tot_trfin_qty = count($tot_trfin_bulanan);
                                    }

                                    if ($tot_trfout_bulanan == null){
                                        $tot_trfout_qty = 0;
                                    }else {
                                        $tot_trfout_qty = count($tot_trfout_bulanan);
                                    }

                                    if ($tot_rb_bulanan == null){
                                        $tot_rb_qty = 0;
                                    }else {
                                        $tot_rb_qty = count($tot_rb_bulanan);
                                    }

                                    if ($tot_rj_bulanan == null){
                                        $tot_rj_qty = 0;
                                    }else {
                                        $tot_rj_qty = count($tot_rj_bulanan);
                                    }

                                    if ($tot_dis_bulanan == null){
                                        $tot_dis_qty = 0;
                                    }else {
                                        $tot_dis_qty = count($tot_dis_bulanan);
                                    }

                                    if ($tot_ass_bulanan == null){
                                        $tot_ass_qty = 0;
                                    }else {
                                        $tot_ass_qty = count($tot_ass_bulanan);
                                    }
                                    
                                    if ($tot_rpk_bulanan == null){
                                        $tot_rpk_qty = 0;
                                    }else {
                                        $tot_rpk_qty = count($tot_rpk_bulanan);
                                    }

                                    $stock_ending = $tot_in_qty - $tot_out_qty - $tot_sale_qty - $tot_trfout_qty + $tot_op_qty + $tot_adj_qty + $tot_trfin_qty - $tot_rb_qty + $tot_rj_qty - $tot_dis_qty + $tot_ass_qty + $tot_rpk_qty;
                                }else{
                                    $stock_ending = $tb_item_bulanan->ending_stock;
                                }
                            }else{
                                $stock_ending = $tb_item_bulanan->ending_stock;
                            } 

                        
                            $stock_out = $tb_item_bulanan->out_stock;
                                    
                            $amount_adj = $tb_item_bulanan->adjustment_amount;
                            $amount_op = $tb_item_bulanan->amount_opname;

                            $stock_trfin = $tb_item_bulanan->trf_in;
                            $amount_trfin = $tb_item_bulanan->trf_in_amount;

                            $stock_trfout = $tb_item_bulanan->trf_out;
                            $amount_trfout = $tb_item_bulanan->trf_out_amount;

                            $produk = Produk::on($konek)->find($data[$i]['kode_produk']);

                            $penerimaandetail2 = PenerimaanDetail::on($konek)->where('no_penerimaan', request()->id)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                            $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                            $harga1 = $penerimaandetail2->harga;
                            $qty1 = $penerimaandetail2->qty;
                            $landed = $penerimaandetail2->landedcost;
                            $landed_final = $landed;
                            $harga_final = ($harga1 + $landed_final) / $konversi->nilai_konversi;
                                    
                            $qty_baru = $data[$i]['qty']*$konversi->nilai_konversi;

                            $stok_masuk = $stok_awal_1 - $qty_baru;
                            $amount_masuk = $amount_awal_1 - ($harga_final*$qty_baru);
                            if ($cek_tipe->tipe_produk == 'Serial'){
                                if($cek_tipe->kode_kategori == 'UNIT' || $cek_tipe->kode_kategori == 'BAN'){
                                    $end_stok2 = 0;
                                }
                                else{
                                    $end_stok2 = $stock_ending - $qty_baru;
                                }
                            }else {
                                $end_stok2 = $stock_ending - $qty_baru;
                            }

                            //HITUNG ULANG STOK DI TABEL BULANAN
                            $end_stok = $stock_ending - $qty_baru;
                            $end_amount = $amount_begin + $amount_masuk - $amount_out - $amount_sale + $amount_trfin - $amount_trfout + $amount_adj + $amount_op - $amount_rb + $amount_rj - $amount_dis + $amount_ass + $amount_rpk;

                            if($end_stok != 0){
                                $hpp = $end_amount / $end_stok;
                            }else{
                                $hpp = $tb_item_bulanan->hpp;
                                $end_amount = 0;
                            }

                            $tabel_baru = [
                                'in_stock'=>$stok_masuk,
                                'in_amount'=>$amount_masuk,
                                'ending_stock'=>$end_stok2,
                                'ending_amount'=>$end_amount,
                                'hpp'=>$hpp,
                            ];

                            //CEK APABILA STOK MINUS MAKA PROSES UNPOSTING DIBATALKAN
                            if($end_stok < 0){
                                $message = [
                                    'success' => false,
                                    'title' => 'Update',
                                    'message' => 'Data gagal di UNPOST, silahkan UNPOST Pemakaian pada [Bulan '.$bulan.'; Tahun '.$tahun.'] terlebih dahulu.'
                                ];
                                return response()->json($message);
                            }
                            else{
                                $update_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$produk_awal)->where('kode_lokasi',$lokasi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($tabel_baru);

                                $qty_baru2 = $data[$i]['qty'];

                                $update_produk_history = tb_produk_history::on($konek)->where('no_transaksi',$no_penerimaan)->delete();

                                $penerimaan = Penerimaan::on($konek)->find(request()->id);
                                $pembelian = Pembelian::on($konek)->find($penerimaan->no_pembelian);
                                $pembeliandetail1 = PembelianDetail::on($konek)->where('no_pembelian', $pembelian->no_pembelian)->where('kode_produk',$data[$i]['kode_produk'])->first();

                                $qty_rec = $pembeliandetail1->qty_received;

                                $pembeliandetail1->qty_received = $qty_rec - $qty_baru2;
                                $pembeliandetail1->save();

                                $tgl_terima1 = $penerimaan->tanggal_penerimaan;
                                $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_terima1)->year;
                                $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_terima1)->month;

                                $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                                        $status_reopen = $reopen->reopen_status;

                                if($status_reopen == 'true'){
                                    $tgl_terima = $penerimaan->tanggal_penerimaan;
                                    $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_terima)->year;
                                    $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_terima)->month;

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
                                        $penerimaandetail2 = PenerimaanDetail::on($konek)->where('no_penerimaan', request()->id)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                                        $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                                        $harga1 = $penerimaandetail2->harga;
                                        $qty1 = $penerimaandetail2->qty;
                                        $landed = $penerimaandetail2->landedcost;
                                        $landed_final = $landed;
                                        $harga_final = ($harga1 + $landed_final) / $konversi->nilai_konversi;
                                               
                                        $stok_masuk = $data[$i]['qty']*$konversi->nilai_konversi;
                                            
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

                                        if($tb_item_bulanan2 != null){
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
                                        }

                                        $j++;
                                    }
                                        
                                }
                            }
                                    
                        }
                    }
                }


                $total_qty_po = 0;
                $total_qty_rec = 0;

                $penerimaan = Penerimaan::on($konek)->find(request()->id);
                $penerimaan->status = "OPEN";
                $penerimaan->save(); 

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Unpost No. Penerimaan: '.$no_penerimaan.'.','created_by'=>$nama,'updated_by'=>$nama];
                user_history::on($konek)->create($tmp);

                $pembelian = Pembelian::on($konek)->find($penerimaan->no_pembelian);
                $pembeliandetail = PembelianDetail::on($konek)->where('no_pembelian', $pembelian->no_pembelian)->get();

                foreach ($pembeliandetail as $row){
                    $total_qty_po += $row->qty;
                    $total_qty_rec += $row->qty_received;
                }
                            
                $qty_total_po = $total_qty_po;
                $qty_total_rec = $total_qty_rec;

                if($qty_total_rec == 0){
                    $pembelian->status = "POSTED";
                    $pembelian->save(); 
                }
                else{
                    $pembelian->status = "RECEIVED";
                    $pembelian->save();
                }

                //UPDATE BALANCE DAN LEDGER
                $cek_company = Auth()->user()->kode_company;
                if($cek_company == '04' || $cek_company == '0401' || $cek_company == '03' || $cek_company == '05' || $cek_company == '06' || $cek_company =='02'){
                    $konek2 = self::konek2();

                    $get_ledger = Ledger::on($konek2)->where('no_journal',$penerimaan->no_journal)->get();
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
                    //UPDATE ACCOUNT BALANCE BERDASARKAN HISTORY LEDGER
                    for($i = 0; $i < $leng; $i++){
                        if($data[$i]['db_cr'] == 'D'){
                            $account = $data[$i]['account'];
                            $harga = $data[$i]['debit'];

                            $type = 'Inventory';
                            $transaksi = $penerimaan;
                            $tgl_trans = $penerimaan->tanggal_penerimaan;
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
                            $transaksi = $penerimaan;
                            $tgl_trans = $penerimaan->tanggal_penerimaan;
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

                    //HAPUS LEDGER
                    $update_ledger = Ledger::on($konek2)->where('no_journal',$penerimaan->no_journal)->delete();
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
        $tanggal = $request->tanggal_penerimaan;
        $konek = self::konek();
        $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal)->year;
        $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal)->month;

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
            $terima = Penerimaan::on($konek)->whereMonth('tanggal_penerimaan',$bulan_transaksi)->whereYear('tanggal_penerimaan',$tahun_transaksi)->where('status','OPEN')->get();
            if (count($terima) >= 1){
                $message = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Masih ada penerimaan yang OPEN.'
                ];
               return response()->json($message);
            }
        }else {
            $tgl = tb_akhir_bulan::on($konek)->where('status_periode','Open')->first();
            $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl->periode)->year;
            $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl->periode)->month;
            //$sekarang = date('Y-m-d');
            $terima = Penerimaan::on($konek)->whereMonth('tanggal_penerimaan',$bulan_transaksi)->whereYear('tanggal_penerimaan',$tahun_transaksi)->where('status','OPEN')->get();
            if (count($terima) >= 1){
                $message = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Masih ada penerimaan yang OPEN.'
                ];
               return response()->json($message);
            }
        }
        
        $todays = Carbon\Carbon::now();
        $today = $todays->toDateString();
        if ($request->tanggal_penerimaan != $today) {
            $message = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Tanggal Penerimaan berbeda dgn tanggal hari ini.'
            ];
            return response()->json($message);
        }
             
        if($validate == true){
            $cek_pembelian = $request->no_pembelian;
            $pembelian = Pembelian::on($konek)->where('no_pembelian', $request->no_pembelian)->first();
            if ($request->tanggal_penerimaan < $pembelian->tanggal_pembelian) {
                $message = [
                    'success' => false,
                    'title' => 'Gagal',
                    'message' => 'Tanggal Penerimaan di bawah tanggal PO.'
                    ];
                return response()->json($message);
            }
            
            $penerimaan = Penerimaan::on($konek)->where('no_pembelian', $request->no_pembelian)->get();
            $penerimaan2 = Penerimaan::on($konek)->where('no_pembelian', $request->no_pembelian)->whereMonth('tanggal_penerimaan', $bulan)->whereYear('tanggal_penerimaan', $tahun)->first();

            if($penerimaan2 != null){
                $status_pembelian = Pembelian::on($konek)->find( $penerimaan2->no_pembelian);

                foreach ($penerimaan as $row){
                    $status = $row->status;
                }

                if($status == 'OPEN'){
                    $message = [
                        'success' => false,
                        'title' => 'Simpan',
                        'message' => 'Ada <b>penerimaan</b> dengan <b>No. Pembelian</b> ['.$cek_pembelian.'] <b>yang belum di POSTING</b>'
                        ];
                    return response()->json($message);
                }else if($status_pembelian->status == 'CLOSED'){
                    $message = [
                        'success' => false,
                        'title' => 'Simpan',
                        'message' => 'Status <b>No. Pembelian</b> ['.$cek_pembelian.'] telah <b>CLOSED</b>'
                        ];
                    return response()->json($message);
                }
                else{
                    $penerimaan = Penerimaan::on($konek)->create($request->all());

                    $no = Penerimaan::on($konek)->orderBy('created_at','desc')->first();
                    $nama = auth()->user()->name;
                    $tmp = ['nama' => $nama,'aksi' => 'Simpan No. Penerimaan: '.$no->no_penerimaan.'.','created_by'=>$nama,'updated_by'=>$nama];
                    //dd($tmp);
                    user_history::on($konek)->create($tmp);

                    $message = [
                        'success' => true,
                        'title' => 'Simpan',
                        'message' => 'Data telah disimpan.',
                    ];
                    return response()->json($message);
                }
            }
            else{
                $penerimaan = Penerimaan::on($konek)->create($request->all());

                $no = Penerimaan::on($konek)->orderBy('created_at','desc')->first();
                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Simpan No. Penerimaan: '.$no->no_penerimaan.'.','created_by'=>$nama,'updated_by'=>$nama];
                //dd($tmp);
                user_history::on($konek)->create($tmp);

                $message = [
                    'success' => true,
                    'title' => 'Simpan',
                    'message' => 'Data telah disimpan.',
                ];
                return response()->json($message);
            }
        
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

    public function edit_penerimaan()
    {
        $konek = self::konek();
        $no_penerimaan = request()->id;
        $data = Penerimaan::on($konek)->find($no_penerimaan);

        $output = array(
            'no_penerimaan'=> $data->no_penerimaan,
            'no_pembelian'=> $data->no_pembelian,
            'tanggal_penerimaan'=> $data->tanggal_penerimaan,
            'status'=> $data->status,
        );
        return response()->json($output);
    }

    public function updateAjax(Request $request)
    {
        $konek = self::konek();
        $tanggal = $request->tanggal_penerimaan;
        $validate = $this->periodeChecker($tanggal);
        
        $todays = Carbon\Carbon::now();
        $today = $todays->toDateString();
        if ($tanggal != $today) {
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Tanggal penerimaan berbeda dgn tanggal hari ini.',
            ];
            return response()->json($message);
        }
             
        if($validate == true){
            $Penerimaan = Penerimaan::on($konek)->find($request->no_penerimaan)->update($request->all());

            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Edit No. Penerimaan: '.$request->no_penerimaan.'.','created_by'=>$nama,'updated_by'=>$nama];

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


    public function hapus_penerimaan()
    {
        $level = auth()->user()->level;
        $konek = self::konek();

        $no_penerimaan = request()->id;
        $data = Penerimaan::on($konek)->find($no_penerimaan);
        $tanggal = $data->tanggal_penerimaan;

        $validate = $this->periodeChecker($tanggal);

        if($validate == true){
            $cek_detail = PenerimaanDetail::on($konek)->where('no_penerimaan',$no_penerimaan)->first();
            if($cek_detail == null){
                $data->delete();

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Hapus No. Penerimaan: '.$no_penerimaan.'.','created_by'=>$nama,'updated_by'=>$nama];
                              
                user_history::on($konek)->create($tmp);

                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data ['.$data->no_penerimaan.'] telah dihapus.'
                ];
                return response()->json($message);
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
}
