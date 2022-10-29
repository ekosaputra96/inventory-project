<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\TransaksiSetup;
use App\Models\tb_akhir_bulan;
use App\Models\MasterLokasi;
use App\Models\Company;
use Carbon;
use Alert;

class TransaksisetupController extends Controller
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
        $create_url = route('transaksisetup.create');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        return view('admin.transaksisetup.index',compact('create_url','period', 'nama_lokasi','nama_company'));

    }

    public function anyData()
    {
        return Datatables::of(TransaksiSetup::query())->make(true);
    }


    public function store(Request $request)
    {
        $kode_transaksi = $request->kode_transaksi;
        $cek_transaksi = TransaksiSetup::where('kode_transaksi',$kode_transaksi)->first();
        if ($cek_transaksi==null){
            TransaksiSetup::create($request->all());
            $message = [
                'success' => true,
                'title' => 'Simpan',
                'message' => 'Data telah disimpan.'
            ];
            return response()->json($message);
        }
        else{
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Kode Transaksi Sudah Ada',
            ];
            return response()->json($message);
        }
    }
    
    public function edit_transaksisetup()
    {
        $kode_setup = request()->id;
        $data = TransaksiSetup::find($kode_setup);
        $output = array(
            'kode_setup'=>$data->kode_setup,
            'kode_transaksi'=>$data->kode_transaksi,
            'nama_transaksi'=>$data->nama_transaksi,
        );
        return response()->json($output);
    }


    public function updateAjax(Request $request)
    {
        TransaksiSetup::find($request->kode_setup)->update($request->all());

        $message = [
            'success' => true,
            'title' => 'Update',
            'message' => 'Data telah di Update.'
        ];
        return response()->json($message);
    }

    public function hapus_transaksisetup()
    {   
        $transaksisetup = TransaksiSetup::find(request()->id);

        $transaksisetup->delete();

        $message = [
            'success' => true,
            'title' => 'Update',
            'message' => 'Data ['.$transaksisetup->nama_transaksi.'] telah dihapus.'
        ];
        return response()->json($message);
    }
}
