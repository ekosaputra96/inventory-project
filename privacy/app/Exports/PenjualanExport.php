<?php
 
namespace App\Exports;
 
use App\Models\tb_item_bulanan;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PenjualanExport implements FromView
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
        }
        return $koneksi;
    }
    
    /**
    * @return \Illuminate\Support\Collection
    */

   	public function __construct(string $awal, string $akhir, string $status, string $get_lokasi, string $kategori, string $lokasi2)
    {
        $this->awal = $awal;
        $this->akhir = $akhir;
        $this->status = $status;
        $this->lokasi = $get_lokasi;
        $this->kategori = $kategori;
        $this->lokasi2 = $lokasi2;
    }

    public function view(): View
    {   
        $konek = self::konek();
        if ($this->lokasi2 != 'SEMUA'){
            if($this->status != 'SEMUA'){
                if($this->status == 'RETUR'){
                    if($this->kategori != 'SEMUA'){
                        return view('/admin/laporanpenjualan/excel', [
                        'data' => PenjualanDetail::on($konek)
                            ->select('penjualan_detail.*','penjualan.tanggal_penjualan','penjualan.status','penjualan.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                            ->join('penjualan', 'penjualan_detail.no_penjualan', '=', 'penjualan.no_penjualan')
                            ->join('produk','penjualan_detail.kode_produk', '=', 'produk.id')
                            ->whereBetween('penjualan.tanggal_penjualan', array($this->awal, $this->akhir))
                            ->where('penjualan_detail.qty_retur','>',0)
                            ->where('produk.kode_kategori', $this->kategori)
                            ->where('penjualan.kode_lokasi', $this->lokasi)
                            ->get()
                        ]);  
                    }
                    else{
                        return view('/admin/laporanpenjualan/excel', [
                        'data' => PenjualanDetail::on($konek)
                            ->select('penjualan_detail.*','penjualan.tanggal_penjualan','penjualan.status','penjualan.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                            ->join('penjualan', 'penjualan_detail.no_penjualan', '=', 'penjualan.no_penjualan')
                            ->join('produk','penjualan_detail.kode_produk', '=', 'produk.id')
                            ->whereBetween('penjualan.tanggal_penjualan', array($this->awal, $this->akhir))
                            ->where('penjualan_detail.qty_retur','>',0)
                            ->where('penjualan.kode_lokasi', $this->lokasi)
                            ->get()
                        ]);  
                    }
                }else{
                    if($this->kategori != 'SEMUA'){
                        return view('/admin/laporanpenjualan/excel', [
                        'data' => PenjualanDetail::on($konek)
                            ->select('penjualan_detail.*','penjualan.tanggal_penjualan','penjualan.status','penjualan.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                            ->join('penjualan', 'penjualan_detail.no_penjualan', '=', 'penjualan.no_penjualan')
                            ->join('produk','penjualan_detail.kode_produk', '=', 'produk.id')
                            ->whereBetween('penjualan.tanggal_penjualan', array($this->awal, $this->akhir))
                            ->where('penjualan.status', $this->status)
                            ->where('penjualan_detail.qty_retur',0)
                            ->where('produk.kode_kategori', $this->kategori)
                            ->where('penjualan.kode_lokasi', $this->lokasi)
                            ->get()
                        ]);  
                    }
                    else{
                        return view('/admin/laporanpenjualan/excel', [
                        'data' => PenjualanDetail::on($konek)
                            ->select('penjualan_detail.*','penjualan.tanggal_penjualan','penjualan.status','penjualan.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                            ->join('penjualan', 'penjualan_detail.no_penjualan', '=', 'penjualan.no_penjualan')
                            ->join('produk','penjualan_detail.kode_produk', '=', 'produk.id')
                            ->whereBetween('penjualan.tanggal_penjualan', array($this->awal, $this->akhir))
                            ->where('penjualan.status', $this->status)
                            ->where('penjualan_detail.qty_retur',0)
                            ->where('penjualan.kode_lokasi', $this->lokasi)
                            ->get()
                        ]);  
                    }
                }
            }else{
                if($this->kategori != 'SEMUA'){
                    return view('/admin/laporanpenjualan/excel', [
                    'data' => PenjualanDetail::on($konek)
                        ->select('penjualan_detail.*','penjualan.tanggal_penjualan','penjualan.status','penjualan.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                        ->join('penjualan', 'penjualan_detail.no_penjualan', '=', 'penjualan.no_penjualan')
                        ->join('produk','penjualan_detail.kode_produk', '=', 'produk.id')
                        ->where('penjualan.kode_lokasi', $this->lokasi)
                        ->where('produk.kode_kategori', $this->kategori)
                        ->whereBetween('penjualan.tanggal_penjualan', array($this->awal, $this->akhir))
                        ->get()
                    ]);  
                }
                else{
                    return view('/admin/laporanpenjualan/excel', [
                    'data' => PenjualanDetail::on($konek)
                        ->select('penjualan_detail.*','penjualan.tanggal_penjualan','penjualan.status','penjualan.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                        ->join('penjualan', 'penjualan_detail.no_penjualan', '=', 'penjualan.no_penjualan')
                        ->join('produk','penjualan_detail.kode_produk', '=', 'produk.id')
                        ->where('penjualan.kode_lokasi', $this->lokasi)
                        ->whereBetween('penjualan.tanggal_penjualan', array($this->awal, $this->akhir))
                        ->get()
                    ]); 
                }
            }
        }else {
            if($this->status != 'SEMUA'){
                if($this->status == 'RETUR'){
                    if($this->kategori != 'SEMUA'){
                        return view('/admin/laporanpenjualan/excel', [
                        'data' => PenjualanDetail::on($konek)
                            ->select('penjualan_detail.*','penjualan.tanggal_penjualan','penjualan.status','penjualan.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                            ->join('penjualan', 'penjualan_detail.no_penjualan', '=', 'penjualan.no_penjualan')
                            ->join('produk','penjualan_detail.kode_produk', '=', 'produk.id')
                            ->whereBetween('penjualan.tanggal_penjualan', array($this->awal, $this->akhir))
                            ->where('penjualan_detail.qty_retur','>',0)
                            ->where('produk.kode_kategori', $this->kategori)
                            ->get()
                        ]);  
                    }
                    else{
                        return view('/admin/laporanpenjualan/excel', [
                        'data' => PenjualanDetail::on($konek)
                            ->select('penjualan_detail.*','penjualan.tanggal_penjualan','penjualan.status','penjualan.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                            ->join('penjualan', 'penjualan_detail.no_penjualan', '=', 'penjualan.no_penjualan')
                            ->join('produk','penjualan_detail.kode_produk', '=', 'produk.id')
                            ->whereBetween('penjualan.tanggal_penjualan', array($this->awal, $this->akhir))
                            ->where('penjualan_detail.qty_retur','>',0)
                            ->get()
                        ]);  
                    }
                }else{
                    if($this->kategori != 'SEMUA'){
                        return view('/admin/laporanpenjualan/excel', [
                        'data' => PenjualanDetail::on($konek)
                            ->select('penjualan_detail.*','penjualan.tanggal_penjualan','penjualan.status','penjualan.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                            ->join('penjualan', 'penjualan_detail.no_penjualan', '=', 'penjualan.no_penjualan')
                            ->join('produk','penjualan_detail.kode_produk', '=', 'produk.id')
                            ->whereBetween('penjualan.tanggal_penjualan', array($this->awal, $this->akhir))
                            ->where('penjualan_detail.qty_retur',0)
                            ->where('penjualan.status', $this->status)
                            ->where('produk.kode_kategori', $this->kategori)
                            ->get()
                        ]);  
                    }
                    else{
                        return view('/admin/laporanpenjualan/excel', [
                        'data' => PenjualanDetail::on($konek)
                            ->select('penjualan_detail.*','penjualan.tanggal_penjualan','penjualan.status','penjualan.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                            ->join('penjualan', 'penjualan_detail.no_penjualan', '=', 'penjualan.no_penjualan')
                            ->join('produk','penjualan_detail.kode_produk', '=', 'produk.id')
                            ->whereBetween('penjualan.tanggal_penjualan', array($this->awal, $this->akhir))
                            ->where('penjualan_detail.qty_retur',0)
                            ->where('penjualan.status', $this->status)
                            ->get()
                        ]);  
                    }
                }
            }else{
                if($this->kategori != 'SEMUA'){
                    return view('/admin/laporanpenjualan/excel', [
                    'data' => PenjualanDetail::on($konek)
                        ->select('penjualan_detail.*','penjualan.tanggal_penjualan','penjualan.status','penjualan.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                        ->join('penjualan', 'penjualan_detail.no_penjualan', '=', 'penjualan.no_penjualan')
                        ->join('produk','penjualan_detail.kode_produk', '=', 'produk.id')
                        ->where('produk.kode_kategori', $this->kategori)
                        ->whereBetween('penjualan.tanggal_penjualan', array($this->awal, $this->akhir))
                        ->get()
                    ]);  
                }
                else{
                    return view('/admin/laporanpenjualan/excel', [
                    'data' => PenjualanDetail::on($konek)
                        ->select('penjualan_detail.*','penjualan.tanggal_penjualan','penjualan.status','penjualan.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                        ->join('penjualan', 'penjualan_detail.no_penjualan', '=', 'penjualan.no_penjualan')
                        ->join('produk','penjualan_detail.kode_produk', '=', 'produk.id')
                        ->whereBetween('penjualan.tanggal_penjualan', array($this->awal, $this->akhir))
                        ->get()
                    ]); 
                }
            }
        }
        
    }
}