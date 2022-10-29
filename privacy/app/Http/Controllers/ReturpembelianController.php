<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Returpembelian;
use App\Models\ReturpembelianDetail;
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
use App\Models\Costcenter;
use PDF;
use Excel;
use DB;
use Carbon;
use DateTime;

class ReturpembelianController extends Controller
{
    public function index()
    {
        $konek = self::konek();
        $create_url = route('returpembelian.create');
        $Satuan= satuan::pluck('nama_satuan', 'kode_satuan');
        $Company= Company::pluck('nama_company','kode_company');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');

        $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_jalan2)->year;
        $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_jalan2)->month;

        $Penerimaan = Penerimaan::on($konek)->where('status','POSTED')->where('kode_lokasi',auth()->user()->kode_lokasi)->orderBy('created_at','desc')->pluck('no_penerimaan','no_penerimaan');

        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        $level = auth()->user()->level;
        
        return view('admin.returpembelian.index',compact('create_url','Penerimaan','Company','Satuan','period', 'nama_lokasi','nama_company'));
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

    public function getkode(){
        $konek = self::konek();
        $get = Returpembelian::on($konek)->join('returpembelian_detail','returpembelian_detail.no_returpembelian','=','retur_pembelian.no_returpembelian')->get();
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
                $update = Returpembelian::on($konek)->where('kode_vendor', $data[$i]['kode_vendor'])->update($tabel_baru);
            }

            //DETAIL
            $cek = Produk::on($konek)->where('kode_produk', $data[$i]['kode_produk'])->first();
            if ($cek != null) {
                $id = $cek->id;

                $tabel_baru = [
                    'kode_produk'=>$id,
                ];
                $update = ReturpembelianDetail::on($konek)->where('kode_produk', $data[$i]['kode_produk'])->update($tabel_baru);
            }
        }

        $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => 'Kode Berhasil di Update.'
        ];
            
        return response()->json($message);
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

    public function anyData()
    {
        $konek = self::konek();
        $lokasi = auth()->user()->kode_lokasi;
        if($lokasi == 'HO'){
            return Datatables::of(Returpembelian::on($konek)->with('company','vendor')->orderBy('created_at','desc'))->make(true);
        }
        else{
            return Datatables::of(Returpembelian::on($konek)->with('company','vendor')->orderBy('created_at','desc')->where('kode_lokasi', auth()->user()->kode_lokasi))->make(true);
        }
    }

    public function exportPDF(){
        $request = $_GET['no_returpembelian'];
        $konek = self::konek();
        $returpembelian = Returpembelian::on($konek)->where('no_returpembelian',$request)->first();
        $no_returpembelian = $returpembelian->no_returpembelian;

        $no_penerimaan = $returpembelian->no_penerimaan;
        $no_pembelian = $returpembelian->no_pembelian;
        $kode_company = $returpembelian->kode_company;

        $returpembeliandetail = ReturpembelianDetail::on($konek)->where('no_returpembelian',$request)->get();
        $penerimaan = Penerimaan::on($konek)->where('no_penerimaan',$no_penerimaan)->first();
        $pembelian = Pembelian::on($konek)->where('no_pembelian',$no_pembelian)->first();

        $kode_vendor = $pembelian->kode_vendor;
        $vendor = Vendor::where('id',$kode_vendor)->first();
        $nama_vendor = $vendor->nama_vendor;

        $company = Company::where('kode_company',$kode_company)->first();
        $nama_company = $company->nama_company;

        $dt = Carbon\Carbon::now();
        $date_now = Carbon\Carbon::parse($dt)->format('d/m/Y H:i:s');

        $tgl = $returpembelian->tanggal_returpembelian;
        $date=date_create($tgl);

        $leng = count($returpembeliandetail);
        $company = auth()->user()->kode_company;

        if($company != '04' && $company != '0401' && $company != '01' && $company != '05'){
            if($leng <= 8){
                $pdf = PDF::loadView('/admin/returpembelian/pdf', compact('returpembeliandetail','request', 'nama_vendor','no_pembelian','tgl', 'no_penerimaan','nama_company','date_now','returpembelian','no_returpembelian','pembelian','konek','date_now'));
                $pdf->setPaper([0, 0, 684, 792], 'potrait');

                return $pdf->stream('Retur Pembelian '.$no_returpembelian.'.pdf');
            }
            else{
                $pdf = PDF::loadView('/admin/returpembelian/pdfnew', compact('returpembeliandetail','request', 'nama_vendor','no_pembelian','tgl', 'no_penerimaan','nama_company','date_now','returpembelian','no_returpembelian','pembelian','konek','date_now'));
                $pdf->setPaper([0, 0, 684, 792], 'potrait');
                return $pdf->stream('Retur Pembelian '.$no_returpembelian.'.pdf');
            }
        }
        else if($company == '01'){
            if($leng <= 8){
                $pdf = PDF::loadView('/admin/returpembelian/pdfdepo', compact('returpembeliandetail','request', 'nama_vendor','no_pembelian','tgl', 'no_penerimaan','nama_company','date_now','returpembelian','no_returpembelian','pembelian','konek','date_now'));
                $pdf->setPaper([0, 0, 684, 792], 'potrait');
                return $pdf->stream('Retur Pembelian '.$no_returpembelian.'.pdf');
            }
            else{
                $pdf = PDF::loadView('/admin/returpembelian/pdfnewdepo', compact('returpembeliandetail','request', 'nama_vendor','no_pembelian','tgl', 'no_penerimaan','nama_company','date_now','returpembelian','no_returpembelian','pembelian','konek','date_now'));
                $pdf->setPaper([0, 0, 684, 792], 'potrait');
                return $pdf->stream('Retur Pembelian '.$no_returpembelian.'.pdf');
            }
        }
        else if($company == '05'){
            if($leng <= 8){
                $pdf = PDF::loadView('/admin/returpembelian/pdfsub', compact('returpembeliandetail','request', 'nama_vendor','no_pembelian','tgl', 'no_penerimaan','nama_company','date_now','returpembelian','no_returpembelian','pembelian','konek','date_now'));
                $pdf->setPaper([0, 0, 684, 792], 'potrait');
                return $pdf->stream('Retur Pembelian '.$no_returpembelian.'.pdf');
            }
            else{
                $pdf = PDF::loadView('/admin/returpembelian/pdfnewsub', compact('returpembeliandetail','request', 'nama_vendor','no_pembelian','tgl', 'no_penerimaan','nama_company','date_now','returpembelian','no_returpembelian','pembelian','konek','date_now'));
                $pdf->setPaper([0, 0, 684, 792], 'potrait');
                return $pdf->stream('Retur Pembelian '.$no_returpembelian.'.pdf');
            }
        }
        else{
            if($leng <= 8){
                $pdf = PDF::loadView('/admin/returpembelian/pdfgut', compact('returpembeliandetail','request', 'nama_vendor','no_pembelian','tgl', 'no_penerimaan','nama_company','date_now','returpembelian','no_returpembelian','pembelian','konek','date_now'));
                $pdf->setPaper([0, 0, 684, 792], 'potrait');
                return $pdf->stream('Retur Pembelian '.$no_returpembelian.'.pdf');
            }
            else{
                $pdf = PDF::loadView('/admin/returpembelian/pdfnewgut', compact('returpembeliandetail','request', 'nama_vendor','no_pembelian','tgl', 'no_penerimaan','nama_company','date_now','returpembelian','no_returpembelian','pembelian','konek','date_now'));
                $pdf->setPaper([0, 0, 684, 792], 'potrait');
                return $pdf->stream('Retur Pembelian '.$no_returpembelian.'.pdf');
            }
        }        
    }


    public function exportPDF2(){
        $konek = self::konek();
        $konek2 = self::konek2();
        $request = $_GET['no_returpembelian'];
        $no_journal = $_GET['no_journal'];

        $returbeli = Returpembelian::on($konek)->find($request);
        $jur = $returbeli->kode_jurnal;
        $jurnal = Jurnal::find($jur);

        $total_qty = 0;
        $total_harga = 0;
        $grand_total = 0;
        $detail = ReturpembelianDetail::on($konek)->where('no_returpembelian',$request)->get();
        foreach ($detail as $row){
            $total_qty += $row->qty;
            $subtotal = ($row->harga + $row->landedcost) * $row->qty;
            $total_harga += $subtotal;
            $grand_total = $total_harga;
        }

        $ledger2 = Ledger::on($konek2)->with('coa')->where('no_journal',$no_journal)->first();

        $ledger = Ledger::on($konek2)->select('ledger.*','coa.ac_description')->join('u5611458_gui_general_ledger_laravel.coa','u5611458_gui_general_ledger_laravel.coa.account','=','ledger.account')->where('no_journal', $no_journal)->get();

        $user = $returbeli->created_by;
        $tgl = $returbeli->tanggal_returpembelian;
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

        $pdf = PDF::loadView('/admin/returpembelian/pdf2', compact('returbeli','request', 'jurnal','tgl','date', 'ttd','nama_company','date_now','ledger','ledger2','dt','user','nama','nama2','journal_date','grand_total'));
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
        $returpembelian = Returpembelian::on($konek)->find($returpembelian);
        $tanggal = $returpembelian->tanggal_returpembelian;
        $no_returpembelian = $returpembelian->no_returpembelian;

        $validate = $this->periodeChecker($tanggal);

        if($validate == true){
            $data = Returpembelian::on($konek)->find($no_returpembelian);

            $Produk = PenerimaanDetail::on($konek)->where('penerimaan_detail.no_penerimaan', $data->no_penerimaan)
                ->Join('produk', 'penerimaan_detail.kode_produk', '=', 'produk.id')
                ->pluck('produk.nama_produk','produk.id','penerimaan_detail.qty');
                
            $Parts = PenerimaanDetail::on($konek)->where('no_penerimaan', $data->no_penerimaan)
                ->pluck('partnumber','partnumber');

            $Satuan = satuan::pluck('nama_satuan','kode_satuan');
            $list_url= route('returpembelian.index');

            $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
            $tgl_jalan2 = $tgl_jalan->periode;
            $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
            $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
            $nama_lokasi = $get_lokasi->nama_lokasi;

            $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
            $nama_company = $get_company->nama_company;

            return view('admin.returpembeliandetail.index', compact('returpembelian','list_url','Produk','Satuan','period', 'nama_lokasi','nama_company','Parts'));
        }
    }

    public function Showdetail()
    {
        $konek = self::konek();
        $total_qty = 0;
        $total_harga = 0;
        $grand_total = 0;
        $returpembeliandetail= ReturpembelianDetail::on($konek)->with('produk','satuan')->where('no_returpembelian',request()->id)
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
                $no_returpembelian = $row->no_returpembelian;
                $produk = $row->produk->nama_produk;
                $no_mesin = $row->no_mesin;
                $partnumber = $row->partnumber;
                $satuan = $row->satuan->nama_satuan;
                $qty = $row->qty;
                $harga = $row->harga;
                $landedcost = $row->landedcost;
                $subtotal =  number_format(($row->harga * $row->qty) + ($row->landedcost*$row->qty),2,",",".");
                $output[] = array(
                    'produk'=>$produk,
                    'satuan'=>$satuan,
                    'partnumber'=>$partnumber,
                    'no_mesin'=>$no_mesin,
                    'qty'=>$qty,
                    'harga'=>$harga,
                    'landedcost'=>$landedcost,
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
        $cek_ar = Returpembelian::on($konek)->where('no_journal', request()->no_journal)->first();

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

        if($cek_bulan == null || $level == 'superadministrator' || $level == 'user_rince' || $level == 'user_thomas'){
            if($cek_company == '04' || $cek_company == '0401' || $cek_company == '03' || $cek_company == '05' || $cek_company == '06' || $cek_company == '02'){
                $returpembeliandetail = ReturpembelianDetail::on($konek)->where('no_returpembelian', request()->id)->get();
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

            $returpembelian = Returpembelian::on($konek)->find(request()->id);
            $cek_status = $returpembelian->status;
            if($cek_status != 'OPEN'){
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'POST No. Retur Pembelian: '.$returpembelian->no_returpembelian.' sudah dilakukan! Pastikan Anda tidak membuka menu RETUR PEMBELIAN lebih dari 1',
                ];
                return response()->json($message);
            }

            $no_returpembelian = $returpembelian->no_returpembelian;
            $koneksi = $returpembelian->kode_lokasi;

            $tgl = $returpembelian->tanggal_returpembelian;
            $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
            $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
            $tanggal = '01';

            $tanggal_baru = Carbon\Carbon::createFromDate($tahun, $bulan, $tanggal)->toDateString();
            $lokasi = $returpembelian->kode_lokasi;
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
            $validate_produk = 'true';
            if($validate_produk == true){
                $no_returpembelian = request()->id;
                $returpembeliandetail = ReturpembelianDetail::on($konek)->where('no_returpembelian', $no_returpembelian)->get();
                $data = array();

                foreach ($returpembeliandetail as $rowdata){
                    $data[] = array(
                        'kode_produk'=>$rowdata->kode_produk,
                        'kode_satuan'=>$rowdata->kode_satuan,
                        'qty'=>$rowdata->qty,
                        'partnumber'=>$rowdata->partnumber,
                        'no_mesin'=>$rowdata->no_mesin,
                        'harga'=>$rowdata->harga,
                        'landedcost'=>$rowdata->landedcost,
                    );
                    
                    $konversi = konversi::on($konek)->where('kode_produk',$rowdata->kode_produk)->where('kode_satuan',$rowdata->kode_satuan)->first();
                    $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$rowdata->kode_produk)->where('partnumber',$rowdata->partnumber)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                    $ended = $tb_item_bulanan->ending_stock - ($rowdata->qty * $konversi->nilai_konversi);
                    if ($ended < 0){
                        $produk = Produk::on($konek)->find($rowdata->kode_produk);
                        $message = [
                            'success' => false,
                            'title' => 'Update',
                            'message' => 'Gagal Post!! Stok sudah dipakai/ada trf out, ['.$rowdata->kode_produk.'] '.$produk->nama_produk.'.'
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
                        $amount_rpk = $tb_item_bulanan->retur_pakai_amount;

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

                        $end_amount = $amount_begin + $amount_masuk - $amount_out - $amount_sale + $amount_trfin - $amount_trfout + $amount_adj + $amount_op - $amount_retur_beli + $amount_returjual - $amount_dis + $amount_ass + $amount_rpk;

                        if($end_stok != 0){
                            $hpp = $end_amount / $end_stok;
                        }else{
                            $hpp = $tb_item_bulanan->hpp;
                        }

                        $tabel_baru = [
                            'retur_beli_stock'=>$stok_retur_beli,
                            'retur_beli_amount'=>$amount_retur_beli,
                            'ending_stock'=>$end_stok,
                            'ending_amount'=>$end_amount,
                            'hpp'=>$hpp,
                        ];

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
                            'qty_transaksi'=>0-$qty_baru,
                            'harga_transaksi'=>$harga_final,
                            'total_transaksi'=>0-($harga_final*$qty_baru),
                            'kode_lokasi'=>$lokasi,
                        ];

                        $update_produk_history = tb_produk_history::on($konek)->create($tabel_history);

                        $no_penerimaan = request()->no_penerimaan;
                        $penerimaandetail1 = PenerimaanDetail::on($konek)->where('no_penerimaan', $no_penerimaan)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();
                        $qty_rec = $penerimaandetail1->qty_retur;
                        $penerimaandetail1->qty_retur = $qty_rec + $qty_baru2;
                        $penerimaandetail1->save();


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

                                $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$lokasi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->first();

                                $bs = $tb_item_bulanan2->begin_stock;
                                $ba = $tb_item_bulanan2->begin_amount;

                                $es = $tb_item_bulanan2->ending_stock;
                                $ea = $tb_item_bulanan2->ending_amount;

                                $begin_stock1 = $bs - $stok_retur_beli;
                                $begin_amount1 = $ba - $amount_retur_beli;

                                $end_stok1 = $es - $stok_retur_beli;
                                $end_amount1 = $ea - $amount_retur_beli;

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

                $hitung = ReturpembelianDetail::on($konek)->where('no_returpembelian', request()->id)->get();
                $leng_retur = count($hitung);

                $penerimaan = Penerimaan::on($konek)->find($no_penerimaan);
                $penerimaan->total_retur = $leng_retur;
                $penerimaan->save(); 

                $cek_terima = PenerimaanDetail::on($konek)->where('no_penerimaan', $no_penerimaan)->where('qty_retur',0)->first();
                if($cek_terima == null){
                    // $penerimaan = Penerimaan::on($konek)->find($no_penerimaan);
                    // $penerimaan->status = "RETUR";
                    // $penerimaan->save(); 
                }

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Post Retur Pembelian: '.$returpembelian->no_returpembelian.'.','created_by'=>$nama,'updated_by'=>$nama];
                user_history::on($konek)->create($tmp);

                //UPDATE JURNAL
                if($cek_company == '04' || $cek_company == '0401' || $cek_company == '03' || $cek_company == '05' || $cek_company == '06' || $cek_company == '02'){
                    $konek2 = self::konek2();
                    $cek_company = Auth()->user()->kode_company;

                    $total_qty = 0;
                    $total_harga = 0;
                    $grand_total = 0;
                    $detail = ReturpembelianDetail::on($konek)->where('no_returpembelian',$returpembelian->no_returpembelian)->get();
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

                    for ($i = 0; $i < $leng; $i++) { 
                        $cek_produk = Produk::on($konek)->where('id', $data[$i]['kode_produk'])->first();

                        $bulan = Carbon\Carbon::parse($returpembelian->tanggal_returpembelian)->format('m');
                        $tahun = Carbon\Carbon::parse($returpembelian->tanggal_returpembelian)->format('Y');

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
                        }else if($cek_company == '06'){
                            $kategori = KategoriProduk::where('kode_kategori', 'APD')->first();
                            $coa_inventory = Coa::where('kode_coa', $kategori->coa_infra)->first();
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

                        $update_ledger = [
                            'tahun'=>$tahun,
                            'periode'=>$bulan,
                            'account'=>$coa_inventory->account,
                            'no_journal'=>$returpembelian->no_journal,
                            'journal_date'=>$returpembelian->tanggal_returpembelian,
                            'db_cr'=>'K',
                            'reference'=>$returpembelian->no_returpembelian,
                            'kredit'=>$gt_apd,
                            'kode_lokasi'=>$lokasi,
                        ];
                        $update = Ledger::on($konek2)->create($update_ledger);

                        $type = 'Inventory';
                        $transaksi = $returpembelian;
                        $tgl_trans = $returpembelian->tanggal_returpembelian;
                        $harga_acc = $gt_apd;
                        $dbkr = 'K';
                        $update_accbalance = $this->accbalance_kredit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
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
                        }else if($cek_company == '06'){
                            $kategori = KategoriProduk::where('kode_kategori', 'BAN')->first();
                            $coa_inventory = Coa::where('kode_coa', $kategori->coa_infra)->first();
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
                            'no_journal'=>$returpembelian->no_journal,
                            'journal_date'=>$returpembelian->tanggal_returpembelian,
                            'db_cr'=>'K',
                            'reference'=>$returpembelian->no_returpembelian,
                            'kredit'=>$gt_ban,
                            'kode_lokasi'=>$lokasi,
                        ];
                        $update = Ledger::on($konek2)->create($update_ledger);

                        $type = 'Inventory';
                        $transaksi = $returpembelian;
                        $tgl_trans = $returpembelian->tanggal_returpembelian;
                        $harga_acc = $gt_ban;
                        $dbkr = 'K';
                        $update_accbalance = $this->accbalance_kredit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
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
                        }else if($cek_company == '06'){
                            $kategori = KategoriProduk::where('kode_kategori', 'BBM')->first();
                            $coa_inventory = Coa::where('kode_coa', $kategori->coa_infra)->first();
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

                        $update_ledger = [
                            'tahun'=>$tahun,
                            'periode'=>$bulan,
                            'account'=>$coa_inventory->account,
                            'no_journal'=>$returpembelian->no_journal,
                            'journal_date'=>$returpembelian->tanggal_returpembelian,
                            'db_cr'=>'K',
                            'reference'=>$returpembelian->no_returpembelian,
                            'kredit'=>$gt_bbm,
                            'kode_lokasi'=>$lokasi,
                        ];
                        $update = Ledger::on($konek2)->create($update_ledger);

                        $type = 'Inventory';
                        $transaksi = $returpembelian;
                        $tgl_trans = $returpembelian->tanggal_returpembelian;
                        $harga_acc = $gt_bbm;
                        $dbkr = 'K';
                        $update_accbalance = $this->accbalance_kredit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
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
                        }else if($cek_company == '06'){
                            $kategori = KategoriProduk::where('kode_kategori', 'OLI')->first();
                            $coa_inventory = Coa::where('kode_coa', $kategori->coa_infra)->first();
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

                        $update_ledger = [
                            'tahun'=>$tahun,
                            'periode'=>$bulan,
                            'account'=>$coa_inventory->account,
                            'no_journal'=>$returpembelian->no_journal,
                            'journal_date'=>$returpembelian->tanggal_returpembelian,
                            'db_cr'=>'K',
                            'reference'=>$returpembelian->no_returpembelian,
                            'kredit'=>$gt_oli,
                            'kode_lokasi'=>$lokasi,
                        ];
                        $update = Ledger::on($konek2)->create($update_ledger);

                        $type = 'Inventory';
                        $transaksi = $returpembelian;
                        $tgl_trans = $returpembelian->tanggal_returpembelian;
                        $harga_acc = $gt_oli;
                        $dbkr = 'K';
                        $update_accbalance = $this->accbalance_kredit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
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
                        }else if($cek_company == '06'){
                            $kategori = KategoriProduk::where('kode_kategori', 'SPRT')->first();
                            $coa_inventory = Coa::where('kode_coa', $kategori->coa_infra)->first();
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

                        $update_ledger = [
                            'tahun'=>$tahun,
                            'periode'=>$bulan,
                            'account'=>$coa_inventory->account,
                            'no_journal'=>$returpembelian->no_journal,
                            'journal_date'=>$returpembelian->tanggal_returpembelian,
                            'db_cr'=>'K',
                            'reference'=>$returpembelian->no_returpembelian,
                            'kredit'=>$gt_sprt,
                            'kode_lokasi'=>$lokasi,
                        ];
                        $update = Ledger::on($konek2)->create($update_ledger);

                        $type = 'Inventory';
                        $transaksi = $returpembelian;
                        $tgl_trans = $returpembelian->tanggal_returpembelian;
                        $harga_acc = $gt_sprt;
                        $dbkr = 'K';
                        $update_accbalance = $this->accbalance_kredit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
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
                        }else if($cek_company == '06'){
                            $kategori = KategoriProduk::where('kode_kategori', 'UNIT')->first();
                            $coa_inventory = Coa::where('kode_coa', $kategori->coa_infra)->first();
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

                        $update_ledger = [
                            'tahun'=>$tahun,
                            'periode'=>$bulan,
                            'account'=>$coa_inventory->account,
                            'no_journal'=>$returpembelian->no_journal,
                            'journal_date'=>$returpembelian->tanggal_returpembelian,
                            'db_cr'=>'K',
                            'reference'=>$returpembelian->no_returpembelian,
                            'kredit'=>$gt_unit,
                            'kode_lokasi'=>$lokasi,
                        ];
                        $update = Ledger::on($konek2)->create($update_ledger);

                        $type = 'Inventory';
                        $transaksi = $returpembelian;
                        $tgl_trans = $returpembelian->tanggal_returpembelian;
                        $harga_acc = $gt_unit;
                        $dbkr = 'K';
                        $update_accbalance = $this->accbalance_kredit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
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
                        }else if($cek_company == '06'){
                            $kategori = KategoriProduk::where('kode_kategori', 'SLDG')->first();
                            $coa_inventory = Coa::where('kode_coa', $kategori->coa_infra)->first();
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

                        $update_ledger = [
                            'tahun'=>$tahun,
                            'periode'=>$bulan,
                            'account'=>$coa_inventory->account,
                            'no_journal'=>$returpembelian->no_journal,
                            'journal_date'=>$returpembelian->tanggal_returpembelian,
                            'db_cr'=>'K',
                            'reference'=>$returpembelian->no_returpembelian,
                            'kredit'=>$gt_sldg,
                            'kode_lokasi'=>$lokasi,
                        ];
                        $update = Ledger::on($konek2)->create($update_ledger);

                        $type = 'Inventory';
                        $transaksi = $returpembelian;
                        $tgl_trans = $returpembelian->tanggal_returpembelian;
                        $harga_acc = $gt_sldg;
                        $dbkr = 'K';
                        $update_accbalance = $this->accbalance_kredit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                        $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                    }

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
                                    $begin = $cek_setelah->beginning_balance - $grand_total;
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
                                    $begin = $cek_setelah->beginning_balance - $grand_total;
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

                    $update_ledger = [
                        'tahun'=>$tahun,
                        'periode'=>$bulan,
                        'account'=>$coa_hutang->account,
                        'no_journal'=>$returpembelian->no_journal,
                        'journal_date'=>$returpembelian->tanggal_returpembelian,
                        'db_cr'=>'D',
                        'reference'=>$returpembelian->no_returpembelian,
                        'debit'=>$grand_total,
                        'kode_lokasi'=>$lokasi,
                    ];
                    $update = Ledger::on($konek2)->create($update_ledger);
                        
                    $type = 'Inventory';
                    $transaksi = $returpembelian;
                    $tgl_trans = $returpembelian->tanggal_returpembelian;
                    $harga_acc = $grand_total;
                    $dbkr = 'D';
                    $update_accbalance = $this->accbalance_debit_post($coa_hutang, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                    $update_lrb = $this->lrb_post($coa_hutang, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
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

        if($cek_bulan == null || $level == 'superadministrator' || $level == 'user_rince' || $level == 'user_thomas'){
            $returpembelian = Returpembelian::on($konek)->find(request()->id);
            $cek_status = $returpembelian->status;
            if($cek_status != 'POSTED'){  
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'UNPOST No. Retur Pembelian: '.$returpembelian->no_returpembelian.' sudah dilakukan! Pastikan Anda tidak membuka menu RETUR PEMBELIAN lebih dari 1',
                ];
                return response()->json($message);
            }
            
            $tgl = $returpembelian->tanggal_returpembelian;
            $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
            $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
            $tanggal = '01';

            $tanggal_baru = Carbon\Carbon::createFromDate($tahun, $bulan, $tanggal)->toDateString();
            $lokasi = auth()->user()->kode_lokasi;
            $validate = $this->periodeChecker($tgl);

            if($validate == true){
                $no_returpembelian = request()->id;
                $returpembeliandetail = ReturpembelianDetail::on($konek)->where('no_returpembelian', $no_returpembelian)->get();
                $data = array();
                
                foreach ($returpembeliandetail as $rowdata){
                    $data[] = array(
                        'kode_produk'=>$rowdata->kode_produk,
                        'kode_satuan'=>$rowdata->kode_satuan,
                        'qty'=>$rowdata->qty,
                        'partnumber'=>$rowdata->partnumber,
                        'no_mesin'=>$rowdata->no_mesin,
                    );          
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
                        $amount_rpk = $tb_item_bulanan->retur_pakai_amount;

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

                        $stok_retur_beli = $stok_awal_retur - $qty_baru;
                        $amount_retur_beli = $amount_awal_retur - ($harga_final*$qty_baru);

                        $end_stok = $stock_ending + $qty_baru;

                        $end_amount = $amount_begin + $amount_masuk - $amount_out - $amount_sale + $amount_trfin - $amount_trfout + $amount_adj + $amount_op - $amount_retur_beli + $amount_returjual - $amount_dis + $amount_ass + $amount_rpk;

                        if($end_stok != 0){
                            $hpp = $end_amount / $end_stok;
                        }else{
                            $hpp = $tb_item_bulanan->hpp;
                        }

                        $tabel_baru = [
                            'retur_beli_stock'=>$stok_retur_beli,
                            'retur_beli_amount'=>$amount_retur_beli,
                            'ending_stock'=>$end_stok,
                            'ending_amount'=>$end_amount,
                            'hpp'=>$hpp,
                        ];

                        $produk_awal = $tb_item_bulanan->kode_produk;
                        $update_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$produk_awal)->where('kode_lokasi',$lokasi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($tabel_baru); 

                        $update_produk_history = tb_produk_history::on($konek)->where('no_transaksi',$no_returpembelian)->delete();

                        $no_penerimaan = request()->no_penerimaan;

                        $penerimaandetail1 = PenerimaanDetail::on($konek)->where('no_penerimaan', $no_penerimaan)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();
                        $qty_rec = $penerimaandetail1->qty_retur;
                        $penerimaandetail1->qty_retur = $qty_rec - $qty_baru2;
                        $penerimaandetail1->save();


                        $tgl_returbeli1 = $returpembelian->tanggal_returpembelian;
                        $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_returbeli1 )->year;
                        $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_returbeli1 )->month;

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

                                    $begin_stock1 = $bs + $stok_masuk;
                                    $begin_amount1 = $ba + $amount_masuk;

                                    $end_stok1 = $es + $stok_masuk;
                                    $end_amount1 = $ea + $amount_masuk;

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

                $penerimaan = Penerimaan::on($konek)->find($no_penerimaan);
                $penerimaan->total_retur = 0;
                $penerimaan->save(); 

                $penerimaan->status = "POSTED";
                $penerimaan->save(); 

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Unpost No. Retur Pembelian: '.$returpembelian->no_returpembelian.'.','created_by'=>$nama,'updated_by'=>$nama];

                user_history::on($konek)->create($tmp);

                $cek_company = Auth()->user()->kode_company;
                if($cek_company == '04' || $cek_company == '0401' || $cek_company == '03' || $cek_company == '05' || $cek_company == '06' || $cek_company =='02'){
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
                            $harga = $data[$i]['debit'];

                            $type = 'Inventory';
                            $transaksi = $returpembelian;
                            $tgl_trans = $returpembelian->tanggal_returpembelian;
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
                            $transaksi = $returpembelian;
                            $tgl_trans = $returpembelian->tanggal_returpembelian;
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
        $tanggal = $request->tanggal_returpembelian;
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
            $returpembelian = Returpembelian::on($konek)->whereMonth('tanggal_returpembelian',$bulan_transaksi)->whereYear('tanggal_returpembelian',$tahun_transaksi)->where('status','OPEN')->get();
            if (count($returpembelian) >= 1){
                $message = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Masih ada retur pembelian yang OPEN.'
                ];
               return response()->json($message);
            }
        }else {
            $tgl = tb_akhir_bulan::on($konek)->where('status_periode','Open')->first();
            $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl->periode)->year;
            $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl->periode)->month;
            $returpembelian = Returpembelian::on($konek)->whereMonth('tanggal_returpembelian',$bulan_transaksi)->whereYear('tanggal_returpembelian',$tahun_transaksi)->where('status','OPEN')->get();
            if (count($returpembelian) >= 1){
                $message = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Masih ada retur pembelian yang OPEN.'
                ];
               return response()->json($message);
            }
        }
             
        if($validate == true){
            $returpembelian = Returpembelian::on($konek)->create($request->all());

            $no = Returpembelian::on($konek)->orderBy('created_at','desc')->first();
            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Simpan No.  Retur Pembelian: '.$no->no_returpembelian.'.','created_by'=>$nama,'updated_by'=>$nama];
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

    public function edit_returpembelian()
    {
        $konek = self::konek();
        $no_returpembelian = request()->id;
        $data = Returpembelian::on($konek)->find($no_returpembelian);
        $output = array(
            'no_returpembelian'=> $data->no_returpembelian,
            'tanggal_returpembelian'=> $data->tanggal_returpembelian,
            'no_penerimaan'=> $data->no_penerimaan,
            'no_pembelian'=> $data->no_pembelian,
            'kode_vendor'=> $data->kode_vendor,
            'keterangan'=> $data->keterangan,
            'status'=> $data->status,
        );
        return response()->json($output);
    }

    public function updateAjax(Request $request)
    {
        $konek = self::konek();
        $cek_returpembelian = Returpembelian::on($konek)->find($request->no_returpembelian);

        $tanggal = $request->tanggal_returpembelian;

        $validate = $this->periodeChecker($tanggal);
             
        if($validate == true){
            $returpembelian = Returpembelian::on($konek)->find($request->no_returpembelian)->update($request->all());

            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Edit No. Retur Pembelian: '.$request->no_returpembelian.'.','created_by'=>$nama,'updated_by'=>$nama];
        
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


    public function hapus_returpembelian()
    {
        $level = auth()->user()->level;
        $konek = self::konek();
            
        $no_returpembelian = request()->id;
        $data = Returpembelian::on($konek)->find($no_returpembelian);
        $tanggal = $data->tanggal_returpembelian;

        $validate = $this->periodeChecker($tanggal);

        if($validate == true){
            $data->delete();

            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Hapus No. Retur Pembelian: '.$no_returpembelian.'.','created_by'=>$nama,'updated_by'=>$nama];

            user_history::on($konek)->create($tmp);

            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data ['.$data->no_returpembelian.'] telah dihapus.'
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
