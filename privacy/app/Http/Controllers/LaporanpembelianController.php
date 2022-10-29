<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\tb_akhir_bulan;
use App\Models\LaporanPemakaian;
use App\Models\LaporanPenerimaan;
use App\Models\LaporanPembelian;
use App\Models\tb_item_bulanan;
use App\Models\tb_produk_history;
use App\Models\Produk;
use App\Models\Jasa;
use App\Models\Vendor;
use App\Models\Mobil;
use App\Models\Pembelian;
use App\Models\KategoriProduk;
use App\Models\MasterLokasi;
use App\Models\PembelianDetail;
use App\Models\Signature;
use App\Models\Company;
use App\Exports\PembelianExport;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use DB;
use Carbon;

class LaporanpembelianController extends Controller
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
        $create_url = route('laporanpembelian.create');
        $no_pembelian = Pembelian::on($konek)->pluck('no_pembelian','no_pembelian');
        $Produk = Produk::on($konek)->pluck('nama_produk', 'id');
        $kategori = KategoriProduk::select('kode_kategori', DB::raw("concat(kode_kategori,' - ',nama_kategori) as kategori"))->pluck('kategori','kode_kategori');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        return view('admin.laporanpembelian.index',compact('create_url','Produk','no_pembelian','period','kategori', 'nama_lokasi'));

    }

    public function exportPDF(){
        $konek = self::konek();
        $tanggal_awal = $_GET['tanggal_awal'];
        $tanggal_akhir = $_GET['tanggal_akhir'];
        $tipe = $_GET['jenis_report'];
        $status = $_GET['status'];
        $kategori = $_GET['kategori'];
        $jenis = $_GET['jenis'];
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

        $nama_lokasi = MasterLokasi::find($get_lokasi);
        $nama = $nama_lokasi->nama_lokasi;

        $nama_company = Company::find($get_company);
        $nama2 = $nama_company->nama_company;

        if($jenis == 'Stock'){
            if ($tipe=='PDF' && $status != 'SEMUA'){
                if($kategori != 'SEMUA'){
                    $pembelian = Pembelian::on($konek)->whereBetween('tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))->get();

                    $pembeliandetail = PembelianDetail::on($konek)
                        ->with('vendor')
                        ->select('pembelian_detail.*','pembelian.*','produk.kode_kategori','produk.nama_produk')
                        ->join('pembelian', 'pembelian_detail.no_pembelian', '=', 'pembelian.no_pembelian')
                        ->join('produk','pembelian_detail.kode_produk', '=', 'produk.id')
                        ->where('pembelian.status', $status)
                        ->where('pembelian.jenis_po', $jenis)
                        ->where('produk.kode_kategori', $kategori)
                        ->where('pembelian.kode_lokasi', $get_lokasi)
                        ->whereBetween('pembelian.tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))
                        ->orderBy('pembelian.no_pembelian','asc')
                        ->orderBy('pembelian.kode_vendor')
                        ->get();

                    $pdf = PDF::loadView('/admin/laporanpembelian/pdf', compact('pembelian','pembeliandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','kategori','dt','format_ttd','jenis'));

                    $pdf->setPaper('a4', 'landscape');

                    return $pdf->stream('Laporan Pembelian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                }
                else if($kategori == 'SEMUA'){
                    $pembelian = Pembelian::on($konek)->whereBetween('tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))->get();

                    $pembeliandetail = PembelianDetail::on($konek)
                        ->with('vendor')
                        ->select('pembelian_detail.*','pembelian.*','produk.kode_kategori','produk.nama_produk')
                        ->join('pembelian', 'pembelian_detail.no_pembelian', '=', 'pembelian.no_pembelian')
                        ->join('produk','pembelian_detail.kode_produk', '=', 'produk.id')
                        ->where('pembelian.status', $status)
                        ->where('pembelian.jenis_po', $jenis)
                        ->where('pembelian.kode_lokasi', $get_lokasi)
                        ->whereBetween('pembelian.tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))
                        ->orderBy('pembelian.no_pembelian','asc')
                        ->orderBy('pembelian.kode_vendor')
                        ->get();

                    $pdf = PDF::loadView('/admin/laporanpembelian/pdf', compact('pembelian','pembeliandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','kategori','dt','format_ttd','jenis'));

                    $pdf->setPaper('a4', 'landscape');

                    return $pdf->stream('Laporan Pembelian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                }
            }
            else if($tipe == 'PDF' && $status == 'SEMUA'){
                if($kategori != 'SEMUA'){
                    $pembelian = Pembelian::on($konek)->whereBetween('tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))->get();

                    $pembeliandetail = PembelianDetail::on($konek)
                        ->with('vendor')
                        ->select('pembelian_detail.*','pembelian.*','produk.kode_kategori','produk.nama_produk')
                        ->join('pembelian', 'pembelian_detail.no_pembelian', '=', 'pembelian.no_pembelian')
                        ->join('produk','pembelian_detail.kode_produk', '=', 'produk.id')
                        ->where('produk.kode_kategori', $kategori)
                        ->where('pembelian.jenis_po', $jenis)
                        ->where('pembelian.kode_lokasi', $get_lokasi)
                        ->whereBetween('pembelian.tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))
                        ->orderBy('pembelian.no_pembelian','asc')
                        ->orderBy('pembelian.kode_vendor')
                        ->get();
                    // dd($pembeliandetail);
                    
                    $pdf = PDF::loadView('/admin/laporanpembelian/pdf', compact('pembelian','pembeliandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','kategori','dt','format_ttd','jenis'));

                    $pdf->setPaper('a4', 'landscape');

                    return $pdf->stream('Laporan Pembelian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                }
                else if($kategori == 'SEMUA'){
                    $pembelian = Pembelian::on($konek)->whereBetween('tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))->get();
                    
                    $pembeliandetail = PembelianDetail::on($konek)
                        ->with('vendor')
                        ->select('pembelian_detail.*','pembelian.*','produk.kode_kategori','produk.nama_produk')
                        ->join('pembelian', 'pembelian_detail.no_pembelian', '=', 'pembelian.no_pembelian')
                        ->join('produk','pembelian_detail.kode_produk', '=', 'produk.id')
                        ->where('pembelian.kode_lokasi', $get_lokasi)
                        ->where('pembelian.jenis_po', $jenis)
                        ->whereBetween('pembelian.tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))
                        ->orderBy('pembelian.no_pembelian','asc')
                        ->orderBy('pembelian.kode_vendor')
                        ->get();

                    $pdf = PDF::loadView('/admin/laporanpembelian/pdf', compact('pembelian','pembeliandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','kategori','dt','format_ttd','jenis'));

                    $pdf->setPaper('a4', 'landscape');

                    return $pdf->stream('Laporan Pembelian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                }
            }
            else if ($tipe == 'excel'){
                return Excel::download(new PembelianExport($tanggal_awal, $tanggal_akhir, $status, $kategori, $get_lokasi, $jenis), 'Laporan Pembelian dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
            }
        }
        else if($jenis == 'Non-Stock'){
            if ($tipe=='PDF' && $status != 'SEMUA'){
                $pembelian = Pembelian::on($konek)->whereBetween('tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))->get();

                $pembeliandetail = PembelianDetail::on($konek)
                    ->with('vendor','nonstock')
                    ->select('pembelian_detail.*','pembelian.*')
                    ->join('pembelian', 'pembelian_detail.no_pembelian', '=', 'pembelian.no_pembelian')
                    ->where('pembelian.jenis_po', $jenis)
                    ->where('pembelian.status', $status)
                    ->where('pembelian.kode_lokasi', $get_lokasi)
                    ->whereBetween('pembelian.tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))
                    ->orderBy('pembelian.no_pembelian','asc')
                    ->orderBy('pembelian.kode_vendor')
                    ->get();

                $pdf = PDF::loadView('/admin/laporanpembelian/pdf', compact('pembelian','pembeliandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','dt','format_ttd','jenis','kategori'));

                $pdf->setPaper('a4', 'landscape');

                return $pdf->stream('Laporan Pembelian Barang Non Stock Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
            }
            else if($tipe == 'PDF' && $status == 'SEMUA'){
                $pembelian = Pembelian::on($konek)->whereBetween('tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))->get();

                $pembeliandetail = PembelianDetail::on($konek)
                    ->with('vendor','nonstock')
                    ->select('pembelian_detail.*','pembelian.*')
                    ->join('pembelian', 'pembelian_detail.no_pembelian', '=', 'pembelian.no_pembelian')
                    ->where('pembelian.jenis_po', $jenis)
                    ->where('pembelian.kode_lokasi', $get_lokasi)
                    ->whereBetween('pembelian.tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))
                    ->orderBy('pembelian.no_pembelian','asc')
                    ->orderBy('pembelian.kode_vendor')
                    ->get();

                $pdf = PDF::loadView('/admin/laporanpembelian/pdf', compact('pembelian','pembeliandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','dt','format_ttd','jenis','kategori'));

                $pdf->setPaper('a4', 'landscape');

                return $pdf->stream('Laporan Pembelian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
            }
            else if ($tipe == 'excel'){
                $kategori = 'Non-Stock';
                return Excel::download(new PembelianExport($tanggal_awal, $tanggal_akhir, $status, $kategori, $get_lokasi, $jenis), 'Laporan Pembelian dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
            }
        }
        else{
            if ($tipe=='PDF' && $status != 'SEMUA'){
                $pembelian = Pembelian::on($konek)->whereBetween('tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))->get();

                $pembeliandetail = PembelianDetail::on($konek)
                    ->with('vendor','jasa')
                    ->select('pembelian_detail.*','pembelian.*')
                    ->join('pembelian', 'pembelian_detail.no_pembelian', '=', 'pembelian.no_pembelian')
                    ->where('pembelian.jenis_po', $jenis)
                    ->where('pembelian.status', $status)
                    ->where('pembelian.kode_lokasi', $get_lokasi)
                    ->whereBetween('pembelian.tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))
                    ->orderBy('pembelian.no_pembelian','asc')
                    ->orderBy('pembelian.kode_vendor')
                    ->get();

                $pdf = PDF::loadView('/admin/laporanpembelian/pdf', compact('pembelian','pembeliandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','dt','format_ttd','jenis','kategori'));

                $pdf->setPaper('a4', 'landscape');

                return $pdf->stream('Laporan Pembelian Barang Non Stock Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
            }
            else if($tipe == 'PDF' && $status == 'SEMUA'){
                $pembelian = Pembelian::on($konek)->whereBetween('tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))->get();

                $pembeliandetail = PembelianDetail::on($konek)
                    ->with('vendor','jasa')
                    ->select('pembelian_detail.*','pembelian.*')
                    ->join('pembelian', 'pembelian_detail.no_pembelian', '=', 'pembelian.no_pembelian')
                    ->where('pembelian.jenis_po', $jenis)
                    ->where('pembelian.kode_lokasi', $get_lokasi)
                    ->whereBetween('pembelian.tanggal_pembelian', array($tanggal_awal, $tanggal_akhir))
                    ->orderBy('pembelian.no_pembelian','asc')
                    ->orderBy('pembelian.kode_vendor')
                    ->get();

                $pdf = PDF::loadView('/admin/laporanpembelian/pdf', compact('pembelian','pembeliandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','dt','format_ttd','jenis','kategori'));

                $pdf->setPaper('a4', 'landscape');

                return $pdf->stream('Laporan Pembelian Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
            }
            else if ($tipe == 'excel'){
                $kategori = 'Jasa';
                return Excel::download(new PembelianExport($tanggal_awal, $tanggal_akhir, $status, $kategori, $get_lokasi, $jenis), 'Laporan Pembelian dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
            }
        }
    }
}
