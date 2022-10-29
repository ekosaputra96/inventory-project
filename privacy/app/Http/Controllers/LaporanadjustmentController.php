<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\tb_akhir_bulan;
use App\Models\LaporanPemakaian;
use App\Models\LaporanAdjustment;
use App\Models\tb_item_bulanan;
use App\Models\tb_produk_history;
use App\Models\Produk;
use App\Models\Mobil;
use App\Models\Adjustment;
use App\Models\KategoriProduk;
use App\Models\AdjustmentDetail;
use App\Models\MasterLokasi;
use App\Models\Company;
use App\Models\Signature;
use App\Exports\AdjustmentExport;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use DB;
use Carbon;

class LaporanadjustmentController extends Controller
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
        $create_url = route('laporanadjustment.create');
        $no_penyesuaian = Adjustment::on($konek)->pluck('no_penyesuaian','no_penyesuaian');
        $Produk = Produk::on($konek)->pluck('nama_produk', 'id');
        $kategori = KategoriProduk::select('kode_kategori', DB::raw("concat(kode_kategori,' - ',nama_kategori) as kategori"))->pluck('kategori','kode_kategori');
        $lokasi = tb_item_bulanan::on($konek)->pluck('kode_lokasi', 'kode_lokasi');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        $lokasi_user = auth()->user()->kode_lokasi;
        
        return view('admin.laporanadjustment.index',compact('create_url','Produk','no_penyesuaian','period','kategori', 'nama_lokasi','lokasi'));
    }

    public function exportPDF(){
        $konek = self::konek();
        $tanggal_awal = $_GET['tanggal_awal'];
        $tanggal_akhir = $_GET['tanggal_akhir'];
        $tipe = $_GET['jenis_report'];
        $status = $_GET['status'];
        $kategori = $_GET['kategori'];
        if(isset($_GET['ttd'])){
            $format_ttd = $_GET['ttd']; 
        }else{
            $format_ttd = 0;
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

        $nama_lokasi = MasterLokasi::find($get_lokasi);
        $nama = $nama_lokasi->nama_lokasi;

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

        if (auth()->user()->kode_lokasi == 'HO'){
            $request4 = $_GET['lokasi'];
            $lokasi2 = $_GET['lokasi'];
        }else {
            $lokasi2 = auth()->user()->kode_lokasi;
            $request4 = $lokasi2;
        }

        if($lokasi2 != 'SEMUA'){
            if($level != 'hse'){
                if ($tipe == 'PDF' && $status != 'SEMUA'){
                    if($kategori != 'SEMUA'){
                        $adjustment = Adjustment::on($konek)->whereBetween('tanggal', array($tanggal_awal, $tanggal_akhir))->get();

                        $adjustmentdetail = AdjustmentDetail::on($konek)
                        ->select('adjustments_detail.*','adjustments.tanggal','adjustments.status','adjustments.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                        ->join('adjustments', 'adjustment_details.no_penyesuaian', '=', 'adjustments.no_penyesuaian')
                        ->join('produk','adjustments_detail.kode_produk', '=', 'produk.id')
                        ->where('adjustments.status', $status)
                        ->where('produk.kode_kategori', $kategori)
                        ->where('adjustments.kode_lokasi', $request4)
                        ->whereBetween('adjustments.tanggal', array($tanggal_awal, $tanggal_akhir))
                        ->orderBy('adjustments.tanggal','asc')
                        ->get();
            
                        $pdf = PDF::loadView('/admin/laporanadjustment/pdf', compact('adjustments','adjustmentdetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','kategori','dt','format_ttd','lokasi2'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan adjustment Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                    else if($kategori == 'SEMUA'){
                        $adjustment = Adjustment::on($konek)->whereBetween('tanggal', array($tanggal_awal, $tanggal_akhir))->get();

                        $adjustmentdetail = AdjustmentDetail::on($konek)
                        ->select('adjustments_detail.*','adjustments.tanggal','adjustments.status','adjustments.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                        ->join('adjustments', 'adjustments_detail.no_penyesuaian', '=', 'adjustments.no_penyesuaian')
                        ->join('produk','adjustments_detail.kode_produk', '=', 'produk.id')
                        ->where('adjustments.status', $status)
                        ->where('adjustments.kode_lokasi', $request4)
                        ->whereBetween('adjustments.tanggal', array($tanggal_awal, $tanggal_akhir))
                        ->orderBy('adjustments.tanggal','asc')
                        ->get();
                        
                        $pdf = PDF::loadView('/admin/laporanadjustment/pdf', compact('adjustments','adjustmentdetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','kategori','dt','format_ttd','lokasi2'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan adjustment Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }

                }
                else if($tipe == 'PDF' && $status == 'SEMUA'){
                    if($kategori != 'SEMUA'){
                        $adjustment = Adjustment::on($konek)->whereBetween('tanggal', array($tanggal_awal, $tanggal_akhir))->get();

                        $adjustmentdetail = AdjustmentDetail::on($konek)
                        ->select('adjustments_detail.*','adjustments.tanggal','adjustments.status','adjustments.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                        ->join('adjustments', 'adjustments_detail.no_penyesuaian', '=', 'adjustments.no_penyesuaian')
                        ->join('produk','adjustments_detail.kode_produk', '=', 'produk.id')
                        ->where('produk.kode_kategori', $kategori)
                        ->where('adjustments.kode_lokasi', $request4)
                        ->whereBetween('adjustments.tanggal', array($tanggal_awal, $tanggal_akhir))
                        ->orderBy('adjustments.tanggal','asc')
                        ->get();
            
                        $pdf = PDF::loadView('/admin/laporanadjustment/pdf', compact('adjustment','adjustmentdetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','kategori','dt','format_ttd','lokasi2'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan adjustment Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                    else if($kategori == 'SEMUA'){
                        $adjustment = Adjustment::on($konek)->whereBetween('tanggal', array($tanggal_awal, $tanggal_akhir))->get();

                        $adjustmentdetail = AdjustmentDetail::on($konek)
                        ->select('adjustments_detail.*','adjustments.tanggal','adjustments.status','adjustments.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                        ->join('adjustments', 'adjustments_detail.no_penyesuaian', '=', 'adjustments.no_penyesuaian')
                        ->join('produk','adjustments_detail.kode_produk', '=', 'produk.id')
                        ->where('adjustments.kode_lokasi', $request4)
                        ->whereBetween('adjustments.tanggal', array($tanggal_awal, $tanggal_akhir))
                        ->orderBy('adjustments.tanggal','asc')
                        ->get();
                        // dd($adjustmentdetail);
            
                        $pdf = PDF::loadView('/admin/laporanadjustment/pdf', compact('adjustment','adjustmentdetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','kategori','dt','format_ttd','lokasi2'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan adjustment Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                } 
                else if ($tipe == 'excel'){
                    return Excel::download(new AdjustmentExport($tanggal_awal, $tanggal_akhir, $status, $kategori, $request4, $lokasi2), 'Laporan adjustment dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                }
            }
            else{
                if ($tipe == 'PDF' && $status != 'SEMUA'){
                    if($kategori != 'SEMUA'){
                        $adjustment = Adjustment::on($konek)->whereBetween('tanggal', array($tanggal_awal, $tanggal_akhir))->get();

                        $adjustmentdetail = AdjustmentDetail::on($konek)
                        ->select('adjustments_detail.*','adjustments.tanggal','adjustments.status','adjustments.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                        ->join('adjustments', 'adjustments_detail.no_penyesuaian', '=', 'adjustments.no_penyesuaian')
                        ->join('produk','adjustments_detail.kode_produk', '=', 'produk.id')
                        ->where('adjustments.status', $status)
                        ->where('produk.kode_kategori', $kategori)
                        ->where('adjustments.kode_lokasi', $request4)
                        ->whereBetween('adjustments.tanggal', array($tanggal_awal, $tanggal_akhir))
                        ->orderBy('adjustments.tanggal','asc')
                        ->get();
            
                        $pdf = PDF::loadView('/admin/laporanadjustment/pdfhse', compact('adjustment','adjustmentdetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','limithse','nama','nama2','kategori','dt','format_ttd','lokasi2'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan adjustment Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                    else if($kategori == 'SEMUA'){
                        $adjustment = Adjustment::on($konek)->whereBetween('tanggal', array($tanggal_awal, $tanggal_akhir))->get();

                        $adjustmentdetail = AdjustmentDetail::on($konek)
                        ->select('adjustments_detail.*','adjustments.tanggal','adjustments.status','adjustments.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                        ->join('adjustments', 'adjustments_detail.no_penyesuaian', '=', 'adjustments.no_penyesuaian')
                        ->join('produk','adjustments_detail.kode_produk', '=', 'produk.id')
                        ->where('adjustments.status', $status)
                        ->where('adjustments.kode_lokasi', $request4)
                        ->whereBetween('adjustments.tanggal', array($tanggal_awal, $tanggal_akhir))
                        ->orderBy('adjustments.tanggal','asc')
                        ->get();
                        
                        $pdf = PDF::loadView('/admin/laporanadjustment/pdfhse', compact('adjustment','adjustmentdetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','limithse','nama','nama2','kategori','dt','format_ttd','lokasi2'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan adjustment Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }

                }
                else if($tipe == 'PDF' && $status == 'SEMUA'){
                    if($kategori != 'SEMUA'){
                        $adjustment = Adjustment::on($konek)->whereBetween('tanggal', array($tanggal_awal, $tanggal_akhir))->get();

                        $adjustmentdetail = AdjustmentDetail::on($konek)
                        ->select('adjustments_detail.*','adjustments.tanggal','adjustments.status','adjustments.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                        ->join('adjustments', 'adjustments_detail.no_penyesuaian', '=', 'adjustments.no_penyesuaian')
                        ->join('produk','adjustments_detail.kode_produk', '=', 'produk.id')
                        ->where('produk.kode_kategori', $kategori)
                        ->where('adjustments.kode_lokasi', $request4)
                        ->whereBetween('adjustments.tanggal', array($tanggal_awal, $tanggal_akhir))
                        ->orderBy('adjustments.tanggal','asc')
                        ->get();
            
                        $pdf = PDF::loadView('/admin/laporanadjustment/pdfhse', compact('adjustment','adjustmentdetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','limithse','nama','nama2','kategori','dt','format_ttd','lokasi2'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan adjustment Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                    else if($kategori == 'SEMUA'){
                        $adjustment = Adjustment::on($konek)->whereBetween('tanggal', array($tanggal_awal, $tanggal_akhir))->get();

                        $adjustmentdetail = AdjustmentDetail::on($konek)
                        ->select('adjustments_detail.*','adjustments.tanggal','adjustments.status','adjustments.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                        ->join('adjustments', 'adjustments_detail.no_penyesuaian', '=', 'adjustments.no_penyesuaian')
                        ->join('produk','adjustments_detail.kode_produk', '=', 'produk.id')
                        ->where('adjustments.kode_lokasi', $request4)
                        ->whereBetween('adjustments.tanggal', array($tanggal_awal, $tanggal_akhir))
                        ->orderBy('adjustments.tanggal','asc')
                        ->get();
                        // dd($adjustmentdetail);
            
                        $pdf = PDF::loadView('/admin/laporanadjustment/pdfhse', compact('adjustment','adjustmentdetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','limithse','nama','nama2','kategori','dt','format_ttd','lokasi2'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan adjustment Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                } 
                else if ($tipe == 'excel'){
                    return Excel::download(new AdjustmentExport($tanggal_awal, $tanggal_akhir, $status, $kategori, $request4), 'Laporan adjustment dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                }
            }
        }
        else{
            if($level != 'hse'){
                if ($tipe == 'PDF' && $status != 'SEMUA'){
                    if($kategori != 'SEMUA'){
                        $adjustment = Adjustment::on($konek)->whereBetween('tanggal', array($tanggal_awal, $tanggal_akhir))->get();

                        $adjustmentdetail = AdjustmentDetail::on($konek)
                        ->select('adjustments_detail.*','adjustments.tanggal','adjustments.status','adjustments.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                        ->join('adjustments', 'adjustment_details.no_penyesuaian', '=', 'adjustments.no_penyesuaian')
                        ->join('produk','adjustments_detail.kode_produk', '=', 'produk.id')
                        ->where('adjustments.status', $status)
                        ->where('produk.kode_kategori', $kategori)
                        ->whereBetween('adjustments.tanggal', array($tanggal_awal, $tanggal_akhir))
                        ->orderBy('adjustments.tanggal','asc')
                        ->get();
            
                        $pdf = PDF::loadView('/admin/laporanadjustment/pdf', compact('adjustment','adjustmentdetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','kategori','dt','format_ttd','lokasi2'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan adjustment Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                    else if($kategori == 'SEMUA'){
                        $adjustment = Adjustment::on($konek)->whereBetween('tanggal', array($tanggal_awal, $tanggal_akhir))->get();

                        $adjustmentdetail = AdjustmentDetail::on($konek)
                        ->select('adjustments_detail.*','adjustments.tanggal','adjustments.status','adjustments.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                        ->join('adjustments', 'adjustments_detail.no_penyesuaian', '=', 'adjustments.no_penyesuaian')
                        ->join('produk','adjustments_detail.kode_produk', '=', 'produk.id')
                        ->where('adjustments.status', $status)
                        ->whereBetween('adjustments.tanggal', array($tanggal_awal, $tanggal_akhir))
                        ->orderBy('adjustments.tanggal','asc')
                        ->get();
                        
                        $pdf = PDF::loadView('/admin/laporanadjustment/pdf', compact('adjustment','adjustmentdetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','kategori','dt','format_ttd','lokasi2'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan adjustment Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }

                }
                else if($tipe == 'PDF' && $status == 'SEMUA'){
                    if($kategori != 'SEMUA'){
                        $adjustment = Adjustment::on($konek)->whereBetween('tanggal', array($tanggal_awal, $tanggal_akhir))->get();

                        $adjustmentdetail = AdjustmentDetail::on($konek)
                        ->select('adjustments_detail.*','adjustments.tanggal','adjustments.status','adjustments.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                        ->join('adjustments', 'adjustments_detail.no_penyesuaian', '=', 'adjustments.no_penyesuaian')
                        ->join('produk','adjustments_detail.kode_produk', '=', 'produk.id')
                        ->where('produk.kode_kategori', $kategori)
                        ->whereBetween('adjustments.tanggal', array($tanggal_awal, $tanggal_akhir))
                        ->orderBy('adjustments.tanggal','asc')
                        ->get();
            
                        $pdf = PDF::loadView('/admin/laporanadjustment/pdf', compact('adjustment','adjustmentdetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','kategori','dt','format_ttd','lokasi2'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan adjustment Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                    else if($kategori == 'SEMUA'){
                        $adjustment = Adjustment::on($konek)->whereBetween('tanggal', array($tanggal_awal, $tanggal_akhir))->get();

                        $adjustmentdetail = AdjustmentDetail::on($konek)
                        ->select('adjustments_detail.*','adjustments.tanggal','adjustments.status','adjustments.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                        ->join('adjustments', 'adjustments_detail.no_penyesuaian', '=', 'adjustments.no_penyesuaian')
                        ->join('produk','adjustments_detail.kode_produk', '=', 'produk.id')
                        ->whereBetween('adjustments.tanggal', array($tanggal_awal, $tanggal_akhir))
                        ->orderBy('adjustments.tanggal','asc')
                        ->get();
                        // dd($adjustmentdetail);
            
                        $pdf = PDF::loadView('/admin/laporanadjustment/pdf', compact('adjustment','adjustmentdetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','kategori','dt','format_ttd','lokasi2'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan adjustment Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                } 
                else if ($tipe == 'excel'){
                    return Excel::download(new AdjustmentExport($tanggal_awal, $tanggal_akhir, $status, $kategori, $get_lokasi), 'Laporan adjustment dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                }
            }
            else{
                if ($tipe == 'PDF' && $status != 'SEMUA'){
                    if($kategori != 'SEMUA'){
                        $adjustment = Adjustment::on($konek)->whereBetween('tanggal', array($tanggal_awal, $tanggal_akhir))->get();

                        $adjustmentdetail = AdjustmentDetail::on($konek)
                        ->select('adjustments_detail.*','adjustments.tanggal','adjustments.status','adjustments.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                        ->join('adjustments', 'adjustments_detail.no_penyesuaian', '=', 'adjustments.no_penyesuaian')
                        ->join('produk','adjustments_detail.kode_produk', '=', 'produk.id')
                        ->where('adjustments.status', $status)
                        ->where('produk.kode_kategori', $kategori)
                        ->whereBetween('adjustments.tanggal', array($tanggal_awal, $tanggal_akhir))
                        ->orderBy('adjustments.tanggal','asc')
                        ->get();
            
                        $pdf = PDF::loadView('/admin/laporanadjustment/pdfhse', compact('adjustment','adjustmentdetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','limithse','nama','nama2','kategori','dt','format_ttd','lokasi2'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan adjustment Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                    else if($kategori == 'SEMUA'){
                        $adjustment = Adjustment::on($konek)->whereBetween('tanggal', array($tanggal_awal, $tanggal_akhir))->get();

                        $adjustmentdetail = AdjustmentDetail::on($konek)
                        ->select('adjustments_detail.*','adjustments.tanggal','adjustments.status','adjustments.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                        ->join('adjustments', 'adjustments_detail.no_penyesuaian', '=', 'adjustments.no_penyesuaian')
                        ->join('produk','adjustments_detail.kode_produk', '=', 'produk.id')
                        ->where('adjustments.status', $status)
                        ->whereBetween('adjustments.tanggal', array($tanggal_awal, $tanggal_akhir))
                        ->orderBy('adjustments.tanggal','asc')
                        ->get();
                        
                        $pdf = PDF::loadView('/admin/laporanadjustment/pdfhse', compact('adjustment','adjustmentdetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','limithse','nama','nama2','kategori','dt','format_ttd','lokasi2'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan adjustment Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }

                }
                else if($tipe == 'PDF' && $status == 'SEMUA'){
                    if($kategori != 'SEMUA'){
                        $adjustment = Adjustment::on($konek)->whereBetween('tanggal', array($tanggal_awal, $tanggal_akhir))->get();

                        $adjustmentdetail = AdjustmentDetail::on($konek)
                        ->select('adjustments_detail.*','adjustments.tanggal','adjustments.status','adjustments.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                        ->join('adjustments', 'adjustments_detail.no_penyesuaian', '=', 'adjustments.no_penyesuaian')
                        ->join('produk','adjustments_detail.kode_produk', '=', 'produk.id')
                        ->where('produk.kode_kategori', $kategori)
                        ->whereBetween('adjustments.tanggal', array($tanggal_awal, $tanggal_akhir))
                        ->orderBy('adjustments.tanggal','asc')
                        ->get();
            
                        $pdf = PDF::loadView('/admin/laporanadjustment/pdfhse', compact('adjustment','adjustmentdetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','limithse','nama','nama2','kategori','dt','format_ttd','lokasi2'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan adjustment Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                    else if($kategori == 'SEMUA'){
                        $adjustment = Adjustment::on($konek)->whereBetween('tanggal', array($tanggal_awal, $tanggal_akhir))->get();

                        $adjustmentdetail = AdjustmentDetail::on($konek)
                        ->select('adjustments_detail.*','adjustments.tanggal','adjustments.status','adjustments.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                        ->join('adjustments', 'adjustments_detail.no_penyesuaian', '=', 'adjustments.no_penyesuaian')
                        ->join('produk','adjustments_detail.kode_produk', '=', 'produk.id')
                        ->whereBetween('adjustments.tanggal', array($tanggal_awal, $tanggal_akhir))
                        ->orderBy('adjustments.tanggal','asc')
                        ->get();
                        // dd($adjustmentdetail);
            
                        $pdf = PDF::loadView('/admin/laporanadjustment/pdfhse', compact('adjustment','adjustmentdetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','limithse','nama','nama2','kategori','dt','format_ttd','lokasi2'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan adjustment Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                } 
                else if ($tipe == 'excel'){
                    return Excel::download(new AdjustmentExport($tanggal_awal, $tanggal_akhir, $status, $kategori, $get_lokasi), 'Laporan adjustment dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                }
            }
        }

    }
}
