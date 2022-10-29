<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Disassembling;
use App\Models\DisassemblingDetail;
use App\Models\Company;
use App\Models\Produk;
use App\Models\tb_akhir_bulan;
use App\Models\tb_item_bulanan;
use App\Models\tb_produk_history;
use App\Models\user_history;
use App\Models\MasterLokasi;
use PDF;
use Excel;
use DB;
use Alert;
use Carbon;
use DateTime;

class DisassemblingdetailController extends Controller
{
    public function index()
    {
        $create_url = route('disassemblingdetail.create');
        return view('admin.disassemblingdetail.index',compact('create_url'));
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
        $konek = static::konek();
        return Datatables::of(DisassemblingDetail::on($konek)->with('produk')->where('id',request()->id)->orderBy('created_at','desc'))
            ->addColumn('subtotal', function ($query){
            return $subtotal = $query->hpp * $query->qty;
           })
            ->addColumn('action', function ($query){
                return '<a href="javascript:;" data-toggle="tooltip" title="Edit" onclick="edit(\''.$query->id.'\',\''.$query->edit_url.'\')" class="btn btn-warning btn-xs"><i class="fa fa-edit"></i></a>'.'&nbsp'.
                    '<a href="javascript:;" data-toggle="tooltip" title="Hapus" onclick="del(\''.$query->id.'\',\''.$query->destroy_url.'\')" id="hapus" class="btn btn-danger btn-xs"> <i class="fa fa-times-circle"></i></a>'.'&nbsp';
           })->make(true);
    }

    public function getharga()
    {
        $konek = self::konek();
        $lokasi = auth()->user()->kode_lokasi;
        $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode', 'Open')->orwhere('reopen_status', 'true')->first();
        $monthly = tb_item_bulanan::on($konek)->where('kode_produk',request()->id)->where('partnumber',request()->part)->where('kode_lokasi',$lokasi)->where('periode',$cek_bulan->periode)->first();
        if($monthly != null && $monthly->ending_stock > 0){
            $hpp = number_format($monthly->ending_amount/$monthly->ending_stock,2, '.', '');
            $output = array(
                'stok'=>$monthly->ending_stock,
                'hpp'=>$hpp,
            );
        }
        else{
            $output = array(
                'stok'=>0,
                'hpp'=>0,
            );
        }
        return response()->json($output);
    }

    public function selectpart(Request $request)
    {
        $konek = self::konek();
        $produk = Produk::on($konek)->find(request()->kode_produk);
        if($produk != null){
            $cek_tipe = $produk->tipe_produk;
            $cek_kategori = $produk->kode_kategori;

            if($cek_tipe == 'Serial' && $cek_kategori == 'UNIT'){
                $cek_period = tb_akhir_bulan::on($konek)->where('status_periode','Open')->orwhere('status_periode','Disable')->first();
                $tgl_period = $cek_period->periode;

                $states2 = tb_item_bulanan::on($konek)->where('kode_produk',$request->kode_produk)->where('ending_stock', 1)->where('periode',$tgl_period)->pluck("partnumber","partnumber")->all();
                
                return response()->json(['options'=>$states2]);
            }else{
                $cek_period = tb_akhir_bulan::on($konek)->where('status_periode','Open')->orwhere('status_periode','Disable')->first();
                $tgl_period = $cek_period->periode;

                $states2 = tb_item_bulanan::on($konek)->where('kode_produk',$request->kode_produk)->pluck("partnumber","partnumber")->all();
                
                return response()->json(['options'=>$states2]);
            }
        }  
    }

    public function store(Request $request)
    {
        $konek = self::konek();
        $cek_disassembling = Disassembling::on($konek)->find($request->id);
        $cek_status = $cek_disassembling->status;
        if($cek_status == 'POSTED'){  
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Disassembling: '.$cek_disassembling->id.' sudah POSTED! Pastikan Anda tidak membuka menu Disassembling lebih dari 1',
            ];
            return response()->json($message);
        }

        if($request->partnumber == ''){
            $message = [
                        'success' => false,
                        'title' => 'Gagal',
                        'message' => 'Partnumber harus diisi.'
                ];
            return response()->json($message);
        }

        $qty = $request->qty;

        $kode_produk = $request->kode_produk;
        $cek_produk = Produk::on($konek)->find($kode_produk);
        if($cek_produk->tipe_produk == 'Serial'){
            if($cek_produk->kode_kategori == 'UNIT' || $cek_produk->kode_kategori == 'BAN'){
                $cekpart = DisassemblingDetail::on($konek)->where('id',$request->id)->where('kode_produk',$request->kode_produk)->where('partnumber',$request->partnumber)->first();
                if ($cekpart != null ){
                    $message = [
                        'success' => false,
                        'title' => 'Gagal',
                        'message' => 'Partnumber sudah ada.'
                    ];
                    return response()->json($message);
                }

                if($qty > 1){
                    $message = [
                        'success' => false,
                        'title' => 'Gagal',
                        'message' => 'Nilai QTY tidak boleh lebih dari 1'
                    ];
                    return response()->json($message);
                }
            }
        }
           
        $disassemblingdetail = DisassemblingDetail::on($konek)->where('id', $request->id)->where('kode_produk', $request->kode_produk)->where('partnumber', $request->partnumber)->get();

        $leng = count($disassemblingdetail);

        $disassemblingdetail = DisassemblingDetail::on($konek)->create($request->all());
        $produk = Produk::on($konek)->where('id', request()->kode_produk)->first();

        $hitung = DisassemblingDetail::on($konek)->where('id', $request->id)->get();
        $leng = count($hitung);

        $update_pemakaian = Disassembling::on($konek)->where('id', $request->id)->first();
        $update_pemakaian->total_item = $leng;
        $update_pemakaian->save();

        $message = [
            'success' => true,
            'title' => 'Update',
            'message' => 'Data telah Disimpan'
        ];
        return response()->json($message);
    }

    
    public function edit($disassemblingdetail)
    {
        $konek = self::konek();
        $id = $disassemblingdetail;
        $data = DisassemblingDetail::on($konek)->find($id);
        $cek_produk = Produk::on($konek)->find($data->kode_produk);
        $output = array(
            'id_detail'=> $data->id_detail,
            'id'=> $data->id,
            'kode_produk'=> $data->kode_produk,
            'nama_produk'=>$cek_produk->nama_produk,
            'partnumber'=> $data->partnumber,
            'hpp'=> $data->hpp,
            'qty'=> $data->qty,
        );
        return response()->json($output);
        
    }

    public function updateAjax(Request $request)
    {
        $konek = self::konek();
        $cek_disassembling = Disassembling::on($konek)->find($request->id);
        $cek_status = $cek_disassembling->status;
        if($cek_status == 'POSTED'){  
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Disassembling: '.$cek_disassembling->id.' sudah POSTED! Pastikan Anda tidak membuka menu Disassembling lebih dari 1',
            ];
            return response()->json($message);
        }

        $kode_produk = $request->kode_produk;
        $cek_produk = Produk::on($konek)->find($kode_produk);
        if($cek_produk->tipe_produk == 'Serial'){
            if($cek_produk->kode_kategori == 'UNIT' || $cek_produk->kode_kategori == 'BAN'){
                $disdetail = DisassemblingDetail::on($konek)->where('id_detail',$request->id_detail)->where('kode_produk',$request->kode_produk)->where('partnumber',$request->partnumber)->first();
                $qty = $disdetail->qty;
                if($qty > 1){
                    $message = [
                        'success' => false,
                        'title' => 'Gagal',
                        'message' => 'Nilai QTY tidak boleh lebih dari 1'
                    ];
                    return response()->json($message);
                }
            }
        }

        $disassemblingdetail= DisassemblingDetail::on($konek)->find($request->id_detail)->update($request->all());

        if($disassemblingdetail){
            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data telah di Update.'
            ];
            return response()->json($message);
        }else{
            $message = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Data Gagal di Update.'
            ];
            return response()->json($message);
        }
    }


    public function destroy($disassemblingdetail)
    {   
        $konek = self::konek();
        $disassemblingdetail = DisassemblingDetail::on($konek)->find($disassemblingdetail);
        $cek_disassembling = Disassembling::on($konek)->find($disassemblingdetail->id);
        $cek_status = $cek_disassembling->status;
        if($cek_status == 'POSTED'){  
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Disassembling: '.$cek_disassembling->id.' sudah POSTED! Pastikan Anda tidak membuka menu Disassembling lebih dari 1',
            ];
            return response()->json($message);
        }
        
        $disassemblingdetail->delete();
        $hitung = DisassemblingDetail::on($konek)->where('id', $disassemblingdetail->id)->get();
        $leng = count($hitung);

        $update_disassembling = Disassembling::on($konek)->where('id', $disassemblingdetail->id)->first();
        $update_disassembling->total_item = $leng;
        $update_disassembling->save();

        if($disassemblingdetail){
            $produk = Produk::on($konek)->find($disassemblingdetail->kode_produk);
            $produk->save();
        }   
        $message = [
            'success' => true,
            'title' => 'Sukses',
            'message' => 'Data telah dihapus.'
        ];
        return response()->json($message);
    }
}
