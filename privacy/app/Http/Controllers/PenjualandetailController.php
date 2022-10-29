<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\tb_item_bulanan;
use App\Models\tb_akhir_bulan;
use App\Models\Produk;
use App\Models\Konversi;
use App\Models\satuan;
use App\Models\Jasa;
use DB;

class PenjualandetailController extends Controller
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
        }
        return $koneksi;
    }

    public function index()
    {
        
        $create_url = route('penjualandetail.create');

        return view('admin.penjualandetail.index',compact('create_url'));

    }

    public function getDatabyID(){
        $konek = self::konek();
        return Datatables::of(PenjualanDetail::on($konek)->with('produk','satuan','jasa')->where('no_penjualan',request()->id)->orderBy('created_at','desc'))->addColumn('subtotal', function ($query){
                return $subtotal = $query->harga_jual * $query->qty;
            })
            ->addColumn('action', function ($query){
                return '<a href="javascript:;" data-toggle="tooltip" title="Edit" onclick="edit(\''.$query->id.'\',\''.$query->edit_url.'\')" class="btn btn-warning btn-xs"><i class="fa fa-edit"></i></a>'.'&nbsp'.
                    '<a href="javascript:;" data-toggle="tooltip" title="Hapus" onclick="del(\''.$query->id.'\',\''.$query->destroy_url.'\')" id="hapus" class="btn btn-danger btn-xs"> <i class="fa fa-times-circle"></i></a>';
            })->make(true);
    }

    public function stockProduk()
    {
        $konek = self::konek();
        $produk = Produk::on($konek)->find(request()->id);
        $jasa = Jasa::find(request()->id);
        if($produk != null && $jasa == null){
            $lokasi = auth()->user()->kode_lokasi;
            $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode','Open')->orwhere('reopen_status','true')->first();
            $monthly = tb_item_bulanan::on($konek)->where('kode_produk',request()->id)->where('periode',$cek_bulan->periode)->where('kode_lokasi',$lokasi)->orderBy('periode','desc')->first();
            
            $hpp = number_format($monthly->ending_amount/$monthly->ending_stock,2, '.', '');
            
             $output = array(
                'stok'=>$monthly->ending_stock,
                'hpp'=>$hpp,
                'tipe'=>$produk->tipe_produk,
                'kategori'=>$produk->kode_kategori,
                'no_mesin'=>$monthly->no_mesin,
            );
            return response()->json($output);
        }
        else if($produk == null && $jasa != null){
             $output = array(
                'stok'=>'-',
                'hpp'=>0,
                'tipe'=>'Jasa',
                'kategori'=>'Jasa',
                'no_mesin'=>'-',
                'kode_satuan'=>$jasa->satuan_item,
            );
            return response()->json($output);
        }
    }

    public function getharga()
    {
         $konek = self::konek();
         $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode','Open')->orwhere('reopen_status','true')->first();
         $monthly = tb_item_bulanan::on($konek)->where('kode_produk',request()->id)->where('partnumber',request()->part)->where('periode',$cek_bulan->periode)->where('kode_lokasi',auth()->user()->kode_lokasi)->first();
         // dd($monthly);
         
         $output = array(
            'stok'=>$monthly->ending_stock,
            'hpp'=>$monthly->hpp,
            'no_mesin'=>$monthly->no_mesin,
         );
         return response()->json($output);
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

    public function selectAjax(Request $request)
    {
        $konek = self::konek();
        $states = Konversi::on($konek)->where('kode_produk',$request->kode_produk)->where('nilai_konversi',1)->pluck("satuan_terbesar","kode_satuan")->all();
        
        return response()->json(['options'=>$states]);
    }

    public function selectpart(Request $request)
    {
        $konek = self::konek();
        $produk = Produk::on($konek)->find(request()->kode_produk);
        $jasa = Jasa::find(request()->kode_produk);
        if($produk != null && $jasa == null){
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
        else if($produk == null && $jasa != null){
            $states2 = '-';
                
            return response()->json(['options'=>$states2]);
        }
    }

    public function selectsatuan(Request $request)
    {
        $states2 = Jasa::where('kode_produk',$request->kode_produk)->pluck("satuan_item","satuan_item")->all();
                
        return response()->json(['options'=>$states2]);
    }

    public function store(Request $request)
    {   
        $konek = self::konek();
        $cek_penjualan = Penjualan::on($konek)->find($request->no_penjualan);
        $cek_status = $cek_penjualan->status;
        if($cek_status == 'POSTED'){  
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Penjualan: '.$cek_penjualan->no_penjualan.' sudah POSTED! Pastikan Anda tidak membuka menu PENJUALAN lebih dari 1',
            ];
            return response()->json($message);
        }

        $qty = $request->qty;
        $harga = $request->harga_jual;
        if($qty < 1 || $harga < 1){
            $message = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Nilai QTY/Harga jual tidak boleh kurang dari 1'
            ];
            return response()->json($message);
        }
        else{
                $produk = Produk::on($konek)->find(request()->kode_produk);
                $jasa = Jasa::find(request()->kode_produk);
                if($produk != null && $jasa == null){
                    $penjualandetail = PenjualanDetail::on($konek)->where('no_penjualan', $request->no_penjualan)->where('kode_produk', $request->kode_produk)->where('partnumber', $request->partnumber)->get();

                    $leng = count($penjualandetail);

                        if($leng > 0){
                            $message = [
                                'success' => false,
                                'title' => 'Gagal',
                                'message' => 'Produk Sudah Ada',
                                ];
                            return response()->json($message);
                        }
                        else{
                                $harga = $request->harga_jual;

                                $penjualandetail = PenjualanDetail::on($konek)->create($request->all());

                                $penjualandetail2 = PenjualanDetail::on($konek)->where('no_penjualan', $request->no_penjualan)->where('kode_produk', $request->kode_produk)->where('partnumber', $request->partnumber)->first();

                                $penjualandetail2->save(); 

                                $total_qty = 0;
                                $total_harga = 0;
                                $grand_total = 0;

                                $penjualandetail = PenjualanDetail::on($konek)->with('produk','satuan')->where('no_penjualan', $request->no_penjualan)
                                ->orderBy('created_at','desc')->get();

                                $data = Penjualan::on($konek)->find($request->no_penjualan);
                                $ppn = $data->ppn;
                                $diskon_persen = $data->diskon_persen;
                                $diskon_rp = $data->diskon_rp;

                                foreach ($penjualandetail as $row){
                                    $total_qty += $row->qty;
                                    $subtotal = $row->harga_jual * $row->qty;
                                    $total_harga += $subtotal;
                                    $grand_total = number_format($total_harga,0,",",".");
                                }

                                if($diskon_persen == 0 && $diskon_rp == 0){
                                    $ppn = ($data->ppn)/100;

                                    $penjualan = Penjualan::on($konek)->find($request->no_penjualan);
                                    $penjualan->grand_total = $total_harga + ($total_harga * $ppn);
                                    $penjualan->save(); 

                                    $hitung = PenjualanDetail::on($konek)->where('no_penjualan', $request->no_penjualan)->get();

                                    $leng = count($hitung);

                                    $update_penjualan = Penjualan::on($konek)->where('no_penjualan', $request->no_penjualan)->first();
                                    $update_penjualan->total_item = $leng;
                                    $update_penjualan->save();

                                    $message = [
                                    'success' => true,
                                    'title' => 'Update',
                                    'message' => 'Data telah disimpan'
                                    ];
                                    return response()->json($message);
                                }
                                else if($diskon_persen > 0 && $ppn == 0 && $diskon_rp == 0){
                                    $diskon_persen = ($data->diskon_persen)/100;

                                    $penjualan = Penjualan::on($konek)->find($request->no_penjualan);
                                    $penjualan->grand_total = $total_harga - ($total_harga * $diskon_persen);
                                    $penjualan->save(); 

                                    $hitung = PenjualanDetail::on($konek)->where('no_penjualan', $request->no_penjualan)->get();

                                    $leng = count($hitung);

                                    $update_penjualan = Penjualan::on($konek)->where('no_penjualan', $request->no_penjualan)->first();
                                    $update_penjualan->total_item = $leng;
                                    $update_penjualan->save();

                                    $message = [
                                    'success' => true,
                                    'title' => 'Update',
                                    'message' => 'Data telah disimpan'
                                    ];
                                    return response()->json($message);
                                } 
                                else if($diskon_rp > 0 && $ppn == 0 && $diskon_persen == 0){  
                                        $diskon_rp = $data->diskon_rp;

                                        $penjualan = Penjualan::on($konek)->find($request->no_penjualan);
                                        $penjualan->grand_total = $total_harga - $diskon_rp;
                                        $penjualan->save(); 

                                        $hitung = PenjualanDetail::on($konek)->where('no_penjualan', $request->no_penjualan)->get();

                                        $leng = count($hitung);

                                        $update_penjualan = Penjualan::on($konek)->where('no_penjualan', $request->no_penjualan)->first();
                                        $update_penjualan->total_item = $leng;
                                        $update_penjualan->save();

                                        $message = [
                                        'success' => true,
                                        'title' => 'Update',
                                        'message' => 'Data telah disimpan'
                                        ];
                                        return response()->json($message);
                                }
                                else if($diskon_rp > 0 && $ppn > 0 && $diskon_persen == 0){  
                                        $diskon_rp = $data->diskon_rp;
                                        $ppn = ($data->ppn)/100;

                                        $penjualan = Penjualan::on($konek)->find($request->no_penjualan);
                                        $penjualan->grand_total = $total_harga + ($total_harga * $ppn) - $diskon_rp;
                                        $penjualan->save(); 

                                        $hitung = PenjualanDetail::on($konek)->where('no_penjualan', $request->no_penjualan)->get();

                                        $leng = count($hitung);

                                        $update_penjualan = Penjualan::on($konek)->where('no_penjualan', $request->no_penjualan)->first();
                                        $update_penjualan->total_item = $leng;
                                        $update_penjualan->save();

                                        $message = [
                                        'success' => true,
                                        'title' => 'Update',
                                        'message' => 'Data telah disimpan'
                                        ];
                                        return response()->json($message);
                                }
                                else if($diskon_persen > 0 && $ppn > 0 && $diskon_rp == 0){  
                                        $diskon_persen = ($data->diskon_persen)/100;
                                        $ppn = ($data->ppn)/100;
                                        $total_ppn = $total_harga - ($total_harga * $diskon_persen);

                                        $penjualan = Penjualan::on($konek)->find($request->no_penjualan);
                                        $penjualan->grand_total = $total_harga - ($total_harga * $diskon_persen) + ($total_ppn * $ppn);
                                        $penjualan->save(); 

                                        $Penjualan = Penjualan::on($konek)->find($request->no_penjualan)->update($request->all());

                                        $nama = auth()->user()->name;
                                        $tmp = ['nama' => $nama,'aksi' => 'Update No. Penjualan: '.$request->no_penjualan.'.','created_by'=>$nama,'updated_by'=>$nama];
                                        //dd($tmp);
                                        user_history::on($konek)->create($tmp);

                                        $message = [
                                        'success' => true,
                                        'title' => 'Update',
                                        'message' => 'Data telah di Update'
                                        ];
                                        return response()->json($message);
                                }
                            }
                }else if($produk == null && $jasa != null){
                    $penjualandetail = PenjualanDetail::on($konek)->where('no_penjualan', $request->no_penjualan)->where('kode_produk', $request->kode_produk)->get();

                    $leng = count($penjualandetail);

                        if($leng > 0){
                            $message = [
                                'success' => false,
                                'title' => 'Gagal',
                                'message' => 'Produk Sudah Ada',
                                ];
                            return response()->json($message);
                        }
                        else{
                                $harga = $request->harga_jual;
                                $penjualandetail = PenjualanDetail::on($konek)->create($request->all());

                                $penjualandetail2 = PenjualanDetail::on($konek)->where('no_penjualan', $request->no_penjualan)->where('kode_produk', $request->kode_produk)->first();
                                $penjualandetail2->partnumber = '-';
                                
                                $penjualandetail2->save(); 

                                $total_qty = 0;
                                $total_harga = 0;
                                $grand_total = 0;

                                $penjualandetail = PenjualanDetail::on($konek)->with('produk','satuan')->where('no_penjualan', $request->no_penjualan)
                                ->orderBy('created_at','desc')->get();

                                $data = Penjualan::on($konek)->find($request->no_penjualan);
                                $ppn = $data->ppn;
                                $diskon_persen = $data->diskon_persen;
                                $diskon_rp = $data->diskon_rp;

                                foreach ($penjualandetail as $row){
                                    $total_qty += $row->qty;
                                    $subtotal = $row->harga_jual * $row->qty;
                                    $total_harga += $subtotal;
                                    $grand_total = number_format($total_harga,0,",",".");
                                }

                                if($diskon_persen == 0 && $diskon_rp == 0){
                                    $ppn = ($data->ppn)/100;

                                    $penjualan = Penjualan::on($konek)->find($request->no_penjualan);
                                    $penjualan->grand_total = $total_harga + ($total_harga * $ppn);
                                    $penjualan->save(); 

                                    $hitung = PenjualanDetail::on($konek)->where('no_penjualan', $request->no_penjualan)->get();

                                    $leng = count($hitung);

                                    $update_penjualan = Penjualan::on($konek)->where('no_penjualan', $request->no_penjualan)->first();
                                    $update_penjualan->total_item = $leng;
                                    $update_penjualan->save();

                                    $message = [
                                    'success' => true,
                                    'title' => 'Update',
                                    'message' => 'Data telah disimpan'
                                    ];
                                    return response()->json($message);
                                }
                                else if($diskon_persen > 0 && $ppn == 0 && $diskon_rp == 0){
                                    $diskon_persen = ($data->diskon_persen)/100;

                                    $penjualan = Penjualan::on($konek)->find($request->no_penjualan);
                                    $penjualan->grand_total = $total_harga - ($total_harga * $diskon_persen);
                                    $penjualan->save(); 

                                    $hitung = PenjualanDetail::on($konek)->where('no_penjualan', $request->no_penjualan)->get();

                                    $leng = count($hitung);

                                    $update_penjualan = Penjualan::on($konek)->where('no_penjualan', $request->no_penjualan)->first();
                                    $update_penjualan->total_item = $leng;
                                    $update_penjualan->save();

                                    $message = [
                                    'success' => true,
                                    'title' => 'Update',
                                    'message' => 'Data telah disimpan'
                                    ];
                                    return response()->json($message);
                                } 
                                else if($diskon_rp > 0 && $ppn == 0 && $diskon_persen == 0){  
                                        $diskon_rp = $data->diskon_rp;

                                        $penjualan = Penjualan::on($konek)->find($request->no_penjualan);
                                        $penjualan->grand_total = $total_harga - $diskon_rp;
                                        $penjualan->save(); 

                                        $hitung = PenjualanDetail::on($konek)->where('no_penjualan', $request->no_penjualan)->get();

                                        $leng = count($hitung);

                                        $update_penjualan = Penjualan::on($konek)->where('no_penjualan', $request->no_penjualan)->first();
                                        $update_penjualan->total_item = $leng;
                                        $update_penjualan->save();

                                        $message = [
                                        'success' => true,
                                        'title' => 'Update',
                                        'message' => 'Data telah disimpan'
                                        ];
                                        return response()->json($message);
                                }
                                else if($diskon_rp > 0 && $ppn > 0 && $diskon_persen == 0){  
                                        $diskon_rp = $data->diskon_rp;
                                        $ppn = ($data->ppn)/100;

                                        $penjualan = Penjualan::on($konek)->find($request->no_penjualan);
                                        $penjualan->grand_total = $total_harga + ($total_harga * $ppn) - $diskon_rp;
                                        $penjualan->save(); 

                                        $hitung = PenjualanDetail::on($konek)->where('no_penjualan', $request->no_penjualan)->get();

                                        $leng = count($hitung);

                                        $update_penjualan = Penjualan::on($konek)->where('no_penjualan', $request->no_penjualan)->first();
                                        $update_penjualan->total_item = $leng;
                                        $update_penjualan->save();

                                        $message = [
                                        'success' => true,
                                        'title' => 'Update',
                                        'message' => 'Data telah disimpan'
                                        ];
                                        return response()->json($message);
                                }
                                else if($diskon_persen > 0 && $ppn > 0 && $diskon_rp == 0){  
                                        $diskon_persen = ($data->diskon_persen)/100;
                                        $ppn = ($data->ppn)/100;

                                        $penjualan = Penjualan::on($konek)->find($request->no_penjualan);
                                        $penjualan->grand_total = $total_harga + ($total_harga * $ppn) - ($total_harga * $diskon_persen);
                                        $penjualan->save(); 

                                        $Penjualan = Penjualan::on($konek)->find($request->no_penjualan)->update($request->all());

                                        $nama = auth()->user()->name;
                                        $tmp = ['nama' => $nama,'aksi' => 'Update No. Penjualan: '.$request->no_penjualan.'.','created_by'=>$nama,'updated_by'=>$nama];
                                        //dd($tmp);
                                        user_history::on($konek)->create($tmp);

                                        $message = [
                                        'success' => true,
                                        'title' => 'Update',
                                        'message' => 'Data telah di Update'
                                        ];
                                        return response()->json($message);
                                }
                            }
                }   

            }
    }
    


    public function check(Request $request)
    { 
        $konek = self::konek();
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
            $penjualandetail = PenjualanDetail::on($konek)->where('no_penjualan', $request->no_penjualan)->where('kode_produk', $request->kode_produk)->where('partnumber', $request->partnumber)->get();

            $leng = count($penjualandetail);

                if($leng > 0){
                    $message = [
                        'success' => false,
                        'title' => 'Gagal',
                        'message' => 'Produk Sudah Ada',
                        ];
                    return response()->json($message);
                }
                else{
                    $penjualandetail = PenjualanDetail::on($konek)->create($request->all());

                    $produk = Produk::on($konek)->where('id', request()->kode_produk)->first();
                    $tipe_produk = $produk->tipe_produk;

                    $hitung = PenjualanDetail::on($konek)->where('no_penjualan', $request->no_penjualan)->get();

                    $leng = count($hitung);

                    $update_penjualan = Penjualan::on($konek)->where('no_penjualan', $request->no_penjualan)->first();
                    $update_penjualan->total_item = $leng;
                    $update_penjualan->save();

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
        $konek = self::konek();
        $produk = request()->kode_produk;
        $satuan = request()->satuan;

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
        $data = PenjualanDetail::on($konek)->find($id);
        $no_penjualan = $data->no_penjualan;
        $cek_header = Penjualan::on($konek)->find($no_penjualan);
        if($cek_header->type_ar == 'Sparepart'){
            $cek_produk = Produk::on($konek)->find($data->kode_produk);
            $nama_produk = $cek_produk->nama_produk;
        }else if($cek_header->type_ar == 'Unit'){
            $cek_produk = Produk::on($konek)->find($data->kode_produk);
            $nama_produk = $cek_produk->nama_produk;
        }else{
            $cek_produk = Jasa::find($data->kode_produk);
            $nama_produk = $cek_produk->nama_item;
        }

        $output = array(
            'no_penjualan'=>$data->no_penjualan,
            'kode_produk'=>$data->kode_produk,
            'nama_produk'=>$nama_produk,
            'kode_satuan'=>$data->kode_satuan,
            'qty'=>$data->qty,
            'harga'=>$data->harga_jual,
            'hpp'=>$data->harga,
            'id'=>$data->id,
        );
        return response()->json($output);
    }

    public function updateAjax(Request $request)
    {
        $konek = self::konek();
        $cek_penjualan = Penjualan::on($konek)->find($request->no_penjualan);
        $cek_status = $cek_penjualan->status;
        if($cek_status == 'POSTED'){  
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Penjualan: '.$cek_penjualan->no_penjualan.' sudah POSTED! Pastikan Anda tidak membuka menu PENJUALAN lebih dari 1',
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
          $request->validate([
            'no_penjualan'=> 'required',
            'qty'=> 'required',
            'harga'=>'required',
          ]);

                            $penjualandetail= PenjualanDetail::on($konek)->find($request->id)->update($request->all());

                            $harga = $request->harga_jual;
                            
                            $qty = $request->qty;

                            $penjualandetail2 = PenjualanDetail::on($konek)->where('no_penjualan', $request->no_penjualan)->where('kode_produk', $request->kode_produk)->where('partnumber', $request->partnumber)->first();

                            $total_qty = 0;
                            $total_harga = 0;
                            $grand_total = 0;

                            $penjualandetail = PenjualanDetail::on($konek)->where('no_penjualan', $request->no_penjualan)
                            ->orderBy('created_at','desc')->get();

                            $data = Penjualan::on($konek)->find($request->no_penjualan);
                            $ppn = $data->ppn;
                            $diskon_persen = $data->diskon_persen;
                            $diskon_rp = $data->diskon_rp;
                            
                            foreach ($penjualandetail as $row){
                                $total_qty += $row->qty;
                                $subtotal = $row->harga_jual * $row->qty;
                                $total_harga += $subtotal;
                                $grand_total = number_format($total_harga,0,",",".");
                            }
                            
                            if($diskon_persen == 0 && $diskon_rp == 0){
                                $ppn = ($data->ppn)/100;

                                $penjualan = Penjualan::on($konek)->find($request->no_penjualan);
                                $penjualan->grand_total = $total_harga + ($total_harga * $ppn);
                                $penjualan->save(); 

                                $hitung = PenjualanDetail::on($konek)->where('no_penjualan', $request->no_penjualan)->get();

                                $leng = count($hitung);

                                $update_penjualan = Penjualan::on($konek)->where('no_penjualan', $request->no_penjualan)->first();
                                $update_penjualan->total_item = $leng;
                                $update_penjualan->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }
                            else if($diskon_persen > 0 && $ppn == 0 && $diskon_rp == 0){
                                $diskon_persen = ($data->diskon_persen)/100;

                                $penjualan = Penjualan::on($konek)->find($request->no_penjualan);
                                $penjualan->grand_total = $total_harga - ($total_harga * $diskon_persen);
                                $penjualan->save(); 

                                $hitung = PenjualanDetail::on($konek)->where('no_penjualan', $request->no_penjualan)->get();

                                $leng = count($hitung);

                                $update_penjualan = Penjualan::on($konek)->where('no_penjualan', $request->no_penjualan)->first();
                                $update_penjualan->total_item = $leng;
                                $update_penjualan->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                        } 
                        else if($diskon_rp > 0 && $ppn == 0 && $diskon_persen == 0){  
                                $diskon_rp = $data->diskon_rp;

                                $penjualan = Penjualan::on($konek)->find($request->no_penjualan);
                                $penjualan->grand_total = $total_harga - $diskon_rp;
                                $penjualan->save(); 

                                $hitung = PenjualanDetail::on($konek)->where('no_penjualan', $request->no_penjualan)->get();

                                $leng = count($hitung);

                                $update_penjualan = Penjualan::on($konek)->where('no_penjualan', $request->no_penjualan)->first();
                                $update_penjualan->total_item = $leng;
                                $update_penjualan->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                        }
                        else if($diskon_rp > 0 && $ppn > 0 && $diskon_persen == 0){  
                                $diskon_rp = $data->diskon_rp;
                                $ppn = ($data->ppn)/100;

                                $penjualan = Penjualan::on($konek)->find($request->no_penjualan);
                                $penjualan->grand_total = ($total_harga - $diskon_rp) + (($total_harga - $diskon_rp) * $ppn);
                                $penjualan->save(); 

                                $hitung = PenjualanDetail::on($konek)->where('no_penjualan', $request->no_penjualan)->get();

                                $leng = count($hitung);

                                $update_penjualan = Penjualan::on($konek)->where('no_penjualan', $request->no_penjualan)->first();
                                $update_penjualan->total_item = $leng;
                                $update_penjualan->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                        }
                        else if($diskon_persen > 0 && $ppn > 0 && $diskon_rp == 0){  
                                $diskon_persen = ($data->diskon_persen)/100;
                                $ppn = ($data->ppn)/100;

                                $penjualan = Penjualan::on($konek)->find($request->no_penjualan);
                                $penjualan->grand_total = ($total_harga - ($total_harga * $diskon_persen)) + (($total_harga - ($total_harga * $diskon_persen)) * $ppn);
                                $penjualan->save(); 

                                $hitung = PenjualanDetail::on($konek)->where('no_penjualan', $request->no_penjualan)->get();

                                $leng = count($hitung);

                                $update_penjualan = Penjualan::on($konek)->where('no_penjualan', $request->no_penjualan)->first();
                                $update_penjualan->total_item = $leng;
                                $update_penjualan->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                        }
        }
    }


    public function destroy($penjualandetail)
    {
        $konek = self::konek();
        $penjualandetail = PenjualanDetail::on($konek)->find($penjualandetail);
        $cek_penjualan = Penjualan::on($konek)->find($penjualandetail->no_penjualan);
        $cek_status = $cek_penjualan->status;
        if($cek_status == 'POSTED'){  
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Penjualan: '.$cek_penjualan->no_penjualan.' sudah POSTED! Pastikan Anda tidak membuka menu PENJUALAN lebih dari 1',
            ];
            return response()->json($message);
        }
        
        try {    
                    $no_penjualan = $penjualandetail->no_penjualan;

                    $penjualandetail->delete();

                    $hitung = PenjualanDetail::on($konek)->where('no_penjualan', $penjualandetail->no_penjualan)->get();

                    $leng = count($hitung);

                    $update_pembelian = Penjualan::on($konek)->where('no_penjualan', $penjualandetail->no_penjualan)->first();
                    $update_pembelian->total_item = $leng;
                    $update_pembelian->save();

                    $total_qty = 0;
                    $total_harga = 0;
                    $grand_total = 0;

                    $penjualandetail = PenjualanDetail::on($konek)->with('produk','satuan')->where('no_penjualan', $no_penjualan)
                    ->orderBy('created_at','desc')->get();

                    $data = Penjualan::on($konek)->find($no_penjualan);
                    $ppn = $data->ppn;
                    $diskon_persen = $data->diskon_persen;
                    $diskon_rp = $data->diskon_rp;

                    foreach ($penjualandetail as $row){
                        $total_qty += $row->qty;
                        $subtotal = $row->harga * $row->qty;
                        $total_harga += $subtotal;
                        $grand_total = number_format($total_harga,0,",",".");
                    }

                    if($diskon_persen == 0 && $diskon_rp == 0){
                        $ppn = ($data->ppn)/100;

                        $penjualan = Penjualan::on($konek)->find($no_penjualan);
                        if($total_harga == 0){
                            $penjualan->grand_total = $total_harga;
                        }else{
                            $penjualan->grand_total = $total_harga + ($total_harga * $ppn);
                        }
                        $penjualan->save(); 

                        $message = [
                            'success' => true,
                            'title' => 'Sukses',
                            'message' => 'Data telah dihapus.'
                        ];
                        return response()->json($message);
                    }

                    else if($diskon_persen > 0 && $ppn == 0 && $diskon_rp == 0){
                        $diskon_persen = ($data->diskon_persen)/100;

                        $penjualan = Penjualan::on($konek)->find($no_penjualan);
                        if($total_harga == 0){
                            $penjualan->grand_total = $total_harga;
                        }else{
                            $penjualan->grand_total = $total_harga - ($total_harga * $diskon_persen);
                        }
                        $penjualan->save(); 

                        $message = [
                            'success' => true,
                            'title' => 'Sukses',
                            'message' => 'Data telah dihapus.'
                        ];
                        return response()->json($message);
                    }

                    else if($diskon_rp > 0 && $ppn == 0 && $diskon_persen == 0){
                        $penjualan = Penjualan::on($konek)->find($no_penjualan);
                        if($total_harga == 0){
                            $penjualan->grand_total = $total_harga;
                        }else{
                            $penjualan->grand_total = $total_harga - $diskon_rp;
                        }
                        $penjualan->save(); 

                        $message = [
                            'success' => true,
                            'title' => 'Sukses',
                            'message' => 'Data telah dihapus.'
                        ];
                        return response()->json($message);
                    }
                    else if($diskon_rp > 0 && $ppn > 0 && $diskon_persen == 0){
                        $diskon_rp = $data->diskon_rp;
                        $ppn = ($data->ppn)/100;

                        $penjualan = Penjualan::on($konek)->find($no_penjualan);
                        if($total_harga == 0){
                            $penjualan->grand_total = $total_harga;
                        }else{
                            $penjualan->grand_total = ($total_harga - $diskon_rp) + (($total_harga - $diskon_rp) * $ppn);
                        }
                        $penjualan->save(); 

                        $message = [
                            'success' => true,
                            'title' => 'Sukses',
                            'message' => 'Data telah dihapus.'
                        ];
                        return response()->json($message);
                    }
                    else if($diskon_persen > 0 && $ppn > 0 && $diskon_rp == 0){
                        $diskon_persen = ($data->diskon_persen)/100;
                        $ppn = ($data->ppn)/100;

                        $penjualan = Penjualan::on($konek)->find($no_penjualan);
                        if($total_harga == 0){
                            $penjualan->grand_total = $total_harga;
                        }else{
                            $penjualan->grand_total = $total_harga + ($total_harga * $ppn) - ($total_harga * $diskon_persen);
                        }
                        $penjualan->save(); 

                        $message = [
                            'success' => true,
                            'title' => 'Sukses',
                            'message' => 'Data telah dihapus.'
                        ];
                        return response()->json($message);
                    }

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
