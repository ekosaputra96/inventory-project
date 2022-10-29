<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Penerimaan;
use App\Models\PenerimaanDetail;
use App\Models\RequestpembelianDetail;
use App\Models\Requestpembelian;
use App\Models\Transfer;
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
use App\Models\Memo;
use App\Models\MemoDetail;
use App\Models\Ukuran;
use App\Models\user_history;
use App\Models\MasterLokasi;
use App\Models\Company;
use App\Models\TaxSetup;
use App\Models\Approve_po;
use App\Models\SetupAkses;
use App\Models\Opname;
use App\Models\Costcenter;
use PDF;
use Excel;
use DB;
use Carbon;


class MemoController extends Controller
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
        $create_url = route('memo.create');
        $Vendor= Vendor::pluck('nama_vendor','id');
        $no_pembelian= Pembelian::on($konek)->where('status','POSTED')->orwhere('status','CLOSED')->pluck('no_pembelian','no_pembelian');
        $Company= Company::pluck('nama_company','kode_company');
        $Lokasi= MasterLokasi::pluck('nama_lokasi','kode_lokasi');

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
            return view('admin.memo.index3',compact('Costcenter','create_url','Vendor','Company','no_pembelian','period', 'nama_lokasi','nama_company','Lokasi'));
        }else{
            return view('admin.memo.index2',compact('create_url','Vendor','Company','no_pembelian','period', 'nama_lokasi','nama_company','Lokasi'));
        } 
    }

    public function anyData()
    {   
        $konek = self::konek();
        $lokasi = Auth()->user()->kode_lokasi;
        if($lokasi == 'HO'){
            return Datatables::of(Memo::on($konek))->make(true);
        }else{
            return Datatables::of(Memo::on($konek)->where('kode_lokasi',$lokasi))->make(true);
        }
        
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
    
    public function closing()
    {
        $konek = self::konek();
        $header = Memo::on($konek)->find(request()->id);
        $header->status =  'CLOSED';
        $header->save();
        
        $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => 'Memo '. $header->no_memo. 'telah di close.'
        ];
        return response()->json($message);
        
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
            'tgl_po'=>$pembelian->tgl_memo,
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

    public function exportPDF(){
        $konek = self::konek();
        $request = $_GET['no_memo'];

        $memo = Memo::on($konek)->where('no_memo',$request)->first();
        $user = $memo->created_by;
        
        $dt = Carbon\Carbon::now();
        $date_now = Carbon\Carbon::parse($dt)->format('d/m/Y H:i:s');

        $memodetail = MemoDetail::on($konek)->where('no_memo',$request)->get();
        $leng = count($memodetail);
        
        $total_qty = 0;
        $ttd = $user;
        
        $company = auth()->user()->kode_company;
        
        $pdf = PDF::loadView('/admin/memo/pdf', compact('memodetail','request', 'memo', 'ttd','total_qty','date_now','konek'));
        $pdf->setPaper([0, 0, 684, 792], 'potrait');
            
        $nama = auth()->user()->name;
        $tmp = ['nama' => $nama,'aksi' => 'Print No. Memo: '.$request.'.','created_by'=>$nama,'updated_by'=>$nama];
        user_history::on($konek)->create($tmp);
                
        return $pdf->stream('Laporan Memo '.$request.'.pdf');
    }

    
    public function printpreview(){
        $konek = self::konek();
        $request = $_GET['no_pembelian'];

        $pembelian = Pembelian::on($konek)->where('no_pembelian',$request)->first();
        $tipe = $pembelian->jenis_po;
        $user = $pembelian->created_by;

        $catatan_po = Catatanpo::on($konek)->get();

        $tgl = $pembelian->tgl_memo;
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
        $memo = Memo::on($konek)->find($pembelian);
        $tanggal = $memo->tgl_memo;
        $no_memo = $memo->no_memo;
        $jenis_po = $memo->jenis_po;

        $data = Pembelian::on($konek)->find($no_memo);

        $memodetail = MemoDetail::on($konek)->with('produk')->where('no_memo', $memo->no_memo)
        ->orderBy('created_at','desc')->get();

        $list_url= route('memo.index');
        $Produk = Produk::on($konek)->pluck('nama_produk','id');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        return view('admin.memodetail.index', compact('memo','memodetail','list_url','Produk','period','nama_lokasi','nama_company'));

    }

    public function Showdetail()
    {
        $konek = self::konek();
        $memodetail= MemoDetail::on($konek)->with('produk')->where('no_memo',request()->id)
        ->orderBy('created_at', 'desc')->get();

        $memo= Memo::on($konek)->where('no_memo',request()->id)->first();
        
        $output = array();

        if($memodetail){
                foreach($memodetail as $row)
                {
                    $output[] = array(
                        'no_wo'=>$row->memo,
                        'produk'=>$row->produk->nama_produk,
                        'qty'=>$row->qty,
                        'qty_to'=>$row->qty_to,
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
        $memo = Memo::on($konek)->find(request()->id);
        $memo->status = 'POSTED';
        $memo->save();

        $nama = auth()->user()->name;
        $tmp = ['nama' => $nama,'aksi' => 'Post No. NPPB: '.$memo->no_memo.'.','created_by'=>$nama,'updated_by'=>$nama];
        user_history::on($konek)->create($tmp);
        
        $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => $memo->no_memo.' berhasil di POST',
        ];
        return response()->json($message);
        
    }

    public function Unpost()
    {
        $konek = self::konek();
        $memo = Memo::on($konek)->find(request()->id);
        $cekreq = RequestpembelianDetail::on($konek)->where('no_memo',$memo->no_memo)->first();
        $cektrf = Transfer::on($konek)->where('no_memo',$memo->no_memo)->where('status','<>','OPEN')->first();
        if($cekreq != null){
            $message = [
                'success' => false,
                'title' => 'Update',
                'message' => 'No ['.$memo->no_memo.'] sudah ada ditarik di Request Pembelian dengan nomor '. $cekreq->no_request
            ];
            return response()->json($message);
        }else if ($cektrf != null){
            $message = [
                'success' => false,
                'title' => 'Update',
                'message' => 'No ['.$memo->no_memo.'] sudah ada ditarik di Transfer Out dengan nomor.'. $cektrf->no_transfer
            ];
            return response()->json($message);
        }else{
            $memo->status = 'OPEN';
            $memo->save();
        } 
        
        $nama = auth()->user()->name;
        $tmp = ['nama' => $nama,'aksi' => 'Unpost No. NPPB: '.$memo->no_memo.'.','created_by'=>$nama,'updated_by'=>$nama];
        user_history::on($konek)->create($tmp);

        $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => $memo->no_memo.' berhasil di UNPOST',
        ];
        return response()->json($message);
    }

    public function store(Request $request)
    {
        $konek = self::konek();
        $tanggal = $request->tgl_memo;
        $pembelian = Memo::on($konek)->create($request->all());

        $no = Memo::on($konek)->orderBy('created_at','desc')->first();
        $nama = auth()->user()->name;
        $tmp = ['nama' => $nama,'aksi' => 'Simpan No. Memo: '.$no->no_memo.'.','created_by'=>$nama,'updated_by'=>$nama];
        //dd($tmp);
        user_history::on($konek)->create($tmp);

        $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => 'Data telah di Disimpan.'
        ];
        return response()->json($message);
    }

    public function edit_memo()
    {
        $konek = self::konek();
        $no_memo = request()->id;
        $data = Memo::on($konek)->find($no_memo);
        $status = $data->status;
        $level = auth()->user()->level;
        
        if($status != 'OPEN'){
            $message = [
                'success' => false,
                'title' => 'Update',
                'message' => 'No ['.$data->no_memo.'] tidak dapat di edit karena sudah POSTED/REQUESTED/CLOSED.'
            ];
            return response()->json($message);
        }
        $output = array(
            'no_memo'=> $data->no_memo,
            'tgl_memo'=>$data->tgl_memo,
            'keterangan'=>$data->keterangan,
        );
        return response()->json($output);

    }
    
    public function updateAjax(Request $request)
    {
        $konek = self::konek();
        $memo = Memo::on($konek)->find($request->no_memo);

        $memodetail = MemoDetail::on($konek)->where('no_memo', $request->no_memo)->get();
        $tanggal = $request->tgl_memo;

        $Memo = Memo::on($konek)->find($request->no_memo)->update($request->all());

        $nama = auth()->user()->name;
        $tmp = ['nama' => $nama,'aksi' => 'Edit No. Memo: '.$request->no_pembelian.'.','created_by'=>$nama,'updated_by'=>$nama];
                //dd($tmp);
        user_history::on($konek)->create($tmp);

        $message = [
            'success' => true,
            'title' => 'Update',
            'message' => 'Data telah di Update.'
        ];
        return response()->json($message);

    }

    public function hapus_memo()
    {
        $konek = self::konek();
        $level = auth()->user()->level;

        $no_memo = request()->id;
        $data = Memo::on($konek)->find($no_memo);
        $tanggal = $data->tgl_memo;

        $cek_detail = MemoDetail::on($konek)->where('no_memo',$no_memo)->first();
        if($cek_detail == null){
            $status = $data->status;

            if($status == 'OPEN'){
                $data->delete();

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Hapus No. Memo: '.$data->no_memo.'.','created_by'=>$nama,'updated_by'=>$nama];
                        //dd($tmp);
                user_history::on($konek)->create($tmp);

                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data ['.$data->no_memo.'] telah dihapus.'
                ];
                return response()->json($message);
            }
        }
    }
    
    public function void_memo()
    {
        $konek = self::konek();
        $level = auth()->user()->level;

        $no_pembelian = request()->id;
        $data = Memo::on($konek)->find($no_pembelian);
        $tanggal = $data->tgl_memo;
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
