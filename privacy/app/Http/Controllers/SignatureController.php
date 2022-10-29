<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Signature;
use App\Models\tb_akhir_bulan;
use App\Models\MasterLokasi;
use App\Models\Company;
use Carbon;
use Alert;

class SignatureController extends Controller
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
        $create_url = route('signature.create');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $Lokasi= MasterLokasi::pluck('nama_lokasi','kode_lokasi');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        return view('admin.signature.index',compact('create_url','period', 'nama_lokasi','nama_company','Lokasi'));

    }

    public function anyData()
    {
        $konek = self::konek();
        return Datatables::of(Signature::on($konek))->make(true);
    }

    public function store(Request $request)
    {
        $konek = self::konek();
     
        Signature::on($konek)->create($request->all());
                    
        $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => 'Data telah disimpan.',
        ];
        return response()->json($message);
    }

    public function edit_signature()
    {
        $konek = self::konek();
        $kode_signature = request()->id;
        $data = Signature::on($konek)->find($kode_signature);
        $output = array(
            'kode_signature'=>$data->kode_signature,
            'mengetahui'=>$data->mengetahui,
            'jabatan'=>$data->jabatan,
            'limit_dari'=>$data->limit_dari,
            'limit_sampai'=>$data->limit_sampai,
            'kode_lokasi'=>$data->kode_lokasi,
        );
        return response()->json($output);
    }
    
    public function updateAjax(Request $request)
    {
        $konek = self::konek();

        Signature::on($konek)->find($request->kode_signature)->update($request->all());
        $message = [
            'success' => true,
            'title' => 'Update',
            'message' => 'Data telah di Update.'
        ];
        return response()->json($message);
    }

    public function hapus_signature()
    {   
        $konek = self::konek();
        $signature = Signature::on($konek)->find(request()->id);

        $signature->delete();

        $message = [
            'success' => true,
            'title' => 'Update',
            'message' => 'Data ['.$signature->mengetahui.'] telah dihapus.'
        ];
        return response()->json($message);
    }
}
