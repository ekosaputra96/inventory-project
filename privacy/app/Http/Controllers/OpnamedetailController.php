<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\OpnameDetail;
use App\Models\Opname;
use App\Models\Produk;
use App\Models\satuan;
use App\Models\Merek;
use App\Models\Ukuran;
use App\Models\user_history;
use App\Models\tb_item_bulanan;
use App\Models\tb_akhir_bulan;
use App\Exports\UsersExport;
use App\Imports\UsersImport;
use App\Exports\FormopnameExport;
use Maatwebsite\Excel\Facades\Excel;
use DB;

class OpnamedetailController extends Controller
{
    public function index()
    {
        $create_url = route('opnamedetail.create');
        return view('admin.opnamedetail.index',compact('create_url'));
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

    public function getDatabyID()
    {
        $konek = self::konek();
        return Datatables::of(OpnameDetail::on($konek)->with('produk','satuan')->where('no_opname',request()->id)->orderBy('created_at','desc'))->addColumn('action', function ($query){
            return
                $btn_destroy = '<a href="javascript:;" data-toggle="tooltip" title="Hapus" class="btn btn-danger btn-xs" onclick="del(\''.$query->id.'\',\''.$query->destroy_url.'\')" id="hapus" class=""><i class="fa fa-times-circle"></i></a>';
            })->make(true);
    }
    
    public function exportPDF()
    {
        $konek = self::konek();
        $no_opname = $_GET['no_opname'];
        
        $nama = auth()->user()->name;
        $tmp = ['nama' => $nama,'aksi' => 'Print Form Opname : '.$no_opname.'.','created_by'=>$nama,'updated_by'=>$nama];
        user_history::on($konek)->create($tmp);
        
        return Excel::download(new FormopnameExport($no_opname), 'Form Opname '.$no_opname.'.xlsx');
    }

    public function satuanproduk()
    {
      $konek = self::konek();
         $produk = Produk::on($konek)->find(request()->id);
         $lokasi = auth()->user()->kode_lokasi;
         $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode', 'Open')->orwhere('reopen_status', 'true')->first();
         $monthly = tb_item_bulanan::on($konek)->where('kode_produk',request()->id)->where('kode_lokasi',$lokasi)->where('periode',$cek_bulan->periode)->first();
         
         $hpp = number_format($monthly->ending_amount/$monthly->ending_stock,2, '.', '');
         $output = array(
            'stok'=>$monthly->ending_stock,
            'tipe_produk'=>$produk->tipe_produk,
            'kode_kategori'=>$produk->kode_kategori,
            'partnumber'=>$monthly->partnumber,
            'no_mesin'=>$monthly->no_mesin,
            'satuan'=>$produk->kode_satuan,
            'hpp'=>$hpp,
        );
        return response()->json($output);
    }

    public function getharga()
    {
      $konek = self::konek();
         $monthly = tb_item_bulanan::on($konek)->where('kode_produk',request()->id)->where('partnumber',request()->part)->first();
         // dd($monthly);
         $hpp = number_format($monthly->ending_amount/$monthly->ending_stock,2, '.', '');
         $output = array(
            'stok'=>$monthly->ending_stock,
            'hpp'=>$hpp,
            'no_mesin'=>$monthly->no_mesin,
        );
        return response()->json($output);
    }

    public function createall()
    {
      $konek = self::konek();
      $cek_opname = Opname::on($konek)->find(request()->id);
      $cek_status = $cek_opname->status;
      if($cek_status == 'POSTED'){  
          $message = [
              'success' => false,
              'title' => 'Simpan',
              'message' => 'Status No. Opname: '.$cek_opname->no_opname.' sudah POSTED! Pastikan Anda tidak membuka menu OPNAME lebih dari 1',
          ];
          return response()->json($message);
      }
                $no_opname = request()->id;
                
                $produk= Produk::on($konek)->with('kategoriproduk','satuan')->get();
                $lokasi = auth()->user()->kode_lokasi;
                $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode', 'Open')->orwhere('reopen_status', 'true')->first();
                $monthly = tb_item_bulanan::on($konek)->where('kode_lokasi',$lokasi)->where('periode',$cek_bulan->periode)->get();
                // dd($produk);
                $isidetail= array();

                foreach ($monthly as $row) {
                  $cek_no = OpnameDetail::on($konek)->where('no_opname', $no_opname)->where('kode_produk', $row->kode_produk)->where('partnumber', $row->partnumber)->first();
                  if ($cek_no == null){
                      $isidetail[] = array(
                       'no_opname'=>$no_opname,
                       'kode_produk'=>$row->kode_produk,
                       'partnumber'=>$row->partnumber,
                       'no_mesin'=>$row->no_mesin,
                       'hpp'=>$row->hpp,
                       'stok'=>$row->ending_stock,
                       'created_at'=>date('Y-m-d H:i:s'),
                       'updated_at'=>date('Y-m-d H:i:s'),
                       'created_by'=>Auth()->user()->id,
                       'updated_by'=>Auth()->user()->id,
                      );

                  }
                }

                $simpandetail= OpnameDetail::on($konek)->insert($isidetail);

                $hitung = OpnameDetail::on($konek)->where('no_opname', $no_opname)->get();

                $leng = count($hitung);

                $update_opname = Opname::on($konek)->where('no_opname', $no_opname)->first();
                $update_opname->total_item = $leng;
                $update_opname->save();

                $message = [
                    'success' => true,
                    'title' => 'Simpan',
                    'message' => 'Data telah Disimpan.',
                ];

                return response()->json($message);
    }

    public function hitungselisih()
    {
      $konek = self::konek();
      $cek_opname = Opname::on($konek)->find(request()->id);
      $cek_status = $cek_opname->status;
      if($cek_status == 'POSTED'){  
          $message = [
              'success' => false,
              'title' => 'Simpan',
              'message' => 'Status No. Opname: '.$cek_opname->no_opname.' sudah POSTED! Pastikan Anda tidak membuka menu OPNAME lebih dari 1',
          ];
          return response()->json($message);
      }

      $no_opname = request()->id;
      $data_detail = OpnameDetail::on($konek)->where('no_opname',$no_opname)->get();

      foreach ($data_detail as $row) {
          $opname_detail = OpnameDetail::on($konek)->where('no_opname', $no_opname)->where('kode_produk',$row->kode_produk)->where('partnumber',$row->partnumber)->first();

          $produk_nama = Produk::on($konek)->find($row->kode_produk);
          $get_kode = $produk_nama->id;
          $get_satuan = $produk_nama->kode_satuan;
                    
          $stock_op = $opname_detail->stok;
          $qty_op = $opname_detail->qty_checker3;

          $harga_op = $opname_detail->hpp;

          $selisih = $qty_op - $stock_op;
          $nilai = $selisih * $harga_op;

          $tabel_baru2 = [
              'kode_produk'=>$get_kode,
              'kode_satuan'=>$get_satuan,
              'stock_opname'=>$selisih,
              'amount_opname'=>$nilai,
          ];

          $update = OpnameDetail::on($konek)->where('no_opname', $no_opname)->where('kode_produk',$row->kode_produk)->where('partnumber',$row->partnumber)->update($tabel_baru2);
      }

      $hitung = OpnameDetail::on($konek)->where('no_opname', $no_opname)->get();
      $leng = count($hitung);

      $update_opname = Opname::on($konek)->where('no_opname', $no_opname)->first();
      $update_opname->total_item = $leng;
      $update_opname->save();
                
      $message = [
          'success' => true,
          'title' => 'Simpan',
          'message' => 'Hitung Selisih berhasil.',
      ];

      return response()->json($message);  
    }

    public function hapusdetail()
    {
      $konek = self::konek();
      $cek_opname = Opname::on($konek)->find(request()->id);
      $cek_status = $cek_opname->status;
      if($cek_status == 'POSTED'){  
          $message = [
              'success' => false,
              'title' => 'Simpan',
              'message' => 'Status No. Opname: '.$cek_opname->no_opname.' sudah POSTED! Pastikan Anda tidak membuka menu OPNAME lebih dari 1',
          ];
          return response()->json($message);
      }
          $no_opname = request()->id;
          $data_detail = OpnameDetail::on($konek)->where('no_opname',$no_opname)->delete();
          $hitung = OpnameDetail::on($konek)->where('no_opname', $no_opname)->get();
          $leng = count($hitung);

          $update_opname = Opname::on($konek)->where('no_opname', $no_opname)->first();
          $update_opname->total_item = $leng;
          $update_opname->save();

          $message = [
              'success' => true,
              'title' => 'Simpan',
              'message' => 'Data telah di hapus.',
          ];

          return response()->json($message);
    }

    public function hapusitem()
    {
      $konek = self::konek();
      $cek_opname = Opname::on($konek)->find(request()->id);
      $cek_status = $cek_opname->status;
      if($cek_status == 'POSTED'){  
          $message = [
              'success' => false,
              'title' => 'Simpan',
              'message' => 'Status No. Opname: '.$cek_opname->no_opname.' sudah POSTED! Pastikan Anda tidak membuka menu OPNAME lebih dari 1',
          ];
          return response()->json($message);
      }

          $no_opname = request()->id;
          $kode_produk = request()->kode;
          $partnumber = request()->partnumber;

          if($kode_produk != null){
              $data_detail = OpnameDetail::on($konek)->where('no_opname',$no_opname)->where('kode_produk',$kode_produk)->where('partnumber',$partnumber)->delete();
                  
              $hitung = OpnameDetail::on($konek)->where('no_opname', $no_opname)->get();
              $leng = count($hitung);

              $update_opname = Opname::on($konek)->where('no_opname', $no_opname)->first();
              $update_opname->total_item = $leng;
              $update_opname->save();

              $message = [
                    'success' => true,
                    'title' => 'Simpan',
                    'message' => 'Data telah dihapus.',
              ];

                  return response()->json($message);
          }

          $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'Pilih item terlebih dahulu.',
          ];
          return response()->json($message);
    }

    public function stockProduk()
    {
      $konek = self::konek();
         $produk = Produk::on($konek)->find(request()->id);
         $lokasi = auth()->user()->kode_lokasi;
         $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode', 'Open')->orwhere('reopen_status', 'true')->first();
         $detail = tb_item_bulanan::on($konek)->where('kode_produk',request()->id)->where('kode_lokasi',$lokasi)->where('periode',$cek_bulan->periode)->first();

         $output = array(
            'hpp'=>$detail->hpp,
            'stok'=>$detail->ending_stock,
            'kode_satuan'=>$produk->kode_satuan,
            'no_mesin'=>$detail->no_mesin,
            'partnumber'=>$detail->partnumber,
         );
         return response()->json($output);
    }

    public function selectpart(Request $request)
    {
      $konek = self::konek();
        $states2 = tb_item_bulanan::on($konek)->where('kode_produk',$request->kode_produk)->pluck("partnumber","partnumber")->all();
        
        return response()->json(['options'=>$states2]);
            
    }


    public function store(Request $request)
    {
      $konek = self::konek();
      $cek_opname = Opname::on($konek)->find($request->no_opname);
      $cek_status = $cek_opname->status;
      if($cek_status == 'POSTED'){  
          $message = [
              'success' => false,
              'title' => 'Simpan',
              'message' => 'Status No. Opname: '.$cek_opname->no_opname.' sudah POSTED! Pastikan Anda tidak membuka menu OPNAME lebih dari 1',
          ];
          return response()->json($message);
      }

        $opnamedetail = OpnameDetail::on($konek)->where('no_opname', $request->no_opname)->where('partnumber', $request->partnumber)->where('kode_produk', $request->kode_produk)->first();

        $cek_tipe = Produk::on($konek)->find($request->kode_produk);
        $tipe_produk = $cek_tipe->tipe_produk;
        // dd($tipe_produk);

        if($opnamedetail == null && $tipe_produk == 'Serial'){
            $opnamedetail = OpnameDetail::on($konek)->create($request->all());
            $hitung = OpnameDetail::on($konek)->where('no_opname', $request->no_opname)->get();
            $leng = count($hitung);

            $update_opname = Opname::on($konek)->where('no_opname', $request->no_opname)->first();
            $update_opname->total_item = $leng;
            $update_opname->save();

            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data telah di Update.'
                ];
            return response()->json($message);
        }
        else if($opnamedetail == null){
            $opnamedetail = OpnameDetail::on($konek)->create($request->all());
            $hitung = OpnameDetail::on($konek)->where('no_opname', $request->no_opname)->get();
            $leng = count($hitung);

            $update_opname = Opname::on($konek)->where('no_opname', $request->no_opname)->first();
            $update_opname->total_item = $leng;
            $update_opname->save();

            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data telah di Update.'
                ];
            return response()->json($message);
        }
        else{
            $message = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Produk Sudah Ada.'
            ];
            return response()->json($message);
        }
    }

    
    public function edit($opnamedetail)
    {
      $konek = self::konek();
        $id = $opnamedetail;
        $data = OpnameDetail::on($konek)->find($id);
        $output = array(
            'no_opname'=>$data->no_opname,
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
      $konek =self::konek();
      $cek_opname = Opname::on($konek)->find(request()->no_opname);
      $cek_status = $cek_opname->status;
      if($cek_status == 'POSTED'){  
          $message = [
              'success' => false,
              'title' => 'Simpan',
              'message' => 'Status No. Opname: '.$cek_opname->no_opname.' sudah POSTED! Pastikan Anda tidak membuka menu OPNAME lebih dari 1',
          ];
          return response()->json($message);
      }

        $Opname = OpnameDetail::on($konek)->where('kode_produk',request()->kode_produk)->where('partnumber',request()->partnumber)->where('no_opname',request()->no_opname)->first();

        $Produk = Produk::on($konek)->where('id',request()->kode_produk)->first();
        $kode_satuan = $Produk->kode_satuan;
        $stock = $request->stok;
        $hpp = $request->hpp;
        $qty_checker3 = $request->qty_checker3;

        $Opname->qty_checker1 = $request->qty_checker1;
        $Opname->qty_checker2 = $request->qty_checker2;
        $Opname->qty_checker3 = $request->qty_checker3;

        $Opname->stok = $request->stok;
        $Opname->hpp = $request->hpp;
        $Opname->no_mesin = $request->no_mesin;

        if($request->kode_satuan == null){
            $Opname->kode_satuan = $kode_satuan;
        }

        $Opname->stock_opname = $qty_checker3 - $stock;
        $Opname->amount_opname = ($qty_checker3 - $stock) * $hpp;
        // dd($selisih_qty);
        
        $Opname->save();

        $message = [
            'success' => true,
            'title' => 'Update',
            'message' => 'Data telah di Update.'
            ];
        return response()->json($message);
    }


    public function getdata()
    {
      $konek = self::konek();
        $opnamedetail = OpnameDetail::on($konek)->where('no_opname',request()->no_opname)->where('kode_produk',request()->kode_produk)->where('partnumber',request()->partnumber)->first();
        // dd($opnamedetail);

        if($opnamedetail){
                $output = array(
                        'no_opname'=>$opnamedetail->no_opname,
                        'kode_produk'=>$opnamedetail->kode_produk,
                        'partnumber'=>$opnamedetail->partnumber,
                        'no_mesin'=>$opnamedetail->no_mesin,
                        'kode_satuan'=>$opnamedetail->kode_satuan,
                        'hpp'=>$opnamedetail->hpp,
                        'stok'=>$opnamedetail->stok,
                        'qty_checker1'=>$opnamedetail->qty_checker1,
                        'qty_checker2'=>$opnamedetail->qty_checker2,
                        'qty_checker3'=>$opnamedetail->qty_checker3,
                    );

            return response()->json($output);
        }else{
            $message = [
                'success' => false,
                'title' => 'Get Data',
                'message' => 'Gagal Mengambil Data.'
                ];
                return response()->json($message);
        }
    }


    public function destroy($opnamedetail)
    {
        // dd($opnamedetail);
      $konek = self::konek();
      $opnamedetail = OpnameDetail::on($konek)->find($opnamedetail);

            $opnamedetail->delete();
            
            if($opnamedetail){
                $produk = Produk::on($konek)->find($opnamedetail->kode_produk);
                $produk->save();

                $hitung = OpnameDetail::on($konek)->where('no_opname', $opnamedetail->no_opname)->get();
                $leng = count($hitung);
                $update_opname = Opname::on($konek)->where('no_opname', $opnamedetail->no_opname)->first();
                $update_opname->total_item = $leng;
                $update_opname->save();
            }   

           alert()->success('Data Telah Di-Hapus', 'BERHASIL')->persistent('Close');
    }

}
