<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Ukuran;
use App\Models\Produk;
use App\Models\tb_akhir_bulan;
use App\Models\MasterLokasi;
use App\Models\Company;
use Carbon;


class UkuranController extends Controller
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
        $create_url = route('ukuran.create');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        return view('admin.ukuran.index',compact('create_url','period', 'nama_lokasi','nama_company'));
    
    }

    public function anyData()
    {
        $konek = self::konek();
        return Datatables::of(Ukuran::on($konek))->make(true);
    }

    public function store(Request $request)
    {
        $konek = self::konek();
        $nama_ukuran = $request->nama_ukuran;
        $cek_ukuran = Ukuran::on($konek)->where('nama_ukuran',$nama_ukuran)->first();
        if ($cek_ukuran == null){
            Ukuran::on($konek)->create($request->all());
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
                'message' => 'Ukuran Sudah Ada',
            ];
            return response()->json($message);  
        }
    }

    public function edit_ukuran()
    {
        $konek = self::konek();
        $kode_ukuran = request()->id;
        $data = Ukuran::on($konek)->find($kode_ukuran);
        $output = array(
            'kode_ukuran'=>$data->kode_ukuran,
            'nama_ukuran'=>$data->nama_ukuran,
        );
        return response()->json($output);
    }

    public function updateAjax(Request $request)
    {
        $konek = self::konek();

        Ukuran::on($konek)->find($request->kode_ukuran)->update($request->all());
   
        $message = [
            'success' => true,
            'title' => 'Update',
            'message' => 'Data telah di Update.'
        ];
        return response()->json($message);
    }

    public function hapus_ukuran()
    {   
        $konek = self::konek();
        $kode_ukuran = request()->id;
        $ukuran = Ukuran::on($konek)->find(request()->id);
        $cek_ukuran = Produk::on($konek)->where('kode_ukuran',$kode_ukuran)->first();

        if ($cek_ukuran == null){
            $ukuran->delete();

            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data ['.$ukuran->nama_ukuran.'] telah dihapus.'
            ];
            return response()->json($message);
        } else {
            $message = [
                'success' => false,
                'title' => 'Update',
                'message' => 'Data ['.$ukuran->nama_ukuran.'] dipakai dalam transaksi.'
            ];
            return response()->json($message);
        }
        
    }
}
