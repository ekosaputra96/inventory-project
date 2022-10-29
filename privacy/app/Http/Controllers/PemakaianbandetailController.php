<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Pemakaianban;
use App\Models\PemakaianbanDetail;
use App\Models\tb_item_bulanan;
use App\Models\tb_akhir_bulan;
use App\Models\Produk;
use App\Models\Konversi;
use App\Models\satuan;
use DB;
use Carbon;

class PemakaianbandetailController extends Controller
{
    public function index()
    {
        
        $create_url = route('pemakaianbandetail.create');
        return view('admin.pemakaianbandetail.index',compact('create_url'));

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
        return Datatables::of(PemakaianbanDetail::on($konek)->with('produk','satuan')->where('no_pemakaianban',request()->id)->orderBy('created_at','desc'))
           ->addColumn('subtotal', function ($query){
            return $subtotal = $query->harga * $query->qty;
           })->addColumn('action', function ($query){
            $konek = self::konek();
            $cek_produk = Produk::on($konek)->where('id',$query->kode_produk)->first();
            $cek = $cek_produk->tipe_produk;
            $cek2 = $cek_produk->kode_kategori;
            if ($cek == 'Serial' && $kode_kategori = 'BAN'){
                return 
                    '<a href="javascript:;" data-toggle="tooltip" title="Hapus" onclick="del(\''.$query->id.'\',\''.$query->destroy_url.'\')" id="hapus" class="btn btn-danger btn-xs"> <i class="fa fa-times-circle"></i></a>';
                 }
            else {
                return '<a href="javascript:;" data-toggle="tooltip" title="Edit" onclick="edit(\''.$query->id.'\',\''.$query->edit_url.'\')" class="btn btn-warning btn-xs"><i class="fa fa-edit"></i></a>'.'&nbsp'.
                    '<a href="javascript:;" data-toggle="tooltip" title="Hapus" onclick="del(\''.$query->id.'\',\''.$query->destroy_url.'\')" id="hapus" class="btn btn-danger btn-xs"> <i class="fa fa-times-circle"></i></a>'.'&nbsp';
            }
           })->make(true);
    }

    public function gethpp()
    {
      $konek = self::konek();
      $cek_pemakaianban = Pemakaianban::on($konek)->find(request()->id);
      $cek_status = $cek_pemakaianban->status;
      if($cek_status == 'POSTED'){  
          $message = [
              'success' => false,
              'title' => 'Simpan',
              'message' => 'Status No. Pemakaian Ban: '.$cek_pemakaianban->no_pemakaianban.' sudah POSTED! Pastikan Anda tidak membuka menu PEMAKAIAN BAN lebih dari 1',
          ];
          return response()->json($message);
      }

                $no_pemakaianban = request()->id;
                $data_detail = PemakaianbanDetail::on($konek)->where('no_pemakaianban',$no_pemakaianban)->get();
                $name_produk = array();
                $index = 0;

                foreach ($data_detail as $row) {
                  $produk = $row->partnumberbaru;
                  $kode_produk = $row->kode_produk;

                  $name_produk[]= array(
                      'partnumberbaru'=>$produk,
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
                      $no_mesin = tb_item_bulanan::on($konek)->where('partnumber',$name_produk[$i]['partnumberbaru'])->where('kode_produk',$name_produk[$i]['kode_produk'])->where('kode_lokasi',$lokasi)->where('periode',$cek_bulan->periode)->first();
                      
                      $get_hpp = $no_mesin->hpp;

                      $tabel_baru2 = [
                            'harga'=>$get_hpp,
                      ];

                      $update = PemakaianbanDetail::on($konek)->where('no_pemakaianban', $no_pemakaianban)->where('kode_produk',$name_produk[$i]['kode_produk'])->where('partnumberbaru',$name_produk[$i]['partnumberbaru'])->update($tabel_baru2);

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
        $monthly = tb_item_bulanan::on($konek)->where('kode_produk',request()->id)->where('kode_lokasi',$lokasi)->where('periode',$cek_bulan->periode)->first();

        if($monthly != null){
            if ($produk->tipe_produk == 'Serial' && $produk->kode_kategori == 'BAN'){
                $output = array(
                'stok'=>$monthly->ending_stock,           
                'hpp'=>$monthly->hpp,
                'tipe'=>$produk->tipe_produk,
                'kategori'=>$produk->kode_kategori,
                // 'satuan'=>$produk->kode_satuan,
                );
            }else {
                $output = array(
                'stok'=>$monthly->ending_stock,           
                'hpp'=>$monthly->hpp,
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
         $monthly = tb_item_bulanan::on($konek)->where('kode_produk',request()->id)->where('partnumber',request()->part)->where('kode_lokasi',$lokasi)->where('periode',$cek_bulan->periode)->first();
         
         $output = array(
            'stok'=>$monthly->ending_stock,
            'hpp'=>$monthly->hpp,
        );
        return response()->json($output);
    }

    public function getharga2()
    {
        $konek = self::konek();
        $lokasi = auth()->user()->kode_lokasi;
        $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode', 'Open')->orwhere('reopen_status', 'true')->first();
        $monthly = tb_item_bulanan::on($konek)->where('kode_produk',request()->id)->where('partnumber',request()->part)->where('kode_lokasi',$lokasi)->where('periode',$cek_bulan->periode)->first();

        $output = array(
            'stok'=>$monthly->ending_stock,
            'hpp'=>$monthly->hpp,
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
        $states2 = tb_item_bulanan::on($konek)->where('kode_produk',$request->kode_produk)->where('ending_stock',0)->pluck("partnumber","partnumber")->all();
        
        return response()->json(['options'=>$states2]);
            
    }

    public function selectpart2(Request $request)
    {
        $konek = self::konek();
        $lokasi = auth()->user()->kode_lokasi;
        $states2 = tb_item_bulanan::on($konek)->where('kode_produk',$request->kode_produk)->where('kode_lokasi',$lokasi)->where('ending_stock',1)->pluck("partnumber","partnumber")->all();
        
        return response()->json(['options'=>$states2]);
            
    }

    public function selectpart3(Request $request)
    {
        $konek = self::konek();
        $states2 = tb_item_bulanan::on($konek)->where('kode_produk',$request->kode_produk)->where('ending_stock',1)->where('out_stock',1)->pluck("partnumber","partnumber")->all();
        
        return response()->json(['options'=>$states2]);
            
    }

    public function store(Request $request)
    {   
        $konek = self::konek();
        $pemakaianbandetail = PemakaianbanDetail::on($konek)->create($request->all());

        if($pemakaianbandetail){
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
        $cek_pemakaianban = Pemakaianban::on($konek)->find($request->no_pemakaianban);
        $cek_status = $cek_pemakaianban->status;
        if($cek_status == 'POSTED'){  
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Pemakaian Ban: '.$cek_pemakaianban->no_pemakaianban.' sudah POSTED! Pastikan Anda tidak membuka menu PEMAKAIAN BAN lebih dari 1',
            ];
            return response()->json($message);
        }

        if($request->partnumberbaru == ''){
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
            $pemakaianbandetail = PemakaianbanDetail::on($konek)->where('no_pemakaianban', $request->no_pemakaianban)->where('kode_produk', $request->kode_produk)->where('partnumber', $request->partnumber)->get();

            $pemakaianbandetail2 = PemakaianbanDetail::on($konek)->where('no_pemakaianban', $request->no_pemakaianban)->where('kode_produk', $request->kode_produk)->where('partnumberbaru', $request->partnumberbaru)->get();

            $leng = count($pemakaianbandetail);
            $leng2 = count($pemakaianbandetail2);

                if($leng > 0 || $leng2 > 0){
                    $message = [
                        'success' => false,
                        'title' => 'Gagal',
                        'message' => 'Serial Number Baru / Serial Number Lama Sudah Ada',
                        ];
                    return response()->json($message);
                }
                else{
                    $pemakaianbandetail = PemakaianbanDetail::on($konek)->create($request->all());

                    $produk = Produk::on($konek)->where('id', request()->kode_produk)->first();
                    $tipe_produk = $produk->tipe_produk;
                    $hitung = PemakaianbanDetail::on($konek)->where('no_pemakaianban', $request->no_pemakaianban)->get();

                    $leng = count($hitung);
                    $update_pemakaian = Pemakaianban::on($konek)->where('no_pemakaianban', $request->no_pemakaianban)->first();
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
        $konek = self::konek();
        $produk = request()->kode_produk;
        $satuan = request()->satuan;

        $kode_produk = Produk::on($konek)->where('id',$produk)->first();
        $stok = $kode_produk->stok;

        return response()->json($stok);
    }

    public function edit($pemakaianbandetail)
    {
        $konek = self::konek();
        $id = $pemakaianbandetail;
        $data = PemakaianbanDetail::on($konek)->find($id);
        $output = array(
            'no_pemakaianban'=>$data->no_pemakaianban,
            'kode_produk'=>$data->kode_produk,
            'kode_satuan'=>$data->kode_satuan,
            'qty'=>$data->qty,
            'harga'=>$data->harga,
            'id'=>$data->id,
        );
        return response()->json($output);
    }

    public function updateAjax(Request $request)
    {
        $konek = self::konek();
        $cek_pemakaianban = Pemakaianban::on($konek)->find($request->no_pemakaianban);
        $cek_status = $cek_pemakaianban->status;
        if($cek_status == 'POSTED'){
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Pemakaian Ban: '.$cek_pemakaianban->no_pemakaianban.' sudah POSTED! Pastikan Anda tidak membuka menu PEMAKAIAN BAN lebih dari 1',
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
            'no_pemakaianban'=> 'required',
            'qty'=> 'required',
            'harga'=>'required',
          ]);

          $pemakaianbandetail= PemakaianbanDetail::on($konek)->find($request->id)->update($request->all());

          if($pemakaianbandetail){
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


    public function destroy($pemakaianbandetail)
    {
        $konek = self::konek();
        $cek_pemakaianban2 = PemakaianbanDetail::on($konek)->find($pemakaianbandetail);
        $cek_pemakaianban = Pemakaianban::on($konek)->find($cek_pemakaianban2->no_pemakaianban);
        $cek_status = $cek_pemakaianban->status;
        if($cek_status == 'POSTED'){  
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Pemakaian Ban: '.$cek_pemakaianban->no_pemakaianban.' sudah POSTED! Pastikan Anda tidak membuka menu PEMAKAIAN BAN lebih dari 1',
            ];
            return response()->json($message);
        }
        $pemakaianbandetail = PemakaianbanDetail::on($konek)->find($pemakaianbandetail);
        try {
            $pemakaianbandetail->delete();

            $hitung = PemakaianbanDetail::on($konek)->where('no_pemakaianban', $pemakaianbandetail->no_pemakaianban)->get();

            $leng = count($hitung);

            $update_pemakaian = Pemakaianban::on($konek)->where('no_pemakaianban', $pemakaianbandetail->no_pemakaianban)->first();
            $update_pemakaian->total_item = $leng;
            $update_pemakaian->save();

            if($pemakaianbandetail){
                $produk = Produk::on($konek)->find($pemakaianbandetail->kode_produk);
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
