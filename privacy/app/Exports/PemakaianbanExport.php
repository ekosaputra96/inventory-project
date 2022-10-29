<?php
 
namespace App\Exports;
 
use App\Models\tb_item_bulanan;
use App\Models\Pemakaianban;
use App\Models\PemakaianbanDetail;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use DB;

class PemakaianbanExport implements FromView
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

    /**
    * @return \Illuminate\Support\Collection
    */

   	public function __construct(string $awal, string $akhir, string $status, string $tipe_pemakaian, string $get_lokasi, string $aset, string $asetalat)
    {
        $this->awal = $awal;
        $this->akhir = $akhir;
        $this->status = $status;
        $this->tipe_pemakaian = $tipe_pemakaian;
        $this->lokasi = $get_lokasi;
        $this->aset = $aset;
        $this->asetalat = $asetalat;
    }

    public function view(): View
    {   
        $konek = self::konek();
        if($this->lokasi != 'SEMUA'){
            if($this->status != 'SEMUA' && $this->tipe_pemakaian == 'Alat'){
                if ($this->asetalat != 'SEMUA'){
                    return view('/admin/laporanpemakaianban/excel_alat', [
                        'data' => PemakaianbanDetail::on($konek)
                            ->select('pemakaianban_detail.*','pemakaianban.*','produk.nama_produk','alat.nama_alat','alat.no_asset_alat')
                            ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                            ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('alat','pemakaianban.kode_alat', '=', 'alat.kode_alat')
                            ->where('pemakaianban.type', $this->tipe_pemakaian)
                            ->where('pemakaianban.no_asset_alat', $this->asetalat)
                            ->where('pemakaianban.status', $this->status)
                            ->where('pemakaianban.kode_lokasi', $this->lokasi)
                            ->whereBetween('pemakaianban.tanggal_pemakaianban', array($this->awal, $this->akhir))
                            ->get()
                    ]);  
                }
                else{
                    return view('/admin/laporanpemakaianban/excel_alat', [
                        'data' => PemakaianbanDetail::on($konek)
                            ->select('pemakaianban_detail.*','pemakaianban.*','produk.nama_produk','alat.nama_alat','alat.no_asset_alat')
                            ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                            ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('alat','pemakaianban.kode_alat', '=', 'alat.kode_alat')
                            ->where('pemakaianban.type', $this->tipe_pemakaian)
                            ->where('pemakaianban.status', $this->status)
                            ->where('pemakaianban.kode_lokasi', $this->lokasi)
                            ->whereBetween('pemakaianban.tanggal_pemakaianban', array($this->awal, $this->akhir))
                            ->get()
                    ]);
                }
            }
            else if($this->status == 'SEMUA' && $this->tipe_pemakaian == 'Alat'){
                if ($this->asetalat != 'SEMUA'){
                    return view('/admin/laporanpemakaianban/excel_alat', [
                        'data' => PemakaianbanDetail::on($konek)
                            ->select('pemakaianban_detail.*','pemakaianban.*','produk.nama_produk','alat.nama_alat','alat.no_asset_alat')
                            ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                            ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('alat','pemakaianban.kode_alat', '=', 'alat.kode_alat')
                            ->where('pemakaianban.type', $this->tipe_pemakaian)
                            ->where('pemakaianban.no_asset_alat', $this->asetalat)
                            ->where('pemakaianban.kode_lokasi', $this->lokasi)
                            ->whereBetween('pemakaianban.tanggal_pemakaianban', array($this->awal, $this->akhir))
                            ->get()
                    ]);  
                }else{
                    return view('/admin/laporanpemakaianban/excel_alat', [
                        'data' => PemakaianbanDetail::on($konek)
                            ->select('pemakaianban_detail.*','pemakaianban.*','produk.nama_produk','alat.nama_alat','alat.no_asset_alat')
                            ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                            ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('alat','pemakaianban.kode_alat', '=', 'alat.kode_alat')
                            ->where('pemakaianban.type', $this->tipe_pemakaian)
                            ->where('pemakaianban.kode_lokasi', $this->lokasi)
                            ->whereBetween('pemakaianban.tanggal_pemakaianban', array($this->awal, $this->akhir))
                            ->get()
                    ]);  
                }
            }
            else if($this->status != 'SEMUA' && $this->tipe_pemakaian == 'Mobil'){
                if ($this->aset != 'SEMUA'){
                    return view('/admin/laporanpemakaianban/excel_mobil', [
                        'data' => PemakaianbanDetail::on($konek)
                            ->select('pemakaianban_detail.*','pemakaianban.*','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                            ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                            ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils', 'pemakaianban.kode_mobil', '=', 'mobils.kode_mobil')
                            ->where('pemakaianban.type', $this->tipe_pemakaian)
                            ->where('pemakaianban.no_asset_mobil', $this->aset)
                            ->where('pemakaianban.status', $this->status)
                            ->where('pemakaianban.kode_lokasi', $this->lokasi)
                            ->whereBetween('pemakaianban.tanggal_pemakaianban', array($this->awal, $this->akhir))
                            ->get()
                    ]);  
                }else{
                    return view('/admin/laporanpemakaianban/excel_mobil', [
                        'data' => PemakaianbanDetail::on($konek)
                            ->select('pemakaianban_detail.*','pemakaianban.*','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                            ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                            ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils', 'pemakaianban.kode_mobil', '=', 'mobils.kode_mobil')
                            ->where('pemakaianban.type', $this->tipe_pemakaian)
                            ->where('pemakaianban.status', $this->status)
                            ->where('pemakaianban.kode_lokasi', $this->lokasi)
                            ->whereBetween('pemakaianban.tanggal_pemakaianban', array($this->awal, $this->akhir))
                            ->get()
                    ]); 
                }
            }
            else if($this->status == 'SEMUA' && $this->tipe_pemakaian == 'Mobil'){
                if ($this->aset != 'SEMUA'){
                    return view('/admin/laporanpemakaianban/excel_mobil', [
                        'data' => PemakaianbanDetail::on($konek)
                            ->select('pemakaianban_detail.*','pemakaianban.*','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                            ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                            ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils', 'pemakaianban.kode_mobil', '=', 'mobils.kode_mobil')
                            ->where('pemakaianban.type', $this->tipe_pemakaian)
                            ->where('pemakaianban.no_asset_mobil', $this->aset)
                            ->where('pemakaianban.kode_lokasi', $this->lokasi)
                            ->whereBetween('pemakaianban.tanggal_pemakaianban', array($this->awal, $this->akhir))
                            ->get()
                    ]);
                }else{
                    return view('/admin/laporanpemakaianban/excel_mobil', [
                        'data' => PemakaianbanDetail::on($konek)
                            ->select('pemakaianban_detail.*','pemakaianban.*','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                            ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                            ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils', 'pemakaianban.kode_mobil', '=', 'mobils.kode_mobil')
                            ->where('pemakaianban.type', $this->tipe_pemakaian)
                            ->where('pemakaianban.kode_lokasi', $this->lokasi)
                            ->whereBetween('pemakaianban.tanggal_pemakaianban', array($this->awal, $this->akhir))
                            ->get()
                    ]);
                }
            }
            else if($this->status != 'SEMUA' && $this->tipe_pemakaian == 'SEMUA'){
                return view('/admin/laporanpemakaianban/excel', [
                    'data' => PemakaianbanDetail::on($konek)
                        ->select('pemakaianban_detail.*','pemakaianban.*','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil','alat.nama_alat','alat.no_asset_alat')
                        ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                        ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                        ->leftjoin('mobils', 'pemakaianban.kode_mobil', '=', 'mobils.kode_mobil')
                        ->leftjoin('alat','pemakaianban.kode_alat', '=', 'alat.kode_alat')
                        ->where('pemakaianban.kode_lokasi', $this->lokasi)
                        ->whereBetween('pemakaianban.tanggal_pemakaianban', array($this->awal, $this->akhir))
                        ->get()
                ]);  
            }
            else if($this->status == 'SEMUA' && $this->tipe_pemakaian == 'SEMUA'){
                return view('/admin/laporanpemakaianban/excel', [
                    'data' => PemakaianbanDetail::on($konek)
                        ->select('pemakaianban_detail.*','pemakaianban.*','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil','alat.nama_alat','alat.no_asset_alat')
                        ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                        ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                        ->leftjoin('mobils', 'pemakaianban.kode_mobil', '=', 'mobils.kode_mobil')
                        ->leftjoin('alat','pemakaianban.kode_alat', '=', 'alat.kode_alat')
                        ->where('pemakaianban.kode_lokasi', $this->lokasi)
                        ->whereBetween('pemakaianban.tanggal_pemakaianban', array($this->awal, $this->akhir))
                        ->get()
                ]);
            }
        }
        else{
            if($this->status != 'SEMUA' && $this->tipe_pemakaian == 'Alat'){
                if ($this->asetalat != 'SEMUA'){
                    return view('/admin/laporanpemakaianban/excel_alat', [
                        'data' => PemakaianbanDetail::on($konek)
                            ->select('pemakaianban_detail.*','pemakaianban.*','produk.nama_produk','alat.nama_alat','alat.no_asset_alat')
                            ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                            ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('alat','pemakaianban.kode_alat', '=', 'alat.kode_alat')
                            ->where('pemakaianban.type', $this->tipe_pemakaian)
                            ->where('pemakaianban.no_asset_alat', $this->asetalat)
                            ->where('pemakaianban.status', $this->status)
                            ->whereBetween('pemakaianban.tanggal_pemakaianban', array($this->awal, $this->akhir))
                            ->get()
                    ]);  
                }else{
                    return view('/admin/laporanpemakaianban/excel_alat', [
                        'data' => PemakaianbanDetail::on($konek)
                            ->select('pemakaianban_detail.*','pemakaianban.*','produk.nama_produk','alat.nama_alat','alat.no_asset_alat')
                            ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                            ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('alat','pemakaianban.kode_alat', '=', 'alat.kode_alat')
                            ->where('pemakaianban.type', $this->tipe_pemakaian)
                            ->where('pemakaianban.status', $this->status)
                            ->whereBetween('pemakaianban.tanggal_pemakaianban', array($this->awal, $this->akhir))
                            ->get()
                    ]);  
                }
            }
            else if($this->status == 'SEMUA' && $this->tipe_pemakaian == 'Alat'){
                if ($this->asetalat != 'SEMUA'){
                    return view('/admin/laporanpemakaianban/excel_alat', [
                        'data' => PemakaianbanDetail::on($konek)
                            ->select('pemakaianban_detail.*','pemakaianban.*','produk.nama_produk','alat.nama_alat','alat.no_asset_alat')
                            ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                            ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('alat','pemakaianban.kode_alat', '=', 'alat.kode_alat')
                            ->where('pemakaianban.type', $this->tipe_pemakaian)
                            ->where('pemakaianban.no_asset_alat', $this->asetalat)
                            ->whereBetween('pemakaianban.tanggal_pemakaianban', array($this->awal, $this->akhir))
                            ->get()
                    ]);  
                }else{
                    return view('/admin/laporanpemakaianban/excel_alat', [
                        'data' => PemakaianbanDetail::on($konek)
                            ->select('pemakaianban_detail.*','pemakaianban.*','produk.nama_produk','alat.nama_alat','alat.no_asset_alat')
                            ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                            ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('alat','pemakaianban.kode_alat', '=', 'alat.kode_alat')
                            ->where('pemakaianban.type', $this->tipe_pemakaian)
                            ->whereBetween('pemakaianban.tanggal_pemakaianban', array($this->awal, $this->akhir))
                            ->get()
                    ]);  
                }
            }
            else if($this->status != 'SEMUA' && $this->tipe_pemakaian == 'Mobil'){
                if ($this->aset != 'SEMUA'){
                    return view('/admin/laporanpemakaianban/excel_mobil', [
                        'data' => PemakaianbanDetail::on($konek)
                            ->select('pemakaianban_detail.*','pemakaianban.*','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                            ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                            ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils', 'pemakaianban.kode_mobil', '=', 'mobils.kode_mobil')
                            ->where('pemakaianban.type', $this->tipe_pemakaian)
                            ->where('pemakaianban.no_asset_mobil', $this->aset)
                            ->where('pemakaianban.status', $this->status)
                            ->whereBetween('pemakaianban.tanggal_pemakaianban', array($this->awal, $this->akhir))
                            ->get()
                    ]);  
                }else{
                    return view('/admin/laporanpemakaianban/excel_mobil', [
                        'data' => PemakaianbanDetail::on($konek)
                            ->select('pemakaianban_detail.*','pemakaianban.*','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                            ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                            ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils', 'pemakaianban.kode_mobil', '=', 'mobils.kode_mobil')
                            ->where('pemakaianban.type', $this->tipe_pemakaian)
                            ->where('pemakaianban.status', $this->status)
                            ->whereBetween('pemakaianban.tanggal_pemakaianban', array($this->awal, $this->akhir))
                            ->get()
                    ]);  
                }
            }
            else if($this->status == 'SEMUA' && $this->tipe_pemakaian == 'Mobil'){
                if ($this->aset != 'SEMUA'){
                    return view('/admin/laporanpemakaianban/excel_mobil', [
                        'data' => PemakaianbanDetail::on($konek)
                            ->select('pemakaianban_detail.*','pemakaianban.*','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                            ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                            ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils', 'pemakaianban.kode_mobil', '=', 'mobils.kode_mobil')
                            ->where('pemakaianban.no_asset_mobil', $this->aset)
                            ->where('pemakaianban.type', $this->tipe_pemakaian)
                            ->whereBetween('pemakaianban.tanggal_pemakaianban', array($this->awal, $this->akhir))
                            ->get()
                    ]);
                }else{
                    return view('/admin/laporanpemakaianban/excel_mobil', [
                        'data' => PemakaianbanDetail::on($konek)
                            ->select('pemakaianban_detail.*','pemakaianban.*','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
                            ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                            ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                            ->leftjoin('mobils', 'pemakaianban.kode_mobil', '=', 'mobils.kode_mobil')
                            ->where('pemakaianban.type', $this->tipe_pemakaian)
                            ->whereBetween('pemakaianban.tanggal_pemakaianban', array($this->awal, $this->akhir))
                            ->get()
                    ]);
                }
            }
            else if($this->status != 'SEMUA' && $this->tipe_pemakaian == 'SEMUA'){
                return view('/admin/laporanpemakaianban/excel', [
                    'data' => PemakaianbanDetail::on($konek)
                        ->select('pemakaianban_detail.*','pemakaianban.*','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil','alat.nama_alat','alat.no_asset_alat')
                        ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                        ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                        ->leftjoin('mobils', 'pemakaianban.kode_mobil', '=', 'mobils.kode_mobil')
                        ->leftjoin('alat','pemakaianban.kode_alat', '=', 'alat.kode_alat')
                        ->whereBetween('pemakaianban.tanggal_pemakaianban', array($this->awal, $this->akhir))
                        ->get()
                ]);  
            }
            else if($this->status == 'SEMUA' && $this->tipe_pemakaian == 'SEMUA'){
                return view('/admin/laporanpemakaianban/excel', [
                    'data' => PemakaianbanDetail::on($konek)
                        ->select('pemakaianban_detail.*','pemakaianban.*','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil','alat.nama_alat','alat.no_asset_alat')
                        ->join('pemakaianban', 'pemakaianban_detail.no_pemakaianban', '=', 'pemakaianban.no_pemakaianban')
                        ->join('produk','pemakaianban_detail.kode_produk', '=', 'produk.id')
                        ->leftjoin('mobils', 'pemakaianban.kode_mobil', '=', 'mobils.kode_mobil')
                        ->leftjoin('alat','pemakaianban.kode_alat', '=', 'alat.kode_alat')
                        ->whereBetween('pemakaianban.tanggal_pemakaianban', array($this->awal, $this->akhir))
                        ->get()
                ]);
            }
        }
    }
}