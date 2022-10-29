<?php
 
namespace App\Exports;
 
use App\Models\tb_item_bulanan;
use App\Models\Adjustment;
use App\Models\AdjustmentDetail;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use DB;

class AdjustmentExport implements FromView
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
            if($this->kategori != 'SEMUA'){
                return view('/admin/laporanadjustment/excel', [
                'data' => AdjustmentDetail::on($konek)
                    ->select('adjustments_detail.*','adjustments.tanggal','adjustments.status','adjustments.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                    ->join('adjustments', 'adjustments_detail.no_penyesuaian', '=', 'adjustments.no_penyesuaian')
                    ->join('produk','adjustments_detail.kode_produk', '=', 'produk.id')
                    ->whereBetween('adjustments.tanggal', array($this->awal, $this->akhir))
                    ->where('adjustments.status', $this->status)
                    ->where('produk.kode_kategori', $this->kategori)
                    ->where('adjustments.kode_lokasi', $this->lokasi)
                    ->get()
                ]);
            }
            else if($this->kategori == 'SEMUA'){
                return view('/admin/laporanadjustment/excel', [
                'data' => AdjustmentDetail::on($konek)
                    ->select('adjustments_detail.*','adjustments.tanggal','adjustments.status','adjustments.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                    ->join('adjustments', 'adjustments_detail.no_penyesuaian', '=', 'adjustments.no_penyesuaian')
                    ->join('produk','adjustments_detail.kode_produk', '=', 'produk.id')
                    ->whereBetween('adjustments.tanggal', array($this->awal, $this->akhir))
                    ->where('adjustments.status', $this->status)
                    ->where('adjustments.kode_lokasi', $this->lokasi)
                    ->get()
                ]);
            }
        }else{
            if($this->kategori != 'SEMUA'){
                return view('/admin/laporanadjustment/excel', [
                'data' => AdjustmentDetail::on($konek)
                    ->select('adjustments_detail.*','adjustments.tanggal','adjustments.status','adjustments.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                    ->join('adjustments', 'adjustments_detail.no_penyesuaian', '=', 'adjustments.no_penyesuaian')
                    ->join('produk','adjustments_detail.kode_produk', '=', 'produk.id')
                    ->whereBetween('adjustments.tanggal', array($this->awal, $this->akhir))
                    ->where('produk.kode_kategori', $this->kategori)
                    ->where('adjustments.kode_lokasi', $this->lokasi)
                    ->get()
                ]);
            }
            else if($this->kategori == 'SEMUA'){
                return view('/admin/laporanadjustment/excel', [
                'data' => AdjustmentDetail::on($konek)
                    ->select('adjustments_detail.*','adjustments.tanggal','adjustments.status','adjustments.kode_lokasi','produk.kode_kategori','produk.nama_produk')
                    ->join('adjustments', 'adjustments_detail.no_penyesuaian', '=', 'adjustments.no_penyesuaian')
                    ->join('produk','adjustments_detail.kode_produk', '=', 'produk.id')
                    ->where('adjustments.kode_lokasi', $this->lokasi)
                    ->whereBetween('adjustments.tanggal', array($this->awal, $this->akhir))
                    ->get()
                ]);
            }
        }
    }
}