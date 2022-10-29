<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Penerimaan;
use App\Models\PenerimaanDetail;
use App\Models\Requestpembelian;
use App\Models\RequestpembelianDetail;
use App\Models\RequestProduk;
use App\Models\Vendor;
use App\Models\Produk;
use App\Models\Jasa;
use App\Models\Nonstock;
use App\Models\satuan;
use App\Models\Konversi;
use App\Models\Catatanpo;
use App\Models\Signature;
use App\Models\tb_akhir_bulan;
use App\Models\tb_item_bulanan;
use App\Models\KategoriProduk;
use App\Models\Merek;
use App\Models\Ukuran;
use App\Models\user_history;
use App\Models\MasterLokasi;
use App\Models\Company;
use App\Models\TaxSetup;
use App\Models\Approve_po;
use App\Models\SetupAkses;
use App\Models\Opname;
use App\Models\Costcenter;
use App\Models\SetupFolder;
use Illuminate\Support\Facades\Storage;
use PDF;
use Excel;
use DB;
use Carbon;


class PembelianController extends Controller
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
        $create_url = route('pembelian.create');
        $Vendor= Vendor::pluck('nama_vendor','id');
        $no_pembelian= Pembelian::on($konek)->where('status','POSTED')->orwhere('status','CLOSED')->pluck('no_pembelian','no_pembelian');
        $Company= Company::pluck('nama_company','kode_company');
        $Lokasi= MasterLokasi::pluck('nama_lokasi','kode_lokasi');
        $Norequest = Requestpembelian::on($konek)->where('status','POSTED')->pluck('no_request','no_request');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;
        
        $Costcenter = Costcenter::where('kode_company', auth()->user()->kode_company)->pluck('desc','cost_center');

        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        $level = auth()->user()->level;
        
        if($level != 'ap'){
            return view('admin.pembelian.index3',compact('Costcenter','Norequest','create_url','Vendor','Company','no_pembelian','period', 'nama_lokasi','nama_company','Lokasi'));
        }else{
            return view('admin.pembelian.index2',compact('create_url','Vendor','Company','no_pembelian','period', 'nama_lokasi','nama_company','Lokasi'));
        } 
    }

    public function anyData()
    {   
        $konek = self::konek();
        return Datatables::of(DB::connection($konek)->table('pembelian')
            ->join('u5611458_db_pusat.vendor','pembelian.kode_vendor','=','u5611458_db_pusat.vendor.id')
            ->select('pembelian.*','u5611458_db_pusat.vendor.nama_vendor')
            ->where('pembelian.kode_lokasi', auth()->user()->kode_lokasi)
            ->get())->make(true);
    }
    
    public function getDatapreview(){
        $konek = self::konek();
        $data = PembelianDetail::on($konek)->with('produk','satuan','jasa','nonstock')->where('no_pembelian',request()->id)->orderBy('created_at','desc')->get();
        return response()->json($data);
    }
    
    public function ttd_buat()
    {
        $konek = self::konek();
        $signature = request()->img;
        $signatureFileName = request()->no.'-dibuat'.'.png';
        $signature = str_replace('data:image/png;base64,', '', $signature);
        $signature = str_replace(' ', '+', $signature);
        $data = base64_decode($signature);

        $cekfile = realpath(dirname(getcwd())).'/gui_inventory_laravel/digital/pembelian/'.$signatureFileName;
        if (file_exists($cekfile)) {
            unlink($cekfile);
        }

        $folder = realpath(dirname(getcwd())).'/gui_inventory_laravel/digital/pembelian/';
        $file = $folder.$signatureFileName;
        file_put_contents($file, $data);

        $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => 'TTD (Dibuat Oleh) telah disimpan.'
        ];
        return response()->json($message);
    }

    public function ttd_periksa()
    {
        $konek = self::konek();
        $signature = request()->img;
        $signatureFileName = request()->no.'-diperiksa'.'.png';
        $signature = str_replace('data:image/png;base64,', '', $signature);
        $signature = str_replace(' ', '+', $signature);
        $data = base64_decode($signature);

        $cekfile = realpath(dirname(getcwd())).'/gui_inventory_laravel/digital/pembelian/'.$signatureFileName;
        if (file_exists($cekfile)) {
            unlink($cekfile);
        }

        $folder = realpath(dirname(getcwd())).'/gui_inventory_laravel/digital/pembelian/';
        $file = $folder.$signatureFileName;
        file_put_contents($file, $data);

        $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => 'TTD (Diperiksa) telah disimpan.'
        ];
        return response()->json($message);
    }
    
    public function ttd_setuju()
    {
        $konek = self::konek();
        $signature = request()->img;
        $signatureFileName = request()->no.'-disetujui'.'.png';
        $signature = str_replace('data:image/png;base64,', '', $signature);
        $signature = str_replace(' ', '+', $signature);
        $data = base64_decode($signature);

        $cekfile = realpath(dirname(getcwd())).'/gui_inventory_laravel/digital/pembelian/'.$signatureFileName;
        if (file_exists($cekfile)) {
            unlink($cekfile);
        }

        $folder = realpath(dirname(getcwd())).'/gui_inventory_laravel/digital/pembelian/';
        $file = $folder.$signatureFileName;
        file_put_contents($file, $data);

        $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => 'TTD (Disetujui) telah disimpan.'
        ];
        return response()->json($message);
    }
    
    public function ttd_tahu()
    {
        $konek = self::konek();
        $signature = request()->img;
        $signatureFileName = request()->no.'-diketahui'.'.png';
        $signature = str_replace('data:image/png;base64,', '', $signature);
        $signature = str_replace(' ', '+', $signature);
        $data = base64_decode($signature);

        $cekfile = realpath(dirname(getcwd())).'/gui_inventory_laravel/digital/pembelian/'.$signatureFileName;
        if (file_exists($cekfile)) {
            unlink($cekfile);
        }

        $folder = realpath(dirname(getcwd())).'/gui_inventory_laravel/digital/pembelian/';
        $file = $folder.$signatureFileName;
        file_put_contents($file, $data);

        $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => 'TTD (Diketahui) telah disimpan.'
        ];
        return response()->json($message);
    }
    
    public function limitos()
    {
        $konek = self::konek();
        $limit = SetupAkses::on($konek)->where('limit_dari', 0)->where('limit_total', 50000000)->first();
        $limit2 = SetupAkses::on($konek)->where('limit_dari', 50000000)->where('limit_total', 500000000)->first();
        $limit3 = SetupAkses::on($konek)->where('limit_dari', 500000000)->where('limit_total','>', 500000000)->first();
        $nama = auth()->user()->name;
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
    
    public function previewpo()
    {
        $konek = self::konek();
        $pembelian = Pembelian::on($konek)->find(request()->id);
        $subtotal = PembelianDetail::on($konek)->where('no_pembelian', $pembelian->no_pembelian)->sum('total_transaksi');

        if ($pembelian->diskon_persen > 0) {
            $diskon = $subtotal * ($pembelian->diskon_persen/100);
        }else if ($pembelian->diskon_rp > 0) {
            $diskon = $subtotal - $pembelian->diskon_rp;
        }else {
            $diskon = 0;
        }

        if ($pembelian->ppn > 0){
            $ppn = ($subtotal - $diskon) * ($pembelian->ppn/100);
        }else {
            $ppn = 0;
        }

        if ($pembelian->pbbkb > 0){
            $pbbkb = $subtotal * ($pembelian->pbbkb/100);
        }else {
            $pbbkb = 0;
        }

        $vendor = Vendor::find($pembelian->kode_vendor);
        $note = $pembelian->deskripsi;
        
        $output = array(
            'vendor'=>$vendor->nama_vendor,
            'alamat'=>$vendor->alamat,
            'kontak'=>$vendor->telp,
            'npwp'=>$vendor->npwp,
            'note'=>$note,
            'no_po'=>$pembelian->no_pembelian,
            'tgl_po'=>$pembelian->tanggal_pembelian,
            'no_penawaran'=>$pembelian->no_penawaran,
            'subtotal'=>number_format($subtotal),
            'diskon'=>number_format($diskon),
            'ppn'=>number_format($ppn),
            'pbbkb'=>number_format($pbbkb),
            'grand_total'=>number_format($subtotal - $diskon + $ppn + $pbbkb),
            'kode_company'=>Auth()->user()->kode_company,
        );
        return response()->json($output);
    }
    
    public function historia()
    {
        $konek = self::konek();
        $name = auth()->user()->name;
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
            'test'=>$name,
        );
        return response()->json($output);
    }

    public function get_ppn()
    {
        $cek_vendor = Vendor::find(request()->kode_vendor);
        $tanggal = Carbon\Carbon::now();
        
        $get_tax = TaxSetup::where('kode_pajak','PPN')->where('tgl_berlaku','<=', $tanggal)->orderBy('tgl_berlaku', 'desc')->first();
        if($cek_vendor->pkp == 'Y'){
            $output = array(
                'ppn'=> $get_tax->nilai_pajak
            );
        }else{
            $output = array(
                'ppn'=> 0
            );
        }
        return response()->json($output);
    }

    public function get_ppn2()
    {
        $cek_vendor = Vendor::find(request()->kode_vendor);
        $get_tax = TaxSetup::where('kode_pajak','PPN')->where('tgl_berlaku','<=', $tanggal)->orderBy('tgl_berlaku', 'desc')->first();
        if($cek_vendor->npwp != null && $cek_vendor->npwp != 0 && $cek_vendor->npwp != 1 && $cek_vendor->npwp != '-'){
            $output = array(
                'ppn'=> $get_tax->nilai_pajak
            );
        }else{
            $output = array(
                'ppn'=> 0
            );
        }
        return response()->json($output);
    }
    
    public function exportPDF(){
        $konek = self::konek();
        $request = $_GET['no_pembelian'];

        $pembelian = Pembelian::on($konek)->where('no_pembelian',$request)->first();
        $tipe = $pembelian->jenis_po;
        $user = $pembelian->created_by;
        $kode_company = $pembelian->kode_company;
        $company_user = Company::where('kode_company',$kode_company)->first();

        $catatan_po = Catatanpo::on($konek)->get();

        $tgl = $pembelian->tanggal_pembelian;
        $date=date_create($tgl);
        
        $dt = Carbon\Carbon::now();
        $date_now = Carbon\Carbon::parse($dt)->format('d/m/Y H:i:s');

        $pembeliandetail = PembelianDetail::on($konek)->where('no_pembelian',$request)->get();
        $leng = count($pembeliandetail);
        
        $total_qty = 0;

        foreach ($pembeliandetail as $row){
            $total_qty += $row->total_transaksi;
        }

        $ttd = $user;
        $limit3 = Signature::on($konek)->where('jabatan','DIREKTUR')->first();
        if($limit3 == null){
            $limit3 = Signature::on($konek)->where('jabatan','DIREKTUR UTAMA / DIREKTUR KEUANGAN')->first();
            $limitdari3 = $limit3->limit_dari;
            $limitsampai3 = $limit3->limit_sampai;
        }else{
            $limitdari3 = $limit3->limit_dari;
            $limitsampai3 = $limit3->limit_sampai;
        }

        $limit2 = Signature::on($konek)->where('jabatan','DIREKTUR KEUANGAN')->first();
        $limitdari2 = $limit2->limit_dari;
        $limitsampai2 = $limit2->limit_sampai;

        $limit1 = Signature::on($konek)->where('jabatan','MANAGER KEUANGAN')->first();
        $limitdari1 = $limit1->limit_dari;
        $limitsampai1 = $limit1->limit_sampai;

        $limit = Signature::on($konek)->where('jabatan','MANAGER OPERASIONAL')->first();

        $limit4 = Signature::on($konek)->where('jabatan','ACCOUNTING')->first();

        if ($total_qty>=$limitdari1 && $total_qty<$limitsampai1){
            $limiter = $limit1->mengetahui;
            $jabatan = $limit1->jabatan;
        }
        else if ($total_qty>=$limitdari2 && $total_qty<$limitsampai2){
            $limiter = $limit2->mengetahui;
            $jabatan = $limit2->jabatan;
        }
        else if ($total_qty>=$limitdari3){
            $limiter = $limit3->mengetahui;
            $jabatan = $limit3->jabatan;
        }
        else {
            $limiter = '';
            $jabatan = '';
        }
        
        $company = auth()->user()->kode_company;
        if($company == '05'){
            if($leng <= 8){
                $pdf = PDF::loadView('/admin/pembelian/pdfsub', compact('pembeliandetail','request', 'pembelian','catatan_po','date', 'ttd', 'limiter','total_qty','jabatan','limit','limit4','tipe','date_now'));
                $pdf->setPaper([0, 0, 684, 792], 'potrait');
            }
            else{
                $pdf = PDF::loadView('/admin/pembelian/pdfnewsub', compact('pembeliandetail','request', 'pembelian','catatan_po','date', 'ttd', 'limiter','total_qty','jabatan','limit','limit4','tipe','date_now'));
                $pdf->setPaper([0, 0, 684, 792], 'potrait');
            }
        }else {
            if($leng <= 6){
                $pdf = PDF::loadView('/admin/pembelian/pdf', compact('pembeliandetail','request', 'pembelian','catatan_po','date', 'ttd', 'limiter','total_qty','jabatan','limit','limit4','tipe','date_now'));
                $pdf->setPaper([0, 0, 684, 792], 'potrait');
            }
            else{
                $pdf = PDF::loadView('/admin/pembelian/pdfnew', compact('pembeliandetail','request', 'pembelian','catatan_po','date', 'ttd', 'limiter','total_qty','jabatan','limit','limit4','tipe','date_now'));
                $pdf->setPaper([0, 0, 684, 792], 'potrait');
            }
        }
        
        $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
        $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
        
        if(substr($request,2,3) == 'NPO'){
            $setupfolder = SetupFolder::find(12);
        }else{
            $setupfolder = SetupFolder::find(13);
        }
        
        $tes_save = $company_user->kode_company.". ".$company_user->nama_company."/".$setupfolder->folder."/PO/".$setupfolder->subfolder."/".$tahun."/".$bulan."/".$request.".pdf";
        
        $nama = auth()->user()->name;
        $tmp = ['nama' => $nama,'aksi' => 'Print No. Pembelian: '.$request.'.','created_by'=>$nama,'updated_by'=>$nama];
        user_history::on($konek)->create($tmp);
                
        Storage::disk('ftp')->put($tes_save, $pdf->output());
        return $pdf->stream($request.'.pdf');
    }
    
    public function printpreview(){
        $konek = self::konek();
        $request = $_GET['no_pembelian'];

        $pembelian = Pembelian::on($konek)->where('no_pembelian',$request)->first();
        $tipe = $pembelian->jenis_po;
        $user = $pembelian->created_by;

        $catatan_po = Catatanpo::on($konek)->get();

        $tgl = $pembelian->tanggal_pembelian;
        $date=date_create($tgl);
        
        $dt = Carbon\Carbon::now();
        $date_now = Carbon\Carbon::parse($dt)->format('d/m/Y H:i:s');

        $pembeliandetail = PembelianDetail::on($konek)->where('no_pembelian',$request)->get();
        $leng = count($pembeliandetail);
        
        $total_qty = 0;

        foreach ($pembeliandetail as $row){
            $total_qty += $row->total_transaksi;
        }

        $ttd = $user;
        $limit3 = Signature::on($konek)->where('jabatan','DIREKTUR')->first();
        if($limit3 == null){
            $limit3 = Signature::on($konek)->where('jabatan','DIREKTUR UTAMA / DIREKTUR KEUANGAN')->first();
            $limitdari3 = $limit3->limit_dari;
            $limitsampai3 = $limit3->limit_sampai;
        }else{
            $limitdari3 = $limit3->limit_dari;
            $limitsampai3 = $limit3->limit_sampai;
        }

        $limit2 = Signature::on($konek)->where('jabatan','DIREKTUR KEUANGAN')->first();
        $limitdari2 = $limit2->limit_dari;
        $limitsampai2 = $limit2->limit_sampai;

        $limit1 = Signature::on($konek)->where('jabatan','MANAGER KEUANGAN')->first();
        $limitdari1 = $limit1->limit_dari;
        $limitsampai1 = $limit1->limit_sampai;

        $limit = Signature::on($konek)->where('jabatan','MANAGER OPERASIONAL')->first();
        $limit4 = Signature::on($konek)->where('jabatan','ACCOUNTING')->first();

        if ($total_qty>=$limitdari1 && $total_qty<$limitsampai1){
            $limiter = $limit1->mengetahui;
            $jabatan = $limit1->jabatan;
        }
        else if ($total_qty>=$limitdari2 && $total_qty<$limitsampai2){
            $limiter = $limit2->mengetahui;
            $jabatan = $limit2->jabatan;
        }
        else if ($total_qty>=$limitdari3){
            $limiter = $limit3->mengetahui;
            $jabatan = $limit3->jabatan;
        }
        else {
            $limiter = '';
            $jabatan = '';
        }
        
        $company = auth()->user()->kode_company;
        
        if($leng <= 6){
            return view('admin.pembelian.previewpdf',compact('pembeliandetail','request', 'pembelian','catatan_po','date', 'ttd', 'limiter','total_qty','jabatan','limit','limit4','tipe','date_now'));
        }
        else{
            return view('admin.pembelian.previewpdfnew',compact('pembeliandetail','request', 'pembelian','catatan_po','date', 'ttd', 'limiter','total_qty','jabatan','limit','limit4','tipe','date_now'));
        }
    }

    public function detail($pembelian)
    {
        $konek = self::konek();
        $pembelian = Pembelian::on($konek)->find($pembelian);
        $tanggal = $pembelian->tanggal_pembelian;
        $no_pembelian = $pembelian->no_pembelian;
        $jenis_po = $pembelian->jenis_po;

        $data = Pembelian::on($konek)->find($no_pembelian);

        if($jenis_po == 'Stock'){
            $total_qty = 0;
            $total_harga = 0;
            $grand_total = 0;

            $pembeliandetail = PembelianDetail::on($konek)->with('produk','satuan')->where('no_pembelian', $pembelian->no_pembelian)
            ->orderBy('created_at','desc')->get();

            $list_url= route('pembelian.index');
            $Produk = Produk::on($konek)->pluck('nama_produk','id');
            $Satuan = satuan::pluck('nama_satuan','kode_satuan');
            $Kategori = KategoriProduk::pluck('nama_kategori', 'kode_kategori');
            $Merek = Merek::on($konek)->pluck('nama_merek', 'kode_merek');
            $Ukuran= Ukuran::on($konek)->pluck('nama_ukuran', 'kode_ukuran');

            $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
            $tgl_jalan2 = $tgl_jalan->periode;
            $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
            $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
            $nama_lokasi = $get_lokasi->nama_lokasi;

            $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
            $nama_company = $get_company->nama_company;

            return view('admin.pembeliandetail.index', compact('pembelian','pembeliandetail','list_url','Produk','Satuan','total_qty','grand_total','Kategori','Merek','Ukuran','period','nama_lokasi','nama_company'));
        }
        else if($jenis_po == 'Jasa'){
            $total_qty = 0;
            $total_harga = 0;
            $grand_total = 0;

            $pembeliandetail = PembelianDetail::on($konek)->with('produk','satuan')->where('no_pembelian', $pembelian->no_pembelian)
            ->orderBy('created_at','desc')->get();

            $list_url= route('pembelian.index');
            $Produk = Jasa::pluck('nama_item','id');
            $Satuan = satuan::pluck('nama_satuan','kode_satuan');
            $Kategori = KategoriProduk::pluck('nama_kategori', 'kode_kategori');
            $Merek = Merek::on($konek)->pluck('nama_merek', 'kode_merek');
            $Ukuran= Ukuran::on($konek)->pluck('nama_ukuran', 'kode_ukuran');

            $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
            $tgl_jalan2 = $tgl_jalan->periode;
            $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
            $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
            $nama_lokasi = $get_lokasi->nama_lokasi;

            $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
            $nama_company = $get_company->nama_company;


            return view('admin.pembeliandetail.index', compact('pembelian','pembeliandetail','list_url','Produk','Satuan','total_qty','grand_total','Kategori','Merek','Ukuran','period','nama_lokasi','nama_company'));
        }else{
            $total_qty = 0;
            $total_harga = 0;
            $grand_total = 0;

            $pembeliandetail = PembelianDetail::on($konek)->with('produk','satuan')->where('no_pembelian', $pembelian->no_pembelian)
            ->orderBy('created_at','desc')->get();

            $list_url= route('pembelian.index');
            $Produk = Nonstock::pluck('nama_item','id');
            $Satuan = satuan::pluck('nama_satuan','kode_satuan');
            $Kategori = KategoriProduk::pluck('nama_kategori', 'kode_kategori');
            $Merek = Merek::on($konek)->pluck('nama_merek', 'kode_merek');
            $Ukuran= Ukuran::on($konek)->pluck('nama_ukuran', 'kode_ukuran');

            $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
            $tgl_jalan2 = $tgl_jalan->periode;
            $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
            $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
            $nama_lokasi = $get_lokasi->nama_lokasi;

            $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
            $nama_company = $get_company->nama_company;

            return view('admin.pembeliandetail.index', compact('pembelian','pembeliandetail','list_url','Produk','Satuan','total_qty','grand_total','Kategori','Merek','Ukuran','period','nama_lokasi','nama_company'));
        }
    }

    public function Showdetail()
    {
        $konek = self::konek();
        $pembeliandetail= PembelianDetail::on($konek)->with('produk','satuan','jasa')->where('no_pembelian',request()->id)
        ->orderBy('created_at', 'desc')->get();

        $pembelian= Pembelian::on($konek)->where('no_pembelian',request()->id)->first();
        $jenis = $pembelian->jenis_po;

        $output = array();

        if($pembeliandetail){
            if($jenis == 'Stock'){
                foreach($pembeliandetail as $row)
                {
                    $output[] = array(
                        'no_pembelian'=>$row->no_pembelian,
                        'produk'=>$row->produk->nama_produk,
                        'keterangan'=>'-',
                        'satuan'=>$row->satuan->nama_satuan,
                        'qty'=>$row->qty,
                        'harga'=>$row->harga,
                        'subtotal'=>number_format($row->harga * $row->qty),
                        'qty_received'=>$row->qty_received,
                    );
                }
            }
            else if($jenis == 'Jasa'){
                foreach($pembeliandetail as $row)
                {
                    $output[] = array(
                        'no_pembelian'=>$row->no_pembelian,
                        'produk'=>$row->jasa->nama_item,
                        'keterangan'=>'-',
                        'satuan'=>$row->kode_satuan,
                        'qty'=>$row->qty,
                        'harga'=>$row->harga,
                        'subtotal'=>number_format($row->harga * $row->qty),
                        'qty_received'=>$row->qty_received,
                    );
                }
            }
            else{
                foreach($pembeliandetail as $row)
                {
                    $output[] = array(
                        'no_pembelian'=>$row->no_pembelian,
                        'produk'=>$row->nonstock->nama_item,
                        'keterangan'=>$row->keterangan,
                        'satuan'=>$row->kode_satuan,
                        'qty'=>$row->qty,
                        'harga'=>$row->harga,
                        'subtotal'=>number_format($row->harga * $row->qty),
                        'qty_received'=>$row->qty_received,
                    );
                }
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

    public function Post()
    {
        $konek = self::konek();
        $level = auth()->user()->level;
        $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode','Disable')->first();
        
        if($cek_bulan == null || $level == 'superadministrator' || $level == 'user_rince' || $level == 'user_thomas' || $level == 'rince_pbm' || $level == 'merisa_pbm'){
            $permintaan = Pembelian::on($konek)->find(request()->id);
            $no_pembelian = $permintaan->no_pembelian;
            $cek_status = $permintaan->status;
            if($cek_status == 'OPEN'){
                $tgl = $permintaan->tanggal_pembelian;
                $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
                $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;

                if(stripos($no_pembelian, 'GA') == TRUE){
                    $permintaan->status = "POSTED";
                    $permintaan->save();

                    $nama = auth()->user()->name;
                    $tmp = ['nama' => $nama,'aksi' => 'Post No. Pembelian: '.$permintaan->no_pembelian.'.','created_by'=>$nama,'updated_by'=>$nama];

                    user_history::on($konek)->create($tmp);

                    $message = [
                        'success' => true,
                        'title' => 'Update',
                        'message' => 'Data berhasil di POST.'
                    ];
                    return response()->json($message);
                }else{
                    $validate = $this->periodeChecker($tgl);
                    if($validate != true){
                        $message = [
                            'success' => false,
                            'title' => 'Update',
                            'message' => 'Data gagal di POST, re-open [Bulan '.$bulan.'; Tahun '.$tahun.'].'
                        ];
                        return response()->json($message);
                    }
                    else{
                        $permintaan->status = "POSTED";
                        $permintaan->save();
                        
                        if($permintaan->no_request != null){
                            $qtydetail = 0;
                            $qtypodetail = 0;

                            $reqpembelian = Requestpembelian::on($konek)->where('no_request',$permintaan->no_request)->first();
                            $detail = PembelianDetail::on($konek)->where('no_pembelian',request()->id)->get();
                            foreach($detail as $row){
                                $reqdetail = Requestproduk::on($konek)->where('no_request',$permintaan->no_request)->where('kode_produk',$row->kode_produk)->first();
                                    if ($reqdetail != null){
                                        $reqdetail->qty_po += $row->qty;
                                        $qtydetail += $reqdetail->qty;
                                        $qtypodetail += $reqdetail->qty_po;
                                        if ($reqdetail->qty_po >= $reqdetail->qty){
                                            $reqdetail->status_produk = "ON";
                                        }
                                        
                                        $reqdetail->save();
                                    }
                            }
                            $getstatus = Requestproduk::on($konek)->where('no_request',$permintaan->no_request)->where('status_produk','OFF')->first();
                            if($getstatus == null){
                                $reqpembelian->status = "CLOSED";
                                $reqpembelian->save();
                            }
                        }

                        $nama = auth()->user()->name;
                        $tmp = ['nama' => $nama,'aksi' => 'Post No. Pembelian: '.$permintaan->no_pembelian.'.','created_by'=>$nama,'updated_by'=>$nama];
                        user_history::on($konek)->create($tmp);

                        $message = [
                            'success' => true,
                            'title' => 'Update',
                            'message' => 'Data berhasil di POST.'
                        ];
                        return response()->json($message);
                    }
                }
            }else{
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'POST No. Pembelian: '.$permintaan->no_pembelian.' sudah dilakukan! Pastikan Anda tidak membuka menu PEMBELIAN lebih dari 1',
                ];
                return response()->json($message);
            } 
        }else{
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Anda tidak mempunyai akses posting data saat periode re-open',
            ];
            return response()->json($message);
        }
        
    }

    public function Unpost()
    {
        $konek = self::konek();
        $level = auth()->user()->level;
        $name = auth()->user()->name;
        $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode','Disable')->first();

        if($cek_bulan == null || $level == 'superadministrator' || $level == 'user_rince' || $level == 'user_thomas' || $level == 'rince_pbm' || $level == 'merisa_pbm'){
            $permintaan = Pembelian::on($konek)->find(request()->id);
            $no_pembelian = $permintaan->no_pembelian;
            $cek_status = $permintaan->status;
            if($cek_status == 'POSTED'){     
                $tgl = $permintaan->tanggal_pembelian;
                $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
                $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;

                if(stripos($no_pembelian, 'GA') == TRUE){
                    $permintaan->status = "OPEN";
                    $permintaan->save();

                    $nama = auth()->user()->name;
                    $tmp = ['nama' => $nama,'aksi' => 'Unpost No. Pembelian: '.$permintaan->no_pembelian.'.','created_by'=>$nama,'updated_by'=>$nama];

                    user_history::on($konek)->create($tmp);
                }else{
                    $validate = $this->periodeChecker($tgl);
                    if($validate != true){
                        $message = [
                            'success' => false,
                            'title' => 'Update',
                            'message' => 'Data gagal di UNPOST, re-open [Bulan '.$bulan.'; Tahun '.$tahun.'].'
                        ];
                        return response()->json($message);
                    }
                    else{
                        $created = $permintaan->created_by;
                        
                        $permintaan->status = "OPEN";
                        $permintaan->save();

                        $cek_detail = Penerimaan::on($konek)->where('no_pembelian',$permintaan->no_pembelian)->first();
                        if ($cek_detail != null){
                            $hapus_detail = PenerimaanDetail::on($konek)->join('produk', 'penerimaan_detail.kode_produk', '=', 'produk.id')->where('produk.kode_kategori','<>','BAN')->where('produk.tipe_produk','<>','Serial')->where('no_penerimaan',$cek_detail->no_penerimaan)->delete();

                            $cek_penerimaan = PenerimaanDetail::on($konek)->where('no_penerimaan',$cek_detail->no_penerimaan)->get();
                            $leng = count($cek_penerimaan);
                            if($leng > 0){
                                $cek_detail->total_item = $leng;
                                $cek_detail->save();
                            }else{
                                $cek_detail->total_item = 0;
                                $cek_detail->save();
                            }
                        }
                        
                        if($permintaan->no_request != null){
                            $reqpembelian = Requestpembelian::on($konek)->where('no_request',$permintaan->no_request)->first();
                            $detail = PembelianDetail::on($konek)->where('no_pembelian',request()->id)->get();
                            foreach($detail as $row){
                                $reqdetail = Requestproduk::on($konek)->where('no_request',$permintaan->no_request)->where('kode_produk',$row->kode_produk)->first();
                                if($reqdetail != null){
                                    $updateqty = $reqdetail->qty_po - $row->qty;
                                    $reqdetail->qty_po = $updateqty;
                                    $reqdetail->status_produk = "OFF";
                                    $reqdetail->save();
                                }
                            } 
                            $reqpembelian->status = "POSTED";
                            $reqpembelian->save();
                        }

                        $nama = auth()->user()->name;
                        $tmp = ['nama' => $nama,'aksi' => 'Unpost No. Pembelian: '.$permintaan->no_pembelian.'.','created_by'=>$nama,'updated_by'=>$nama];

                        user_history::on($konek)->create($tmp);
                    }
                }
                
                $signatureFileName1 = request()->id.'-dibuat'.'.png';
                $signatureFileName2 = request()->id.'-diperiksa'.'.png';
                $signatureFileName3 = request()->id.'-disetujui'.'.png';
                $signatureFileName4 = request()->id.'-diketahui'.'.png';
        
                $cekfile1 = realpath(dirname(getcwd())).'/gui_inventory_laravel/digital/pembelian/'.$signatureFileName1;
                if (file_exists($cekfile1)) {
                    unlink($cekfile1);
                }
                
                $cekfile2 = realpath(dirname(getcwd())).'/gui_inventory_laravel/digital/pembelian/'.$signatureFileName2;
                if (file_exists($cekfile2)) {
                    unlink($cekfile2);
                }
                
                $cekfile3 = realpath(dirname(getcwd())).'/gui_inventory_laravel/digital/pembelian/'.$signatureFileName3;
                if (file_exists($cekfile3)) {
                    unlink($cekfile3);
                }
                
                $cekfile4 = realpath(dirname(getcwd())).'/gui_inventory_laravel/digital/pembelian/'.$signatureFileName4;
                if (file_exists($cekfile4)) {
                    unlink($cekfile4);
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
                    'title' => 'Simpan',
                    'message' => 'UNPOST No. Pembelian: '.$permintaan->no_pembelian.' sudah dilakukan! Pastikan Anda tidak membuka menu PEMBELIAN lebih dari 1',
                ];
                return response()->json($message);
            }     
        }else{
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Anda tidak mempunyai akses unposting data saat periode re-open',
            ];
            return response()->json($message);
        }
        
    }
    
    public function Approve()
    {
        $konek = self::konek();
        $level = auth()->user()->level;
        $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode','Disable')->first();
        
        if($cek_bulan == null || $level == 'superadministrator' || $level == 'user_rince' || $level == 'user_gina' || $level == 'user_thomas'){
            $permintaan = Pembelian::on($konek)->find(request()->id);
            $no_pembelian = $permintaan->no_pembelian;
            $cek_status = $permintaan->status;
            if($cek_status == 'RECEIVED'){
                $tgl = $permintaan->tanggal_pembelian;
                $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
                $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;

                $permintaan->status = "APPROVED";
                $permintaan->save();

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Approve No. Pembelian: '.$permintaan->no_pembelian.'.','created_by'=>$nama,'updated_by'=>$nama];
                user_history::on($konek)->create($tmp);

                $approve = ['no_pembelian' => $no_pembelian,'approve_status' => 'true'];
                Approve_po::on($konek)->create($approve);

                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data berhasil di APPROVE.'
                ];
                return response()->json($message);
            }else{
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'APPROVE No. Pembelian: '.$permintaan->no_pembelian.' sudah dilakukan! Pastikan Anda tidak membuka menu PEMBELIAN lebih dari 1',
                ];
                return response()->json($message);
            } 
        }else{
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Anda tidak mempunyai akses Approve',
            ];
            return response()->json($message);
        }
        
    }

    public function Disapprove()
    {
        $konek = self::konek();
        $level = auth()->user()->level;
        
        if($level == 'superadministrator' || $level == 'rince_pbm' || $level == 'user_gina' || $level == 'user_thomas'){
            $permintaan = Pembelian::on($konek)->find(request()->id);
            $no_pembelian = $permintaan->no_pembelian;
            $cek_status = $permintaan->status;
            if($cek_status == 'APPROVED'){
                $tgl = $permintaan->tanggal_pembelian;
                $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
                $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;

                $permintaan->status = "RECEIVED";
                $permintaan->save();

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Disapprove No. Pembelian: '.$permintaan->no_pembelian.'.','created_by'=>$nama,'updated_by'=>$nama];
                user_history::on($konek)->create($tmp);

                $approve = ['no_pembelian' => $no_pembelian,'approve_status' => 'false'];
                Approve_po::on($konek)->create($approve);

                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data berhasil di DISAPPROVE.'
                ];
                return response()->json($message);
            }else{
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'DISAPPROVE No. Pembelian: '.$permintaan->no_pembelian.' sudah dilakukan! Pastikan Anda tidak membuka menu PEMBELIAN lebih dari 1',
                ];
                return response()->json($message);
            } 
        }else{
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Anda tidak mempunyai akses Disapprove',
            ];
            return response()->json($message);
        }
        
    }

    public function store(Request $request)
    {
        $konek = self::konek();
        $tanggal = $request->tanggal_pembelian;
        
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

            $tahun_transaksi_new = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal)->year;
            $bulan_transaksi_new = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal)->month;

            $user = Auth()->user()->level;
            if($request->jenis_po != 'Stock'){
                $beli = Pembelian::on($konek)->whereMonth('tanggal_pembelian',$bulan_transaksi)->whereYear('tanggal_pembelian',$tahun_transaksi)->where('no_pembelian', 'like', '%POGA%')->where('status','OPEN')->get();

                $beli_new = Pembelian::on($konek)->whereMonth('tanggal_pembelian',$bulan_transaksi_new)->whereYear('tanggal_pembelian',$tahun_transaksi_new)->where('no_pembelian', 'like', '%POGA%')->where('status','OPEN')->get();

                if (count($beli) >= 1){
                    $message = [
                        'success' => false,
                        'title' => 'Gagal',
                        'message' => 'Masih ada pembelian GA yang OPEN.'
                    ];
                    return response()->json($message);
                }

                if (count($beli_new) >= 1){
                    $message = [
                        'success' => false,
                        'title' => 'Gagal',
                        'message' => 'Masih ada pembelian GA yang OPEN.'
                    ];
                    return response()->json($message);
                }
            }
            else{
                $beli2 = Pembelian::on($konek)->whereMonth('tanggal_pembelian',$bulan_transaksi)->whereYear('tanggal_pembelian',$tahun_transaksi)->where('no_pembelian', 'like', '%NPO%')->where('status','OPEN')->get();

                $beli2_new = Pembelian::on($konek)->whereMonth('tanggal_pembelian',$bulan_transaksi_new)->whereYear('tanggal_pembelian',$tahun_transaksi_new)->where('no_pembelian', 'like', '%NPO%')->where('status','OPEN')->get();

                if (count($beli2) >= 1){
                    $message = [
                        'success' => false,
                        'title' => 'Gagal',
                        'message' => 'Masih ada pembelian yang OPEN.'
                    ];
                    return response()->json($message);
                }

                if (count($beli2_new) >= 1){
                    $message = [
                        'success' => false,
                        'title' => 'Gagal',
                        'message' => 'Masih ada pembelian yang OPEN.'
                    ];
                    return response()->json($message);
                }
            }
        }else {
            $tgl = tb_akhir_bulan::on($konek)->where('status_periode','Open')->first();
            $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl->periode)->year;
            $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl->periode)->month;

            $tahun_transaksi_new = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal)->year;
            $bulan_transaksi_new = Carbon\Carbon::createFromFormat('Y-m-d',$tanggal)->month;
            
            $beli = Pembelian::on($konek)->whereMonth('tanggal_pembelian',$bulan_transaksi)->whereYear('tanggal_pembelian',$tahun_transaksi)->where('status','OPEN')->get();
            $user = Auth()->user()->level;
            if($request->jenis_po != 'Stock'){
                $beli = Pembelian::on($konek)->whereMonth('tanggal_pembelian',$bulan_transaksi)->whereYear('tanggal_pembelian',$tahun_transaksi)->where('no_pembelian', 'like', '%POGA%')->where('status','OPEN')->get();

                $beli_new = Pembelian::on($konek)->whereMonth('tanggal_pembelian',$bulan_transaksi_new)->whereYear('tanggal_pembelian',$tahun_transaksi_new)->where('no_pembelian', 'like', '%POGA%')->where('status','OPEN')->get();

                if (count($beli) >= 1){
                    $message = [
                        'success' => false,
                        'title' => 'Gagal',
                        'message' => 'Masih ada pembelian GA yang OPEN.'
                    ];
                    return response()->json($message);
                }

                if (count($beli_new) >= 1){
                    $message = [
                        'success' => false,
                        'title' => 'Gagal',
                        'message' => 'Masih ada pembelian GA yang OPEN.'
                    ];
                    return response()->json($message);
                }
            }
            else{
                $beli2 = Pembelian::on($konek)->whereMonth('tanggal_pembelian',$bulan_transaksi)->whereYear('tanggal_pembelian',$tahun_transaksi)->where('no_pembelian', 'like', '%NPO%')->where('status','OPEN')->get();

                $beli2_new = Pembelian::on($konek)->whereMonth('tanggal_pembelian',$bulan_transaksi_new)->whereYear('tanggal_pembelian',$tahun_transaksi_new)->where('no_pembelian', 'like', '%NPO%')->where('status','OPEN')->get();

                if (count($beli2) >= 1){
                    $message = [
                        'success' => false,
                        'title' => 'Gagal',
                        'message' => 'Masih ada pembelian yang OPEN.'
                    ];
                    return response()->json($message);
                }

                if (count($beli2_new) >= 1){
                    $message = [
                        'success' => false,
                        'title' => 'Gagal',
                        'message' => 'Masih ada pembelian yang OPEN.'
                    ];
                    return response()->json($message);
                }
            }
        }
        
        if (auth()->user()->level != 'ap'){
            if ($request->ppn == '0' || $request->ppn == null){
            
            }else {
                if ($request->ppn < 11){
                    $message = [
                            'success' => false,
                            'title' => 'Gagal',
                            'message' => 'Peraturan PPN terbaru tidak boleh di bawah 11%.'
                    ];
                    return response()->json($message);
                }
            }
        }else {
            
        }


        $pembelian = Pembelian::on($konek)->create($request->all());
        
        $getpembelian = Pembelian::on($konek)->where('no_request',$request->no_request)->orderBy('created_at','desc')->first();
        
        if($request->no_request != null){
            $requestproduk = Requestproduk::on($konek)->where('no_request',$request->no_request)->get();
            foreach($requestproduk as $row){

                // $cekstatus = Requestproduk::on($konek)->where('no_request',$request->no_request)->where('kode_produk',$row->kode_produk)->where('status_produk','OFF')->first();
                // $getqty = $cekstatus->qty - $cekstatus->qty_po;
                // $getsatuan = Produk::on($konek)->where('id',$row->kode_produk)->first();
                // if($cekstatus != null){
                //     $tabel_baru = [
                //         'no_pembelian'=>$getpembelian->no_pembelian,
                //         'kode_produk'=>$cekstatus->kode_produk,
                //         'kode_satuan'=>$getsatuan->kode_satuan,
                //         'harga'=>0,
                //         'total_transaksi'=>0,
                //         'qty'=>$getqty,
                //         'qty_received'=>0,
                //         'kode_company'=>auth()->user()->kode_company,
                //     ];
                //     PembelianDetail::on($konek)->create($tabel_baru);
                // }
                
                
                if ($row->status_produk == 'OFF'){
                    $getqty = $row->qty - $row->qty_po;
                    $getsatuan = Produk::on($konek)->where('id',$row->kode_produk)->first();
                    $tabel_baru = [
                        'no_pembelian'=>$getpembelian->no_pembelian,
                        'kode_produk'=>$row->kode_produk,
                        'kode_satuan'=>$getsatuan->kode_satuan,
                        'harga'=>0,
                        'total_transaksi'=>0,
                        'qty'=>$getqty,
                        'qty_received'=>0,
                        'kode_company'=>auth()->user()->kode_company,
                    ];
                    PembelianDetail::on($konek)->create($tabel_baru);
                }
            }

            $countdetail = PembelianDetail::on($konek)->where('no_pembelian', $getpembelian->no_pembelian)->get();
            $lenger = count($countdetail);
            $getpembelian->total_item = $lenger;
            $getpembelian->save();
        }


        $no = Pembelian::on($konek)->orderBy('created_at','desc')->first();
        $nama = auth()->user()->name;
        $tmp = ['nama' => $nama,'aksi' => 'Simpan No. Pembelian: '.$no->no_pembelian.'.','created_by'=>$nama,'updated_by'=>$nama];
        //dd($tmp);
        user_history::on($konek)->create($tmp);

        $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => 'Data telah di Disimpan.'
        ];
        return response()->json($message);
    }

    public function edit_pembelian()
    {
        $konek = self::konek();
        $no_pembelian = request()->id;
        $data = Pembelian::on($konek)->find($no_pembelian);
        $status = $data->status;
        $level = auth()->user()->level;

        if($status == 'OPEN' || $level == 'ap'){
            $output = array(
                'no_pembelian'=> $data->no_pembelian,
                'kode_vendor'=>$data->kode_vendor,
                'no_penawaran'=>$data->no_penawaran,
                'top'=>$data->top,
                'due_date'=>$data->due_date,
                'diskon_persen'=>$data->diskon_persen,
                'diskon_rp'=>$data->diskon_rp,
                'ppn'=>$data->ppn,
                'pbbkb'=>$data->pbbkb,
                'pbbkb_rp'=>$data->pbbkb_rp,
                'ongkos_angkut'=>$data->ongkos_angkut,
                'cost_center'=>$data->cost_center,
                'deskripsi'=>$data->deskripsi,
                'tanggal_pembelian'=> $data->tanggal_pembelian,
                'status'=> $data->status,
                'no_ap'=> $data->no_ap,
                'jenis_po'=> $data->jenis_po,
            );
            return response()->json($output);
        }
    }
    
    public function updateAjax(Request $request)
    {
        $konek = self::konek();
        $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
        $vendor = Vendor::find($request->kode_vendor);
        
        if (auth()->user()->level != 'ap'){
            if ($request->ppn == '0' || $request->ppn == null){
            
            }else {
                if ($request->ppn < 11){
                    $message = [
                            'success' => false,
                            'title' => 'Gagal',
                            'message' => 'Peraturan PPN terbaru tidak boleh di bawah 11%.'
                    ];
                    return response()->json($message);
                }
            }
        }else {
            
        }

        $pembeliandetail = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();

        $total_qty = 0;
        $total_harga = 0;
        $grand_total = 0;

        foreach ($pembeliandetail as $row){
            $total_qty += $row->qty;
            $subtotal = $row->harga * $row->qty;
            $total_harga += $subtotal;
        }

        $pembelian->grand_total = $total_harga;
        $pembelian->save();


        $ppn = $request->ppn;
        $pbbkb = $request->pbbkb;
        $pbbkb_rp = $request->pbbkb_rp;
        $diskonrp = $request->diskon_rp;
        $diskonpersen = $request->diskon_persen;
        $ongkos_angkut = $request->ongkos_angkut;

        if($ppn == 0 && $diskonrp == 0 && $diskonpersen == 0 && $pbbkb == 0 && $pbbkb_rp == 0){
            $tanggal = $request->tanggal_pembelian;

            $pembeliandetail = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();

            $total_qty = 0;
            $total_harga = 0;
            $grand_total = 0;

            foreach ($pembeliandetail as $row){
                $total_qty += $row->qty;
                $subtotal = $row->harga * $row->qty;
                $total_harga += $subtotal;
            }     

            $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
            $pembelian->grand_total =  $total_harga + $ongkos_angkut; 
            $pembelian->save(); 

            $Pembelian = Pembelian::on($konek)->find($request->no_pembelian)->update($request->all());

            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Edit No. Pembelian: '.$request->no_pembelian.'.','created_by'=>$nama,'updated_by'=>$nama];
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
            $pembeliandetail = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();

            $total_qty = 0;
            $total_harga = 0;
            $grand_total = 0;

            $diskonpersen_final = ($diskonpersen)/100;
            $diskonrp_final = $diskonrp;

            if($diskonpersen == 0 && $diskonrp == 0 && $pbbkb == 0 && $pbbkb_rp == 0){
                $ppn_final = ($ppn)/100;

                foreach ($pembeliandetail as $row){
                    $total_qty += $row->qty;
                    $subtotal = $row->harga * $row->qty;
                    $total_harga += $subtotal;
                }

                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                if($total_harga == 0){
                    $pembelian->grand_total = $total_harga;
                }else{
                    $pembelian->grand_total = ($total_harga + ($total_harga * $ppn_final)) + $ongkos_angkut;
                }


                $pembelian->save(); 

                $Pembelian = Pembelian::on($konek)->find($request->no_pembelian)->update($request->all());

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Update No. Pembelian: '.$request->no_pembelian.'.','created_by'=>$nama,'updated_by'=>$nama];
                                //dd($tmp);
                user_history::on($konek)->create($tmp);

                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data telah di Update'
                ];
                return response()->json($message);
            }

            else if($diskonpersen > 0 && $ppn == 0 && $diskonrp == 0 && $pbbkb == 0 && $pbbkb_rp == 0){
                $diskonpersen_final = ($diskonpersen)/100;

                foreach ($pembeliandetail as $row){
                    $total_qty += $row->qty;
                    $subtotal = $row->harga * $row->qty;
                    $total_harga += $subtotal;
                }

                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                if($total_harga == 0){
                    $pembelian->grand_total = $total_harga;
                }else{
                    $pembelian->grand_total = ($total_harga - ($total_harga * $diskonpersen_final)) + $ongkos_angkut;
                }


                $pembelian->save(); 

                $Pembelian = Pembelian::on($konek)->find($request->no_pembelian)->update($request->all());

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Update No. Pembelian: '.$request->no_pembelian.'.','created_by'=>$nama,'updated_by'=>$nama];
                                //dd($tmp);
                user_history::on($konek)->create($tmp);

                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data telah di Update'
                ];
                return response()->json($message);
            }

            else if($diskonrp > 0 && $ppn == 0 && $diskonpersen == 0 && $pbbkb == 0 && $pbbkb_rp == 0){  
                $diskonrp_final = $diskonrp;

                foreach ($pembeliandetail as $row){
                    $total_qty += $row->qty;
                    $subtotal = $row->harga * $row->qty;
                    $total_harga += $subtotal;
                }

                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                if($total_harga == 0){
                    $pembelian->grand_total = $total_harga;
                }else{
                    $pembelian->grand_total = ($total_harga - $diskonrp_final) + $ongkos_angkut;
                }


                $pembelian->save(); 

                $Pembelian = Pembelian::on($konek)->find($request->no_pembelian)->update($request->all());

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Update No. Pembelian: '.$request->no_pembelian.'.','created_by'=>$nama,'updated_by'=>$nama];
                                //dd($tmp);
                user_history::on($konek)->create($tmp);

                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data telah di Update'
                ];
                return response()->json($message);
            }

            else if($pbbkb > 0 && $ppn == 0 && $diskonpersen == 0 && $diskonrp == 0 && $pbbkb_rp == 0){
                $pbbkb_final = ($pbbkb)/100;

                foreach ($pembeliandetail as $row){
                    $total_qty += $row->qty;
                    $subtotal = $row->harga * $row->qty;
                    $total_harga += $subtotal;
                }

                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                $pembelian->grand_total = ($total_harga + ($total_harga * $pbbkb_final)) + $ongkos_angkut;
                $pembelian->save(); 

                $Pembelian = Pembelian::on($konek)->find($request->no_pembelian)->update($request->all());

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Update No. Pembelian: '.$request->no_pembelian.'.','created_by'=>$nama,'updated_by'=>$nama];
                                //dd($tmp);
                user_history::on($konek)->create($tmp);

                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data telah di Update'
                ];
                return response()->json($message);
            }

            else if($pbbkb_rp > 0 && $ppn == 0 && $diskonpersen == 0 && $diskonrp == 0 && $pbbkb == 0){
                $pbbkb_rp_final = $pbbkb_rp;

                foreach ($pembeliandetail as $row){
                    $total_qty += $row->qty;
                    $subtotal = $row->harga * $row->qty;
                    $total_harga += $subtotal;
                }

                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                $pembelian->grand_total = $total_harga + $pbbkb_rp_final + $ongkos_angkut;
                $pembelian->save(); 

                $Pembelian = Pembelian::on($konek)->find($request->no_pembelian)->update($request->all());

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Update No. Pembelian: '.$request->no_pembelian.'.','created_by'=>$nama,'updated_by'=>$nama];
                                //dd($tmp);
                user_history::on($konek)->create($tmp);

                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data telah di Update'
                ];
                return response()->json($message);
            }

            else if($diskonrp > 0 && $ppn > 0 && $diskonpersen == 0 && $pbbkb == 0 && $pbbkb_rp == 0){  
                $diskonrp_final = $diskonrp;
                $ppn_final = ($ppn)/100;

                foreach ($pembeliandetail as $row){
                    $total_qty += $row->qty;
                    $subtotal = $row->harga * $row->qty;
                    $total_harga += $subtotal;
                }

                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                if($total_harga == 0){
                    $pembelian->grand_total = $total_harga;
                }else{
                    $pembelian->grand_total = (($total_harga - $diskonrp_final) + (($total_harga - $diskonrp_final) * $ppn_final)) + $ongkos_angkut;
                }


                $pembelian->save(); 

                $Pembelian = Pembelian::on($konek)->find($request->no_pembelian)->update($request->all());

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Update No. Pembelian: '.$request->no_pembelian.'.','created_by'=>$nama,'updated_by'=>$nama];
                                //dd($tmp);
                user_history::on($konek)->create($tmp);

                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data telah di Update'
                ];
                return response()->json($message);
            }
            else if($diskonpersen > 0 && $ppn > 0 && $diskonrp == 0 && $pbbkb == 0 && $pbbkb_rp == 0){  
                $diskonpersen_final = ($diskonpersen)/100;
                $ppn_final = ($ppn)/100;

                foreach ($pembeliandetail as $row){
                    $total_qty += $row->qty;
                    $subtotal = $row->harga * $row->qty;
                    $total_harga += $subtotal;
                }

                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                if($total_harga == 0){
                    $pembelian->grand_total = $total_harga;
                }else{
                    $totaldiskon = $total_harga - ($total_harga * $diskonpersen_final);
                    $pembelian->grand_total = $totaldiskon + ($totaldiskon * $ppn_final) + $ongkos_angkut;
                }


                $pembelian->save(); 

                $Pembelian = Pembelian::on($konek)->find($request->no_pembelian)->update($request->all());

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Update No. Pembelian: '.$request->no_pembelian.'.','created_by'=>$nama,'updated_by'=>$nama];
                                    //dd($tmp);
                user_history::on($konek)->create($tmp);

                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data telah di Update'
                ];
                return response()->json($message);
            }
            else if($pbbkb > 0 && $ppn > 0 && $diskonrp == 0 && $diskonpersen == 0 && $pbbkb_rp == 0){  
                $ppn = ($ppn)/100;
                $pbbkb_final = ($pbbkb)/100;

                foreach ($pembeliandetail as $row){
                    $total_qty += $row->qty;
                    $subtotal = $row->harga * $row->qty;
                    $total_harga += $subtotal;
                }

                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                $totalhargapbbkb = $total_harga + ($total_harga * $pbbkb_final) + $ongkos_angkut;
                $pembelian->grand_total = $totalhargapbbkb + ($total_harga * $ppn);
                $pembelian->save(); 

                $Pembelian = Pembelian::on($konek)->find($request->no_pembelian)->update($request->all());

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Update No. Pembelian: '.$request->no_pembelian.'.','created_by'=>$nama,'updated_by'=>$nama];
                                    //dd($tmp);
                user_history::on($konek)->create($tmp);

                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data telah di Update'
                ];
                return response()->json($message);
            }

            else if($pbbkb_rp > 0 && $ppn > 0 && $diskonrp == 0 && $diskonpersen == 0 && $pbbkb == 0){  
                $ppn = ($ppn)/100;
                $pbbkb_rp_final = $pbbkb_rp;

                foreach ($pembeliandetail as $row){
                    $total_qty += $row->qty;
                    $subtotal = $row->harga * $row->qty;
                    $total_harga += $subtotal;
                }

                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                $totalhargapbbkb_rp = $total_harga + $pbbkb_rp_final + $ongkos_angkut;
                $pembelian->grand_total =  $totalhargapbbkb_rp + ($total_harga * $ppn); 
                $pembelian->save(); 

                $Pembelian = Pembelian::on($konek)->find($request->no_pembelian)->update($request->all());

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Update No. Pembelian: '.$request->no_pembelian.'.','created_by'=>$nama,'updated_by'=>$nama];
                                    //dd($tmp);
                user_history::on($konek)->create($tmp);

                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data telah di Update'
                ];
                return response()->json($message);
            }

            else if($diskonrp > 0 && $pbbkb > 0 && $diskonpersen == 0 && $ppn == 0){  
                $diskonrp_final = $diskonrp;
                $pbbkb_final = ($pbbkb)/100;

                foreach ($pembeliandetail as $row){
                    $total_qty += $row->qty;
                    $subtotal = $row->harga * $row->qty;
                    $total_harga += $subtotal;
                }

                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                if($total_harga == 0){
                    $pembelian->grand_total = $total_harga;
                }else{
                    $pembelian->grand_total = (($total_harga - $diskonrp_final) + (($total_harga - $diskonrp_final) * $pbbkb_final)) + $ongkos_angkut;
                }


                $pembelian->save(); 

                $Pembelian = Pembelian::on($konek)->find($request->no_pembelian)->update($request->all());

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Update No. Pembelian: '.$request->no_pembelian.'.','created_by'=>$nama,'updated_by'=>$nama];
                                //dd($tmp);
                user_history::on($konek)->create($tmp);

                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data telah di Update'
                ];
                return response()->json($message);
            }

            else if($diskonrp > 0 && $pbbkb_rp > 0 && $diskonpersen == 0 && $ppn == 0 && $pbbkb == 0){  
                $diskonrp_final = $diskonrp;
                $pbbkb_rp_final = $pbbkb_rp;

                foreach ($pembeliandetail as $row){
                    $total_qty += $row->qty;
                    $subtotal = $row->harga * $row->qty;
                    $total_harga += $subtotal;
                }

                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                if($total_harga == 0){
                    $pembelian->grand_total = $total_harga;
                }else{
                    $pembelian->grand_total = (($total_harga - $diskonrp_final) + $pbbkb_rp_final) + $ongkos_angkut;
                }


                $pembelian->save(); 

                $Pembelian = Pembelian::on($konek)->find($request->no_pembelian)->update($request->all());

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Update No. Pembelian: '.$request->no_pembelian.'.','created_by'=>$nama,'updated_by'=>$nama];
                                //dd($tmp);
                user_history::on($konek)->create($tmp);

                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data telah di Update'
                ];
                return response()->json($message);
            }

            else if($diskonpersen > 0 && $pbbkb > 0 && $diskonrp == 0 && $ppn == 0 && $pbbkb_rp == 0){  
                $diskonpersen_final = ($diskonpersen)/100;
                $pbbkb_final = ($pbbkb)/100;

                foreach ($pembeliandetail as $row){
                    $total_qty += $row->qty;
                    $subtotal = $row->harga * $row->qty;
                    $total_harga += $subtotal;
                }

                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                if($total_harga == 0){
                    $pembelian->grand_total = $total_harga;
                }else{
                    $pembelian->grand_total = (($total_harga - ($total_harga * $diskonpersen_final)) + (($total_harga - ($total_harga * $diskonpersen_final)) * $pbbkb_final)) + $ongkos_angkut;
                }


                $pembelian->save(); 

                $Pembelian = Pembelian::on($konek)->find($request->no_pembelian)->update($request->all());

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Update No. Pembelian: '.$request->no_pembelian.'.','created_by'=>$nama,'updated_by'=>$nama];
                                //dd($tmp);
                user_history::on($konek)->create($tmp);

                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data telah di Update'
                ];
                return response()->json($message);
            }

            else if($diskonpersen > 0 && $pbbkb_rp > 0 && $diskonrp == 0 && $ppn == 0 && $pbbkb == 0){  
                $diskonpersen_final = ($diskonpersen)/100;
                $pbbkb_rp_final = $pbbkb_rp;

                foreach ($pembeliandetail as $row){
                    $total_qty += $row->qty;
                    $subtotal = $row->harga * $row->qty;
                    $total_harga += $subtotal;
                }

                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                if($total_harga == 0){
                    $pembelian->grand_total = $total_harga;
                }else{
                    $pembelian->grand_total = (($total_harga - ($total_harga * $diskonpersen_final)) + $pbbkb_rp_final + $ongkos_angkut);
                }


                $pembelian->save(); 

                $Pembelian = Pembelian::on($konek)->find($request->no_pembelian)->update($request->all());

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Update No. Pembelian: '.$request->no_pembelian.'.','created_by'=>$nama,'updated_by'=>$nama];
                                //dd($tmp);
                user_history::on($konek)->create($tmp);

                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data telah di Update'
                ];
                return response()->json($message);
            }

            else if($diskonpersen > 0 && $ppn > 0 && $pbbkb > 0 && $diskonrp == 0 && $pbbkb_rp == 0){  
                $diskonpersen_final = ($diskonpersen)/100;
                $ppn_final = ($ppn)/100;
                $pbbkb_final = ($pbbkb)/100;

                foreach ($pembeliandetail as $row){
                    $total_qty += $row->qty;
                    $subtotal = $row->harga * $row->qty;
                    $total_harga += $subtotal;
                }       

                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                $totaldiskon = $total_harga - ($total_harga * $diskonpersen_final);
                $totalhargapbbkb = ($totaldiskon - ($totaldiskon * $pbbkb_final)) + $ongkos_angkut;
                $pembelian->grand_total =  $totalhargapbbkb + ($totaldiskon * $ppn_final);
                $pembelian->save(); 

                $Pembelian = Pembelian::on($konek)->find($request->no_pembelian)->update($request->all());

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Update No. Pembelian: '.$request->no_pembelian.'.','created_by'=>$nama,'updated_by'=>$nama];
                                    //dd($tmp);
                user_history::on($konek)->create($tmp);

                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data telah di Update'
                ];
                return response()->json($message);
            }

            else if($diskonpersen > 0 && $ppn > 0 && $pbbkb_rp > 0 && $diskonrp == 0 && $pbbkb == 0){  
                $diskonpersen_final = ($diskonpersen)/100;
                $ppn_final = ($ppn)/100;
                $pbbkb_rp_final = $pbbkb_rp;

                foreach ($pembeliandetail as $row){
                    $total_qty += $row->qty;
                    $subtotal = $row->harga * $row->qty;
                    $total_harga += $subtotal;
                }       

                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                $totaldiskon = $total_harga - ($total_harga * $diskonpersen_final);
                $totalhargapbbkb_rp = $totaldiskon + $pbbkb_rp_final + $ongkos_angkut;
                $pembelian->grand_total =  $totalhargapbbkb_rp + ($totaldiskon * $ppn_final); 
                $pembelian->save(); 

                $Pembelian = Pembelian::on($konek)->find($request->no_pembelian)->update($request->all());

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Update No. Pembelian: '.$request->no_pembelian.'.','created_by'=>$nama,'updated_by'=>$nama];
                                    //dd($tmp);
                user_history::on($konek)->create($tmp);

                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data telah di Update'
                ];
                return response()->json($message);
            }

            else if($diskonrp > 0 && $ppn > 0 && $pbbkb > 0 && $diskonpersen == 0 && $pbbkb_rp == 0){  
                $diskonrp_final = $diskonrp;
                $ppn_final = ($ppn)/100;
                $pbbkb_final = ($pbbkb)/100;

                foreach ($pembeliandetail as $row){
                    $total_qty += $row->qty;
                    $subtotal = $row->harga * $row->qty;
                    $total_harga += $subtotal;
                }     

                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                $totaldiskon = $total_harga - $diskonrp_final;
                $totalhargapbbkb = $totaldiskon + ($totaldiskon * $pbbkb_final) + $ongkos_angkut;
                $pembelian->grand_total =  $totalhargapbbkb + ($totaldiskon * $ppn_final); 
                $pembelian->save(); 

                $Pembelian = Pembelian::on($konek)->find($request->no_pembelian)->update($request->all());

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Update No. Pembelian: '.$request->no_pembelian.'.','created_by'=>$nama,'updated_by'=>$nama];
                                    //dd($tmp);
                user_history::on($konek)->create($tmp);

                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data telah di Update'
                ];
                return response()->json($message);
            }

            else if($diskonrp > 0 && $ppn > 0 && $pbbkb_rp > 0 && $diskonpersen == 0 && $pbbkb == 0){  
                $diskonrp_final = $diskonrp;
                $ppn_final = ($ppn)/100;
                $pbbkb_rp_final = $pbbkb_rp;

                foreach ($pembeliandetail as $row){
                    $total_qty += $row->qty;
                    $subtotal = $row->harga * $row->qty;
                    $total_harga += $subtotal;
                }     

                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                $totaldiskon = $total_harga - $diskonrp_final;
                $totalhargapbbkb = $totaldiskon + $pbbkb_rp_final + $ongkos_angkut;
                $pembelian->grand_total =  $totalhargapbbkb + ($totaldiskon * $ppn_final);
                $pembelian->save(); 

                $Pembelian = Pembelian::on($konek)->find($request->no_pembelian)->update($request->all());

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Update No. Pembelian: '.$request->no_pembelian.'.','created_by'=>$nama,'updated_by'=>$nama];
                                    //dd($tmp);
                user_history::on($konek)->create($tmp);

                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data telah di Update'
                ];
                return response()->json($message);
            }
        }
    }

    public function hapus_pembelian()
    {
        $konek = self::konek();
        $level = auth()->user()->level;

        $no_pembelian = request()->id;
        $data = Pembelian::on($konek)->find($no_pembelian);
        $tanggal = $data->tanggal_pembelian;

        $cek_detail = PembelianDetail::on($konek)->where('no_pembelian',$no_pembelian)->first();
        if($cek_detail == null){
            $status = $data->status;

            if($status == 'OPEN'){
                $data->delete();

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Hapus No. Pembelian: '.$data->no_pembelian.'.','created_by'=>$nama,'updated_by'=>$nama];
                        //dd($tmp);
                user_history::on($konek)->create($tmp);

                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data ['.$data->no_pembelian.'] telah dihapus.'
                ];
                return response()->json($message);
            }
            else{
                alert()->success('Input Data Excel','BERHASIL!')->persistent('Close');
            }
        }
    }
    
    public function void_pembelian()
    {
        $konek = self::konek();
        $level = auth()->user()->level;

        $no_pembelian = request()->id;
        $data = Pembelian::on($konek)->find($no_pembelian);
        $tanggal = $data->tanggal_pembelian;
        if ($data->status == 'OPEN') {
            $data->status = 'VOID';
            $data->save();
            
            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Void No. Pembelian: '.$data->no_pembelian.'.','created_by'=>$nama,'updated_by'=>$nama];
            user_history::on($konek)->create($tmp);

            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data ['.$data->no_pembelian.'] telah di void.'
            ];
            return response()->json($message);
        }else if ($data->status == 'POSTED'){
            $cekterima = Penerimaan::on($konek)->where('no_pembelian', $no_pembelian)->first();
            if ($cekterima != null) {
                $message = [
                    'success' => false,
                    'title' => 'Update',
                    'message' => 'No ['.$data->no_pembelian.'] sudah ada di penerimaan.'
                ];
                return response()->json($message);
            }else {
                $data->status = 'VOID';
                $data->save();
                
                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Void No. Pembelian: '.$data->no_pembelian.'.','created_by'=>$nama,'updated_by'=>$nama];
                user_history::on($konek)->create($tmp);
                
                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data ['.$data->no_pembelian.'] telah di void.'
                ];
                return response()->json($message);
            }
        }
    }
}
