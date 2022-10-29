<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\MemoDetail;
use App\Models\Memo;
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

class MemodetailController extends Controller
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
        return Datatables::of(MemoDetail::on($konek)->with('produk')->where('no_memo',request()->id)->orderBy('created_at','desc'))
           ->addColumn('subtotal', function ($query){
            return $subtotal = $query->harga * $query->qty;
           })->addColumn('action', function ($query){
                return '<a href="javascript:;" data-toggle="tooltip" title="Edit" onclick="edit(\''.$query->id.'\',\''.$query->edit_url.'\')" class="btn btn-warning btn-xs"><i class="fa fa-edit"></i></a>'.'&nbsp'.
                    '<a href="javascript:;" data-toggle="tooltip" title="Hapus" onclick="del(\''.$query->id.'\',\''.$query->destroy_url.'\')" id="hapus" class="btn btn-danger btn-xs"> <i class="fa fa-times-circle"></i></a>'.'&nbsp';
           })->make(true);
    }

    public function store(Request $request)
    {
        $konek = self::konek();
        $cek_memo = Memo::on($konek)->find($request->no_memo);
        $cek_status = $cek_memo->status;
        if($cek_status == 'POSTED'){  
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Pembelian: '.$cek_memo->no_pembelian.' sudah POSTED! Pastikan Anda tidak membuka menu PEMBELIAN lebih dari 1',
            ];
            return response()->json($message);
        }

        $cek_sama = MemoDetail::on($konek)->where('no_memo',$request->no_memo)->where('kode_produk',$request->kode_produk)->first();

        if ($cek_sama == null){
            MemoDetail::on($konek)->create($request->all());

            $memodetail = MemoDetail::on($konek)->where('no_memo', $request->no_memo)->get();
            $lenger = count($memodetail);
            $cek_memo->total_item = $lenger;
            $cek_memo->save();

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
                'message' => 'Kode Produk Sudah Ada',
            ];
            return response()->json($message);
        }
       
    }

    public function edit($memodetail)
    {
        $konek = self::konek();
        $id = $memodetail;
        $data = MemoDetail::on($konek)->find($id);
        $no_memo = $data->no_memo;
        $cek_header = Memo::on($konek)->find($no_memo);
        $cek_produk = Produk::on($konek)->find($data->kode_produk);
        $nama_produk = $cek_produk->nama_produk;

        $output = array(
            'id'=>$data->id,
            'no_memo'=>$data->no_memo,
            'kode_produk'=>$data->kode_produk,
            'qty'=>$data->qty,
        );
        return response()->json($output);
    }

    public function updateAjax(Request $request)
    {
        $konek = self::konek();
        $cek_memo = Memo::on($konek)->find($request->no_memo);
        $cek_status = $cek_memo->status;

        if($cek_status == 'POSTED'){  
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Memo: '.$cek_memo->no_memo.' sudah POSTED! Pastikan Anda tidak membuka menu MEMO lebih dari 1',
            ];
            return response()->json($message);
        }

        $qty = $request->qty;
        if($qty < 1){
            $message = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Nilai Qty tidak boleh kurang dari 1'
            ];
            return response()->json($message);
        }

        else{
            $request->validate([
                'no_memo'=> 'required',
                'qty'=> 'required',
            ]);
        }  
        $pemakaiandetail= MemoDetail::on($konek)->find($request->id)->update($request->all());

        $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => 'Data berhasil di update'
        ];
        return response()->json($message);

    }


    public function destroy($memodetail)
    {
        $konek = self::konek();
        $id = $memodetail;
        $data = MemoDetail::on($konek)->find($id);
        $no_memo = $data->no_memo;
        $cek_header = Memo::on($konek)->find($no_memo);
        if($data->qty_to != 0){
            $message = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Produk telah di TO'
            ];
            return response()->json($message);
        }
        $data->delete();
        $cek_detail = MemoDetail::on($konek)->where('no_memo',$no_memo)->get();
        $lenger = count($cek_detail);
        $cek_header->total_item = $lenger;
        $cek_header->save();

        $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => 'Data berhasil di update'
        ];
        return response()->json($message);
    }

}
