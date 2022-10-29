<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Returpembelian;
use App\Models\ReturpembelianDetail;
use App\Models\ReturPemakaian;
use App\Models\ReturpemakaianDetail;
use App\Models\Pemakaian;
use App\Models\PemakaianDetail;
use App\Models\Penerimaan;
use App\Models\PenerimaanDetail;
use App\Models\Pembelian;
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
use App\Models\KategoriProduk;
use App\Models\Ledger;
use App\Models\Coa;
use App\Models\AccBalance;
use App\Models\Tb_acc_history;
use App\Models\Jurnal;
use App\Models\Labarugiberjalan;
use App\Models\Opname;
use App\Models\WorkorderDetail;
use App\Models\Costcenter;
use App\Models\SetupFolder;
use Illuminate\Support\Facades\Storage;
use PDF;
use Excel;
use DB;
use Carbon;
use DateTime;

class ReturpemakaianController extends Controller
{
    public function index()
    {
        $konek = self::konek();
        $create_url = route('returpemakaian.create');
        $Satuan= satuan::pluck('nama_satuan', 'kode_satuan');
        $Company= Company::pluck('nama_company','kode_company');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');

        $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_jalan2)->year;
        $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_jalan2)->month;

        $Pemakaian = Pemakaian::on($konek)->where('status','POSTED')->where('kode_lokasi',auth()->user()->kode_lokasi)->whereMonth('tanggal_pemakaian', '<=', $bulan)->whereYear('tanggal_pemakaian', $tahun)->orderBy('created_at','desc')->pluck('no_pemakaian','no_pemakaian');

        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        $level = auth()->user()->level;
        
        return view('admin.returpemakaian.index',compact('create_url','Pemakaian','Company','Satuan','period', 'nama_lokasi','nama_company'));
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
    
    public function historia()
    {
        $konek = self::konek();
        $post = user_history::on($konek)->where('aksi', 'like', '%'.request()->id.'%')->where('aksi','like','Post%')->orderBy('created_at','desc')->first();
        if ($post != null) {
            $nama1 = $post->nama;
        }else {
            $nama1 = 'None';
        }

        $unpost = user_history::on($konek)->where('aksi', 'like', '%'.request()->id.'%')->where('aksi','like','Unpost%')->orderBy('created_at','desc')->first();
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

    public function getkode(){
        $konek = self::konek();
        $get = ReturPemakaian::on($konek)->join('retur_pemakaian_detail','retur_pemakaian_detail.no_retur_pemakaian','=','retur_pemakaian.no_retur_pemakaian')->get();
        $leng = count($get);

        $data = array();

        foreach ($get as $rowdata){
            $kode_vendor = $rowdata->kode_vendor;
            $kode_produk = $rowdata->kode_produk;

            $data[] = array(
                'kode_vendor'=>$kode_vendor,
                'kode_produk'=>$kode_produk,
             );
        }

        for ($i = 0; $i < $leng; $i++) { 
            //HEADER
            $cek = Vendor::where('kode_vendor', $data[$i]['kode_vendor'])->first();
            if($cek != null){
                $id = $cek->id;

                $tabel_baru = [
                   'kode_vendor'=>$id,
                ];
                $update = ReturPemakaian::on($konek)->where('kode_vendor', $data[$i]['kode_vendor'])->update($tabel_baru);
            }

            //DETAIL
            $cek = Produk::on($konek)->where('kode_produk', $data[$i]['kode_produk'])->first();
            if ($cek != null) {
                $id = $cek->id;

                $tabel_baru = [
                    'kode_produk'=>$id,
                ];
                $update = ReturpemakaianDetail::on($konek)->where('kode_produk', $data[$i]['kode_produk'])->update($tabel_baru);
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
            return Datatables::of(ReturPemakaian::on($konek)->with('company')->orderBy('created_at','desc'))->make(true);
        }
        else{
            return Datatables::of(ReturPemakaian::on($konek)->with('company')->orderBy('created_at','desc')->where('kode_lokasi', auth()->user()->kode_lokasi))->make(true);
        }
    }

    public function exportPDF(){
        $request = $_GET['no_retur_pemakaian'];
        $konek = self::konek();
        $returpembelian = ReturPemakaian::on($konek)->where('no_retur_pemakaian',$request)->first();
        $no_returpembelian = $returpembelian->no_retur_pemakaian;

        $no_penerimaan = $returpembelian->no_pemakaian;
        $no_pembelian = $returpembelian->no_pemakaian;
        $kode_company = $returpembelian->kode_company;

        $returpembeliandetail = ReturpemakaianDetail::on($konek)->where('no_retur_pemakaian',$request)->get();
        $penerimaan = Pemakaian::on($konek)->where('no_pemakaian',$no_penerimaan)->first();
        $pembelian = Pemakaian::on($konek)->where('no_pemakaian',$no_pembelian)->first();

        $nama_vendor = '';
        $company_user = Company::where('kode_company',$kode_company)->first();
        $nama_company = $company_user->nama_company;

        $dt = Carbon\Carbon::now();
        $date_now = Carbon\Carbon::parse($dt)->format('d/m/Y H:i:s');

        $tgl = $returpembelian->tgl_retur_pemakaian;
        $date=date_create($tgl);

        $leng = count($returpembeliandetail);
        $company = auth()->user()->kode_company;

        if($company != '04' && $company != '0401' && $company != '01' && $company != '05'){
            if($leng <= 8){
                $pdf = PDF::loadView('/admin/returpemakaian/pdf', compact('returpembeliandetail','request', 'nama_vendor','no_pembelian','tgl', 'no_penerimaan','nama_company','date_now','returpembelian','no_returpembelian','pembelian','konek'));
                $pdf->setPaper([0, 0, 684, 792], 'potrait');
            }
            else{
                $pdf = PDF::loadView('/admin/returpembelian/pdfnew', compact('returpembeliandetail','request', 'nama_vendor','no_pembelian','tgl', 'no_penerimaan','nama_company','date_now','returpembelian','no_returpembelian','pembelian','konek'));
                $pdf->setPaper([0, 0, 684, 792], 'potrait');
            }
        }
        else if($company == '01'){
            if($leng <= 8){
                $pdf = PDF::loadView('/admin/returpembelian/pdfdepo', compact('returpembeliandetail','request', 'nama_vendor','no_pembelian','tgl', 'no_penerimaan','nama_company','date_now','returpembelian','no_returpembelian','pembelian','konek'));
                $pdf->setPaper([0, 0, 684, 792], 'potrait');
            }
            else{
                $pdf = PDF::loadView('/admin/returpembelian/pdfnewdepo', compact('returpembeliandetail','request', 'nama_vendor','no_pembelian','tgl', 'no_penerimaan','nama_company','date_now','returpembelian','no_returpembelian','pembelian','konek'));
                $pdf->setPaper([0, 0, 684, 792], 'potrait');
            }
        }
        else if($company == '05'){
            if($leng <= 8){
                $pdf = PDF::loadView('/admin/returpembelian/pdfsub', compact('returpembeliandetail','request', 'nama_vendor','no_pembelian','tgl', 'no_penerimaan','nama_company','date_now','returpembelian','no_returpembelian','pembelian','konek'));
                $pdf->setPaper([0, 0, 684, 792], 'potrait');
            }
            else{
                $pdf = PDF::loadView('/admin/returpembelian/pdfnewsub', compact('returpembeliandetail','request', 'nama_vendor','no_pembelian','tgl', 'no_penerimaan','nama_company','date_now','returpembelian','no_returpembelian','pembelian','konek'));
                $pdf->setPaper([0, 0, 684, 792], 'potrait');
            }
        }
        else{
            if($leng <= 8){
                $pdf = PDF::loadView('/admin/returpembelian/pdfgut', compact('returpembeliandetail','request', 'nama_vendor','no_pembelian','tgl', 'no_penerimaan','nama_company','date_now','returpembelian','no_returpembelian','pembelian','konek'));
                $pdf->setPaper([0, 0, 684, 792], 'potrait');
            }
            else{
                $pdf = PDF::loadView('/admin/returpembelian/pdfnewgut', compact('returpembeliandetail','request', 'nama_vendor','no_pembelian','tgl', 'no_penerimaan','nama_company','date_now','returpembelian','no_returpembelian','pembelian','konek'));
                $pdf->setPaper([0, 0, 684, 792], 'potrait');
            }
        }   
        
        $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
        $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
        
        $setupfolder = SetupFolder::find(11);
        $tes_save = $company_user->kode_company.". ".$company_user->nama_company."/".$setupfolder->folder."/".$setupfolder->subfolder."/".$tahun."/".$bulan."/".$no_returpembelian.".pdf";
        
        $nama = auth()->user()->name;
        $tmp = ['nama' => $nama,'aksi' => 'Print No. Retur Pemakaian : '.$no_returpembelian.'.','created_by'=>$nama,'updated_by'=>$nama];
        user_history::on($konek)->create($tmp);
        
        Storage::disk('ftp')->put($tes_save, $pdf->output());
        return $pdf->stream('Retur Pemakaian '.$no_returpembelian.'.pdf');
    }


    public function exportPDF2(){
        $konek = self::konek();
        $konek2 = self::konek2();
        $request = $_GET['no_retur_pemakaian'];
        $no_journal = $_GET['no_journal'];

        $returbeli = ReturPemakaian::on($konek)->find($request);
        $jur = $returbeli->kode_jurnal;
        $jurnal = Jurnal::find($jur);

        $total_qty = 0;
        $total_harga = 0;
        $grand_total = 0;
        $detail = ReturpemakaianDetail::on($konek)->where('no_retur_pemakaian',$request)->get();
        foreach ($detail as $row){
            $total_qty += $row->qty;
            $subtotal = $row->harga * $row->qty;
            $total_harga += $subtotal;
            $grand_total = $total_harga;
        }

        $ledger2 = Ledger::on($konek2)->with('coa')->where('no_journal',$no_journal)->first();

        $ledger = Ledger::on($konek2)->select('ledger.*','coa.ac_description')->join('u5611458_gui_general_ledger_laravel.coa','u5611458_gui_general_ledger_laravel.coa.account','=','ledger.account')->where('no_journal', $no_journal)->get();

        $user = $returbeli->created_by;
        $tgl = $returbeli->tgl_retur_pemakaian;
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

        $pdf = PDF::loadView('/admin/returpemakaian/pdf2', compact('returbeli','request', 'jurnal','tgl','date', 'ttd','nama_company','date_now','ledger','ledger2','dt','user','nama','nama2','journal_date','grand_total'));
        $pdf->setPaper([0, 0, 684, 792], 'potrait');
        return $pdf->stream('Cetak Zoom Jurnal '.$request.'.pdf');
    }



    public function getpo()
    {   
        $konek = self::konek();
        $penerimaan = Penerimaan::on($konek)->find(request()->no_penerimaan);
        $no_pembelian = $penerimaan->no_pembelian;

        $pembelian = Pembelian::on($konek)->find($no_pembelian);
        $kode_vendor = $pembelian->kode_vendor;

        $output = array(
            'no_pembelian'=>$no_pembelian,
            'kode_vendor'=>$kode_vendor,
        );

        return response()->json($output);
    }

    public function getpo1()
    {   
        $konek = self::konek();
        $penerimaan = Penerimaan::on($konek)->find(request()->no_penerimaan);
        $no_pembelian = $penerimaan->no_pembelian;

        $pembelian = Pembelian::on($konek)->find($no_pembelian);
        $kode_vendor = $pembelian->kode_vendor;

        $output = array(
            'no_pembelian'=>$no_pembelian,
            'kode_vendor'=>$kode_vendor,
        );

        return response()->json($output);
    }

    public function detail($returpembelian)
    {
        $konek = self::konek();
        $returpembelian = ReturPemakaian::on($konek)->find($returpembelian);
        $tanggal = $returpembelian->tgl_retur_pemakaian;
        $no_returpembelian = $returpembelian->no_retur_pemakaian;

        $validate = $this->periodeChecker($tanggal);
        if($validate == true){
            $data = ReturPemakaian::on($konek)->find($no_returpembelian);

            $Produk = PemakaianDetail::on($konek)->where('pemakaian_detail.no_pemakaian', $data->no_pemakaian)
                ->Join('produk', 'pemakaian_detail.kode_produk', '=', 'produk.id')
                ->pluck('produk.nama_produk','produk.id','pemakaian_detail.qty');
                
            $Parts = PemakaianDetail::on($konek)->where('no_pemakaian', $data->no_pemakaian)
                ->pluck('partnumber','partnumber');

            $Satuan = satuan::pluck('nama_satuan','kode_satuan');
            $list_url= route('returpemakaian.index');

            $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
            $tgl_jalan2 = $tgl_jalan->periode;
            $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
            $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
            $nama_lokasi = $get_lokasi->nama_lokasi;

            $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
            $nama_company = $get_company->nama_company;

            return view('admin.returpemakaiandetail.index', compact('returpembelian','list_url','Produk','Satuan','period', 'nama_lokasi','nama_company','Parts'));
        }
    }

    public function Showdetail()
    {
        $konek = self::konek();
        $total_qty = 0;
        $total_harga = 0;
        $grand_total = 0;
        $returpembeliandetail= ReturpemakaianDetail::on($konek)->with('produk','satuan')->where('no_retur_pemakaian',request()->id)
        ->orderBy('created_at', 'desc')->get();

        $output = array();

        foreach ($returpembeliandetail as $row){
            $total_qty += $row->qty;
            $subtotal = $row->harga * $row->qty;
            $total_harga += $subtotal;
            $grand_total = number_format($total_harga,2,",",".");
        }

        if($returpembeliandetail){
            foreach($returpembeliandetail as $row)
            {
                $no_returpembelian = $row->no_retur_pemakaian;
                $produk = $row->produk->nama_produk;
                $no_mesin = $row->no_mesin;
                $partnumber = $row->partnumber;
                $satuan = $row->satuan->nama_satuan;
                $qty = $row->qty;
                $harga = $row->harga;
                $landedcost = $row->landedcost;
                $subtotal =  number_format(($row->harga * $row->qty),2,",",".");
                $output[] = array(
                    'produk'=>$produk,
                    'satuan'=>$satuan,
                    'partnumber'=>$partnumber,
                    'no_mesin'=>$no_mesin,
                    'qty'=>$qty,
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

    function produkChecker($no_returpembelian, $tahun, $bulan, $tanggal_baru, $tgl, $returpembelian, $koneksi)
    {   
        $konek = self::konek();
        $returpembeliandetail = ReturpembelianDetail::on($konek)->where('no_returpembelian', $no_returpembelian)->get();
        $data = array();

        if(!empty($returpembeliandetail)){
            foreach ($returpembeliandetail as $rowdata){
                $data[] = array(
                    'kode_produk'=>$rowdata->kode_produk,
                    'kode_satuan'=>$rowdata->kode_satuan,
                    'qty'=>$rowdata->qty,
                    'partnumber'=>$rowdata->partnumber,
                    'no_mesin'=>$rowdata->no_mesin,
                );         
            }
        }

        if(!empty($returpembeliandetail)){
            $leng = count($returpembeliandetail);

            $i = 0;

            for($i = 0; $i < $leng; $i++){
                $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi',$koneksi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

                $amount_begin = $tb_item_bulanan->begin_amount;
                $amount_masuk = $tb_item_bulanan->in_amount;
                $amount_out = $tb_item_bulanan->out_amount;
                $amount_sale = $tb_item_bulanan->sale_amount;     
                $amount_adj = $tb_item_bulanan->adjustment_amount;
                $amount_op = $tb_item_bulanan->amount_opname;
                $amount_returjual = $tb_item_bulanan->retur_jual_amount;
                $amount_trfin = $tb_item_bulanan->trf_in_amount;
                $amount_trfout = $tb_item_bulanan->trf_out_amount;
                $disassembling_amount = $tb_item_bulanan->disassembling_amount;
                $assembling_amount = $tb_item_bulanan->assembling_amount;

                $stock_ending = $tb_item_bulanan->ending_stock;
                $amount_akhir1 = $tb_item_bulanan->ending_amount;

                $returpembeliandetail2 = ReturpembelianDetail::on($konek)->where('no_returpembelian', request()->id)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                $harga1 = $returpembeliandetail2->harga;
                $qty1 = $returpembeliandetail2->qty;
                $landed = $returpembeliandetail2->landedcost;
                $landed_final = $landed;
                $harga_final = ($harga1 + $landed_final) / $konversi->nilai_konversi;

                $stok_awal_retur = $tb_item_bulanan->retur_beli_stock;
                $amount_awal_retur = $tb_item_bulanan->retur_beli_amount;

                $qty_baru = $data[$i]['qty']*$konversi->nilai_konversi;
                $qty_baru2 = $data[$i]['qty'];
                $partnumber = $data[$i]['partnumber'];
                $no_mesin = $data[$i]['no_mesin'];

                $stok_retur_beli = $stok_awal_retur + $qty_baru;
                $amount_retur_beli = $amount_awal_retur + ($harga_final*$qty_baru);

                $end_stok = $stock_ending - $qty_baru;

                $end_amount = $amount_begin + $amount_masuk - $amount_out - $amount_sale + $amount_trfin - $amount_trfout + $amount_adj + $amount_op - $amount_retur_beli + $amount_returjual - $disassembling_amount + $assembling_amount;

                $produks = $data[$i]['kode_produk'];
                $cek_produk = Produk::on($konek)->find($produks);
                $nama_produk = $cek_produk->nama_produk;

                if($end_stok < 0){
                    exit("Barang $nama_produk telah digunakan dalam PEMAKAIAN / PENJUALAN pada periode ($bulan-$tahun). Silahkan UNPOST terlebih dahulu.");
                }

                $tgl_returbeli1 = $returpembelian->tanggal_returpembelian;
                $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_returbeli1)->year;
                $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_returbeli1)->month;

                $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                $status_reopen = $reopen->reopen_status;

                if($status_reopen == 'true'){
                    $tgl_returbeli = $returpembelian->tanggal_returpembelian;
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
                        $returpembeliandetail2 = ReturpembelianDetail::on($konek)->where('no_returpembelian', request()->id)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                        $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                        $harga1 = $returpembeliandetail2->harga;
                        $qty1 = $returpembeliandetail2->qty;
                        $landed = $returpembeliandetail2->landedcost;
                        $landed_final = $landed;
                        $harga_final = ($harga1 + $landed_final) / $konversi->nilai_konversi;

                        $stok_retur_beli = $data[$i]['qty']*$konversi->nilai_konversi; 
                        $amount_retur_beli = $harga_final*$stok_retur_beli;

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

                        $bs = $tb_item_bulanan2->begin_stock;
                        $ba = $tb_item_bulanan2->begin_amount;

                        $es = $tb_item_bulanan2->ending_stock;
                        $ea = $tb_item_bulanan2->ending_amount;

                        $begin_stock1 = $bs - $stok_retur_beli;
                        $begin_amount1 = $ba - $amount_retur_beli;

                        $end_stok1 = $es - $stok_retur_beli;
                        $end_amount1 = $ea - $amount_retur_beli;

                        if($end_stok1 < 0){
                            exit("Barang $nama_produk telah digunakan dalam PEMAKAIAN / PENJUALAN pada periode ($bulan2-$tahun2). Silahkan UNPOST atau RETUR PENJUALAN terlebih dahulu.");
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
        $data = Ledger::on($konek2)->with('costcenter')->select('ledger.*','u5611458_gui_general_ledger_laravel.coa.ac_description')->join('u5611458_gui_general_ledger_laravel.coa','u5611458_gui_general_ledger_laravel.coa.account','=','ledger.account')->where('ledger.no_journal',request()->id)->orderBy('ledger.created_at','desc')->get();
        return response()->json($data);
    }

    public function cekjurnal2()
    {
        $konek = self::konek();
        $konek2 = self::konek2();
        $cek = Ledger::on($konek2)->where('no_journal', request()->no_journal)->first();
        $cek_ar = ReturPemakaian::on($konek)->where('no_journal', request()->no_journal)->first();

        $output = array(
            'journal_date'=>Carbon\Carbon::parse($cek->journal_date)->format('d/m/Y'),
            'reference'=>$cek->reference,
            'created_at'=>($cek_ar->created_at)->format('d/m/Y H:i:s'),
            'updated_by'=>$cek->updated_by,
            'status'=>$cek_ar->status,
        );
        return response()->json($output);
    }


    public function Posting()
    {
        $konek = self::konek();
        $level = auth()->user()->level;
        $cek_company = Auth()->user()->kode_company;
        $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode','Disable')->first();
        
        $todays = Carbon\Carbon::now();
        $today = $todays->toDateString();
        $bans = ReturPemakaian::on($konek)->find(request()->id);
        $pemakaian = Pemakaian::on($konek)->find($bans->no_pemakaian);
        if ($bans->tgl_retur_pemakaian != $today) {
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Tanggal hari ini: '.$today.' Tanggal Retur Pemakaian berbeda, Posting hanya dapat dilakukan di hari yang sama.',
            ];
            return response()->json($message);
        }

        if($cek_bulan == null || $level == 'superadministrator' || $level == 'user_rince' || $level == 'user_thomas'){
            if($cek_company == '04' || $cek_company == '0401' || $cek_company == '03' || $cek_company == '05' || $cek_company == '06' || $cek_company == '02'){
                $returpembeliandetail = ReturpemakaianDetail::on($konek)->where('no_retur_pemakaian', request()->id)->get();
                $leng = count($returpembeliandetail);
                $data = array();

                $kat1 = 0;
                foreach ($returpembeliandetail as $rowdata){
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


            $returpembelian = ReturPemakaian::on($konek)->find(request()->id);
            $cek_status = $returpembelian->status;
            if($cek_status != 'OPEN'){
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'POST No. Retur Pemakaian: '.$returpembelian->no_retur_pemakaian.' sudah dilakukan! Pastikan Anda tidak membuka menu RETUR PEMAKAIAN lebih dari 1',
                ];
                return response()->json($message);
            }

            $no_returpembelian = $returpembelian->no_retur_pemakaian;
            $koneksi = $returpembelian->kode_lokasi;

            $tgl = $returpembelian->tgl_retur_pemakaian;
            $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
            $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
            $tanggal = '01';

            $tanggal_baru = Carbon\Carbon::createFromDate($tahun, $bulan, $tanggal)->toDateString();
            $lokasi = auth()->user()->kode_lokasi;
            $validate = $this->periodeChecker($tgl);
            if($validate != true){  
                $message = [
                    'success' => false,
                    'title' => 'Update',
                    'message' => 'Data gagal di POSTING, re-open [Bulan '.$bulan.'; Tahun '.$tahun.'].'
                ];
                return response()->json($message);
            }

            // $validate_produk = $this->produkChecker($no_returpembelian, $tahun, $bulan, $tanggal_baru, $tgl, $returpembelian, $koneksi);
            $validate_produk = true;

            if($validate_produk == true){
                $no_returpembelian = request()->id;
                $returpembeliandetail = ReturpemakaianDetail::on($konek)->where('no_retur_pemakaian', $no_returpembelian)->get();
                $data = array();

                if(!empty($returpembeliandetail)){
                    foreach ($returpembeliandetail as $rowdata){
                        $data[] = array(
                            'kode_produk'=>$rowdata->kode_produk,
                            'kode_satuan'=>$rowdata->kode_satuan,
                            'qty'=>$rowdata->qty,
                            'partnumber'=>$rowdata->partnumber,
                            'no_mesin'=>$rowdata->no_mesin,
                            'harga'=>$rowdata->harga,
                        );         
                    }
                }

                if(!empty($returpembeliandetail)){
                    $leng = count($returpembeliandetail);

                    $i = 0;

                    for($i = 0; $i < $leng; $i++){
                        $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

                        $amount_begin = $tb_item_bulanan->begin_amount;
                        $amount_masuk = $tb_item_bulanan->in_amount;
                        $amount_out = $tb_item_bulanan->out_amount;
                        $amount_sale = $tb_item_bulanan->sale_amount;     
                        $amount_adj = $tb_item_bulanan->adjustment_amount;
                        $amount_op = $tb_item_bulanan->amount_opname;
                        $amount_returjual = $tb_item_bulanan->retur_jual_amount;
                        $amount_trfin = $tb_item_bulanan->trf_in_amount;
                        $amount_trfout = $tb_item_bulanan->trf_out_amount;
                        $amount_dis = $tb_item_bulanan->disassembling_amount;
                        $amount_ass = $tb_item_bulanan->assembling_amount;
                        $amount_returbeli = $tb_item_bulanan->retur_beli_amount;

                        $stock_ending = $tb_item_bulanan->ending_stock;
                        $amount_akhir1 = $tb_item_bulanan->ending_amount;

                        $returpembeliandetail2 = ReturpemakaianDetail::on($konek)->where('no_retur_pemakaian', request()->id)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                        $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                        $harga1 = $returpembeliandetail2->harga;
                        $qty1 = $returpembeliandetail2->qty;
                        $harga_final = $harga1 / $konversi->nilai_konversi;

                        $stok_awal_retur = $tb_item_bulanan->retur_pakai_stock;
                        $amount_awal_retur = $tb_item_bulanan->retur_pakai_amount;

                        $qty_baru = $data[$i]['qty']*$konversi->nilai_konversi;
                        $qty_baru2 = $data[$i]['qty'];
                        $partnumber = $data[$i]['partnumber'];
                        $no_mesin = $data[$i]['no_mesin'];

                        $stok_retur_pakai = $stok_awal_retur + $qty_baru;
                        $amount_retur_pakai = $amount_awal_retur + ($harga_final*$qty_baru);

                        $end_stok = $stock_ending + $qty_baru;

                        $end_amount = $amount_begin + $amount_masuk - $amount_out - $amount_sale + $amount_trfin - $amount_trfout + $amount_adj + $amount_op - $amount_returbeli + $amount_returjual - $amount_dis + $amount_ass + $amount_retur_pakai;

                        if($end_stok != 0){
                            $hpp = $end_amount / $end_stok;
                        }else{
                            $hpp = $tb_item_bulanan->hpp;
                        }

                        $tabel_baru = [
                            'retur_pakai_stock'=>$stok_retur_pakai,
                            'retur_pakai_amount'=>$amount_retur_pakai,
                            'ending_stock'=>$end_stok,
                            'ending_amount'=>$end_amount,
                            'hpp'=>$hpp,
                        ];
                        
                        
                        if($pemakaian->no_wo != null){
                            $wodetail = WorkorderDetail::on($konek)->where('no_wo',$pemakaian->no_wo)->where('kode_produk',$data[$i]['kode_produk'])->first();
                            $wodetail->qty_pakai = $data[$i]['qty'];
                            if($wodetail->qty_pakai < $wodetail->qty){
                                $wodetail->status_produk = 'OFF';
                            }
                            
                            $wodetail->save();  
                        }
                        

                        $produk_awal = $tb_item_bulanan->kode_produk;
                        $update_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$produk_awal)->where('kode_lokasi',$lokasi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($tabel_baru); 

                        $barang = $data[$i]['kode_produk'];
                        $create_returpembelian = $returpembelian->created_at;
                        $tabel_history = [
                            'kode_produk'=>$barang,
                            'no_transaksi'=>$no_returpembelian,
                            'kode_lokasi'=>auth()->user()->kode_lokasi,
                            'tanggal_transaksi'=>$tgl,
                            'jam_transaksi'=>$create_returpembelian,
                            'qty_transaksi'=>$qty_baru,
                            'harga_transaksi'=>$harga_final,
                            'total_transaksi'=>$harga_final*$qty_baru,
                            'kode_lokasi'=>$lokasi,
                        ];

                        $update_produk_history = tb_produk_history::on($konek)->create($tabel_history);

                        $no_penerimaan = request()->no_penerimaan;
                        $penerimaandetail1 = PemakaianDetail::on($konek)->where('no_pemakaian', $no_penerimaan)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();
                        $qty_rec = $penerimaandetail1->qty_retur;
                        $penerimaandetail1->qty_retur = $qty_rec + $qty_baru2;
                        $penerimaandetail1->save();


                        $tgl_returbeli1 = $returpembelian->tgl_retur_pemakaian;
                        $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_returbeli1)->year;
                        $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_returbeli1)->month;

                        $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                        $status_reopen = $reopen->reopen_status;

                        if($status_reopen == 'true'){
                            $tgl_returbeli = $returpembelian->tgl_retur_pemakaian;
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
                                $returpembeliandetail2 = ReturpemakaianDetail::on($konek)->where('no_retur_pemakaian', request()->id)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                                $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                                $harga1 = $returpembeliandetail2->harga;
                                $qty1 = $returpembeliandetail2->qty;
                                $harga_final = $harga1  / $konversi->nilai_konversi;

                                $stok_retur_pakai = $data[$i]['qty']*$konversi->nilai_konversi; 
                                $amount_retur_pakai = $harga_final*$stok_retur_pakai;

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

                                $begin_stock1 = $bs + $stok_retur_pakai;
                                $begin_amount1 = $ba + $amount_retur_pakai;

                                $end_stok1 = $es + $stok_retur_pakai;
                                $end_amount1 = $ea + $amount_retur_pakai;

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

                $returpembelian->status = "POSTED";
                $returpembelian->save();

                $hitung = ReturpemakaianDetail::on($konek)->where('no_retur_pemakaian', request()->id)->get();
                $leng_retur = count($hitung);

                $penerimaan = Pemakaian::on($konek)->find($no_penerimaan);
                // $penerimaan->total_retur = $leng_retur;
                $penerimaan->save(); 

                $cek_terima = PemakaianDetail::on($konek)->where('no_pemakaian', $no_penerimaan)->where('qty_retur',0)->first();
                if($cek_terima == null){
                    $penerimaan = Pemakaian::on($konek)->find($no_penerimaan);
                    // $penerimaan->status = "RETUR";
                    $penerimaan->save(); 
                }

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Post Retur Pemakaian: '.$returpembelian->no_retur_pemakaian.'.','created_by'=>$nama,'updated_by'=>$nama];
                user_history::on($konek)->create($tmp);


                // //UPDATE JURNAL
                if($cek_company == '04' || $cek_company == '0401' || $cek_company == '03' || $cek_company == '05' || $cek_company == '06' || $cek_company == '02'){
                    $konek2 = self::konek2();
                    $cek_company = Auth()->user()->kode_company;
                    $lokasi = 'HO';

                    $total_qty = 0;
                    $total_harga = 0;
                    $grand_total = 0;
                    // $detail = ReturpemakaianDetail::on($konek)->where('no_retur_pemakaian',$returpembelian->no_retur_pemakaian)->get();
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
                    
                    $detail = KategoriProduk::join($compan.'.produk','kategori_produk.kode_kategori','=',$compan.'.produk.kode_kategori')->join($compan.'.retur_pemakaian_detail',$compan.'.produk.id','=',$compan.'.retur_pemakaian_detail.kode_produk')->where($compan.'.retur_pemakaian_detail.no_retur_pemakaian', request()->id)->groupBy('kategori_produk.kode_kategori')->get();
                    foreach ($detail as $row){
                        $total_qty += $row->qty;
                        $subtotal = $row->harga * $row->qty;
                        $total_harga += $subtotal;
                        
                        $totalhpp = ReturpemakaianDetail::on($konek)->select(DB::raw('SUM('.$compan.'.retur_pemakaian_detail.qty *'.$compan.'.retur_pemakaian_detail.harga) as total'))->join($compan.'.produk',$compan.'.retur_pemakaian_detail.kode_produk','=',$compan.'.produk.id')->where($compan.'.retur_pemakaian_detail.no_retur_pemakaian', request()->id)->where($compan.'.produk.kode_kategori', $row->kode_kategori)->first();
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
                            'cost_center'=>'',
                            'no_journal'=>$returpembelian->no_journal,
                            'journal_date'=>$returpembelian->tgl_retur_pemakaian,
                            'db_cr'=>'D',
                            'reference'=>$returpembelian->no_retur_pemakaian,
                            'debit'=>$totalhpp,
                            'kode_lokasi'=>$lokasi,
                        ];
                        $update = Ledger::on($konek2)->create($update_ledger);

                        $type = 'Inventory';
                        $transaksi = $returpembelian;
                        $tgl_trans = $returpembelian->tgl_retur_pemakaian;
                        $harga_acc = $totalhpp;
                        $dbkr = 'D';
                        $update_accbalance = $this->accbalance_debit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                        $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                        
                        $cek_balance2 = AccBalance::on($konek2)->where('account',$coa_biaya->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                        if ($cek_balance2 == null) {
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
                                        $begin = $cek_setelah->beginning_balance + $totalhpp;
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
                                        $begin = $cek_setelah->beginning_balance + $totalhpp;
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
    
                                        $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                    }
                                }
                            }
                        }
    
                        $update_ledger = [
                            'tahun'=>$tahun,
                            'periode'=>$bulan,
                            'account'=>$coa_biaya->account,
                            'cost_center'=>$pemakaian->cost_center,
                            'no_journal'=>$returpembelian->no_journal,
                            'journal_date'=>$returpembelian->tgl_retur_pemakaian,
                            'db_cr'=>'K',
                            'reference'=>$returpembelian->no_retur_pemakaian,
                            'kredit'=>$totalhpp,
                            'kode_lokasi'=>$lokasi,
                        ];
                        $update = Ledger::on($konek2)->create($update_ledger);
                            
                        $type = 'Inventory';
                        $transaksi = $returpembelian;
                        $tgl_trans = $returpembelian->tgl_retur_pemakaian;
                        $harga_acc = $totalhpp;
                        $dbkr = 'K';
                        $update_accbalance = $this->accbalance_kredit_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                        $update_lrb = $this->lrb_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                        
                    }
                }

                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data berhasil di POST.'
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
        
        $todays = Carbon\Carbon::now();
        $today = $todays->toDateString();
        $bans = ReturPemakaian::on($konek)->find(request()->id);
        if ($bans->tgl_retur_pemakaian != $today) {
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Tanggal hari ini: '.$today.' Tanggal Retur Pemakaian berbeda, Unposting hanya dapat dilakukan di hari yang sama.',
            ];
            return response()->json($message);
        }

        if($cek_bulan == null || $level == 'superadministrator' || $level == 'user_rince' || $level == 'user_thomas'){
            $returpembelian = ReturPemakaian::on($konek)->find(request()->id);
            $cek_status = $returpembelian->status;
            if($cek_status != 'POSTED'){  
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'UNPOST No. Retur Pemakaian: '.$returpembelian->no_retur_pemakaian.' sudah dilakukan! Pastikan Anda tidak membuka menu RETUR PEMAKAIAN lebih dari 1',
                ];
                return response()->json($message);
            }
            
            $tgl = $returpembelian->tgl_retur_pemakaian;
            $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
            $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
            $tanggal = '01';

            $tanggal_baru = Carbon\Carbon::createFromDate($tahun, $bulan, $tanggal)->toDateString();
            $lokasi = $returpembelian->kode_lokasi;
            $validate = $this->periodeChecker($tgl);

            if($validate == true){
                $no_returpembelian = request()->id;
                $returpembeliandetail = ReturpemakaianDetail::on($konek)->where('no_retur_pemakaian', $no_returpembelian)->get();
                $data = array();
                
                foreach ($returpembeliandetail as $rowdata){
                    $data[] = array(
                        'kode_produk'=>$rowdata->kode_produk,
                        'kode_satuan'=>$rowdata->kode_satuan,
                        'qty'=>$rowdata->qty,
                        'partnumber'=>$rowdata->partnumber,
                        'no_mesin'=>$rowdata->no_mesin,
                    );
                    
                    $konversi = Konversi::on($konek)->where('kode_produk',$rowdata->kode_produk)->where('kode_satuan',$rowdata->kode_satuan)->first();
                    
                    $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$rowdata->kode_produk)->where('partnumber',$rowdata->partnumber)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                    $ended = $tb_item_bulanan->ending_stock - ($rowdata->qty*$konversi->nilai_konversi);
                    if ($ended < 0){
                        $produk = Produk::on($konek)->find($rowdata->kode_produk);
                        $message = [
                            'success' => false,
                            'title' => 'Simpan',
                            'message' => 'Unpost retur pemakaian gagal!! Produk: ['.$rowdata->kode_produk.'] '.$produk->nama_produk.' stok tidak cukup, ada pemakaian/trf out.',
                        ];
                        return response()->json($message);
                    }
                }
                

                if(!empty($returpembeliandetail)){
                    $leng = count($returpembeliandetail);

                    $i = 0;

                    for($i = 0; $i < $leng; $i++){
                        $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

                        $amount_begin = $tb_item_bulanan->begin_amount;
                        $amount_masuk = $tb_item_bulanan->in_amount;
                        $amount_out = $tb_item_bulanan->out_amount;
                        $amount_sale = $tb_item_bulanan->sale_amount;     
                        $amount_adj = $tb_item_bulanan->adjustment_amount;
                        $amount_op = $tb_item_bulanan->amount_opname;
                        $amount_returjual = $tb_item_bulanan->retur_jual_amount;
                        $amount_trfin = $tb_item_bulanan->trf_in_amount;
                        $amount_trfout = $tb_item_bulanan->trf_out_amount;
                        $amount_dis = $tb_item_bulanan->disassembling_amount;
                        $amount_ass = $tb_item_bulanan->assembling_amount;
                        $amount_returbeli = $tb_item_bulanan->retur_beli_amount;

                        $stock_ending = $tb_item_bulanan->ending_stock;
                        $amount_akhir1 = $tb_item_bulanan->ending_amount;

                        $returpembeliandetail2 = ReturpemakaianDetail::on($konek)->where('no_retur_pemakaian', request()->id)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                        $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                        $harga1 = $returpembeliandetail2->harga;
                        $qty1 = $returpembeliandetail2->qty;
                        $harga_final = $harga1 / $konversi->nilai_konversi;

                        $stok_awal_retur = $tb_item_bulanan->retur_pakai_stock;
                        $amount_awal_retur = $tb_item_bulanan->retur_pakai_amount;

                        $qty_baru = $data[$i]['qty']*$konversi->nilai_konversi;
                        $qty_baru2 = $data[$i]['qty'];
                        $partnumber = $data[$i]['partnumber'];
                        $no_mesin = $data[$i]['no_mesin'];

                        $stok_retur_pakai = $stok_awal_retur - $qty_baru;
                        $amount_retur_pakai = $amount_awal_retur - ($harga_final*$qty_baru);

                        $end_stok = $stock_ending - $qty_baru;

                        $end_amount = $amount_begin + $amount_masuk - $amount_out - $amount_sale + $amount_trfin - $amount_trfout + $amount_adj + $amount_op - $amount_returbeli + $amount_returjual - $amount_dis + $amount_ass + $amount_retur_pakai;

                        if($end_stok != 0){
                            $hpp = $end_amount / $end_stok;
                        }else{
                            $hpp = $tb_item_bulanan->hpp;
                        }

                        $tabel_baru = [
                            'retur_pakai_stock'=>$stok_retur_pakai,
                            'retur_pakai_amount'=>$amount_retur_pakai,
                            'ending_stock'=>$end_stok,
                            'ending_amount'=>$end_amount,
                            'hpp'=>$hpp,
                        ];

                        $produk_awal = $tb_item_bulanan->kode_produk;
                        $update_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$produk_awal)->where('kode_lokasi',$lokasi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($tabel_baru); 

                        $update_produk_history = tb_produk_history::on($konek)->where('no_transaksi',$no_returpembelian)->delete();

                        $no_penerimaan = request()->no_penerimaan;

                        $penerimaandetail1 = PemakaianDetail::on($konek)->where('no_pemakaian', $no_penerimaan)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();
                        $qty_rec = $penerimaandetail1->qty_retur;
                        $penerimaandetail1->qty_retur = $qty_rec - $qty_baru2;
                        $penerimaandetail1->save();


                        $tgl_returbeli1 = $returpembelian->tgl_retur_pemakaian;
                        $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_returbeli1 )->year;
                        $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_returbeli1 )->month;

                        $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                        $status_reopen = $reopen->reopen_status;

                        if($status_reopen == 'true'){
                            $tgl_returbeli = $returpembelian->tgl_retur_pemakaian;
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
                                $returpembeliandetail2 = ReturpemakaianDetail::on($konek)->where('no_retur_pemakaian', request()->id)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                                $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                                $harga1 = $returpembeliandetail2->harga;
                                $qty1 = $returpembeliandetail2->qty;
                                $harga_final = $harga1 / $konversi->nilai_konversi;
                                                   
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

                $returpembelian->status = "OPEN";
                $returpembelian->save();    

                $penerimaan = Pemakaian::on($konek)->find($no_penerimaan);
                $penerimaan->save(); 

                // $penerimaan->status = "POSTED";
                // $penerimaan->save(); 

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Unpost No. Retur Pemakaian: '.$returpembelian->no_retur_pemakaian.'.','created_by'=>$nama,'updated_by'=>$nama];

                user_history::on($konek)->create($tmp);

                $cek_company = Auth()->user()->kode_company;
                if($cek_company == '04' || $cek_company == '0401' || $cek_company == '03' || $cek_company == '05' || $cek_company == '06' || $cek_company == '02'){
                    $konek2 = self::konek2();

                    $get_ledger = Ledger::on($konek2)->where('no_journal',$returpembelian->no_journal)->get();

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
                            $harga = $data[$i]['kredit'];

                            $type = 'Inventory';
                            $transaksi = $returpembelian;
                            $tgl_trans = $returpembelian->tgl_retur_pemakaian;
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
                            $harga = $data[$i]['debit'];

                            $type = 'Inventory';
                            $transaksi = $returpembelian;
                            $tgl_trans = $returpembelian->tgl_retur_pemakaian;
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

                    $update_ledger = Ledger::on($konek2)->where('no_journal',$returpembelian->no_journal)->delete();
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
                    'message' => 'Data gagal di UNPOSTING, re-open [Bulan '.$bulan.'; Tahun '.$tahun.'].'
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
        $tanggal = $request->tgl_retur_pemakaian;
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
            $returpembelian = ReturPemakaian::on($konek)->whereMonth('tgl_retur_pemakaian',$bulan_transaksi)->whereYear('tgl_retur_pemakaian',$tahun_transaksi)->where('status','OPEN')->get();
            if (count($returpembelian) >= 1){
                $message = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Masih ada retur pemakaian yang OPEN.'
                ];
               return response()->json($message);
            }
        }else {
            $tgl = tb_akhir_bulan::on($konek)->where('status_periode','Open')->first();
            $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl->periode)->year;
            $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl->periode)->month;
            $returpembelian = ReturPemakaian::on($konek)->whereMonth('tgl_retur_pemakaian',$bulan_transaksi)->whereYear('tgl_retur_pemakaian',$tahun_transaksi)->where('status','OPEN')->get();
            if (count($returpembelian) >= 1){
                $message = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Masih ada retur pemakaian yang OPEN.'
                ];
               return response()->json($message);
            }
        }
             
        if($validate == true){
            $returpembelian = ReturPemakaian::on($konek)->create($request->all());

            $no = ReturPemakaian::on($konek)->orderBy('created_at','desc')->first();
            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Simpan No.  Retur Pemakaian: '.$no->no_retur_pemakaian.'.','created_by'=>$nama,'updated_by'=>$nama];
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

    public function edit_returpemakaian()
    {
        $konek = self::konek();
        $no_returpembelian = request()->id;
        $data = ReturPemakaian::on($konek)->find($no_returpembelian);
        $output = array(
            'no_retur_pemakaian'=> $data->no_retur_pemakaian,
            'tgl_retur_pemakaian'=> $data->tgl_retur_pemakaian,
            'no_pemakaian'=> $data->no_pemakaian,
            'keterangan'=> $data->keterangan,
            'status'=> $data->status,
        );
        return response()->json($output);
    }

    public function updateAjax(Request $request)
    {
        $konek = self::konek();
        $cek_returpembelian = ReturPemakaian::on($konek)->find($request->no_retur_pemakaian);

        $tanggal = $request->tgl_retur_pemakaian;

        $validate = $this->periodeChecker($tanggal);
             
        if($validate == true){
            $returpembelian = ReturPemakaian::on($konek)->find($request->no_retur_pemakaian)->update($request->all());

            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Edit No. Retur Pemakaian: '.$request->no_retur_pemakaian.'.','created_by'=>$nama,'updated_by'=>$nama];
        
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


    public function hapus_returpemakaian()
    {
        $level = auth()->user()->level;
        $konek = self::konek();
            
        $no_returpembelian = request()->id;
        $data = ReturPemakaian::on($konek)->find($no_returpembelian);
        $tanggal = $data->tgl_retur_pemakaian;

        $validate = $this->periodeChecker($tanggal);

        if($validate == true){
            $data->delete();

            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Hapus No. Retur Pemakaian: '.$no_returpembelian.'.','created_by'=>$nama,'updated_by'=>$nama];

            user_history::on($konek)->create($tmp);

            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data ['.$data->no_retur_pemakaian.'] telah dihapus.'
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
}
