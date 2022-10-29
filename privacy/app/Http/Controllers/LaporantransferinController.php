<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\tb_akhir_bulan;
use App\Models\LaporanPemakaian;
use App\Models\LaporanPenerimaan;
use App\Models\LaporanTransferin;
use App\Models\tb_item_bulanan;
use App\Models\tb_produk_history;
use App\Models\Produk;
use App\Models\Jasa;
use App\Models\Vendor;
use App\Models\Mobil;
use App\Models\KategoriProduk;
use App\Models\MasterLokasi;
use App\Models\Company;
use App\Models\TransferIn;
use App\Models\TransferInDetail;
use App\Models\Signature;
use App\Exports\TransferinExport;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use DB;
use Carbon;

class LaporantransferinController extends Controller
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
        $create_url = route('laporantransferin.create');
        $no_trf_in = TransferIn::on($konek)->pluck('no_trf_in','no_trf_in');
        $Produk = Produk::on($konek)->pluck('nama_produk', 'id');
        $kategori = KategoriProduk::select('kode_kategori', DB::raw("concat(kode_kategori,' - ',nama_kategori) as kategori"))->pluck('kategori','kode_kategori');
        $lokasi = tb_item_bulanan::on($konek)->pluck('kode_lokasi', 'kode_lokasi');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        $lokasi_user = auth()->user()->kode_lokasi;
        
        return view('admin.laporantransferin.index',compact('create_url','Produk','no_trf_in','period','kategori', 'nama_lokasi','lokasi'));
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
            if ($tipe == 'PDF' && $status != 'SEMUA'){
                if($kategori != 'SEMUA'){
                    $transferin = TransferIn::on($konek)->whereBetween('tanggal_transfer', array($tanggal_awal, $tanggal_akhir))->get();

                    $transferindetail = TransferInDetail::on($konek)
                    ->select('transfer_in_detail.*','transfer_in.tanggal_transfer','transfer_in.status','transfer_in.no_trf_in','transfer_in.kode_lokasi','transfer_in.no_transfer','transfer_in.transfer_dari','produk.kode_kategori','produk.nama_produk','produk.kode_satuan')
                    ->join('transfer_in', 'transfer_in_detail.no_trf_in', '=', 'transfer_in.no_trf_in')
                    ->join('produk','transfer_in_detail.kode_produk', '=', 'produk.id')
                    ->where('transfer_in.status', $status)
                    ->where('produk.kode_kategori', $kategori)
                    ->where('transfer_in.kode_lokasi', $request4)
                    ->whereBetween('transfer_in.tanggal_transfer', array($tanggal_awal, $tanggal_akhir))
                    ->orderBy('transfer_in.no_trf_in','asc')
                    ->get();
                }
                else if($kategori == 'SEMUA'){
                    $transferin = TransferIn::on($konek)->whereBetween('tanggal_transfer', array($tanggal_awal, $tanggal_akhir))->get();

                    $transferindetail = TransferInDetail::on($konek)
                    ->select('transfer_in_detail.*','transfer_in.tanggal_transfer','transfer_in.status','transfer_in.no_trf_in','transfer_in.kode_lokasi','transfer_in.no_transfer','transfer_in.transfer_dari','produk.kode_kategori','produk.nama_produk','produk.kode_satuan')
                    ->join('transfer_in', 'transfer_in_detail.no_trf_in', '=', 'transfer_in.no_trf_in')
                    ->join('produk','transfer_in_detail.kode_produk', '=', 'produk.id')
                    ->where('transfer_in.status', $status)
                    ->where('transfer_in.kode_lokasi', $request4)
                    ->whereBetween('transfer_in.tanggal_transfer', array($tanggal_awal, $tanggal_akhir))
                    ->orderBy('transfer_in.no_trf_in','asc')
                    ->get();
                }

                $pdf = PDF::loadView('/admin/laporantransferin/pdf', compact('transferin','transferindetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','kategori','lokasi','dt','format_ttd'));

                $pdf->setPaper('a4', 'landscape');

                return $pdf->stream('Laporan Transfer In Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
            }
            else if($tipe == 'PDF' && $status == 'SEMUA'){
                if($kategori != 'SEMUA'){
                    $transferin = TransferIn::on($konek)->whereBetween('tanggal_transfer', array($tanggal_awal, $tanggal_akhir))->get();

                    $transferindetail = TransferInDetail::on($konek)
                    ->select('transfer_in_detail.*','transfer_in.tanggal_transfer','transfer_in.status','transfer_in.no_trf_in','transfer_in.kode_lokasi','transfer_in.no_transfer','transfer_in.transfer_dari','produk.kode_kategori','produk.nama_produk','produk.kode_satuan')
                    ->join('transfer_in', 'transfer_in_detail.no_trf_in', '=', 'transfer_in.no_trf_in')
                    ->join('produk','transfer_in_detail.kode_produk', '=', 'produk.id')
                    ->where('produk.kode_kategori', $kategori)
                    ->where('transfer_in.kode_lokasi', $request4)
                    ->whereBetween('transfer_in.tanggal_transfer', array($tanggal_awal, $tanggal_akhir))
                    ->orderBy('transfer_in.no_trf_in','asc')
                    ->get();
                }
                else if($kategori == 'SEMUA'){
                    $transferin = TransferIn::on($konek)->whereBetween('tanggal_transfer', array($tanggal_awal, $tanggal_akhir))->get();

                    $transferindetail = TransferInDetail::on($konek)
                    ->select('transfer_in_detail.*','transfer_in.tanggal_transfer','transfer_in.status','transfer_in.no_trf_in','transfer_in.kode_lokasi','transfer_in.no_transfer','transfer_in.transfer_dari','produk.kode_kategori','produk.nama_produk','produk.kode_satuan')
                    ->join('transfer_in', 'transfer_in_detail.no_trf_in', '=', 'transfer_in.no_trf_in')
                    ->join('produk','transfer_in_detail.kode_produk', '=', 'produk.id')
                    ->where('transfer_in.kode_lokasi', $request4)
                    ->whereBetween('transfer_in.tanggal_transfer', array($tanggal_awal, $tanggal_akhir))
                    ->orderBy('transfer_in.no_trf_in','asc')
                    ->get();
                }

                $pdf = PDF::loadView('/admin/laporantransferin/pdf', compact('transferin','transferindetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','kategori','lokasi','dt','format_ttd'));

                $pdf->setPaper('a4', 'landscape');

                return $pdf->stream('Laporan Transfer In Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
            } 
            else if ($tipe == 'excel'){
                return Excel::download(new TransferinExport($tanggal_awal, $tanggal_akhir, $status, $kategori, $lokasi), 'Laporan Transfer In dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
            } 
        }
        else{
            if ($tipe == 'PDF' && $status != 'SEMUA'){
                if($kategori != 'SEMUA'){
                    $transferin = TransferIn::on($konek)->whereBetween('tanggal_transfer', array($tanggal_awal, $tanggal_akhir))->get();

                    $transferindetail = TransferInDetail::on($konek)
                    ->select('transfer_in_detail.*','transfer_in.tanggal_transfer','transfer_in.status','transfer_in.no_trf_in','transfer_in.kode_lokasi','transfer_in.no_transfer','transfer_in.transfer_dari','produk.kode_kategori','produk.nama_produk','produk.kode_satuan')
                    ->join('transfer_in', 'transfer_in_detail.no_trf_in', '=', 'transfer_in.no_trf_in')
                    ->join('produk','transfer_in_detail.kode_produk', '=', 'produk.id')
                    ->where('transfer_in.status', $status)
                    ->where('produk.kode_kategori', $kategori)
                    ->whereBetween('transfer_in.tanggal_transfer', array($tanggal_awal, $tanggal_akhir))
                    ->orderBy('transfer_in.no_trf_in','asc')
                    ->get();
                }
                else if($kategori == 'SEMUA'){
                    $transferin = TransferIn::on($konek)->whereBetween('tanggal_transfer', array($tanggal_awal, $tanggal_akhir))->get();

                    $transferindetail = TransferInDetail::on($konek)
                    ->select('transfer_in_detail.*','transfer_in.tanggal_transfer','transfer_in.status','transfer_in.no_trf_in','transfer_in.kode_lokasi','transfer_in.no_transfer','transfer_in.transfer_dari','produk.kode_kategori','produk.nama_produk','produk.kode_satuan')
                    ->join('transfer_in', 'transfer_in_detail.no_trf_in', '=', 'transfer_in.no_trf_in')
                    ->join('produk','transfer_in_detail.kode_produk', '=', 'produk.id')
                    ->where('transfer_in.status', $status)
                    ->whereBetween('transfer_in.tanggal_transfer', array($tanggal_awal, $tanggal_akhir))
                    ->orderBy('transfer_in.no_trf_in','asc')
                    ->get();
                }

                $pdf = PDF::loadView('/admin/laporantransferin/pdf', compact('transferin','transferindetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','kategori','lokasi','dt','format_ttd'));

                $pdf->setPaper('a4', 'landscape');

                return $pdf->stream('Laporan Transfer In Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
            }
            else if($tipe == 'PDF' && $status == 'SEMUA'){
                if($kategori != 'SEMUA'){
                    $transferin = TransferIn::on($konek)->whereBetween('tanggal_transfer', array($tanggal_awal, $tanggal_akhir))->get();

                    $transferindetail = TransferInDetail::on($konek)
                    ->select('transfer_in_detail.*','transfer_in.tanggal_transfer','transfer_in.status','transfer_in.no_trf_in','transfer_in.kode_lokasi','transfer_in.no_transfer','transfer_in.transfer_dari','produk.kode_kategori','produk.nama_produk','produk.kode_satuan')
                    ->join('transfer_in', 'transfer_in_detail.no_trf_in', '=', 'transfer_in.no_trf_in')
                    ->join('produk','transfer_in_detail.kode_produk', '=', 'produk.id')
                    ->where('produk.kode_kategori', $kategori)
                    ->whereBetween('transfer_in.tanggal_transfer', array($tanggal_awal, $tanggal_akhir))
                    ->orderBy('transfer_in.no_trf_in','asc')
                    ->get();
                }
                else if($kategori == 'SEMUA'){
                    $transferin = TransferIn::on($konek)->whereBetween('tanggal_transfer', array($tanggal_awal, $tanggal_akhir))->get();

                    $transferindetail = TransferInDetail::on($konek)
                    ->select('transfer_in_detail.*','transfer_in.tanggal_transfer','transfer_in.status','transfer_in.no_trf_in','transfer_in.kode_lokasi','transfer_in.no_transfer','transfer_in.transfer_dari','produk.kode_kategori','produk.nama_produk','produk.kode_satuan')
                    ->join('transfer_in', 'transfer_in_detail.no_trf_in', '=', 'transfer_in.no_trf_in')
                    ->join('produk','transfer_in_detail.kode_produk', '=', 'produk.id')
                    ->whereBetween('transfer_in.tanggal_transfer', array($tanggal_awal, $tanggal_akhir))
                    ->orderBy('transfer_in.no_trf_in','asc')
                    ->get();
                }

                $pdf = PDF::loadView('/admin/laporantransferin/pdf', compact('transferin','transferindetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','kategori','lokasi','dt','format_ttd'));

                $pdf->setPaper('a4', 'landscape');

                return $pdf->stream('Laporan Transfer In Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
            } 
            else if ($tipe == 'excel'){
                return Excel::download(new TransferinExport($tanggal_awal, $tanggal_akhir, $status, $kategori, $lokasi), 'Laporan Transfer In dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
            }
        }
    }
}
