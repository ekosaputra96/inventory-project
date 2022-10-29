<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\tb_akhir_bulan;
use App\Models\LaporanPemakaian;
use App\Models\tb_item_bulanan;
use App\Models\tb_produk_history;
use App\Models\Produk;
use App\Models\Mobil;
use App\Models\Alat;
use App\Models\Kapal;
use App\Models\Pemakaian;
use App\Models\KategoriProduk;
use App\Models\PemakaianDetail;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Exports\PemakaianExport;
use App\Models\MasterLokasi;
use App\Models\Company;
use App\Models\Signature;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use DB;
use Carbon;

class LaporanpemakaianController extends Controller
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
        $konek = self::konek();
        $create_url = route('laporanpemakaian.create');
        $no_pemakaian = Pemakaian::on($konek)->pluck('no_pemakaian','no_pemakaian');
        $Produk = Produk::on($konek)->pluck('nama_produk', 'kode_produk');
        $Aset = Mobil::on($konek)->pluck('no_asset_mobil','no_asset_mobil');
        $Asetalat = Alat::on($konek)->pluck('no_asset_alat','no_asset_alat');
        $Asetkapal = Kapal::on($konek)->pluck('no_asset_kapal','no_asset_kapal');
        $kategori = KategoriProduk::select('kode_kategori', DB::raw("concat(kode_kategori,' - ',nama_kategori) as kategori"))->pluck('kategori','kode_kategori');
        $lokasi = tb_item_bulanan::on($konek)->pluck('kode_lokasi', 'kode_lokasi');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        $lokasi_user = auth()->user()->kode_lokasi;
        return view('admin.laporanpemakaian.index',compact('create_url','Produk','no_pemakaian','period','kategori', 'nama_lokasi','lokasi','Aset','Asetalat','Asetkapal'));
    }

    public function exportPDF(){
        $konek = self::konek();
        $tanggal_awal = $_GET['tanggal_awal'];
        $tanggal_akhir = $_GET['tanggal_akhir'];
        $tipe = $_GET['jenis_report'];
        $tipe_pemakaian = $_GET['tipe'];
        $status = $_GET['status'];
        $kategori = $_GET['kategori'];
        

        if(isset($_GET['ttd'])){
            $format_ttd = $_GET['ttd']; 
        }else{
            $format_ttd = 0;
        }

        if(isset($_GET['nilai'])){
            $format_nilai = $_GET['nilai']; 
        }else{
            $format_nilai = 0;
        }

        $limit3 = Signature::on($konek)->where('jabatan','MANAGER OPERASIONAL')->first();
        if($limit3 == null){
            $limit3 = Signature::on($konek)->where('jabatan','DIREKTUR')->first();
        }
        
        $limithse = Signature::on($konek)->where('jabatan','MANAGER HSE')->first();

        $dt = Carbon\Carbon::now();
        $date=date_create($dt);
    
        $ttd = auth()->user()->name;
        $level = auth()->user()->level;
        $get_lokasi = auth()->user()->kode_lokasi;
        $get_company = auth()->user()->kode_company;

        if ($tipe != 'PDF') {
            $field = $_GET['item2'];
        
            $produk = 'kode_produk';
            $namaproduk = 'nama_produk';
            $partnumber = 'partnumber';
            $satuan = 'kode_satuan';
            $kategoriproduk = 'kode_kategori';
            $harga = 'harga';
            $subtotal = 'subtotal';
            $semua = 'SEMUA';
    
            $leng = count($field);
    
            $i = 0;
            for ($i = 0; $i < $leng; $i++) { 
                if($produk == $_GET['item2'][$i]){
                    $produk = 'true';
                }
                
                if ($namaproduk == $_GET['item2'][$i]) {
                    $namaproduk = 'true';
                }
                
                if ($partnumber == $_GET['item2'][$i]) {
                    $partnumber = 'true';
                }
                
                if ($satuan == $_GET['item2'][$i]) {
                    $satuan = 'true';
                }
                
                if ($kategori == $_GET['item2'][$i]) {
                    $kategoriproduk = 'true';
                }
                
                if ($harga == $_GET['item2'][$i]) {
                    $harga = 'true';
                }
                
                if ($subtotal == $_GET['item2'][$i]) {
                    $subtotal = 'true';
                }
                
                if ($semua == $_GET['item2'][$i]) {
                    $semua = 'true';
                }
            }
        }
        
        
        if($get_lokasi == 'HO'){
            $lokasi = $_GET['lokasi'];
            if($lokasi != 'SEMUA'){
                $nama_lokasi = MasterLokasi::find($lokasi);
                $nama = $nama_lokasi->nama_lokasi;
            }
            else{
                $nama_lokasi = MasterLokasi::find($get_lokasi);
                $nama = $nama_lokasi->nama_lokasi;
            }
        }else{
            $lokasi = auth()->user()->kode_lokasi;
            $nama_lokasi = MasterLokasi::find($get_lokasi);
            $nama = $nama_lokasi->nama_lokasi;
        }

        $nama_company = Company::find($get_company);
        $nama2 = $nama_company->nama_company;
        
        $pembeliandetail ='';
        $Mobil='';
        
        if($level != 'hse'){
            if($get_lokasi == 'HO'){
                $lokasi = $_GET['lokasi'];
                if($lokasi != 'SEMUA'){
                    $request4 = $_GET['lokasi'];
                    if ($tipe == 'PDF' && $tipe_pemakaian == 'Alat' && $status != 'SEMUA'){
                        $asetalat = $_GET['assetalat'];
                        if ($asetalat == '' || $asetalat == null){
                            $message = [
                                'success' => false,
                                'title' => 'Gagal',
                                'message' => 'Harap pilih nomor aset dahulu.',
                            ];
                            return response()->json($message);
                        }else{
                            if($kategori != 'SEMUA'){
                                if ($asetalat != 'SEMUA'){
                                    $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                    $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat', 'pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.status', $status)
                                    ->where('pemakaian.no_asset_alat', $asetalat)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->where('pemakaian.kode_lokasi', $request4)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                    $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alat', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','asetalat','lokasi','dt','format_ttd','format_nilai','konek'));
                                    $pdf->setPaper('a4', 'landscape');
                        
                                    return $pdf->stream('Laporan Pemakaian No Aset '.$asetalat.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                                }else if ($asetalat == 'SEMUA'){
                                    $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                    $pemakaiandetail = PemakaianDetail::on($konek)
                                        ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                        ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                        ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                        ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                        ->where('pemakaian.type', $tipe_pemakaian)
                                        ->where('pemakaian.status', $status)
                                        ->where('produk.kode_kategori', $kategori)
                                        ->where('pemakaian.kode_lokasi', $request4)
                                        ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                        ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                        ->get();
                        
                                    $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alat', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','lokasi','asetalat','dt','format_ttd','format_nilai','konek'));
                        
                                    $pdf->setPaper('a4', 'landscape');
                        
                                    return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                                }
                            }
                            else if($kategori == 'SEMUA'){
                                if ($asetalat != 'SEMUA'){
                                    $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                    $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat', 'pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.status', $status)
                                    ->where('pemakaian.no_asset_alat', $asetalat)
                                    ->where('pemakaian.kode_lokasi', $request4)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                    $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alat', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','asetalat','lokasi','dt','format_ttd','format_nilai','konek'));
                                    $pdf->setPaper('a4', 'landscape');
                        
                                    return $pdf->stream('Laporan Pemakaian No Aset '.$asetalat.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                                }else if ($asetalat == 'SEMUA'){
                                    $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                    $pemakaiandetail = PemakaianDetail::on($konek)
                                        ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                        ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                        ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                        ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                        ->where('pemakaian.type', $tipe_pemakaian)
                                        ->where('pemakaian.status', $status)
                                        ->where('pemakaian.kode_lokasi', $request4)
                                        ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                        ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                        ->get();
                        
                                    $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alat', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','nama','nama2','lokasi','asetalat','dt','format_ttd','format_nilai','konek'));
                        
                                    $pdf->setPaper('a4', 'landscape');
                        
                                    return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                                }
                            }
                        }
                    } 

                    else if($tipe == 'PDF' && $tipe_pemakaian == 'Mobil' && $status != 'SEMUA'){
                        $aset = $_GET['asset'];
                        if ($aset == '' || $aset == null){
                            $message = [
                                'success' => false,
                                'title' => 'Gagal',
                                'message' => 'Harap pilih nomor aset dahulu.',
                            ];
                            return response()->json($message);
                        }else {
                            if($kategori != 'SEMUA'){
                                if ($aset != 'SEMUA'){
                                    $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                    $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.status', $status)
                                    ->where('pemakaian.no_asset_mobil', $aset)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->where('pemakaian.kode_lokasi', $request4)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                    $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobil', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                                    $pdf->setPaper('a4', 'landscape');
                        
                                    return $pdf->stream('Laporan Pemakaian No Aset '.$aset.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                                }else if ($aset == 'SEMUA'){
                                    $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                    $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.status', $status)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->where('pemakaian.kode_lokasi', $request4)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                    $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobil', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                                    $pdf->setPaper('a4', 'landscape');
                        
                                    return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                                }
                            }else if($kategori == 'SEMUA'){
                                if ($aset != 'SEMUA'){
                                    $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                    $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.status', $status)
                                    ->where('pemakaian.no_asset_mobil', $aset)
                                    ->where('pemakaian.kode_lokasi', $request4)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                    $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobil', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                                    $pdf->setPaper('a4', 'landscape');
                    
                                    return $pdf->stream('Laporan Pemakaian No Aset '.$aset.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                                }else if ($aset == 'SEMUA'){
                                    $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                    $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.status', $status)
                                    ->where('pemakaian.kode_lokasi', $request4)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                    $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobil', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                                    $pdf->setPaper('a4', 'landscape');
                    
                                    return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                                }
                            }
                        }
                    } 

                    else if($tipe == 'PDF' && $tipe_pemakaian == 'Kapal' && $status != 'SEMUA'){
                        $asetkapal = $_GET['assetkapal'];
                        if($kategori != 'SEMUA'){
                            if ($asetkapal != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.no_asset_kapal', $asetkapal)
                                    ->where('pemakaian.status', $status)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->where('pemakaian.kode_lokasi', $request4)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapal', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian No Aset '.$asetkapal.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                            else if ($asetkapal == 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.status', $status)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->where('pemakaian.kode_lokasi', $request4)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapal', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }else if($kategori == 'SEMUA'){
                            if ($asetkapal != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.no_asset_kapal', $asetkapal)
                                    ->where('pemakaian.status', $status)
                                    ->where('pemakaian.kode_lokasi', $request4)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapal', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian No Aset '.$asetkapal.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                            else if ($asetkapal == 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.status', $status)
                                    ->where('pemakaian.kode_lokasi', $request4)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapal', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }
                    }
                    
                    else if($tipe == 'PDF' && $tipe_pemakaian == 'Other' && $status != 'SEMUA'){
                        if($kategori != 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.status', $status)
                                ->where('produk.kode_kategori', $kategori)
                                ->where('pemakaian.kode_lokasi', $request4)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_other', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }else if($kategori == 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.status', $status)
                                ->where('pemakaian.kode_lokasi', $request4)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_other', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }
                        
                    }

                    else if($tipe == 'PDF' && $tipe_pemakaian == 'SEMUA' && $status != 'SEMUA'){
                        if($kategori != 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();
                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','pemakaian.type','produk.kode_kategori','produk.nama_produk')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                ->where('pemakaian.status', $status)
                                ->where('produk.kode_kategori', $kategori)
                                ->where('pemakaian.kode_lokasi', $request4)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();

                            $pembelian = Pembelian::on($konek)->where('no_pembelian','like','%POGA%')
                                ->whereNotNull('no_alat')->where('jenis_po', 'Non-Stock')
                                ->whereBetween('tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))
                                ->where('status', $status)
                                ->first();
                            
                            if ($pembelian != null) {
                                $pembeliandetail = PembelianDetail::on($konek)
                                ->select('pembelian_detail.*','pembelian.tanggal_pembelian','pembelian.status','pembelian.no_alat')
                                ->join('pembelian', 'pembelian_detail.no_pembelian', '=', 'pembelian.no_pembelian')
                                ->where('pembelian.jenis_po', 'Non-Stock')
                                ->where('pembelian.status', $status)
                                ->where('pembelian.kode_lokasi', $request4)
                                ->where('pembelian.no_pembelian','like','%POGA%')
                                ->whereNotNull('pembelian.no_alat')
                                ->whereBetween('pembelian.tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pembelian.tanggal_pembelian','asc')
                                ->get();
                            }
                            
                
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf', compact('pemakaian','pemakaiandetail','pembelian','pembeliandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }else if($kategori == 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','pemakaian.type','produk.kode_kategori','produk.nama_produk')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                ->where('pemakaian.status', $status)
                                ->where('pemakaian.kode_lokasi', $request4)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();

                            $pembelian = Pembelian::on($konek)->where('no_pembelian','like','%POGA%')
                                ->whereNotNull('no_alat')->where('jenis_po', 'Non-Stock')
                                ->whereBetween('tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))
                                ->where('status', $status)
                                ->first();

                            if ($pembelian != null) {
                                $pembeliandetail = PembelianDetail::on($konek)
                                ->select('pembelian_detail.*','pembelian.tanggal_pembelian','pembelian.status','pembelian.no_alat')
                                ->join('pembelian', 'pembelian_detail.no_pembelian', '=', 'pembelian.no_pembelian')
                                ->where('pembelian.jenis_po', 'Non-Stock')
                                ->where('pembelian.status', $status)
                                ->where('pembelian.kode_lokasi', $request4)
                                ->where('pembelian.no_pembelian','like','%POGA%')
                                ->whereNotNull('pembelian.no_alat')
                                ->whereBetween('pembelian.tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pembelian.tanggal_pembelian','asc')
                                ->get();
                            }
                            
                
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf', compact('pemakaian','pemakaiandetail','pembelian','pembeliandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }
                        
                    }
                    
                    else if($tipe == 'PDF' && $tipe_pemakaian == 'Alat' && $status == 'SEMUA'){
                        $asetalat = $_GET['assetalat'];
                        if($kategori != 'SEMUA'){
                            if ($asetalat != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat', 'pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.no_asset_alat', $asetalat)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->where('pemakaian.kode_lokasi', $request4)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alat', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','asetalat','lokasi','dt','format_ttd','format_nilai','konek'));
                                $pdf->setPaper('a4', 'landscape');
                        
                                return $pdf->stream('Laporan Pemakaian No Aset '.$asetalat.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }else if ($asetalat == 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->where('pemakaian.kode_lokasi', $request4)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                        
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alat', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','lokasi','asetalat','dt','format_ttd','format_nilai','konek'));
                        
                                $pdf->setPaper('a4', 'landscape');
                        
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }
                        else if($kategori == 'SEMUA'){
                            if ($asetalat != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat', 'pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.no_asset_alat', $asetalat)
                                    ->where('pemakaian.kode_lokasi', $request4)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alat', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','asetalat','lokasi','dt','format_ttd','format_nilai','konek'));
                                $pdf->setPaper('a4', 'landscape');
                        
                                return $pdf->stream('Laporan Pemakaian No Aset '.$asetalat.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }else if ($asetalat == 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.kode_lokasi', $request4)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                        
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alat', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','nama','nama2','lokasi','asetalat','dt','format_ttd','format_nilai','konek'));
                        
                                $pdf->setPaper('a4', 'landscape');
                        
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }
                    }
                    
                    else if($tipe == 'PDF' && $tipe_pemakaian == 'Mobil' && $status == 'SEMUA'){
                        $aset = $_GET['asset'];
                        if($kategori != 'SEMUA'){
                            if ($aset != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('produk.kode_kategori', $kategori)
                                ->where('pemakaian.no_asset_mobil', $aset)
                                ->where('pemakaian.kode_lokasi', $request4)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobil', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                                $pdf->setPaper('a4', 'landscape');
                
                                return $pdf->stream('Laporan Pemakaian No Aset '.$aset.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }else if ($aset == 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('produk.kode_kategori', $kategori)
                                ->where('pemakaian.kode_lokasi', $request4)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobil', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }else if($kategori == 'SEMUA'){
                            if ($aset != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.no_asset_mobil', $aset)
                                ->where('pemakaian.kode_lokasi', $request4)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobil', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian No Aset '.$aset.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }else if ($aset == 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.kode_lokasi', $request4)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobil', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }
                    }

                    else if($tipe == 'PDF' && $tipe_pemakaian == 'Kapal' && $status == 'SEMUA'){
                        $asetkapal = $_GET['assetkapal'];
                        if($kategori != 'SEMUA'){
                            if ($asetkapal != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.no_asset_kapal', $asetkapal)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->where('pemakaian.kode_lokasi', $request4)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapal', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian No Aset '.$asetkapal.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                            else if ($asetkapal == 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->where('pemakaian.kode_lokasi', $request4)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapal', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }else if($kategori == 'SEMUA'){
                            if ($asetkapal != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.no_asset_kapal', $asetkapal)
                                    ->where('pemakaian.kode_lokasi', $request4)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapal', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian No Aset '.$asetkapal.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                            else if ($asetkapal == 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.kode_lokasi', $request4)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapal', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }
                    }
                    
                    else if($tipe == 'PDF' && $tipe_pemakaian == 'Other' && $status == 'SEMUA'){
                        if($kategori != 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('produk.kode_kategori', $kategori)
                                ->where('pemakaian.kode_lokasi', $request4)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_other', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }else if($kategori == 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.kode_lokasi', $request4)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_other', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }
                        
                    }
                    
                    else if($tipe == 'PDF' && $tipe_pemakaian == 'SEMUA' && $status == 'SEMUA'){
                        if($kategori != 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','pemakaian.type','produk.kode_kategori','produk.nama_produk')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                ->where('produk.kode_kategori', $kategori)
                                ->where('pemakaian.kode_lokasi', $request4)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();

                            $pembelian = Pembelian::on($konek)->where('no_pembelian','like','%POGA%')
                                ->whereNotNull('no_alat')->where('jenis_po', 'Non-Stock')
                                ->whereBetween('tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))
                                ->first();

                            if ($pembelian != null) {
                                $pembeliandetail = PembelianDetail::on($konek)
                                ->select('pembelian_detail.*','pembelian.tanggal_pembelian','pembelian.status','pembelian.no_alat')
                                ->join('pembelian', 'pembelian_detail.no_pembelian', '=', 'pembelian.no_pembelian')
                                ->where('pembelian.jenis_po', 'Non-Stock')
                                ->where('pembelian.kode_lokasi', $request4)
                                ->where('pembelian.no_pembelian','like','%POGA%')
                                ->whereNotNull('pembelian.no_alat')
                                ->whereBetween('pembelian.tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pembelian.tanggal_pembelian','asc')
                                ->get();
                            }
                            
                
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf', compact('pemakaian','pemakaiandetail','pembelian','pembeliandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }else if($kategori == 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();
                            // dd($pemakaian);
                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','pemakaian.type','produk.kode_kategori','produk.nama_produk')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                ->where('pemakaian.kode_lokasi', $request4)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();

                            $pembelian = Pembelian::on($konek)->where('no_pembelian','like','%POGA%')
                                ->whereNotNull('no_alat')->where('jenis_po', 'Non-Stock')
                                ->whereBetween('tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))
                                ->first();

                            if ($pembelian != null) {
                                $pembeliandetail = PembelianDetail::on($konek)
                                ->select('pembelian_detail.*','pembelian.tanggal_pembelian','pembelian.status','pembelian.no_alat')
                                ->join('pembelian', 'pembelian_detail.no_pembelian', '=', 'pembelian.no_pembelian')
                                ->where('pembelian.jenis_po', 'Non-Stock')
                                ->where('pembelian.kode_lokasi', $request4)
                                ->where('pembelian.no_pembelian','like','%POGA%')
                                ->whereNotNull('pembelian.no_alat')
                                ->whereBetween('pembelian.tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pembelian.tanggal_pembelian','asc')
                                ->get();
                            }
                            
                
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf', compact('pemakaian','pemakaiandetail','pembelian','pembeliandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }
                        
                    }
                
                    else if ($tipe == 'excel' && $tipe_pemakaian == 'Kapal'){
                        $request4 = $_GET['lokasi'];
                        $aset = 'none';
                        $asetalat = 'none';
                        $asetkapal = $_GET['assetkapal'];
                        return Excel::download(new PemakaianExport($tanggal_awal, $tanggal_akhir, $status, $tipe_pemakaian, $kategori, $request4, $aset, $asetalat, $asetkapal, $nama2, $nama, $dt, $produk, $namaproduk, $partnumber, $satuan, $kategoriproduk, $harga, $subtotal, $semua), 'Laporan Pemakaian no aset kapal '.$asetkapal.' dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                    }
                
                    else if ($tipe == 'excel' && $tipe_pemakaian == 'Alat'){
                        $request4 = $_GET['lokasi'];
                        $aset = 'none';
                        $asetalat = $_GET['assetalat'];
                        $asetkapal = 'none';
                        return Excel::download(new PemakaianExport($tanggal_awal, $tanggal_akhir, $status, $tipe_pemakaian, $kategori, $request4, $aset, $asetalat, $asetkapal, $nama2, $nama, $dt, $produk, $namaproduk, $partnumber, $satuan, $kategoriproduk, $harga, $subtotal, $semua), 'Laporan Pemakaian no aset alat '.$asetalat.' dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                    }

                    else if ($tipe == 'excel' && $tipe_pemakaian == 'Mobil'){
                        $request4 = $_GET['lokasi'];
                        $aset = $_GET['asset'];
                        $asetalat = 'none';
                        $asetkapal = 'none';
                        return Excel::download(new PemakaianExport($tanggal_awal, $tanggal_akhir, $status, $tipe_pemakaian, $kategori, $request4, $aset, $asetalat, $asetkapal, $nama2, $nama, $dt, $produk, $namaproduk, $partnumber, $satuan, $kategoriproduk, $harga, $subtotal, $semua), 'Laporan Pemakaian no aset mobil '.$aset.' dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                    }

                    else if ($tipe == 'excel'){
                        $request4 = $_GET['lokasi'];
                        $aset = 'none';
                        $asetalat = 'none';
                        $asetkapal = 'none';
                        return Excel::download(new PemakaianExport($tanggal_awal, $tanggal_akhir, $status, $tipe_pemakaian, $kategori, $request4, $aset, $asetalat, $asetkapal, $nama2, $nama, $dt, $produk, $namaproduk, $partnumber, $satuan, $kategoriproduk, $harga, $subtotal, $semua), 'Laporan Pemakaian dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                    }
                }else{
                    if ($tipe == 'PDF' && $tipe_pemakaian == 'Alat' && $status != 'SEMUA'){
                        $asetalat = $_GET['assetalat'];
                        if ($asetalat == '' || $asetalat == null){
                            $message = [
                                'success' => false,
                                'title' => 'Gagal',
                                'message' => 'Harap pilih nomor aset dahulu.',
                            ];
                            return response()->json($message);
                        }else{
                            if($kategori != 'SEMUA'){
                                if ($asetalat != 'SEMUA'){
                                    $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                    $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat', 'pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.status', $status)
                                    ->where('pemakaian.no_asset_alat', $asetalat)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                    $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alat', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','asetalat','lokasi','dt','format_ttd','format_nilai','konek'));
                                    $pdf->setPaper('a4', 'landscape');
                        
                                    return $pdf->stream('Laporan Pemakaian No Aset '.$asetalat.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                                }else if ($asetalat == 'SEMUA'){
                                    $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                    $pemakaiandetail = PemakaianDetail::on($konek)
                                        ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                        ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                        ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                        ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                        ->where('pemakaian.type', $tipe_pemakaian)
                                        ->where('pemakaian.status', $status)
                                        ->where('produk.kode_kategori', $kategori)
                                        ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                        ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                        ->get();
                        
                                    $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alat', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','lokasi','asetalat','dt','format_ttd','format_nilai','konek'));
                        
                                    $pdf->setPaper('a4', 'landscape');
                        
                                    return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                                }
                            }
                            else if($kategori == 'SEMUA'){
                                if ($asetalat != 'SEMUA'){
                                    $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                    $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat', 'pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.status', $status)
                                    ->where('pemakaian.no_asset_alat', $asetalat)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                    $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alat', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','asetalat','lokasi','dt','format_ttd','format_nilai','konek'));
                                    $pdf->setPaper('a4', 'landscape');
                        
                                    return $pdf->stream('Laporan Pemakaian No Aset '.$asetalat.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                                }else if ($asetalat == 'SEMUA'){
                                    $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                    $pemakaiandetail = PemakaianDetail::on($konek)
                                        ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                        ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                        ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                        ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                        ->where('pemakaian.type', $tipe_pemakaian)
                                        ->where('pemakaian.status', $status)
                                        ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                        ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                        ->get();
                        
                                    $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alat', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','nama','nama2','lokasi','asetalat','dt','format_ttd','format_nilai','konek'));
                        
                                    $pdf->setPaper('a4', 'landscape');
                        
                                    return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                                }
                            }
                        }
                    }

                    else if($tipe == 'PDF' && $tipe_pemakaian == 'Mobil' && $status != 'SEMUA'){
                        $aset = $_GET['asset'];
                        if ($aset == '' || $aset == null){
                            $message = [
                                'success' => false,
                                'title' => 'Gagal',
                                'message' => 'Harap pilih nomor aset dahulu.',
                            ];
                            return response()->json($message);
                        }else {
                            if($kategori != 'SEMUA'){
                                if ($aset != 'SEMUA'){
                                    $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                    $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.status', $status)
                                    ->where('pemakaian.no_asset_mobil', $aset)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                    $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobil', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                                    $pdf->setPaper('a4', 'landscape');
                        
                                    return $pdf->stream('Laporan Pemakaian No Aset '.$aset.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                                }else if ($aset == 'SEMUA'){
                                    $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                    $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.status', $status)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                    $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobil', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                                    $pdf->setPaper('a4', 'landscape');
                        
                                    return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                                }
                            }else if($kategori == 'SEMUA'){
                                if ($aset != 'SEMUA'){
                                    $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                    $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.status', $status)
                                    ->where('pemakaian.no_asset_mobil', $aset)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                    $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobil', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                                    $pdf->setPaper('a4', 'landscape');
                    
                                    return $pdf->stream('Laporan Pemakaian No Aset '.$aset.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                                }else if ($aset == 'SEMUA'){
                                    $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                    $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.status', $status)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                    $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobil', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                                    $pdf->setPaper('a4', 'landscape');
                    
                                    return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                                }
                            }
                        }
                    } 

                    else if($tipe == 'PDF' && $tipe_pemakaian == 'Kapal' && $status != 'SEMUA'){
                        $asetkapal = $_GET['assetkapal'];
                        if($kategori != 'SEMUA'){
                            if ($asetkapal != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.status', $status)
                                    ->where('pemakaian.no_asset_kapal', $asetkapal)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapal', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian No Aset '.$asetkapal.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                            else if ($asetkapal == 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.status', $status)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapal', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }
                        else if($kategori == 'SEMUA'){
                            if ($asetalat != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.no_asset_kapal', $asetkapal)
                                    ->where('pemakaian.status', $status)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapal', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian No Aset '.$asetkapal.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                            else if ($asetalat == 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.status', $status)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapal', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }
                    }
                    
                    else if($tipe == 'PDF' && $tipe_pemakaian == 'Other' && $status != 'SEMUA'){
                        if($kategori != 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.status', $status)
                                ->where('produk.kode_kategori', $kategori)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_other', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }else if($kategori == 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.status', $status)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_other', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }
                        
                    }

                    else if($tipe == 'PDF' && $tipe_pemakaian == 'SEMUA' && $status != 'SEMUA'){
                        if($kategori != 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','pemakaian.type','produk.kode_kategori','produk.nama_produk','pemakaian.no_asset_alat','pemakaian.no_asset_mobil','pemakaian.no_asset_kapal')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                ->where('pemakaian.status', $status)
                                ->where('produk.kode_kategori', $kategori)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                                
                            $pembelian = Pembelian::on($konek)->where('no_pembelian','like','%POGA%')
                                ->whereNotNull('no_alat')->where('jenis_po', 'Non-Stock')->where('status',$status)
                                ->whereBetween('tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))
                                ->first();
                            
                            if ($pembelian != null)
                            {
                                $pembeliandetail = PembelianDetail::on($konek)
                                ->select('pembelian_detail.*','pembelian.tanggal_pembelian','pembelian.status','pembelian.no_alat')
                                ->join('pembelian', 'pembelian_detail.no_pembelian', '=', 'pembelian.no_pembelian')
                                ->where('pembelian.jenis_po', 'Non-Stock')
                                ->where('pembelian.status', $status)
                                ->where('pembelian.kode_lokasi', $request4)
                                ->where('pembelian.no_pembelian','like','%POGA%')
                                ->whereNotNull('pembelian.no_alat')
                                ->whereBetween('pembelian.tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pembelian.tanggal_pembelian','asc')
                                ->get();
                            }
                            
                
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf', compact('pemakaian','pemakaiandetail','pembelian','pembeliandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }else if($kategori == 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','pemakaian.type','produk.kode_kategori','produk.nama_produk','pemakaian.no_asset_alat','pemakaian.no_asset_mobil','pemakaian.no_asset_kapal')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                ->where('pemakaian.status', $status)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                                
                            $pembelian = Pembelian::on($konek)->where('no_pembelian','like','%POGA%')
                                ->whereNotNull('no_alat')->where('jenis_po', 'Non-Stock')->where('status', $status)
                                ->whereBetween('tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))
                                ->first();

                            if ($pembelian != null)
                            {
                                $pembeliandetail = PembelianDetail::on($konek)
                                    ->select('pembelian_detail.*','pembelian.tanggal_pembelian','pembelian.status','pembelian.no_alat')
                                    ->join('pembelian', 'pembelian_detail.no_pembelian', '=', 'pembelian.no_pembelian')
                                    ->where('pembelian.jenis_po', 'Non-Stock')
                                    ->where('pembelian.status', $status)
                                    ->where('pembelian.kode_lokasi', $request4)
                                    ->where('pembelian.no_pembelian','like','%POGA%')
                                    ->whereNotNull('pembelian.no_alat')
                                    ->whereBetween('pembelian.tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pembelian.tanggal_pembelian','asc')
                                    ->get();
                            }
                
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf', compact('pemakaian','pemakaiandetail','pembelian','pembeliandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }
                        
                    }
                    
                    else if($tipe == 'PDF' && $tipe_pemakaian == 'Alat' && $status == 'SEMUA'){
                        $asetalat = $_GET['assetalat'];
                        if($kategori != 'SEMUA'){
                            if ($asetalat != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat', 'pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.no_asset_alat', $asetalat)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alat', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','asetalat','lokasi','dt','format_ttd','format_nilai','konek'));
                                $pdf->setPaper('a4', 'landscape');
                        
                                return $pdf->stream('Laporan Pemakaian No Aset '.$asetalat.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }else if ($asetalat == 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                        
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alat', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','lokasi','asetalat','dt','format_ttd','format_nilai','konek'));
                        
                                $pdf->setPaper('a4', 'landscape');
                        
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }
                        else if($kategori == 'SEMUA'){
                            if ($asetalat != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat', 'pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.no_asset_alat', $asetalat)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alat', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','asetalat','lokasi','dt','format_ttd','format_nilai','konek'));
                                $pdf->setPaper('a4', 'landscape');
                        
                                return $pdf->stream('Laporan Pemakaian No Aset '.$asetalat.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }else if ($asetalat == 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                        
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alat', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','nama','nama2','lokasi','asetalat','dt','format_ttd','format_nilai','konek'));
                        
                                $pdf->setPaper('a4', 'landscape');
                        
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }
                    }
                    
                    else if($tipe == 'PDF' && $tipe_pemakaian == 'Mobil' && $status == 'SEMUA'){
                        $aset = $_GET['asset'];
                        if($kategori != 'SEMUA'){
                            if ($aset != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('produk.kode_kategori', $kategori)
                                ->where('pemakaian.no_asset_mobil', $aset)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobil', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                                $pdf->setPaper('a4', 'landscape');
                
                                return $pdf->stream('Laporan Pemakaian No Aset '.$aset.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }else if ($aset == 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('produk.kode_kategori', $kategori)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobil', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }else if($kategori == 'SEMUA'){
                            if ($aset != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.no_asset_mobil', $aset)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobil', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian No Aset '.$aset.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }else if ($aset == 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobil', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }
                    }

                    else if($tipe == 'PDF' && $tipe_pemakaian == 'Kapal' && $status == 'SEMUA'){
                        $asetkapal = $_GET['assetkapal'];
                        if($kategori != 'SEMUA'){
                            if ($asetkapal != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.no_asset_kapal', $asetkapal)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapal', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian No Aset '.$asetkapal.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }else if ($asetkapal == 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapal', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }else if($kategori == 'SEMUA'){
                            if ($asetkapal != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                    ->where('pemakaian.no_asset_kapal', $asetkapal)
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapal', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian No Aset '.$asetkapal.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }else if ($asetkapal == 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapal', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }
                    }
                    
                    else if($tipe == 'PDF' && $tipe_pemakaian == 'Other' && $status == 'SEMUA'){
                        if($kategori != 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('produk.kode_kategori', $kategori)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_other', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }else if($kategori == 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_other', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }
                        
                    }
                    
                    else if($tipe == 'PDF' && $tipe_pemakaian == 'SEMUA' && $status == 'SEMUA'){
                        if($kategori != 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','pemakaian.type','produk.kode_kategori','produk.nama_produk','pemakaian.no_asset_alat','pemakaian.no_asset_mobil','pemakaian.no_asset_kapal')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                ->where('produk.kode_kategori', $kategori)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                            
                            $pembelian = Pembelian::on($konek)->where('no_pembelian','like','%POGA%')
                                ->whereNotNull('no_alat')->where('jenis_po', 'Non-Stock')
                                ->whereBetween('tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))
                                ->first();
                                
                            if ($pembelian != null) {
                                $pembeliandetail = PembelianDetail::on($konek)
                                ->select('pembelian_detail.*','pembelian.tanggal_pembelian','pembelian.status','pembelian.no_alat')
                                ->join('pembelian', 'pembelian_detail.no_pembelian', '=', 'pembelian.no_pembelian')
                                ->where('pembelian.jenis_po', 'Non-Stock')
                                ->where('pembelian.no_pembelian','like','%POGA%')
                                ->whereNotNull('pembelian.no_alat')
                                ->whereBetween('pembelian.tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pembelian.tanggal_pembelian','asc')
                                ->get();
                            }
                            
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf', compact('pemakaian','pemakaiandetail','pembelian','pembeliandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }else if($kategori == 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();
                            // dd($pemakaian);
                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','pemakaian.type','produk.kode_kategori','produk.nama_produk','pemakaian.no_asset_alat','pemakaian.no_asset_mobil','pemakaian.no_asset_kapal')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                            
                            $pembelian = Pembelian::on($konek)->where('no_pembelian','like','%POGA%')
                                ->whereNotNull('no_alat')->where('jenis_po', 'Non-Stock')
                                ->whereBetween('tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))
                                ->first();

                            if ($pembelian != null) {
                                $pembeliandetail = PembelianDetail::on($konek)
                                ->select('pembelian_detail.*','pembelian.tanggal_pembelian','pembelian.status','pembelian.no_alat')
                                ->join('pembelian', 'pembelian_detail.no_pembelian', '=', 'pembelian.no_pembelian')
                                ->where('pembelian.jenis_po', 'Non-Stock')
                                ->where('pembelian.no_pembelian','like','%POGA%')
                                ->whereNotNull('pembelian.no_alat')
                                ->whereBetween('pembelian.tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pembelian.tanggal_pembelian','asc')
                                ->get();
                            }
                            
                
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf', compact('pemakaian','pemakaiandetail','pembelian','pembeliandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }
                        
                    }
                
                    else if ($tipe == 'excel' && $tipe_pemakaian == 'Kapal'){
                        $request4 = $_GET['lokasi'];
                        $aset = 'none';
                        $asetalat = 'none';
                        $asetkapal = $_GET['assetkapal'];
                        return Excel::download(new PemakaianExport($tanggal_awal, $tanggal_akhir, $status, $tipe_pemakaian, $kategori, $request4, $aset, $asetalat, $asetkapal, $nama2, $nama, $dt, $produk, $namaproduk, $partnumber, $satuan, $kategoriproduk, $harga, $subtotal, $semua), 'Laporan Pemakaian no aset kapal '.$asetkapal.' dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                    }
                
                    else if ($tipe == 'excel' && $tipe_pemakaian == 'Alat'){
                        $request4 = $_GET['lokasi'];
                        $aset = 'none';
                        $asetalat = $_GET['assetalat'];
                        $asetkapal = 'none';
                        return Excel::download(new PemakaianExport($tanggal_awal, $tanggal_akhir, $status, $tipe_pemakaian, $kategori, $request4, $aset, $asetalat, $asetkapal, $nama2, $nama, $dt, $produk, $namaproduk, $partnumber, $satuan, $kategoriproduk, $harga, $subtotal, $semua), 'Laporan Pemakaian no aset alat '.$asetalat.' dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                    }
                
                    else if ($tipe == 'excel' && $tipe_pemakaian == 'Mobil'){
                        $request4 = $_GET['lokasi'];
                        $aset = $_GET['asset'];
                        $asetalat = 'none';
                        $asetkapal = 'none';
                        return Excel::download(new PemakaianExport($tanggal_awal, $tanggal_akhir, $status, $tipe_pemakaian, $kategori, $request4, $aset, $asetalat, $asetkapal, $nama2, $nama, $dt, $produk, $namaproduk, $partnumber, $satuan, $kategoriproduk, $harga, $subtotal, $semua), 'Laporan Pemakaian no aset mobil '.$aset.' dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                    }

                    else if ($tipe == 'excel'){
                        $request4 = $_GET['lokasi'];
                        $aset = 'none';
                        $asetalat = 'none';
                        $asetkapal = 'none';
                        return Excel::download(new PemakaianExport($tanggal_awal, $tanggal_akhir, $status, $tipe_pemakaian, $kategori, $request4, $aset, $asetalat, $asetkapal, $nama2, $nama, $dt, $produk, $namaproduk, $partnumber, $satuan, $kategoriproduk, $harga, $subtotal, $semua), 'Laporan Pemakaian dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                    }
                }
            }
            else{
                if ($tipe == 'PDF' && $tipe_pemakaian == 'Alat' && $status != 'SEMUA'){
                        $asetalat = $_GET['assetalat'];
                        if ($asetalat == '' || $asetalat == null){
                            $message = [
                                'success' => false,
                                'title' => 'Gagal',
                                'message' => 'Harap pilih nomor aset dahulu.',
                            ];
                            return response()->json($message);
                        }else{
                            if($kategori != 'SEMUA'){
                                if ($asetalat != 'SEMUA'){
                                    $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                    $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat', 'pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.status', $status)
                                    ->where('pemakaian.no_asset_alat', $asetalat)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->where('pemakaian.kode_lokasi', $get_lokasi)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                    $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alat', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','asetalat','lokasi','dt','format_ttd','format_nilai','konek'));
                                    $pdf->setPaper('a4', 'landscape');
                        
                                    return $pdf->stream('Laporan Pemakaian No Aset '.$asetalat.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                                }else if ($asetalat == 'SEMUA'){
                                    $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                    $pemakaiandetail = PemakaianDetail::on($konek)
                                        ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                        ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                        ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                        ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                        ->where('pemakaian.type', $tipe_pemakaian)
                                        ->where('pemakaian.status', $status)
                                        ->where('produk.kode_kategori', $kategori)
                                        ->where('pemakaian.kode_lokasi', $get_lokasi)
                                        ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                        ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                        ->get();
                        
                                    $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alat', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','lokasi','asetalat','dt','format_ttd','format_nilai','konek'));
                        
                                    $pdf->setPaper('a4', 'landscape');
                        
                                    return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                                }
                            }
                            else if($kategori == 'SEMUA'){
                                if ($asetalat != 'SEMUA'){
                                    $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                    $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat', 'pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.status', $status)
                                    ->where('pemakaian.no_asset_alat', $asetalat)
                                    ->where('pemakaian.kode_lokasi', $get_lokasi)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                    $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alat', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','asetalat','lokasi','dt','format_ttd','format_nilai','konek'));
                                    $pdf->setPaper('a4', 'landscape');
                        
                                    return $pdf->stream('Laporan Pemakaian No Aset '.$asetalat.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                                }else if ($asetalat == 'SEMUA'){
                                    $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                    $pemakaiandetail = PemakaianDetail::on($konek)
                                        ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                        ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                        ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                        ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                        ->where('pemakaian.type', $tipe_pemakaian)
                                        ->where('pemakaian.status', $status)
                                        ->where('pemakaian.kode_lokasi', $get_lokasi)
                                        ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                        ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                        ->get();
                        
                                    $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alat', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','nama','nama2','lokasi','asetalat','dt','format_ttd','format_nilai','konek'));
                        
                                    $pdf->setPaper('a4', 'landscape');
                        
                                    return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                                }
                            }
                        }
                }  

                else if($tipe == 'PDF' && $tipe_pemakaian == 'Mobil' && $status != 'SEMUA'){
                    $aset = $_GET['asset'];
                    if($kategori != 'SEMUA'){
                        if ($aset != 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                            ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                            ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                            ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                            ->where('pemakaian.type', $tipe_pemakaian)
                            ->where('pemakaian.status', $status)
                            ->where('pemakaian.no_asset_mobil', $aset)
                            ->where('produk.kode_kategori', $kategori)
                            ->where('pemakaian.kode_lokasi', $get_lokasi)
                            ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaian.tanggal_pemakaian','asc')
                            ->get();
            
                            $lokasi = $get_lokasi;
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobil', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian No Aset '.$aset.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }else if ($aset == 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                            ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                            ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                            ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                            ->where('pemakaian.type', $tipe_pemakaian)
                            ->where('pemakaian.status', $status)
                            ->where('produk.kode_kategori', $kategori)
                            ->where('pemakaian.kode_lokasi', $get_lokasi)
                            ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaian.tanggal_pemakaian','asc')
                            ->get();
            
                            $lokasi = $get_lokasi;
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobil', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }
                        
                    }else if($kategori == 'SEMUA'){
                        if ($aset != 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                            ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                            ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                            ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                            ->where('pemakaian.type', $tipe_pemakaian)
                            ->where('pemakaian.status', $status)
                            ->where('pemakaian.no_asset_mobil', $aset)
                            ->where('pemakaian.kode_lokasi', $get_lokasi)
                            ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaian.tanggal_pemakaian','asc')
                            ->get();
            
                            $lokasi = $get_lokasi;
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobil', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian No Aset '.$aset.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }else if ($aset == 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                            ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                            ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                            ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                            ->where('pemakaian.type', $tipe_pemakaian)
                            ->where('pemakaian.status', $status)
                            ->where('pemakaian.kode_lokasi', $get_lokasi)
                            ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaian.tanggal_pemakaian','asc')
                            ->get();
            
                            $lokasi = $get_lokasi;
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobil', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }
                        
                    }
                    
                } 

                else if($tipe == 'PDF' && $tipe_pemakaian == 'Kapal' && $status != 'SEMUA'){
                    $asetkapal = $_GET['assetkapal'];
                    if($kategori != 'SEMUA'){
                        if ($asetkapal != 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.no_asset_kapal', $asetkapal)
                                ->where('pemakaian.status', $status)
                                ->where('produk.kode_kategori', $kategori)
                                ->where('pemakaian.kode_lokasi', $get_lokasi)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $lokasi = $get_lokasi;
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapal', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian No Aset '.$asetkapal.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }else if ($asetkapal == 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.status', $status)
                                ->where('produk.kode_kategori', $kategori)
                                ->where('pemakaian.kode_lokasi', $get_lokasi)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $lokasi = $get_lokasi;
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapal', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }
                    }else if($kategori == 'SEMUA'){
                        if ($asetkapal != 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.no_asset_kapal', $asetkapal)
                                ->where('pemakaian.status', $status)
                                ->where('pemakaian.kode_lokasi', $get_lokasi)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $lokasi = $get_lokasi;
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapal', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian No Aset '.$asetkapal.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }else if ($asetkapal == 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.status', $status)
                                ->where('pemakaian.kode_lokasi', $get_lokasi)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $lokasi = $get_lokasi;
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapal', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }
                    }
                }
                
                else if($tipe == 'PDF' && $tipe_pemakaian == 'Other' && $status != 'SEMUA'){
                    if($kategori != 'SEMUA'){
                        $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaiandetail = PemakaianDetail::on($konek)
                            ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                            ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                            ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                            ->where('pemakaian.type', $tipe_pemakaian)
                            ->where('pemakaian.status', $status)
                            ->where('produk.kode_kategori', $kategori)
                            ->where('pemakaian.kode_lokasi', $get_lokasi)
                            ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaian.tanggal_pemakaian','asc')
                            ->get();
            
                        $lokasi = $get_lokasi;
                        $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_other', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
            
                        $pdf->setPaper('a4', 'landscape');
            
                        return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }else if($kategori == 'SEMUA'){
                        $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaiandetail = PemakaianDetail::on($konek)
                            ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                            ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                            ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                            ->where('pemakaian.type', $tipe_pemakaian)
                            ->where('pemakaian.status', $status)
                            ->where('pemakaian.kode_lokasi', $get_lokasi)
                            ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaian.tanggal_pemakaian','asc')
                            ->get();
            
                        $lokasi = $get_lokasi;
                        $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_other', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
            
                        $pdf->setPaper('a4', 'landscape');
            
                        return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                    
                }

                else if($tipe == 'PDF' && $tipe_pemakaian == 'SEMUA' && $status != 'SEMUA'){
                    if($kategori != 'SEMUA'){
                        $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaiandetail = PemakaianDetail::on($konek)
                            ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','pemakaian.type','produk.kode_kategori','produk.nama_produk')
                            ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                            ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                            ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                            ->where('pemakaian.status', $status)
                            ->where('produk.kode_kategori', $kategori)
                            ->where('pemakaian.kode_lokasi', $get_lokasi)
                            ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaian.tanggal_pemakaian','asc')
                            ->get();
            
                        $lokasi = $get_lokasi;

                        $pembelian = Pembelian::on($konek)->where('no_pembelian','like','%POGA%')
                                ->whereNotNull('no_alat')->where('jenis_po', 'Non-Stock')
                                ->whereBetween('tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))
                                ->where('status', $status)
                                ->first();
                            
                        if ($pembelian != null) {
                            $pembeliandetail = PembelianDetail::on($konek)
                                ->select('pembelian_detail.*','pembelian.tanggal_pembelian','pembelian.status','pembelian.no_alat')
                                ->join('pembelian', 'pembelian_detail.no_pembelian', '=', 'pembelian.no_pembelian')
                                ->where('pembelian.jenis_po', 'Non-Stock')
                                ->where('pembelian.status', $status)
                                ->where('pembelian.kode_lokasi', $request4)
                                ->where('pembelian.no_pembelian','like','%POGA%')
                                ->whereNotNull('pembelian.no_alat')
                                ->whereBetween('pembelian.tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pembelian.tanggal_pembelian','asc')
                                ->get();
                        }

                        $pdf = PDF::loadView('/admin/laporanpemakaian/pdf', compact('pemakaian','pemakaiandetail','pembelian','pembeliandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
            
                        $pdf->setPaper('a4', 'landscape');
            
                        return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }else if($kategori == 'SEMUA'){
                        $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaiandetail = PemakaianDetail::on($konek)
                            ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','pemakaian.type','produk.kode_kategori','produk.nama_produk')
                            ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                            ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                            ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                            ->where('pemakaian.status', $status)
                            ->where('pemakaian.kode_lokasi', $get_lokasi)
                            ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaian.tanggal_pemakaian','asc')
                            ->get();

                        $pembelian = Pembelian::on($konek)->where('no_pembelian','like','%POGA%')
                                ->whereNotNull('no_alat')->where('jenis_po', 'Non-Stock')
                                ->whereBetween('tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))
                                ->first();
                            
                        if ($pembelian != null) {
                            $pembeliandetail = PembelianDetail::on($konek)
                                ->select('pembelian_detail.*','pembelian.tanggal_pembelian','pembelian.status','pembelian.no_alat')
                                ->join('pembelian', 'pembelian_detail.no_pembelian', '=', 'pembelian.no_pembelian')
                                ->where('pembelian.jenis_po', 'Non-Stock')
                                ->where('pembelian.kode_lokasi', $request4)
                                ->where('pembelian.no_pembelian','like','%POGA%')
                                ->whereNotNull('pembelian.no_alat')
                                ->whereBetween('pembelian.tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pembelian.tanggal_pembelian','asc')
                                ->get();
                        }
            
                        $lokasi = $get_lokasi;
                        $pdf = PDF::loadView('/admin/laporanpemakaian/pdf', compact('pemakaian','pemakaiandetail','pembelian','pembeliandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
            
                        $pdf->setPaper('a4', 'landscape');
            
                        return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                    
                }
                
                else if($tipe == 'PDF' && $tipe_pemakaian == 'Alat' && $status == 'SEMUA'){
                        $asetalat = $_GET['assetalat'];
                        if($kategori != 'SEMUA'){
                            if ($asetalat != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat', 'pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.no_asset_alat', $asetalat)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->where('pemakaian.kode_lokasi', $get_lokasi)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alat', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','asetalat','lokasi','dt','format_ttd','format_nilai','konek'));
                                $pdf->setPaper('a4', 'landscape');
                        
                                return $pdf->stream('Laporan Pemakaian No Aset '.$asetalat.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }else if ($asetalat == 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->where('pemakaian.kode_lokasi', $get_lokasi)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                        
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alat', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','lokasi','asetalat','dt','format_ttd','format_nilai','konek'));
                        
                                $pdf->setPaper('a4', 'landscape');
                        
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }
                        else if($kategori == 'SEMUA'){
                            if ($asetalat != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat', 'pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.no_asset_alat', $asetalat)
                                    ->where('pemakaian.kode_lokasi', $get_lokasi)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alat', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','asetalat','lokasi','dt','format_ttd','format_nilai','konek'));
                                $pdf->setPaper('a4', 'landscape');
                        
                                return $pdf->stream('Laporan Pemakaian No Aset '.$asetalat.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }else if ($asetalat == 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.kode_lokasi', $get_lokasi)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                        
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alat', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','nama','nama2','lokasi','asetalat','dt','format_ttd','format_nilai','konek'));
                        
                                $pdf->setPaper('a4', 'landscape');
                        
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }
                }
                
                else if($tipe == 'PDF' && $tipe_pemakaian == 'Mobil' && $status == 'SEMUA'){
                    $aset = $_GET['asset'];
                    if($kategori != 'SEMUA'){
                        if ($aset != 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                            ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                            ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                            ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                            ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                            ->where('pemakaian.type', $tipe_pemakaian)
                            ->where('produk.kode_kategori', $kategori)
                            ->where('pemakaian.no_asset_mobil', $aset)
                            ->where('pemakaian.kode_lokasi', $get_lokasi)
                            ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaian.tanggal_pemakaian','asc')
                            ->get();
            
                            $lokasi = $get_lokasi;
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobil', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian '.$aset.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }else if ($aset == 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                            ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                            ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                            ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                            ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                            ->where('pemakaian.type', $tipe_pemakaian)
                            ->where('produk.kode_kategori', $kategori)
                            ->where('pemakaian.kode_lokasi', $get_lokasi)
                            ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaian.tanggal_pemakaian','asc')
                            ->get();
            
                            $lokasi = $get_lokasi;
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobil', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }
                    }else if($kategori == 'SEMUA'){
                        if ($aset != 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                            ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                            ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                            ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                            ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                            ->where('pemakaian.type', $tipe_pemakaian)
                            ->where('pemakaian.no_asset_mobil', $aset)
                            ->where('pemakaian.kode_lokasi', $get_lokasi)
                            ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaian.tanggal_pemakaian','asc')
                            ->get();
            
                            $lokasi = $get_lokasi;
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobil', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian '.$aset.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }else if ($aset == 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                            ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                            ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                            ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                            ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                            ->where('pemakaian.type', $tipe_pemakaian)
                            ->where('pemakaian.kode_lokasi', $get_lokasi)
                            ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaian.tanggal_pemakaian','asc')
                            ->get();
            
                            $lokasi = $get_lokasi;
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobil', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }
                    }
                }

                else if($tipe == 'PDF' && $tipe_pemakaian == 'Kapal' && $status == 'SEMUA'){
                    $asetkapal = $_GET['assetkapal'];
                    if($kategori != 'SEMUA'){
                        if ($asetkapal != 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.no_asset_kapal', $asetkapal)
                                ->where('produk.kode_kategori', $kategori)
                                ->where('pemakaian.kode_lokasi', $get_lokasi)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $lokasi = $get_lokasi;
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapal', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian '.$asetkapal.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }else if ($asetkapal == 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('produk.kode_kategori', $kategori)
                                ->where('pemakaian.kode_lokasi', $get_lokasi)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $lokasi = $get_lokasi;
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapal', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }
                    }else if($kategori == 'SEMUA'){
                        if ($asetkapal != 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.no_asset_kapal', $asetkapal)
                                ->where('pemakaian.kode_lokasi', $get_lokasi)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $lokasi = $get_lokasi;
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapal', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian '.$asetkapal.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }else if ($asetkapal == 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.kode_lokasi', $get_lokasi)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $lokasi = $get_lokasi;
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapal', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }
                    }
                }
                
                else if($tipe == 'PDF' && $tipe_pemakaian == 'Other' && $status == 'SEMUA'){
                    if($kategori != 'SEMUA'){
                        $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaiandetail = PemakaianDetail::on($konek)
                            ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                            ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                            ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                            ->where('pemakaian.type', $tipe_pemakaian)
                            ->where('produk.kode_kategori', $kategori)
                            ->where('pemakaian.kode_lokasi', $get_lokasi)
                            ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaian.tanggal_pemakaian','asc')
                            ->get();
            
                        $lokasi = $get_lokasi;
                        $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_other', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
            
                        $pdf->setPaper('a4', 'landscape');
            
                        return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }else if($kategori == 'SEMUA'){
                        $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaiandetail = PemakaianDetail::on($konek)
                            ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                            ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                            ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                            ->where('pemakaian.type', $tipe_pemakaian)
                            ->where('pemakaian.kode_lokasi', $get_lokasi)
                            ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaian.tanggal_pemakaian','asc')
                            ->get();
            
                        $lokasi = $get_lokasi;
                        $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_other', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
            
                        $pdf->setPaper('a4', 'landscape');
            
                        return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                    
                }
                
                else if($tipe == 'PDF' && $tipe_pemakaian == 'SEMUA' && $status == 'SEMUA'){
                    if($kategori != 'SEMUA'){
                        $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaiandetail = PemakaianDetail::on($konek)
                            ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','pemakaian.type','produk.kode_kategori','produk.nama_produk')
                            ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                            ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                            ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                            ->where('produk.kode_kategori', $kategori)
                            ->where('pemakaian.kode_lokasi', $get_lokasi)
                            ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaian.tanggal_pemakaian','asc')
                            ->get();

                        $pembelian = Pembelian::on($konek)->where('no_pembelian','like','%POGA%')
                                ->whereNotNull('no_alat')->where('jenis_po', 'Non-Stock')
                                ->whereBetween('tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))
                                ->first();
                            
                        if ($pembelian != null) {
                            $pembeliandetail = PembelianDetail::on($konek)
                                ->select('pembelian_detail.*','pembelian.tanggal_pembelian','pembelian.status','pembelian.no_alat')
                                ->join('pembelian', 'pembelian_detail.no_pembelian', '=', 'pembelian.no_pembelian')
                                ->where('pembelian.jenis_po', 'Non-Stock')
                                ->where('pembelian.kode_lokasi', $request4)
                                ->where('pembelian.no_pembelian','like','%POGA%')
                                ->whereNotNull('pembelian.no_alat')
                                ->whereBetween('pembelian.tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pembelian.tanggal_pembelian','asc')
                                ->get();
                        }
            
                        $lokasi = $get_lokasi;
                        $pdf = PDF::loadView('/admin/laporanpemakaian/pdf', compact('pemakaian','pemakaiandetail','pembelian','pembeliandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
            
                        $pdf->setPaper('a4', 'landscape');
            
                        return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }else if($kategori == 'SEMUA'){
                        $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();
                        // dd($pemakaian);
                        $pemakaiandetail = PemakaianDetail::on($konek)
                            ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','pemakaian.type','produk.kode_kategori','produk.nama_produk')
                            ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                            ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                            ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                            ->where('pemakaian.kode_lokasi', $get_lokasi)
                            ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaian.tanggal_pemakaian','asc')
                            ->get();

                        $pembelian = Pembelian::on($konek)->where('no_pembelian','like','%POGA%')
                                ->whereNotNull('no_alat')->where('jenis_po', 'Non-Stock')
                                ->whereBetween('tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))
                                ->first();
                            
                        if ($pembelian != null) {
                            $pembeliandetail = PembelianDetail::on($konek)
                                ->select('pembelian_detail.*','pembelian.tanggal_pembelian','pembelian.status','pembelian.no_alat')
                                ->join('pembelian', 'pembelian_detail.no_pembelian', '=', 'pembelian.no_pembelian')
                                ->where('pembelian.jenis_po', 'Non-Stock')
                                ->where('pembelian.kode_lokasi', $request4)
                                ->where('pembelian.no_pembelian','like','%POGA%')
                                ->whereNotNull('pembelian.no_alat')
                                ->whereBetween('pembelian.tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pembelian.tanggal_pembelian','asc')
                                ->get();
                        }
            
                        $lokasi = $get_lokasi;
                        $pdf = PDF::loadView('/admin/laporanpemakaian/pdf', compact('pemakaian','pemakaiandetail','pembelian','pembeliandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
            
                        $pdf->setPaper('a4', 'landscape');
            
                        return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                    
                }
                
                else if ($tipe == 'excel' && $tipe_pemakaian == 'Kapal'){
                    $request4 = $_GET['lokasi'];
                    $aset = 'none';
                    $asetalat = 'none';
                    $asetkapal = $_GET['assetkapal'];
                    return Excel::download(new PemakaianExport($tanggal_awal, $tanggal_akhir, $status, $tipe_pemakaian, $kategori, $request4, $aset, $asetalat, $asetkapal, $nama2, $nama, $dt, $produk, $namaproduk, $partnumber, $satuan, $kategoriproduk, $harga, $subtotal, $semua), 'Laporan Pemakaian no aset kapal '.$asetkapal.' dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                }
                
                else if ($tipe == 'excel' && $tipe_pemakaian == 'Alat'){
                    $request4 = $get_lokasi;
                    $aset = 'none';
                    $asetalat = $_GET['assetalat'];
                    $asetkapal = 'none';
                    return Excel::download(new PemakaianExport($tanggal_awal, $tanggal_akhir, $status, $tipe_pemakaian, $kategori, $request4, $aset, $asetalat, $asetkapal, $nama2, $nama, $dt, $produk, $namaproduk, $partnumber, $satuan, $kategoriproduk, $harga, $subtotal, $semua), 'Laporan Pemakaian no aset alat '.$asetalat.' dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                }
                
                else if ($tipe == 'excel' && $tipe_pemakaian == 'Mobil'){
                    $request4 = $get_lokasi;
                    $aset = $_GET['asset'];
                    $asetalat = 'none';
                    $asetkapal = 'none';
                    return Excel::download(new PemakaianExport($tanggal_awal, $tanggal_akhir, $status, $tipe_pemakaian, $kategori, $request4, $aset, $asetalat, $asetkapal, $nama2, $nama, $dt, $produk, $namaproduk, $partnumber, $satuan, $kategoriproduk, $harga, $subtotal, $semua), 'Laporan Pemakaian no aset mobil '.$aset.' dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                }

                else if ($tipe == 'excel'){
                    $request4 = $get_lokasi;
                    $aset = 'none';
                    $asetalat = 'none';
                    $asetkapal = 'none';
                    return Excel::download(new PemakaianExport($tanggal_awal, $tanggal_akhir, $status, $tipe_pemakaian, $kategori, $request4, $aset, $asetalat, $asetkapal, $nama2, $nama, $dt, $produk, $namaproduk, $partnumber, $satuan, $kategoriproduk, $harga, $subtotal, $semua), 'Laporan Pemakaian no aset mobil '.$aset.' dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                }
            }
        }
        else{
            if($get_lokasi == 'HO'){
                $lokasi = $_GET['lokasi'];
                if($lokasi != 'SEMUA'){
                    $request4 = $_GET['lokasi'];
                    if ($tipe == 'PDF' && $tipe_pemakaian == 'Alat' && $status != 'SEMUA'){
                        $asetalat = $_GET['assetalat'];
                        if ($asetalat == '' || $asetalat == null){
                            $message = [
                                'success' => false,
                                'title' => 'Gagal',
                                'message' => 'Harap pilih nomor aset dahulu.',
                            ];
                            return response()->json($message);
                        }else{
                            if($kategori != 'SEMUA'){
                                if ($asetalat != 'SEMUA'){
                                    $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                    $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat', 'pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.status', $status)
                                    ->where('pemakaian.no_asset_alat', $asetalat)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->where('pemakaian.kode_lokasi', $request4)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                    $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alathse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','asetalat','lokasi','dt','format_ttd','format_nilai','konek'));
                                    $pdf->setPaper('a4', 'landscape');
                        
                                    return $pdf->stream('Laporan Pemakaian No Aset '.$asetalat.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                                }else if ($asetalat == 'SEMUA'){
                                    $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                    $pemakaiandetail = PemakaianDetail::on($konek)
                                        ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                        ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                        ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                        ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                        ->where('pemakaian.type', $tipe_pemakaian)
                                        ->where('pemakaian.status', $status)
                                        ->where('produk.kode_kategori', $kategori)
                                        ->where('pemakaian.kode_lokasi', $request4)
                                        ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                        ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                        ->get();
                        
                                    $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alathse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','lokasi','asetalat','dt','format_ttd','format_nilai','konek'));
                        
                                    $pdf->setPaper('a4', 'landscape');
                        
                                    return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                                }
                            }
                            else if($kategori == 'SEMUA'){
                                if ($asetalat != 'SEMUA'){
                                    $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                    $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat', 'pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.status', $status)
                                    ->where('pemakaian.no_asset_alat', $asetalat)
                                    ->where('pemakaian.kode_lokasi', $request4)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                    $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alathse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','asetalat','lokasi','dt','format_ttd','format_nilai','konek'));
                                    $pdf->setPaper('a4', 'landscape');
                        
                                    return $pdf->stream('Laporan Pemakaian No Aset '.$asetalat.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                                }else if ($asetalat == 'SEMUA'){
                                    $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                    $pemakaiandetail = PemakaianDetail::on($konek)
                                        ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                        ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                        ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                        ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                        ->where('pemakaian.type', $tipe_pemakaian)
                                        ->where('pemakaian.status', $status)
                                        ->where('pemakaian.kode_lokasi', $request4)
                                        ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                        ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                        ->get();
                        
                                    $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alathse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','nama','nama2','lokasi','asetalat','dt','format_ttd','format_nilai','konek'));
                        
                                    $pdf->setPaper('a4', 'landscape');
                        
                                    return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                                }
                            }
                        }
                    }

                    else if($tipe == 'PDF' && $tipe_pemakaian == 'Mobil' && $status != 'SEMUA'){
                        $aset = $_GET['asset'];
                        if($kategori != 'SEMUA'){
                            if ($aset != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.status', $status)
                                ->where('pemakaian.no_asset_mobil', $aset)
                                ->where('produk.kode_kategori', $kategori)
                                ->where('pemakaian.kode_lokasi', $request4)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobilhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian no aset mobil '.$aset.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }else {
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.status', $status)
                                ->where('produk.kode_kategori', $kategori)
                                ->where('pemakaian.kode_lokasi', $request4)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobilhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }else if($kategori == 'SEMUA'){
                            if ($aset != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.status', $status)
                                ->where('pemakaian.no_asset_mobil', $aset)
                                ->where('pemakaian.kode_lokasi', $request4)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobilhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian no aset mobil '.$aset.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }else {
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.status', $status)
                                ->where('pemakaian.kode_lokasi', $request4)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobilhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }
                    }
                    else if($tipe == 'PDF' && $tipe_pemakaian == 'Kapal' && $status != 'SEMUA'){
                        $asetkapal = $_GET['assetkapal'];
                        if($kategori != 'SEMUA'){
                            if ($asetkapal != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.no_asset_kapal', $asetkapal)
                                    ->where('pemakaian.status', $status)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->where('pemakaian.kode_lokasi', $request4)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapalhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian no aset kapal '.$asetkapal.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            } else if ($asetkapal == 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.status', $status)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->where('pemakaian.kode_lokasi', $request4)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapalhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }else if($kategori == 'SEMUA'){
                            if ($asetkapal != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.no_asset_kapal', $asetkapal)
                                    ->where('pemakaian.status', $status)
                                    ->where('pemakaian.kode_lokasi', $request4)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapalhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian no aset kapal '.$asetkapal.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            } else if ($asetkapal == 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.status', $status)
                                    ->where('pemakaian.kode_lokasi', $request4)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapalhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }
                    }
                    else if($tipe == 'PDF' && $tipe_pemakaian == 'Other' && $status != 'SEMUA'){
                        if($kategori != 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.status', $status)
                                ->where('produk.kode_kategori', $kategori)
                                ->where('pemakaian.kode_lokasi', $request4)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_otherhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }else if($kategori == 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.status', $status)
                                ->where('pemakaian.kode_lokasi', $request4)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_otherhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }
                    }
                    else if($tipe == 'PDF' && $tipe_pemakaian == 'SEMUA' && $status != 'SEMUA'){
                        if($kategori != 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','pemakaian.type','produk.kode_kategori','produk.nama_produk')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                ->where('pemakaian.status', $status)
                                ->where('produk.kode_kategori', $kategori)
                                ->where('pemakaian.kode_lokasi', $request4)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdfhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }else if($kategori == 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','pemakaian.type','produk.kode_kategori','produk.nama_produk')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                ->where('pemakaian.status', $status)
                                ->where('pemakaian.kode_lokasi', $request4)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdfhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }
                    }
                    else if($tipe == 'PDF' && $tipe_pemakaian == 'Alat' && $status == 'SEMUA'){
                        $asetalat = $_GET['assetalat'];
                        if($kategori != 'SEMUA'){
                            if ($asetalat != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat', 'pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.no_asset_alat', $asetalat)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->where('pemakaian.kode_lokasi', $request4)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alathse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','asetalat','lokasi','dt','format_ttd','format_nilai','konek'));
                                $pdf->setPaper('a4', 'landscape');
                        
                                return $pdf->stream('Laporan Pemakaian No Aset '.$asetalat.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }else if ($asetalat == 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->where('pemakaian.kode_lokasi', $request4)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                        
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alathse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','lokasi','asetalat','dt','format_ttd','format_nilai','konek'));
                        
                                $pdf->setPaper('a4', 'landscape');
                        
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }
                        else if($kategori == 'SEMUA'){
                            if ($asetalat != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat', 'pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.no_asset_alat', $asetalat)
                                    ->where('pemakaian.kode_lokasi', $request4)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alathse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','asetalat','lokasi','dt','format_ttd','format_nilai','konek'));
                                $pdf->setPaper('a4', 'landscape');
                        
                                return $pdf->stream('Laporan Pemakaian No Aset '.$asetalat.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }else if ($asetalat == 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.kode_lokasi', $request4)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                        
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alathse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','nama','nama2','lokasi','asetalat','dt','format_ttd','format_nilai','konek'));
                        
                                $pdf->setPaper('a4', 'landscape');
                        
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }
                    }
                    else if($tipe == 'PDF' && $tipe_pemakaian == 'Mobil' && $status == 'SEMUA'){
                        $aset = $_GET['asset'];
                        if($kategori != 'SEMUA'){
                            if ($aset != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('produk.kode_kategori', $kategori)
                                ->where('pemakaian.no_asset_mobil', $aset)
                                ->where('pemakaian.kode_lokasi', $request4)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobilhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian no aset mobil '.$aset.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }else {
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('produk.kode_kategori', $kategori)
                                ->where('pemakaian.kode_lokasi', $request4)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobilhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }else if($kategori == 'SEMUA'){
                            if ($aset != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.no_asset_mobil', $aset)
                                ->where('pemakaian.kode_lokasi', $request4)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobilhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian no aset mobil '.$aset.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }else {
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.kode_lokasi', $request4)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobilhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }
                    }
                    else if($tipe == 'PDF' && $tipe_pemakaian == 'Kapal' && $status == 'SEMUA'){
                        $asetkapal = $_GET['assetkapal'];
                        if($kategori != 'SEMUA'){
                            if ($asetkapal != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.no_asset_kapal', $asetkapal)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->where('pemakaian.kode_lokasi', $request4)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapalhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian no aset kapal '.$asetkapal.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }else{
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->where('pemakaian.kode_lokasi', $request4)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapalhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }else if($kategori == 'SEMUA'){
                            if ($asetkapal != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.no_asset_kapal', $asetkapal)
                                    ->where('pemakaian.kode_lokasi', $request4)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapalhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian no aset kapal '.$asetkapal.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }else{
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.kode_lokasi', $request4)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapalhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }
                    }
                    
                    else if($tipe == 'PDF' && $tipe_pemakaian == 'Other' && $status == 'SEMUA'){
                        if($kategori != 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('produk.kode_kategori', $kategori)
                                ->where('pemakaian.kode_lokasi', $request4)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_otherhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }else if($kategori == 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.kode_lokasi', $request4)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_otherhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }
                        
                    }
                    
                    else if($tipe == 'PDF' && $tipe_pemakaian == 'SEMUA' && $status == 'SEMUA'){
                        if($kategori != 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','pemakaian.type','produk.kode_kategori','produk.nama_produk')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                ->where('produk.kode_kategori', $kategori)
                                ->where('pemakaian.kode_lokasi', $request4)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdfhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }else if($kategori == 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();
                            // dd($pemakaian);
                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','pemakaian.type','produk.kode_kategori','produk.nama_produk')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                ->where('pemakaian.kode_lokasi', $request4)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdfhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }
                        
                    }
                
                    else if ($tipe == 'excel' && $tipe_pemakaian == 'Kapal'){
                        $request4 = $_GET['lokasi'];
                        $aset = 'none';
                        $asetalat = 'none';
                        $asetkapal = $_GET['assetkapal'];
                        return Excel::download(new PemakaianExport($tanggal_awal, $tanggal_akhir, $status, $tipe_pemakaian, $kategori, $request4, $aset, $asetalat, $asetkapal, $nama2, $nama, $dt, $produk, $namaproduk, $partnumber, $satuan, $kategoriproduk, $harga, $subtotal, $semua), 'Laporan Pemakaian no aset kapal '.$asetkapal.' dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                    }
                
                    else if ($tipe == 'excel' && $tipe_pemakaian == 'Alat'){
                        $request4 = $_GET['lokasi'];
                        $aset = 'none';
                        $asetalat = $_GET['assetalat'];
                        $asetkapal = 'none';
                        return Excel::download(new PemakaianExport($tanggal_awal, $tanggal_akhir, $status, $tipe_pemakaian, $kategori, $request4, $aset, $asetalat, $asetkapal, $nama2, $nama, $dt, $produk, $namaproduk, $partnumber, $satuan, $kategoriproduk, $harga, $subtotal, $semua), 'Laporan Pemakaian no aset alat '.$asetalat.' dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                    }
                    
                    else if ($tipe == 'excel' && $tipe_pemakaian == 'Mobil'){
                        $request4 = $_GET['lokasi'];
                        $aset = $_GET['asset'];
                        $asetalat = 'none';
                        $asetkapal = 'none';
                        return Excel::download(new PemakaianExport($tanggal_awal, $tanggal_akhir, $status, $tipe_pemakaian, $kategori, $request4, $aset, $asetalat, $asetkapal, $nama2, $nama, $dt, $produk, $namaproduk, $partnumber, $satuan, $kategoriproduk, $harga, $subtotal, $semua), 'Laporan Pemakaian no aset mobil '.$aset.' dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                    }

                    else if ($tipe == 'excel'){
                        $request4 = $_GET['lokasi'];
                        $aset = 'none';
                        $asetalat = 'none';
                        $asetkapal = 'none';
                        return Excel::download(new PemakaianExport($tanggal_awal, $tanggal_akhir, $status, $tipe_pemakaian, $kategori, $request4, $aset, $asetalat, $asetkapal, $nama2, $nama, $dt, $produk, $namaproduk, $partnumber, $satuan, $kategoriproduk, $harga, $subtotal, $semua), 'Laporan Pemakaian dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                    }
                }else{
                    if ($tipe == 'PDF' && $tipe_pemakaian == 'Alat' && $status != 'SEMUA'){
                        $asetalat = $_GET['assetalat'];
                        if ($asetalat == '' || $asetalat == null){
                            $message = [
                                'success' => false,
                                'title' => 'Gagal',
                                'message' => 'Harap pilih nomor aset dahulu.',
                            ];
                            return response()->json($message);
                        }else{
                            if($kategori != 'SEMUA'){
                                if ($asetalat != 'SEMUA'){
                                    $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                    $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat', 'pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.status', $status)
                                    ->where('pemakaian.no_asset_alat', $asetalat)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                    $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alathse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','asetalat','lokasi','dt','format_ttd','format_nilai','konek'));
                                    $pdf->setPaper('a4', 'landscape');
                        
                                    return $pdf->stream('Laporan Pemakaian No Aset '.$asetalat.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                                }else if ($asetalat == 'SEMUA'){
                                    $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                    $pemakaiandetail = PemakaianDetail::on($konek)
                                        ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                        ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                        ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                        ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                        ->where('pemakaian.type', $tipe_pemakaian)
                                        ->where('pemakaian.status', $status)
                                        ->where('produk.kode_kategori', $kategori)
                                        ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                        ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                        ->get();
                        
                                    $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alathse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','lokasi','asetalat','dt','format_ttd','format_nilai','konek'));
                        
                                    $pdf->setPaper('a4', 'landscape');
                        
                                    return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                                }
                            }
                            else if($kategori == 'SEMUA'){
                                if ($asetalat != 'SEMUA'){
                                    $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                    $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat', 'pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.status', $status)
                                    ->where('pemakaian.no_asset_alat', $asetalat)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                    $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alathse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','asetalat','lokasi','dt','format_ttd','format_nilai','konek'));
                                    $pdf->setPaper('a4', 'landscape');
                        
                                    return $pdf->stream('Laporan Pemakaian No Aset '.$asetalat.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                                }else if ($asetalat == 'SEMUA'){
                                    $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                    $pemakaiandetail = PemakaianDetail::on($konek)
                                        ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                        ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                        ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                        ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                        ->where('pemakaian.type', $tipe_pemakaian)
                                        ->where('pemakaian.status', $status)
                                        ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                        ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                        ->get();
                        
                                    $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alathse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','nama','nama2','lokasi','asetalat','dt','format_ttd','format_nilai','konek'));
                        
                                    $pdf->setPaper('a4', 'landscape');
                        
                                    return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                                }
                            }
                        }
                    }

                    else if($tipe == 'PDF' && $tipe_pemakaian == 'Mobil' && $status != 'SEMUA'){
                        $aset = $_GET['asset'];
                        if($kategori != 'SEMUA'){
                            if ($aset != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.status', $status)
                                ->where('pemakaian.no_asset_mobil', $aset)
                                ->where('produk.kode_kategori', $kategori)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobilhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian no aset mobil '.$aset.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }else {
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.status', $status)
                                ->where('produk.kode_kategori', $kategori)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobilhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }else if($kategori == 'SEMUA'){
                            if ($aset != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.status', $status)
                                ->where('pemakaian.no_asset_mobil', $aset)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobilhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian no aset mobil '.$aset.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }else {
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.status', $status)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobilhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }
                    }
                    else if($tipe == 'PDF' && $tipe_pemakaian == 'Kapal' && $status != 'SEMUA'){
                        $asetkapal = $_GET['assetkapal'];
                        if($kategori != 'SEMUA'){
                            if ($asetkapal != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.no_asset_kapal', $asetkapal)
                                    ->where('pemakaian.status', $status)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapalhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian no aset kapal '.$asetkapal.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }else{
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.status', $status)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapalhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }else if($kategori == 'SEMUA'){
                            if ($asetkapal != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.no_asset_kapal', $asetkapal)
                                    ->where('pemakaian.status', $status)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapalhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian no aset kapal '.$asetkapal.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }else{
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.status', $status)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapalhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }
                    }
                    else if($tipe == 'PDF' && $tipe_pemakaian == 'Other' && $status != 'SEMUA'){
                        if($kategori != 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.status', $status)
                                ->where('produk.kode_kategori', $kategori)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_otherhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }else if($kategori == 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.status', $status)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_otherhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }
                    }
                    else if($tipe == 'PDF' && $tipe_pemakaian == 'SEMUA' && $status != 'SEMUA'){
                        if($kategori != 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','pemakaian.type','produk.kode_kategori','produk.nama_produk')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                ->where('pemakaian.status', $status)
                                ->where('produk.kode_kategori', $kategori)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdfhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }else if($kategori == 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','pemakaian.type','produk.kode_kategori','produk.nama_produk')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                ->where('pemakaian.status', $status)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdfhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }
                    }
                    else if($tipe == 'PDF' && $tipe_pemakaian == 'Alat' && $status == 'SEMUA'){
                        $asetalat = $_GET['assetalat'];
                        if($kategori != 'SEMUA'){
                            if ($asetalat != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat', 'pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.no_asset_alat', $asetalat)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alathse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','asetalat','lokasi','dt','format_ttd','format_nilai','konek'));
                                $pdf->setPaper('a4', 'landscape');
                        
                                return $pdf->stream('Laporan Pemakaian No Aset '.$asetalat.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }else if ($asetalat == 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                        
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alathse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','lokasi','asetalat','dt','format_ttd','format_nilai','konek'));
                        
                                $pdf->setPaper('a4', 'landscape');
                        
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }
                        else if($kategori == 'SEMUA'){
                            if ($asetalat != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat', 'pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.no_asset_alat', $asetalat)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alathse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','asetalat','lokasi','dt','format_ttd','format_nilai','konek'));
                                $pdf->setPaper('a4', 'landscape');
                        
                                return $pdf->stream('Laporan Pemakaian No Aset '.$asetalat.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }else if ($asetalat == 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                        
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alathse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','nama','nama2','lokasi','asetalat','dt','format_ttd','format_nilai','konek'));
                        
                                $pdf->setPaper('a4', 'landscape');
                        
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }
                    }
                    else if($tipe == 'PDF' && $tipe_pemakaian == 'Mobil' && $status == 'SEMUA'){
                        $aset = $_GET['asset'];
                        if($kategori != 'SEMUA'){
                            if ($aset != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('produk.kode_kategori', $kategori)
                                ->where('pemakaian.no_asset_mobil', $aset)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobilhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian no aset mobil '.$aset.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }else {
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('produk.kode_kategori', $kategori)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobilhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }else if($kategori == 'SEMUA'){
                            if ($aset != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.no_asset_mobil', $aset)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobilhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }else {
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobilhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }
                    }
                    else if($tipe == 'PDF' && $tipe_pemakaian == 'Kapal' && $status == 'SEMUA'){
                        $asetkapal = $_GET['assetkapal'];
                        if($kategori != 'SEMUA'){
                            if ($asetkapal != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                    ->where('pemakaian.no_asset_kapal', $asetkapal)
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapalhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian no aset kapal '.$asetkapal.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }else{
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapalhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }else if($kategori == 'SEMUA'){
                            if ($asetkapal != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                    ->where('pemakaian.no_asset_kapal', $asetkapal)
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapalhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian no aset kapal '.$asetkapal.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }else{
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapalhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }
                    }
                    
                    else if($tipe == 'PDF' && $tipe_pemakaian == 'Other' && $status == 'SEMUA'){
                        if($kategori != 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('produk.kode_kategori', $kategori)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_otherhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }else if($kategori == 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_otherhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }
                        
                    }
                    
                    else if($tipe == 'PDF' && $tipe_pemakaian == 'SEMUA' && $status == 'SEMUA'){
                        if($kategori != 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','pemakaian.type','produk.kode_kategori','produk.nama_produk')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                ->where('produk.kode_kategori', $kategori)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdfhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }else if($kategori == 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();
                            // dd($pemakaian);
                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','pemakaian.type','produk.kode_kategori','produk.nama_produk')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                                ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdfhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }
                        
                    }
                
                    else if ($tipe == 'excel' && $tipe_pemakaian == 'Kapal'){
                        $request4 = $_GET['lokasi'];
                        $aset = 'none';
                        $asetalat = 'none';
                        $asetkapal = $_GET['assetkapal'];
                        return Excel::download(new PemakaianExport($tanggal_awal, $tanggal_akhir, $status, $tipe_pemakaian, $kategori, $request4, $aset, $asetalat, $asetkapal, $nama2, $nama, $dt, $produk, $namaproduk, $partnumber, $satuan, $kategoriproduk, $harga, $subtotal, $semua), 'Laporan Pemakaian no aset kapal '.$asetkapal.' dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                    }
                
                    else if ($tipe == 'excel' && $tipe_pemakaian == 'Alat'){
                        $request4 = $_GET['lokasi'];
                        $aset = 'none';
                        $asetalat = $_GET['assetalat'];
                        $asetkapal = 'none';
                        return Excel::download(new PemakaianExport($tanggal_awal, $tanggal_akhir, $status, $tipe_pemakaian, $kategori, $request4, $aset, $asetalat, $asetkapal, $nama2, $nama, $dt, $produk, $namaproduk, $partnumber, $satuan, $kategoriproduk, $harga, $subtotal, $semua), 'Laporan Pemakaian no aset alat '.$asetalat.' dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                    }
                    
                    else if ($tipe == 'excel' && $tipe_pemakaian == 'Mobil'){
                        $request4 = $_GET['lokasi'];
                        $aset = $_GET['asset'];
                        $asetalat = 'none';
                        $asetkapal = 'none';
                        return Excel::download(new PemakaianExport($tanggal_awal, $tanggal_akhir, $status, $tipe_pemakaian, $kategori, $request4, $aset, $asetalat, $asetkapal, $nama2, $nama, $dt, $produk, $namaproduk, $partnumber, $satuan, $kategoriproduk, $harga, $subtotal, $semua), 'Laporan Pemakaian no aset mobil '.$aset.' dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                    }

                    else if ($tipe == 'excel'){
                        $request4 = $_GET['lokasi'];
                        $aset = 'none';
                        $asetalat = 'none';
                        $asetkapal = 'none';
                        return Excel::download(new PemakaianExport($tanggal_awal, $tanggal_akhir, $status, $tipe_pemakaian, $kategori, $request4, $aset, $asetalat, $asetkapal, $nama2, $nama, $dt, $produk, $namaproduk, $partnumber, $satuan, $kategoriproduk, $harga, $subtotal, $semua), 'Laporan Pemakaian dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                    }
                }
            }
            else{
                if ($tipe == 'PDF' && $tipe_pemakaian == 'Alat' && $status != 'SEMUA'){
                        $asetalat = $_GET['assetalat'];
                        if ($asetalat == '' || $asetalat == null){
                            $message = [
                                'success' => false,
                                'title' => 'Gagal',
                                'message' => 'Harap pilih nomor aset dahulu.',
                            ];
                            return response()->json($message);
                        }else{
                            if($kategori != 'SEMUA'){
                                if ($asetalat != 'SEMUA'){
                                    $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                    $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat', 'pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.status', $status)
                                    ->where('pemakaian.no_asset_alat', $asetalat)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->where('pemakaian.kode_lokasi', $get_lokasi)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                    $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alathse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','asetalat','lokasi','dt','format_ttd','format_nilai','konek'));
                                    $pdf->setPaper('a4', 'landscape');
                        
                                    return $pdf->stream('Laporan Pemakaian No Aset '.$asetalat.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                                }else if ($asetalat == 'SEMUA'){
                                    $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                    $pemakaiandetail = PemakaianDetail::on($konek)
                                        ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                        ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                        ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                        ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                        ->where('pemakaian.type', $tipe_pemakaian)
                                        ->where('pemakaian.status', $status)
                                        ->where('produk.kode_kategori', $kategori)
                                        ->where('pemakaian.kode_lokasi', $get_lokasi)
                                        ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                        ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                        ->get();
                        
                                    $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alathse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','lokasi','asetalat','dt','format_ttd','format_nilai','konek'));
                        
                                    $pdf->setPaper('a4', 'landscape');
                        
                                    return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                                }
                            }
                            else if($kategori == 'SEMUA'){
                                if ($asetalat != 'SEMUA'){
                                    $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                    $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat', 'pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.status', $status)
                                    ->where('pemakaian.no_asset_alat', $asetalat)
                                    ->where('pemakaian.kode_lokasi', $get_lokasi)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                    $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alathse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','asetalat','lokasi','dt','format_ttd','format_nilai','konek'));
                                    $pdf->setPaper('a4', 'landscape');
                        
                                    return $pdf->stream('Laporan Pemakaian No Aset '.$asetalat.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                                }else if ($asetalat == 'SEMUA'){
                                    $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                    $pemakaiandetail = PemakaianDetail::on($konek)
                                        ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                        ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                        ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                        ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                        ->where('pemakaian.type', $tipe_pemakaian)
                                        ->where('pemakaian.status', $status)
                                        ->where('pemakaian.kode_lokasi', $get_lokasi)
                                        ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                        ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                        ->get();
                        
                                    $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alathse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','nama','nama2','lokasi','asetalat','dt','format_ttd','format_nilai','konek'));
                        
                                    $pdf->setPaper('a4', 'landscape');
                        
                                    return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                                }
                            }
                        }
                } 

                else if($tipe == 'PDF' && $tipe_pemakaian == 'Mobil' && $status != 'SEMUA'){
                    $aset = $_GET['asset'];
                    if($kategori != 'SEMUA'){
                        $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaiandetail = PemakaianDetail::on($konek)
                            ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                            ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                            ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                            ->where('pemakaian.type', $tipe_pemakaian)
                            ->where('pemakaian.status', $status)
                            ->where('produk.kode_kategori', $kategori)
                            ->where('pemakaian.kode_lokasi', $get_lokasi)
                            ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaian.tanggal_pemakaian','asc')
                            ->get();
            
                        $lokasi = $get_lokasi;
                        $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobilhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
            
                        $pdf->setPaper('a4', 'landscape');
            
                        return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }else if($kategori == 'SEMUA'){
                        $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaiandetail = PemakaianDetail::on($konek)
                            ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                            ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                            ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                            ->where('pemakaian.type', $tipe_pemakaian)
                            ->where('pemakaian.status', $status)
                            ->where('pemakaian.kode_lokasi', $get_lokasi)
                            ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaian.tanggal_pemakaian','asc')
                            ->get();
            
                        $lokasi = $get_lokasi;
                        $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobilhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
            
                        $pdf->setPaper('a4', 'landscape');
            
                        return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                    
                } 

                else if($tipe == 'PDF' && $tipe_pemakaian == 'Kapal' && $status != 'SEMUA'){
                    $asetkapal = $_GET['assetkapal'];
                    if($kategori != 'SEMUA'){
                        if ($asetkapal != 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.no_asset_kapal', $asetkapal)
                                ->where('pemakaian.status', $status)
                                ->where('produk.kode_kategori', $kategori)
                                ->where('pemakaian.kode_lokasi', $get_lokasi)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $lokasi = $get_lokasi;
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapalhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian no aset kapal '.$asetkapal.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }else{
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.status', $status)
                                ->where('produk.kode_kategori', $kategori)
                                ->where('pemakaian.kode_lokasi', $get_lokasi)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $lokasi = $get_lokasi;
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapalhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }
                    }else if($kategori == 'SEMUA'){
                        if ($asetkapal != 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.no_asset_kapal', $asetkapal)
                                ->where('pemakaian.status', $status)
                                ->where('pemakaian.kode_lokasi', $get_lokasi)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $lokasi = $get_lokasi;
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapalhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian no aset kapal '.$asetkapal.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }else{
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.status', $status)
                                ->where('pemakaian.kode_lokasi', $get_lokasi)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $lokasi = $get_lokasi;
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapalhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }
                    }
                }
                
                else if($tipe == 'PDF' && $tipe_pemakaian == 'Other' && $status != 'SEMUA'){
                    if($kategori != 'SEMUA'){
                        $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaiandetail = PemakaianDetail::on($konek)
                            ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                            ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                            ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                            ->where('pemakaian.type', $tipe_pemakaian)
                            ->where('pemakaian.status', $status)
                            ->where('produk.kode_kategori', $kategori)
                            ->where('pemakaian.kode_lokasi', $get_lokasi)
                            ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaian.tanggal_pemakaian','asc')
                            ->get();
            
                        $lokasi = $get_lokasi;
                        $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_otherhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
            
                        $pdf->setPaper('a4', 'landscape');
            
                        return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }else if($kategori == 'SEMUA'){
                        $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaiandetail = PemakaianDetail::on($konek)
                            ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                            ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                            ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                            ->where('pemakaian.type', $tipe_pemakaian)
                            ->where('pemakaian.status', $status)
                            ->where('pemakaian.kode_lokasi', $get_lokasi)
                            ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaian.tanggal_pemakaian','asc')
                            ->get();
            
                        $lokasi = $get_lokasi;
                        $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_otherhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
            
                        $pdf->setPaper('a4', 'landscape');
            
                        return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                    
                }

                else if($tipe == 'PDF' && $tipe_pemakaian == 'SEMUA' && $status != 'SEMUA'){
                    if($kategori != 'SEMUA'){
                        $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaiandetail = PemakaianDetail::on($konek)
                            ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','pemakaian.type','produk.kode_kategori','produk.nama_produk')
                            ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                            ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                            ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                            ->where('pemakaian.status', $status)
                            ->where('produk.kode_kategori', $kategori)
                            ->where('pemakaian.kode_lokasi', $get_lokasi)
                            ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaian.tanggal_pemakaian','asc')
                            ->get();
            
                        $lokasi = $get_lokasi;
                        $pdf = PDF::loadView('/admin/laporanpemakaian/pdfhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
            
                        $pdf->setPaper('a4', 'landscape');
            
                        return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }else if($kategori == 'SEMUA'){
                        $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaiandetail = PemakaianDetail::on($konek)
                            ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','pemakaian.type','produk.kode_kategori','produk.nama_produk')
                            ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                            ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                            ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                            ->where('pemakaian.status', $status)
                            ->where('pemakaian.kode_lokasi', $get_lokasi)
                            ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaian.tanggal_pemakaian','asc')
                            ->get();
            
                        $lokasi = $get_lokasi;
                        $pdf = PDF::loadView('/admin/laporanpemakaian/pdfhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
            
                        $pdf->setPaper('a4', 'landscape');
            
                        return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                    
                }
                
                else if($tipe == 'PDF' && $tipe_pemakaian == 'Alat' && $status == 'SEMUA'){
                        $asetalat = $_GET['assetalat'];
                        if($kategori != 'SEMUA'){
                            if ($asetalat != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat', 'pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.no_asset_alat', $asetalat)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->where('pemakaian.kode_lokasi', $get_lokasi)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alathse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','asetalat','lokasi','dt','format_ttd','format_nilai','konek'));
                                $pdf->setPaper('a4', 'landscape');
                        
                                return $pdf->stream('Laporan Pemakaian No Aset '.$asetalat.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }else if ($asetalat == 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('produk.kode_kategori', $kategori)
                                    ->where('pemakaian.kode_lokasi', $get_lokasi)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                        
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alathse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','lokasi','asetalat','dt','format_ttd','format_nilai','konek'));
                        
                                $pdf->setPaper('a4', 'landscape');
                        
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }
                        else if($kategori == 'SEMUA'){
                            if ($asetalat != 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat', 'pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.no_asset_alat', $asetalat)
                                    ->where('pemakaian.kode_lokasi', $get_lokasi)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alathse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','asetalat','lokasi','dt','format_ttd','format_nilai','konek'));
                                $pdf->setPaper('a4', 'landscape');
                        
                                return $pdf->stream('Laporan Pemakaian No Aset '.$asetalat.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }else if ($asetalat == 'SEMUA'){
                                $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.hmkm')
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                                    ->where('pemakaian.type', $tipe_pemakaian)
                                    ->where('pemakaian.kode_lokasi', $get_lokasi)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                    ->get();
                        
                                $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_alathse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limithse','kategori','nama','nama2','nama','nama2','lokasi','asetalat','dt','format_ttd','format_nilai','konek'));
                        
                                $pdf->setPaper('a4', 'landscape');
                        
                                return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }
                }
                
                else if($tipe == 'PDF' && $tipe_pemakaian == 'Mobil' && $status == 'SEMUA'){
                    $aset = $_GET['asset'];
                    if($kategori != 'SEMUA'){
                        $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaiandetail = PemakaianDetail::on($konek)
                            ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                            ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                            ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                            ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                            ->where('pemakaian.type', $tipe_pemakaian)
                            ->where('produk.kode_kategori', $kategori)
                            ->where('pemakaian.kode_lokasi', $get_lokasi)
                            ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaian.tanggal_pemakaian','asc')
                            ->get();
            
                        $lokasi = $get_lokasi;
                        $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobilhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
            
                        $pdf->setPaper('a4', 'landscape');
            
                        return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }else if($kategori == 'SEMUA'){
                        $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaiandetail = PemakaianDetail::on($konek)
                            ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                            ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                            ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                            ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                            ->where('pemakaian.type', $tipe_pemakaian)
                            ->where('pemakaian.kode_lokasi', $get_lokasi)
                            ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaian.tanggal_pemakaian','asc')
                            ->get();
            
                        $lokasi = $get_lokasi;
                        $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_mobilhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','aset','lokasi','dt','format_ttd','format_nilai','konek'));
            
                        $pdf->setPaper('a4', 'landscape');
            
                        return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                    
                }

                else if($tipe == 'PDF' && $tipe_pemakaian == 'Kapal' && $status == 'SEMUA'){
                    $asetkapal = $_GET['assetkapal'];
                    if($kategori != 'SEMUA'){
                        if ($asetkapal != 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.no_asset_kapal', $asetkapal)
                                ->where('produk.kode_kategori', $kategori)
                                ->where('pemakaian.kode_lokasi', $get_lokasi)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $lokasi = $get_lokasi;
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapalhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian no aset kapal '.$asetkapal.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }else{
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('produk.kode_kategori', $kategori)
                                ->where('pemakaian.kode_lokasi', $get_lokasi)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $lokasi = $get_lokasi;
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapalhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }
                    }else if($kategori == 'SEMUA'){
                        if ($asetkapal != 'SEMUA'){
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.no_asset_kapal', $asetkapal)
                                ->where('pemakaian.kode_lokasi', $get_lokasi)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $lokasi = $get_lokasi;
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapalhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian no aset kapal '.$asetkapal.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }else{
                            $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaiandetail = PemakaianDetail::on($konek)
                                ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
                                ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
                                ->where('pemakaian.type', $tipe_pemakaian)
                                ->where('pemakaian.kode_lokasi', $get_lokasi)
                                ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaian.tanggal_pemakaian','asc')
                                ->get();
                
                            $lokasi = $get_lokasi;
                            $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_kapalhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','lokasi','asetkapal','dt','format_ttd','format_nilai','konek'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }
                    }
                }
                
                else if($tipe == 'PDF' && $tipe_pemakaian == 'Other' && $status == 'SEMUA'){
                    if($kategori != 'SEMUA'){
                        $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaiandetail = PemakaianDetail::on($konek)
                            ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                            ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                            ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                            ->where('pemakaian.type', $tipe_pemakaian)
                            ->where('produk.kode_kategori', $kategori)
                            ->where('pemakaian.kode_lokasi', $get_lokasi)
                            ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaian.tanggal_pemakaian','asc')
                            ->get();
            
                        $lokasi = $get_lokasi;
                        $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_otherhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
            
                        $pdf->setPaper('a4', 'landscape');
            
                        return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }else if($kategori == 'SEMUA'){
                        $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaiandetail = PemakaianDetail::on($konek)
                            ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                            ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                            ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                            ->where('pemakaian.type', $tipe_pemakaian)
                            ->where('pemakaian.kode_lokasi', $get_lokasi)
                            ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaian.tanggal_pemakaian','asc')
                            ->get();
            
                        $lokasi = $get_lokasi;
                        $pdf = PDF::loadView('/admin/laporanpemakaian/pdf_otherhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
            
                        $pdf->setPaper('a4', 'landscape');
            
                        return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                    
                }
                
                else if($tipe == 'PDF' && $tipe_pemakaian == 'SEMUA' && $status == 'SEMUA'){
                    if($kategori != 'SEMUA'){
                        $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaiandetail = PemakaianDetail::on($konek)
                            ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','pemakaian.type','produk.kode_kategori','produk.nama_produk')
                            ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                            ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                            ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                            ->where('produk.kode_kategori', $kategori)
                            ->where('pemakaian.kode_lokasi', $get_lokasi)
                            ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaian.tanggal_pemakaian','asc')
                            ->get();
            
                        $lokasi = $get_lokasi;
                        $pdf = PDF::loadView('/admin/laporanpemakaian/pdfhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
            
                        $pdf->setPaper('a4', 'landscape');
            
                        return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }else if($kategori == 'SEMUA'){
                        $pemakaian = Pemakaian::on($konek)->where('type',$tipe_pemakaian)->whereBetween('tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))->get();
                        // dd($pemakaian);
                        $pemakaiandetail = PemakaianDetail::on($konek)
                            ->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','pemakaian.kode_lokasi','pemakaian.type','produk.kode_kategori','produk.nama_produk')
                            ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                            ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
                            ->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
                            ->where('pemakaian.kode_lokasi', $get_lokasi)
                            ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaian.tanggal_pemakaian','asc')
                            ->get();
            
                        $lokasi = $get_lokasi;
                        $pdf = PDF::loadView('/admin/laporanpemakaian/pdfhse', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limithse','kategori','nama','nama2','lokasi','dt','format_ttd','format_nilai','konek'));
            
                        $pdf->setPaper('a4', 'landscape');
            
                        return $pdf->stream('Laporan Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                    
                }
                
                else if ($tipe == 'excel' && $tipe_pemakaian == 'Kapal'){
                    $request4 = $_GET['lokasi'];
                    $aset = 'none';
                    $asetalat = 'none';
                    $asetkapal = $_GET['assetkapal'];
                    return Excel::download(new PemakaianExport($tanggal_awal, $tanggal_akhir, $status, $tipe_pemakaian, $kategori, $request4, $aset, $asetalat, $asetkapal, $nama2, $nama, $dt, $produk, $namaproduk, $partnumber, $satuan, $kategoriproduk, $harga, $subtotal, $semua), 'Laporan Pemakaian no aset kapal '.$asetkapal.' dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                }
                
                else if ($tipe == 'excel' && $tipe_pemakaian == 'Alat'){
                    $request4 = $get_lokasi;
                    $aset = 'none';
                    $asetalat = $_GET['assetalat'];
                    $asetkapal = 'none';
                    return Excel::download(new PemakaianExport($tanggal_awal, $tanggal_akhir, $status, $tipe_pemakaian, $kategori, $request4, $aset, $asetalat, $asetkapal, $nama2, $nama, $dt, $produk, $namaproduk, $partnumber, $satuan, $kategoriproduk, $harga, $subtotal, $semua), 'Laporan Pemakaian no aset alat '.$asetalat.' dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                }
                
                else if ($tipe == 'excel' && $tipe_pemakaian == 'Mobil'){
                    $request4 = $get_lokasi;
                    $aset = $_GET['asset'];
                    $asetalat = 'none';
                    $asetkapal = 'none';
                    return Excel::download(new PemakaianExport($tanggal_awal, $tanggal_akhir, $status, $tipe_pemakaian, $kategori, $request4, $aset, $asetalat, $asetkapal, $nama2, $nama, $dt, $produk, $namaproduk, $partnumber, $satuan, $kategoriproduk, $harga, $subtotal, $semua), 'Laporan Pemakaian no aset mobil '.$aset.' dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                }

                else if ($tipe == 'excel'){
                    $request4 = $get_lokasi;
                    $aset = 'none';
                    $asetalat = 'none';
                    $asetkapal = 'none';
                    return Excel::download(new PemakaianExport($tanggal_awal, $tanggal_akhir, $status, $tipe_pemakaian, $kategori, $request4, $aset, $asetalat, $asetkapal, $nama2, $nama, $dt, $produk, $namaproduk, $partnumber, $satuan, $kategoriproduk, $harga, $subtotal, $semua), 'Laporan Pemakaian dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                }
            }
        }
    }
}
