<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\tb_akhir_bulan;
use App\Models\LaporanPemakaian;
use App\Models\LaporanPenerimaan;
use App\Models\tb_item_bulanan;
use App\Models\tb_produk_history;
use App\Models\Produk;
use App\Models\Mobil;
use App\Models\Penerimaan;
use App\Models\KategoriProduk;
use App\Models\PenerimaanDetail;
use App\Models\MasterLokasi;
use App\Models\Company;
use App\Models\Signature;
use App\Exports\PenerimaanExport;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use DB;
use Carbon;

class LaporanpenerimaanController extends Controller
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
        $create_url = route('laporanpenerimaan.create');
        $no_penerimaan = Penerimaan::on($konek)->pluck('no_penerimaan','no_penerimaan');
        $Produk = Produk::on($konek)->pluck('nama_produk', 'id');
        $kategori = KategoriProduk::select('kode_kategori', DB::raw("concat(kode_kategori,' - ',nama_kategori) as kategori"))->pluck('kategori','kode_kategori');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        return view('admin.laporanpenerimaan.index',compact('create_url','Produk','no_penerimaan','period','kategori', 'nama_lokasi'));
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

        
            if ($tipe == 'PDF' && $status != 'SEMUA'){
                if($status == 'RETUR'){
                    if($kategori != 'SEMUA'){
                        $penerimaan = Penerimaan::on($konek)->whereBetween('tanggal_penerimaan', array($tanggal_awal, $tanggal_akhir))->get();

                        $penerimaandetail = PenerimaanDetail::on($konek)
                        ->select('penerimaan_detail.*','penerimaan.tanggal_penerimaan','penerimaan.status','penerimaan.no_pembelian','penerimaan.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                        ->join('penerimaan', 'penerimaan_detail.no_penerimaan', '=', 'penerimaan.no_penerimaan')
                        ->join('produk','penerimaan_detail.kode_produk', '=', 'produk.id')
                        ->where('penerimaan_detail.qty_retur','>',0)
                        ->where('produk.kode_kategori', $kategori)
                        ->where('penerimaan.kode_lokasi', $get_lokasi)
                        ->whereBetween('penerimaan.tanggal_penerimaan', array($tanggal_awal, $tanggal_akhir))
                        ->orderBy('penerimaan.tanggal_penerimaan','asc')
                        ->get();
            
                        $pdf = PDF::loadView('/admin/laporanpenerimaan/pdf', compact('penerimaan','penerimaandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','dt','format_ttd'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan Penerimaan Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                    else if($kategori == 'SEMUA'){
                        $penerimaan = Penerimaan::on($konek)->whereBetween('tanggal_penerimaan', array($tanggal_awal, $tanggal_akhir))->get();

                        $penerimaandetail = PenerimaanDetail::on($konek)
                        ->select('penerimaan_detail.*','penerimaan.tanggal_penerimaan','penerimaan.status','penerimaan.no_pembelian','penerimaan.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                        ->join('penerimaan', 'penerimaan_detail.no_penerimaan', '=', 'penerimaan.no_penerimaan')
                        ->join('produk','penerimaan_detail.kode_produk', '=', 'produk.id')
                        ->where('penerimaan_detail.qty_retur','>',0)
                        ->where('penerimaan.kode_lokasi', $get_lokasi)
                        ->whereBetween('penerimaan.tanggal_penerimaan', array($tanggal_awal, $tanggal_akhir))
                        ->orderBy('penerimaan.tanggal_penerimaan','asc')
                        ->get();
                        
                        $pdf = PDF::loadView('/admin/laporanpenerimaan/pdf', compact('penerimaan','penerimaandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','dt','format_ttd'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan Penerimaan Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                }
                else{
                    if($kategori != 'SEMUA'){
                        $penerimaan = Penerimaan::on($konek)->whereBetween('tanggal_penerimaan', array($tanggal_awal, $tanggal_akhir))->get();

                        $penerimaandetail = PenerimaanDetail::on($konek)
                        ->select('penerimaan_detail.*','penerimaan.tanggal_penerimaan','penerimaan.status','penerimaan.no_pembelian','penerimaan.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                        ->join('penerimaan', 'penerimaan_detail.no_penerimaan', '=', 'penerimaan.no_penerimaan')
                        ->join('produk','penerimaan_detail.kode_produk', '=', 'produk.id')
                        ->where('penerimaan.status', $status)
                        ->where('penerimaan_detail.qty_retur',0)
                        ->where('produk.kode_kategori', $kategori)
                        ->where('penerimaan.kode_lokasi', $get_lokasi)
                        ->whereBetween('penerimaan.tanggal_penerimaan', array($tanggal_awal, $tanggal_akhir))
                        ->orderBy('penerimaan.tanggal_penerimaan','asc')
                        ->get();
            
                        $pdf = PDF::loadView('/admin/laporanpenerimaan/pdf', compact('penerimaan','penerimaandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','dt','format_ttd'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan Penerimaan Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                    else if($kategori == 'SEMUA'){
                        $penerimaan = Penerimaan::on($konek)->whereBetween('tanggal_penerimaan', array($tanggal_awal, $tanggal_akhir))->get();

                        $penerimaandetail = PenerimaanDetail::on($konek)
                        ->select('penerimaan_detail.*','penerimaan.tanggal_penerimaan','penerimaan.status','penerimaan.no_pembelian','penerimaan.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                        ->join('penerimaan', 'penerimaan_detail.no_penerimaan', '=', 'penerimaan.no_penerimaan')
                        ->join('produk','penerimaan_detail.kode_produk', '=', 'produk.id')
                        ->where('penerimaan.status', $status)
                        ->where('penerimaan_detail.qty_retur',0)
                        ->where('penerimaan.kode_lokasi', $get_lokasi)
                        ->whereBetween('penerimaan.tanggal_penerimaan', array($tanggal_awal, $tanggal_akhir))
                        ->orderBy('penerimaan.tanggal_penerimaan','asc')
                        ->get();
                        
                        $pdf = PDF::loadView('/admin/laporanpenerimaan/pdf', compact('penerimaan','penerimaandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','dt','format_ttd'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan Penerimaan Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                }
            }
            else if($tipe == 'PDF' && $status == 'SEMUA'){
                if($kategori != 'SEMUA'){
                    $penerimaan = Penerimaan::on($konek)->whereBetween('tanggal_penerimaan', array($tanggal_awal, $tanggal_akhir))->get();

                    $penerimaandetail = PenerimaanDetail::on($konek)
                    ->select('penerimaan_detail.*','penerimaan.tanggal_penerimaan','penerimaan.status','penerimaan.no_pembelian','penerimaan.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                    ->join('penerimaan', 'penerimaan_detail.no_penerimaan', '=', 'penerimaan.no_penerimaan')
                    ->join('produk','penerimaan_detail.kode_produk', '=', 'produk.id')
                    ->where('produk.kode_kategori', $kategori)
                    ->where('penerimaan.kode_lokasi', $get_lokasi)
                    ->whereBetween('penerimaan.tanggal_penerimaan', array($tanggal_awal, $tanggal_akhir))
                    ->orderBy('penerimaan.tanggal_penerimaan','asc')
                    ->get();
        
                    $pdf = PDF::loadView('/admin/laporanpenerimaan/pdf', compact('penerimaan','penerimaandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','dt','format_ttd'));

                    $pdf->setPaper('a4', 'landscape');

                    return $pdf->stream('Laporan Penerimaan Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                }
                else if($kategori == 'SEMUA'){
                    $penerimaan = Penerimaan::on($konek)->whereBetween('tanggal_penerimaan', array($tanggal_awal, $tanggal_akhir))->get();

                    $penerimaandetail = PenerimaanDetail::on($konek)
                    ->select('penerimaan_detail.*','penerimaan.tanggal_penerimaan','penerimaan.status','penerimaan.no_pembelian','penerimaan.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                    ->join('penerimaan', 'penerimaan_detail.no_penerimaan', '=', 'penerimaan.no_penerimaan')
                    ->join('produk','penerimaan_detail.kode_produk', '=', 'produk.id')
                    ->where('penerimaan.kode_lokasi', $get_lokasi)
                    ->whereBetween('penerimaan.tanggal_penerimaan', array($tanggal_awal, $tanggal_akhir))
                    ->orderBy('penerimaan.tanggal_penerimaan','asc')
                    ->get();
                    // dd($penerimaandetail);
        
                    $pdf = PDF::loadView('/admin/laporanpenerimaan/pdf', compact('penerimaan','penerimaandetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','kategori','nama','nama2','dt','format_ttd'));

                    $pdf->setPaper('a4', 'landscape');

                    return $pdf->stream('Laporan Penerimaan Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                }
            } 
            else if ($tipe == 'excel'){
                return Excel::download(new PenerimaanExport($tanggal_awal, $tanggal_akhir, $status, $kategori, $get_lokasi), 'Laporan Penerimaan dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
            }
    }
}
