<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\tb_akhir_bulan;
use App\Models\MasterLokasi;
use App\Models\Company;
use App\Models\Coa;
use App\Models\CoaDetail;
use App\Models\Costcenter;
use Carbon;
use DB;

class KategoriProdukController extends Controller
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
        $create_url = route('kategoriproduk.create');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        $Coa = CoaDetail::select('u5611458_gui_general_ledger_laravel.coa.kode_coa',DB::raw("concat(u5611458_gui_general_ledger_laravel.coa.account,' - ',u5611458_gui_general_ledger_laravel.coa.ac_description) as akun"))->join('u5611458_gui_general_ledger_laravel.coa','coa_detail.kode_coa','=','u5611458_gui_general_ledger_laravel.coa.kode_coa')->where('coa_detail.kode_company', auth()->user()->kode_company)->pluck('akun','u5611458_gui_general_ledger_laravel.coa.kode_coa');
        
        $Costcenter = Costcenter::where('kode_company', auth()->user()->kode_company)->pluck('desc','cost_center');
        
        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        return view('admin.kategoriproduk.index',compact('Costcenter','create_url','period', 'nama_lokasi','nama_company','Coa'));
    }

    public function anyData()
    {
        //BANYAK RELASI KARENA SETIAP COMPANY SETUP COA KATEGORINYA BERBEDA-BEDA
        return Datatables::of(KategoriProduk::with('coa','coa1','coa2','coa3','coa4','coa5','coa6','coa7','coa8','coa9','coa10','coa11','coa12','coa13','cost_gut','cost_gutjkt','cost_emkl','cost_pbm','cost_infra','cost_depo','cost_sub','cost_gut_persediaan','cost_gutjkt_persediaan','cost_emkl_persediaan','cost_pbm_persediaan','cost_infra_persediaan','cost_depo_persediaan','cost_sub_persediaan'))->make(true);
    }

    public function store(Request $request)
    {
        $kode_kategori = $request->kode_kategori;
        $nama_kategori = $request->nama_kategori;
        $cek_kategori = KategoriProduk::where('kode_kategori',$kode_kategori)->orwhere('nama_kategori',$nama_kategori)->first();
        if ($cek_kategori==null){
            KategoriProduk::create($request->all());
            $message = [
                'success' => true,
                'title' => 'Simpan',
                'message' => 'Data telah di Disimpan.'
            ];
            return response()->json($message);
        }
        else {
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Kode Kategori Sudah Ada',
            ];
            return response()->json($message);
        }
        
    }

    public function edit_kategori()
    {
        $kode_kategori = request()->id;
        $data = KategoriProduk::find($kode_kategori);

        $output = array(
            'kode_kategori'=>$data->kode_kategori,
            'nama_kategori'=>$data->nama_kategori,
            'status'=>$data->status,
            'coa_gut'=>$data->coa_gut,
            'coa_emkl'=>$data->coa_emkl,
            'coa_pbm'=>$data->coa_pbm,
            'coa_infra'=>$data->coa_infra,
            'coa_depo'=>$data->coa_depo,
            'coa_sub'=>$data->coa_sub,
            'coa_gutjkt'=>$data->coa_gutjkt,
            'coabiaya_infra'=>$data->coabiaya_infra,
            'coabiaya_gut'=>$data->coabiaya_gut,
            'coabiaya_emkl'=>$data->coabiaya_emkl,
            'coabiaya_pbm'=>$data->coabiaya_pbm,
            'coabiaya_depo'=>$data->coabiaya_depo,
            'coabiaya_sub'=>$data->coabiaya_sub,
            'coabiaya_gutjkt'=>$data->coabiaya_gutjkt,
            'cc_gut'=>$data->cc_gut,
            'cc_gutjkt'=>$data->cc_gutjkt,
            'cc_emkl'=>$data->cc_emkl,
            'cc_pbm'=>$data->cc_pbm,
            'cc_infra'=>$data->cc_infra,
            'cc_depo'=>$data->cc_depo,
            'cc_sub'=>$data->cc_sub,
            'cc_gut_persediaan'=>$data->cc_gut_persediaan,
            'cc_gutjkt_persediaan'=>$data->cc_gutjkt_persediaan,
            'cc_emkl_persediaan'=>$data->cc_emkl_persediaan,
            'cc_pbm_persediaan'=>$data->cc_pbm_persediaan,
            'cc_infra_persediaan'=>$data->cc_infra_persediaan,
            'cc_depo_persediaan'=>$data->cc_depo_persediaan,
            'cc_sub_persediaan'=>$data->cc_sub_persediaan,
        );
        return response()->json($output);
    }

    public function updateAjax(Request $request)
    {
        $kategori = $request->kode_kategori;
        $cek_kategori1 = Produk::on('mysqldepo')->where('kode_kategori',$kategori)->first();
        $cek_kategori2 = Produk::on('mysqlpbm')->where('kode_kategori',$kategori)->first();
        $cek_kategori3 = Produk::on('mysqlemkl')->where('kode_kategori',$kategori)->first();
        $cek_kategori4 = Produk::on('mysqlgut')->where('kode_kategori',$kategori)->first();
        $cek_kategori5 = Produk::on('mysql')->where('kode_kategori',$kategori)->first();
        $cek_kategori6 = Produk::on('mysqlinfra')->where('kode_kategori',$kategori)->first();
        
        // if ($cek_kategori1 != null || $cek_kategori2 != null || $cek_kategori3 != null || $cek_kategori4 != null || $cek_kategori5 != null || $cek_kategori6 != null){
        //     $message = [
        //         'success' => false,
        //         'title' => 'Update',
        //         'message' => 'Data Rusak!!! Kategori sudah terdapat pada produk salah satu company.'
        //     ];
        //     return response()->json($message);
        // }else {
            KategoriProduk::find($request->kode_kategori)->update($request->all());

            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data telah di Update.'
            ];
            return response()->json($message);
        // }
    }

    public function hapus_kategori()
    {   
        $kategori = request()->id;
        $kategoriproduk = KategoriProduk::find(request()->id);
        $cek_kategori1 = Produk::on('mysqldepo')->where('kode_kategori',$kategori)->first();
        $cek_kategori2 = Produk::on('mysqlpbm')->where('kode_kategori',$kategori)->first();
        $cek_kategori3 = Produk::on('mysqlemkl')->where('kode_kategori',$kategori)->first();
        $cek_kategori4 = Produk::on('mysqlgut')->where('kode_kategori',$kategori)->first();
        $cek_kategori5 = Produk::on('mysql')->where('kode_kategori',$kategori)->first();
        $cek_kategori6 = Produk::on('mysqlinfra')->where('kode_kategori',$kategori)->first();

        if ($cek_kategori1 == null || $cek_kategori2 == null || $cek_kategori3 == null || $cek_kategori4 == null || $cek_kategori5 == null || $cek_kategori6 == null){
            $kategoriproduk->delete();

            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data ['.$kategoriproduk->nama_kategori.'] telah dihapus.'
            ];
            return response()->json($message);
        } else {
            $message = [
                'success' => false,
                'title' => 'Update',
                'message' => 'Broken Input !!! Data ['.$kategoriproduk->nama_kategori.'] dipakai dalam produk salah satu company.'
            ];
            return response()->json($message);
        }
        
    }
}
