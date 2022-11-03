<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\tb_akhir_bulan;
use App\Models\MasterLokasi;
use App\Models\Company;
use App\Models\SetupAkses;
use App\Models\Users;
use Carbon;
use PDF;
use Excel;
use DB;

class SetupaksesController extends Controller
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
            $koneksi = 'mysqlemkl';
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
        $create_url = route('setupakses.create');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');

        $User = Users::where('kode_company', auth()->user()->kode_company)->pluck('name','name');

        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;
        
        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        return view('admin.setupakses.index',compact('create_url','period', 'nama_lokasi','nama_company','User'));

    }

    public function anyData()
    {
        $konek = self::konek();
        return Datatables::of(SetupAkses::on($konek)->get())->make(true);
    }
    
    public function store(Request $request)
    {
        $konek = self::konek();
        $nama_user = $request->nama_user;
        $cek = SetupAkses::on($konek)->where('nama_user',$nama_user)->where('kode_company', auth()->user()->kode_company)->first();
        if($cek == null){
            $bank = SetupAkses::on($konek)->create($request->all());
            $message = [
                'success' => true,
                'title' => 'Simpan',
                'message' => 'Data telah di Disimpan.'
            ];
            return response()->json($message);
        }
        else{
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'User Sudah Ada',
            ];
            return response()->json($message);
        }
    }

    public function edit_bank()
    {
        $konek = self::konek();
        $kode_bank = request()->id;
        $data = SetupAkses::on($konek)->find($kode_bank);
        $output = array(
            'id'=>$data->id,
            'nama_user'=> $data->nama_user,
            'nama_user2'=> $data->nama_user2,
            'nama_user3'=> $data->nama_user3,
            'limit_dari'=>$data->limit_dari,
            'limit_total'=> $data->limit_total,
        );
        return response()->json($output);
    }

    public function updateAjax(Request $request)
    {
        $konek = self::konek();
        SetupAkses::on($konek)->find($request->id)->update($request->all());
           
        $message = [
            'success' => true,
            'title' => 'Update',
            'message' => 'Data telah di Update.'
        ];
        return response()->json($message);
    }

    public function hapus_bank()
    {   
        $konek = self::konek();
        $bank = SetupAkses::on($konek)->find(request()->id);
        
        $bank->delete();
        
        $message = [
            'success' => true,
            'title' => 'Update',
            'message' => 'Data User telah dihapus.'
        ];
        return response()->json($message);
    }
}
