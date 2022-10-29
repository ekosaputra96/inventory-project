<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Merek;
use App\Models\Produk;
use App\Models\tb_akhir_bulan;
use App\Models\MasterLokasi;
use App\Models\Company;
use Carbon;

class MerekController extends Controller
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
        $create_url = route('merek.create');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;
        
        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        return view('admin.merek.index',compact('create_url','period', 'nama_lokasi','nama_company'));
    }

    public function anyData()
    {
        $konek = self::konek();
        return Datatables::of(Merek::on($konek))->make(true);
    }

    public function store(Request $request)
    {
        $konek = self::konek();
        $nama_merek = $request->nama_merek;
        $cek_merek = Merek::on($konek)->where('nama_merek',$nama_merek)->first();
        if ($cek_merek == null){
            Merek::on($konek)->create($request->all());
            $message = [
                'success' => true,
                'title' => 'Simpan',
                'message' => 'Data telah di Disimpan.'
            ];
            return response()->json($message);
        } else {
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Merek Sudah Ada',
            ];
            return response()->json($message);
        }
    }

    public function edit_merek()
    {
        $konek = self::konek();
        $kode_merek = request()->id;
        $data = Merek::on($konek)->find($kode_merek);
        $output = array(
            'kode_merek'=>$data->kode_merek,
            'nama_merek'=>$data->nama_merek,
        );
        return response()->json($output);
    }

    public function updateAjax(Request $request)
    {
        $konek = self::konek();
        $kode_merek = $request->kode_merek;
        $cek_merek = Produk::on($konek)->where('kode_merek',$kode_merek)->first();
        if ($cek_merek == null){
            Merek::on($konek)->find($request->kode_merek)->update($request->all());
       
            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data telah di Update.'
            ];
            return response()->json($message);      
        } else{
            $message = [
                'success' => false,
                'title' => 'Update',
                'message' => 'Data ['.$request->kode_merek.'] sudah terikat ke item.',
            ];
            return response()->json($message);  
          
        }
    }

    public function hapus_merek()
    {   
        $konek = self::konek();
        $kode_merek = request()->id;
        $merek = Merek::on($konek)->find(request()->id);
        $cek_merek = Produk::on($konek)->where('kode_merek',$kode_merek)->first();

        if ($cek_merek == null){
            $merek->delete();

            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data ['.$merek->nama_merek.'] telah dihapus.'
            ];
            return response()->json($message);
        } else {
            $message = [
                'success' => false,
                'title' => 'Update',
                'message' => 'Data ['.$merek->nama_merek.'] dipakai dalam transaksi.'
            ];
            return response()->json($message);
        }
        
    }
}
