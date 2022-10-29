<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\TransferInDetail;
use App\Models\Transfer;
use App\Models\TransferDetail;
use App\Models\TransferIn;
use App\Models\Produk;
use App\Models\Konversi;
use App\Models\satuan;
use App\Models\tb_akhir_bulan;
use App\Models\tb_item_bulanan;
use DB;
use Carbon;

class TransferInDetailController extends Controller
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
        
        $create_url = route('permintaandetail.create');

        return view('admin.transferindetail.index',compact('create_url'));

    }

    public function getDatabyID(){
        $konek = self::konek();
        return Datatables::of(TransferInDetail::on($konek)->with('produk','satuan')->where('no_trf_in',request()->id)->orderBy('created_at','desc'))
           ->addColumn('subtotal', function ($query){
            return $subtotal = ($query->hpp * $query->qty);
           })->addColumn('action', function ($query){
                $konek = self::konek();
                return 
                    '<a href="javascript:;" data-toggle="tooltip" title="Hapus" onclick="del(\''.$query->id.'\',\''.$query->destroy_url.'\')" id="hapus" class="btn btn-danger btn-xs"> <i class="fa fa-times-circle"></i></a>'.'&nbsp';
           })->make(true);
    }

    public function qtycheck()
    {
         $konek = self::konek();
         $no_Transfer = request()->no;
         $produk = request()->id;
         $satuan = request()->satuan;
         $nilai_qty = request()->qty;

         $Transfer = Transfer::on($konek)->find($no_Transfer);

         $TransferDetail1 = TransferDetail::on($konek)->where('no_transfer', $no_Transfer)->where('kode_produk',$produk)->first();


         $qty_po = $TransferDetail1->qty;

         $qty_final = $qty_po;

        return response()->json($qty_final);
    }

    public function qtyProduk()
    {
         $konek = self::konek();
         $TransferDetail = TransferDetail::on($konek)->where('no_transfer',request()->id)->where('kode_produk', request()->kode_produk)->first();
        
         $produk = Produk::on($konek)->find(request()->kode_produk);
         $cek_kategori = $produk->kode_kategori;
         $cek_tipe = $produk->tipe_produk;
         $partnumber = $produk->partnumber;

         $qty_po = $TransferDetail->qty;
         $qty_sisa = $qty_po;

         $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode','Open')->orwhere('reopen_status','true')->first();
         $lokasi = auth()->user()->kode_lokasi;

         $transfer = Transfer::on($konek)->find(request()->id);
         $sisa = tb_item_bulanan::on($konek)->where('kode_produk', request()->kode_produk)->where('partnumber',$TransferDetail->partnumber)->where('kode_lokasi', $transfer->kode_lokasi)->where('periode', $cek_bulan->periode)->first();
         $monthly = tb_item_bulanan::on($konek)->where('kode_produk',request()->kode_produk)->where('kode_lokasi',$lokasi)->where('periode',$cek_bulan->periode)->orderBy('periode','desc')->first();
         if ($monthly == null){
            $no_mesin = "-";
         }else {
            $no_mesin = $monthly->no_mesin;
         }
         
         if ($cek_tipe != 'Serial'){
             $qtys = $sisa->ending_stock - $qty_sisa;
             if ($qtys < 0){
                $message = [
                    'success' => false,
                ];
                return response()->json($message);
             }else {
                $output = array(
                    'qty'=>$qty_sisa,
                    'harga'=>$TransferDetail->hpp,
                    'satuan'=>$TransferDetail->kode_satuan,
                    'partnumber'=>$partnumber,
                    'no_mesin'=>$no_mesin,
                    'kategori'=>$cek_kategori,
                    'tipe'=>$cek_tipe,
                    'success' => true,
                );
                return response()->json($output);
             }
         }else {
             if ($cek_kategori == 'BAN' || $cek_kategori == 'UNIT') {
                 $output = array(
                    'qty'=>$qty_sisa,
                    'harga'=>$TransferDetail->hpp,
                    'satuan'=>$TransferDetail->kode_satuan,
                    'partnumber'=>$partnumber,
                    'no_mesin'=>$no_mesin,
                    'kategori'=>$cek_kategori,
                    'tipe'=>$cek_tipe,
                    'success' => true,
                );
                return response()->json($output);
             }else {
                 $qtys = $sisa->ending_stock - $qty_sisa;
                 if ($qtys < 0){
                    $message = [
                        'success' => false,
                    ];
                    return response()->json($message);
                 }else {
                    $output = array(
                        'qty'=>$qty_sisa,
                        'harga'=>$TransferDetail->hpp,
                        'satuan'=>$TransferDetail->kode_satuan,
                        'partnumber'=>$partnumber,
                        'no_mesin'=>$no_mesin,
                        'kategori'=>$cek_kategori,
                        'tipe'=>$cek_tipe,
                        'success' => true,
                    );
                    return response()->json($output);
                 }
             }
         }
    }

    public function qtyProduk2()
    {
         $konek = self::konek();
         $TransferDetail = TransferDetail::on($konek)->where('no_transfer',request()->id)->where('kode_produk', request()->kode_produk)->first();

         $qty_rec = $TransferDetail->qty_received;
         $qty_po = $TransferDetail->qty;

         $qty_sisa = $qty_po - $qty_rec;

         $output = array(
                        'qty'=>$qty_sisa,
                    );

        return response()->json($output);
    }

    public function selectAjax(Request $request)
    {
        $konek = self::konek();
        $states = Konversi::on($konek)->where('kode_produk',$request->kode_produk)->pluck("satuan_terbesar","kode_satuan")->all();
        
        return response()->json(['options'=>$states]);
            
    }

    public function selectpart(Request $request)
    {
        $konek = self::konek();
        $produk = Produk::on($konek)->find(request()->kode_produk);
        $cek_tipe = $produk->tipe_produk;
        $cek_kategori = $produk->kode_kategori;
        $lokasi = auth()->user()->kode_lokasi;
        
        $states2 = TransferDetail::on($konek)->where('no_transfer',$request->no_transfer)->where('kode_produk',$request->kode_produk)->pluck("partnumber","partnumber")->all();
                     
        return response()->json(['options'=>$states2]);
        
    }

    public function getharga()
    {
         $konek = self::konek();
         $monthly = TransferDetail::on($konek)->where('no_transfer',request()->no_transfer)->where('kode_produk',request()->id)->where('partnumber',request()->part)->first();
         
         $produk = Produk::on($konek)->find(request()->id);
         $cek_tipe = $produk->tipe_produk;
         $cek_kategori = $produk->kode_kategori;
         
         $output = array(
            'stok'=>$monthly->qty,
            'hpp'=>$monthly->hpp,
            'no_mesin'=>$monthly->no_mesin,
            'tipe'=>$cek_tipe,
            'kategori'=>$cek_kategori,
        );
        return response()->json($output);
    }

    public function store(Request $request)
    {
        $konek = self::konek();
        $cek_trfin = TransferIn::on($konek)->find($request->no_trf_in);

        $no_transfer = $cek_trfin->no_transfer;
        $cek_trfout = Transfer::on($konek)->find($request->no_transfer);

        $cek_status = $cek_trfin->status;
        if($cek_status == 'POSTED'){  
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Transfer In: '.$cek_trfin->no_trf_in.' sudah POSTED! Pastikan Anda tidak membuka menu TRANSFER IN lebih dari 1',
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

        $cek_status = $cek_trfout->status;
        if($cek_status != 'POSTED'){  
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Transfer Out: '.$cek_trfin->no_transfer.' belum POSTED! Silahkan POST terlebih dahulu No. Transfer Out',
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

        $transferindetail = TransferInDetail::on($konek)->where('no_trf_in', $request->no_trf_in)->where('kode_produk', $request->kode_produk)->where('partnumber', $request->partnumber)->get();

        $cek_serial = Produk::on($konek)->where('id',$request->kode_produk)->first();
        $cek_tipe = $cek_serial->tipe_produk;
        $cek_kategori = $cek_serial->kode_kategori;

         if ($cek_tipe != 'Serial' || $cek_kategori != 'UNIT'){
            $leng = count($transferindetail);
                if($leng > 0){
                    $message = [
                        'success' => false,
                        'title' => 'Gagal',
                        'message' => 'Produk Sudah Ada'
                    ];
                    return response()->json($message);
                }
        }

                    $cek_qty_beli = TransferDetail::on($konek)->where('no_transfer',$request->no_transfer)->where('kode_produk',$request->kode_produk)->first();
                    // $cek_qty_recv = $cek_qty_beli->qty_received;
                    // $cek_qty_beli2 = $cek_qty_beli->qty - $cek_qty_recv;

                    $produk = Produk::on($konek)->where('id', request()->kode_produk)->first();
                    $tipe_produk = $produk->tipe_produk;
                    $tipe_kategori = $produk->kode_kategori;
                    $lokasi = auth()->user()->kode_lokasi;

                    if ($tipe_produk == 'Serial' && $tipe_kategori == 'UNIT'){
                        $cek_part = tb_item_bulanan::on($konek)->where('kode_produk',$request->kode_produk)->where('partnumber',$request->partnumber)->where('kode_lokasi',$lokasi)->first();
                        $cek_part2 = TransferInDetail::on($konek)->where('kode_produk',$request->kode_produk)->where('partnumber',$request->partnumber)->first();
                        $cek_qty_terima = TransferInDetail::on($konek)->where('no_trf_in', $request->no_trf_in)->where('kode_produk', $request->kode_produk)->get();
                        $cek_qty_terima2 = count($cek_qty_terima);

                        // if ($cek_part != null || $cek_part2 != null){
                        //     $message = [
                        //     'success' => false,
                        //     'title' => 'Gagal',
                        //     'message' => 'Part Number sudah pernah di input.'
                        //     ];
                        //     return response()->json($message);
                        // }

                    }

                    $transferindetail = TransferInDetail::on($konek)->create($request->all());
                    $produk = Produk::on($konek)->where('id', request()->kode_produk)->first();
                    $tipe_produk = $produk->tipe_produk;
                    $tipe_kategori = $produk->kode_kategori;

                    if($tipe_produk == 'Serial' && $tipe_kategori == 'UNIT'){
                        $update_penerimaan = TransferInDetail::on($konek)->where('no_trf_in', $request->no_trf_in)->where('kode_produk', $request->kode_produk)->first();
                        $update_penerimaan->qty = 1;
                        $update_penerimaan->save();
                    }

                    $hitung = TransferInDetail::on($konek)->where('no_trf_in', $request->no_trf_in)->get();
                    $leng = count($hitung);

                    $update_penerimaan = TransferIn::on($konek)->where('no_trf_in', $request->no_trf_in)->first();
                    $update_penerimaan->total_item = $leng;
                    $update_penerimaan->save();

                    $message = [
                        'success' => true,
                        'title' => 'Update',
                        'message' => 'Data telah Disimpan'
                        ];
                    return response()->json($message);
                
    }

    public function edit($transferindetail)
    {
        $konek = self::konek();
        $data = TransferInDetail::on($konek)->find($transferindetail);
        $output = array(
            'no_trf_in'=>$data->no_trf_in,
            'kode_produk'=>$data->kode_produk, 
            'kode_satuan'=>$data->kode_satuan, 
            'partnumber'=>$data->partnumber,   
            'no_mesin'=>$data->no_mesin,             
            'qty'=>$data->qty,
            'hpp'=>$data->hpp,
            'id'=>$data->id,
        );
        return response()->json($output);
    }

    public function updateAjax(Request $request)
    {
        $konek = self::konek();
        $cek_trfin = TransferIn::on($konek)->find($request->no_trf_in);
        $cek_status = $cek_trfin->status;
        if($cek_status == 'POSTED'){  
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Transfer In: '.$cek_trfin->no_trf_in.' sudah POSTED! Pastikan Anda tidak membuka menu TRANSFER IN lebih dari 1',
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
            'no_trf_in'=> 'required',
            'qty'=> 'required',
            'hpp'=> 'required',
          ]);

          $transferindetail = TransferInDetail::on($konek)->find($request->id)->update($request->all());

          if($transferindetail){
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

    public function destroy($transferindetail)
    {
        $konek = self::konek();
        $transferindetail = TransferInDetail::on($konek)->find($transferindetail);
        $get_no = $transferindetail->no_trf_in;
        
        $cek_trfin = TransferIn::on($konek)->find($get_no);
        $cek_status = $cek_trfin->status;
        if($cek_status == 'POSTED'){  
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Transfer In: '.$cek_trfin->no_trf_in.' sudah POSTED! Pastikan Anda tidak membuka menu TRANSFER IN lebih dari 1',
            ];
            return response()->json($message);
        }
        
            $transferindetail->delete();

                    $hitung = TransferInDetail::on($konek)->where('no_trf_in', $transferindetail->no_trf_in)->get();

                    $leng = count($hitung);

                    $update_penerimaan = TransferIn::on($konek)->where('no_trf_in', $transferindetail->no_trf_in)->first();
                    $update_penerimaan->total_item = $leng;
                    $update_penerimaan->save();

            if($transferindetail){
                $produk = Produk::on($konek)->find($transferindetail->kode_produk);
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
