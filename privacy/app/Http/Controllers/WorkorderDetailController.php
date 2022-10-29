<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\PembelianDetail;
use App\Models\Pembelian;
use App\Models\Pemakaian;
use App\Models\PemakaianDetail;
use App\Models\Produk;
use App\Models\Jasa;
use App\Models\Nonstock;
use App\Models\satuan;
use App\Models\Konversi;
use App\Models\tb_akhir_bulan;
use App\Models\tb_item_bulanan;
use App\Models\TransferDetail;
use App\Models\Workorder;
use App\Models\WorkorderDetail;
use DB;
use Carbon;

class WorkorderDetailController extends Controller
{
    public function index()
    {
        $create_url = route('workorderdetail.create');
        return view('admin.workorderdetail.index',compact('create_url'));
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
        return Datatables::of(WorkorderDetail::on($konek)->with('produk')->where('no_wo',request()->id)->orderBy('created_at','desc'))
           ->addColumn('action', function ($query){
                return '<a href="javascript:;" data-toggle="tooltip" title="Edit" onclick="edit(\''.$query->id.'\',\''.$query->edit_url.'\')" class="btn btn-warning btn-xs"><i class="fa fa-edit"></i></a>'.'&nbsp'.
                    '<a href="javascript:;" data-toggle="tooltip" title="Hapus" onclick="del(\''.$query->id.'\',\''.$query->destroy_url.'\')" id="hapus" class="btn btn-danger btn-xs"> <i class="fa fa-times-circle"></i></a>'.'&nbsp';
           })->make(true);
    }

    public function selectpart(Request $request)
    {
        $konek = self::konek();
        $produk = Produk::on($konek)->find(request()->kode_produk);
        if($produk != null){
            $cek_period = tb_akhir_bulan::on($konek)->where('status_periode','Open')->orwhere('status_periode','Disable')->first();
            $tgl_period = $cek_period->periode;

            $states2 = tb_item_bulanan::on($konek)->where('kode_produk',$request->kode_produk)->pluck("partnumber","partnumber")->all();

            return response()->json(['options'=>$states2]);
            
        }
            
    }
    public function stockProduk()
    {
        $konek = self::konek();
        $produk = Produk::on($konek)->find(request()->id);
        $lokasi = auth()->user()->kode_lokasi;
        $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode', 'Open')->orwhere('reopen_status', 'true')->first();
        $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$cek_bulan->periode)->month;
        $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$cek_bulan->periode)->year;
        $monthly = tb_item_bulanan::on($konek)->where('kode_produk',request()->id)->where('kode_lokasi',$lokasi)->where('periode',$cek_bulan->periode)->first();
        
        $trfqty = TransferDetail::on($konek)->join('transfer','transfer_detail.no_transfer','=','transfer.no_transfer')
                    ->where('transfer.kode_lokasi', $lokasi)
                    ->whereMonth('transfer.tanggal_transfer', $bulan)
                    ->whereYear('transfer.tanggal_transfer', $tahun)
                    ->where('transfer.status','<>', 'CLOSED')
                    ->where('transfer_detail.kode_produk', request()->id)
                    ->sum('transfer_detail.qty');
                
        if ($trfqty == null){
            $trfqty = 0;
        }

        if($monthly != null){
            if ($produk->tipe_produk == 'Serial' && $produk->kode_kategori == 'UNIT'){
                $output = array(
                'stok'=>$monthly->ending_stock,

                );
            }else {
                $output = array(
                'stok'=>$monthly->ending_stock - $trfqty,           

                );
            }          
            return response()->json($output);
        }else{
            $output = array(
                'stok'=>0,           
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

   public function store(Request $request)
    {
        $konek = self::konek();
        $cek_pembelian = Workorder::on($konek)->find($request->no_wo);
        $cek_status = $cek_pembelian->status;
        if($cek_status == 'POSTED'){  
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. WO: '.$cek_pembelian->no_wo.' sudah POSTED! Pastikan Anda tidak membuka menu WORK ORDER lebih dari 1',
            ];
            return response()->json($message);
        }

        $pembeliandetail = WorkorderDetail::on($konek)->where('no_wo', $request->no_wo)->where('kode_produk', $request->kode_produk)->get();
        $leng = count($pembeliandetail);
            
        if($leng > 0){
            $message = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Produk Sudah Ada'
            ];
            return response()->json($message);
        }
                
        $qty = $request->qty;
        if($qty < 1){
            $message = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Nilai Qty tidak boleh minus/kosong'
            ];
            return response()->json($message);
        }
        
        if ($request->type == 'Stock'){
            $prod = Produk::on($konek)->find($request->kode_produk);
            $kodeproduk = $request->kode_produk;
            $namaproduk = $prod->nama_produk;
        }else if($request->type == 'NonStock'){
            $kodeproduk = "-";
            $namaproduk = $request->nonstock;
        }else if($request->type == 'Lainnya'){
            $kodeproduk = "-";
            $namaproduk = $request->item_lain;
        }
        
        $simpan = [
            'no_wo'=>$request->no_wo,
            'type'=>$request->type,
            'kode_produk'=>$kodeproduk,
            'partnumber'=>$request->partnumber,
            'nama_produk'=>$namaproduk,
            'qty'=>$request->qty,
        ];

        WorkorderDetail::on($konek)->create($simpan);

        $workdetail = WorkorderDetail::on($konek)->where('no_wo', $request->no_wo)->get();
        $lenger = count($workdetail);

        $cek_pembelian->total_item = $lenger;
        $cek_pembelian->save();

        $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => 'Disimpan'
        ];
        return response()->json($message);
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
        $data = WorkorderDetail::on($konek)->find($id);
        $no_pembelian = $data->no_wo;
        $cek_header = Workorder::on($konek)->find($no_pembelian);
        
        if ($data->type == 'Stock'){
            $prod = Produk::on($konek)->find($data->kode_produk);
            $namaproduk = $prod->nama_produk;
        }else{
            $prod = WorkorderDetail::on($konek)->find($id);
            $namaproduk = $prod->nama_produk;
        }

        $output = array(
            'no_wo'=>$data->no_wo,
            'type'=>$data->type,
            'kode_produk'=>$data->kode_produk,
            'partnumber'=>$data->partnumber,
            'nama_produk'=>$namaproduk,
            'qty'=>$data->qty,
            'id'=>$data->id,
        );
        return response()->json($output);
    }

   public function updateAjax(Request $request)
    {
        $konek = self::konek();
        $cek_pembelian = Workorder::on($konek)->find($request->no_wo);
        $cek_status = $cek_pembelian->status;
        if($cek_status == 'POSTED'){  
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. WO: '.$cek_pembelian->no_wo.' sudah POSTED! Pastikan Anda tidak membuka menu WORK ORDER lebih dari 1',
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
            $wodetail = WorkorderDetail::on($konek)->find($request->id);
            $wodetail->update($request->all());
            
            if($wodetail->qty > $wodetail->qty_pakai){
                $wodetail->status_produk ='OFF';
                $wodetail->save();
            }
            
                 $message = [
                'success' => true,
                'title' => 'Simpan',
                'message' => 'Berhasil Diubah',
                ];
            return response()->json($message);
        }
    }

    public function destroy($pembeliandetail)
    {
        $konek = self::konek();
        $wo_detail = WorkorderDetail::on($konek)->find($pembeliandetail);
        $cek_pembelian = Workorder::on($konek)->find($wo_detail->no_wo);
        $cek_status = $cek_pembelian->status;
        // $cek_tarik = PemakaianDetail::on($konek)->join('pemakaian','pemakaian.no_pemakaian','=','pemakaian_detail.no_pemakaian')->join('workorder_detail','workorder_detail.no_wo','pemakaian.no_wo')->where('pemakaian_detail.kode_produk','workorder_detail.kode_produk')->where('workorder_detail.no_wo',$wo_detail->no_wo)->first();
        if($wo_detail->qty_pakai != 0)
        {
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Kode Produk telah dipakai ',
            ];
            return response()->json($message);
        }

        if($cek_status == 'POSTED'){  
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. WO: '.$cek_pembelian->no_wo.' sudah POSTED! Pastikan Anda tidak membuka menu WORK ORDER lebih dari 1',
            ];
            return response()->json($message);
        }
        
        //JOIN 3 TABEL
        // $cek_tarik = WorkorderDetail::on($konek)->join('pemakaian_detail','workorder_detail.kode_produk','=','pemakaian_detail.kode_produk')->join('pemakaian','pemakaian.no_pemakaian','=','pemakaian_detail.no_pemakaian')->where('pemakaian.no_wo',$wo_detail->no_wo)->first();
        $cek_tarik = WorkorderDetail::on($konek)->join('pemakaian','pemakaian.no_wo','=','workorder_detail.no_wo')->join('pemakaian_detail','pemakaian_detail.no_pemakaian','=','pemakaian.no_pemakaian')->where('pemakaian_detail.kode_produk',$wo_detail->kode_produk)->where('pemakaian.no_wo',$wo_detail->no_wo)->first();
        if($cek_tarik != null)
        {
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Kode Produk'.$wo_detail->nama_produk.'telah ditarik ke Pemakaian Nomor '.$cek_tarik->no_pemakaian,
            ];
            return response()->json($message);
        }
        
        
        $wo_detail->delete();
        
        $workdetail = WorkorderDetail::on($konek)->where('no_wo', $cek_pembelian->no_wo)->get();
        $lenger = count($workdetail);
        $cek_pembelian->total_item = $lenger;
        $cek_pembelian->save();

        $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => 'Berhasil Dihapus',
        ];
        return response()->json($message);
    }

}
