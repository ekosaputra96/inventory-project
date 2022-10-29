<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\tb_akhir_bulan;
use App\Models\LaporanPemakaian;
use App\Models\tb_item_bulanan;
use App\Models\tb_produk_history;
use App\Models\Produk;
use App\Models\Merek;
use App\Models\KategoriProduk;
use App\Models\Mobil;
use App\Models\Pemakaian;
use App\Models\PemakaianDetail;
use App\Models\LaporanProduk;
use App\Models\MasterLokasi;
use App\Models\Company;
use App\Models\Signature;
use App\Exports\ProdukExport;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use DB;
use Carbon;

class LaporanprodukController extends Controller
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
        $create_url = route('laporanproduk.create');
        $no_pemakaian = Pemakaian::on($konek)->pluck('no_pemakaian','no_pemakaian');
        $Produk = Produk::on($konek)->pluck('nama_produk', 'id');
        $lokasi = tb_item_bulanan::on($konek)->pluck('kode_lokasi', 'kode_lokasi');
        $kategori = KategoriProduk::select('kode_kategori', DB::raw("concat(kode_kategori,' - ',nama_kategori) as kategori"))->pluck('kategori','kode_kategori');
        $merek = Merek::on($konek)->pluck('nama_merek','kode_merek');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        $lokasi_user = auth()->user()->kode_lokasi;
        
        return view('admin.laporanproduk.index',compact('merek','create_url','Produk','no_pemakaian','period', 'nama_lokasi','lokasi','kategori'));
        
    }

    public function exportPDF(){
        $konek = self::konek();
        $format = $_GET['format_cetak'];
        $kategori = $_GET['kategori'];
        $merek = $_GET['merek'];
        
        if(isset($_GET['ttd'])){
            $format_ttd = $_GET['ttd']; 
        }else{
            $format_ttd = 0;
        }
        
        $dt = Carbon\Carbon::now();
        $date=date_create($dt);

        $level = auth()->user()->level;
        if($level != 'hse'){
            $limit3 = Signature::on($konek)->where('jabatan','MANAGER OPERASIONAL')->first();
            if($limit3 == null){
                $limit3 = Signature::on($konek)->where('jabatan','DIREKTUR')->where('kode_lokasi',auth()->user()->kode_lokasi)->first();
            }
        }else{
            $limit3 = Signature::on($konek)->where('jabatan','MANAGER HSE')->first();
        }
    
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
            $nama_lokasi = MasterLokasi::find($get_lokasi);
            $nama = $nama_lokasi->nama_lokasi;
        }

        $nama_company = Company::find($get_company);
        $nama2 = $nama_company->nama_company;
        
        $cek_bulan2 = tb_akhir_bulan::on($konek)->where('status_periode', 'Open')->orwhere('reopen_status', 'true')->first();
        $bulan2 = $cek_bulan2->periode;

        if($get_lokasi == 'HO'){
            $lokasi = $_GET['lokasi'];
            if($lokasi != 'SEMUA'){
                $request4 = $_GET['lokasi'];
                if($format == 'PDF'){
                    if($kategori != 'SEMUA'){
                        $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode', 'Open')->orwhere('reopen_status', 'true')->first();
                        
                        if ($merek != 'SEMUA'){
                            $monthly = tb_item_bulanan::on($konek)
                            ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori','produk.kode_satuan')
                            ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                            ->where('produk.kode_kategori', $kategori)
                            ->where('produk.kode_merek', $merek)
                            ->where('tb_item_bulanan.kode_lokasi', $request4)
                            ->where('tb_item_bulanan.periode', $cek_bulan->periode)
                            ->get();
                        }else {
                            $monthly = tb_item_bulanan::on($konek)
                            ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori','produk.kode_satuan')
                            ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                            ->where('produk.kode_kategori', $kategori)
                            ->where('tb_item_bulanan.kode_lokasi', $request4)
                            ->where('tb_item_bulanan.periode', $cek_bulan->periode)
                            ->get();
                        }

                        $pdf = PDF::loadView('/admin/laporanproduk/pdf', compact('konek','merek','monthly','date','ttd','limit3','kategori','nama','nama2','lokasi','dt','format_ttd'));
                        $pdf->setPaper('a4', 'landscape');
                        return $pdf->stream('Laporan Data Produk.pdf');
                    }
                    else{
                        $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode', 'Open')->orwhere('reopen_status', 'true')->first();
                        
                        if ($merek != 'SEMUA'){
                            $monthly = tb_item_bulanan::on($konek)
                            ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori','produk.kode_satuan')
                            ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                            ->where('produk.kode_merek', $merek)
                            ->where('tb_item_bulanan.kode_lokasi', $request4)
                            ->where('tb_item_bulanan.periode', $cek_bulan->periode)
                            ->get();
                        }else {
                            $monthly = tb_item_bulanan::on($konek)
                            ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori','produk.kode_satuan')
                            ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                            ->where('tb_item_bulanan.kode_lokasi', $request4)
                            ->where('tb_item_bulanan.periode', $cek_bulan->periode)
                            ->get();
                        }

                        $pdf = PDF::loadView('/admin/laporanproduk/pdf', compact('konek','merek','monthly','date','ttd','limit3','kategori','nama','nama2','lokasi','dt','format_ttd'));
                        $pdf->setPaper('a4', 'landscape');
                        return $pdf->stream('Laporan Data Produk.pdf');
                    }
                }
                else{
                    $produk_cetak = Produk::on($konek)->get();
                    return Excel::download(new ProdukExport($lokasi, $bulan2, $kategori, $merek), 'Laporan Produk.xlsx');
                }
            }
            else{
                if($format == 'PDF'){
                    if($kategori != 'SEMUA'){
                        $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode', 'Open')->orwhere('reopen_status', 'true')->first();

                        if ($merek != 'SEMUA'){
                            $monthly = tb_item_bulanan::on($konek)
                            ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori','produk.kode_satuan')
                            ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                            ->where('produk.kode_kategori', $kategori)
                            ->where('produk.kode_merek', $merek)
                            ->where('tb_item_bulanan.periode', $cek_bulan->periode)
                            ->get();
                        }else {
                            $monthly = tb_item_bulanan::on($konek)
                            ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori','produk.kode_satuan')
                            ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                            ->where('produk.kode_kategori', $kategori)
                            ->where('tb_item_bulanan.periode', $cek_bulan->periode)
                            ->get();
                        }

                        $pdf = PDF::loadView('/admin/laporanproduk/pdf', compact('konek','merek','monthly','date','ttd','limit3','kategori','nama','nama2','lokasi','dt','format_ttd'));
                        $pdf->setPaper('a4', 'landscape');
                        return $pdf->stream('Laporan Data Produk.pdf');
                    }
                    else{
                        $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode', 'Open')->orwhere('reopen_status', 'true')->first();
                        
                        if ($merek != 'SEMUA'){
                            $monthly = tb_item_bulanan::on($konek)
                            ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori','produk.kode_satuan')
                            ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                            ->where('tb_item_bulanan.periode', $cek_bulan->periode)
                            ->where('produk.kode_merek', $merek)
                            ->get();
                        }else {
                            $monthly = tb_item_bulanan::on($konek)
                            ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori','produk.kode_satuan')
                            ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                            ->where('tb_item_bulanan.periode', $cek_bulan->periode)
                            ->get();
                        }
                        
                        $pdf = PDF::loadView('/admin/laporanproduk/pdf', compact('konek','merek','monthly','date','ttd','limit3','kategori','nama','nama2','lokasi','dt','format_ttd'));
                        $pdf->setPaper('a4', 'landscape');
                        return $pdf->stream('Laporan Data Produk.pdf');
                    }
                }
                else{
                    $produk_cetak = Produk::on($konek)->get();
                    return Excel::download(new ProdukExport($lokasi, $bulan2, $kategori, $merek), 'Laporan Produk.xlsx');
                }
            }
        }
        else{
            if($format == 'PDF'){
                if($kategori != 'SEMUA'){
                    $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode', 'Open')->orwhere('reopen_status', 'true')->first();

                    if ($merek != 'SEMUA'){
                        $monthly = tb_item_bulanan::on($konek)
                        ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori','produk.kode_satuan')
                        ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                        ->where('produk.kode_kategori', $kategori)
                        ->where('produk.kode_merek', $merek)
                        ->where('tb_item_bulanan.kode_lokasi', $get_lokasi)
                        ->where('tb_item_bulanan.periode', $cek_bulan->periode)
                        ->get();
                    }else {
                        $monthly = tb_item_bulanan::on($konek)
                        ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori','produk.kode_satuan')
                        ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                        ->where('produk.kode_kategori', $kategori)
                        ->where('tb_item_bulanan.kode_lokasi', $get_lokasi)
                        ->where('tb_item_bulanan.periode', $cek_bulan->periode)
                        ->get();
                    }
                    
                    $lokasi = $get_lokasi;
                    $pdf = PDF::loadView('/admin/laporanproduk/pdf', compact('konek','merek','monthly','date','ttd','limit3','kategori','nama','nama2','lokasi','dt','format_ttd'));
                    $pdf->setPaper('a4', 'landscape');
                    return $pdf->stream('Laporan Data Produk.pdf');
                }else{
                    $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode', 'Open')->orwhere('reopen_status', 'true')->first();

                    if ($merek != 'SEMUA'){
                        $monthly = tb_item_bulanan::on($konek)
                        ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori','produk.kode_satuan')
                        ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                        ->where('tb_item_bulanan.kode_lokasi', $get_lokasi)
                        ->where('produk.kode_merek', $merek)
                        ->where('tb_item_bulanan.periode', $cek_bulan->periode)
                        ->get();
                    }else {
                        $monthly = tb_item_bulanan::on($konek)
                        ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori','produk.kode_satuan')
                        ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                        ->where('tb_item_bulanan.kode_lokasi', $get_lokasi)
                        ->where('tb_item_bulanan.periode', $cek_bulan->periode)
                        ->get();
                    }

                    $lokasi = $get_lokasi;
                    $pdf = PDF::loadView('/admin/laporanproduk/pdf', compact('konek','merek','monthly','date','ttd','limit3','kategori','nama','nama2','lokasi','dt','format_ttd'));
                    $pdf->setPaper('a4', 'landscape');
                    return $pdf->stream('Laporan Data Produk.pdf');
                }
            }
            else{
                $produk_cetak = Produk::on($konek)->get();
                return Excel::download(new ProdukExport($get_lokasi, $bulan2, $kategori, $merek), 'Laporan Produk.xlsx');
            }
        }
    }
}
