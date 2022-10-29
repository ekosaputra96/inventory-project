<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Konversi;
use App\Models\Produk;
use App\Models\PembelianDetail;
use App\Models\PemakaianDetail;
use App\Models\AdjustmentDetail;
use App\Models\satuan;
use App\Models\tb_akhir_bulan;
use App\Models\MasterLokasi;
use App\Models\Company;
use Carbon;
use PDF;
use Excel;
use DB;

class KonversiController extends Controller
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
        }else if ($compa == '22'){
            $koneksi = 'mysqlskt';
        }else if ($compa == '03'){
            $koneksi = 'mysqlemkl';   
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
        $konek = self::konek();
        $create_url = route('konversi.create');
        $produk = Produk::on($konek)->orderBy('nama_produk','asc')->pluck('nama_produk','id');
        $Satuan= satuan::pluck('nama_satuan', 'kode_satuan');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;
        
        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        return view('admin.konversi.index',compact('create_url','produk','Satuan','period', 'nama_lokasi','nama_company'));
        
    }

    public function anyData()
    {
        $konek = self::konek();
        return Datatables::of(Konversi::on($konek)->with('produk','satuan')->orderBy('kode_produk','asc'))->make(true);
    }

    public function satuan_produk()
    {
        $konek = self::konek();
        $produk = Produk::on($konek)->with('satuan')->find(request()->id);
        $output = array(
            'kode_satuan'=>$produk->satuan->kode_satuan,
            'satuan'=>$produk->satuan->nama_satuan,
        );
        return response()->json($output);
    }

    public function satuan_produk2()
    {
        $konek = self::konek();
        $satuan = satuan::find(request()->kode);
        $output = array(
            'kode_satuan'=>$satuan->kode_satuan,
            'satuan_terbesar'=>$satuan->nama_satuan,
        );
        return response()->json($output);
    }

    public function satuan_produk3()
    {
        $konek = self::konek();
        $satuan = satuan::find(request()->kode);
        $output = array(
            'kode_satuan'=>$satuan->nama_satuan,
        );
        return response()->json($output);
    }

    public function store(Request $request)
    {
        $konek = self::konek();
        $kode_produk = $request->kode_produk;
        $kode_satuan = $request->kode_satuan;

        $cek_satuan = Konversi::on($konek)->where('kode_produk',$kode_produk)->where('kode_satuan',$kode_satuan)->first();
        if ($cek_satuan == null ){
                $cek_satuan2 = Konversi::on($konek)->where('kode_produk',$kode_produk)->first();
                if($cek_satuan2 != null){
                    $cek_nilai = $request->nilai_konversi;
                    if($cek_nilai <= 1){
                        $message = [
                            'success' => false,
                            'title' => 'Simpan',
                            'message' => 'Konversi satuan terkecil sudah ada',
                            ];
                        return response()->json($message);
                    }
                }

                Konversi::on($konek)->create($request->all());
                $message = [
                    'success' => true,
                    'title' => 'Simpan',
                    'message' => 'Data telah di Disimpan.'
                ];
                return response()->json($message);
        } else {
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Konversi Satuan Sudah Ada',
            ];
            return response()->json($message);
        }
       
    }

    public function edit_konversi()
    {
        $konek = self::konek();
        $kode_konversi = request()->id;
        $data = Konversi::on($konek)->find($kode_konversi);
        $get_produk = Produk::on($konek)->find($data->kode_produk);
        $output = array(
            'kode_konversi'=> $data->kode_konversi,
            'kode_produk'=> $data->kode_produk,
            'nama_produk'=> $get_produk->nama_produk,
            'kode_satuan'=>$data->kode_satuan,
            'satuan_terbesar'=>$data->satuan_terbesar,
            'nilai_konversi'=> $data->nilai_konversi,
            'kode_satuanterkecil'=>$data->kode_satuanterkecil,
            'satuan_terkecil'=> $data->satuan_terkecil,
        );
        return response()->json($output);
    }


    public function updateAjax(Request $request)
    {
        $konek = self::konek();
        $kode_produk = $request->kode_produk;
        $kode_satuan = $request->kode_satuan;
        $kode_konversi = $request->kode_konversi;
        $nilai_konversi = $request->nilai_konversi;
        $cek_konversi = Konversi::on($konek)->where('kode_produk',$kode_produk)->where('nilai_konversi',1)->first();
        $cek_konversi2 = $cek_konversi->kode_konversi;
        
        $cek_satuan = Konversi::on($konek)->where('kode_produk',$kode_produk)->where('kode_satuan',$kode_satuan)->first();
        if ($cek_satuan != null ){
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Konversi Satuan Sudah Ada',
            ];
            return response()->json($message);
        }
      
        $cek_beli = PembelianDetail::on($konek)->where('kode_produk',$kode_produk)->where('kode_satuan',$kode_satuan)->first();
        $cek_adj = AdjustmentDetail::on($konek)->where('kode_produk',$kode_produk)->where('kode_satuan',$kode_satuan)->first();

        if ($cek_beli == null && $cek_adj == null){
            if ($cek_konversi2 == $kode_konversi){
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'Tidak bisa mengedit satuan terkecil.',
                ];
                return response()->json($message);
            }
            else if ($kode_satuan == $cek_konversi->kode_satuan){
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'Satuan sudah ada.',
                ];
                return response()->json($message);
            }

            $cek_nilai = $request->nilai_konversi;
            if($cek_nilai <= 1){
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'Konversi satuan terkecil sudah ada',
                ];
                return response()->json($message);
            }

            Konversi::on($konek)->find($request->kode_konversi)->update($request->all());
            
            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data telah di Update.'
            ];
            return response()->json($message);
        } 
        else{
            $cek_beli = PembelianDetail::on($konek)->where('kode_produk',$kode_produk)->where('kode_satuan',$kode_satuan)->first();
            $cek_adj = AdjustmentDetail::on($konek)->where('kode_produk',$kode_produk)->where('kode_satuan',$kode_satuan)->first();

            if ($cek_beli != null){
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'Produk ['.$kode_produk.'] sudah ada di pembelian.',
                ];
                return response()->json($message);
            }else if ($cek_adj != null){
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'Produk ['.$kode_produk.'] sudah ada di adjustment.',
                ];
                return response()->json($message);
            }else {
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'Konversi ['.$request->kode_satuan.'] pada produk ['.$request->kode_produk.'] sudah ada.',
                ];
                return response()->json($message);
            }
            
        }       
    }

    public function hapus_konversi()
    {   
        $konek = self::konek();
        $konversi = Konversi::on($konek)->find(request()->id);
        $kode_produk = $konversi->kode_produk;
        $satuan_terbesar = $konversi->kode_satuan;

        $cek_beli = PembelianDetail::on($konek)->where('kode_produk',$kode_produk)->where('kode_satuan',$satuan_terbesar)->first();
        $cek_adj = AdjustmentDetail::on($konek)->where('kode_produk',$kode_produk)->where('kode_satuan',$satuan_terbesar)->first();

        if ($cek_beli == null && $cek_adj == null){
            $konversi->delete();

            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data ['.$konversi->kode_konversi.'] telah dihapus.'
            ];
            return response()->json($message);
        } else {
            $message = [
                'success' => false,
                'title' => 'Update',
                'message' => 'Data ['.$konversi->kode_konversi.'] dipakai dalam transaksi.'
            ];
            return response()->json($message);
        }
        
    }
}
