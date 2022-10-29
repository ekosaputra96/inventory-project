<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Alat;
use App\Models\Alat2;
use App\Models\LokasiAlat;
use App\Models\Operator;
use App\Models\MasterLokasi;
use App\Models\Pemakaian;
use App\Models\Pemakaianban;
use App\Models\tb_akhir_bulan;
use App\Models\Company;
use App\Exports\ListalatExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon;
use DB;

class AlatController extends Controller
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

    public function konek3()
    {
        $compa = auth()->user()->kode_company;
        if ($compa == '02'){
            $koneksi = 'mysql_front_pbm';
        }else if ($compa == '03'){
            $koneksi = 'mysql3';
        }else if ($compa == '05'){
            $koneksi = 'mysql_front_sub';
        }else if ($compa == '06'){
            $koneksi = 'mysql_front_inf';
        }
        return $koneksi;
    }

    public function index()
    {
        $konek = self::konek();
        $create_url = route('alat.create');
        $lokasi= MasterLokasi::pluck('nama_lokasi','kode_lokasi');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');

        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;
        
        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;
        
        return view('admin.alat.index',compact('create_url','period','lokasi', 'nama_lokasi','nama_company'));
    }


    public function anyData()
    {
        $konek = self::konek();
        return Datatables::of(Alat::on($konek)->with('masterlokasi')->where('status','Aktif')->orderBy('nama_alat','asc'))->make(true);
    }
    
    public function getDatabyID()
    {
        $konek = self::konek();
        return Datatables::of(LokasiAlat::on($konek)->with('operator')->where('kode_alat',request()->kode_customer)->orderBy('created_at','desc'))->make(true);
    }
    
    public function exportexcel(){
        $konek = self::konek();
        $kode_company = auth()->user()->kode_company;
        $nama = Company::find($kode_company);
        return Excel::download(new ListalatExport($kode_company), 'List Alat '.$nama->nama_company.'.xlsx');
    }
    
    public function detaillokasi($kode)
    {
        $konek = self::konek();
        $konek3 = self::konek3();
        $list_url= route('alat.index');
        $cust = Alat::on($konek)->find($kode);
        $Lokasi = MasterLokasi::pluck('nama_lokasi','kode_lokasi');
        $operator = Operator::on($konek3)->pluck('nama_operator','id');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        return view('admin.alat.indexlokasi', compact('list_url','nama_lokasi','nama_company','cust','Lokasi','period','operator'));
    }

    public function store_lokasi(Request $request)
    {
        $konek = self::konek();
        $kode_alat = $request->kode_alat;
        $cek_cust = Alat::on($konek)->find($kode_alat);
        
        // if ($request->kode_lokasi != $cek_cust->kode_lokasi) {
            LokasiAlat::on($konek)->create($request->all());
        
            $ceknpwp = LokasiAlat::on($konek)->where('kode_alat', $kode_alat)->orderBy('created_at','desc')->first();
            $update_info = [
                'kode_lokasi'=>$ceknpwp->kode_lokasi,
                'kode_operator'=>$ceknpwp->kode_operator,
            ];
            $cek_cust->update($update_info);
        // }
        
        $cek_cust->updated_at = $ceknpwp->updated_at;
        $cek_cust->updated_by = $ceknpwp->updated_by;
        $cek_cust->save();
        
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
        $comp = auth()->user()->kode_company;
        $nama_alat = $request->nama_alat;
        $no_asset_alat = $request->no_asset_alat;

        // $cek_nama = Alat::on($konek)->where('nama_alat',$nama_alat)->where('no_asset_alat',$no_asset_alat)->first();  
        // if ($cek_nama==null){
            // if ($comp == '02') {
                // $Alat = Alat::on($konek)->create($request->all());
            // }else {
                
            // }
        $Alat = Alat::on($konek)->create($request->all());
            
        $konversi_simbol = Alat::on($konek)->update(['no_asset_alat' => DB::raw("REPLACE(no_asset_alat,  ' ', '')")]);
            
        $message = [
                'success' => true,
                'title' => 'Simpan',
                'message' => 'Data telah di Disimpan.',
        ];
        return response()->json($message);
        // }else{
        //     $message = [
        //         'success' => false,
        //         'title' => 'Simpan',
        //         'message' => 'Nama Alat Sudah Ada',
        //     ];
        //     return response()->json($message);
        // }
    }

    public function edit_alat()
    {
        $konek = self::konek();
        $kode_alat = request()->id;
        $data = Alat::on($konek)->find($kode_alat);
        $output = array(
            'kode_alat'=>$data->kode_alat,
            'nama_alat'=>$data->nama_alat,
            'merk'=>$data->merk,
            'type'=>$data->type,
            'kapasitas'=>$data->kapasitas,
            'tahun'=>$data->tahun,
            'no_asset_alat'=>$data->no_asset_alat,
            'kode_lokasi'=>$data->kode_lokasi,
            'status'=>$data->status,
        );
        return response()->json($output);
    }


    public function updateAjax(Request $request)
    {
        $konek = self::konek();
        $kode = $request->kode_alat;
        $asset_alat = $request->no_asset_alat;
        $cek_kapa = Alat::on($konek)->where('kode_alat',$kode)->first();
        $kode_alat = Pemakaian::on($konek)->where('kode_alat',$kode)->first();
        $asset_alat = Pemakaian::on($konek)->where('no_asset_alat',$asset_alat)->first();
        $kode_alat_ban = Pemakaianban::on($konek)->where('kode_alat',$kode)->first();
        
        // if ($kode_alat == null && $asset_alat == null && $kode_alat_ban == null){
            Alat::on($konek)->find($request->kode_alat)->update($request->all());
            
            $konversi_simbol = Alat::on($konek)->update(['no_asset_alat' => DB::raw("REPLACE(no_asset_alat,  ' ', '')")]);
            
            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data telah di Update.'
            ];
            return response()->json($message);
        
        // }else{
        //     $message = [
        //         'success' => false,
        //         'title' => 'Update',
        //         'message' => 'Alat ['.$request->nama_alat. ' / ' .$request->no_asset_alat.'] dipakai dalam transaksi.'
        //     ];
        //     return response()->json($message);
        // }
    }


    public function hapus_alat()
    {   
        $konek = self::konek();
        $kode = request()->id;
        $alat = Alat::on($konek)->find(request()->id);
        $asset_alat = $alat->no_asset_alat;
        $kode_alat = Pemakaian::on($konek)->where('kode_alat',$kode)->first();
        $asset_alat = Pemakaian::on($konek)->where('no_asset_alat',$asset_alat)->first();
        $kode_alat_ban = Pemakaianban::on($konek)->where('kode_alat',$kode)->first();

        if ($kode_alat == null && $asset_alat == null && $kode_alat_ban == null){
            $alat->delete();

            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Alat ['.$alat->nama_alat.'] telah dihapus.'
            ];
            return response()->json($message);
        } else {
            $message = [
                'success' => false,
                'title' => 'Update',
                'message' => 'Alat ['.$alat->nama_alat. ' / ' .$request->no_asset_alat.'] dipakai dalam transaksi.'
            ];
            return response()->json($message);
        }
        
    }
}
