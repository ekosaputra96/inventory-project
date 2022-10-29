<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\PenerimaanDetail;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Penerimaan;
use App\Models\Produk;
use App\Models\Konversi;
use App\Models\satuan;
use App\Models\tb_akhir_bulan;
use App\Models\tb_item_bulanan;
use DB;
use Carbon;

class PenerimaandetailController extends Controller
{
    public function index()
    {
        $create_url = route('permintaandetail.create');
        return view('admin.penerimaandetail.index',compact('create_url'));
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
        return Datatables::of(PenerimaanDetail::on($konek)->with('produk','satuan')->where('no_penerimaan',request()->id)->orderBy('created_at','desc'))
           ->addColumn('subtotal', function ($query){
            return $subtotal = round(($query->harga * $query->qty) + ($query->landedcost * $query->qty));
           })->addColumn('action', function ($query){
                return '<a href="javascript:;" data-toggle="tooltip" title="Edit" onclick="edit(\''.$query->id.'\',\''.$query->edit_url.'\')" class="btn btn-warning btn-xs"><i class="fa fa-edit"></i></a>'.'&nbsp'.
                    '<a href="javascript:;" data-toggle="tooltip" title="Hapus" onclick="del(\''.$query->id.'\',\''.$query->destroy_url.'\')" id="hapus" class="btn btn-danger btn-xs"> <i class="fa fa-times-circle"></i></a>'.'&nbsp';
           })->make(true);
    }

    public function getharga()
    {
          $konek = self::konek();
          $cek_penerimaan = Penerimaan::on($konek)->find(request()->id);
          $cek_status = $cek_penerimaan->status;
          if($cek_status == 'POSTED'){  
              $message = [
                  'success' => false,
                  'title' => 'Simpan',
                  'message' => 'Status No. Penerimaan: '.$cek_penerimaan->no_penerimaan.' sudah POSTED! Pastikan Anda tidak membuka menu PENERIMAAN lebih dari 1',
              ];
              return response()->json($message);
          }

        $no_penerimaan = request()->id;
        $data_detail = PenerimaanDetail::on($konek)->where('no_penerimaan',$no_penerimaan)->get();
        $name_produk = array();
        $index = 0;

        foreach ($data_detail as $row) {
            $kode_produk = $row->kode_produk;

            $name_produk[]= array(
                'kode_produk'=>$kode_produk,
            );

            $index++;
        }

                if($name_produk){
                    $leng = count($name_produk);

                    $i = 0;

                    while($i < $leng){
                        $cek_po = PembelianDetail::on($konek)->where('no_pembelian',$cek_penerimaan->no_pembelian)->where('kode_produk',$name_produk[$i]['kode_produk'])->first();

                        $pembelian = Pembelian::on($konek)->find($cek_penerimaan->no_pembelian);
                        $get_diskon = $pembelian->diskon_persen;
                        if($get_diskon > 0){
                            $get_diskon = $pembelian->diskon_persen/100;
                        }

                        $pembeliandetail_leng = PembelianDetail::on($konek)->where('no_pembelian',$cek_penerimaan->no_pembelian)->get();
                        $leng = count($pembeliandetail_leng);

                        $get_diskonrp = $pembelian->diskon_rp/$cek_po->qty;

                        if($get_diskonrp > 0 && $leng == 1){
                            $harga_po = $cek_po->harga;

                            $tabel_baru2 = [
                                'harga'=>$harga_po,
                            ];  
                        }else{
                            $harga_po = $cek_po->harga;

                            $tabel_baru2 = [
                                'harga'=>$harga_po,
                            ];  
                        }

                        $update = PenerimaanDetail::on($konek)->where('no_penerimaan', $no_penerimaan)->where('kode_produk',$name_produk[$i]['kode_produk'])->update($tabel_baru2);

                        $i++;
                    }

                    $message = [
                        'success' => true,
                        'title' => 'Simpan',
                        'message' => 'Harga telah diperbarui.',
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

    public function qtycheck()
    {
        $konek = self::konek();
         $no_pembelian = request()->no;
         $produk = request()->id;
         $satuan = request()->satuan;
         $nilai_qty = request()->qty;

         $pembelian = Pembelian::on($konek)->find($no_pembelian);
         $pembeliandetail1 = PembelianDetail::on($konek)->where('no_pembelian', $no_pembelian)->where('kode_produk',$produk)->first();

         $qty_rec = $pembeliandetail1->qty_received;
         $qty_po = $pembeliandetail1->qty;

         $qty_final = $qty_po - $qty_rec;

        return response()->json($qty_final);
    }
    
    public function getlanded()
    {
         $konek = self::konek();
         $no_pembelian = request()->id;

         $pembelian = Pembelian::on($konek)->find($no_pembelian);
         // dd($pembelian);
         $cek_ongkir = $pembelian->ongkos_angkut;

         if($cek_ongkir != 0){
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Landedcost Tidak Dapat Diubah.',
            ];

            return response()->json($message);
         }else{
            $message = [
                'success' => true,
                'title' => 'Simpan',
                'message' => 'Landedcost Dapat Diubah.',
            ];

            return response()->json($message);
         }
    }

    public function qtyProduk()
    {
        $konek = self::konek();
        $pembeliandetail = PembelianDetail::on($konek)->where('no_pembelian',request()->id)->where('kode_produk', request()->kode_produk)->first();

        $pembeliandetail_leng = PembelianDetail::on($konek)->where('no_pembelian',request()->id)->get();
        $leng = count($pembeliandetail_leng);
        $total_qty = 0;
        foreach ($pembeliandetail_leng as $row){
            $total_qty += $row->qty;
        }

        $pembelian = Pembelian::on($konek)->find(request()->id);
        $get_ongkir = $pembelian->ongkos_angkut;
        $landedcost = $get_ongkir / $total_qty;

        $get_diskon = $pembelian->diskon_persen;
        if($get_diskon > 0){
            $get_diskon = $pembelian->diskon_persen/100;
        }

        $get_diskonrp = $pembelian->diskon_rp;
        if($get_diskonrp > 0){
            $get_diskonrp = $pembelian->diskon_rp/$total_qty;
        }

         $produk = Produk::on($konek)->find(request()->kode_produk);
         
         $cek_nama = Produk::on($konek)->where('id',request()->kode_produk)->where('nama_produk','like','%VULKANISIR%')->first();
         
         $cek_kategori = $produk->kode_kategori;
         $cek_tipe = $produk->tipe_produk;
         $partnumber = $produk->partnumber;
         
         $qty_rec = $pembeliandetail->qty_received;
         $qty_po = $pembeliandetail->qty;
         
         $qty_rcv = 0;

         $penerimaan = Penerimaan::on($konek)->where('no_pembelian', request()->id)->get();
         foreach ($penerimaan as $row2) {
             $detail = PenerimaanDetail::on($konek)->where('no_penerimaan', $row2->no_penerimaan)->where('kode_produk', request()->kode_produk)->first();
             if ($detail != null){
                $qty_rcv += $detail->qty - $detail->qty_retur;
             }
         }

         $qty_sisa = $qty_po - $qty_rcv;
         
         $company = Auth()->user()->kode_company;

        if($get_diskonrp > 0 && $leng == 1){
            $output = array(
                'qty'=>$qty_sisa,
                'harga'=>$pembeliandetail->harga,
                'satuan'=>$pembeliandetail->kode_satuan,
                'tipe'=>$cek_tipe,
                'partnumber'=>$partnumber,
                'kategori'=>$cek_kategori,
                'cek_nama'=>$cek_nama,
                'landedcost'=>$landedcost,
                'company'=>$company,
            );
            return response()->json($output);
        }else{
            $output = array(
                'qty'=>$qty_sisa,
                'harga'=>$pembeliandetail->harga,
                'satuan'=>$pembeliandetail->kode_satuan,
                'tipe'=>$cek_tipe,
                'partnumber'=>$partnumber,
                'kategori'=>$cek_kategori,
                'cek_nama'=>$cek_nama,
                'landedcost'=>$landedcost,
                'company'=>$company,
            );
            return response()->json($output);
        }
    }
    
    
    public function isipart()
    {
        $konek = self::konek();
        $pembelian = Pembelian::on($konek)->where('no_pembelian',request()->id)->first();
        $penerimaan = Penerimaan::on($konek)->where('no_pembelian',request()->id)->first();
        $produk = Produk::on($konek)->where('id',request()->kode_produk)->first();

        //company code
        $kode_company = auth()->user()->kode_company;
        $company = substr($kode_company,1);

        //tipe ban
        $tipe = request()->tipe;

        //merk dan ukuran
        $merk = substr($produk->kode_merek,1,2);
        $ukuran = substr($produk->kode_ukuran,1,2);

        //vendor code
        if (strlen($pembelian->kode_vendor) > 3) {
            $vendor = $pembelian->kode_vendor;
        }else if (strlen($pembelian->kode_vendor) > 2) {
            $vendor = '0'.$pembelian->kode_vendor;
        }else if (strlen($pembelian->kode_vendor) > 1) {
            $vendor = '00'.$pembelian->kode_vendor;
        }else {
            $vendor = '000'.$pembelian->kode_vendor;
        }
        
        // $ven1 = substr($pembelian->kode_vendor,0,1);
        // $ven2 = substr($pembelian->kode_vendor,4,2);
        // $vendor = $ven1.$ven2;
        
        //kategori ban
        $kategori = request()->kategori;
        $kategori2 = request()->kategori2;

        if($kategori == 'ORIGINAL'){
            $kategori = "0";
        }else{
            $kategori = "1";
        }

        //tahun bulan tanggal penerimaan
        $tahun = Carbon\Carbon::parse($penerimaan->tanggal_penerimaan)->format('y');
        $bulan = Carbon\Carbon::parse($penerimaan->tanggal_penerimaan)->format('m');
        $tanggal = Carbon\Carbon::parse($penerimaan->tanggal_penerimaan)->format('d');

        $part1 = $company.$tipe.$merk.$ukuran.$vendor.$kategori.$tahun.$bulan.$tanggal;
        // dd($company,$tipe,$merk,$ukuran,$vendor,$kategori,$tahun,$bulan,$tanggal);
        
        $cek_part = PenerimaanDetail::on($konek)->where(DB::raw('LEFT(partnumber,17)'),$part1)->orderBy('id','desc')->first();
        if ($cek_part != null){
            $parto = substr($cek_part->partnumber,0,17);
            if ($part1 == $parto){
                $kode = substr($cek_part->partnumber,17,3);
                $kode2 = 0;
                $kode3 = 0;
                $kode += 1;
                if ($kode >= 100){
                    $kode = substr($cek_part->partnumber,17,3);
                    $kode += 1;
                    $hasil = $part1.$kode;
                }else if ($kode >= 10){
                    $kode = substr($cek_part->partnumber,17,3);
                    $kode += 1;
                    $hasil = $part1.$kode2.$kode;
                }else {
                    $hasil = $part1.$kode2.$kode3.$kode;
                }
            }else {
                $hasil = $part1.'001';
            }
        }else {
            $hasil = $part1.'001';
        }

        $output = array(
                'hasil'=>$hasil,
                );
        return response()->json($output);
    }

    public function checkpart()
    {
        $konek = self::konek();
        $partnumberlama = request()->part;
        $produk = request()->kode_produk;

        $cek_serial = PemakaianbanDetail::on($konek)->where('partnumber',$partnumberlama)->first();

        if($cek_serial == null){
            $message = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Nomor Serial Number Lama Tidak Terdaftar.'
            ];
            return response()->json($message);
        }
    }
    
    public function qtyProduk2()
    {
        $konek = self::konek();
        $pembeliandetail = PembelianDetail::on($konek)->where('no_pembelian',request()->id)->where('kode_produk', request()->kode_produk)->first();

        $qty_rec = $pembeliandetail->qty_received;
        $qty_po = $pembeliandetail->qty;

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


    public function store(Request $request)
    {
        $konek = self::konek();
        $cek_penerimaan = Penerimaan::on($konek)->find($request->no_penerimaan);
        $cek_status = $cek_penerimaan->status;
        if($cek_status == 'POSTED'){
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Penerimaan: '.$cek_penerimaan->no_penerimaan.' sudah POSTED! Pastikan Anda tidak membuka menu PENERIMAAN lebih dari 1',
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
                        'message' => 'Nilai Qty tidak boleh kurang dari 1'
                ];
            return response()->json($message);
        }

        $penerimaandetail = PenerimaanDetail::on($konek)->where('no_penerimaan', $request->no_penerimaan)->where('kode_produk', $request->kode_produk)->where('partnumber', $request->partnumber)->get();

        $cek_serial = Produk::on($konek)->where('id',$request->kode_produk)->first();
        $cek_tipe = $cek_serial->tipe_produk;
        $cek_kategori = $cek_serial->kode_kategori;

        if ($cek_tipe != 'Serial'){
            
                $leng = count($penerimaandetail);
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
                $leng = count($penerimaandetail);
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

            $cek_qty_beli = PembelianDetail::on($konek)->where('no_pembelian',$request->no_pembelian)->where('kode_produk',$request->kode_produk)->first();               
            $cek_qty_recv = $cek_qty_beli->qty_received;
            $cek_qty_beli2 = $cek_qty_beli->qty - $cek_qty_recv;

            $produk = Produk::on($konek)->where('id', request()->kode_produk)->first();
            $tipe_produk = $produk->tipe_produk;
            $tipe_kategori = $produk->kode_kategori;


            if ($tipe_produk == 'Serial'){
                if($tipe_kategori == 'UNIT' || $tipe_kategori == 'BAN'){
                    $cek_part = tb_item_bulanan::on($konek)->where('kode_produk',$request->kode_produk)->where('partnumber',$request->partnumber)->first();
                    $cek_part2 = PenerimaanDetail::on($konek)->where('no_penerimaan',$request->no_penerimaan)->where('kode_produk',$request->kode_produk)->where('partnumber',$request->partnumber)->first();
                    $cek_qty_terima = PenerimaanDetail::on($konek)->where('no_penerimaan', $request->no_penerimaan)->where('kode_produk', $request->kode_produk)->get();
                    $cek_qty_terima2 = count($cek_qty_terima);

                    if ($cek_part != null || $cek_part2 != null){
                        $message = [
                            'success' => false,
                            'title' => 'Gagal',
                            'message' => 'Part Number sudah pernah di input.'
                        ];
                        return response()->json($message);
                    }

                    if ($cek_qty_terima2 >= $cek_qty_beli2){
                        $message = [
                            'success' => false,
                            'title' => 'Gagal',
                            'message' => 'Qty melebihi jumlah pembelian.'
                        ];
                        return response()->json($message);
                    }
                }
            }

            $penerimaandetail = PenerimaanDetail::on($konek)->create($request->all());
            $produk = Produk::on($konek)->where('id', request()->kode_produk)->first();
            $tipe_produk = $produk->tipe_produk;
            $tipe_kategori = $produk->kode_kategori;

            if ($tipe_produk == 'Serial'){
                if($tipe_kategori == 'UNIT' || $tipe_kategori == 'BAN'){
                    $update_penerimaan = PenerimaanDetail::on($konek)->where('no_penerimaan', $request->no_penerimaan)->where('kode_produk', $request->kode_produk)->first();
                    $update_penerimaan->qty = 1;
                    $update_penerimaan->save();
                }
            }

            $hitung = PenerimaanDetail::on($konek)->where('no_penerimaan', $request->no_penerimaan)->get();
            $leng = count($hitung);

            $update_penerimaan = Penerimaan::on($konek)->where('no_penerimaan', $request->no_penerimaan)->first();
            $update_penerimaan->total_item = $leng;
            $update_penerimaan->save();

            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data telah Disimpan'
            ];
            return response()->json($message);
                
    }


    public function edit($penerimaandetail)
    {
        $konek = self::konek();
        $id = $penerimaandetail;
        $data = PenerimaanDetail::on($konek)->find($id);
        $cek_produk = Produk::on($konek)->find($data->kode_produk);
        $output = array(
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
        $cek_penerimaan = Penerimaan::on($konek)->find($request->no_penerimaan);
        $cek_status = $cek_penerimaan->status;
        if($cek_status == 'POSTED'){
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Penerimaan: '.$cek_penerimaan->no_penerimaan.' sudah POSTED! Pastikan Anda tidak membuka menu PENERIMAAN lebih dari 1',
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
                    $penerimaandetail = PenerimaanDetail::on($konek)->find($request->id)->update($request->all());
                    
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

              $penerimaandetail = PenerimaanDetail::on($konek)->find($request->id)->update($request->all());

              if($penerimaandetail){
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


    public function destroy($penerimaandetail)
    {
        $konek = self::konek();
        $cek_penerimaan2 = PenerimaanDetail::on($konek)->find($penerimaandetail);
        $cek_penerimaan = Penerimaan::on($konek)->find($cek_penerimaan2->no_penerimaan);
        $cek_status = $cek_penerimaan->status;
        if($cek_status == 'POSTED'){  
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Status No. Penerimaan: '.$cek_penerimaan->no_penerimaan.' sudah POSTED! Pastikan Anda tidak membuka menu PENERIMAAN lebih dari 1',
            ];
            return response()->json($message);
        }
        $penerimaandetail = PenerimaanDetail::on($konek)->find($penerimaandetail);
        $cek_produk = Produk::on($konek)->where('id', $penerimaandetail->kode_produk)->first();
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
                    $penerimaandetail->delete();
                    
                    $bulan = tb_akhir_bulan::on($konek)->where('status_periode','Open')->first();
                    $periode = $bulan->periode;
                    $bulanan = tb_item_bulanan::on($konek)->where('kode_produk', $penerimaandetail->kode_produk)->where('partnumber',$penerimaandetail->partnumber)->where('periode', $periode)->delete();

                    $hitung = PenerimaanDetail::on($konek)->where('no_penerimaan', $penerimaandetail->no_penerimaan)->get();
                    $leng = count($hitung);

                    $update_penerimaan = Penerimaan::on($konek)->where('no_penerimaan', $penerimaandetail->no_penerimaan)->first();
                    $update_penerimaan->total_item = $leng;
                    $update_penerimaan->save();

                    if($penerimaandetail){
                        $produk = Produk::on($konek)->find($penerimaandetail->kode_produk);
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
        
                $penerimaandetail->delete();

                $hitung = PenerimaanDetail::on($konek)->where('no_penerimaan', $penerimaandetail->no_penerimaan)->get();
                $leng = count($hitung);
                $update_penerimaan = Penerimaan::on($konek)->where('no_penerimaan', $penerimaandetail->no_penerimaan)->first();
                $update_penerimaan->total_item = $leng;
                $update_penerimaan->save();

                if($penerimaandetail){
                    $produk = Produk::on($konek)->find($penerimaandetail->kode_produk);
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
