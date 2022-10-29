<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Pemakaian;
use App\Models\PemakaianDetail;
use App\Models\tb_item_bulanan;
use App\Models\tb_akhir_bulan;
use App\Models\Produk;
use App\Models\Konversi;
use App\Models\satuan;
use App\Models\TransferDetail;
use DB;
use Carbon;

class PemakaiandetailController extends Controller
{
    public function index()
    {
        
        $create_url = route('pemakaiandetail.create');
        return view('admin.pemakaiandetail.index',compact('create_url'));

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
        return Datatables::of(PemakaianDetail::on($konek)->with('produk','satuan')->where('no_pemakaian',request()->id)->orderBy('created_at','desc'))
           ->addColumn('subtotal', function ($query){
            return $subtotal = $query->harga * $query->qty;
           })->addColumn('action', function ($query){
         
                return '<a href="javascript:;" data-toggle="tooltip" title="Edit" onclick="edit(\''.$query->id.'\',\''.$query->edit_url.'\')" class="btn btn-warning btn-xs"><i class="fa fa-edit"></i></a>'.'&nbsp'.
                    '<a href="javascript:;" data-toggle="tooltip" title="Hapus" onclick="del(\''.$query->id.'\',\''.$query->destroy_url.'\')" id="hapus" class="btn btn-danger btn-xs"> <i class="fa fa-times-circle"></i></a>'.'&nbsp';
            
           })->make(true);
    }
    
    public function hapusall()
    {
        $konek = self::konek();
        $detail = PemakaianDetail::on($konek)->where('no_pemakaian',request()->id)->delete();
        $total = Pemakaian::on($konek)->where('no_pemakaian',request()->id)->first();
        $total->total_item = 0;
        $total->save();
        $message = [
              'success' => true,
              'title' => 'Hapus',
              'message' => 'Semua detail No. Pemakaian: '.request()->id.' sudah DIHAPUS!',
          ];
          return response()->json($message);
    }

    public function gethpp()
    {
      $konek = self::konek();
      $cek_pemakaian = Pemakaian::on($konek)->find(request()->id);
      $cek_status = $cek_pemakaian->status;
      if($cek_status == 'POSTED'){  
          $message = [
              'success' => false,
              'title' => 'Simpan',
              'message' => 'Status No. Pemakaian: '.$cek_pemakaian->no_pemakaian.' sudah POSTED! Pastikan Anda tidak membuka menu PEMAKAIAN lebih dari 1',
          ];
          return response()->json($message);
      }

                $no_pemakaian = request()->id;
                $data_detail = PemakaianDetail::on($konek)->where('no_pemakaian',$no_pemakaian)->get();
                $name_produk = array();
                $index = 0;

                foreach ($data_detail as $row) {
                  $produk = $row->partnumber;
                  $kode_produk = $row->kode_produk;

                  $name_produk[]= array(
                      'partnumber'=>$produk,
                      'kode_produk'=>$kode_produk,
                  );

                  $index++;
                }

                if($name_produk){
                    $leng = count($name_produk);

                    $i = 0;

                    while($i < $leng){
                      $lokasi = auth()->user()->kode_lokasi;
                      $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode', 'Open')->orwhere('reopen_status', 'true')->first();
                      $no_mesin = tb_item_bulanan::on($konek)->where('partnumber',$name_produk[$i]['partnumber'])->where('kode_produk',$name_produk[$i]['kode_produk'])->where('kode_lokasi',$lokasi)->where('periode',$cek_bulan->periode)->first();
                      
                      $get_hpp = $no_mesin->hpp;

                      $tabel_baru2 = [
                            'harga'=>$get_hpp,
                      ];

                      $update = PemakaianDetail::on($konek)->where('no_pemakaian', $no_pemakaian)->where('kode_produk',$name_produk[$i]['kode_produk'])->where('partnumber',$name_produk[$i]['partnumber'])->update($tabel_baru2);

                      $i++;
                    }
                    
                    $message = [
                        'success' => true,
                        'title' => 'Simpan',
                        'message' => 'HPP telah diperbarui.',
                    ];

                    return response()->json($message);
                }
              
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Data tidak ada.',
            ];

            return response()->json($message);
              
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
        
        // $trfqtyopen = TransferDetail::on($konek)->join('transfer','transfer_detail.no_transfer','=','transfer.no_transfer')
        //             ->where('transfer.kode_lokasi', $lokasi)
        //             ->whereMonth('transfer.tanggal_transfer', $bulan)
        //             ->whereYear('transfer.tanggal_transfer', $tahun)
        //             ->where('transfer.status', 'OPEN')
        //             ->where('transfer_detail.kode_produk', request()->id)
        //             ->sum('transfer_detail.qty');
                    
        if ($trfqty == null){
            $trfqty = 0;
        }
        
        // if ($trfqtyopen == null){
        //     $trfqtyopen = 0;
        // }

        if($monthly != null){
            $hpp = number_format($monthly->ending_amount/$monthly->ending_stock,2, '.', '');
            if ($produk->tipe_produk == 'Serial' && $produk->kode_kategori == 'UNIT'){
                $output = array(
                'stok'=>$monthly->ending_stock,
                'hpp'=>$hpp,
                'tipe'=>$produk->tipe_produk,
                'kategori'=>$produk->kode_kategori,
                // 'satuan'=>$produk->kode_satuan,
                );
            }else {
                $output = array(
                'stok'=>$monthly->ending_stock - $trfqty,           
                'hpp'=>$hpp,
                'tipe'=>$produk->tipe_produk,
                'kategori'=>$produk->kode_kategori,
                // 'satuan'=>$produk->kode_satuan,
                );
            }          
            return response()->json($output);
        }else{
            $output = array(
                'stok'=>0,           
                'hpp'=>0,
                'tipe'=>$produk->tipe_produk,
                'kategori'=>$produk->kode_kategori,
            );
            return response()->json($output);
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
                    ->where('transfer.status','<>', 'CLOSED')
                    ->where('transfer_detail.kode_produk', request()->id)
                    ->sum('transfer_detail.qty');
                    
        // $trfqtyopen = TransferDetail::on($konek)->join('transfer','transfer_detail.no_transfer','=','transfer.no_transfer')
        //             ->where('transfer.kode_lokasi', $lokasi)
        //             ->whereMonth('transfer.tanggal_transfer', $bulan)
        //             ->whereYear('transfer.tanggal_transfer', $tahun)
        //             ->where('transfer.status', 'OPEN')
        //             ->where('transfer_detail.kode_produk', request()->id)
        //             ->sum('transfer_detail.qty');
                    
        if ($trfqty == null){
            $trfqty = 0;
        }
        
        // if ($trfqtyopen == null){
        //     $trfqtyopen = 0;
        // }
                    
         if($monthly != null){
            $hpp = number_format($monthly->ending_amount/$monthly->ending_stock,2, '.', '');
            $output = array(
                'stok'=>$monthly->ending_stock - $trfqty,
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
        $pemakaiandetail = PemakaianDetail::on($konek)->create($request->all());
        if($pemakaiandetail){
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
        $cek_pemakaian = Pemakaian::on($konek)->find($request->no_pemakaian);
        $cek_status = $cek_pemakaian->status;
        if($cek_status == 'POSTED'){  
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Pemakaian: '.$cek_pemakaian->no_pemakaian.' sudah POSTED! Pastikan Anda tidak membuka menu PEMAKAIAN lebih dari 1',
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

        $cek_produk = Produk::on($konek)->find($request->kode_produk);
        $cek_kategori = $cek_produk->kode_kategori;
        $cek_tipe = $cek_produk->tipe_produk;
        
        if($cek_kategori == 'BAN' && $cek_tipe == 'Serial'){
            $message = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Pemakaian BAN LUAR / BAN VULKANISIR dilakukan di transkasi PEMAKAIAN BAN'
            ];
            return response()->json($message);
        }
        else{
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
                $pemakaiandetail = PemakaianDetail::on($konek)->where('no_pemakaian', $request->no_pemakaian)->where('kode_produk', $request->kode_produk)->where('partnumber', $request->partnumber)->get();

                $leng = count($pemakaiandetail);

                    if($leng > 0){
                        $message = [
                            'success' => false,
                            'title' => 'Gagal',
                            'message' => 'Produk Sudah Ada',
                            ];
                        return response()->json($message);
                    }
                    else{
                        $pemakaiandetail = PemakaianDetail::on($konek)->create($request->all());
                        $produk = Produk::on($konek)->where('id', request()->kode_produk)->first();
                        $tipe_produk = $produk->tipe_produk;
                        $hitung = PemakaianDetail::on($konek)->where('no_pemakaian', $request->no_pemakaian)->get();

                        $leng = count($hitung);

                        $update_pemakaian = Pemakaian::on($konek)->where('no_pemakaian', $request->no_pemakaian)->first();
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

    public function edit($pemakaiandetail)
    {
        $konek = self::konek();
        $id = $pemakaiandetail;
        $data = PemakaianDetail::on($konek)->find($id);
        $cek_produk = Produk::on($konek)->find($data->kode_produk);
        $output = array(
            'no_pemakaian'=>$data->no_pemakaian,
            'kode_produk'=>$data->kode_produk,
            'nama_produk'=>$cek_produk->nama_produk,
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
        $cek_pemakaian = Pemakaian::on($konek)->find($request->no_pemakaian);
        $cek_status = $cek_pemakaian->status;
        if($cek_status == 'POSTED'){  
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Pemakaian: '.$cek_pemakaian->no_pemakaian.' sudah POSTED! Pastikan Anda tidak membuka menu PEMAKAIAN lebih dari 1',
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
            'no_pemakaian'=> 'required',
            'qty'=> 'required',
            'harga'=>'required',
          ]);

          $pemakaiandetail= PemakaianDetail::on($konek)->find($request->id)->update($request->all());

          if($pemakaiandetail){
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


    public function destroy($pemakaiandetail)
    {
        $konek = self::konek();
        $cek_pemakaian2 = PemakaianDetail::on($konek)->find($pemakaiandetail);
        $cek_pemakaian = Pemakaian::on($konek)->find($cek_pemakaian2->no_pemakaian);
        $cek_status = $cek_pemakaian->status;
        if($cek_status == 'POSTED'){  
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Pemakaian: '.$cek_pemakaian->no_pemakaian.' sudah POSTED! Pastikan Anda tidak membuka menu PEMAKAIAN lebih dari 1',
            ];
            return response()->json($message);
        }
        $pemakaiandetail = PemakaianDetail::on($konek)->find($pemakaiandetail);
        try {
            $pemakaiandetail->delete();
            $hitung = PemakaianDetail::on($konek)->where('no_pemakaian', $pemakaiandetail->no_pemakaian)->get();
            $leng = count($hitung);
            $update_pemakaian = Pemakaian::on($konek)->where('no_pemakaian', $pemakaiandetail->no_pemakaian)->first();
            $update_pemakaian->total_item = $leng;
            $update_pemakaian->save();

            if($pemakaiandetail){
                $produk = Produk::on($konek)->find($pemakaiandetail->kode_produk);
                $produk->save();
            }   
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
