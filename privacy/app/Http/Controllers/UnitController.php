<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Unit;
use App\Models\Produk;
use App\Models\tb_akhir_bulan;
use App\Models\MasterLokasi;
use App\Models\Company;
use App\Models\Coa;
use App\Models\CoaDetail;
use Carbon;
use DB;

class UnitController extends Controller
{
    public function konek()
    {
        $compa2 = auth()->user()->kode_company;
        $compa = substr($compa2,0,2);
        if ($compa == '01'){
            $koneksi = 'mysqldepo';
        }else if ($compa == '02'){
            $koneksi = 'mysqlpbm';
        }else if ($compa == '03'){
            $koneksi = 'mysqlemkl';
        }else if ($compa == '22'){
            $koneksi = 'mysqlskt';
        }else if ($compa == '04'){
            $koneksi = 'mysqlgut';
        }else if ($compa == '05'){
            $koneksi = 'mysql';
        }
        return $koneksi;
    }
    
    public function index()
    {
        $konek = self::konek();
        $create_url = route('unit.create');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;
        
        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        return view('admin.unit.index',compact('create_url','period', 'nama_lokasi','nama_company'));
    }

    public function anyData()
    {
        return Datatables::of(Unit::all())->make(true);
    }

    public function store(Request $request)
    {
        $kode_unit = $request->kode_unit;
        $nama_unit = $request->nama_unit;

        $cek_sama = Unit::where('kode_unit',$kode_unit)->orwhere('nama_unit',$nama_unit)->first();

        
        if ($cek_sama == null){
            Unit::create($request->all());
            $message = [
                'success' => true,
                'title' => 'Simpan',
                'message' => 'Data telah di Disimpan.'
            ];
            return response()->json($message);
        }
        else {
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Kode Unit Sudah Ada',
            ];
            return response()->json($message);
        }
        
    }

    public function edit_unit()
    {
        $kode_unit = request()->kode_unit;
        $data = Unit::find($kode_unit);

        $output = array(
            'kode_unit'=>$data->kode_unit,
            'nama_unit'=>$data->nama_unit,

        );
        return response()->json($output);
    }

    public function updateAjax(Request $request)
    {
        $unit = $request->kode_unit;
        Unit::find($unit)->update($request->all());

        $message = [
            'success' => true,
            'title' => 'Update',
            'message' => 'Data telah di Update.'
        ];
        return response()->json($message);
       
    }

    public function hapus_unit()
    {   
        $konek = self::konek();
        $unit = request()->kode_unit;
        $unitproduk = Unit::find($unit);
        $cek_unit = Produk::on($konek)->where('kode_unit',$unit)->first();

        if ($cek_unit == null){
            $unitproduk->delete();

            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data ['.$unitproduk->nama_unit.'] telah dihapus.'
            ];
            return response()->json($message);
        } else {
            $message = [
                'success' => false,
                'title' => 'Update',
                'message' => 'Data ['.$unitproduk->nama_unit.'] dipakai dalam transaksi.'
            ];
            return response()->json($message);
        }
        
    }
}
