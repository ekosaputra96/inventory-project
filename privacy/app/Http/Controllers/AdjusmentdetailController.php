<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Adjustment;
use App\Models\AdjustmentDetail;
use App\Models\Company;
use App\Models\Produk;
use App\Models\Konversi;
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

class AdjusmentdetailController extends Controller
{
    public function index()
    {
        $create_url = route('adjustmentdetail.create');
        return view('admin.adjustmentdetail.index',compact('create_url'));
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
        return Datatables::of(AdjustmentDetail::on($konek)->with('produk','satuan')->where('no_penyesuaian',request()->id)->orderBy('created_at','desc'))
            ->addColumn('subtotal', function ($query){
            return $subtotal = $query->harga * $query->qty;
           })
            ->addColumn('action', function ($query){
                return '<a href="javascript:;" data-toggle="tooltip" title="Edit" onclick="edit(\''.$query->id.'\',\''.$query->edit_url.'\')" class="btn btn-warning btn-xs"><i class="fa fa-edit"></i></a>'.'&nbsp'.
                    '<a href="javascript:;" data-toggle="tooltip" title="Hapus" onclick="del(\''.$query->id.'\',\''.$query->destroy_url.'\')" id="hapus" class="btn btn-danger btn-xs"> <i class="fa fa-times-circle"></i></a>'.'&nbsp';
           })->make(true);
    }

    public function selectAjax(Request $request)
    {
        $konek = self::konek();
        $states = Konversi::on($konek)->where('kode_produk',$request->kode_produk)->where('nilai_konversi',1)->pluck("satuan_terbesar","kode_satuan")->all();
        
        return response()->json(['options'=>$states]);
            
    }

    public function qtycheck()
    {
         $konek = static::konek();
         $produk = request()->id;
         $cek_produk = Produk::on($konek)->find($produk);
         $tipe_produk = $cek_produk->tipe_produk;
         $kategori = $cek_produk->kode_kategori;

         $output = array(
                'tipe'=>$tipe_produk,
                'kategori'=>$kategori,
                );
                 
         return response()->json($output);
    }

    public function stockProduk()
    {
        $konek = self::konek();
        $produk = Produk::on($konek)->find(request()->id);
        $lokasi = auth()->user()->kode_lokasi;
        $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode', 'Open')->orwhere('reopen_status', 'true')->first();
        $monthly = tb_item_bulanan::on($konek)->where('kode_produk',request()->id)->where('kode_lokasi',$lokasi)->where('periode',$cek_bulan->periode)->first();
        
        if($monthly != null){
            if($monthly->ending_stock != 0){
                $hpp = number_format($monthly->ending_amount/$monthly->ending_stock,2, '.', '');
            }
            else{
                $hpp = $monthly->hpp;
            }
            
            if ($produk->tipe_produk == 'Serial' && $produk->kode_kategori == 'UNIT'){
                $output = array(
                'stok'=>$monthly->ending_stock,    
                'amount'=>$monthly->ending_amount,         
                'hpp'=>$hpp,
                'tipe'=>$produk->tipe_produk,
                'kategori'=>$produk->kode_kategori,
                // 'satuan'=>$produk->kode_satuan,
                );
            }else {
                $output = array(
                'stok'=>$monthly->ending_stock,     
                'amount'=>$monthly->ending_amount,        
                'hpp'=>$hpp,
                'tipe'=>$produk->tipe_produk,
                'kategori'=>$produk->kode_kategori,
                // 'satuan'=>$produk->kode_satuan,
                );
            }          
            return response()->json($output);
        }else{
            $output = array(
                'monthly'=>'kosong',
                'stok'=>0,
                'hpp'=>0,
                'amount'=>0,
                'tipe'=>$produk->tipe_produk,
                'kategori'=>$produk->kode_kategori,
            );
            return response()->json($output);
        }
    }

    public function selectpart(Request $request)
    {
        $konek = self::konek();
        $cekproduk = tb_item_bulanan::on($konek)->where('kode_produk',$request->kode_produk)->first();
        if ($cekproduk != null) {
            $states2 = tb_item_bulanan::on($konek)->where('kode_produk',$request->kode_produk)->pluck("partnumber","partnumber")->all();
        }
        
        return response()->json(['options'=>$states2]);
            
    }

    public function getharga()
    {   
        $konek = self::konek();
        $lokasi = auth()->user()->kode_lokasi;
        $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode', 'Open')->orwhere('reopen_status', 'true')->first();
        $monthly = tb_item_bulanan::on($konek)->where('kode_produk',request()->id)->where('partnumber',request()->part)->where('kode_lokasi',$lokasi)->where('periode',$cek_bulan->periode)->first();
         
            if($monthly->ending_stock != 0){
                $hpp = number_format($monthly->ending_amount/$monthly->ending_stock,2, '.', '');
            }
            else{
                $hpp = $monthly->hpp;
            }

            $output = array(
                'stok'=>$monthly->ending_stock,
                'ending_amount'=>$monthly->ending_amount,
                'hpp'=>$hpp,
            );
            
        $output = array(
            'stok'=>$monthly->ending_stock,
            'ending_amount'=>$monthly->ending_amount,
            'hpp'=>$hpp,
        );
        return response()->json($output);
    }

    public function store(Request $request)
    {
        $konek = self::konek();
        $cek_adjustment = Adjustment::on($konek)->find($request->no_penyesuaian);
        $cek_status = $cek_adjustment->status;
        if($cek_status == 'POSTED'){  
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Adjustment: '.$cek_adjustment->no_penyesuaian.' sudah POSTED! Pastikan Anda tidak membuka menu ADJUSTMENT lebih dari 1',
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
                    $cekpart = AdjustmentDetail::on($konek)->where('no_penyesuaian',$request->no_penyesuaian)->where('kode_produk',$request->kode_produk)->where('partnumber',$request->partnumber)->first();
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

           
                $adjustmentdetail = AdjustmentDetail::on($konek)->where('no_penyesuaian', $request->no_penyesuaian)->where('kode_produk', $request->kode_produk)->where('partnumber', $request->partnumber)->get();

                $leng = count($adjustmentdetail);
                    
                $adjustmentdetail = AdjustmentDetail::on($konek)->create($request->all());
                $produk = Produk::on($konek)->where('id', request()->kode_produk)->first();

                $hitung = AdjustmentDetail::on($konek)->where('no_penyesuaian', $request->no_penyesuaian)->get();
                $leng = count($hitung);

                $update_pemakaian = Adjustment::on($konek)->where('no_penyesuaian', $request->no_penyesuaian)->first();
                $update_pemakaian->total_item = $leng;
                $update_pemakaian->save();

                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data telah Disimpan'
                    ];
                return response()->json($message);
                    
            
    }

    
    public function edit($adjustmentdetail)
    {
        $konek = self::konek();
        $no_penyesuaian = $adjustmentdetail;
        $data = AdjustmentDetail::on($konek)->find($no_penyesuaian);
        $cek_produk = Produk::on($konek)->find($data->kode_produk);
        $output = array(
            'id'=> $data->id,
            'no_penyesuaian'=> $data->no_penyesuaian,
            'tanggal'=> $data->tanggal,
            'kode_produk'=> $data->kode_produk,
            'nama_produk'=>$cek_produk->nama_produk,
            'kode_satuan'=> $data->kode_satuan,
            'partnumber'=> $data->partnumber,
            'harga'=> $data->harga,
            'qty'=> $data->qty,
            'keterangan'=> $data->keterangan,
            );
        return response()->json($output);
        
    }

    public function updateAjax(Request $request)
    {
        $konek = self::konek();
        $cek_adjustment = Adjustment::on($konek)->find($request->no_penyesuaian);
        $cek_status = $cek_adjustment->status;
        if($cek_status == 'POSTED'){  
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Adjustment: '.$cek_adjustment->no_penyesuaian.' sudah POSTED! Pastikan Anda tidak membuka menu ADJUSTMENT lebih dari 1',
            ];
            return response()->json($message);
        }

        $kode_produk = $request->kode_produk;
        $cek_produk = Produk::on($konek)->find($kode_produk);
        if($cek_produk->tipe_produk == 'Serial'){
            if($cek_produk->kode_kategori == 'UNIT' || $cek_produk->kode_kategori == 'BAN'){
                $adjdetail = AdjustmentDetail::on($konek)->where('no_penyesuaian',$request->no_penyesuaian)->where('kode_produk',$request->kode_produk)->where('partnumber',$request->partnumber)->first();
                $qty = $adjdetail->qty;
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
        

          $adjustmentdetail= AdjustmentDetail::on($konek)->find($request->id)->update($request->all());

          if($adjustmentdetail){
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


    public function destroy($adjustmentdetail)
    {   
        $konek = self::konek();
        $adjustmentdetail = AdjustmentDetail::on($konek)->find($adjustmentdetail);
        $cek_adjustment = Adjustment::on($konek)->find($adjustmentdetail->no_penyesuaian);
        $cek_status = $cek_adjustment->status;
        if($cek_status == 'POSTED'){  
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Adjustment: '.$cek_adjustment->no_penyesuaian.' sudah POSTED! Pastikan Anda tidak membuka menu ADJUSTMENT lebih dari 1',
            ];
            return response()->json($message);
        }
        
            $adjustmentdetail->delete();
            $hitung = AdjustmentDetail::on($konek)->where('no_penyesuaian', $adjustmentdetail->no_penyesuaian)->get();
            $leng = count($hitung);

            $update_adjustment = Adjustment::on($konek)->where('no_penyesuaian', $adjustmentdetail->no_penyesuaian)->first();
            $update_adjustment->total_item = $leng;
            $update_adjustment->save();

            if($adjustmentdetail){
                $produk = Produk::on($konek)->find($adjustmentdetail->kode_produk);
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
