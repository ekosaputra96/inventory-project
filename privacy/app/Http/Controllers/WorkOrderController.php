<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Penerimaan;
use App\Models\PenerimaanDetail;
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
use App\Models\Workorder;
use App\Models\WorkorderDetail;
use App\Models\Alat;
use DateTime;
use PDF;
use Excel;
use DB;
use Carbon;


class WorkOrderController extends Controller
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
        $create_url = route('workorder.create');
        $Vendor= Vendor::pluck('nama_vendor','id');
        $no_pembelian= Pembelian::on($konek)->where('status','POSTED')->orwhere('status','CLOSED')->pluck('no_pembelian','no_pembelian');
        $Company= Company::pluck('nama_company','kode_company');
        $Lokasi= MasterLokasi::pluck('nama_lokasi','kode_lokasi');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;
        
        $Costcenter = Costcenter::pluck('desc','cost_center');
        $Alat = Alat::on($konek)->pluck('no_asset_alat','no_asset_alat');
        
        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        $level = auth()->user()->level;
        
        return view('admin.workorder.index3',compact('Costcenter','create_url','Vendor','Company','no_pembelian','period', 'nama_lokasi','nama_company','Lokasi','Alat'));
        
    }

    public function anyData()
    {   
        $konek = self::konek();
        $lokasi = auth()->user()->kode_lokasi;
        if($lokasi == 'HO'){
            return Datatables::of(DB::connection($konek)->table('workorder')->orderBy('created_at','desc')->get())->make(true);
        }else{
            return Datatables::of(DB::connection($konek)->table('workorder')->orderBy('created_at','desc')->where('kode_lokasi', $lokasi)->get())->make(true);
        }
        
    }
    
    public function hitungdate()
    {
        $dt1 = new DateTime(request()->tglin);
        $dt2 = new DateTime(request()->tglfinish);
        $hasil = $dt1->diff($dt2)->format('%d days, %h hours, %i minutes, %s seconds');
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
    
    public function historia()
    {
        $konek = self::konek();
        $name = auth()->user()->name;
        $post = user_history::on($konek)->where('aksi', 'like', '%'.request()->id.'%')->where('aksi','like','Post No. WO%')->orderBy('created_at','desc')->first();
        if ($post != null) {
            $nama1 = $post->nama;
        }else {
            $nama1 = 'None';
        }

        $unpost = user_history::on($konek)->where('aksi', 'like', '%'.request()->id.'%')->where('aksi','like','Unpost No. WO%')->orderBy('created_at','desc')->first();
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

        $work = Workorder::on($konek)->where('no_wo',$request)->first();
        $user = $work->created_by;

        $catatan_po = Catatanpo::on($konek)->get();

        $tgl = $work->date_in;
        $date=date_create($tgl);

        $tgl2 = $work->date_finish;
        $date2=date_create($tgl2);
        
        $dt = Carbon\Carbon::now();
        $date_now = Carbon\Carbon::parse($dt)->format('d/m/Y H:i:s');

        $workdetail = WorkorderDetail::on($konek)->where('no_wo',$request)->get();
        $leng = count($workdetail);
        
        $total_qty = 0;
        $ttd = $user;
        
        $company = auth()->user()->kode_company;
        
        $pdf = PDF::loadView('/admin/workorder/pdf', compact('workdetail','request', 'work','catatan_po','date','date2', 'ttd','total_qty','date_now','konek'));
        $pdf->setPaper([0, 0, 684, 792], 'potrait');
            
        $nama = auth()->user()->name;
        $tmp = ['nama' => $nama,'aksi' => 'Print No. WO: '.$request.'.','created_by'=>$nama,'updated_by'=>$nama];
        user_history::on($konek)->create($tmp);
                
        return $pdf->stream('Laporan WO '.$request.'.pdf');
    }

    public function detail($pembelian)
    {
        $konek = self::konek();
        $work = Workorder::on($konek)->find($pembelian);

        $workorderdetail = WorkorderDetail::on($konek)->where('no_wo', $work->no_wo)
            ->orderBy('created_at','desc')->get();

        $list_url= route('workorder.index');
        $Produk = Produk::on($konek)->pluck('nama_produk','id');
        $Nonstock = Nonstock::pluck('nama_item','id');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        return view('admin.workorderdetail.index', compact('work','workorderdetail','list_url','Produk','period','nama_lokasi','nama_company','Nonstock'));
    }

    public function Showdetail()
    {
        $konek = self::konek();
        $workorderdetail= WorkorderDetail::on($konek)->with('produk')->where('no_wo',request()->id)
        ->orderBy('created_at', 'desc')->get();

        $workorder= Workorder::on($konek)->where('no_wo',request()->id)->first();
        $output = array();

            foreach($workorderdetail as $row)
            {
                if($row->kode_produk != 0){
                    $produk = $row->produk->nama_produk;
                    $partnumber = $row->partnumber;
                }else{
                    $produk = $row->nama_produk;
                    $partnumber = "-";
                }
                
                $output[] = array(
                    'no_wo'=>$row->no_wo,
                    'produk'=>$produk,
                    'type'=>$row->type,
                    'partnumber'=>$partnumber,
                    'qty'=>$row->qty,
                    'qty_pakai'=>$row->qty_pakai,
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

        if($tabel != null) {
            $stat = $tabel->status_periode;
            $re_stat = $tabel->reopen_status;
            if($stat =='Open' || $re_stat == 'true') {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
    // public function Close()
    // {
    //     $konek = self::konek();
    //     $work = Workorder::on($konek)->find(request()->id);
    //     $no_wo = $work->no_wo;
    //     $cek_status = $work->status;
    //     if($cek_status == 'POSTED'){
    //         $work->status = 'CLOSED';
    //         $work->save();
            
    //         $nama = auth()->user()->name;
    //         $tmp = ['nama' => $nama,'aksi' => 'Close No. WO: '.$no_wo.'.','created_by'=>$nama,'updated_by'=>$nama];
    //         user_history::on($konek)->create($tmp);
            
    //         $message = [
    //             'success' => true,
    //             'title' => 'Simpan',
    //             'message' => 'CLOSED!! ',
    //         ];
    //         return response()->json($message);
    //     }else{
    //         $message = [
    //             'success' => false,
    //             'title' => 'Simpan',
    //             'message' => 'CLOSE No. WO: '.$work->no_wo.' sudah dilakukan! Pastikan Anda tidak membuka menu WORK ORDER lebih dari 1',
    //         ];
    //         return response()->json($message);
    //     } 
    // }

    public function Post()
    {
        $konek = self::konek();
        $work = Workorder::on($konek)->find(request()->id);
        $no_wo = $work->no_wo;
        $cek_status = $work->status;
        if($cek_status == 'OPEN'){
            $work->status = 'CLOSED';
            $work->save();
            
            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Post No. WO: '.$no_wo.'.','created_by'=>$nama,'updated_by'=>$nama];
            user_history::on($konek)->create($tmp);
            
            $message = [
                'success' => true,
                'title' => 'Simpan',
                'message' => 'CLOSED!! ',
            ];
            return response()->json($message);
        }else{
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'CLOSE No. WO: '.$work->no_wo.' sudah dilakukan! Pastikan Anda tidak membuka menu WORK ORDER lebih dari 1',
            ];
            return response()->json($message);
        } 
    }

    public function Unpost()
    {
        $konek = self::konek();
        $work = Workorder::on($konek)->find(request()->id);
        $no_wo = $work->no_wo;
        $cek_status = $work->status;
        if($cek_status == 'CLOSED'){
            $work->status = 'OPEN';
            $work->save();
            
            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Unclose No. WO: '.$no_wo.'.','created_by'=>$nama,'updated_by'=>$nama];
            user_history::on($konek)->create($tmp);

            $message = [
                'success' => true,
                'title' => 'Simpan',
                'message' => 'UNCLOSE!! ',
            ];
            return response()->json($message);
        }else{
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'UNCLOSE No. WO: '.$work->no_wo.' sudah dilakukan! Pastikan Anda tidak membuka menu WORK ORDER lebih dari 1',
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
        $work = Workorder::on($konek)->find($request->no_wo);

        if ($work != null){
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'No WO sudah ada.'
            ];
            return response()->json($message);
        }
        
        Workorder::on($konek)->create($request->all());
        // $getalat = Alat::on($konek)->where('no_asset_alat',$request->no_asset_alat)->first();
        // $work->kode_alat = $getalat->id;
        // $work->save();
        
        $no = Workorder::on($konek)->orderBy('created_at','desc')->first();
        $nama = auth()->user()->name;
        $tmp = ['nama' => $nama,'aksi' => 'Simpan No. WO: '.$no->no_wo.'.','created_by'=>$nama,'updated_by'=>$nama];
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
        $data = Workorder::on($konek)->find($no_pembelian);
        $status = $data->status;
        $level = auth()->user()->level;

        if($status == 'OPEN'){
            $output = array(
                'no_wo'=> $data->no_wo,
                'no_reff'=> $data->no_reff,
                'date_in'=>$data->date_in,
                'date_finish'=>$data->date_finish,
                'tipe'=>$data->tipe,
                'no_asset_alat'=>$data->no_asset_alat,
                'kode_lokasi'=>$data->kode_lokasi,
                'keterangan'=>$data->keterangan,
            );
            return response()->json($output);
        }
    }
    
    public function updateAjax(Request $request)
    {
        $konek = self::konek();
        $work = Workorder::on($konek)->find($request->no_wo);
        $work->update($request->all());
        $work->no_asset_alat = $request->no_asset_alat;
        $work->save();
        
        $message = [
            'success' => true,
            'title' => 'Update',
            'message' => 'Data ['.$work->no_wo.'] telah diubah.'
        ];
        return response()->json($message);
    }

    public function hapus_pembelian()
    {
        $konek = self::konek();
        $level = auth()->user()->level;

        $no_pembelian = request()->id;
        $data = Workorder::on($konek)->find($no_pembelian);

        $cek_detail = WorkorderDetail::on($konek)->where('no_wo',$no_pembelian)->first();
        if($cek_detail == null){
            $status = $data->status;

            if($status == 'OPEN'){
                $data->delete();
                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Hapus No. WO: '.$data->no_wo.'.','created_by'=>$nama,'updated_by'=>$nama];
                        //dd($tmp);
                user_history::on($konek)->create($tmp);

                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data ['.$data->no_wo.'] telah dihapus.'
                ];
                return response()->json($message);
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
