<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\PembelianDetail;
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

class PembeliandetailController extends Controller
{
    public function index()
    {
        $create_url = route('pembeliandetail.create');

        return view('admin.pembeliandetail.index',compact('create_url'));
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
        return Datatables::of(PembelianDetail::on($konek)->with('produk','satuan','jasa','nonstock')->where('no_pembelian',request()->id)->orderBy('created_at','desc'))
           ->addColumn('subtotal', function ($query){
            return $subtotal = $query->harga * $query->qty;
           })->addColumn('action', function ($query){
                return '<a href="javascript:;" data-toggle="tooltip" title="Edit" onclick="edit(\''.$query->id.'\',\''.$query->edit_url.'\')" class="btn btn-warning btn-xs"><i class="fa fa-edit"></i></a>'.'&nbsp'.
                    '<a href="javascript:;" data-toggle="tooltip" title="Hapus" onclick="del(\''.$query->id.'\',\''.$query->destroy_url.'\')" id="hapus" class="btn btn-danger btn-xs"> <i class="fa fa-times-circle"></i></a>'.'&nbsp';
           })->make(true);
    }

    public function stockProduk()
    {
         $konek = self::konek();
         $produk = Produk::on($konek)->find(request()->id);
         $pembelian = Pembelian::on($konek)->find(request()->no);
         $lokasi = auth()->user()->kode_lokasi;
         $period = tb_akhir_bulan::on($konek)->where('status_periode','Open')->orwhere('reopen_status','true')->first();
         $stok = tb_item_bulanan::on($konek)->where('kode_produk', request()->id)->where('kode_lokasi',$lokasi)->where('periode',$period->periode)->first();

         $jenis = $pembelian->jenis_po;
         if($jenis == 'Stock'){
            $cek_tipe = $produk->tipe_produk;
            $cek_kategori = $produk->kode_kategori;
            
            if($cek_tipe == 'Serial'){
                if($cek_kategori = 'UNIT' || $cek_kategori = 'BAN'){
                    $cek_stok = tb_item_bulanan::on($konek)->where('kode_produk', request()->id)->where('kode_lokasi',$lokasi)->where('periode',$period->periode)->where('ending_stock',1)->get();
                    $leng = count($cek_stok);

                    $output = array(
                        'stock'=>$leng,
                        'harga_beli'=>$produk->harga_beli,
                        'satuan'=>$produk->kode_satuan,
                    );

                    return response()->json($output);
                }
            }


            if ($stok != null){
                $output = array(
                'stock'=>$stok->ending_stock,
                'harga_beli'=>$produk->harga_beli,
                'satuan'=>$produk->kode_satuan,
                );
            }else {
                $output = array(
                'stock'=>0,
                'harga_beli'=>0,
                'satuan'=>$produk->kode_satuan,
                );
            }
            return response()->json($output);
         }
         else if($jenis == 'Jasa'){
            $jasa = Jasa::find(request()->id);
            $output = array(
                'stock'=>0,
                'hpp'=>0,
                'satuan'=>$jasa->satuan_item,
            );
            return response()->json($output);
         }else{
            $nonstock = Nonstock::find(request()->id);
            $output = array(
                'stock'=>0,
                'hpp'=>0,
                'satuan'=>$nonstock->satuan_item,
            );
            return response()->json($output);
         }
         
    }


    public function satuankonversi()
    {   
        $konek = self::konek();
        $produk = Produk::on($konek)->find(request()->id);
        $Satuan = Konversi::on($konek)->where('kode_produk', request()->id)
            ->first();
        
            $output = array(
                'stock'=>$produk->stok,
                'satuan'=>$produk->satuan->nama_satuan,
                'harga_beli'=>$produk->hpp,
                'konversi'=>$Satuan->satuan_terbesar,
            );
    
            return response()->json($output);
    }


    public function selectAjax(Request $request)
    {
        $konek = self::konek();
        $pembelian = Pembelian::on($konek)->find(request()->no);
        $jenis = $pembelian->jenis_po;
        if($jenis == 'Stock'){
            $states = Konversi::on($konek)->where('kode_produk',$request->kode_produk)->pluck("satuan_terbesar","kode_satuan")->all();
        
            return response()->json(['options'=>$states]);
        }
        else if($jenis == 'Jasa'){
            $states = Jasa::on('mysql2')->where('kode_produk',$request->kode_produk)->pluck("satuan_item","satuan_item")->all();
            // dd($states);
            return response()->json(['options'=>$states]);
        }  
        else if($jenis == 'Non-Stock'){
            $states = Nonstock::on('mysql2')->where('kode_produk',$request->kode_produk)->pluck("satuan_item","satuan_item")->all();
            // dd($states);
            return response()->json(['options'=>$states]);
        }  
    }
    
    public function selectAjax2(Request $request)
    {
        $konek = self::konek();
        $pembelian = Pembelian::on($konek)->find(request()->no);
        $jenis = $pembelian->jenis_po;
        if($jenis == 'Stock'){
            $states = Konversi::on($konek)->where('kode_produk',$request->kode_produk)->pluck("satuan_terbesar","kode_satuan")->all();
            return response()->json(['options'=>$states]);
        }
        else if($jenis == 'Jasa'){
            $states = Jasa::on('mysql2')->where('kode_produk',$request->kode_produk)->pluck("satuan_item","satuan_item")->all();
            // dd($states);
            return response()->json(['options'=>$states]);
        }  
        else if($jenis == 'Non-Stock'){
            $states = Nonstock::on('mysql2')->where('kode_produk',$request->kode_produk)->pluck("satuan_item","satuan_item")->all();
            // dd($states);
            return response()->json(['options'=>$states]);
        }  
    }

   public function store(Request $request)
    {
        $konek = self::konek();
        $cek_pembelian = Pembelian::on($konek)->find($request->no_pembelian);
        $cek_status = $cek_pembelian->status;
        if($cek_status == 'POSTED'){  
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Pembelian: '.$cek_pembelian->no_pembelian.' sudah POSTED! Pastikan Anda tidak membuka menu PEMBELIAN lebih dari 1',
            ];
            return response()->json($message);
        }

        $pembeliandetail = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->where('kode_produk', $request->kode_produk)->get();
        $leng = count($pembeliandetail);
        
        if($cek_pembelian->jenis_po != 'Non-Stock' && $cek_pembelian->jenis_po != 'Jasa'){
            $konversi = Konversi::on($konek)->where('kode_produk', $request->kode_produk)->first();
            if ($konversi == null) {
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'Nilai Konversi Satuan belum didaftarkan, silakan daftarkan terlebih dahulu.',
                ];
                return response()->json($message);
            }
            
            if($leng > 0){
                $message = [
                    'success' => false,
                    'title' => 'Gagal',
                    'message' => 'Produk Sudah Ada'
                ];
                return response()->json($message);
            }
        }else{
            $pembeliandetail2 = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->where('keterangan', $request->keterangan)->get();
            $leng2 = count($pembeliandetail2);
            if($leng2 > 0){
                $message = [
                    'success' => false,
                    'title' => 'Gagal',
                    'message' => 'Produk Sudah Ada'
                ];
                return response()->json($message);
            }
        }
        
        PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->where('keterangan', 'LIKE', '%&%')->update(['keterangan' => DB::raw("REPLACE(keterangan,  '&', 'DAN')")]);
                
                    $qty = $request->qty;
                    $harga = $request->harga;
                    if($qty < 1 || $harga < 1){
                        $message = [
                            'success' => false,
                            'title' => 'Gagal',
                            'message' => 'Nilai Qty/Harga tidak boleh kurang dari 1'
                        ];
                        return response()->json($message);
                    }
                    else{
                            $harga = $request->harga;
                            $total_transaksi = round($qty * $harga);
                            $pembeliandetail = PembelianDetail::on($konek)->create($request->all());
                            if($cek_pembelian->jenis_po != 'Non-Stock'){
                                $pembeliandetail2 = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->where('kode_produk', $request->kode_produk)->first();
                            }else{
                                $pembeliandetail2 = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->where('keterangan', $request->keterangan)->first();
                            }
                            $pembeliandetail2->total_transaksi = $total_transaksi;
                            $pembeliandetail2->save();
                            
                            $total_qty = 0;
                            $total_harga = 0;
                            $grand_total = 0;

                            $pembeliandetail = PembelianDetail::on($konek)->with('produk','satuan')->where('no_pembelian', $request->no_pembelian)
                            ->orderBy('created_at','desc')->get();

                            $data = Pembelian::on($konek)->find($request->no_pembelian);
                            $ppn = $data->ppn;
                            $pbbkb = $data->pbbkb;
                            $pbbkb_rp = $data->pbbkb_rp;
                            $diskon_persen = $data->diskon_persen;
                            $diskon_rp = $data->diskon_rp;
                            $ongkos_angkut = $data->ongkos_angkut;

                            foreach ($pembeliandetail as $row){
                                $total_qty += $row->qty;
                                $subtotal = $row->harga * $row->qty;
                                $total_harga += $subtotal;
                                $grand_total = number_format($total_harga,0,",",".");
                            }

                            if($diskon_persen == 0 && $diskon_rp == 0 && $pbbkb == 0 && $pbbkb_rp == 0){
                                $ppn = ($data->ppn)/100;

                                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                                $pembelian->grand_total = round(($total_harga + ($total_harga * $ppn)) + $ongkos_angkut);
                                $pembelian->save(); 

                                $hitung = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();

                                $leng = count($hitung);

                                $update_pembelian = Pembelian::on($konek)->where('no_pembelian', $request->no_pembelian)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }

                            else if($diskon_persen > 0 && $ppn == 0 && $diskon_rp == 0 && $pbbkb == 0 && $pbbkb_rp == 0){
                                $diskon_persen = ($data->diskon_persen)/100;

                                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                                $pembelian->grand_total = round(($total_harga - ($total_harga * $diskon_persen)) + $ongkos_angkut);
                                $pembelian->save(); 

                                $hitung = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();
                                $leng = count($hitung);
                                $update_pembelian = Pembelian::on($konek)->where('no_pembelian', $request->no_pembelian)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }

                            else if($diskon_rp > 0 && $ppn == 0 && $diskon_persen == 0 && $pbbkb == 0 && $pbbkb_rp == 0){
                                $diskon_rp = $data->diskon_rp;

                                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                                $pembelian->grand_total =  round(($total_harga - $diskon_rp) + $ongkos_angkut); 
                                $pembelian->save(); 

                                $hitung = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();

                                $leng = count($hitung);

                                $update_pembelian = Pembelian::on($konek)->where('no_pembelian', $request->no_pembelian)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }

                            else if($pbbkb > 0 && $ppn == 0 && $diskon_persen == 0 && $diskon_rp == 0 && $pbbkb_rp == 0){
                                $pbbkb = ($data->pbbkb)/100;

                                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                                $pembelian->grand_total = round(($total_harga + ($total_harga * $pbbkb)) + $ongkos_angkut);
                                $pembelian->save(); 

                                $hitung = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();

                                $leng = count($hitung);

                                $update_pembelian = Pembelian::on($konek)->where('no_pembelian', $request->no_pembelian)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }

                            else if($pbbkb_rp > 0 && $ppn == 0 && $diskon_persen == 0 && $diskon_rp == 0 && $pbbkb == 0){
                                $pbbkb_rp = $data->pbbkb_rp;

                                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                                $pembelian->grand_total = round($total_harga + $pbbkb_rp + $ongkos_angkut);
                                $pembelian->save(); 

                                $hitung = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();

                                $leng = count($hitung);

                                $update_pembelian = Pembelian::on($konek)->where('no_pembelian', $request->no_pembelian)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }

                            else if($diskon_rp > 0 && $ppn > 0 && $diskon_persen == 0 && $pbbkb == 0 && $pbbkb_rp == 0){  
                                $diskon_rp = $data->diskon_rp;
                                $ppn = ($data->ppn)/100;

                                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                                $pembelian->grand_total =  round((($total_harga - $diskon_rp) + (($total_harga - $diskon_rp) * $ppn)) + $ongkos_angkut); 
                                $pembelian->save(); 

                                $hitung = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();

                                $leng = count($hitung);

                                $update_pembelian = Pembelian::on($konek)->where('no_pembelian', $request->no_pembelian)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }

                            else if($diskon_persen > 0 && $ppn > 0 && $diskon_rp == 0 && $pbbkb == 0 && $pbbkb_rp == 0){  
                                $diskon_persen = ($data->diskon_persen)/100;
                                $ppn = ($data->ppn)/100;

                                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                                $pembelian->grand_total = round((($total_harga - ($total_harga * $diskon_persen)) + (($total_harga - ($total_harga * $diskon_persen)) * $ppn)) + $ongkos_angkut); 
                                $pembelian->save(); 

                                $hitung = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();

                                $leng = count($hitung);

                                $update_pembelian = Pembelian::on($konek)->where('no_pembelian', $request->no_pembelian)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }

                            else if($pbbkb > 0 && $ppn > 0 && $diskon_rp == 0 && $diskon_persen == 0 && $pbbkb_rp == 0){  
                                $ppn = ($data->ppn)/100;
                                $pbbkb = ($data->pbbkb)/100;

                                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                                $pembelian->grand_total =  round((($total_harga + ($total_harga * $ppn) + ($total_harga * $pbbkb))) + $ongkos_angkut); 
                                $pembelian->save(); 

                                $hitung = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();

                                $leng = count($hitung);

                                $update_pembelian = Pembelian::on($konek)->where('no_pembelian', $request->no_pembelian)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }

                            else if($pbbkb_rp > 0 && $ppn > 0 && $diskon_rp == 0 && $diskon_persen == 0 && $pbbkb == 0){  
                                $ppn = ($data->ppn)/100;
                                $pbbkb_rp = $data->pbbkb_rp;

                                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                                $pembelian->grand_total = round((($total_harga + ($total_harga * $ppn) + $pbbkb_rp)) + $ongkos_angkut); 
                                $pembelian->save(); 

                                $hitung = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();

                                $leng = count($hitung);

                                $update_pembelian = Pembelian::on($konek)->where('no_pembelian', $request->no_pembelian)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }

                            else if($pbbkb > 0 && $diskon_rp > 0 && $ppn == 0 && $diskon_persen == 0 && $pbbkb_rp == 0){  
                                $diskon_rp = $data->diskon_rp;
                                $pbbkb = ($data->pbbkb)/100;

                                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                                $pembelian->grand_total =  round((($total_harga - $diskon_rp) + (($total_harga - $diskon_rp) * $pbbkb)) + $ongkos_angkut); 
                                $pembelian->save(); 

                                $hitung = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();

                                $leng = count($hitung);

                                $update_pembelian = Pembelian::on($konek)->where('no_pembelian', $request->no_pembelian)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }

                            else if($pbbkb_rp > 0 && $diskon_rp > 0 && $ppn == 0 && $diskon_persen == 0 && $pbbkb == 0){  
                                $diskon_rp = $data->diskon_rp;
                                $pbbkb_rp = $data->pbbkb_rp;

                                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                                $pembelian->grand_total = round((($total_harga - $diskon_rp) + $pbbkb_rp + $ongkos_angkut)); 
                                $pembelian->save(); 

                                $hitung = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();

                                $leng = count($hitung);

                                $update_pembelian = Pembelian::on($konek)->where('no_pembelian', $request->no_pembelian)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }

                            else if($diskon_persen > 0 && $pbbkb > 0 && $diskon_rp == 0 && $ppn == 0 && $pbbkb_rp == 0){  
                                $diskon_persen = ($data->diskon_persen)/100;
                                $pbbkb = ($data->pbbkb)/100;

                                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                                $pembelian->grand_total = round((($total_harga - ($total_harga * $diskon_persen)) + (($total_harga - ($total_harga * $diskon_persen)) * $pbbkb)) + $ongkos_angkut); 
                                $pembelian->save(); 

                                $hitung = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();

                                $leng = count($hitung);

                                $update_pembelian = Pembelian::on($konek)->where('no_pembelian', $request->no_pembelian)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }

                            else if($diskon_persen > 0 && $pbbkb_rp > 0 && $diskon_rp == 0 && $ppn == 0 && $pbbkb == 0){  
                                $diskon_persen = ($data->diskon_persen)/100;
                                $pbbkb_rp = $data->pbbkb_rp;

                                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                                $pembelian->grand_total = round((($total_harga - ($total_harga * $diskon_persen)) + $pbbkb_rp + $ongkos_angkut)); 
                                $pembelian->save(); 

                                $hitung = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();

                                $leng = count($hitung);

                                $update_pembelian = Pembelian::on($konek)->where('no_pembelian', $request->no_pembelian)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }

                            else if($diskon_persen > 0 && $ppn > 0 && $pbbkb > 0 && $diskon_rp == 0 && $pbbkb_rp == 0){  
                                $diskon_persen = ($data->diskon_persen)/100;
                                $ppn = ($data->ppn)/100;
                                $pbbkb = ($data->pbbkb)/100;

                                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                                $pembelian->grand_total = round((($total_harga - ($total_harga * $diskon_persen)) + (($total_harga - ($total_harga * $diskon_persen)) * $ppn) + (($total_harga - ($total_harga * $diskon_persen)) * $pbbkb)) + $ongkos_angkut); 
                                $pembelian->save(); 

                                $hitung = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();

                                $leng = count($hitung);

                                $update_pembelian = Pembelian::on($konek)->where('no_pembelian', $request->no_pembelian)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }

                            else if($diskon_persen > 0 && $ppn > 0 && $pbbkb_rp > 0 && $diskon_rp == 0 && $pbbkb == 0){  
                                $diskon_persen = ($data->diskon_persen)/100;
                                $ppn = ($data->ppn)/100;
                                $pbbkb_rp = $data->pbbkb_rp;

                                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                                $pembelian->grand_total =  round((($total_harga - ($total_harga * $diskon_persen)) + (($total_harga - ($total_harga * $diskon_persen)) * $ppn) + $pbbkb_rp + $ongkos_angkut)); 
                                $pembelian->save(); 

                                $hitung = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();

                                $leng = count($hitung);

                                $update_pembelian = Pembelian::on($konek)->where('no_pembelian', $request->no_pembelian)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }

                            else if($diskon_rp > 0 && $ppn > 0 && $pbbkb > 0 && $diskon_persen == 0 && $pbbkb_rp == 0){  
                                $diskon_rp = $data->diskon_rp;
                                $ppn = ($data->ppn)/100;
                                $pbbkb = ($data->pbbkb)/100;

                                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                                $pembelian->grand_total =  round((($total_harga - $diskon_rp) + (($total_harga - $diskon_rp) * $ppn) + (($total_harga - $diskon_rp) * $pbbkb)) + $ongkos_angkut); 
                                $pembelian->save(); 

                                $hitung = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();

                                $leng = count($hitung);

                                $update_pembelian = Pembelian::on($konek)->where('no_pembelian', $request->no_pembelian)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }

                            else if($diskon_rp > 0 && $ppn > 0 && $pbbkb_rp > 0 && $diskon_persen == 0 && $pbbkb == 0){  
                                $diskon_rp = $data->diskon_rp;
                                $ppn = ($data->ppn)/100;
                                $pbbkb_rp = $data->pbbkb_rp;

                                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                                $pembelian->grand_total = round(($total_harga - $diskon_rp) + (($total_harga - $diskon_rp) * $ppn) + $pbbkb_rp + $ongkos_angkut); 
                                $pembelian->save(); 

                                $hitung = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();

                                $leng = count($hitung);

                                $update_pembelian = Pembelian::on($konek)->where('no_pembelian', $request->no_pembelian)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }
                        }

                

    }

    public function store_produk(Request $request)
    {
        $konek = self::konek();
        $validator = $request->validate([
                'nama_produk'=> 'required',
                'kode_kategori'=> 'required',
                'kode_merek'=> 'required',
                'kode_ukuran'=> 'required',
                'kode_satuan'=> 'required',
                'type'=> 'required',
                'harga_beli'=> 'required',
                'harga_jual'=> 'required',
                'hpp'=> 'required',
                'stok'=> 'required',
                'aktif'=> 'required',
        ]);

        try {
            $Produk = Produk::on($konek)->create($request->all());
            $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => 'Data telah disimpan.'
            ];
            return response()->json($message);
        }catch (\Exception $exception){
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Data Gagal disimpan',
                'error'=> $exception->getMessage()
                ];
            return response()->json($message);
        }
    }

    public function edit($pembeliandetail)
    {
        $konek = self::konek();
        $id = $pembeliandetail;
        $data = PembelianDetail::on($konek)->find($id);
        $no_pembelian = $data->no_pembelian;
        $cek_header = Pembelian::on($konek)->find($no_pembelian);
        if($cek_header->jenis_po == 'Stock'){
            $cek_produk = Produk::on($konek)->find($data->kode_produk);
            $nama_produk = $cek_produk->nama_produk;
        }else if($cek_header->jenis_po == 'Non-Stock'){
            $cek_produk = Nonstock::find($data->kode_produk);
            $nama_produk = $cek_produk->nama_item;
        }else{
            $cek_produk = Jasa::find($data->kode_produk);
            $nama_produk = $cek_produk->nama_item;
        }
        
        $output = array(
            'no_pembelian'=>$data->no_pembelian,
            'kode_produk'=>$data->kode_produk,
            'nama_produk'=>$nama_produk,
            'kode_satuan'=>$data->kode_satuan,
            'qty'=>$data->qty,
            'harga'=>$data->harga,
            'keterangan'=>$data->keterangan,
            'id'=>$data->id,
        );
        return response()->json($output);
    }

   public function updateAjax(Request $request)
    {
        $konek = self::konek();
        $cek_pembelian = Pembelian::on($konek)->find($request->no_pembelian);
        $cek_status = $cek_pembelian->status;
        if($cek_status == 'POSTED'){  
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Pembelian: '.$cek_pembelian->no_pembelian.' sudah POSTED! Pastikan Anda tidak membuka menu PEMBELIAN lebih dari 1',
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
            $satuan = PembelianDetail::on($konek)->find($request->id)->update($request->all());
            
            $produk = $request->kode_produk;
            $qty = $request->qty;
            $harga = $request->harga;
            $total_transaksi = $qty * $harga;

            if($cek_pembelian->jenis_po != 'Non-Stock'){
                $pembeliandetail2 = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->where('kode_produk', $request->kode_produk)->first();
            }else{
                $pembeliandetail2 = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->where('keterangan', $request->keterangan)->first();
            }
            $pembeliandetail2->total_transaksi = $total_transaksi;
            $pembeliandetail2->save(); 

            $total_qty = 0;
            $total_harga = 0;
            $grand_total = 0;

            $pembeliandetail = PembelianDetail::on($konek)->with('produk','satuan')->where('no_pembelian', $request->no_pembelian)->orderBy('created_at','desc')->get();

            $data = Pembelian::on($konek)->find($request->no_pembelian);
            $ppn = $data->ppn;
            $pbbkb = $data->pbbkb;
            $pbbkb_rp = $data->pbbkb_rp;
            $diskon_persen = $data->diskon_persen;
            $diskon_rp = $data->diskon_rp;
            $ongkos_angkut = $data->ongkos_angkut;

            foreach ($pembeliandetail as $row){
                $total_qty += $row->qty;
                $subtotal = $row->harga * $row->qty;
                $total_harga += $subtotal;
                $grand_total = number_format($total_harga,0,",",".");
            }

                            if($diskon_persen == 0 && $diskon_rp == 0 && $pbbkb == 0 && $pbbkb_rp == 0){
                                $ppn = ($data->ppn)/100;

                                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                                $pembelian->grand_total = ($total_harga + ($total_harga * $ppn)) + $ongkos_angkut;
                                $pembelian->save(); 

                                $hitung = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();

                                $leng = count($hitung);

                                $update_pembelian = Pembelian::on($konek)->where('no_pembelian', $request->no_pembelian)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }

                            else if($diskon_persen > 0 && $ppn == 0 && $diskon_rp == 0 && $pbbkb == 0 && $pbbkb_rp == 0){
                                $diskon_persen = ($data->diskon_persen)/100;

                                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                                $pembelian->grand_total = ($total_harga - ($total_harga * $diskon_persen)) + $ongkos_angkut;
                                $pembelian->save(); 

                                $hitung = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();
                                $leng = count($hitung);
                                $update_pembelian = Pembelian::on($konek)->where('no_pembelian', $request->no_pembelian)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }

                            else if($diskon_rp > 0 && $ppn == 0 && $diskon_persen == 0 && $pbbkb == 0 && $pbbkb_rp == 0){
                                $diskon_rp = $data->diskon_rp;

                                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                                $pembelian->grand_total =  ($total_harga - $diskon_rp) + $ongkos_angkut; 
                                $pembelian->save(); 

                                $hitung = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();

                                $leng = count($hitung);

                                $update_pembelian = Pembelian::on($konek)->where('no_pembelian', $request->no_pembelian)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }

                            else if($pbbkb > 0 && $ppn == 0 && $diskon_persen == 0 && $diskon_rp == 0 && $pbbkb_rp == 0){
                                $pbbkb = ($data->pbbkb)/100;

                                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                                $pembelian->grand_total = ($total_harga + ($total_harga * $pbbkb)) + $ongkos_angkut;
                                $pembelian->save(); 

                                $hitung = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();

                                $leng = count($hitung);

                                $update_pembelian = Pembelian::on($konek)->where('no_pembelian', $request->no_pembelian)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }

                            else if($pbbkb_rp > 0 && $ppn == 0 && $diskon_persen == 0 && $diskon_rp == 0 && $pbbkb == 0){
                                $pbbkb_rp = $data->pbbkb_rp;

                                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                                $pembelian->grand_total = $total_harga + $pbbkb_rp + $ongkos_angkut;
                                $pembelian->save(); 

                                $hitung = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();

                                $leng = count($hitung);

                                $update_pembelian = Pembelian::on($konek)->where('no_pembelian', $request->no_pembelian)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }

                            else if($diskon_rp > 0 && $ppn > 0 && $diskon_persen == 0 && $pbbkb == 0 && $pbbkb_rp == 0){  
                                $diskon_rp = $data->diskon_rp;
                                $ppn = ($data->ppn)/100;

                                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                                $pembelian->grand_total =  (($total_harga - $diskon_rp) + (($total_harga - $diskon_rp) * $ppn)) + $ongkos_angkut; 
                                $pembelian->save(); 

                                $hitung = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();

                                $leng = count($hitung);

                                $update_pembelian = Pembelian::on($konek)->where('no_pembelian', $request->no_pembelian)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }

                            else if($diskon_persen > 0 && $ppn > 0 && $diskon_rp == 0 && $pbbkb == 0 && $pbbkb_rp == 0){  
                                $diskon_persen = ($data->diskon_persen)/100;
                                $ppn = ($data->ppn)/100;

                                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                                $pembelian->grand_total =  (($total_harga - ($total_harga * $diskon_persen)) + (($total_harga - ($total_harga * $diskon_persen)) * $ppn)) + $ongkos_angkut; 
                                $pembelian->save(); 

                                $hitung = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();

                                $leng = count($hitung);

                                $update_pembelian = Pembelian::on($konek)->where('no_pembelian', $request->no_pembelian)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }

                            else if($pbbkb > 0 && $ppn > 0 && $diskon_rp == 0 && $diskon_persen == 0 && $pbbkb_rp == 0){  
                                $ppn = ($data->ppn)/100;
                                $pbbkb = ($data->pbbkb)/100;

                                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                                $totalpbbkb = $total_harga + ($total_harga * $pbbkb) + $ongkos_angkut;
                                $pembelian->grand_total =  $totalpbbkb + ($total_harga * $ppn); 
                                $pembelian->save(); 

                                $hitung = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();

                                $leng = count($hitung);

                                $update_pembelian = Pembelian::on($konek)->where('no_pembelian', $request->no_pembelian)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }

                            else if($pbbkb_rp > 0 && $ppn > 0 && $diskon_rp == 0 && $diskon_persen == 0 && $pbbkb == 0){  
                                $ppn = ($data->ppn)/100;
                                $pbbkb_rp = $data->pbbkb_rp;

                                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                                $totalpbbkb = $total_harga + $pbbkb_rp + $ongkos_angkut;
                                $pembelian->grand_total =  $totalpbbkb + ($total_harga * $ppn); 
                                $pembelian->save(); 

                                $hitung = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();

                                $leng = count($hitung);

                                $update_pembelian = Pembelian::on($konek)->where('no_pembelian', $request->no_pembelian)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }

                            else if($pbbkb > 0 && $diskon_rp > 0 && $ppn == 0 && $diskon_persen == 0 && $pbbkb_rp == 0){  
                                $diskon_rp = $data->diskon_rp;
                                $pbbkb = ($data->pbbkb)/100;

                                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                                $pembelian->grand_total =  (($total_harga - $diskon_rp) + (($total_harga - $diskon_rp) * $pbbkb)) + $ongkos_angkut; 
                                $pembelian->save(); 

                                $hitung = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();

                                $leng = count($hitung);

                                $update_pembelian = Pembelian::on($konek)->where('no_pembelian', $request->no_pembelian)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }

                            else if($pbbkb_rp > 0 && $diskon_rp > 0 && $ppn == 0 && $diskon_persen == 0 && $pbbkb == 0){  
                                $diskon_rp = $data->diskon_rp;
                                $pbbkb_rp = $data->pbbkb_rp;

                                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                                $pembelian->grand_total =  (($total_harga - $diskon_rp) + $pbbkb_rp + $ongkos_angkut); 
                                $pembelian->save(); 

                                $hitung = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();

                                $leng = count($hitung);

                                $update_pembelian = Pembelian::on($konek)->where('no_pembelian', $request->no_pembelian)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }

                            else if($diskon_persen > 0 && $pbbkb > 0 && $diskon_rp == 0 && $ppn == 0 && $pbbkb_rp == 0){  
                                $diskon_persen = ($data->diskon_persen)/100;
                                $pbbkb = ($data->pbbkb)/100;

                                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                                $pembelian->grand_total =  (($total_harga - ($total_harga * $diskon_persen)) + (($total_harga - ($total_harga * $diskon_persen)) * $pbbkb)) + $ongkos_angkut; 
                                $pembelian->save(); 

                                $hitung = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();

                                $leng = count($hitung);

                                $update_pembelian = Pembelian::on($konek)->where('no_pembelian', $request->no_pembelian)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }

                            else if($diskon_persen > 0 && $pbbkb_rp > 0 && $diskon_rp == 0 && $ppn == 0 && $pbbkb == 0){  
                                $diskon_persen = ($data->diskon_persen)/100;
                                $pbbkb_rp = $data->pbbkb_rp;

                                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                                $pembelian->grand_total =  (($total_harga - ($total_harga * $diskon_persen)) + $pbbkb_rp + $ongkos_angkut); 
                                $pembelian->save(); 

                                $hitung = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();

                                $leng = count($hitung);

                                $update_pembelian = Pembelian::on($konek)->where('no_pembelian', $request->no_pembelian)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }

                            else if($diskon_persen > 0 && $ppn > 0 && $pbbkb > 0 && $diskon_rp == 0 && $pbbkb_rp == 0){  
                                $diskon_persen = ($data->diskon_persen)/100;
                                $ppn = ($data->ppn)/100;
                                $pbbkb = ($data->pbbkb)/100;

                                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                                $totaldiskon = $total_harga - ($total_harga * $diskon_persen);
                                $totalpbbkb = $totaldiskon + ($totaldiskon * $pbbkb) + $ongkos_angkut; 
                                $pembelian->grand_total =  $totalpbbkb + ($totaldiskon * $ppn);
                                $pembelian->save(); 

                                $hitung = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();

                                $leng = count($hitung);

                                $update_pembelian = Pembelian::on($konek)->where('no_pembelian', $request->no_pembelian)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }

                            else if($diskon_persen > 0 && $ppn > 0 && $pbbkb_rp > 0 && $diskon_rp == 0 && $pbbkb == 0){  
                                $diskon_persen = ($data->diskon_persen)/100;
                                $ppn = ($data->ppn)/100;
                                $pbbkb_rp = $data->pbbkb_rp;

                                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                                $totaldiskon = $total_harga - ($total_harga * $diskon_persen);
                                $totalpbbkb = $totaldiskon + $pbbkb_rp + $ongkos_angkut;
                                $pembelian->grand_total =  $totalpbbkb + ($totaldiskon * $ppn); 
                                $pembelian->save(); 

                                $hitung = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();

                                $leng = count($hitung);

                                $update_pembelian = Pembelian::on($konek)->where('no_pembelian', $request->no_pembelian)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }

                            else if($diskon_rp > 0 && $ppn > 0 && $pbbkb > 0 && $diskon_persen == 0 && $pbbkb_rp == 0){  
                                $diskon_rp = $data->diskon_rp;
                                $ppn = ($data->ppn)/100;
                                $pbbkb = ($data->pbbkb)/100;

                                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                                $totaldiskon = $total_harga - $diskon_rp;
                                $totalpbbkb = ($totaldiskon + ($totaldiskon * $pbbkb)) + $ongkos_angkut;
                                $pembelian->grand_total =  $totalpbbkb + ($totaldiskon * $ppn); 
                                $pembelian->save(); 

                                $hitung = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();

                                $leng = count($hitung);

                                $update_pembelian = Pembelian::on($konek)->where('no_pembelian', $request->no_pembelian)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }

                            else if($diskon_rp > 0 && $ppn > 0 && $pbbkb_rp > 0 && $diskon_persen == 0 && $pbbkb == 0){  
                                $diskon_rp = $data->diskon_rp;
                                $ppn = ($data->ppn)/100;
                                $pbbkb_rp = $data->pbbkb_rp;

                                $pembelian = Pembelian::on($konek)->find($request->no_pembelian);
                                $totaldiskon = $total_harga - $diskon_rp;
                                $totalpbbkb = $totaldiskon + $pbbkb_rp + $ongkos_angkut;
                                $pembelian->grand_total = $totalpbbkb + ($totaldiskon * $ppn); 
                                $pembelian->save(); 

                                $hitung = PembelianDetail::on($konek)->where('no_pembelian', $request->no_pembelian)->get();

                                $leng = count($hitung);

                                $update_pembelian = Pembelian::on($konek)->where('no_pembelian', $request->no_pembelian)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }
                        
        }
      
    }


    public function destroy($pembeliandetail)
    {
        $konek = self::konek();
        $pembeliandetail = PembelianDetail::on($konek)->find($pembeliandetail);
        $cek_pembelian = Pembelian::on($konek)->find($pembeliandetail->no_pembelian);
        $cek_status = $cek_pembelian->status;
        if($cek_status == 'POSTED'){  
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Pembelian: '.$cek_pembelian->no_pembelian.' sudah POSTED! Pastikan Anda tidak membuka menu PEMBELIAN lebih dari 1',
            ];
            return response()->json($message);
        }
        
           try {
                $no_pembelian = $pembeliandetail->no_pembelian;
                $pembeliandetail->delete();

                $hitung = PembelianDetail::on($konek)->where('no_pembelian', $pembeliandetail->no_pembelian)->get();
                $leng = count($hitung);

                $update_pembelian = Pembelian::on($konek)->where('no_pembelian', $pembeliandetail->no_pembelian)->first();
                $update_pembelian->total_item = $leng;
                $update_pembelian->save();

                $total_qty = 0;
                $total_harga = 0;
                $grand_total = 0;

                $pembeliandetail = PembelianDetail::on($konek)->with('produk','satuan')->where('no_pembelian', $no_pembelian)
                    ->orderBy('created_at','desc')->get();

                $data = Pembelian::on($konek)->find($no_pembelian);
                $ppn = ($data->ppn)/100;
                $pbbkb = ($data->pbbkb)/100;
                $pbbkb_rp = $data->pbbkb_rp;
                $diskon_persen = ($data->diskon_persen)/100;
                $diskon_rp = $data->diskon_rp;
                $ongkos_angkut = $data->ongkos_angkut;

                if($diskon_persen == 0 && $diskon_rp == 0 && $pbbkb == 0 && $pbbkb_rp == 0){
                    foreach ($pembeliandetail as $row){
                        $total_qty += $row->qty;
                        $subtotal = $row->harga * $row->qty;
                        $total_harga += $subtotal;
                        $grand_total = number_format($total_harga,0,",",".");
                    }

                        $pembelian = Pembelian::on($konek)->find($no_pembelian);
                        $pembelian->grand_total = ($total_harga + ($total_harga * $ppn)) + $ongkos_angkut;
                        $pembelian->save(); 

                        $message = [
                            'success' => true,
                            'title' => 'Sukses',
                            'message' => 'Data telah dihapus.'
                        ];
                        return response()->json($message);
                    }
                else if($diskon_persen > 0 && $ppn == 0 && $diskon_rp == 0 && $pbbkb == 0 && $pbbkb_rp == 0){
                    foreach ($pembeliandetail as $row){
                        $total_qty += $row->qty;
                        $subtotal = $row->harga * $row->qty;
                        $total_harga += $subtotal;
                        $grand_total = number_format($total_harga,0,",",".");
                    }

                        $pembelian = Pembelian::on($konek)->find($no_pembelian);
                        $pembelian->grand_total = ($total_harga - ($total_harga * $diskon_persen)) + $ongkos_angkut;
                        $pembelian->save(); 

                        $message = [
                            'success' => true,
                            'title' => 'Sukses',
                            'message' => 'Data telah dihapus.'
                        ];
                        return response()->json($message);
                    }
                else if($diskon_rp > 0 && $ppn == 0 && $diskon_persen == 0 && $pbbkb == 0 && $pbbkb_rp == 0){
                    foreach ($pembeliandetail as $row){
                        $total_qty += $row->qty;
                        $subtotal = $row->harga * $row->qty;
                        $total_harga += $subtotal;
                        $grand_total = number_format($total_harga,0,",",".");
                    }

                        $pembelian = Pembelian::on($konek)->find($no_pembelian);
                        if ($total_harga <= 0){
                            $pembelian->grand_total = 0;
                        }else {
                            $pembelian->grand_total = ($total_harga - $diskon_rp) + $ongkos_angkut;
                        }               
                        $pembelian->save();

                        $message = [
                            'success' => true,
                            'title' => 'Sukses',
                            'message' => 'Data telah dihapus.'
                        ];
                        return response()->json($message);
                    }
                else if($pbbkb > 0 && $ppn == 0 && $diskon_persen == 0 && $diskon_rp == 0 && $pbbkb_rp == 0){
                    foreach ($pembeliandetail as $row){
                        $total_qty += $row->qty;
                        $subtotal = $row->harga * $row->qty;
                        $total_harga += $subtotal;
                        $grand_total = number_format($total_harga,0,",",".");
                    }

                        $pembelian = Pembelian::on($konek)->find($no_pembelian);
                        if ($total_harga <= 0){
                            $pembelian->grand_total = 0;
                        }else {
                            $pembelian->grand_total = ($total_harga + ($total_harga * $pbbkb)) + $ongkos_angkut;
                        }               
                        $pembelian->save();

                        $message = [
                            'success' => true,
                            'title' => 'Sukses',
                            'message' => 'Data telah dihapus.'
                        ];
                        return response()->json($message);
                    }
                else if($pbbkb_rp > 0 && $ppn == 0 && $diskon_persen == 0 && $diskon_rp == 0 && $pbbkb == 0){
                    foreach ($pembeliandetail as $row){
                        $total_qty += $row->qty;
                        $subtotal = $row->harga * $row->qty;
                        $total_harga += $subtotal;
                        $grand_total = number_format($total_harga,0,",",".");
                    }

                        $pembelian = Pembelian::on($konek)->find($no_pembelian);
                        if ($total_harga <= 0){
                            $pembelian->grand_total = 0;
                        }else {
                            $pembelian->grand_total = $total_harga + $pbbkb_rp + $ongkos_angkut;
                        }               
                        $pembelian->save();

                        $message = [
                            'success' => true,
                            'title' => 'Sukses',
                            'message' => 'Data telah dihapus.'
                        ];
                        return response()->json($message);
                    }
                else if($ppn > 0 && $diskon_rp > 0 && $diskon_persen == 0 && $pbbkb == 0 && $pbbkb_rp == 0){
                    foreach ($pembeliandetail as $row){
                        $total_qty += $row->qty;
                        $subtotal = $row->harga * $row->qty;
                        $total_harga += $subtotal;
                        $grand_total = number_format($total_harga,0,",",".");
                    }

                        $pembelian = Pembelian::on($konek)->find($no_pembelian);
                        if ($total_harga <= 0){
                            $pembelian->grand_total = 0;
                        }else {
                            $pembelian->grand_total = (($total_harga - $diskon_rp) + (($total_harga - $diskon_rp) * $ppn)) + $ongkos_angkut;
                        }               
                        $pembelian->save();

                        $message = [
                            'success' => true,
                            'title' => 'Sukses',
                            'message' => 'Data telah dihapus.'
                        ];
                        return response()->json($message);
                    }
                else if($ppn > 0 && $diskon_persen > 0 && $diskon_rp == 0 && $pbbkb == 0 && $pbbkb_rp == 0){
                    foreach ($pembeliandetail as $row){
                        $total_qty += $row->qty;
                        $subtotal = $row->harga * $row->qty;
                        $total_harga += $subtotal;
                        $grand_total = number_format($total_harga,0,",",".");
                    }

                        $pembelian = Pembelian::on($konek)->find($no_pembelian);
                        if ($total_harga <= 0){
                            $pembelian->grand_total = 0;
                        }else {
                            $pembelian->grand_total = (($total_harga - ($total_harga * $diskon_persen)) + (($total_harga - ($total_harga * $diskon_persen)) * $ppn)) + $ongkos_angkut; 
                        }               
                        $pembelian->save();

                        $message = [
                            'success' => true,
                            'title' => 'Sukses',
                            'message' => 'Data telah dihapus.'
                        ];
                        return response()->json($message);
                    }
                else if($ppn > 0 && $pbbkb > 0 && $diskon_rp == 0 && $diskon_persen == 0 && $pbbkb_rp == 0){
                    foreach ($pembeliandetail as $row){
                        $total_qty += $row->qty;
                        $subtotal = $row->harga * $row->qty;
                        $total_harga += $subtotal;
                        $grand_total = number_format($total_harga,0,",",".");
                    }

                        $pembelian = Pembelian::on($konek)->find($no_pembelian);
                        if ($total_harga <= 0){
                            $pembelian->grand_total = 0;
                        }else {
                            $totalpbbkb = $total_harga + ($total_harga * $pbbkb) + $ongkos_angkut;
                            $pembelian->grand_total =  $totalpbbkb + ($total_harga * $ppn); 
                        }               
                        $pembelian->save();

                        $message = [
                            'success' => true,
                            'title' => 'Sukses',
                            'message' => 'Data telah dihapus.'
                        ];
                        return response()->json($message);
                    }
                else if($ppn > 0 && $pbbkb_rp > 0 && $diskon_rp == 0 && $diskon_persen == 0 && $pbbkb == 0){
                    foreach ($pembeliandetail as $row){
                        $total_qty += $row->qty;
                        $subtotal = $row->harga * $row->qty;
                        $total_harga += $subtotal;
                        $grand_total = number_format($total_harga,0,",",".");
                    }

                        $pembelian = Pembelian::on($konek)->find($no_pembelian);
                        if ($total_harga <= 0){
                            $pembelian->grand_total = 0;
                        }else {
                            $totalpbbkb = $total_harga + $pbbkb_rp + $ongkos_angkut;
                            $pembelian->grand_total =  $totalpbbkb + ($total_harga * $ppn);
                        }               
                        $pembelian->save();

                        $message = [
                            'success' => true,
                            'title' => 'Sukses',
                            'message' => 'Data telah dihapus.'
                        ];
                        return response()->json($message);
                    }
                else if($diskon_persen > 0 && $pbbkb > 0 && $diskon_rp == 0 && $ppn == 0 && $pbbkb_rp == 0){
                    foreach ($pembeliandetail as $row){
                        $total_qty += $row->qty;
                        $subtotal = $row->harga * $row->qty;
                        $total_harga += $subtotal;
                        $grand_total = number_format($total_harga,0,",",".");
                    }

                        $pembelian = Pembelian::on($konek)->find($no_pembelian);
                        if ($total_harga <= 0){
                            $pembelian->grand_total = 0;
                        }else {
                            $pembelian->grand_total = (($total_harga - ($total_harga * $diskon_persen)) + (($total_harga - ($total_harga * $diskon_persen)) * $pbbkb)) + $ongkos_angkut;
                        }               
                        $pembelian->save();

                        $message = [
                            'success' => true,
                            'title' => 'Sukses',
                            'message' => 'Data telah dihapus.'
                        ];
                        return response()->json($message);
                    }
                else if($diskon_persen > 0 && $pbbkb_rp > 0 && $diskon_rp == 0 && $ppn == 0 && $pbbkb == 0){
                    foreach ($pembeliandetail as $row){
                        $total_qty += $row->qty;
                        $subtotal = $row->harga * $row->qty;
                        $total_harga += $subtotal;
                        $grand_total = number_format($total_harga,0,",",".");
                    }

                        $pembelian = Pembelian::on($konek)->find($no_pembelian);
                        if ($total_harga <= 0){
                            $pembelian->grand_total = 0;
                        }else {
                            $pembelian->grand_total = (($total_harga - ($total_harga * $diskon_persen)) + $pbbkb_rp + $ongkos_angkut);
                        }               
                        $pembelian->save();

                        $message = [
                            'success' => true,
                            'title' => 'Sukses',
                            'message' => 'Data telah dihapus.'
                        ];
                        return response()->json($message);
                    }
                else if($pbbkb > 0 && $diskon_rp > 0 && $diskon_persen == 0 && $ppn == 0 && $pbbkb_rp == 0){
                    foreach ($pembeliandetail as $row){
                        $total_qty += $row->qty;
                        $subtotal = $row->harga * $row->qty;
                        $total_harga += $subtotal;
                        $grand_total = number_format($total_harga,0,",",".");
                    }

                        $pembelian = Pembelian::on($konek)->find($no_pembelian);
                        if ($total_harga <= 0){
                            $pembelian->grand_total = 0;
                        }else {
                            $pembelian->grand_total = (($total_harga - $diskon_rp) + (($total_harga - $diskon_rp) * $pbbkb)) + $ongkos_angkut;
                        }               
                        $pembelian->save();

                        $message = [
                            'success' => true,
                            'title' => 'Sukses',
                            'message' => 'Data telah dihapus.'
                        ];
                        return response()->json($message);
                    }
                else if($pbbkb_rp > 0 && $diskon_rp > 0 && $diskon_persen == 0 && $ppn == 0 && $pbbkb == 0){
                    foreach ($pembeliandetail as $row){
                        $total_qty += $row->qty;
                        $subtotal = $row->harga * $row->qty;
                        $total_harga += $subtotal;
                        $grand_total = number_format($total_harga,0,",",".");
                    }

                        $pembelian = Pembelian::on($konek)->find($no_pembelian);
                        if ($total_harga <= 0){
                            $pembelian->grand_total = 0;
                        }else {
                            $pembelian->grand_total = (($total_harga - $diskon_rp) + $pbbkb_rp) + $ongkos_angkut;
                        }               
                        $pembelian->save();

                        $message = [
                            'success' => true,
                            'title' => 'Sukses',
                            'message' => 'Data telah dihapus.'
                        ];
                        return response()->json($message);
                    }
                else if($ppn > 0 && $pbbkb > 0 && $diskon_persen > 0 && $diskon_rp == 0 && $pbbkb_rp == 0){
                    foreach ($pembeliandetail as $row){
                        $total_qty += $row->qty;
                        $subtotal = $row->harga * $row->qty;
                        $total_harga += $subtotal;
                        $grand_total = number_format($total_harga,0,",",".");
                    }

                        $pembelian = Pembelian::on($konek)->find($no_pembelian);
                        if ($total_harga <= 0){
                            $pembelian->grand_total = 0;
                        }else {
                            $totaldiskon = $total_harga - ($total_harga * $diskon_persen);
                            $totalpbbkb = $totaldiskon + ($totaldiskon * $pbbkb) + $ongkos_angkut; 
                            $pembelian->grand_total =  $totalpbbkb + ($totaldiskon * $ppn);
                        }               
                        $pembelian->save();

                        $message = [
                            'success' => true,
                            'title' => 'Sukses',
                            'message' => 'Data telah dihapus.'
                        ];
                        return response()->json($message);
                    }
                else if($ppn > 0 && $pbbkb_rp > 0 && $diskon_persen > 0 && $diskon_rp == 0 && $pbbkb == 0){
                    foreach ($pembeliandetail as $row){
                        $total_qty += $row->qty;
                        $subtotal = $row->harga * $row->qty;
                        $total_harga += $subtotal;
                        $grand_total = number_format($total_harga,0,",",".");
                    }

                        $pembelian = Pembelian::on($konek)->find($no_pembelian);
                        if ($total_harga <= 0){
                            $pembelian->grand_total = 0;
                        }else {
                            $totaldiskon = $total_harga - ($total_harga * $diskon_persen);
                            $totalpbbkb = $totaldiskon + $pbbkb_rp + $ongkos_angkut;
                            $pembelian->grand_total =  $totalpbbkb + ($totaldiskon * $ppn);
                        }               
                        $pembelian->save();

                        $message = [
                            'success' => true,
                            'title' => 'Sukses',
                            'message' => 'Data telah dihapus.'
                        ];
                        return response()->json($message);
                    }
                else if($ppn > 0 && $pbbkb > 0 && $diskon_rp > 0 && $diskon_persen == 0 && $pbbkb_rp == 0){
                    foreach ($pembeliandetail as $row){
                        $total_qty += $row->qty;
                        $subtotal = $row->harga * $row->qty;
                        $total_harga += $subtotal;
                        $grand_total = number_format($total_harga,0,",",".");
                    }

                        $pembelian = Pembelian::on($konek)->find($no_pembelian);
                        if ($total_harga <= 0){
                            $pembelian->grand_total = 0;
                        }else {
                            $totaldiskon = $total_harga - $diskon_rp;
                            $totalpbbkb = ($totaldiskon + ($totaldiskon * $pbbkb)) + $ongkos_angkut;
                            $pembelian->grand_total =  $totalpbbkb + ($totaldiskon * $ppn);
                        }               
                        $pembelian->save();

                        $message = [
                            'success' => true,
                            'title' => 'Sukses',
                            'message' => 'Data telah dihapus.'
                        ];
                        return response()->json($message);
                    }
                else if($ppn > 0 && $pbbkb_rp > 0 && $diskon_rp > 0 && $diskon_persen == 0 && $pbbkb == 0){
                    foreach ($pembeliandetail as $row){
                        $total_qty += $row->qty;
                        $subtotal = $row->harga * $row->qty;
                        $total_harga += $subtotal;
                        $grand_total = number_format($total_harga,0,",",".");
                    }

                        $pembelian = Pembelian::on($konek)->find($no_pembelian);
                        if ($total_harga <= 0){
                            $pembelian->grand_total = 0;
                        }else {
                            $totaldiskon = $total_harga - $diskon_rp;
                            $totalpbbkb = $totaldiskon + $pbbkb_rp + $ongkos_angkut;
                            $pembelian->grand_total = $totalpbbkb + ($totaldiskon * $ppn);
                        }               
                        $pembelian->save();

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
