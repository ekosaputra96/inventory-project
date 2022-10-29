<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\TaxSetup;
use App\Models\tb_akhir_bulan;
use App\Models\MasterLokasi;
use App\Models\Company;
use Carbon;
use Alert;

class TaxSetupController extends Controller
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
        $create_url = route('taxsetup.create');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        return view('admin.taxsetup.index',compact('create_url','period', 'nama_lokasi','nama_company'));

    }

    public function anyData()
    {
        return Datatables::of(TaxSetup::query())->make(true);
    }

    public function store(Request $request)
    {
        TaxSetup::create($request->all());
        $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => 'Data telah di Disimpan.'
        ];
        return response()->json($message);
    }

    public function edit_taxsetup()
    {
        $id_pajak = request()->id;
        $data = TaxSetup::find($id_pajak);
        $output = array(
            'id_pajak'=>$data->id_pajak,
            'kode_pajak'=>$data->kode_pajak,
            'nama_pajak'=>$data->nama_pajak,
            'nilai_pajak'=>$data->nilai_pajak,
            'tgl_berlaku'=>$data->tgl_berlaku,
        );
        return response()->json($output);
    }

    public function updateAjax(Request $request)
    {
        TaxSetup::find($request->id_pajak)->update($request->all());
   
        $message = [
            'success' => true,
            'title' => 'Update',
            'message' => 'Data telah di Update.'
        ];
        return response()->json($message);
    }

    public function hapus_taxsetup()
    {   
        $taxsetup = TaxSetup::find(request()->id);

        $taxsetup->delete();

        $message = [
            'success' => true,
            'title' => 'Update',
            'message' => 'Data ['.$taxsetup->nama_pajak.'] telah dihapus.'
        ];
        return response()->json($message);
    }
}
