<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Company;
use App\Models\tb_akhir_bulan;
use App\Models\MasterLokasi;
use Carbon;
use DB;

class CompanyController extends Controller
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
        $create_url = route('company.create');
        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;
        
        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;
        
        $company = Company::select('kode_company', DB::raw("concat(kode_company,' - ',nama_company) as compan"))->whereRaw('LENGTH(kode_company) = 2')->pluck('compan','kode_company');

        return view('admin.company.index',compact('create_url','period', 'nama_lokasi','company','nama_company'));

    }

    public function anyData()
    {
        return Datatables::of(Company::orderBy('kode_company'))->make(true);
    }

    public function store(Request $request)
    {   
        $kode_company = $request->kode_company;
        $nama_company = $request->nama_company;

        $cek_nama = Company::where('nama_company',$nama_company)->first();       
        if ($cek_nama==null){

            if ($request->tipe == "Cabang"){

                $comp = $request->kode_comp;
                $cek_comp = Company::where('kode_company', 'like', $comp.'%')->orderBy('kode_company','desc')->first();
                $data = Company::create($request->all());
                if (strlen($cek_comp->kode_company) == 4){
                    $kode = substr($cek_comp->kode_company,3);
                    $kode += 1;
                    $no = $request->all();
                    if (strlen($kode) == 2){
                        $kode2 = substr($cek_comp->kode_company,0,2);
                        $no['kode_company'] = $kode2.$kode;
                        $data->update($no);
                    }else {
                        $kode2 = substr($cek_comp->kode_company,0,3);
                        $no['kode_company'] = $kode2.$kode;
                        $data->update($no);
                    }
                }else {
                    $kode = $comp."01";
                    $no = $request->all();
                    $no['kode_company'] = $kode;
                    $data->update($no);
                }
            }else {
                Company::create($request->all());
            }

            $message = [
                'success' => true,
                'title' => 'Simpan',
                'message' => 'Data telah Disimpan.'
            ];
            return response()->json($message);
        }
        else{
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Gagal! Nama Company Sudah Ada.'
            ];
            return response()->json($message);
        }
    }

    public function edit_company()
    {
        $kode_company = request()->id;
        $data = Company::find($kode_company);
        $output = array(
            'kode_company'=>$data->kode_company,
            'nama_company'=>$data->nama_company,
            'alamat'=>$data->alamat,
            'telp'=>$data->telp,
            'npwp'=>$data->npwp,
            'status'=>$data->status,
        );
        return response()->json($output);
    }

    public function updateAjax(Request $request)
    {
        $nama_company = $request->nama_company;

        Company::find($request->kode_company)->update($request->all());

        $message = [
            'success' => true,
            'title' => 'Update',
            'message' => 'Data telah di Update.'
        ];
        return response()->json($message);
    }

    public function hapus_company()
    {   
        $company = Company::find(request()->id);

        $company->delete();

        $message = [
            'success' => true,
            'title' => 'Update',
            'message' => 'Data ['.$company->nama_company.'] telah dihapus.'
        ];
        return response()->json($message);
    }
}
