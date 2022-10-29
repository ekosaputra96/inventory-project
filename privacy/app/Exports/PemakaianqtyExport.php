<?php
 
namespace App\Exports;
 
use App\Models\tb_item_bulanan;
use App\Models\Pemakaian;
use App\Models\PemakaianDetail;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use DB;

class PemakaianqtyExport implements FromView
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
        }else if ($compa == '22'){
            $koneksi = 'mysqlskt';
        }else if ($compa == '03'){
            $koneksi = 'mysqlemkl';   
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

    public function __construct(string $awal, string $akhir, string $kategori, string $get_lokasi, string $pilih, string $produk)
    {
        $this->awal = $awal;
        $this->akhir = $akhir;
        $this->kategori = $kategori;
        $this->produk = $produk;
        $this->lokasi = $get_lokasi;
        $this->pilih = $pilih;
    }

    public function view(): View
    {   
        $konek = self::konek();
        if($this->pilih == 'Kategori'){
            if($this->lokasi != 'SEMUA'){
                if($this->kategori != 'SEMUA'){
                    return view('/admin/laporanpemakaianqty/excel', [
                    'data' => PemakaianDetail::on($konek)
                        ->select('pemakaian_detail.*','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk', DB::raw("SUM(pemakaian_detail.qty) as qtys"))
                        ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                        ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                        ->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
                        ->where('produk.kode_kategori', $this->kategori)
                        ->where('pemakaian.kode_lokasi', $this->lokasi)
                        ->groupBy('pemakaian_detail.kode_produk')
                        ->orderBy('qtys','desc')
                        ->get()
                    ]); 
                }
                else if($this->kategori == 'SEMUA'){
                    return view('/admin/laporanpemakaianqty/excel', [
                    'data' => PemakaianDetail::on($konek)
                        ->select('pemakaian_detail.*','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk', DB::raw("SUM(pemakaian_detail.qty) as qtys"))
                        ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                        ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                        ->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
                        ->where('pemakaian.kode_lokasi', $this->lokasi)
                        ->groupBy('pemakaian_detail.kode_produk')
                        ->orderBy('qtys','desc')
                        ->get()
                    ]); 
                }
            }
            else{
                if($this->kategori != 'SEMUA'){
                    return view('/admin/laporanpemakaianqty/excel', [
                    'data' => PemakaianDetail::on($konek)
                        ->select('pemakaian_detail.*','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk', DB::raw("SUM(pemakaian_detail.qty) as qtys"))
                        ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                        ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                        ->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
                        ->where('produk.kode_kategori', $this->kategori)
                        ->groupBy('pemakaian_detail.kode_produk')
                        ->orderBy('qtys','desc')
                        ->get()
                    ]); 
                }
                else if($this->kategori == 'SEMUA'){
                    return view('/admin/laporanpemakaianqty/excel', [
                    'data' => PemakaianDetail::on($konek)
                        ->select('pemakaian_detail.*','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk', DB::raw("SUM(pemakaian_detail.qty) as qtys"))
                        ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                        ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                        ->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
                        ->groupBy('pemakaian_detail.kode_produk')
                        ->orderBy('qtys','desc')
                        ->get()
                    ]); 
                }
            }
        }else{
            if($this->lokasi != 'SEMUA'){
                    return view('/admin/laporanpemakaianqty/excel2', [
                    'data' => PemakaianDetail::on($konek)
                        ->select('pemakaian_detail.*','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk', DB::raw("SUM(pemakaian_detail.qty) as qtys"))
                        ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                        ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                        ->where('produk.id', $this->produk)
                        ->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
                        ->where('pemakaian.kode_lokasi', $this->lokasi)
                        ->groupBy('pemakaian_detail.kode_produk')
                        ->orderBy('qtys','desc')
                        ->get()
                    ]); 
            }
            else{
                    return view('/admin/laporanpemakaianqty/excel2', [
                    'data' => PemakaianDetail::on($konek)
                        ->select('pemakaian_detail.*','pemakaian.kode_lokasi','produk.kode_kategori','produk.nama_produk', DB::raw("SUM(pemakaian_detail.qty) as qtys"))
                        ->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
                        ->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
                        ->where('produk.id', $this->produk)
                        ->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
                        ->groupBy('pemakaian_detail.kode_produk')
                        ->orderBy('qtys','desc')
                        ->get()
                    ]); 
            }
        }
    }
}