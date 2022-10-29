<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\tb_akhir_bulan;
use App\Models\LaporanPemakaianban;
use App\Models\tb_item_bulanan;
use App\Models\tb_produk_history;
use App\Models\Produk;
use App\Models\Jasa;
use App\Models\Vendor;
use App\Models\Mobil;
use App\Models\Alat;
use App\Models\KategoriProduk;
use App\Models\MasterLokasi;
use App\Models\Company;
use App\Models\Pemakaianban;
use App\Models\Signature;
use App\Models\PemakaianbanDetail;
use App\Exports\PemakaianbanExport;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use DB;
use Carbon;

class LaporanpemakaianbanController extends Controller
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
        $create_url = route('laporanpemakaianban.create');

        $Aset = Mobil::on($konek)->pluck('no_asset_mobil','no_asset_mobil');
        $Asetalat = Alat::on($konek)->pluck('no_asset_alat','no_asset_alat');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $lokasi = tb_item_bulanan::on($konek)->pluck('kode_lokasi', 'kode_lokasi');
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        $lokasi_user = auth()->user()->kode_lokasi;
        
        return view('admin.laporanpemakaianban.index',compact('create_url','period', 'nama_lokasi','lokasi','Aset','Asetalat'));
    }

    public function exportPDF(){
        $konek = self::konek();
        $tanggal_awal = $_GET['tanggal_awal'];
        $tanggal_akhir = $_GET['tanggal_akhir'];
        $tipe = $_GET['jenis_report'];
        $status = $_GET['status'];
        $tipe_pemakaian = $_GET['tipe'];
        if(isset($_GET['ttd'])){
            $format_ttd = $_GET['ttd']; 
        }else{
            $format_ttd = 0;
        }

        $limit3 = Signature::on($konek)->where('jabatan','MANAGER OPERASIONAL')->first();
        if($limit3 == null){
            $limit3 = Signature::on($konek)->where('jabatan','DIREKTUR')->first();
        }
        
        $dt = Carbon\Carbon::now();
        $date=date_create($dt);
    
        $ttd = auth()->user()->name;
        $level = auth()->user()->level;
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

            if($request4 != 'SEMUA'){
                if ($tipe=='PDF' && $tipe_pemakaian == 'Alat' && $status != 'SEMUA'){
                    $asetalat = $_GET['assetalat'];
                    if ($asetalat == '' || $asetalat == null){
                        $message = [
                            'success' => false,
                            'title' => 'Gagal',
                            'message' => 'Harap pilih nomor aset dahulu.',
                        ];
                        return response()->json($message);
                    }else{
                        if ($asetalat != 'SEMUA'){
                            $pemakaianban = Pemakaianban::on($konek)->whereBetween('tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaianbandetail = PemakaianbanDetail::on($konek)
                                ->select('pemakaianban_detail.*','pemakaianban.*','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat')
                                ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                                ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('alat','pemakaianban.kode_alat', '=', 'alat.kode_alat')
                                ->where('pemakaianban.status', $status)
                                ->where('pemakaianban.no_asset_alat', $asetalat)
                                ->where('pemakaianban.kode_lokasi', $request4)
                                ->where('pemakaianban.type', $tipe_pemakaian)
                                ->whereBetween('pemakaianban.tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaianban.tanggal_pemakaianban','asc')
                                ->get();

                            $pdf = PDF::loadView('/admin/laporanpemakaianban/pdf_alat', compact('pemakaianban','pemakaianbandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','lokasi','asetalat','dt','format_ttd'));

                            $pdf->setPaper('a4', 'landscape');

                            return $pdf->stream('Laporan pemakaianban No Aset '.$asetalat.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }else if ($asetalat == 'SEMUA'){
                            $pemakaianban = Pemakaianban::on($konek)->whereBetween('tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))->get();

                            $pemakaianbandetail = PemakaianbanDetail::on($konek)
                                ->select('pemakaianban_detail.*','pemakaianban.*','produk.kode_kategori','produk.nama_produk','alat.nama_alat')
                                ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                                ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                                ->leftjoin('alat','pemakaianban.kode_alat', '=', 'alat.kode_alat')
                                ->where('pemakaianban.status', $status)
                                ->where('pemakaianban.kode_lokasi', $request4)
                                ->where('pemakaianban.type', $tipe_pemakaian)
                                ->whereBetween('pemakaianban.tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))
                                ->orderBy('pemakaianban.tanggal_pemakaianban','asc')
                                ->get();

                            $pdf = PDF::loadView('/admin/laporanpemakaianban/pdf_alat', compact('pemakaianban','pemakaianbandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','lokasi','asetalat','dt','format_ttd'));

                            $pdf->setPaper('a4', 'landscape');

                            return $pdf->stream('Laporan pemakaianban Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                        }
                    }
                }
                else if($tipe == 'PDF' && $tipe_pemakaian == 'Alat' && $status == 'SEMUA'){
                    $asetalat = $_GET['assetalat'];
                    if ($asetalat != 'SEMUA'){
                        $pemakaianban = Pemakaianban::on($konek)->whereBetween('tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaianbandetail = PemakaianbanDetail::on($konek)
                            ->select('pemakaianban_detail.*','pemakaianban.*','produk.kode_kategori','produk.nama_produk','alat.nama_alat')
                            ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                            ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('alat','pemakaianban.kode_alat', '=', 'alat.kode_alat')
                            ->where('pemakaianban.no_asset_alat', $asetalat)
                            ->where('pemakaianban.kode_lokasi', $request4)
                            ->where('pemakaianban.type', $tipe_pemakaian)
                            ->whereBetween('pemakaianban.tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaianban.tanggal_pemakaianban','asc')
                            ->get();
                        
                        $pdf = PDF::loadView('/admin/laporanpemakaianban/pdf_alat', compact('pemakaianban','pemakaianbandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','lokasi','asetalat','dt','format_ttd'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan pemakaianban No Aset '.$asetalat.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }else if ($asetalat == 'SEMUA'){
                        $pemakaianban = Pemakaianban::on($konek)->whereBetween('tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaianbandetail = PemakaianbanDetail::on($konek)
                            ->select('pemakaianban_detail.*','pemakaianban.*','produk.kode_kategori','produk.nama_produk','alat.nama_alat')
                            ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                            ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('alat','pemakaianban.kode_alat', '=', 'alat.kode_alat')
                            ->where('pemakaianban.kode_lokasi', $request4)
                            ->where('pemakaianban.type', $tipe_pemakaian)
                            ->whereBetween('pemakaianban.tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaianban.tanggal_pemakaianban','asc')
                            ->get();
                        
                        $pdf = PDF::loadView('/admin/laporanpemakaianban/pdf_alat', compact('pemakaianban','pemakaianbandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','lokasi','asetalat','dt','format_ttd'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan pemakaianban Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                }
                else if ($tipe=='PDF' && $tipe_pemakaian == 'Mobil' && $status != 'SEMUA'){
                    $aset = $_GET['asset'];
                    if ($aset != 'SEMUA'){
                        $pemakaianban = Pemakaianban::on($konek)->whereBetween('tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaianbandetail = PemakaianbanDetail::on($konek)
                            ->select('pemakaianban_detail.*','pemakaianban.*','produk.kode_kategori','produk.nama_produk','mobils.nopol')
                            ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                            ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils','pemakaianban.kode_mobil', '=', 'mobils.kode_mobil')
                            ->where('pemakaianban.status', $status)
                            ->where('pemakaianban.no_asset_mobil', $aset)
                            ->where('pemakaianban.kode_lokasi', $request4)
                            ->where('pemakaianban.type', $tipe_pemakaian)
                            ->whereBetween('pemakaianban.tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaianban.tanggal_pemakaianban','asc')
                            ->get();

                        $pdf = PDF::loadView('/admin/laporanpemakaianban/pdf_mobil', compact('pemakaianban','pemakaianbandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','lokasi','aset','dt','format_ttd'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan Pemakaianban No Aset '.$aset.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }else if ($aset == 'SEMUA'){
                        $pemakaianban = Pemakaianban::on($konek)->whereBetween('tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaianbandetail = PemakaianbanDetail::on($konek)
                            ->select('pemakaianban_detail.*','pemakaianban.*','produk.kode_kategori','produk.nama_produk','mobils.nopol')
                            ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                            ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils','pemakaianban.kode_mobil', '=', 'mobils.kode_mobil')
                            ->where('pemakaianban.status', $status)
                            ->where('pemakaianban.kode_lokasi', $request4)
                            ->where('pemakaianban.type', $tipe_pemakaian)
                            ->whereBetween('pemakaianban.tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaianban.tanggal_pemakaianban','asc')
                            ->get();

                        $pdf = PDF::loadView('/admin/laporanpemakaianban/pdf_mobil', compact('pemakaianban','pemakaianbandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','lokasi','aset','dt','format_ttd'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan pemakaianban Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                }
                else if($tipe == 'PDF' && $tipe_pemakaian == 'Mobil' && $status == 'SEMUA'){
                    $aset = $_GET['asset'];
                    if ($aset != 'SEMUA'){
                        $pemakaianban = Pemakaianban::on($konek)->whereBetween('tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaianbandetail = PemakaianbanDetail::on($konek)
                            ->select('pemakaianban_detail.*','pemakaianban.*','produk.kode_kategori','produk.nama_produk','mobils.nopol')
                            ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                            ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils','pemakaianban.kode_mobil', '=', 'mobils.kode_mobil')
                            ->where('pemakaianban.kode_lokasi', $request4)
                            ->where('pemakaianban.no_asset_mobil', $aset)
                            ->where('pemakaianban.type', $tipe_pemakaian)
                            ->whereBetween('pemakaianban.tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaianban.tanggal_pemakaianban','asc')
                            ->get();
                        
                        $pdf = PDF::loadView('/admin/laporanpemakaianban/pdf_mobil', compact('pemakaianban','pemakaianbandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','lokasi','aset','dt','format_ttd'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan Pemakaianban No Aset '.$aset.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }else if ($aset == 'SEMUA'){
                        $pemakaianban = Pemakaianban::on($konek)->whereBetween('tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaianbandetail = PemakaianbanDetail::on($konek)
                            ->select('pemakaianban_detail.*','pemakaianban.*','produk.kode_kategori','produk.nama_produk','mobils.nopol')
                            ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                            ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils','pemakaianban.kode_mobil', '=', 'mobils.kode_mobil')
                            ->where('pemakaianban.kode_lokasi', $request4)
                            ->where('pemakaianban.type', $tipe_pemakaian)
                            ->whereBetween('pemakaianban.tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaianban.tanggal_pemakaianban','asc')
                            ->get();
                        
                        $pdf = PDF::loadView('/admin/laporanpemakaianban/pdf_mobil', compact('pemakaianban','pemakaianbandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','lokasi','aset','dt','format_ttd'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan pemakaianban Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                        
                }
                else if ($tipe=='PDF' && $tipe_pemakaian == 'SEMUA' && $status != 'SEMUA'){
                        $pemakaianban = Pemakaianban::on($konek)->whereBetween('tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaianbandetail = PemakaianbanDetail::on($konek)
                            ->select('pemakaianban_detail.*','pemakaianban.*','produk.kode_kategori','produk.nama_produk','mobils.nopol','alat.nama_alat')
                            ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                            ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils','pemakaianban.kode_mobil', '=', 'mobils.kode_mobil')
                            ->leftjoin('alat','pemakaianban.kode_alat', '=', 'alat.kode_alat')
                            ->where('pemakaianban.status', $status)
                            ->where('pemakaianban.kode_lokasi', $request4)
                            ->whereBetween('pemakaianban.tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaianban.tanggal_pemakaianban','asc')
                            ->get();

                        $pdf = PDF::loadView('/admin/laporanpemakaianban/pdf', compact('pemakaianban','pemakaianbandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','lokasi','dt','format_ttd'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan pemakaianban Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                }
                else if($tipe == 'PDF' && $tipe_pemakaian == 'SEMUA' && $status == 'SEMUA'){
                        $pemakaianban = Pemakaianban::on($konek)->whereBetween('tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaianbandetail = PemakaianbanDetail::on($konek)
                            ->select('pemakaianban_detail.*','pemakaianban.*','produk.kode_kategori','produk.nama_produk','mobils.nopol','alat.nama_alat')
                            ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                            ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils','pemakaianban.kode_mobil', '=', 'mobils.kode_mobil')
                            ->leftjoin('alat','pemakaianban.kode_alat', '=', 'alat.kode_alat')
                            ->where('pemakaianban.kode_lokasi', $request4)
                            ->whereBetween('pemakaianban.tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaianban.tanggal_pemakaianban','asc')
                            ->get();
                        // dd($pemakaianbandetail);
                        
                        $pdf = PDF::loadView('/admin/laporanpemakaianban/pdf', compact('pemakaianban','pemakaianbandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','lokasi','dt','format_ttd'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan pemakaianban Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                }

                else if ($tipe == 'excel' && $tipe_pemakaian == 'Alat'){
                    $request4 = $_GET['lokasi'];
                    $aset = 'none';
                    $asetalat = $_GET['assetalat'];
                    return Excel::download(new pemakaianbanExport($tanggal_awal, $tanggal_akhir, $status, $tipe_pemakaian, $lokasi, $aset, $asetalat), 'Laporan Pemakaianban no aset alat '.$asetalat.' dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                }

                else if ($tipe == 'excel' && $tipe_pemakaian == 'Mobil'){
                    $request4 = $_GET['lokasi'];
                    $aset = $_GET['asset'];
                    $asetalat = 'none';
                    return Excel::download(new pemakaianbanExport($tanggal_awal, $tanggal_akhir, $status, $tipe_pemakaian, $lokasi, $aset, $asetalat), 'Laporan Pemakaianban no aset mobil '.$aset.' dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                }

                else if ($tipe == 'excel'){
                    $request4 = $get_lokasi;
                    $aset = 'none';
                    $asetalat = 'none';
                    return Excel::download(new pemakaianbanExport($tanggal_awal, $tanggal_akhir, $status, $tipe_pemakaian, $lokasi, $aset, $asetalat), 'Laporan pemakaianban dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                }
            }
            else{
                if ($tipe=='PDF' && $tipe_pemakaian == 'Alat' && $status != 'SEMUA'){
                    $asetalat = $_GET['assetalat'];
                    if ($asetalat != 'SEMUA'){
                        $pemakaianban = Pemakaianban::on($konek)->whereBetween('tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaianbandetail = PemakaianbanDetail::on($konek)
                            ->select('pemakaianban_detail.*','pemakaianban.*','produk.kode_kategori','produk.nama_produk','alat.nama_alat')
                            ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                            ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('alat','pemakaianban.kode_alat', '=', 'alat.kode_alat')
                            ->where('pemakaianban.status', $status)
                            ->where('pemakaianban.no_asset_alat', $asetalat)
                            ->where('pemakaianban.type', $tipe_pemakaian)
                            ->whereBetween('pemakaianban.tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaianban.tanggal_pemakaianban','asc')
                            ->get();

                        $pdf = PDF::loadView('/admin/laporanpemakaianban/pdf_alat', compact('pemakaianban','pemakaianbandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','lokasi','asetalat','dt','format_ttd'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan Pemakaianban No Aset '.$asetalat.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                    else if ($asetalat == 'SEMUA'){
                        $pemakaianban = Pemakaianban::on($konek)->whereBetween('tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaianbandetail = PemakaianbanDetail::on($konek)
                            ->select('pemakaianban_detail.*','pemakaianban.*','produk.kode_kategori','produk.nama_produk','alat.nama_alat')
                            ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                            ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('alat','pemakaianban.kode_alat', '=', 'alat.kode_alat')
                            ->where('pemakaianban.status', $status)
                            ->where('pemakaianban.type', $tipe_pemakaian)
                            ->whereBetween('pemakaianban.tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaianban.tanggal_pemakaianban','asc')
                            ->get();

                        $pdf = PDF::loadView('/admin/laporanpemakaianban/pdf_alat', compact('pemakaianban','pemakaianbandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','lokasi','asetalat','dt','format_ttd'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan pemakaianban Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                }
                else if($tipe == 'PDF' && $tipe_pemakaian == 'Alat' && $status == 'SEMUA'){
                    $asetalat = $_GET['assetalat'];
                    if ($asetalat != 'SEMUA'){
                        $pemakaianban = Pemakaianban::on($konek)->whereBetween('tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaianbandetail = PemakaianbanDetail::on($konek)
                            ->select('pemakaianban_detail.*','pemakaianban.*','produk.kode_kategori','produk.nama_produk','alat.nama_alat')
                            ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                            ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('alat','pemakaianban.kode_alat', '=', 'alat.kode_alat')
                            ->where('pemakaianban.no_asset_alat', $asetalat)
                            ->where('pemakaianban.type', $tipe_pemakaian)
                            ->whereBetween('pemakaianban.tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaianban.tanggal_pemakaianban','asc')
                            ->get();
                        
                        $pdf = PDF::loadView('/admin/laporanpemakaianban/pdf_alat', compact('pemakaianban','pemakaianbandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','lokasi','asetalat','dt','format_ttd'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan Pemakaianban No Aset '.$asetalat.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                    else if ($asetalat == 'SEMUA'){
                        $pemakaianban = Pemakaianban::on($konek)->whereBetween('tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaianbandetail = PemakaianbanDetail::on($konek)
                            ->select('pemakaianban_detail.*','pemakaianban.*','produk.kode_kategori','produk.nama_produk','alat.nama_alat')
                            ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                            ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('alat','pemakaianban.kode_alat', '=', 'alat.kode_alat')
                            ->where('pemakaianban.type', $tipe_pemakaian)
                            ->whereBetween('pemakaianban.tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaianban.tanggal_pemakaianban','asc')
                            ->get();
                        
                        $pdf = PDF::loadView('/admin/laporanpemakaianban/pdf_alat', compact('pemakaianban','pemakaianbandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','lokasi','asetalat','dt','format_ttd'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan pemakaianban Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                        
                }
                else if ($tipe=='PDF' && $tipe_pemakaian == 'Mobil' && $status != 'SEMUA'){
                    $aset = $_GET['asset'];
                    if ($aset != 'SEMUA'){
                        $pemakaianban = Pemakaianban::on($konek)->whereBetween('tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaianbandetail = PemakaianbanDetail::on($konek)
                            ->select('pemakaianban_detail.*','pemakaianban.*','produk.kode_kategori','produk.nama_produk','mobils.nopol')
                            ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                            ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils','pemakaianban.kode_mobil', '=', 'mobils.kode_mobil')
                            ->where('pemakaianban.no_asset_mobil', $aset)
                            ->where('pemakaianban.status', $status)
                            ->where('pemakaianban.type', $tipe_pemakaian)
                            ->whereBetween('pemakaianban.tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaianban.tanggal_pemakaianban','asc')
                            ->get();

                        $pdf = PDF::loadView('/admin/laporanpemakaianban/pdf_mobil', compact('pemakaianban','pemakaianbandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','lokasi','aset','dt','format_ttd'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan Pemakaianban No Aset '.$aset.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                    else if ($aset == 'SEMUA'){
                        $pemakaianban = Pemakaianban::on($konek)->whereBetween('tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaianbandetail = PemakaianbanDetail::on($konek)
                            ->select('pemakaianban_detail.*','pemakaianban.*','produk.kode_kategori','produk.nama_produk','mobils.nopol')
                            ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                            ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils','pemakaianban.kode_mobil', '=', 'mobils.kode_mobil')
                            ->where('pemakaianban.status', $status)
                            ->where('pemakaianban.type', $tipe_pemakaian)
                            ->whereBetween('pemakaianban.tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaianban.tanggal_pemakaianban','asc')
                            ->get();

                        $pdf = PDF::loadView('/admin/laporanpemakaianban/pdf_mobil', compact('pemakaianban','pemakaianbandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','lokasi','aset','dt','format_ttd'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan pemakaianban Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                }
                else if($tipe == 'PDF' && $tipe_pemakaian == 'Mobil' && $status == 'SEMUA'){
                    $aset = $_GET['asset'];
                    if ($aset != 'SEMUA'){
                        $pemakaianban = Pemakaianban::on($konek)->whereBetween('tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaianbandetail = PemakaianbanDetail::on($konek)
                            ->select('pemakaianban_detail.*','pemakaianban.*','produk.kode_kategori','produk.nama_produk','mobils.nopol')
                            ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                            ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils','pemakaianban.kode_mobil', '=', 'mobils.kode_mobil')
                            ->where('pemakaianban.no_asset_mobil', $aset)
                            ->where('pemakaianban.type', $tipe_pemakaian)
                            ->whereBetween('pemakaianban.tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaianban.tanggal_pemakaianban','asc')
                            ->get();
                        // dd($pemakaianbandetail);
                        
                        $pdf = PDF::loadView('/admin/laporanpemakaianban/pdf_mobil', compact('pemakaianban','pemakaianbandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','lokasi','aset','dt','format_ttd'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan Pemakaianban No Aset '.$aset.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                    else if ($aset == 'SEMUA'){
                        $pemakaianban = Pemakaianban::on($konek)->whereBetween('tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaianbandetail = PemakaianbanDetail::on($konek)
                            ->select('pemakaianban_detail.*','pemakaianban.*','produk.kode_kategori','produk.nama_produk','mobils.nopol')
                            ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                            ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils','pemakaianban.kode_mobil', '=', 'mobils.kode_mobil')
                            ->where('pemakaianban.type', $tipe_pemakaian)
                            ->whereBetween('pemakaianban.tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaianban.tanggal_pemakaianban','asc')
                            ->get();
                        // dd($pemakaianbandetail);
                        
                        $pdf = PDF::loadView('/admin/laporanpemakaianban/pdf_mobil', compact('pemakaianban','pemakaianbandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','lokasi','aset','dt','format_ttd'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan pemakaianban Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    } 
                }
                else if ($tipe=='PDF' && $tipe_pemakaian == 'SEMUA' && $status != 'SEMUA'){
                        $pemakaianban = Pemakaianban::on($konek)->whereBetween('tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaianbandetail = PemakaianbanDetail::on($konek)
                            ->select('pemakaianban_detail.*','pemakaianban.*','produk.kode_kategori','produk.nama_produk','mobils.nopol','alat.nama_alat')
                            ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                            ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils','pemakaianban.kode_mobil', '=', 'mobils.kode_mobil')
                            ->leftjoin('alat','pemakaianban.kode_alat', '=', 'alat.kode_alat')
                            ->where('pemakaianban.status', $status)
                            ->whereBetween('pemakaianban.tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaianban.tanggal_pemakaianban','asc')
                            ->get();

                        $pdf = PDF::loadView('/admin/laporanpemakaianban/pdf', compact('pemakaianban','pemakaianbandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','lokasi','dt','format_ttd'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan pemakaianban Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                }
                else if($tipe == 'PDF' && $tipe_pemakaian == 'SEMUA' && $status == 'SEMUA'){
                        $pemakaianban = Pemakaianban::on($konek)->whereBetween('tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))->get();

                        $pemakaianbandetail = PemakaianbanDetail::on($konek)
                            ->select('pemakaianban_detail.*','pemakaianban.*','produk.kode_kategori','produk.nama_produk','mobils.nopol','alat.nama_alat')
                            ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                            ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils','pemakaianban.kode_mobil', '=', 'mobils.kode_mobil')
                            ->leftjoin('alat','pemakaianban.kode_alat', '=', 'alat.kode_alat')
                            ->whereBetween('pemakaianban.tanggal_pemakaianban', array($tanggal_awal, $tanggal_akhir))
                            ->orderBy('pemakaianban.tanggal_pemakaianban','asc')
                            ->get();
                        // dd($pemakaianbandetail);
                        
                        $pdf = PDF::loadView('/admin/laporanpemakaianban/pdf', compact('pemakaianban','pemakaianbandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','lokasi','dt','format_ttd'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan pemakaianban Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                }

                else if ($tipe == 'excel' && $tipe_pemakaian == 'Alat'){
                    $request4 = $_GET['lokasi'];
                    $aset = 'none';
                    $asetalat = $_GET['assetalat'];
                    return Excel::download(new pemakaianbanExport($tanggal_awal, $tanggal_akhir, $status, $tipe_pemakaian, $lokasi, $aset, $asetalat), 'Laporan Pemakaianban no aset alat '.$asetalat.' dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                }

                else if ($tipe == 'excel' && $tipe_pemakaian == 'Mobil'){
                    $request4 = $_GET['lokasi'];
                    $aset = $_GET['asset'];
                    $asetalat = 'none';
                    return Excel::download(new pemakaianbanExport($tanggal_awal, $tanggal_akhir, $status, $tipe_pemakaian, $lokasi, $aset, $asetalat), 'Laporan Pemakaianban no aset mobil '.$aset.' dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                }

                else if ($tipe == 'excel'){
                    $request4 = $_GET['lokasi'];
                    $aset = 'none';
                    $asetalat = 'none';
                    return Excel::download(new pemakaianbanExport($tanggal_awal, $tanggal_akhir, $status, $tipe_pemakaian, $lokasi, $aset, $asetalat), 'Laporan pemakaianban dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                }
            }
        
    }
}
