<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\JenisMobil;
use App\Models\Pemakaian;
use App\Models\Mobil;
use App\Models\tb_akhir_bulan;
use App\Models\MasterLokasi;
use App\Models\Company;
use Carbon;

class JenismobilController extends Controller
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
        $create_url = route('jenismobil.create');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;
        
        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        return view('admin.jenismobil.index',compact('create_url','period', 'nama_lokasi','nama_company'));
        
    }

    public function anyData()
    {
        $konek = self::konek();
        return Datatables::of(JenisMobil::on($konek)->orderBy('nama_jenis_mobil','asc'))->make(true);
    }

    public function store(Request $request)
    {
        $konek = self::konek();
        $nama_jenis_mobil = $request->nama_jenis_mobil;
        $cek_jenis = JenisMobil::on($konek)->where('nama_jenis_mobil',$nama_jenis_mobil)->first();
        if ($cek_jenis == null ){
            JenisMobil::on($konek)->create($request->all());
            $message = [
                'success' => true,
                'title' => 'Simpan',
                'message' => 'Data telah di Disimpan.'
            ];
            return response()->json($message);
        }else {
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Jenis Mobil Sudah Ada',
            ];
            return response()->json($message);
        }
    }

    public function edit_jenismobil()
    {
        $konek = self::konek();
        $kode_jenis_mobil = request()->id;
        $data = JenisMobil::on($konek)->find($kode_jenis_mobil);
        $output = array(
            'id'=>$data->id,
            'nama_jenis_mobil'=>$data->nama_jenis_mobil,
        );
        return response()->json($output);
    }

    public function updateAjax(Request $request)
    {
        $konek = self::konek();
        $kode_jenis_mobil = $request->kode_jenis_mobil;
        $cek_jenis = Mobil::on($konek)->where('kode_jenis_mobil',$kode_jenis_mobil)->first();
        if ($cek_jenis == null){
            JenisMobil::on($konek)->find($request->kode_jenis_mobil)->update($request->all());
       
            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data telah di Update.'
            ];
            return response()->json($message);
        }else{
            $message = [
                'success' => false,
                'title' => 'Update',
                'message' => 'Data ['.$request->nama_jenis_mobil.'] dipakai di master mobil.'
            ];
            return response()->json($message);
        }
      
    }

    public function hapus_jenismobil()
    {   
        $konek = self::konek();
        $jenismobil = JenisMobil::on($konek)->find(request()->id);
        $kode_jenis_mobil = $jenismobil->kode_jenis_mobil;
        $cek_jenis = Mobil::on($konek)->where('kode_jenis_mobil',$kode_jenis_mobil)->first();

        if ($cek_jenis == null){
            $jenismobil->delete();

            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data ['.$jenismobil->nama_jenis_mobil.'] telah dihapus.'
            ];
            return response()->json($message);
        } else {
            $message = [
                'success' => false,
                'title' => 'Update',
                'message' => 'Data ['.$jenismobil->nama_jenis_mobil.'] dipakai dalam transaksi.'
            ];
            return response()->json($message);
        }
        
    }
}
