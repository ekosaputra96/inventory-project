<?php
 
namespace App\Exports;
 
use App\Models\tb_item_bulanan;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PembelianExport implements FromView
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

   	public function __construct(string $awal, string $akhir, string $status, string $kategori, string $get_lokasi, string $jenis)
    {
        $this->awal = $awal;
        $this->akhir = $akhir;
        $this->status = $status;
        $this->kategori = $kategori;
        $this->lokasi = $get_lokasi;
        $this->jenis = $jenis;
    }

    public function view(): View
    {   
        $konek = self::konek();
        if($this->jenis == 'Stock'){
            if($this->status != 'SEMUA'){
                if($this->kategori != 'SEMUA'){
                    return view('/admin/laporanpembelian/excel', [
                    'data' => PembelianDetail::on($konek)
                        ->with('vendor')
                        ->select('pembelian_detail.*','pembelian.*','produk.kode_kategori','produk.nama_produk')
                        ->join('pembelian', 'pembelian_detail.no_pembelian', '=', 'pembelian.no_pembelian')
                        ->join('produk','pembelian_detail.kode_produk', '=', 'produk.id')
                        ->whereBetween('pembelian.tanggal_pembelian', array($this->awal, $this->akhir))
                        ->where('pembelian.status', $this->status)
                        ->where('pembelian.jenis_po', $this->jenis)
                        ->where('produk.kode_kategori', $this->kategori)
                        ->where('pembelian.kode_lokasi', $this->lokasi)
                        ->orderBy('pembelian.kode_vendor')
                        ->orderBy('pembelian.no_pembelian','asc')
                        ->get(),
                    'jenis'=>$this->jenis,
                    ]);  
                }
                else if($this->kategori == 'SEMUA'){
                    return view('/admin/laporanpembelian/excel', [
                    'data' => PembelianDetail::on($konek)
                        ->with('vendor')
                        ->select('pembelian_detail.*','pembelian.*','produk.kode_kategori','produk.nama_produk')
                        ->join('pembelian', 'pembelian_detail.no_pembelian', '=', 'pembelian.no_pembelian')
                        ->join('produk','pembelian_detail.kode_produk', '=', 'produk.id')
                        ->whereBetween('pembelian.tanggal_pembelian', array($this->awal, $this->akhir))
                        ->where('pembelian.status', $this->status)
                        ->where('pembelian.jenis_po', $this->jenis)
                        ->where('pembelian.kode_lokasi', $this->lokasi)
                        ->orderBy('pembelian.kode_vendor')
                        ->orderBy('pembelian.no_pembelian','asc')
                        ->get(),
                    'jenis'=>$this->jenis,
                    ]);  
                }
            }
            else{
                if($this->kategori != 'SEMUA'){
                    return view('/admin/laporanpembelian/excel', [
                    'data' => PembelianDetail::on($konek)
                        ->with('vendor')
                        ->select('pembelian_detail.*','pembelian.*','produk.kode_kategori','produk.nama_produk')
                        ->join('pembelian', 'pembelian_detail.no_pembelian', '=', 'pembelian.no_pembelian')
                        ->join('produk','pembelian_detail.kode_produk', '=', 'produk.id')
                        ->whereBetween('pembelian.tanggal_pembelian', array($this->awal, $this->akhir))
                        ->where('produk.kode_kategori', $this->kategori)
                        ->where('pembelian.jenis_po', $this->jenis)
                        ->where('pembelian.kode_lokasi', $this->lokasi)
                        ->orderBy('pembelian.kode_vendor')
                        ->orderBy('pembelian.no_pembelian','asc')
                        ->get(),
                    'jenis'=>$this->jenis,
                    ]);  
                }
                else if($this->kategori == 'SEMUA'){
                    return view('/admin/laporanpembelian/excel', [
                    'data' => PembelianDetail::on($konek)
                        ->with('vendor')
                        ->select('pembelian_detail.*','pembelian.*','produk.kode_kategori','produk.nama_produk')
                        ->join('pembelian', 'pembelian_detail.no_pembelian', '=', 'pembelian.no_pembelian')
                        ->join('produk','pembelian_detail.kode_produk', '=', 'produk.id')
                        ->where('pembelian.kode_lokasi', $this->lokasi)
                        ->where('pembelian.jenis_po', $this->jenis)
                        ->whereBetween('pembelian.tanggal_pembelian', array($this->awal, $this->akhir))
                        ->orderBy('pembelian.kode_vendor')
                        ->orderBy('pembelian.no_pembelian','asc')
                        ->get(),
                    'jenis'=>$this->jenis,
                    ]);  
                }
            }
        }
        else if($this->jenis == 'Non-Stock'){
            if($this->status != 'SEMUA'){
                return view('/admin/laporanpembelian/excel', [
                'data' => PembelianDetail::on($konek)
                    ->with('vendor','nonstock')
                    ->select('pembelian_detail.*','pembelian.*')
                    ->join('pembelian', 'pembelian_detail.no_pembelian', '=', 'pembelian.no_pembelian')
                    ->where('pembelian.jenis_po', $this->jenis)
                    ->where('pembelian.status', $this->status)
                    ->where('pembelian.kode_lokasi', $this->lokasi)
                    ->whereBetween('pembelian.tanggal_pembelian', array($this->awal, $this->akhir))
                    ->orderBy('pembelian.kode_vendor')
                    ->orderBy('pembelian.no_pembelian','asc')
                    ->get(),
                'jenis'=>$this->jenis,
                ]);  
            }else{
                return view('/admin/laporanpembelian/excel', [
                'data' => PembelianDetail::on($konek)
                    ->with('vendor','nonstock')
                    ->select('pembelian_detail.*','pembelian.*')
                    ->join('pembelian', 'pembelian_detail.no_pembelian', '=', 'pembelian.no_pembelian')
                    ->where('pembelian.jenis_po', $this->jenis)
                    ->where('pembelian.kode_lokasi', $this->lokasi)
                    ->whereBetween('pembelian.tanggal_pembelian', array($this->awal, $this->akhir))
                    ->orderBy('pembelian.kode_vendor')
                    ->orderBy('pembelian.no_pembelian','asc')
                    ->get(),
                'jenis'=>$this->jenis,
                ]); 
            }
        }
        else{
            if($this->status != 'SEMUA'){
                return view('/admin/laporanpembelian/excel', [
                'data' => PembelianDetail::on($konek)
                    ->with('vendor','jasa')
                    ->select('pembelian_detail.*','pembelian.*')
                    ->join('pembelian', 'pembelian_detail.no_pembelian', '=', 'pembelian.no_pembelian')
                    ->where('pembelian.jenis_po', $this->jenis)
                    ->where('pembelian.status', $this->status)
                    ->where('pembelian.kode_lokasi', $this->lokasi)
                    ->whereBetween('pembelian.tanggal_pembelian', array($this->awal, $this->akhir))
                    ->orderBy('pembelian.kode_vendor')
                    ->orderBy('pembelian.no_pembelian','asc')
                    ->get(),
                'jenis'=>$this->jenis,
                ]);  
            }else{
                return view('/admin/laporanpembelian/excel', [
                'data' => PembelianDetail::on($konek)
                    ->with('vendor','jasa')
                    ->select('pembelian_detail.*','pembelian.*')
                    ->join('pembelian', 'pembelian_detail.no_pembelian', '=', 'pembelian.no_pembelian')
                    ->where('pembelian.jenis_po', $this->jenis)
                    ->where('pembelian.kode_lokasi', $this->lokasi)
                    ->whereBetween('pembelian.tanggal_pembelian', array($this->awal, $this->akhir))
                    ->orderBy('pembelian.kode_vendor')
                    ->orderBy('pembelian.no_pembelian','asc')
                    ->get(),
                'jenis'=>$this->jenis,
                ]); 
            }
        }
    }
}