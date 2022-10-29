<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\tb_akhir_bulan;
use App\Models\LaporanPemakaianqty;
use App\Models\tb_item_bulanan;
use App\Models\tb_produk_history;
use App\Models\Produk;
use App\Models\Mobil;
use App\Models\Pemakaian;
use App\Models\KategoriProduk;
use App\Models\PemakaianDetail;
use App\Exports\PemakaianqtyExport;
use App\Models\MasterLokasi;
use App\Models\Company;
use App\Models\Signature;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use DB;
use Carbon;

class LaporanpemakaianqtyController extends Controller
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
        $create_url = route('laporanpemakaianqty.create');
        $no_pemakaian = Pemakaian::on($konek)->pluck('no_pemakaian','no_pemakaian');
        $Produk = Produk::on($konek)->Join('tb_item_bulanan', 'produk.id', '=', 'tb_item_bulanan.kode_produk')->where('kode_lokasi',auth()->user()->kode_lokasi)->select('produk.id', DB::raw("concat(produk.id,' - ',nama_produk) as produks"))->pluck('produks','produk.id');
        $Aset = Mobil::on($konek)->pluck('no_asset_mobil','no_asset_mobil');
        $kategori = KategoriProduk::select('kode_kategori', DB::raw("concat(kode_kategori,' - ',nama_kategori) as kategori"))->pluck('kategori','kode_kategori');
        $lokasi = tb_item_bulanan::on($konek)->pluck('kode_lokasi', 'kode_lokasi');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        $lokasi_user = auth()->user()->kode_lokasi;

        return view('admin.laporanpemakaianqty.index',compact('create_url','Produk','no_pemakaian','period','kategori', 'nama_lokasi','lokasi','Aset'));
    }

    public function exportPDF(){
        $konek = self::konek();
        $tanggal_awal = $_GET['tanggal_awal'];
        $tanggal_akhir = $_GET['tanggal_akhir'];
        $tipe = $_GET['jenis_report'];
        $pilih = $_GET['show'];
        if(isset($_GET['ttd'])){
            $format_ttd = $_GET['ttd']; 
        }else{
            $format_ttd = 0;
        }

        $level = auth()->user()->level;
        if($level != 'hse'){
            $limit3 = Signature::on($konek)->where('jabatan','MANAGER OPERASIONAL')->first();
            if($limit3 == null){
                $limit3 = Signature::on($konek)->where('jabatan','DIREKTUR')->where('kode_lokasi',auth()->user()->kode_lokasi)->first();
            }
        }else{
            $limit3 = Signature::on($konek)->where('jabatan','MANAGER HSE')->first();
        }

        $dt = Carbon\Carbon::now();
        $date=date_create($dt);
    
        $ttd = auth()->user()->name;
        $get_lokasi = auth()->user()->kode_lokasi;
        $get_company = auth()->user()->kode_company;
        
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
            $lokasi = $get_lokasi;
            $nama_lokasi = MasterLokasi::find($get_lokasi);
            $nama = $nama_lokasi->nama_lokasi;
        }

        $nama_company = Company::find($get_company);
        $nama2 = $nama_company->nama_company;

        if($get_lokasi == 'HO'){
            $request4 = $_GET['lokasi'];
        }else{
            $request4 = $get_lokasi;
        }
        
        if($pilih == 'Kategori'){
            $produk = 'none';
            $kategori = $_GET['kategori'];
                if($get_lokasi == 'HO'){     
                        $request4 = $_GET['lokasi'];
                        if($tipe == 'PDF'){
                            if($kategori != 'SEMUA'){
                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk', DB::raw("SUM(pemakaian_detail.qty) as qtys"))
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->where('produk.kode_kategori', $kategori)
                                    ->where('pemakaian.kode_lokasi', $request4)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->groupBy('pemakaian_detail.kode_produk')
                                    ->orderBy('qtys','desc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaianqty/pdf', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','lokasi','dt','format_ttd'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan QTY Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }else if($kategori == 'SEMUA'){
                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk', DB::raw("SUM(pemakaian_detail.qty) as qtys"))
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->where('pemakaian.kode_lokasi', $request4)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->groupBy('pemakaian_detail.kode_produk')
                                    ->orderBy('qtys','desc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaianqty/pdf', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','lokasi','dt','format_ttd'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan QTY Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                            }
                        }
                        else if ($tipe == 'excel'){
                            $request4 = $_GET['lokasi'];
                            $aset = 'none';
                            return Excel::download(new PemakaianqtyExport($tanggal_awal, $tanggal_akhir, $kategori, $request4, $pilih, $produk), 'Laporan QTY Pemakaian dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                        }
                }
                else{
                    if($tipe == 'PDF'){
                        if($kategori != 'SEMUA'){
                            $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk', DB::raw("SUM(pemakaian_detail.qty) as qtys"))
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->where('produk.kode_kategori', $kategori)
                                    ->where('pemakaian.kode_lokasi', $get_lokasi)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->groupBy('pemakaian_detail.kode_produk')
                                    ->orderBy('qtys','desc')
                                    ->get();
                
                            $lokasi = $get_lokasi;
                            $pdf = PDF::loadView('/admin/laporanpemakaianqty/pdf', compact('pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','lokasi','dt','format_ttd'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan QTY Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }else if($kategori == 'SEMUA'){
                            $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk', DB::raw("SUM(pemakaian_detail.qty) as qtys"))
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->where('pemakaian.kode_lokasi', $get_lokasi)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->groupBy('pemakaian_detail.kode_produk')
                                    ->orderBy('qtys','desc')
                                    ->get();
                
                            $lokasi = $get_lokasi;
                            $pdf = PDF::loadView('/admin/laporanpemakaianqty/pdf', compact('pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','lokasi','dt','format_ttd'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan QTY Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }
                    }
                    else if ($tipe == 'excel'){
                        $request4 = $get_lokasi;
                        $aset = 'none';
                        return Excel::download(new PemakaianqtyExport($tanggal_awal, $tanggal_akhir, $kategori, $request4, $pilih, $produk), 'Laporan Pemakaian QTY dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                    }
                }
        }
        else{
            $produk = $_GET['kode_produk'];
            $produk2 = Produk::on($konek)->where('id',$produk)->first();
            $nama_produk = $produk2->nama_produk;
            $kategori = $produk2->kode_kategori;

            
                if($get_lokasi == 'HO'){
                    $lokasi = $_GET['lokasi'];
                    if($lokasi != 'SEMUA'){
                        $request4 = $_GET['lokasi'];
                        if($tipe == 'PDF'){
                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk', DB::raw("SUM(pemakaian_detail.qty) as qtys"))
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->where('produk.id', $produk)
                                    ->where('pemakaian.kode_lokasi', $request4)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->groupBy('pemakaian_detail.kode_produk')
                                    ->orderBy('qtys','desc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaianqty/pdf2', compact('pemakaian','pemakaiandetail','tanggal_awal','tanggal_akhir','Mobil','date','ttd','limit3','kategori','nama','nama2','lokasi','nama_produk','produk','dt','format_ttd'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan QTY Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }
                        else if ($tipe == 'excel'){
                            $request4 = $_GET['lokasi'];
                            $aset = 'none';
                            return Excel::download(new PemakaianqtyExport($tanggal_awal, $tanggal_akhir, $kategori, $request4, $pilih, $produk), 'Laporan QTY Pemakaian dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                        }
                    }else{
                        if($tipe == 'PDF'){
                                $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk', DB::raw("SUM(pemakaian_detail.qty) as qtys"))
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->where('produk.id', $produk)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->groupBy('pemakaian_detail.kode_produk')
                                    ->orderBy('qtys','desc')
                                    ->get();
                    
                                $pdf = PDF::loadView('/admin/laporanpemakaianqty/pdf2', compact('pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','lokasi','nama_produk','produk','dt','format_ttd'));
                    
                                $pdf->setPaper('a4', 'landscape');
                    
                                return $pdf->stream('Laporan QTY Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }
                        else if ($tipe == 'excel'){
                            $request4 = $_GET['lokasi'];
                            $aset = 'none';
                            return Excel::download(new PemakaianqtyExport($tanggal_awal, $tanggal_akhir, $kategori, $request4, $pilih, $produk), 'Laporan QTY Pemakaian dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                        }
                    }
                }
                else{
                    if($tipe == 'PDF'){
                            $pemakaiandetail = PemakaianDetail::on($konek)
                                    ->select('pemakaian_detail.*','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk', DB::raw("SUM(pemakaian_detail.qty) as qtys"))
                                    ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                                    ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                                    ->where('produk.id', $produk)
                                    ->where('pemakaian.kode_lokasi', $get_lokasi)
                                    ->whereBetween('pemakaian.tanggal_pemakaian', array($tanggal_awal, $tanggal_akhir))
                                    ->groupBy('pemakaian_detail.kode_produk')
                                    ->orderBy('qtys','desc')
                                    ->get();
                
                            $lokasi = $get_lokasi;
                            $pdf = PDF::loadView('/admin/laporanpemakaianqty/pdf2', compact('pemakaiandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','lokasi','nama_produk','produk','dt','format_ttd'));
                
                            $pdf->setPaper('a4', 'landscape');
                
                            return $pdf->stream('Laporan QTY Pemakaian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                    else if ($tipe == 'excel'){
                        $request4 = $get_lokasi;
                        $aset = 'none';
                        return Excel::download(new PemakaianqtyExport($tanggal_awal, $tanggal_akhir, $kategori, $request4, $pilih, $produk), 'Laporan Pemakaian QTY dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                    }
                }
        }
    }
}
