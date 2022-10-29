<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Penerimaan;
use App\Models\PenerimaanDetail;
use App\Models\Returpembelian;
use App\Models\ReturpembelianDetail;
use App\Models\Produk;
use App\Models\Konversi;
use App\Models\satuan;
use App\Models\tb_akhir_bulan;
use App\Models\tb_item_bulanan;
use DB;
use Carbon;

class ReturpembelianDetailController extends Controller
{
    public function index()
    {
        $create_url = route('returpembeliandetail.create');
        return view('admin.returpembeliandetail.index',compact('create_url'));
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
        return Datatables::of(ReturpembelianDetail::on($konek)->with('produk','satuan')->where('no_returpembelian',request()->id)->orderBy('created_at','desc'))
           ->addColumn('subtotal', function ($query){
            return $subtotal = ($query->harga * $query->qty) + ($query->landedcost * $query->qty);
           })->addColumn('action', function ($query){
                return '<a href="javascript:;" data-toggle="tooltip" title="Edit" onclick="edit(\''.$query->id.'\',\''.$query->edit_url.'\')" class="btn btn-warning btn-xs"><i class="fa fa-edit"></i></a>'.'&nbsp'.
                    '<a href="javascript:;" data-toggle="tooltip" title="Hapus" onclick="del(\''.$query->id.'\',\''.$query->destroy_url.'\')" id="hapus" class="btn btn-danger btn-xs"> <i class="fa fa-times-circle"></i></a>'.'&nbsp';
           })->make(true);
    }

    public function getinfo()
    {
         $konek = self::konek();
         $no_penerimaan = request()->no_penerimaan;
         $kode_produk = request()->kode_produk;

         $penerimaandetail = PenerimaanDetail::on($konek)->where('no_penerimaan',$no_penerimaan)->where('kode_produk',$kode_produk)->first();
         
         $kode_satuan = $penerimaandetail->kode_satuan;
         $partnumber = $penerimaandetail->partnumber;
         $no_mesin = $penerimaandetail->no_mesin;
         $qty = $penerimaandetail->qty;
         $qty_retur = $penerimaandetail->qty_retur;
         $harga = $penerimaandetail->harga;
         $landedcost = $penerimaandetail->landedcost;

         $produk = Produk::on($konek)->find(request()->kode_produk);
         $cek_tipe = $produk->tipe_produk;
         $cek_kategori = $produk->kode_kategori;

         $output = array(
                        'kode_satuan'=>$kode_satuan,
                        'partnumber'=>$partnumber,
                        'no_mesin'=>$no_mesin,
                        'qty'=>$qty - $qty_retur,
                        'harga'=>$harga,
                        'landedcost'=>$landedcost,
                        'tipe'=>$cek_tipe,
                        'kategori'=>$cek_kategori,
                    );

         return response()->json($output);
    }

    public function getstock()
    {
        $konek = self::konek();
        $penerimaandetail = PenerimaanDetail::on($konek)->where('no_penerimaan',request()->no_penerimaan)->where('kode_produk', request()->kode_produk)->where('partnumber', request()->partnumber)->first();

        $qty = $penerimaandetail->qty;
        $qty_retur = $penerimaandetail->qty_retur;

        $result = array(
            'qty'=>$qty - $qty_retur,
        );
        return response()->json($result);
    }

    public function qtyProduk()
    {
        $konek = self::konek();
        $penerimaandetail = PenerimaanDetail::on($konek)->where('no_penerimaan',request()->no_penerimaan)->where('kode_produk', request()->kode_produk)->first();

        $qty_e = request()->qty;
        $qty = $penerimaandetail->qty - $penerimaandetail->qty_retur;

        if($qty_e > $qty){
            return 'false';
        }else{
            return 'true';
        }
    }

    public function store(Request $request)
    {
        $konek = self::konek();
        $cek_returpembelian = Returpembelian::on($konek)->find($request->no_returpembelian);
        $cek_status = $cek_returpembelian->status;
        if($cek_status == 'POSTED'){
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Retur Pembelian: '.$cek_returpembelian->no_returpembelian.' sudah POSTED! Pastikan Anda tidak membuka menu RETUR PEMBELIAN lebih dari 1',
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

        $returpembeliandetail = ReturpembelianDetail::on($konek)->where('no_returpembelian', $request->no_returpembelian)->where('kode_produk', $request->kode_produk)->where('partnumber', $request->partnumber)->get();

        $cek_serial = Produk::on($konek)->where('id',$request->kode_produk)->first();
        $cek_tipe = $cek_serial->tipe_produk;
        $cek_kategori = $cek_serial->kode_kategori;

        if ($cek_tipe != 'Serial'){
            
                $leng = count($returpembeliandetail);
                    if($leng > 0){
                        $message = [
                            'success' => false,
                            'title' => 'Gagal',
                            'message' => 'Produk Sudah Ada'
                        ];
                        return response()->json($message);
                    }
            
        }else{
            if($cek_kategori != 'UNIT' || $cek_kategori != 'BAN'){
                $leng = count($returpembeliandetail);
                    if($leng > 0){
                        $message = [
                            'success' => false,
                            'title' => 'Gagal',
                            'message' => 'Produk Sudah Ada'
                        ];
                        return response()->json($message);
                    }
            }
        }

            $cek_terima = PenerimaanDetail::on($konek)->where('no_penerimaan',$request->no_penerimaan)->where('kode_produk',$request->kode_produk)->first();               
            $qty_retur = $cek_terima->qty_retur;
            $qty_final = $cek_terima->qty - $qty_retur;

            $produk = Produk::on($konek)->where('id', request()->kode_produk)->first();
            $tipe_produk = $produk->tipe_produk;
            $tipe_kategori = $produk->kode_kategori;

            if ($tipe_produk == 'Serial'){
                if($tipe_kategori == 'UNIT' || $tipe_kategori == 'BAN'){
                    $cek_part2 = ReturpembelianDetail::on($konek)->where('no_returpembelian',$request->no_returpembelian)->where('kode_produk',$request->kode_produk)->where('partnumber',$request->partnumber)->first();

                    if ($cek_part2 != null){
                        $message = [
                            'success' => false,
                            'title' => 'Gagal',
                            'message' => 'Part Number sudah pernah di input.'
                        ];
                        return response()->json($message);
                    }
                }
            }

            $returpembeliandetail = ReturpembelianDetail::on($konek)->create($request->all());
            $produk = Produk::on($konek)->where('id', request()->kode_produk)->first();
            $tipe_produk = $produk->tipe_produk;
            $tipe_kategori = $produk->kode_kategori;

            if ($tipe_produk == 'Serial'){
                if($tipe_kategori == 'UNIT' || $tipe_kategori == 'BAN'){
                    $update_returpembelian = ReturpembelianDetail::on($konek)->where('no_returpembelian', $request->no_returpembelian)->where('kode_produk', $request->kode_produk)->first();
                    $update_returpembelian->qty = 1;
                    $update_returpembelian->save();
                }
            }

            $hitung = ReturpembelianDetail::on($konek)->where('no_returpembelian', $request->no_returpembelian)->get();
            $leng = count($hitung);

            $update_returpembelian = Returpembelian::on($konek)->where('no_returpembelian', $request->no_returpembelian)->first();
            $update_returpembelian->total_item = $leng;
            $update_returpembelian->save();

            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data telah Disimpan'
            ];
            return response()->json($message);
                
    }


    public function edit($returpembeliandetail)
    {
        $konek = self::konek();
        $id = $returpembeliandetail;
        $data = ReturpembelianDetail::on($konek)->find($id);
        $cek_produk = Produk::on($konek)->find($data->kode_produk);
        $output = array(
            'no_returpembelian'=>$data->no_returpembelian,
            'no_penerimaan'=>$data->no_penerimaan,
            'kode_produk'=>$data->kode_produk,
            'nama_produk'=>$cek_produk->nama_produk,
            'kode_satuan'=>$data->kode_satuan,
            'qty'=>$data->qty,
            'harga'=>$data->harga,
            'landedcost'=>$data->landedcost,
            'id'=>$data->id,
        );
        return response()->json($output);
    }


    public function updateAjax(Request $request)
    {
        $konek = self::konek();
        $cek_returpembelian = Returpembelian::on($konek)->find($request->no_returpembelian);
        $cek_status = $cek_returpembelian->status;
        if($cek_status == 'POSTED'){
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Penerimaan: '.$cek_returpembelian->no_returpembelian.' sudah POSTED! Pastikan Anda tidak membuka menu RETUR PEMBELIAN lebih dari 1',
            ];
            return response()->json($message);
        }

        $cek_produk = Produk::on($konek)->find($request->kode_produk);
        $tipe = $cek_produk->tipe_produk;
        $kategori = $cek_produk->kode_kategori;

        if($tipe == 'Serial'){
            if($kategori == 'UNIT' || $kategori == 'BAN'){
                $qty = $request->qty;
                if($qty != 1){
                    $message = [
                        'success' => false,
                        'title' => 'Gagal',
                        'message' => 'Nilai QTY UNIT / BAN harus 1'
                    ];
                    return response()->json($message);
                }
                else{
                    $returpembeliandetail = ReturpembelianDetail::on($konek)->find($request->id)->update($request->all());
                    
                    $message = [
                        'success' => true,
                        'title' => 'Update',
                        'message' => 'Data telah di Update.'
                    ];
                    return response()->json($message);
                }
            }
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
                $request->validate([
                'no_penerimaan'=> 'required',
                'qty'=> 'required',
                'harga'=> 'required',
              ]);

              $returpembeliandetail = ReturpembelianDetail::on($konek)->find($request->id)->update($request->all());

              if($returpembeliandetail){
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


    public function destroy($returpembeliandetail)
    {
        $konek = self::konek();
        $cek_returpembelian2 = ReturpembelianDetail::on($konek)->find($returpembeliandetail);
        $cek_returpembelian = Returpembelian::on($konek)->find($cek_returpembelian2->no_returpembelian);
        $cek_status = $cek_returpembelian->status;
        if($cek_status == 'POSTED'){  
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Retur Pembelian: '.$cek_returpembelian->no_returpembelian.' sudah POSTED! Pastikan Anda tidak membuka menu RETUR PEMBELIAN lebih dari 1',
            ];
            return response()->json($message);
        }
        $returpembeliandetail = ReturpembelianDetail::on($konek)->find($returpembeliandetail);
        $cek_produk = Produk::on($konek)->where('id', $returpembeliandetail->kode_produk)->first();
        $cek_kategori = $cek_produk->kode_kategori;
        $cek_tipe = $cek_produk->tipe_produk;
        $cek_bulan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->first();

        if($cek_tipe == 'Serial' && $cek_bulan != null){
            if($cek_kategori == 'UNIT' || $cek_kategori == 'BAN'){
                $message = [
                    'success' => false,
                    'title' => 'Update',
                    'message' => 'Saat periode RE-OPEN, data UNIT / BAN tidak dapat di hapus.'
                ];
                return response()->json($message);
            }
        }
        
        if($cek_tipe == 'Serial' && $cek_bulan == null){
            if($cek_kategori == 'UNIT' || $cek_kategori == 'BAN'){
                    $returpembeliandetail->delete();
                    
                    $bulan = tb_akhir_bulan::on($konek)->where('status_periode','Open')->first();
                    $periode = $bulan->periode;
                    $bulanan = tb_item_bulanan::on($konek)->where('kode_produk', $returpembeliandetail->kode_produk)->where('partnumber',$returpembeliandetail->partnumber)->where('periode', $periode)->delete();

                    $hitung = ReturpembelianDetail::on($konek)->where('no_returpembelian', $returpembeliandetail->no_returpembelian)->get();
                    $leng = count($hitung);

                    $update_returpembelian = Returpembelian::on($konek)->where('no_returpembelian', $returpembeliandetail->no_returpembelian)->first();
                    $update_returpembelian->total_item = $leng;
                    $update_returpembelian->save();

                    if($returpembeliandetail){
                        $produk = Produk::on($konek)->find($returpembeliandetail->kode_produk);
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
        
        
                $returpembeliandetail->delete();

                $hitung = ReturpembelianDetail::on($konek)->where('no_returpembelian', $returpembeliandetail->no_returpembelian)->get();
                $leng = count($hitung);
                $update_penerimaan = Returpembelian::on($konek)->where('no_returpembelian', $returpembeliandetail->no_returpembelian)->first();
                $update_penerimaan->total_item = $leng;
                $update_penerimaan->save();

                if($returpembeliandetail){
                    $produk = Produk::on($konek)->find($returpembeliandetail->kode_produk);
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
