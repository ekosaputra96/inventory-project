<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\LokasiRak;
use App\Models\tb_akhir_bulan;
use App\Models\tb_item_bulanan;
use App\Models\Produk;
use App\Models\MasterLokasi;
use App\Models\Company;
use Carbon;
use DB;

class LokasiRakController extends Controller
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
        }else if ($compa == '22'){
            $koneksi = 'mysqlskt';
        }else if ($compa == '03'){
            $koneksi = 'mysqlemkl';   
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
        $create_url = route('lokasirak.create');
        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $Produk = Produk::on($konek)->select('kode_produk', DB::raw("concat(kode_produk,' - ',nama_produk) as produks"))->pluck('produks','kode_produk');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;
        
        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        return view('admin.lokasirak.index',compact('create_url','period','Produk','nama_lokasi','nama_company'));
    }

    public function anyData()
    {
        $konek = self::konek();
        $level = auth()->user()->level;
        $lokasi = auth()->user()->kode_lokasi;
        if($lokasi == 'HO'){
            return Datatables::of(LokasiRak::on($konek)->with('produk','Lokasi'))
           ->addColumn('action', function ($query){
                return '<a href="javascript:;" onclick="edit(\''.$query->id.'\',\''.$query->edit_url.'\')" class="btn btn-warning btn-xs" data-toggle="tooltip" title="Edit"><i class="fa fa-edit"></i></a>'.'&nbsp'.
                    '<a href="javascript:;" onclick="del(\''.$query->id.'\',\''.$query->destroy_url.'\')" id="hapus" class="btn btn-danger btn-xs" data-toggle="tooltip" title="Hapus"> <i class="fa fa-times-circle"></i></a>'.'&nbsp';
                           })
            ->make(true);
        }
        else{
            return Datatables::of(LokasiRak::on($konek)->with('produk','Lokasi')->where('kode_lokasi',auth()->user()->kode_lokasi))
           ->addColumn('action', function ($query){
                return '<a href="javascript:;" onclick="edit(\''.$query->id.'\',\''.$query->edit_url.'\')" class="btn btn-warning btn-xs" data-toggle="tooltip" title="Edit"><i class="fa fa-edit"></i></a>'.'&nbsp';
                           })
            ->make(true);
        }
    }

    public function stockProduk()
    {
        $konek = self::konek();
        $produk = Produk::on($konek)->find(request()->id);
        $lokasi = auth()->user()->kode_lokasi;
        $stok = tb_item_bulanan::on($konek)->where('kode_produk', request()->id)->where('kode_lokasi',$lokasi)->where('periode',$period->periode)->first();

            $output = array(
                'partnumber'=>$stok->partnumber,
            );
            return response()->json($output);
    }

    public function selectpart(Request $request)
    {
        $konek = self::konek();
        $produk = Produk::on($konek)->find(request()->kode_produk);
        $cek_tipe = $produk->tipe_produk;
        $cek_kategori = $produk->kode_kategori;

        if($cek_tipe == 'Serial' && $cek_kategori == 'UNIT'){
            $cek_period = tb_akhir_bulan::on($konek)->where('status_periode','Open')->orwhere('status_periode','Disable')->first();
            $tgl_period = $cek_period->periode;
            $states2 = DB::table('tb_item_bulanan')->where('kode_produk',$request->kode_produk)->where('ending_stock', 1)->where('kode_lokasi',auth()->user()->kode_lokasi)->where('periode',$tgl_period)->pluck("partnumber","partnumber")->all();
            return response()->json(['options'=>$states2]);
        }else{
            $cek_period = tb_akhir_bulan::on($konek)->where('status_periode','Open')->orwhere('status_periode','Disable')->first();
            $tgl_period = $cek_period->periode;
            $states2 = tb_item_bulanan::on($konek)->where('kode_produk',$request->kode_produk)->where('kode_lokasi',auth()->user()->kode_lokasi)->pluck("partnumber","partnumber")->all();
            return response()->json(['options'=>$states2]);
        }

    }

    public function store(Request $request)
    {
        $konek = self::konek();
        $cek_produk = LokasiRak::on($konek)->where('kode_produk',$request->kode_produk)->where('kode_lokasi',$request->kode_lokasi)->where('partnumber',$request->partnumber)->first();
        if ($cek_produk != null){
            $message = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Data sudah ada.'
            ];
            return response()->json($message);
        }
        LokasiRak::on($konek)->create($request->all());
            $message = [
                'success' => true,
                'title' => 'Simpan',
                'message' => 'Data telah di Disimpan.'
            ];
            return response()->json($message);
    }

    public function edit_lokasirak()
    {
        $konek = self::konek();
        $id = request()->id;
        $data = LokasiRak::on($konek)->find($id);
        $prod = Produk::on($konek)->where('id',$data->kode_produk)->pluck('nama_produk','nama_produk');

        $output = array(
            'id'=>$data->id,
            'kode_lokasi'=>$data->kode_lokasi,
            'kode_produk'=>$data->kode_produk,
            'partnumber'=>$data->partnumber,
            'rak'=>$data->lokasi_rak,
            'nama'=>$prod,
        );
        return response()->json($output);
    }

    public function updateAjax(Request $request)
    {
        $konek = self::konek();
        $produkrak = LokasiRak::on($konek)->where('id',$request->id)->first();
        $produkrak->update($request->all());

        $message = [
            'success' => true,
            'title' => 'Update',
            'message' => 'Data telah di Update.'
        ];
        return response()->json($message);
    }

    public function hapus_lokasirak()
    {   
        $konek = self::konek();
        $data = LokasiRak::on($konek)->find(request()->id);

        $data->delete();

        $message = [
            'success' => true,
            'title' => 'Update',
            'message' => 'Kode Produk:'.$data->kode_produk.' Part:'.$data->partnumber.' telah dihapus.'
        ];
        return response()->json($message);
    }
}
