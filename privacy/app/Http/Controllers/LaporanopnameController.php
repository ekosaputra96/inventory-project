<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\tb_akhir_bulan;
use App\Models\tb_item_bulanan;
use App\Models\tb_produk_history;
use App\Models\Opname;
use App\Models\OpnameDetail;
use App\Models\MasterLokasi;
use App\Models\Company;
use App\Models\Signature;
use App\Exports\LaporanopnameExport;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use DB;
use Carbon;

class LaporanopnameController extends Controller
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
        $create_url = route('laporanopname.create');
        $no_opname = Opname::on($konek)->pluck('no_opname','no_opname');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;
        
        $lokasi = MasterLokasi::select('kode_lokasi', DB::raw("concat(kode_lokasi,' - ',nama_lokasi) as lokasi"))->pluck('lokasi','kode_lokasi');

        return view('admin.laporanopname.index',compact('lokasi','create_url','no_opname','period', 'nama_lokasi'));
    }

    public function exportPDF(){
        $konek = self::konek();
        $tanggal_awal = $_GET['tanggal_awal'];
        $tanggal_akhir = $_GET['tanggal_akhir'];
        $tipe = $_GET['jenis_report'];
        $status = $_GET['status'];
        $lokasi = $_GET['lokasi'];
        
        if(isset($_GET['ttd'])){
            $format_ttd = $_GET['ttd']; 
        }else{
            $format_ttd = 0;
        }
        
        $limit2 = Signature::on($konek)->where('jabatan','DIREKTUR KEUANGAN')->first();

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

        $nama_lokasi = MasterLokasi::find($lokasi);
        $nama = $nama_lokasi->kode_lokasi.' - '.$nama_lokasi->nama_lokasi;

        $nama_company = Company::find($get_company);
        $nama2 = $nama_company->nama_company;

       
            if ($tipe == 'PDF'){
                $opname = Opname::on($konek)->whereBetween('tanggal_opname', array($tanggal_awal, $tanggal_akhir))->get();

                $opnamedetail = OpnameDetail::on($konek)
                    ->select('opname_detail.*','opname.tanggal_opname','opname.status','opname.no_opname','produk.nama_produk','produk.kode_kategori','opname.kode_lokasi')
                    ->join('opname', 'opname_detail.no_opname', '=', 'opname.no_opname')
                    ->join('produk','opname_detail.kode_produk', '=', 'produk.id')
                    ->where('opname.status', $status)
                    ->where('opname.kode_lokasi', $lokasi)
                    ->whereBetween('opname.tanggal_opname', array($tanggal_awal, $tanggal_akhir))
                    ->orderBy('opname.tanggal_opname','asc')
                    ->get();
                        
                $pdf = PDF::loadView('/admin/laporanopname/pdf', compact('opname','opnamedetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','limit2','nama','nama2','dt','format_ttd'));

                $pdf->setPaper('a4', 'landscape');

                return $pdf->stream('Laporan Opname Site '.$lokasi.' Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
            }
            else {
                return Excel::download(new LaporanopnameExport($tanggal_awal, $tanggal_akhir, $status,$lokasi), 'Laporan Opname Site '.$lokasi.' dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
            }
        
    }
}
