<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Mobil;
use App\Models\LokasiMobil;
use App\Models\MasterLokasi;
use App\Models\JenisMobil;
use App\Models\Pemakaian;
use App\Models\Pemakaianban;
use App\Models\tb_akhir_bulan;
use App\Exports\ListmobilExport;
use App\Models\Company;
use Maatwebsite\Excel\Facades\Excel;
use Carbon;


class MobilController extends Controller
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
        $create_url = route('mobil.create');
        $lokasi= MasterLokasi::pluck('nama_lokasi','kode_lokasi');
        $JenisMobil= JenisMobil::on($konek)->pluck('nama_jenis_mobil','id');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;
        
        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        return view('admin.mobil.index',compact('create_url','lokasi','JenisMobil','period', 'nama_lokasi','nama_company'));
    }
    
     public function exportexcel(){
        $konek = self::konek();
        $kode_company = auth()->user()->kode_company;
        $nama = Company::find($kode_company);
        return Excel::download(new ListmobilExport($kode_company), 'List Mobil '.$nama->nama_company.'.xlsx');
    }

    public function anyData()
    {
        $konek = self::konek();
        return Datatables::of(Mobil::on($konek)->with('masterlokasi','jenismobil')->orderBy('kode_mobil','asc'))->make(true);
    }
    
    public function getDatabyID()
    {
        $konek = self::konek();
        return Datatables::of(LokasiMobil::on($konek)->where('kode_mobil',request()->kode_customer)->orderBy('created_at','desc'))->make(true);
    }

    public function detaillokasi($kode)
    {
        $konek = self::konek();
        $list_url= route('mobil.index');
        $cust = Mobil::on($konek)->find($kode);
        $Lokasi = MasterLokasi::pluck('nama_lokasi','kode_lokasi');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        return view('admin.mobil.indexlokasi', compact('list_url','period','nama_lokasi','nama_company','cust','Lokasi'));
    }

    public function store_lokasi(Request $request)
    {
        $konek = self::konek();
        $kode_customer = $request->kode_mobil;

        $cek_cust = Mobil::on($konek)->find($kode_customer);
        
        if ($request->kode_lokasi != $cek_cust->kode_lokasi) {
            LokasiMobil::on($konek)->create($request->all());
            
            $ceknpwp = LokasiMobil::on($konek)->where('kode_mobil', $kode_customer)->orderBy('created_at','desc')->first();
            $update_info = [
                'kode_lokasi'=>$ceknpwp->kode_lokasi,
            ];
            $cek_cust->update($update_info);
        }

        $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => 'Data telah disimpan.'
        ];
        return response()->json($message);
    }

    public function store(Request $request)
    {   
        $konek = self::konek();
        $nopol = $request->nopol;
        $ceknopol = Mobil::on($konek)->where('nopol',$nopol)->first();
        if ($ceknopol == null) {
            Mobil::on($konek)->create($request->all());
                $message = [
                'success' => true,
                'title' => 'Simpan',
                'message' => 'Data telah di Disimpan.'
            ];
            return response()->json($message);    
        }else{
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Nopol sudah ada.',
            ];
            return response()->json($message);
        }
    }

    public function edit_mobil()
    {
        $konek = self::konek();
        $kode_mobil = request()->id;
        $data = Mobil::on($konek)->find($kode_mobil);
        $output = array(
            'kode_mobil'=>$data->kode_mobil,
            'nopol'=>$data->nopol,
            'kode_jenis_mobil'=>$data->kode_jenis_mobil,
            'tahun'=>$data->tahun,
            'no_asset_mobil'=>$data->no_asset_mobil,
            'kode_lokasi'=>$data->kode_lokasi,
            'status_mobil'=>$data->status_mobil,
        );
        return response()->json($output);
    }

    public function updateAjax(Request $request)
    {
        $konek = self::konek();
        $kode = $request->kode_mobil;
        $asset_mobil = $request->no_asset_mobil;
        $kode_mobil = Pemakaian::on($konek)->where('kode_mobil',$kode)->first();
        $asset_mobil = Pemakaian::on($konek)->where('no_asset_mobil',$asset_mobil)->first();
        $kode_mobil_ban = Pemakaianban::on($konek)->where('kode_mobil',$kode)->first();
            
        if ($kode_mobil == null && $asset_mobil == null && $kode_mobil_ban == null){
            mobil::on($konek)->find($request->kode_mobil)->update($request->all());
       
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
                'message' => 'Mobil ['.$request->nopol. ' / ' .$request->no_asset_mobil.'] dipakai dalam transaksi.'
            ];
            return response()->json($message);
        }
    }

    public function hapus_mobil()
    {   
        $konek = self::konek();
        $mobil = Mobil::on($konek)->find(request()->id);
        $kode = request()->id;
        $asset_mobil = $mobil->no_asset_mobil;
        $kode_mobil = Pemakaian::on($konek)->where('kode_mobil',$kode)->first();
        $asset_mobil = Pemakaian::on($konek)->where('no_asset_mobil',$asset_mobil)->first();
        $kode_mobil_ban = Pemakaianban::on($konek)->where('kode_mobil',$kode)->first();

        if ($kode_mobil == null && $asset_mobil == null && $kode_mobil_ban == null){
            $mobil->delete();

            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Mobil ['.$mobil->nopol. ' / ' .$mobil->no_asset_mobil.'] telah dihapus.'
            ];
            return response()->json($message);
        } else {
            $message = [
                'success' => false,
                'title' => 'Update',
                'message' => 'Mobil ['.$mobil->nopol. ' / ' .$mobil->no_asset_mobil.'] dipakai dalam transaksi.'
            ];
            return response()->json($message);
        }
        
    }
}
