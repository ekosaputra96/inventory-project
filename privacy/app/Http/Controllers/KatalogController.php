<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Katalog;
use App\Models\tb_akhir_bulan;
use App\Models\MasterLokasi;
use App\Models\Company;
use Carbon;
use PDF;
use Excel;
use DB;

class KatalogController extends Controller
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
        }
        return $koneksi;
    }

    public function index()
    {
        $konek = self::konek();
        $create_url = route('katalog.create');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;
        
        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        $level = auth()->user()->level;
        return view('admin.katalog.index',compact('create_url','period', 'nama_lokasi','nama_company'));
    
    }

    public function anyData()
    {
        $konek = self::konek();
        return Datatables::of(Katalog::on($konek))
           ->addColumn('action', function ($query){
            return '<a href="javascript:;" onclick="edit(\''.$query->id.'\',\''.$query->edit_url.'\')" class="btn btn-warning btn-xs" data-toggle="tooltip" title="Edit"> <i class="fa fa-edit"></i></a>'.'&nbsp'.          
              '<a href="javascript:;" onclick="del(\''.$query->id.'\',\''.$query->destroy_url.'\')" id="hapus" class="btn btn-danger btn-xs" data-toggle="tooltip" title="Hapus"> <i class="fa fa-times-circle"></i> </a>';
                           })
            ->make(true);

    }

    public function store(Request $request)
    {
        $konek = self::konek();
        $level = auth()->user()->level;
        if($level == 'superadministrator'){
            $validator = $request->validate([
                'partnumber'=> 'required',
                'nama_item'=> 'required',
                'nama_item_en'=> 'required',
                'tipe'=> 'required',
                'ic'=> 'required',
              ]);

            try {
                $katalog = Katalog::on($konek)->create($request->all());
                $message = [
                'success' => true,
                'title' => 'Simpan',
                'message' => 'Data telah di Disimpan.'
                ];
                return response()->json($message);
            }catch (\Exception $exception){
                
                return response()->json(['errors' => $validator->errors()]);
            }
        }
        else{
            $message = [
                        'success' => false,
                        'title' => 'Simpan',
                        'message' => 'Anda tidak mempunyai akses tambah data',
                        ];
            return response()->json($message);
        }
        
    }

    public function edit_katalog()
    {
        $konek = self::konek();
        $kode_item = request()->id;
        $data = Katalog::on($konek)->find($kode_item);
        $output = array(
            'kode_item'=> $data->kode_item,
            'partnumber'=> $data->partnumber,
            'nama_item'=> $data->nama_item,
            'nama_item_en'=> $data->nama_item_en,
            'tipe'=> $data->tipe,
            'ic'=> $data->ic,
        );
        return response()->json($output);
    }


    public function updateAjax(Request $request)
    {
        $konek = self::konek();
        $level = auth()->user()->level;
        if($level == 'superadministrator'){
            $request->validate([
                'kode_item'=>'required',
                'partnumber'=> 'required',
                'nama_item'=> 'required',
                'nama_item_en'=> 'required',
                'tipe'=> 'required',
                'ic'=> 'required',
              ]);

              Katalog::on($konek)->find($request->kode_item)->update($request->all());
           
              $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data telah di Update.'
                ];
                return response()->json($message);
        }
        else{
            $message = [
            'success' => false,
            'title' => 'Update',
            'message' => 'Anda tidak mempunyai akses edit data'
            ];
            return response()->json($message);
        }
    }

    public function hapus_katalog()
    {   
        $konek = self::konek();
        $katalog = Katalog::on($konek)->find(request()->id);

        $katalog->delete();

        $message = [
            'success' => true,
            'title' => 'Update',
            'message' => 'Data ['.$katalog->kode_item.'] telah dihapus.'
        ];
        return response()->json($message);
    }
}
