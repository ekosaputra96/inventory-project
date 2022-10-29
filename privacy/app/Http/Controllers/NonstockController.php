<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Nonstock;
use App\Models\satuan;
use App\Models\PembelianDetail;
use App\Models\tb_akhir_bulan;
use App\Models\MasterLokasi;
use App\Models\Company;
use App\Models\Coa;
use App\Models\Costcenter;
use Carbon;
use DB;

class NonstockController extends Controller
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
        $create_url = route('nonstock.create');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;
        
        $Costcenter = Costcenter::pluck('desc','cost_center');
        $Coa = Coa::select('kode_coa', DB::raw("concat(account,' - ',ac_description) as coas"))->where('position','DETAIL')->pluck('coas','kode_coa');
        
        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        return view('admin.nonstock.index',compact('Costcenter','create_url','period', 'nama_lokasi','nama_company','Coa'));
    }

    public function anyData()
    {
        return Datatables::of(Nonstock::with('coa')->orderBy('nama_item','asc'))->make(true); 
    }

    public function store(Request $request)
    {
        $nama_item = $request->nama_item;
        $cek_nama = Nonstock::where('nama_item',$nama_item)->first();
        if ($cek_nama==null){
            $Nonstock = Nonstock::create($request->all());
            Nonstock::where('nama_item', 'LIKE', '%&%')->update(['nama_item' => DB::raw("REPLACE(nama_item,  '&', 'DAN')")]);
            
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
                'message' => 'Nama Item Sudah Ada',
            ];
            return response()->json($message);
        }
        
    }

    public function edit_nonstock()
    {
        $kode_produk = request()->id;
        $data = Nonstock::find($kode_produk);
        $output = array(
            'kode_produk'=>$data->id,
            'nama_item'=>$data->nama_item,
            'coa'=>$data->coa,
            'cost_center'=>$data->cost_center,
        );
        return response()->json($output);
    }


    public function updateAjax(Request $request)
    {
        $cekcc = Coa::find($request->coa);
        if ($cekcc->cost_center != 'Y'){
            if ($request->cost_center != null){
                $message = [
                    'success' => false,
                    'title' => 'Update',
                    'message' => 'Tidak dapat menambah Cost Center, COA status CC = FALSE.'
                ];
                return response()->json($message);
            }
        }
        Nonstock::find($request->kode_produk)->update($request->all());
        Nonstock::where('nama_item', 'LIKE', '%&%')->update(['nama_item' => DB::raw("REPLACE(nama_item,  '&', 'DAN')")]);
        
        $message = [
            'success' => true,
            'title' => 'Update',
            'message' => 'Data telah di Update.'
        ];
        return response()->json($message);
    }

    public function hapus_nonstock()
    {   
        $nonstock = Nonstock::find(request()->id);
        $nonstock->delete();

        $message = [
            'success' => true,
            'title' => 'Update',
            'message' => 'Data ['.$nonstock->nama_item.'] telah dihapus.'
        ];
        return response()->json($message);
    }
}
