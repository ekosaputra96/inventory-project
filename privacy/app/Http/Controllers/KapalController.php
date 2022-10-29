<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Kapal;
use App\Models\LokasiKapal;
use App\Models\MasterLokasi;
use App\Models\Pemakaian;
use App\Models\tb_akhir_bulan;
use App\Models\Company;
use App\Exports\ListkapalExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon;

class KapalController extends Controller
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
        $create_url = route('kapal.create');
        $lokasi= MasterLokasi::pluck('nama_lokasi','kode_lokasi');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;
        
        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;
        
        return view('admin.kapal.index',compact('create_url','period','lokasi', 'nama_lokasi','nama_company'));
        
    }


    public function anyData()
    {
        $konek = self::konek();
        return Datatables::of(Kapal::on($konek)->with('masterlokasi')->orderBy('nama_kapal','asc'))->make(true);
    }
    
    public function getDatabyID()
    {
        $konek = self::konek();
        return Datatables::of(LokasiKapal::on($konek)->where('kode_kapal',request()->kode_customer)->orderBy('created_at','desc'))->make(true);
    }
    
    public function exportexcel(){
        $konek = self::konek();
        $kode_company = auth()->user()->kode_company;
        $nama = Company::find($kode_company);
        return Excel::download(new ListkapalExport($kode_company), 'List Kapal '.$nama->nama_company.'.xlsx');
    }
    
    public function detaillokasi($kode)
    {
        $konek = self::konek();
        $list_url= route('kapal.index');
        $cust = Kapal::on($konek)->find($kode);
        $Lokasi = MasterLokasi::pluck('nama_lokasi','kode_lokasi');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        return view('admin.kapal.indexlokasi', compact('list_url','period','nama_lokasi','nama_company','cust','Lokasi'));
    }

    public function store_lokasi(Request $request)
    {
        $konek = self::konek();
        $kode_customer = $request->kode_kapal;
        
        $cek_cust = Kapal::on($konek)->find($kode_customer);
        
        if ($request->kode_lokasi != $cek_cust->kode_lokasi) {
            LokasiKapal::on($konek)->create($request->all());
            
            $ceknpwp = LokasiKapal::on($konek)->where('kode_kapal', $kode_customer)->orderBy('created_at','desc')->first();
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
        $nama_kapal = $request->nama_kapal;
        $cek_nama = Kapal::on($konek)->where('nama_kapal',$nama_kapal)->first();       
        if ($cek_nama==null){
            $kapal = Kapal::on($konek)->create($request->all());
                $message = [
                'success' => true,
                'title' => 'Simpan',
                'message' => 'Data telah di Disimpan.',
            ];
            return response()->json($message);
        }
        else{
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Nama kapal Sudah Ada',
            ];
            return response()->json($message);
        }
    }

    public function edit_kapal()
    {
        $konek = self::konek();
        $kode_kapal = request()->id;
        $data = Kapal::on($konek)->find($kode_kapal);
        $output = array(
            'kode_kapal'=>$data->kode_kapal,
            'nama_kapal'=>$data->nama_kapal,
            'merk'=>$data->merk,
            'type'=>$data->type,
            'tahun'=>$data->tahun,
            'no_asset_kapal'=>$data->no_asset_kapal,
            'kode_lokasi'=>$data->kode_lokasi,
        );
        return response()->json($output);
    }


    public function updateAjax(Request $request)
    {
        $konek = self::konek();
        $kode = $request->kode_kapal;
        $asset_kapal = $request->no_asset_kapal;
        $kode_kapal = Pemakaian::on($konek)->where('kode_kapal',$kode)->first();
        $asset_kapal = Pemakaian::on($konek)->where('no_asset_kapal',$asset_kapal)->first();
        if ($kode_kapal == null && $asset_kapal == null){
            Kapal::on($konek)->find($request->kode_kapal)->update($request->all());
            
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
                'message' => 'Kapal ['.$request->nama_kapal.'] dipakai dalam transaksi.'
            ];
            return response()->json($message);
        }
    }

    public function hapus_kapal()
    {   
        $konek = self::konek();
        $kode = request()->id;
        $kapal = Kapal::on($konek)->find(request()->id);
        $asset_kapal = $kapal->no_asset_kapal;
        $kode_kapal = Pemakaian::on($konek)->where('kode_kapal',$kode)->first();
        $asset_kapal = Pemakaian::on($konek)->where('no_asset_kapal',$asset_kapal)->first();

        if ($kode_kapal == null && $asset_kapal == null){
            $kapal->delete();

            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Kapal ['.$kapal->nama_kapal.'] telah dihapus.'
            ];
            return response()->json($message);
        } else {
            $message = [
                'success' => false,
                'title' => 'Update',
                'message' => 'Kapal ['.$kapal->nama_kapal.'] dipakai dalam transaksi.'
            ];
            return response()->json($message);
        }
        
    }
}
