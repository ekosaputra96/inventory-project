<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Signature;
use App\Models\Company;
use App\Models\MasterLokasi;
use App\Models\user_history;
use App\Models\tb_akhir_bulan;
use App\Models\Kasbon;
use PDF;
use Excel;
use DB;
use Carbon;
use DateTime;

class KasbonController extends Controller
{
    public function konek()
    {
        $compa = auth()->user()->kode_company;
        if ($compa == '01'){
            $koneksi = 'mysqldepo';
        }else if ($compa == '02'){
            $koneksi = 'mysqlpbm';
        }else if ($compa == '99'){
            $koneksi = 'mysqlpbmlama';
        }else if ($compa == '0401'){
            $koneksi = 'mysqlgutjkt';
        }else if ($compa == '03'){
            $koneksi = 'mysql';
        }else if ($compa == '04'){
            $koneksi = 'mysqlgut';
        }else if ($compa == '05'){
            $koneksi = 'mysqlsub';
        }
        return $koneksi;
    }

    public function index()
    {
        $konek = self::konek();
        $Company= Company::pluck('nama_company','kode_company');
        $Lokasi= MasterLokasi::where('kode_lokasi', '<>', auth()->user()->kode_lokasi)->pluck('nama_lokasi','kode_lokasi');
        $asal = MasterLokasi::where('kode_lokasi', auth()->user()->kode_lokasi)->first();
        
        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;
        
        return view('admin.kasbon.index',compact('Company','period','asal','Lokasi', 'nama_lokasi','nama_company'));
    }

    public function anyData()
    {
        $konek = self::konek();
        $data = Kasbon::on($konek)->get();
        return response()->json($data);
    }

    public function exportPDF(){
        $konek = self::konek();
        $request = $_GET['no_pkb'];

        $kasbon = Kasbon::on($konek)->find($request);
        $user = $kasbon->created_by;

        $tgl = $kasbon->tanggal_permintaan;
        $date=date_create($tgl);

        $total_qty = $kasbon->nilai;
        $ttd = $user;

        $get_lokasi = auth()->user()->kode_lokasi;
        $get_company = auth()->user()->kode_company;

        $nama_lokasi = MasterLokasi::find($get_lokasi);
        $nama = $nama_lokasi->nama_lokasi;

        $company = Company::find($get_company);
        $nama2 = $company->nama_company;

        $dt = Carbon\Carbon::now();
        $date_now = Carbon\Carbon::parse($dt)->format('d/m/Y');

        $pdf = PDF::loadView('/admin/kasbon/pdf', compact('request', 'kasbon','tgl','date', 'ttd','total_qty','date_now','dt','nama','nama2'));
        $pdf->setPaper([0, 0, 684, 792], 'potrait');
        return $pdf->stream('Permintaan Kasbon '.$request.'.pdf');
    }

    public function getnama()
    {   
        $konek = self::konek();
        $lokasi = MasterLokasi::find(request()->lokasi);

        $output = array(
            'nama_lokasi'=>$lokasi->nama_lokasi,
        );

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

    public function approve()
    {
        $konek = self::konek();
        $level = auth()->user()->level;
        $lokasi = auth()->user()->kode_lokasi;
        $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode','Disable')->first();

        if ($level == 'superadministrator' || $cek_bulan == null) {
            $cek = Kasbon::on($konek)->find(request()->id);
            if ($cek->status != 'POSTED' || $cek->status == 'OPEN') {
                $message = [
                        'success' => false,
                        'title' => 'Gagal',
                        'message' => 'Permintaan masih OPEN / sudah di APPROVE.',
                ];
                return response()->json($message);
            }else if ($cek->status == 'POSTED') {
                $cek->status = 'APPROVED';
                $cek->save();

                $message = [
                        'success' => true,
                        'title' => 'Berhasil',
                        'message' => 'Permintaan berhasil disetujui.',
                ];
                return response()->json($message);
            }
        }else {
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Anda tidak mempunyai akses posting data',
            ];
            return response()->json($message);
        }
    }

    public function Post()
    {
        $konek = self::konek();
        $level = auth()->user()->level;
        $lokasi = auth()->user()->kode_lokasi;
        $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode','Disable')->first();

        if ($level == 'superadministrator' || $cek_bulan == null) {
            $cek = Kasbon::on($konek)->find(request()->id);
            if ($cek->status != 'OPEN') {
                $message = [
                        'success' => false,
                        'title' => 'Gagal',
                        'message' => 'Permintaan sudah di Posting.',
                ];
                return response()->json($message);
            }else if ($cek->status == 'OPEN') {
                $cek->status = 'POSTED';
                $cek->save();

                $message = [
                        'success' => true,
                        'title' => 'Berhasil',
                        'message' => 'Permintaan berhasil di Posting.',
                ];
                return response()->json($message);
            }
        }else {
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Anda tidak mempunyai akses posting data',
            ];
            return response()->json($message);
        }
    }

    public function Unpost()
    {
        $konek = self::konek();
        $level = auth()->user()->level;
        $lokasi = auth()->user()->kode_lokasi;
        $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode','Disable')->first();

        if ($level == 'superadministrator' || $cek_bulan == null) {
            $cek = Kasbon::on($konek)->find(request()->id);
            if ($cek->status != 'POSTED') {
                $message = [
                    'success' => false,
                    'title' => 'Gagal',
                    'message' => 'Permintaan sudah di unpost/approved.',
                ];
                return response()->json($message);
            }else if ($cek->status == 'POSTED') {
                $cek->status = 'OPEN';
                $cek->save();

                $message = [
                    'success' => true,
                    'title' => 'Berhasil',
                    'message' => 'Permintaan berhasil di Unpost.',
                ];
                return response()->json($message);
            }
        }else {
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Anda tidak mempunyai akses posting data',
            ];
            return response()->json($message);
        }
        
    }

    public function store(Request $request)
    {
        $konek = self::konek();
        $tanggal = $request->tanggal_permintaan;
        $period = Carbon\Carbon::parse($tanggal)->format('F Y');

        $reopen = tb_akhir_bulan::on($konek)->where('reopen_status','true')->first();

        if ($reopen != null){
            $tgl = $reopen->periode;
            $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
            $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
            $cek = Kasbon::on($konek)->whereMonth('tanggal_permintaan',$bulan_transaksi)->whereYear('tanggal_permintaan',$tahun_transaksi)->where('status','OPEN')->get();
            if (count($cek) >= 1){
                $message = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Masih ada Transaksi PKB yang OPEN.'
                ];
               return response()->json($message);
            }
        }else {
            $tgl = tb_akhir_bulan::on($konek)->where('status_periode','Open')->first();
            $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl->periode)->year;
            $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl->periode)->month;
            $cek = Kasbon::on($konek)->whereMonth('tanggal_permintaan',$bulan_transaksi)->whereYear('tanggal_permintaan',$tahun_transaksi)->where('status','OPEN')->get();
            if (count($cek) >= 1){
                $message = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Masih ada Transaksi PKB yang OPEN.'
                ];
               return response()->json($message);
            }
        }

        $validate = $this->periodeChecker($tanggal);
             
        if($validate == true){
            $transfer = Kasbon::on($konek)->create($request->all());

            $no = Kasbon::on($konek)->orderBy('created_at','desc')->first();
            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Simpan No. transfer: '.$no->no_pkb.'.','created_by'=>$nama,'updated_by'=>$nama];
                    
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
                'message' => '<b>Periode</b> ['.$period.'] <b>Telah Ditutup / Belum Dibuka</b>'
            ];
            return response()->json($message);
        }
        
    }

    public function edit_kasbon()
    {
        $konek = self::konek();
        $pkb = request()->id;
        $data = Kasbon::on($konek)->find($pkb);
        
        $output = array(
            'no_pkb'=>$data->no_pkb,
            'nama_pemohon'=> $data->nama_pemohon,
            'tanggal_permintaan'=> $data->tanggal_permintaan,
            'nilai'=>$data->nilai,
            'keterangan'=> $data->keterangan,
        );
        return response()->json($output);
    }

    public function updateAjax(Request $request)
    {
        $konek = self::konek();

        $tanggal = $request->tanggal_permintaan;
        $period = Carbon\Carbon::parse($tanggal)->format('F Y');

        $validate = $this->periodeChecker($tanggal);
             
        if($validate == true){
            $transfer = Kasbon::on($konek)->find($request->no_pkb)->update($request->all());
            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Edit No. PKB: '.$request->no_pkb.'.','created_by'=>$nama,'updated_by'=>$nama];
                    
            user_history::on($konek)->create($tmp);
            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data telah di Update.'
            ];
            return response()->json($message);
        }else{
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => '<b>Periode</b> ['.$period.'] <b>Telah Ditutup / Belum Dibuka</b>'
            ];
            return response()->json($message);
        }
    }

    public function hapus_kasbon()
    {
        $konek = self::konek();
        $kasbon = Kasbon::on($konek)->find(request()->id);

        $tanggal = $kasbon->tanggal_permintaan;
        $period = Carbon\Carbon::parse($tanggal)->format('F Y');

        $validate = $this->periodeChecker($tanggal);
             
        if($validate == true){
            $no_pkb = $kasbon->no_pkb;
                  
            $data = Kasbon::on($konek)->find($no_pkb);
            
            $kasbon->delete();

            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Hapus No. transfer: '.$no_pkb.'.','created_by'=>$nama,'updated_by'=>$nama];
                              
            user_history::on($konek)->create($tmp);

            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data ['.$kasbon->no_pkb.'] telah dihapus.'
            ];
            return response()->json($message);
        }else{
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => '<b>Periode</b> ['.$period.'] <b>Telah Ditutup / Belum Dibuka</b>'
            ];
            return response()->json($message);
        }
    }
}
