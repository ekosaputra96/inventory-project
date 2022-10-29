<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\tb_akhir_bulan;
use App\Models\LaporanPemakaian;
use App\Models\LaporanPenerimaan;
use App\Models\LaporanTransferout;
use App\Models\tb_item_bulanan;
use App\Models\tb_produk_history;
use App\Models\Produk;
use App\Models\Jasa;
use App\Models\Vendor;
use App\Models\Mobil;
use App\Models\KategoriProduk;
use App\Models\MasterLokasi;
use App\Models\Company;
use App\Models\Transfer;
use App\Models\TransferDetail;
use App\Models\TransferIn;
use App\Models\TransferInDetail;
use App\Models\Signature;
use App\Exports\TransferoutExport;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use DB;
use Carbon;

class LaporantransferoutController extends Controller
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
        $create_url = route('laporantransferout.create');
        $no_transfer = Transfer::on($konek)->pluck('no_transfer','no_transfer');
        $Produk = Produk::on($konek)->pluck('nama_produk', 'id');
        $kategori = KategoriProduk::select('kode_kategori', DB::raw("concat(kode_kategori,' - ',nama_kategori) as kategori"))->pluck('kategori','kode_kategori');
        $lokasi = tb_item_bulanan::on($konek)->pluck('kode_lokasi', 'kode_lokasi');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        $lokasi_user = auth()->user()->kode_lokasi;
        
        return view('admin.laporantransferout.index',compact('create_url','Produk','no_transfer','period','kategori', 'nama_lokasi','lokasi'));
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
            $nama_lokasi = MasterLokasi::find($get_lokasi);
            $nama = $nama_lokasi->nama_lokasi;
        }

        $nama_company = Company::find($get_company);
        $nama2 = $nama_company->nama_company;

        if($get_lokasi == 'HO'){
            $lokasi = $_GET['lokasi'];
            if($lokasi != 'SEMUA'){
                $request4 = $_GET['lokasi'];
                if ($tipe == 'PDF' && $status != 'SEMUA'){
                    if($kategori != 'SEMUA'){
                        $transferout = Transfer::on($konek)->whereBetween('tanggal_transfer', array($tanggal_awal, $tanggal_akhir))->get();

                        $transferoutdetail = TransferDetail::on($konek)
                        ->select('transfer_detail.*','transfer.tanggal_transfer','transfer.status','transfer.no_transfer','transfer.kode_lokasi','produk.kode_kategori','produk.nama_produk','transfer.transfer_dari','transfer.transfer_tujuan')
                        ->join('transfer', 'transfer_detail.no_transfer', '=', 'transfer.no_transfer')
                        ->join('produk','transfer_detail.kode_produk', '=', 'produk.id')
                        ->where('transfer.status', $status)
                        ->where('produk.kode_kategori', $kategori)
                        ->where('transfer.kode_lokasi', $request4)
                        ->whereBetween('transfer.tanggal_transfer', array($tanggal_awal, $tanggal_akhir))
                        ->orderBy('transfer.no_transfer','asc')
                        ->get();
            
                        $pdf = PDF::loadView('/admin/laporantransferout/pdf', compact('konek','transferout','transferoutdetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','kategori','lokasi','dt','format_ttd'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan Transfer Out Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                    else if($kategori == 'SEMUA'){
                        $transferout = Transfer::on($konek)->whereBetween('tanggal_transfer', array($tanggal_awal, $tanggal_akhir))->get();

                        $transferoutdetail = TransferDetail::on($konek)
                        ->select('transfer_detail.*','transfer.tanggal_transfer','transfer.status','transfer.no_transfer','transfer.kode_lokasi','produk.kode_kategori','produk.nama_produk','transfer.transfer_dari','transfer.transfer_tujuan')
                        ->join('transfer', 'transfer_detail.no_transfer', '=', 'transfer.no_transfer')
                        ->join('produk','transfer_detail.kode_produk', '=', 'produk.id')
                        ->where('transfer.status', $status)
                        ->where('transfer.kode_lokasi', $request4)
                        ->whereBetween('transfer.tanggal_transfer', array($tanggal_awal, $tanggal_akhir))
                        ->orderBy('transfer.no_transfer','asc')
                        ->get();
                        
                        $pdf = PDF::loadView('/admin/laporantransferout/pdf', compact('konek','transferout','transferoutdetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','kategori','lokasi','dt','format_ttd'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan Transfer Out Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }

                }
                else if($tipe == 'PDF' && $status == 'SEMUA'){
                    if($kategori != 'SEMUA'){
                        $transferout = Transfer::on($konek)->whereBetween('tanggal_transfer', array($tanggal_awal, $tanggal_akhir))->get();

                        $transferoutdetail = TransferDetail::on($konek)
                        ->select('transfer_detail.*','transfer.tanggal_transfer','transfer.status','transfer.no_transfer','transfer.kode_lokasi','produk.kode_kategori','produk.nama_produk','transfer.transfer_dari','transfer.transfer_tujuan')
                        ->join('transfer', 'transfer_detail.no_transfer', '=', 'transfer.no_transfer')
                        ->join('produk','transfer_detail.kode_produk', '=', 'produk.id')
                        ->where('produk.kode_kategori', $kategori)
                        ->where('transfer.kode_lokasi', $request4)
                        ->whereBetween('transfer.tanggal_transfer', array($tanggal_awal, $tanggal_akhir))
                        ->orderBy('transfer.no_transfer','asc')
                        ->get();
            
                        $pdf = PDF::loadView('/admin/laporantransferout/pdf', compact('konek','transferout','transferoutdetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','kategori','lokasi','dt','format_ttd'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan Transfer Out Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                    else if($kategori == 'SEMUA'){
                        $transferout = Transfer::on($konek)->whereBetween('tanggal_transfer', array($tanggal_awal, $tanggal_akhir))->get();

                        $transferoutdetail = TransferDetail::on($konek)
                        ->select('transfer_detail.*','transfer.tanggal_transfer','transfer.status','transfer.no_transfer','transfer.kode_lokasi','produk.kode_kategori','produk.nama_produk','transfer.transfer_dari','transfer.transfer_tujuan')
                        ->join('transfer', 'transfer_detail.no_transfer', '=', 'transfer.no_transfer')
                        ->join('produk','transfer_detail.kode_produk', '=', 'produk.id')
                        ->where('transfer.kode_lokasi', $request4)
                        ->whereBetween('transfer.tanggal_transfer', array($tanggal_awal, $tanggal_akhir))
                        ->orderBy('transfer.no_transfer','asc')
                        ->get();
            
                        $pdf = PDF::loadView('/admin/laporantransferout/pdf', compact('konek','transferout','transferoutdetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','kategori','lokasi','dt','format_ttd'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan Transfer Out Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                } 
                else if ($tipe == 'excel'){
                    return Excel::download(new TransferoutExport($tanggal_awal, $tanggal_akhir, $status, $kategori, $lokasi), 'Laporan Transfer Out dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                }
            }
            else{
                if ($tipe == 'PDF' && $status != 'SEMUA'){
                    if($kategori != 'SEMUA'){
                        $transferout = Transfer::on($konek)->whereBetween('tanggal_transfer', array($tanggal_awal, $tanggal_akhir))->get();

                        $transferoutdetail = TransferDetail::on($konek)
                        ->select('transfer_detail.*','transfer.tanggal_transfer','transfer.status','transfer.no_transfer','transfer.kode_lokasi','produk.kode_kategori','produk.nama_produk','transfer.transfer_dari','transfer.transfer_tujuan')
                        ->join('transfer', 'transfer_detail.no_transfer', '=', 'transfer.no_transfer')
                        ->join('produk','transfer_detail.kode_produk', '=', 'produk.id')
                        ->where('transfer.status', $status)
                        ->where('produk.kode_kategori', $kategori)
                        ->whereBetween('transfer.tanggal_transfer', array($tanggal_awal, $tanggal_akhir))
                        ->orderBy('transfer.no_transfer','asc')
                        ->get();
            
                        $pdf = PDF::loadView('/admin/laporantransferout/pdf', compact('konek','transferout','transferoutdetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','kategori','lokasi','dt','format_ttd'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan Transfer Out Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                    else if($kategori == 'SEMUA'){
                        $transferout = Transfer::on($konek)->whereBetween('tanggal_transfer', array($tanggal_awal, $tanggal_akhir))->get();

                        $transferoutdetail = TransferDetail::on($konek)
                        ->select('transfer_detail.*','transfer.tanggal_transfer','transfer.status','transfer.no_transfer','transfer.kode_lokasi','produk.kode_kategori','produk.nama_produk','transfer.transfer_dari','transfer.transfer_tujuan')
                        ->join('transfer', 'transfer_detail.no_transfer', '=', 'transfer.no_transfer')
                        ->join('produk','transfer_detail.kode_produk', '=', 'produk.id')
                        ->where('transfer.status', $status)
                        ->whereBetween('transfer.tanggal_transfer', array($tanggal_awal, $tanggal_akhir))
                        ->orderBy('transfer.no_transfer','asc')
                        ->get();
                        
                        $pdf = PDF::loadView('/admin/laporantransferout/pdf', compact('konek','transferout','transferoutdetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','kategori','lokasi','dt','format_ttd'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan Transfer Out Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }

                }
                else if($tipe == 'PDF' && $status == 'SEMUA'){
                    if($kategori != 'SEMUA'){
                        $transferout = Transfer::on($konek)->whereBetween('tanggal_transfer', array($tanggal_awal, $tanggal_akhir))->get();

                        $transferoutdetail = TransferDetail::on($konek)
                        ->select('transfer_detail.*','transfer.tanggal_transfer','transfer.status','transfer.no_transfer','transfer.kode_lokasi','produk.kode_kategori','produk.nama_produk','transfer.transfer_dari','transfer.transfer_tujuan')
                        ->join('transfer', 'transfer_detail.no_transfer', '=', 'transfer.no_transfer')
                        ->join('produk','transfer_detail.kode_produk', '=', 'produk.id')
                        ->where('produk.kode_kategori', $kategori)
                        ->whereBetween('transfer.tanggal_transfer', array($tanggal_awal, $tanggal_akhir))
                        ->orderBy('transfer.no_transfer','asc')
                        ->get();
            
                        $pdf = PDF::loadView('/admin/laporantransferout/pdf', compact('konek','transferout','transferoutdetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','kategori','lokasi','dt','format_ttd'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan Transfer Out Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                    else if($kategori == 'SEMUA'){
                        $transferout = Transfer::on($konek)->whereBetween('tanggal_transfer', array($tanggal_awal, $tanggal_akhir))->get();

                        $transferoutdetail = TransferDetail::on($konek)
                        ->select('transfer_detail.*','transfer.tanggal_transfer','transfer.status','transfer.no_transfer','transfer.kode_lokasi','produk.kode_kategori','produk.nama_produk','transfer.transfer_dari','transfer.transfer_tujuan')
                        ->join('transfer', 'transfer_detail.no_transfer', '=', 'transfer.no_transfer')
                        ->join('produk','transfer_detail.kode_produk', '=', 'produk.id')
                        ->whereBetween('transfer.tanggal_transfer', array($tanggal_awal, $tanggal_akhir))
                        ->orderBy('transfer.no_transfer','asc')
                        ->get();
            
                        $pdf = PDF::loadView('/admin/laporantransferout/pdf', compact('konek','transferout','transferoutdetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','kategori','lokasi','dt','format_ttd'));

                        $pdf->setPaper('a4', 'landscape');

                        return $pdf->stream('Laporan Transfer Out Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                    }
                } 
                else if ($tipe == 'excel'){
                    return Excel::download(new TransferoutExport($tanggal_awal, $tanggal_akhir, $status, $kategori, $lokasi), 'Laporan Transfer Out dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
                }
            }
        }
        else{
            if ($tipe == 'PDF' && $status != 'SEMUA'){
                if($kategori != 'SEMUA'){
                    $transferout = Transfer::on($konek)->whereBetween('tanggal_transfer', array($tanggal_awal, $tanggal_akhir))->get();

                    $transferoutdetail = TransferDetail::on($konek)
                    ->select('transfer_detail.*','transfer.tanggal_transfer','transfer.status','transfer.no_transfer','transfer.kode_lokasi','produk.kode_kategori','produk.nama_produk','transfer.transfer_dari','transfer.transfer_tujuan')
                    ->join('transfer', 'transfer_detail.no_transfer', '=', 'transfer.no_transfer')
                    ->join('produk','transfer_detail.kode_produk', '=', 'produk.id')
                    ->where('transfer.status', $status)
                    ->where('produk.kode_kategori', $kategori)
                    ->where('transfer.kode_lokasi', $get_lokasi)
                    ->whereBetween('transfer.tanggal_transfer', array($tanggal_awal, $tanggal_akhir))
                    ->orderBy('transfer.no_transfer','asc')
                    ->get();
        
                    $lokasi = $get_lokasi;
                    $pdf = PDF::loadView('/admin/laporantransferout/pdf', compact('konek','transferout','transferoutdetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','kategori','lokasi','dt','format_ttd'));

                    $pdf->setPaper('a4', 'landscape');

                    return $pdf->stream('Laporan Transfer Out Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                }
                else if($kategori == 'SEMUA'){
                    $transferout = Transfer::on($konek)->whereBetween('tanggal_transfer', array($tanggal_awal, $tanggal_akhir))->get();

                    $transferoutdetail = TransferDetail::on($konek)
                    ->select('transfer_detail.*','transfer.tanggal_transfer','transfer.status','transfer.no_transfer','transfer.kode_lokasi','produk.kode_kategori','produk.nama_produk','transfer.transfer_dari','transfer.transfer_tujuan')
                    ->join('transfer', 'transfer_detail.no_transfer', '=', 'transfer.no_transfer')
                    ->join('produk','transfer_detail.kode_produk', '=', 'produk.id')
                    ->where('transfer.status', $status)
                    ->where('transfer.kode_lokasi', $get_lokasi)
                    ->whereBetween('transfer.tanggal_transfer', array($tanggal_awal, $tanggal_akhir))
                    ->orderBy('transfer.no_transfer','asc')
                    ->get();
                    
                    $lokasi = $get_lokasi;
                    $pdf = PDF::loadView('/admin/laporantransferout/pdf', compact('konek','transferout','transferoutdetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','kategori','lokasi','dt','format_ttd'));

                    $pdf->setPaper('a4', 'landscape');

                    return $pdf->stream('Laporan Transfer Out Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                }

            }
            else if($tipe == 'PDF' && $status == 'SEMUA'){
                if($kategori != 'SEMUA'){
                    $transferout = Transfer::on($konek)->whereBetween('tanggal_transfer', array($tanggal_awal, $tanggal_akhir))->get();

                    $transferoutdetail = TransferDetail::on($konek)
                    ->select('transfer_detail.*','transfer.tanggal_transfer','transfer.status','transfer.no_transfer','transfer.kode_lokasi','produk.kode_kategori','produk.nama_produk','transfer.transfer_dari','transfer.transfer_tujuan')
                    ->join('transfer', 'transfer_detail.no_transfer', '=', 'transfer.no_transfer')
                    ->join('produk','transfer_detail.kode_produk', '=', 'produk.id')
                    ->where('produk.kode_kategori', $kategori)
                    ->where('transfer.kode_lokasi', $get_lokasi)
                    ->whereBetween('transfer.tanggal_transfer', array($tanggal_awal, $tanggal_akhir))
                    ->orderBy('transfer.no_transfer','asc')
                    ->get();
        
                    $lokasi = $get_lokasi;
                    $pdf = PDF::loadView('/admin/laporantransferout/pdf', compact('konek','transferout','transferoutdetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','kategori','lokasi','dt','format_ttd'));

                    $pdf->setPaper('a4', 'landscape');

                    return $pdf->stream('Laporan Transfer Out Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                }
                else if($kategori == 'SEMUA'){
                    $transferout = Transfer::on($konek)->whereBetween('tanggal_transfer', array($tanggal_awal, $tanggal_akhir))->get();

                    $transferoutdetail = TransferDetail::on($konek)
                    ->select('transfer_detail.*','transfer.tanggal_transfer','transfer.status','transfer.no_transfer','transfer.kode_lokasi','produk.kode_kategori','produk.nama_produk','transfer.transfer_dari','transfer.transfer_tujuan')
                    ->join('transfer', 'transfer_detail.no_transfer', '=', 'transfer.no_transfer')
                    ->join('produk','transfer_detail.kode_produk', '=', 'produk.id')
                    ->where('transfer.kode_lokasi', $get_lokasi)
                    ->whereBetween('transfer.tanggal_transfer', array($tanggal_awal, $tanggal_akhir))
                    ->orderBy('transfer.no_transfer','asc')
                    ->get();
        
                    $lokasi = $get_lokasi;
                    $pdf = PDF::loadView('/admin/laporantransferout/pdf', compact('konek','transferout','transferoutdetail','tanggal_awal','tanggal_akhir','date','ttd','limit3','nama','nama2','kategori','lokasi','dt','format_ttd'));

                    $pdf->setPaper('a4', 'landscape');

                    return $pdf->stream('Laporan Transfer Out Dari Tanggal '.$tanggal_awal.' s/d '.$tanggal_akhir.'.pdf');
                }
            } 
            else if ($tipe == 'excel'){
                return Excel::download(new TransferoutExport($tanggal_awal, $tanggal_akhir, $status, $kategori, $get_lokasi), 'Laporan Transfer Out dari tanggal '.$tanggal_awal.' sd '.$tanggal_akhir.'.xlsx');
            }
        }
    }
}
