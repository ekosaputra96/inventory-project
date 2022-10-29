<?php
 
namespace App\Exports;
 
use App\Models\tb_item_bulanan;
use App\Models\Penerimaan;
use App\Models\PenerimaanDetail;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use DB;

class PenerimaanExport implements FromView
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

   	public function __construct(string $awal, string $akhir, string $status, string $kategori, string $get_lokasi)
    {
        $this->awal = $awal;
        $this->akhir = $akhir;
        $this->status = $status;
        $this->kategori = $kategori;
        $this->lokasi = $get_lokasi;
    }

    public function view(): View
    {   
        $konek = self::konek();
        if($this->status != 'SEMUA'){
            if($this->status == 'RETUR'){
                if($this->kategori != 'SEMUA'){
                    return view('/admin/laporanpenerimaan/excel', [
                    'data' => PenerimaanDetail::on($konek)
                        ->select('penerimaan_detail.*','penerimaan.tanggal_penerimaan','penerimaan.status','penerimaan.kode_lokasi','penerimaan.no_pembelian','produk.kode_kategori','produk.nama_produk')
                        ->join('penerimaan', 'penerimaan_detail.no_penerimaan', '=', 'penerimaan.no_penerimaan')
                        ->join('produk','penerimaan_detail.kode_produk', '=', 'produk.id')
                        ->whereBetween('penerimaan.tanggal_penerimaan', array($this->awal, $this->akhir))
                        ->where('penerimaan_detail.qty_retur','>',0)
                        ->where('produk.kode_kategori', $this->kategori)
                        ->where('penerimaan.kode_lokasi', $this->lokasi)
                        ->get()
                    ]);
                }
                else if($this->kategori == 'SEMUA'){
                    return view('/admin/laporanpenerimaan/excel', [
                    'data' => PenerimaanDetail::on($konek)
                        ->select('penerimaan_detail.*','penerimaan.tanggal_penerimaan','penerimaan.status','penerimaan.kode_lokasi','penerimaan.no_pembelian','produk.kode_kategori','produk.nama_produk')
                        ->join('penerimaan', 'penerimaan_detail.no_penerimaan', '=', 'penerimaan.no_penerimaan')
                        ->join('produk','penerimaan_detail.kode_produk', '=', 'produk.id')
                        ->whereBetween('penerimaan.tanggal_penerimaan', array($this->awal, $this->akhir))
                        ->where('penerimaan_detail.qty_retur','>',0)
                        ->where('penerimaan.kode_lokasi', $this->lokasi)
                        ->get()
                    ]);
                }
            }else{
                if($this->kategori != 'SEMUA'){
                    return view('/admin/laporanpenerimaan/excel', [
                    'data' => PenerimaanDetail::on($konek)
                        ->select('penerimaan_detail.*','penerimaan.tanggal_penerimaan','penerimaan.status','penerimaan.kode_lokasi','penerimaan.no_pembelian','produk.kode_kategori','produk.nama_produk')
                        ->join('penerimaan', 'penerimaan_detail.no_penerimaan', '=', 'penerimaan.no_penerimaan')
                        ->join('produk','penerimaan_detail.kode_produk', '=', 'produk.id')
                        ->whereBetween('penerimaan.tanggal_penerimaan', array($this->awal, $this->akhir))
                        ->where('penerimaan.status', $this->status)
                        ->where('penerimaan_detail.qty_retur',0)
                        ->where('produk.kode_kategori', $this->kategori)
                        ->where('penerimaan.kode_lokasi', $this->lokasi)
                        ->get()
                    ]);
                }
                else if($this->kategori == 'SEMUA'){
                    return view('/admin/laporanpenerimaan/excel', [
                    'data' => PenerimaanDetail::on($konek)
                        ->select('penerimaan_detail.*','penerimaan.tanggal_penerimaan','penerimaan.status','penerimaan.kode_lokasi','penerimaan.no_pembelian','produk.kode_kategori','produk.nama_produk')
                        ->join('penerimaan', 'penerimaan_detail.no_penerimaan', '=', 'penerimaan.no_penerimaan')
                        ->join('produk','penerimaan_detail.kode_produk', '=', 'produk.id')
                        ->whereBetween('penerimaan.tanggal_penerimaan', array($this->awal, $this->akhir))
                        ->where('penerimaan.status', $this->status)
                        ->where('penerimaan_detail.qty_retur',0)
                        ->where('penerimaan.kode_lokasi', $this->lokasi)
                        ->get()
                    ]);
                }
            }
        }else{
            if($this->kategori != 'SEMUA'){
                return view('/admin/laporanpenerimaan/excel', [
                'data' => PenerimaanDetail::on($konek)
                    ->select('penerimaan_detail.*','penerimaan.tanggal_penerimaan','penerimaan.status','penerimaan.kode_lokasi','penerimaan.no_pembelian','produk.kode_kategori','produk.nama_produk')
                    ->join('penerimaan', 'penerimaan_detail.no_penerimaan', '=', 'penerimaan.no_penerimaan')
                    ->join('produk','penerimaan_detail.kode_produk', '=', 'produk.id')
                    ->whereBetween('penerimaan.tanggal_penerimaan', array($this->awal, $this->akhir))
                    ->where('produk.kode_kategori', $this->kategori)
                    ->where('penerimaan.kode_lokasi', $this->lokasi)
                    ->get()
                ]);
            }
            else if($this->kategori == 'SEMUA'){
                return view('/admin/laporanpenerimaan/excel', [
                'data' => PenerimaanDetail::on($konek)
                    ->select('penerimaan_detail.*','penerimaan.tanggal_penerimaan','penerimaan.status','penerimaan.kode_lokasi','penerimaan.no_pembelian','produk.kode_kategori','produk.nama_produk')
                    ->join('penerimaan', 'penerimaan_detail.no_penerimaan', '=', 'penerimaan.no_penerimaan')
                    ->join('produk','penerimaan_detail.kode_produk', '=', 'produk.id')
                    ->where('penerimaan.kode_lokasi', $this->lokasi)
                    ->whereBetween('penerimaan.tanggal_penerimaan', array($this->awal, $this->akhir))
                    ->get()
                ]);
            }
        }
    }
}