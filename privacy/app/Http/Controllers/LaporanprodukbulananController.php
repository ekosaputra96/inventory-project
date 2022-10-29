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
use App\Models\Pemakaian;
use App\Models\PemakaianDetail;
use App\Models\LaporanProdukBulanan;
use App\Models\KategoriProduk;
use App\Models\MasterLokasi;
use App\Models\Company;
use App\Exports\OpnameExport;
use App\Models\Signature;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use DB;
use Carbon;

class LaporanprodukbulananController extends Controller
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
        $create_url = route('laporanprodukbulanan.create');
        $no_pemakaian = Pemakaian::on($konek)->pluck('no_pemakaian','no_pemakaian');
        $Produk = Produk::on($konek)->pluck('nama_produk', 'id');
        $kategori = KategoriProduk::select('kode_kategori', DB::raw("concat(kode_kategori,' - ',nama_kategori) as kategori"))->pluck('kategori','kode_kategori');
        $lokasi = tb_item_bulanan::on($konek)->pluck('kode_lokasi', 'kode_lokasi');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;
        
        $lokasi_user = auth()->user()->kode_lokasi;
        
        return view('admin.laporanprodukbulanan.index',compact('create_url','Produk','no_pemakaian','period','kategori', 'nama_lokasi','lokasi'));
        
    }

    public function exportPDF(){
        $konek = self::konek();
        $request1 = '01';
        $request = $_GET['month'];
        $req = $_GET['year'];
        $field1 = $_GET['item'];
        $format = $_GET['format_cetak'];
        $kategori = $_GET['kategori'];

        $field = $_GET['item2'];
        if(isset($_GET['ttd'])){
            $format_ttd = $_GET['ttd']; 
        }else{
            $format_ttd = 0;
        }

        $pemakaian = 'Pemakaian';
        $penerimaan = 'Penerimaan';
        $penjualan = 'Penjualan';
        $adjustment = 'Adjustment';
        $opname = 'Opname';
        $transferin = 'Transfer_In';
        $transferout = 'Transfer_Out';
        $returbeli = 'Retur_Beli';
        $returjual = 'Retur_Jual';
        $returpakai = 'Retur_Pakai';
        $semua = 'SEMUA';

        $leng = count($field);

        $i = 0;
        for($i = 0; $i < $leng; $i++){
            if($pemakaian == $_GET['item2'][$i]){
                $pemakaian = 'true';
            }else if($penerimaan == $_GET['item2'][$i]){
                $penerimaan = 'true';
            }else if($penjualan == $_GET['item2'][$i]){
                $penjualan = 'true';
            }else if($adjustment == $_GET['item2'][$i]){
                $adjustment = 'true';
            }else if($opname == $_GET['item2'][$i]){
                $opname = 'true';
            }else if($opname == $_GET['item2'][$i]){
                $opname = 'true';
            }else if($transferin == $_GET['item2'][$i]){
                $transferin = 'true';
            }else if($transferout == $_GET['item2'][$i]){
                $transferout = 'true';
            }else if($returbeli == $_GET['item2'][$i]){
                $returbeli = 'true';
            }else if($returjual == $_GET['item2'][$i]){
                $returjual = 'true';
            }else if($returpakai == $_GET['item2'][$i]){
                $returpakai = 'true';
            }else if($semua == $_GET['item2'][$i]){
                $semua = 'true';
            }
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
        // dd($format);

        if($get_lokasi == 'HO'){
            if($level != 'hse'){
                $lokasi2 = $_GET['lokasi'];
                if($lokasi2 != 'SEMUA'){
                    $request4 = $_GET['lokasi'];
                    if($format == 'PDF'){
                        $tanggal_baru = Carbon\Carbon::createFromDate($req, $request, $request1)->toDateString();
                        $nama_bulan = Carbon\Carbon::parse($tanggal_baru)->format('F');
                            if($kategori != 'SEMUA'){
                                $opnamedetail_cetak = tb_item_bulanan::on($konek)
                                ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori')
                                ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                                ->where('produk.kode_kategori', $kategori)
                                ->where('tb_item_bulanan.kode_lokasi', $request4)
                                ->whereMonth('tb_item_bulanan.periode', $request)
                                ->whereYear('tb_item_bulanan.periode', $req)
                                ->get();
                                
                                $pdf = PDF::loadView('/admin/laporanprodukbulanan/pdf', compact('opnamedetail_cetak','request', 'req', 'nama_bulan','date','ttd','limit3','pemakaian','penerimaan','penjualan','adjustment','opname','transferin','transferout','returbeli','returjual','returpakai','semua','kategori','nama','nama2','semua','field1','lokasi2','dt','format_ttd'));
                                $pdf->setPaper('legal', 'landscape');
                                return $pdf->stream('Laporan Item Bulan '.$request.' Tahun '.$req.'.pdf');
                            }
                            else{
                                $opnamedetail_cetak = tb_item_bulanan::on($konek)
                                ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori')
                                ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                                ->where('tb_item_bulanan.kode_lokasi', $request4)
                                ->whereMonth('tb_item_bulanan.periode', $request)
                                ->whereYear('tb_item_bulanan.periode', $req)
                                ->get();
                                
                                $pdf = PDF::loadView('/admin/laporanprodukbulanan/pdf', compact('opnamedetail_cetak','request', 'req', 'nama_bulan','date','ttd','limit3','pemakaian','penerimaan','penjualan','adjustment','opname','transferin','transferout','returbeli','returjual','returpakai','kategori','nama','nama2','semua','field1','lokasi2','dt','format_ttd'));
                                $pdf->setPaper('legal', 'landscape');
                                return $pdf->stream('Laporan Item Bulan '.$request.' Tahun '.$req.'.pdf');
                            }
                    }
                    else{
                        $opnamedetail_cetak = tb_item_bulanan::whereMonth('periode', $request)->whereYear('periode', $req)->get();
                        if($kategori != 'SEMUA'){
                            return Excel::download(new OpnameExport($request, $req, $request4, $kategori, $lokasi2), 'Laporan Item Bulan '.$request.' Tahun '.$req.'.xlsx');
                        }else{
                            return Excel::download(new OpnameExport($request, $req, $request4, $kategori, $lokasi2), 'Laporan Item Bulan '.$request.' Tahun '.$req.'.xlsx');
                        }
                    }
                }
                else{
                    if($format == 'PDF'){
                        $tanggal_baru = Carbon\Carbon::createFromDate($req, $request, $request1)->toDateString();
                        $nama_bulan = Carbon\Carbon::parse($tanggal_baru)->format('F');
                            if($kategori != 'SEMUA'){
                                $opnamedetail_cetak = tb_item_bulanan::on($konek)
                                ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori')
                                ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                                ->where('produk.kode_kategori', $kategori)
                                ->whereMonth('tb_item_bulanan.periode', $request)
                                ->whereYear('tb_item_bulanan.periode', $req)
                                ->get();
                                // dd($opnamedetail_cetak);
                                
                                $pdf = PDF::loadView('/admin/laporanprodukbulanan/pdf', compact('opnamedetail_cetak','request', 'req', 'nama_bulan','date','ttd','limit3','pemakaian','penerimaan','penjualan','adjustment','opname','transferin','transferout','returbeli','returjual','returpakai','semua','kategori','nama','nama2','semua','field1','lokasi2','dt','format_ttd'));
                                $pdf->setPaper('legal', 'landscape');
                                return $pdf->stream('Laporan Item Bulan '.$request.' Tahun '.$req.'.pdf');
                            }
                            else{
                                $opnamedetail_cetak = tb_item_bulanan::on($konek)
                                ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori')
                                ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                                ->whereMonth('tb_item_bulanan.periode', $request)
                                ->whereYear('tb_item_bulanan.periode', $req)
                                ->get();
    
                                $pdf = PDF::loadView('/admin/laporanprodukbulanan/pdf', compact('opnamedetail_cetak','request', 'req', 'nama_bulan','date','ttd','limit3','pemakaian','penerimaan','penjualan','adjustment','opname','transferin','transferout','returbeli','returjual','returpakai','semua','kategori','nama','nama2','semua','field1','lokasi2','dt','format_ttd'));
                                $pdf->setPaper('legal', 'landscape');
                                return $pdf->stream('Laporan Item Bulan '.$request.' Tahun '.$req.'.pdf');
                            }
                    }
                    else{
                        $opnamedetail_cetak = tb_item_bulanan::whereMonth('periode', $request)->whereYear('periode', $req)->get();
                        if($kategori != 'SEMUA'){
                            return Excel::download(new OpnameExport($request, $req, $get_lokasi, $kategori, $lokasi2), 'Laporan Item Bulan '.$request.' Tahun '.$req.'.xlsx');
                        }else{
                            return Excel::download(new OpnameExport($request, $req, $get_lokasi, $kategori, $lokasi2), 'Laporan Item Bulan '.$request.' Tahun '.$req.'.xlsx');
                        }
                    }
                }
            }
            else{
                $lokasi2 = $_GET['lokasi'];
                if($lokasi2 != 'SEMUA'){
                    $request4 = $_GET['lokasi'];
                    if($format == 'PDF'){
                        $tanggal_baru = Carbon\Carbon::createFromDate($req, $request, $request1)->toDateString();
                        $nama_bulan = Carbon\Carbon::parse($tanggal_baru)->format('F');
                            if($kategori != 'SEMUA'){
                                $opnamedetail_cetak = tb_item_bulanan::on($konek)
                                ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori')
                                ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                                ->where('produk.kode_kategori', $kategori)
                                ->where('tb_item_bulanan.kode_lokasi', $request4)
                                ->whereMonth('tb_item_bulanan.periode', $request)
                                ->whereYear('tb_item_bulanan.periode', $req)
                                ->get();
                                // dd($opnamedetail_cetak);
                                
                                $pdf = PDF::loadView('/admin/laporanprodukbulanan/pdfhse', compact('opnamedetail_cetak','request', 'req', 'nama_bulan','date','ttd','limit3','limithse','kategori','nama','nama2','pemakaian','penerimaan','penjualan','adjustment','opname','transferin','returpakai','transferout','returbeli','returjual','semua','field1','lokasi2','dt','format_ttd'));
                                $pdf->setPaper('legal', 'landscape');
                                return $pdf->stream('Laporan Item Bulan '.$request.' Tahun '.$req.'.pdf');
                            }
                            else{
                                $opnamedetail_cetak = tb_item_bulanan::on($konek)
                                ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori')
                                ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                                ->where('tb_item_bulanan.kode_lokasi', $request4)
                                ->whereMonth('tb_item_bulanan.periode', $request)
                                ->whereYear('tb_item_bulanan.periode', $req)
                                ->get();
                                
                                $pdf = PDF::loadView('/admin/laporanprodukbulanan/pdfhse', compact('opnamedetail_cetak','request', 'req', 'nama_bulan','date','ttd','limit3','limithse','kategori','nama','nama2','pemakaian','penerimaan','penjualan','adjustment','opname','transferin','returpakai','transferout','returbeli','returjual','semua','field1','lokasi2','dt','format_ttd'));
                                $pdf->setPaper('legal', 'landscape');
                                return $pdf->stream('Laporan Item Bulan '.$request.' Tahun '.$req.'.pdf');
                            }
                    }
                    else{
                        $opnamedetail_cetak = tb_item_bulanan::whereMonth('periode', $request)->whereYear('periode', $req)->get();
                        if($kategori != 'SEMUA'){
                            return Excel::download(new OpnameExport($request, $req, $request4, $kategori, $lokasi2), 'Laporan Item Bulan '.$request.' Tahun '.$req.'.xlsx');
                        }else{
                            return Excel::download(new OpnameExport($request, $req, $request4, $kategori, $lokasi2), 'Laporan Item Bulan '.$request.' Tahun '.$req.'.xlsx');
                        }
                    }
                }
                else{
                    if($format == 'PDF'){
                        $tanggal_baru = Carbon\Carbon::createFromDate($req, $request, $request1)->toDateString();
                        $nama_bulan = Carbon\Carbon::parse($tanggal_baru)->format('F');
    
                            if($kategori != 'SEMUA'){
                                $opnamedetail_cetak = tb_item_bulanan::on($konek)
                                ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori')
                                ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                                ->where('produk.kode_kategori', $kategori)
                                ->whereMonth('tb_item_bulanan.periode', $request)
                                ->whereYear('tb_item_bulanan.periode', $req)
                                ->get();
                                
                                $lokasi2 = $get_lokasi;
                                $pdf = PDF::loadView('/admin/laporanprodukbulanan/pdfhse', compact('opnamedetail_cetak','request', 'req', 'nama_bulan','date','ttd','limit3','limithse','kategori','nama','nama2','pemakaian','penerimaan','penjualan','adjustment','opname','transferin','returpakai','transferout','returbeli','returjual','semua','field1','lokasi2','dt','format_ttd'));
                                $pdf->setPaper('legal', 'landscape');
                
                                return $pdf->stream('Laporan Item Bulan '.$request.' Tahun '.$req.'.pdf');
                            }
                            else{
                                $opnamedetail_cetak = tb_item_bulanan::on($konek)
                                ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori')
                                ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                                ->whereMonth('tb_item_bulanan.periode', $request)
                                ->whereYear('tb_item_bulanan.periode', $req)
                                ->get();
                                
                                $lokasi2 = $get_lokasi;
                                $pdf = PDF::loadView('/admin/laporanprodukbulanan/pdfhse', compact('opnamedetail_cetak','request', 'req', 'nama_bulan','date','ttd','limit3','limithse','kategori','nama','nama2','pemakaian','penerimaan','penjualan','adjustment','opname','transferin','returpakai','transferout','returbeli','returjual','semua','field1','lokasi2','dt','format_ttd'));
                                $pdf->setPaper('legal', 'landscape');
                            
                                return $pdf->stream('Laporan Item Bulan '.$request.' Tahun '.$req.'.pdf');
                            }
                    }
                    else{
                        $opnamedetail_cetak = tb_item_bulanan::whereMonth('periode', $request)->whereYear('periode', $req)->get();
                        if($kategori != 'SEMUA'){
                            return Excel::download(new OpnameExport($request, $req, $get_lokasi, $kategori, $lokasi2), 'Laporan Item Bulan '.$request.' Tahun '.$req.'.xlsx');
                        }else{
                            return Excel::download(new OpnameExport($request, $req, $get_lokasi, $kategori, $lokasi2), 'Laporan Item Bulan '.$request.' Tahun '.$req.'.xlsx');
                        }
                    }
                }
            }
        }else{
            if($level != 'hse'){
                if($format == 'PDF'){
                    $tanggal_baru = Carbon\Carbon::createFromDate($req, $request, $request1)->toDateString();
                    $nama_bulan = Carbon\Carbon::parse($tanggal_baru)->format('F');
                        if($kategori != 'SEMUA'){
                            $opnamedetail_cetak = tb_item_bulanan::on($konek)
                            ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori')
                            ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                            ->where('produk.kode_kategori', $kategori)
                            ->where('tb_item_bulanan.kode_lokasi', $get_lokasi)
                            ->whereMonth('tb_item_bulanan.periode', $request)
                            ->whereYear('tb_item_bulanan.periode', $req)
                            ->get();
                            
                            $lokasi2 = $get_lokasi;
                            $pdf = PDF::loadView('/admin/laporanprodukbulanan/pdf', compact('opnamedetail_cetak','request', 'req', 'nama_bulan','date','ttd','limit3','pemakaian','penerimaan','penjualan','adjustment','opname','transferin','transferout','returbeli','returjual','returpakai','semua','kategori','nama','nama2','semua','field1','lokasi2','dt','format_ttd'));
                            $pdf->setPaper('legal', 'landscape');
                            return $pdf->stream('Laporan Item Bulan '.$request.' Tahun '.$req.'.pdf');
                        }
                        else{
                            $opnamedetail_cetak = tb_item_bulanan::on($konek)
                            ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori')
                            ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                            ->where('tb_item_bulanan.kode_lokasi', $get_lokasi)
                            ->whereMonth('tb_item_bulanan.periode', $request)
                            ->whereYear('tb_item_bulanan.periode', $req)
                            ->get();
                            
                            $lokasi2 = $get_lokasi;
                            $pdf = PDF::loadView('/admin/laporanprodukbulanan/pdf', compact('opnamedetail_cetak','request', 'req', 'nama_bulan','date','ttd','limit3','pemakaian','penerimaan','penjualan','adjustment','opname','transferin','transferout','returbeli','returjual','returpakai','kategori','nama','nama2','semua','field1','lokasi2','dt','format_ttd'));
                            $pdf->setPaper('legal', 'landscape');
                            return $pdf->stream('Laporan Item Bulan '.$request.' Tahun '.$req.'.pdf');
                        }
                }
                else{
                    $lokasi2 = $get_lokasi;
                    $opnamedetail_cetak = tb_item_bulanan::whereMonth('periode', $request)->whereYear('periode', $req)->get();
                    if($kategori != 'SEMUA'){
                        return Excel::download(new OpnameExport($request, $req, $get_lokasi, $kategori, $lokasi2), 'Laporan Item Bulan '.$request.' Tahun '.$req.'.xlsx');
                    }else{
                        return Excel::download(new OpnameExport($request, $req, $get_lokasi, $kategori, $lokasi2), 'Laporan Item Bulan '.$request.' Tahun '.$req.'.xlsx');
                    }
                }
            }
            else{
                if($format == 'PDF'){
                    $tanggal_baru = Carbon\Carbon::createFromDate($req, $request, $request1)->toDateString();
                    $nama_bulan = Carbon\Carbon::parse($tanggal_baru)->format('F');
                        if($kategori != 'SEMUA'){
                            $opnamedetail_cetak = tb_item_bulanan::on($konek)
                            ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori')
                            ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                            ->where('produk.kode_kategori', $kategori)
                            ->where('tb_item_bulanan.kode_lokasi', $get_lokasi)
                            ->whereMonth('tb_item_bulanan.periode', $request)
                            ->whereYear('tb_item_bulanan.periode', $req)
                            ->get();
                            // dd($opnamedetail_cetak);
                            
                            $lokasi2 = $get_lokasi;
                            $pdf = PDF::loadView('/admin/laporanprodukbulanan/pdfhse', compact('opnamedetail_cetak','request', 'req', 'nama_bulan','date','ttd','limit3','limithse','kategori','nama','nama2','pemakaian','penerimaan','penjualan','adjustment','opname','transferin','returpakai','transferout','returbeli','returjual','semua','field1','lokasi2','dt','format_ttd'));
                            $pdf->setPaper('legal', 'landscape');
                            return $pdf->stream('Laporan Item Bulan '.$request.' Tahun '.$req.'.pdf');
                        }
                        else{
                            $opnamedetail_cetak = tb_item_bulanan::on($konek)
                            ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori')
                            ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                            ->where('tb_item_bulanan.kode_lokasi', $get_lokasi)
                            ->whereMonth('tb_item_bulanan.periode', $request)
                            ->whereYear('tb_item_bulanan.periode', $req)
                            ->get();
                            
                            $lokasi2 = $get_lokasi;
                            $pdf = PDF::loadView('/admin/laporanprodukbulanan/pdfhse', compact('opnamedetail_cetak','request', 'req', 'nama_bulan','date','ttd','limit3','limithse','kategori','nama','nama2','pemakaian','penerimaan','penjualan','adjustment','opname','transferin','returpakai','transferout','returbeli','returjual','semua','field1','lokasi2','dt','format_ttd'));
                            $pdf->setPaper('legal', 'landscape');
                            return $pdf->stream('Laporan Item Bulan '.$request.' Tahun '.$req.'.pdf');
                        }
                }
                else{
                    $lokasi2 = $get_lokasi;
                    $opnamedetail_cetak = tb_item_bulanan::whereMonth('periode', $request)->whereYear('periode', $req)->get();
                    if($kategori != 'SEMUA'){
                        return Excel::download(new OpnameExport($request, $req, $get_lokasi, $kategori, $lokasi2), 'Laporan Item Bulan '.$request.' Tahun '.$req.'.xlsx');
                    }else{
                        return Excel::download(new OpnameExport($request, $req, $get_lokasi, $kategori, $lokasi2), 'Laporan Item Bulan '.$request.' Tahun '.$req.'.xlsx');
                    }
                }
            }
        }
    }
}
