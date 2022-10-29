<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\tb_akhir_bulan;
use App\Models\tb_item_bulanan;
use App\Models\Produk;
use App\Models\Returpembelian;
use App\Models\ReturpembelianDetail;
use App\Models\MasterLokasi;
use App\Models\Company;
use App\Models\Signature;
use App\Exports\ReturpembelianExport;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use DB;
use Carbon;

class LaporanreturpembelianController extends Controller
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
        $create_url = route('laporanreturpembelian.create');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        return view('admin.laporanreturpembelian.index',compact('create_url','period','nama_lokasi'));
    }

    public function exportPDF(){
        $konek = self::konek();
        $tanggal_awal = $_GET['tanggal_awal'];
        $tanggal_akhir = $_GET['tanggal_akhir'];
        $tipe = $_GET['jenis_report'];
        $status = $_GET['status'];
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

        $nama_lokasi = MasterLokasi::find($get_lokasi);
        $nama = $nama_lokasi->nama_lokasi;

        $nama_company = Company::find($get_company);
        $nama2 = $nama_company->nama_company;

       
            if ($tipe == 'PDF' && $status != 'SEMUA'){
                $returbeli = Returpembelian::on($konek)->whereBetween('tanggal_returpembelian', array($tanggal_awal, $tanggal_akhir))->get();

                $returbelidetail = ReturpembelianDetail::on($konek)
                    ->with('vendor')
                    ->select('returpembelian_detail.*','retur_pembelian.tanggal_returpembelian','retur_pembelian.status','retur_pembelian.no_pembelian','retur_pembelian.no_penerimaan','retur_pembelian.kode_vendor','produk.nama_produk')
                    ->join('retur_pembelian', 'returpembelian_detail.no_returpembelian', '=', 'retur_pembelian.no_returpembelian')
                    ->join('produk','returpembelian_detail.kode_produk', '=', 'produk.id')
                    ->where('retur_pembelian.status', $status)
                    ->whereBetween('retur_pembelian.tanggal_returpembelian', array($tanggal_awal, $tanggal_akhir))
                    ->orderBy('retur_pembelian.tanggal_returpembelian','asc')
                    ->get();
                        
                $pdf = PDF::loadView('/admin/laporanreturpembelian/pdf', compact('returbeli','returbelidetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','dt','format_ttd'));

                $pdf->setPaper('a4', 'landscape');

                return $pdf->stream('Laporan Penerimaan Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
            }
            else if($tipe == 'PDF' && $status == 'SEMUA'){
                $returbeli = Returpembelian::on($konek)->whereBetween('tanggal_returpembelian', array($tanggal_awal, $tanggal_akhir))->get();

                $returbelidetail = ReturpembelianDetail::on($konek)
                    ->with('vendor')
                    ->select('returpembelian_detail.*','retur_pembelian.tanggal_returpembelian','retur_pembelian.status','retur_pembelian.no_pembelian','retur_pembelian.no_penerimaan','retur_pembelian.kode_vendor','produk.nama_produk')
                    ->join('retur_pembelian', 'returpembelian_detail.no_returpembelian', '=', 'retur_pembelian.no_returpembelian')
                    ->join('produk','returpembelian_detail.kode_produk', '=', 'produk.id')
                    ->whereBetween('retur_pembelian.tanggal_returpembelian', array($tanggal_awal, $tanggal_akhir))
                    ->orderBy('retur_pembelian.tanggal_returpembelian','asc')
                    ->get();

                $pdf = PDF::loadView('/admin/laporanreturpembelian/pdf', compact('returbeli','returbelidetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','dt','format_ttd'));

                $pdf->setPaper('a4', 'landscape');

                return $pdf->stream('Laporan Penerimaan Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
            }
            else if ($tipe == 'excel'){
                return Excel::download(new ReturpembelianExport($tanggal_awal, $tanggal_akhir, $status), 'Laporan Retur Pembelian dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
            }
        
    }
}
