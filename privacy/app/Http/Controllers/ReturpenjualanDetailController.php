<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\ReturPenjualan;
use App\Models\ReturPenjualanDetail;
use App\Models\tb_item_bulanan;
use App\Models\tb_akhir_bulan;
use App\Models\Produk;
use App\Models\Konversi;
use App\Models\satuan;
use DB;
use Carbon;

class ReturpenjualanDetailController extends Controller
{
    public function index()
    {
        $create_url = route('returjualdetail.create');
        return view('admin.returjualdetail.index',compact('create_url'));
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
        }
        return $koneksi;
    }

    public function getDatabyID(){
        $konek = self::konek();
        return Datatables::of(ReturPenjualanDetail::on($konek)->with('produk','satuan')->where('no_retur_jual',request()->id)->orderBy('created_at','desc'))
           ->addColumn('subtotal', function ($query){
            return $subtotal = $query->harga_jual * $query->qty_retur;
           })->addColumn('action', function ($query){
                return '<a href="javascript:;" data-toggle="tooltip" title="Edit" onclick="edit(\''.$query->id.'\',\''.$query->edit_url.'\')" class="btn btn-warning btn-xs"><i class="fa fa-edit"></i></a>'.'&nbsp'.
                    '<a href="javascript:;" data-toggle="tooltip" title="Hapus" onclick="del(\''.$query->id.'\',\''.$query->destroy_url.'\')" id="hapus" class="btn btn-danger btn-xs"> <i class="fa fa-times-circle"></i></a>'.'&nbsp';
           })->make(true);
    }
    
    // public function hapusall()
    // {
    //     $konek = self::konek();
    //     $detail = ReturPenjualanDetail::on($konek)->where('no_retur_jual',request()->id)->delete();
    //     $total = ReturPenjualan::on($konek)->where('no_retur_jual',request()->id)->first();
    //     $total->total_item = 0;
    //     $total->save();
    //     $message = [
    //           'success' => true,
    //           'title' => 'Hapus',
    //           'message' => 'Semua detail No. Pemakaian: '.request()->id.' sudah DIHAPUS!',
    //       ];
    //       return response()->json($message);
    // }

    public function stockProduk()
    {
        $konek = self::konek();
        $lokasi = auth()->user()->kode_lokasi;
        $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode', 'Open')->orwhere('reopen_status', 'true')->first();
        $PenjualanDetail = PenjualanDetail::on($konek)->where('no_penjualan', request()->nojual)->where('kode_produk', request()->kode_produk)->first();

        $produk = Produk::on($konek)->find(request()->kode_produk);
        $cek_tipe = $produk->tipe_produk;
        $cek_kategori = $produk->kode_kategori;

        if($PenjualanDetail != null){
            if ($produk->tipe_produk == 'Serial' && $produk->kode_kategori == 'UNIT'){
                $output = array(
                'qty'=>$PenjualanDetail->qty-$PenjualanDetail->qty_retur,
                'harga'=>$PenjualanDetail->harga,
                'harga_jual'=>$PenjualanDetail->harga_jual,
                'satuan'=>$produk->kode_satuan,
                'partnumber'=>$PenjualanDetail->partnumber,
                'tipe'=>$cek_tipe,
                'kategori'=>$cek_kategori,
                );
            }else {
                $output = array(
                'qty'=>$PenjualanDetail->qty-$PenjualanDetail->qty_retur,
                'harga'=>$PenjualanDetail->harga,
                'harga_jual'=>$PenjualanDetail->harga_jual,
                'satuan'=>$produk->kode_satuan,
                'partnumber'=>$PenjualanDetail->partnumber,
                'tipe'=>$cek_tipe,
                'kategori'=>$cek_kategori,
                );
            }          
            return response()->json($output);
        }else{
            $output = array(
                'stok'=>0,
                'hpp'=>0,
            );
            return response()->json($output);
        }
        
    }

    public function getstock()
    {
        $konek = self::konek();
        $penjualandetail = PenjualanDetail::on($konek)->where('no_penjualan',request()->no_penjualan)->where('kode_produk', request()->kode_produk)->where('partnumber', request()->partnumber)->first();

        $qty = $penjualandetail->qty;
        $qty_retur = $penjualandetail->qty_retur;

        $result = array(
            'qty'=>$qty - $qty_retur,
        );
        return response()->json($result);
    }

    public function qtycheck()
    {
        $konek = self::konek();
        $produk = request()->id;
        $satuan = request()->satuan;
        $nilai_qty = request()->qty;
        $nilai_stok = request()->stok;

        $konversi = Konversi::on($konek)->where('kode_produk',$produk)->where('kode_satuan',$satuan)->first();
        $nilai = $konversi->nilai_konversi;
        $stok_final = $nilai_qty*$nilai;

        return response()->json($stok_final);
    }

    public function qtycheck2()
    {
        $konek = self::konek();
        $produk = request()->id;
        $nilai_qty = request()->qty;
        
        $cekdetail = PenjualanDetail::on($konek)->where('no_penjualan',request()->no_penjualan)->where('kode_produk',request()->id)->first();
        $qtypakai = $cekdetail->qty-$cekdetail->qty_retur;
        if ($nilai_qty > $qtypakai){
            return 'false';
        }else if ($nilai_qty == 0){
            return 'rusak';
        }else {
            return 'true';
        }
    }

    public function store(Request $request)
    {   
        $konek = self::konek();
        $PenjualanDetail = ReturPenjualanDetail::on($konek)->create($request->all());
        if($PenjualanDetail){
            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data telah disimpan.'
            ];
            return response()->json($message);
        }else{
            $message = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Data Gagal Disimpan.'
            ];
            return response()->json($message);
        }
    }


    public function check(Request $request)
    { 
        $konek = self::konek();
        $cek_pemakaian = ReturPenjualan::on($konek)->find($request->no_retur_jual);
        $cek_status = $cek_pemakaian->status;
        if($cek_status == 'POSTED'){
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Retur Penjualan: '.$cek_pemakaian->no_retur_jual.' sudah POSTED! Pastikan Anda tidak membuka menu RETUR PENJUALAN lebih dari 1',
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
            if($qty < 1){
                $message = [
                    'success' => false,
                    'title' => 'Gagal',
                    'message' => 'Nilai QTY tidak boleh kurang dari 1'
                ];
                return response()->json($message);
            }
            else{
                $PenjualanDetail = ReturPenjualanDetail::on($konek)->where('no_retur_jual', $request->no_retur_jual)->where('kode_produk', $request->kode_produk)->where('partnumber', $request->partnumber)->get();
                $leng = count($PenjualanDetail);
                    if($leng > 0){
                        $message = [
                            'success' => false,
                            'title' => 'Gagal',
                            'message' => 'Produk Sudah Ada',
                            ];
                        return response()->json($message);
                    }
                    else{
                        $PenjualanDetail = ReturPenjualanDetail::on($konek)->create($request->all());
                        $hitung = ReturPenjualanDetail::on($konek)->where('no_retur_jual', $request->no_retur_jual)->get();
                        $leng = count($hitung);

                        $update_pemakaian = ReturPenjualan::on($konek)->where('no_retur_jual', $request->no_retur_jual)->first();
                        $update_pemakaian->total_item = $leng;
                        $update_pemakaian->save();

                        $message = [
                            'success' => true,
                            'title' => 'Update',
                            'message' => 'Data telah Disimpan'
                            ];
                        return response()->json($message);
                    }
            }
    }

    public function qtyProduk2()
    {
        $produk = request()->kode_produk;
        $satuan = request()->satuan;
        $konek = self::konek();
        $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode','Open')->orwhere('reopen_status','true')->first();
        $lokasi = auth()->user()->kode_lokasi;
        $monthly = tb_item_bulanan::on($konek)->where('kode_produk',$produk)->where('kode_lokasi',$lokasi)->where('periode',$cek_bulan->periode)->orderBy('periode','desc')->first();
        $stok = $monthly->ending_stock;

        return response()->json($stok);
    }

    public function edit($penjualandetail)
    {
        $konek = self::konek();
        $id = $penjualandetail;
        $data = ReturPenjualanDetail::on($konek)->find($id);
        $cek_produk = Produk::on($konek)->find($data->kode_produk);
        $output = array(
            'no_retur_jual'=>$data->no_retur_jual,
            'no_penjualan'=>$data->no_penjualan,
            'kode_produk'=>$data->kode_produk,
            'nama_produk'=>$cek_produk->nama_produk,
            'kode_satuan'=>$data->kode_satuan,
            'qty_retur'=>$data->qty_retur,
            'harga'=>$data->harga,
            'harga_jual'=>$data->harga_jual,
            'id'=>$data->id,
        );
        return response()->json($output);
    }

    public function updateAjax(Request $request)
    {
        $konek = self::konek();
        $cek_pemakaian = ReturPenjualan::on($konek)->find($request->no_retur_jual);
        $cek_status = $cek_pemakaian->status;
        if($cek_status == 'POSTED'){  
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Retur Penjualan: '.$cek_pemakaian->no_retur_jual.' sudah POSTED! Pastikan Anda tidak membuka menu RETUR PENJUALAN lebih dari 1',
            ];
            return response()->json($message);
        }

        $qty = $request->qty_retur;
        if($qty < 1){
            $message = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Nilai QTY tidak boleh kurang dari 1'
            ];
            return response()->json($message);
        }
        else{

          $returpenjualandetail= ReturPenjualanDetail::on($konek)->find($request->id)->update($request->all());

          if($returpenjualandetail){
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
                    'message' => 'Data Gagal Disimpan.'
                ];
                return response()->json($message);
            }
        }
    }


    public function destroy($returpenjualandetail)
    {
        $konek = self::konek();
        $cek_returjual2 = ReturPenjualanDetail::on($konek)->find($returpenjualandetail);
        $cek_returjual = ReturPenjualan::on($konek)->find($cek_returjual2->no_retur_jual);
        $cek_status = $cek_returjual->status;
        if($cek_status == 'POSTED'){  
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Retur Penjualan: '.$cek_returjual->no_retur_jual.' sudah POSTED! Pastikan Anda tidak membuka menu RETUR PENJUALAN lebih dari 1',
            ];
            return response()->json($message);
        }
        $returpenjualandetail = ReturPenjualanDetail::on($konek)->find($returpenjualandetail);
        try {
            $returpenjualandetail->delete();
            $hitung = ReturPenjualanDetail::on($konek)->where('no_retur_jual', $returpenjualandetail->no_retur_jual)->get();
            $leng = count($hitung);
            $update_pemakaian = ReturPenjualan::on($konek)->where('no_retur_jual', $returpenjualandetail->no_retur_jual)->first();
            $update_pemakaian->total_item = $leng;
            $update_pemakaian->save();

            $message = [
                'success' => true,
                'title' => 'Sukses',
                'message' => 'Data telah dihapus.'
            ];
            return response()->json($message);

        }catch (\Exception $exception){
            $message = [
                'success' => false,
                'title' => 'Update',
                'message' => 'Data gagal dihapus.'
            ];
            return response()->json($message);
        }
    }
}
