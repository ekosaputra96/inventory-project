<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Carbon;
use App\Models\tb_akhir_bulan;
use App\Models\tb_item_bulanan;
use App\Models\Produk;
use App\Models\Pembelian;
use App\Models\Penerimaan;
use App\Models\Pemakaian;
use App\Models\PemakaianDetail;
use App\Models\sessions;
use App\Models\MasterLokasi;
use App\Models\Company;
use App\Models\Chat;
use App\User;
use Alert;

class HomeController extends Controller
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
        }else if ($compa == '06'){
            $koneksi = 'mysqlinfra';
        }
        return $koneksi;
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    { 
        $konek = self::konek();
        $lokasi = auth()->user()->kode_lokasi;
        $company = auth()->user()->kode_company;

        $pembelian_open = Pembelian::on($konek)->where('status','OPEN')->where('kode_lokasi',$lokasi)->get();
        $leng = $pembelian_open->count();

        $penerimaan_open = Penerimaan::on($konek)->where('status','OPEN')->where('kode_lokasi',$lokasi)->get();
        $leng2 = $penerimaan_open->count();

        $pemakaian_open = Pemakaian::on($konek)->where('status','OPEN')->where('kode_lokasi',$lokasi)->get();
        $leng3 = $pemakaian_open->count();

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');

        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;
        
        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        $level = auth()->user()->level;
        $nama_user = auth()->user()->username;

        $user_login = User::join('sessions', 'users.id', '=', 'sessions.user_id')
                ->get();
        $leng4 = $user_login->count();
        
        $depo = 'DEPO';
        $gut = 'GUT';
        $pbm = 'PBM';
        $inf = 'INFRA';
        $sub = 'SUB';
        $gutjkt = 'GUT';
        $skt = 'SKT';
        $emkl = 'EMKL';
        $pbmlama = 'PBM (LAMA)';
        
        $files = glob('/home/u5611458/public_html/aplikasi/gui_inventory_laravel/privacy/storage/debugbar/*'); // get all file names
        foreach($files as $file){ // iterate files
          if(is_file($file)) {
            unlink($file); // delete file
          }
        }
        
        $files2 = glob('/home/u5611458/public_html/aplikasi/gui_inventory_laravel/privacy/storage/logs/laravel.log'); // get all file names
        foreach($files2 as $file2){ // iterate files
          if(is_file($file2)) {
            unlink($file2); // delete file
          }
        }
        

        $user_login2 = User::select('users.name')
                ->where('users.kode_company',auth()->user()->kode_company)
                ->get();

        $chat = Chat::on($konek)
                ->join('users', 'users_chat.to_id', '=', 'users.id')
                ->get();  
        $leng_chat = $chat->count();

        $periods = tb_akhir_bulan::on($konek)->where('status_periode','Open')->orwhere('reopen_status','true')->first();

        $produk_min = tb_item_bulanan::on($konek)->join('produk', 'produk.id', '=', 'tb_item_bulanan.kode_produk')->where('min_qty','>','ending_stock')->where('periode',$periods->periode)->where('kode_lokasi',auth()->user()->kode_lokasi)->get();
        $leng_min = $produk_min->count();
        
        $produk_max = tb_item_bulanan::on($konek)->join('produk', 'produk.id', '=', 'tb_item_bulanan.kode_produk')->where('max_qty','<','ending_stock')->where('periode',$periods->periode)->where('kode_lokasi',auth()->user()->kode_lokasi)->get();
        $leng_max = $produk_max->count();

        return view('home',compact('period','leng','pembelian_open', 'penerimaan_open', 'pemakaian_open', 'user_login', 'leng2', 'leng3', 'leng4', 'nama_lokasi', 'level','nama_company','nama_user', 'user_login2', 'chat', 'leng_chat','inf','depo','gut','pbm','sub','gutjkt','skt','emkl','leng_min','produk_min','produk_max','leng_max'));
    }


    public function savechat(Request $request)
    {
        $konek = self::konek();
        $from_id = auth()->user()->id;
        $pesan = $request->pesan;
        $tujuan = $request->tujuan;

        $gettujuan_id = User::where('name',$tujuan)->first();
        $to_id = $gettujuan_id->id;

        $chat = [
            'from_id'=>$from_id,
            'to_id'=>$to_id,
            'chat'=>$pesan,
        ];

        $savechat = Chat::on($konek)->create($chat);
        return redirect()->back();
    }

}
