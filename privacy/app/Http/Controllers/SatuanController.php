<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\satuan;
use App\Models\Produk;
use App\Models\Jasa;
use App\Models\tb_akhir_bulan;
use App\Models\MasterLokasi;
use App\Models\Company;
use Carbon;


class SatuanController extends Controller
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
        $create_url = route('satuan.create');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        return view('admin.satuan.index',compact('create_url','period', 'nama_lokasi','nama_company'));
        
    }

    public function anyData()
    {
        return Datatables::of(satuan::query())->make(true);
    }

    public function store(Request $request)
    {
        $kode_satuan = $request->kode_satuan;
        $nama_satuan = $request->nama_satuan;
        $cek_satuan = Satuan::where('kode_satuan',$kode_satuan)->orwhere('nama_satuan',$nama_satuan)->first();
        if ($cek_satuan==null){
            $satuan = satuan::create($request->all());
            $message = [
                'success' => true,
                'title' => 'Simpan',
                'message' => 'Data telah disimpan.'
            ];
            return response()->json($message);
        } else {
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Kode Satuan Sudah Ada',
            ];
            return response()->json($message);  
        }
        
    }

    public function edit_satuan()
    {
        $kode_satuan = request()->id;
        $data = satuan::find($kode_satuan);
        $output = array(
            'kode_satuan'=>$data->kode_satuan,
            'nama_satuan'=>$data->nama_satuan,
            'status'=>$data->status,
        );
        return response()->json($output);
    }

    public function updateAjax(Request $request)
    {
        $satuan2 = $request->kode_satuan;
        $cek_satuan = Produk::where('kode_satuan',$satuan2)->first();
        if ($cek_satuan == null){
            $request->validate([
                'kode_satuan'=>'required',
                'nama_satuan'=> 'required',
                'status' => 'required'
            ]);

            satuan::find($request->kode_satuan)->update($request->all());

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
                'message' => 'Data ['.$request->kode_satuan.'] sudah terikat ke item.'
            ];
            return response()->json($message);
        }
    }

    public function hapus_satuan()
    {   
        $satuan2 = request()->id;
        $satuan = satuan::find(request()->id);
        $cek_satuan = Produk::where('kode_satuan',$satuan2)->first();

        if ($cek_satuan == null){
            $satuan->delete();

            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data ['.$satuan->nama_satuan.'] telah dihapus.'
            ];
            return response()->json($message);
        } else {
            $message = [
                'success' => false,
                'title' => 'Update',
                'message' => 'Data ['.$satuan->nama_satuan.'] dipakai dalam transaksi.'
            ];
            return response()->json($message);
        }
        
    }
}
