<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Jasa;
use App\Models\satuan;
use App\Models\PembelianDetail;
use App\Models\tb_akhir_bulan;
use App\Models\MasterLokasi;
use App\Models\Company;
use Carbon;

class JasaController extends Controller
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
        $create_url = route('jasa.create');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        
        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        return view('admin.jasa.index',compact('create_url','period', 'nama_lokasi','nama_company'));

    }

    public function anyData()
    {
        return Datatables::of(Jasa::query()->orderBy('nama_item','asc'))->make(true);
    }

    public function store(Request $request)
    {
        $nama_item = $request->nama_item;
        $cek_nama = Jasa::where('nama_item',$nama_item)->first();       
        if ($cek_nama==null){
            $Jasa = Jasa::create($request->all());
            $message = [
                'success' => true,
                'title' => 'Simpan',
                'message' => 'Data telah di Disimpan.',
            ];
            return response()->json($message);
        }
        else{
            $message = [
                'sucess' => false,
                'title' => 'Simpan',
                'message' => 'Nama Item Sudah Ada',
            ];
            return response()->json($message);
        }
    }


    public function edit_jasa()
    {
        $kode_produk = request()->id;
        $data = Jasa::find($kode_produk);
        $output = array(
            'kode_produk'=>$data->id,
            'nama_item'=>$data->nama_item,
            'keterangan'=>$data->keterangan,
        );
        return response()->json($output);
    }


    public function updateAjax(Request $request)
    {
        Jasa::find($request->kode_produk)->update($request->all());
       
        $message = [
            'success' => true,
            'title' => 'Update',
            'message' => 'Data telah di Update.'
        ];
        return response()->json($message);
    }

    public function hapus_jasa()
    {   
        $jasa = Jasa::find(request()->id);
        $jasa->delete();

        $message = [
            'success' => true,
            'title' => 'Update',
            'message' => 'Data ['.$jasa->nama_item.'] telah dihapus.'
        ];
        return response()->json($message);
    }
}
