<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\MemoDetail;
use App\Models\Memo;
use App\Models\RequestpembelianDetail;
use App\Models\RequestProduk;
use App\Models\Requestpembelian;
use App\Models\Pembelian;
use App\Models\Produk;
use App\Models\Jasa;
use App\Models\Nonstock;
use App\Models\satuan;
use App\Models\Konversi;
use App\Models\tb_akhir_bulan;
use App\Models\tb_item_bulanan;
use DB;
use Carbon;

class RequestpembeliandetailController extends Controller
{
    public function index()
    {
        $create_url = route('memodetail.create');

        return view('admin.memodetail.index',compact('create_url'));
    }

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

    public function getDatabyID(){
        $konek = self::konek();
        return Datatables::of(RequestpembelianDetail::on($konek)->where('no_request',request()->id)->orderBy('created_at','desc'))
           ->addColumn('action', function ($query){
                return 
                    '<a href="javascript:;" data-toggle="tooltip" title="Hapus" onclick="del(\''.$query->id.'\',\''.$query->destroy_url.'\')" id="hapus" class="btn btn-danger btn-xs"> <i class="fa fa-times-circle"></i></a>'.'&nbsp';
           })->make(true);
    }

    public function getDatabyID2(){
        $konek = self::konek();
        return Datatables::of(RequestProduk::on($konek)->with('produk')->where('no_request',request()->id))->make(true);
    }

    public function store(Request $request)
    {
        $konek = self::konek();
        $cek_request = Requestpembelian::on($konek)->find($request->no_request);
        $cek_status = $cek_request->status;

        if($cek_status == 'POSTED'){  
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Pembelian: '.$cek_request->no_request.' sudah POSTED! Pastikan Anda tidak membuka menu REQUEST PEMBELIAN lebih dari 1',
            ];
            return response()->json($message);
        }

        $cek = RequestpembelianDetail::on($konek)->where('no_memo', $request->no_memo)->where('no_request', $request->no_request)->first();
        if ($cek != null){
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'No Memo: '.$request->no_memo.' sudah ada.',
            ];
            return response()->json($message);
        }

        $memo = Memo::on($konek)->where('no_memo',$request->no_memo)->first();

        if($memo->status == 'OPEN'){
           $message = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'No Memo: '.$request->no_memo.' sedang OPEN.',
            ];
            return response()->json($message); 
        }
        $memodetail = MemoDetail::on($konek)->where('no_memo',$request->no_memo)->get();
        $updateqty = 0;

        foreach ($memodetail as $row){

            $cek_produk = Requestproduk::on($konek)->where('no_request',$request->no_request)->where('kode_produk',$row->kode_produk)->first();

            if($cek_produk == null){
                $tabel_baru = [
                    'no_request'=>$request->no_request,
                    'kode_produk'=>$row->kode_produk,
                    'qty'=>$row->qty,
                ];
                Requestproduk::on($konek)->create($tabel_baru);

            }else{
                $updateqty = $row->qty + $cek_produk->qty;
                $cek_produk->qty = $updateqty;
                $cek_produk->save();
            }
        }

        RequestpembelianDetail::on($konek)->create($request->all());
    
        $savememo = RequestpembelianDetail::on($konek)->where('no_memo', $request->no_memo)->first();
        $savememo->tgl_memo = $memo->tgl_memo;
        $savememo->kode_lokasi = $memo->kode_lokasi;
        $savememo->save();


        $countpembelian = Requestproduk::on($konek)->where('no_request', $request->no_request)->get();
        $lenger = count($countpembelian);
        $cek_request->total_item = $lenger;
        $cek_request->save();


        $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => 'Data telah di Disimpan.'
        ];
        return response()->json($message);    
    }

    public function destroy($reqprodukdetail)
    {
        $konek = self::konek();
        $reqdetail = RequestpembelianDetail::on($konek)->find($reqprodukdetail);
        $header = Requestpembelian::on($konek)->find($reqdetail->no_request);
        $memo = Memo::on($konek)->where('no_memo',$reqdetail->no_memo)->first();
        $memodetail = MemoDetail::on($konek)->where('no_memo',$reqdetail->no_memo)->get();
        $updateqty = 0;

        foreach ($memodetail as $row){

            $cek_produk = Requestproduk::on($konek)->where('no_request',$reqdetail->no_request)->where('kode_produk',$row->kode_produk)->first();

            $updateqty = $cek_produk->qty - $row->qty;
            $cek_produk->qty = $updateqty;
            $cek_produk->save();

            if($cek_produk->qty == 0){
                $cek_produk->delete();
            }
        }
        
        $lengproduk = Requestproduk::on($konek)->where('no_request',$reqdetail->no_request)->get();
        $leng = count($lengproduk);
        $header->total_item = $leng;
        $header->save();
        
        $reqdetail->delete();

        $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => $reqdetail->no_memo.' telah di hapus.'
        ];
        return response()->json($message);    
    }

}
