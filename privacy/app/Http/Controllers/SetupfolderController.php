<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Bank;
use App\Models\tb_akhir_bulan;
use App\Models\MasterLokasi;
use App\Models\Company;
use App\Models\SetupAkses;
use App\Models\SetupFolder;
use App\Models\Users;
use Carbon;
use PDF;
use Excel;
use DB;

class SetupfolderController extends Controller
{
    public function konek()
    {
        $compa = auth()->user()->kode_company;
        if ($compa == '01'){
            $koneksi = 'mysqldepo';
        }else if ($compa == '02'){
            $koneksi = 'mysqlpbm';
        }else if ($compa == '0401'){
            $koneksi = 'mysqlgutjkt';
        }else if ($compa == '03'){
            $koneksi = 'mysql';
        }else if ($compa == '04'){
            $koneksi = 'mysqlgut';
        }else if ($compa == '05'){
            $koneksi = 'mysqlsub';
        }else if ($compa == '06'){
            $koneksi = 'mysqlinf';
        }
        return $koneksi;
    }

    public function index()
    {
        $konek = self::konek();
        $create_url = route('setupfolder.create');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');

        $User = Users::where('kode_company', auth()->user()->kode_company)->pluck('name','name');

        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;
        
        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        return view('admin.setupfolder.index',compact('create_url','period', 'nama_lokasi','nama_company','User'));
    }

    public function anyData()
    {
        $konek = self::konek();
        return Datatables::of(SetupFolder::get())->make(true);
    }
    
    public function store(Request $request)
    {
        SetupFolder::create($request->all());
        $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => 'Data telah di Disimpan.'
        ];
        return response()->json($message);
    }

    public function edit_bank()
    {
        $kode_bank = request()->id;
        $data = SetupFolder::find($kode_bank);
        $output = array(
            'id'=>$data->id,
            'keterangan'=>$data->keterangan,
            'folder'=>$data->folder,
            'subfolder'=>$data->subfolder,
        );
        return response()->json($output);
    }

    public function updateAjax(Request $request)
    {
        SetupFolder::find($request->id)->update($request->all());
        $message = [
            'success' => true,
            'title' => 'Update',
            'message' => 'Data telah di Update.'
        ];
        return response()->json($message);
    }

    public function hapus_bank()
    {   
        SetupFolder::find(request()->id)->delete();
        $message = [
            'success' => true,
            'title' => 'Update',
            'message' => 'Data telah dihapus.'
        ];
        return response()->json($message);
    }
}
