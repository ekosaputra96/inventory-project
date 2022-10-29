<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Pemakaian;
use App\Models\PemakaianDetail;
use App\Models\Transfer;
use App\Models\TransferDetail;
use App\Models\tb_item_bulanan;
use App\Models\tb_akhir_bulan;
use App\Models\Produk;
use App\Models\Konversi;
use App\Models\satuan;
use App\Models\MemoDetail;
use App\Models\MasterLokasi;
use App\Models\user_history;
use DB;
use Carbon;

class TransferDetailController extends Controller
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
        
        $create_url = route('transferdetail.create');

        return view('admin.transferdetail.index',compact('create_url'));

    }

    public function getDatabyID(){
        $konek = self::konek();
        return Datatables::of(TransferDetail::on($konek)->with('produk','satuan')->where('no_transfer',request()->id)->orderBy('created_at','desc'))
           ->addColumn('subtotal', function ($query){
            return $subtotal = $query->hpp * $query->qty;
           })->addColumn('action', function ($query){
            $konek = self::konek();
            $cek_produk = Produk::on($konek)->where('id',$query->kode_produk)->first();
            $cek = $cek_produk->tipe_produk;
            $cek2 = $cek_produk->kode_kategori;
            if ($cek == 'Serial' && $cek2 == 'UNIT'){
                return 
                '<a href="javascript:;" data-toggle="tooltip" title="Edit" onclick="edit(\''.$query->id.'\',\''.$query->edit_url.'\')" class="btn btn-warning btn-xs"><i class="fa fa-edit"></i></a>'.'&nbsp'.
                    '<a href="javascript:;" data-toggle="tooltip" title="Hapus" onclick="del(\''.$query->id.'\',\''.$query->destroy_url.'\')" id="hapus" class="btn btn-danger btn-xs"> <i class="fa fa-times-circle"></i></a>';
                 }
            else {
                return '<a href="javascript:;" data-toggle="tooltip" title="Edit" onclick="edit(\''.$query->id.'\',\''.$query->edit_url.'\')" class="btn btn-warning btn-xs"><i class="fa fa-edit"></i></a>'.'&nbsp'.
                    '<a href="javascript:;" data-toggle="tooltip" title="Hapus" onclick="del(\''.$query->id.'\',\''.$query->destroy_url.'\')" id="hapus" class="btn btn-danger btn-xs"> <i class="fa fa-times-circle"></i></a>'.'&nbsp';
            }
           })->make(true);
    }

    public function stockProduk()
    {
        $konek = self::konek();
        $produk = Produk::on($konek)->find(request()->id);
        $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode','Open')->orwhere('reopen_status','true')->first();
        $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$cek_bulan->periode)->month;
        $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$cek_bulan->periode)->year;

        $lokasi = auth()->user()->kode_lokasi;
        $monthly = tb_item_bulanan::on($konek)->where('kode_produk',request()->id)->where('kode_lokasi',$lokasi)->where('periode',$cek_bulan->periode)->orderBy('periode','desc')->first();
        $monthly2 = tb_item_bulanan::on($konek)->where('kode_produk',request()->id)->where('kode_lokasi',$lokasi)->where('periode',$cek_bulan->periode)->orderBy('ending_stock','desc')->first();
        
        $trfqty = TransferDetail::on($konek)->join('transfer','transfer_detail.no_transfer','=','transfer.no_transfer')
                    ->where('transfer.kode_lokasi', $lokasi)
                    ->whereMonth('transfer.tanggal_transfer', $bulan)
                    ->whereYear('transfer.tanggal_transfer', $tahun)
                    ->where('transfer.status', 'POSTED')
                    ->where('transfer_detail.kode_produk', request()->id)
                    ->sum('transfer_detail.qty');
                    
        if ($trfqty == null){
            $trfqty = 0;
        }
        
        $pakaiqty = PemakaianDetail::on($konek)->join('pemakaian','pemakaian_detail.no_pemakaian','=','pemakaian.no_pemakaian')
                    ->where('pemakaian.kode_lokasi', $lokasi)
                    ->whereMonth('pemakaian.tanggal_pemakaian', $bulan)
                    ->whereYear('pemakaian.tanggal_pemakaian', $tahun)
                    ->where('pemakaian.status', 'OPEN')
                    ->where('pemakaian_detail.kode_produk', request()->id)
                    ->sum('pemakaian_detail.qty');
        
        if ($pakaiqty == null){
            $pakaiqty = 0;
        }

        if($monthly != null){
            $hpp = number_format($monthly->ending_amount/$monthly->ending_stock,2, '.', '');
            $hpp2 = number_format($monthly2->ending_amount/$monthly2->ending_stock,2, '.', '');
            if ($produk->tipe_produk == 'Serial' && $produk->kode_kategori == 'UNIT'){
                $output = array(
                'success' => true,
                'stok'=>$monthly2->ending_stock,
                'hpp'=>$hpp2,
                'no_mesin'=>$monthly2->no_mesin,
                'partnumber'=>$monthly2->partnumber,
                'tipe'=>$produk->tipe_produk,
                'kategori'=>$produk->kode_kategori,
                );
                return response()->json($output);
            }else {
                $output = array(
                'success' => true,
                'stok'=>$monthly->ending_stock - $trfqty - $pakaiqty,
                'hpp'=>$hpp,
                'no_mesin'=>$monthly2->no_mesin,
                'partnumber'=>$monthly2->partnumber,
                'tipe'=>$produk->tipe_produk,
                'kategori'=>$produk->kode_kategori,
                );
                return response()->json($output);
            }
        }else{
            $message =  [
                            'success' => false,
                            'title' => 'Gagal',
                            'message' => 'Stock Produk Tidak Ada',
                        ];
                        return response()->json($message);
        }
        
    }

    public function getharga()
    {
         $konek = self::konek();
         $lokasi = auth()->user()->kode_lokasi;
         $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode', 'Open')->orwhere('reopen_status', 'true')->first();
         $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$cek_bulan->periode)->month;
         $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$cek_bulan->periode)->year;
         $monthly = tb_item_bulanan::on($konek)->where('kode_produk',request()->id)->where('partnumber',request()->part)->where('kode_lokasi',$lokasi)->where('periode',$cek_bulan->periode)->first();

         $trfqty = TransferDetail::on($konek)->join('transfer','transfer_detail.no_transfer','=','transfer.no_transfer')
                    ->where('transfer.kode_lokasi', $lokasi)
                    ->whereMonth('transfer.tanggal_transfer', $bulan)
                    ->whereYear('transfer.tanggal_transfer', $tahun)
                    ->where('transfer.status', 'POSTED')
                    ->where('transfer_detail.kode_produk', request()->id)
                    ->sum('transfer_detail.qty');
        
        if ($trfqty == null){
            $trfqty = 0;
        }
        
        $pakaiqty = PemakaianDetail::on($konek)->join('pemakaian','pemakaian_detail.no_pemakaian','=','pemakaian.no_pemakaian')
                    ->where('pemakaian.kode_lokasi', $lokasi)
                    ->whereMonth('pemakaian.tanggal_pemakaian', $bulan)
                    ->whereYear('pemakaian.tanggal_pemakaian', $tahun)
                    ->where('pemakaian.status', 'OPEN')
                    ->where('pemakaian_detail.kode_produk', request()->id)
                    ->sum('pemakaian_detail.qty');
        
        if ($pakaiqty == null){
            $pakaiqty = 0;
        }
         
         if($monthly != null){
            if($monthly->ending_stock != 0){
                $hpp = number_format($monthly->ending_amount/$monthly->ending_stock,2, '.', '');
                $output = array(
                    'stok'=>$monthly->ending_stock - $trfqty - $pakaiqty,
                    'hpp'=>$hpp,
                    'no_mesin'=>$monthly->no_mesin,
                );
            }else{
                $output = array(
                    'stok'=>0,
                    'hpp'=>0,
                    'no_mesin'=>'-',
                );
            }
         }
         else{
            $output = array(
                'stok'=>0,
                'hpp'=>0,
                'no_mesin'=>'-',
            );
         }
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
        $cek_tipe = $produk->tipe_produk;
        $cek_kategori = $produk->kode_kategori;
        $lokasi = auth()->user()->kode_lokasi;

        if($cek_tipe == 'Serial'){
            if($cek_kategori == 'BAN' || $cek_kategori == 'UNIT'){
                $cek_period = tb_akhir_bulan::on($konek)->where('status_periode','Open')->orwhere('status_periode','Disable')->first();
                $tgl_period = $cek_period->periode;
                $states2 = tb_item_bulanan::on($konek)->where('kode_produk',$request->kode_produk)->where('kode_lokasi',$lokasi)->where('ending_stock', 1)->where('periode',$tgl_period)->pluck("partnumber","partnumber")->all();
                
                return response()->json(['options'=>$states2]);
            }
            else{
                $cek_period = tb_akhir_bulan::on($konek)->where('status_periode','Open')->orwhere('status_periode','Disable')->first();
                $tgl_period = $cek_period->periode;
                $states2 = tb_item_bulanan::on($konek)->where('kode_produk',$request->kode_produk)->where('kode_lokasi',$lokasi)->pluck("partnumber","partnumber")->all();
                
                return response()->json(['options'=>$states2]);
            }
        }else{
            $cek_period = tb_akhir_bulan::on($konek)->where('status_periode','Open')->orwhere('status_periode','Disable')->first();
            $tgl_period = $cek_period->periode;
            $states2 = tb_item_bulanan::on($konek)->where('kode_produk',$request->kode_produk)->where('kode_lokasi',$lokasi)->pluck("partnumber","partnumber")->all();
            
            return response()->json(['options'=>$states2]);
        }

    }


    public function store(Request $request)
    {   
        $konek = self::konek();
        $cek_trfout = Transfer::on($konek)->find($request->no_transfer);
        $cek_status = $cek_trfout->status;
        if($cek_status == 'POSTED'){  
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Transfer Out: '.$cek_trfout->no_transfer.' sudah POSTED! Pastikan Anda tidak membuka menu TRANSFER OUT lebih dari 1',
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
                'message' => 'Nilai QTY/Harga jual tidak boleh kurang dari 1'
            ];
            return response()->json($message);
        }
        else{
                $transferdetail = TransferDetail::on($konek)->where('no_transfer', $request->no_transfer)->where('kode_produk', $request->kode_produk)->where('partnumber', $request->partnumber)->get();
                $leng = count($transferdetail);

                    if($leng > 0){
                        $message = [
                            'success' => false,
                            'title' => 'Gagal',
                            'message' => 'Produk Sudah Ada',
                            ];
                        return response()->json($message);
                    }
                    else{
                            $transferdetail = TransferDetail::on($konek)->create($request->all());

                            $total_qty = 0;
                            $total_harga = 0;

                            $transferdetail = TransferDetail::on($konek)->with('produk','satuan')->where('no_transfer', $request->no_transfer)
                            ->orderBy('created_at','desc')->get();

                            $data = Transfer::on($konek)->find($request->no_transfer);
                            foreach ($transferdetail as $row){
                                $total_qty += $row->qty;
                                $subtotal = $row->hpp * $row->qty;
                                $total_harga += $subtotal;
                            }

                                $transfer = Transfer::on($konek)->find($request->no_transfer);
                                $transferdetail = TransferDetail::on($konek)->where('no_transfer', $request->no_transfer)->get();
                                $leng = count($transferdetail);
                                $transfer->total_item = $leng;
                                $transfer->save();

                                $transfer = Transfer::on($konek)->find($request->no_transfer)->update($request->all());
                                $nama = auth()->user()->name;
                                $tmp = ['nama' => $nama,'aksi' => 'Update No. transfer: '.$request->no_transfer.'.','created_by'=>$nama,'updated_by'=>$nama];
                                //dd($tmp);
                                user_history::create($tmp);

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah di Update'
                                ];
                            return response()->json($message);

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
            $transferdetail = TransferDetail::on($konek)->where('no_transfer', $request->no_transfer)->where('kode_produk', $request->kode_produk)->where('partnumber', $request->partnumber)->get();

            $leng = count($transferdetail);

                if($leng > 0){
                    $message = [
                        'success' => false,
                        'title' => 'Gagal',
                        'message' => 'Produk Sudah Ada',
                        ];
                    return response()->json($message);
                }
                else{
                    $transferdetail = TransferDetail::on($konek)->create($request->all());

                    $produk = Produk::on($konek)->where('id', request()->kode_produk)->first();
                    $tipe_produk = $produk->tipe_produk;

                    $hitung = TransferDetail::on($konek)->where('no_transfer', $request->no_transfer)->get();

                    $leng = count($hitung);

                    $update_transfer = Transfer::on($konek)->where('no_transfer', $request->no_transfer)->first();
                    $update_transfer->total_item = $leng;
                    $update_transfer->save();

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

         // $kode_produk = Produk::on($konek)->where('id',$produk)->first();

         $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode','Open')->orwhere('reopen_status','true')->first();
         $lokasi = auth()->user()->kode_lokasi;
         $monthly = tb_item_bulanan::on($konek)->where('kode_produk',$produk)->where('kode_lokasi',$lokasi)->where('periode',$cek_bulan->periode)->orderBy('periode','desc')->first();

         $stok = $monthly->ending_stock;

        return response()->json($stok);
    }


    public function edit($transferdetail)
    {
        $konek = self::konek();
        $id = $transferdetail;
        $data = TransferDetail::on($konek)->find($id);
        $header = Transfer::on($konek)->find($data->no_transfer);
        $cek_produk = Produk::on($konek)->find($data->kode_produk);
        $sisastock = Tb_item_bulanan::on($konek)->where('kode_produk',$data->kode_produk)->where('kode_lokasi',$header->kode_dari)->orderby('periode','desc')->first();
        $output = array(
            'no_transfer'=>$data->no_transfer,
            'kode_produk'=>$data->kode_produk,
            'nama_produk'=>$cek_produk->nama_produk,
            'kode_satuan'=>$data->kode_satuan,
            'qty'=>$data->qty,
            'hpp'=>$data->hpp,
            'stock'=>$sisastock->ending_stock,
            'id'=>$data->id,
        );
        return response()->json($output);
    }

    public function updateAjax(Request $request)
    {
        $konek = self::konek();
        $cek_trfout = Transfer::on($konek)->find($request->no_transfer);
        $cek_status = $cek_trfout->status;
        if($cek_status == 'POSTED'){  
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Transfer Out: '.$cek_trfout->no_transfer.' sudah POSTED! Pastikan Anda tidak membuka menu TRANSFER OUT lebih dari 1',
            ];
            return response()->json($message);
        }
        $qty = $request->qty;
        
        if($cek_trfout->no_memo != null){
            $cekqtydetail = MemoDetail::on($konek)->where('no_memo',$cek_trfout->no_memo)->where('kode_produk',$request->kode_produk)->first();

            if($qty > $cekqtydetail->qty){
                $message = [
                    'success' => false,
                    'title' => 'Gagal',
                    'message' => 'Qty melebihi Qty di NPPB'
                ];
                return response()->json($message);
            }
        }
        
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
            'no_transfer'=> 'required',
            'qty'=> 'required',
            'hpp'=>'required',
          ]);

                            $transferdetail= TransferDetail::on($konek)->find($request->id)->update($request->all());

                            $harga = $request->harga_jual;
                            
                            $qty = $request->qty;

                            $transferdetail2 = TransferDetail::on($konek)->where('no_transfer', $request->no_transfer)->where('kode_produk', $request->kode_produk)->where('partnumber', $request->partnumber)->first();

                            $total_qty = 0;
                            $total_harga = 0;

                            $transferdetail = TransferDetail::on($konek)->where('no_transfer', $request->no_transfer)
                            ->orderBy('created_at','desc')->get();

                            $data = Transfer::on($konek)->find($request->no_transfer);
                            $ppn = $data->ppn;
                            $diskon_persen = $data->diskon_persen;
                            $diskon_rp = $data->diskon_rp;
                            
                            foreach ($transferdetail as $row){
                                $total_qty += $row->qty;
                                $subtotal = $row->harga_jual * $row->qty;
                                $total_harga += $subtotal;
                            }
                            
                            if($diskon_persen == 0 && $diskon_rp == 0){
                                $ppn = ($data->ppn)/100;

                                $transfer = Transfer::on($konek)->find($request->no_transfer);
                                $transfer->save(); 

                                $hitung = TransferDetail::on($konek)->where('no_transfer', $request->no_transfer)->get();

                                $leng = count($hitung);

                                $update_pembelian = Transfer::on($konek)->where('no_transfer', $request->no_transfer)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                            }
                            else if($diskon_persen > 0 && $ppn == 0 && $diskon_rp == 0){
                                $diskon_persen = ($data->diskon_persen)/100;

                                $transfer = Transfer::on($konek)->find($request->no_transfer);
                                $transfer->save(); 

                                $hitung = TransferDetail::on($konek)->where('no_transfer', $request->no_transfer)->get();

                                $leng = count($hitung);

                                $update_pembelian = Transfer::on($konek)->where('no_transfer', $request->no_transfer)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

                                $message = [
                                'success' => true,
                                'title' => 'Update',
                                'message' => 'Data telah disimpan'
                                ];
                                return response()->json($message);
                        } 
                        else if($diskon_rp > 0 && $ppn == 0 && $diskon_persen == 0){  
                                $diskon_rp = $data->diskon_rp;

                                $transfer = Transfer::on($konek)->find($request->no_transfer);
                                $transfer->save(); 

                                $hitung = TransferDetail::on($konek)->where('no_transfer', $request->no_transfer)->get();

                                $leng = count($hitung);

                                $update_pembelian = Transfer::on($konek)->where('no_transfer', $request->no_transfer)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

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

                                $transfer = Transfer::on($konek)->find($request->no_transfer);
                                $transfer->save(); 

                                $hitung = TransferDetail::on($konek)->where('no_transfer', $request->no_transfer)->get();

                                $leng = count($hitung);

                                $update_pembelian = Transfer::on($konek)->where('no_transfer', $request->no_transfer)->first();
                                $update_pembelian->total_item = $leng;
                                $update_pembelian->save();

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

                                $transfer = Transfer::on($konek)->find($request->no_transfer);
                                $transfer->save(); 

                                $hitung = TransferDetail::on($konek)->where('no_transfer', $request->no_transfer)->get();

                                $leng = count($hitung);

                                $update_pembelian = Transfer::on($konek)->where('no_transfer', $request->no_transfer)->first();
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

    public function destroy($transferdetail)
    {
        $konek = self::konek();
        $transferdetail = TransferDetail::on($konek)->find($transferdetail);
        $get_no = $transferdetail->no_transfer;
        
        $cek_trfout = Transfer::on($konek)->find($get_no);
        $cek_status = $cek_trfout->status;
        if($cek_status == 'POSTED'){  
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Transfer Out: '.$cek_trfout->no_transfer.' sudah POSTED! Pastikan Anda tidak membuka menu TRANSFER OUT lebih dari 1',
            ];
            return response()->json($message);
        }

            $transferdetail->delete();

            $hitung = TransferDetail::on($konek)->where('no_transfer', $cek_trfout->no_transfer)->get();

            $leng = count($hitung);

            $update_pemakaian = Transfer::on($konek)->where('no_transfer', $cek_trfout->no_transfer)->first();
            $update_pemakaian->total_item = $leng;
            $update_pemakaian->save();

            if($transferdetail){
                $produk = Produk::on($konek)->find($transferdetail->kode_produk);
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
