<?php
 
namespace App\Exports;
 
use App\Models\tb_item_bulanan;
use App\Models\Returpembelian;
use App\Models\ReturpembelianDetail;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use DB;

class ReturpembelianExport implements FromView
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

   	public function __construct(string $awal, string $akhir, string $status)
    {
        $this->awal = $awal;
        $this->akhir = $akhir;
        $this->status = $status;
    }

    public function view(): View
    {   
        $konek = self::konek();
        if($this->status != 'SEMUA'){
            return view('/admin/laporanreturpembelian/excel', [
            'data' => ReturpembelianDetail::on($konek)
                ->select('returpembelian_detail.*','retur_pembelian.tanggal_returpembelian','retur_pembelian.status','retur_pembelian.kode_lokasi','produk.nama_produk')
                ->join('retur_pembelian', 'returpembelian_detail.no_returpembelian', '=', 'retur_pembelian.no_returpembelian')
                ->join('produk','returpembelian_detail.kode_produk', '=', 'produk.id')
                ->whereBetween('retur_pembelian.tanggal_returpembelian', array($this->awal, $this->akhir))
                ->where('retur_pembelian.status', $this->status)
                ->get()
            ]);
        }else{
            return view('/admin/laporanreturpembelian/excel', [
            'data' => ReturpembelianDetail::on($konek)
                ->select('returpembelian_detail.*','retur_pembelian.tanggal_returpembelian','retur_pembelian.status','retur_pembelian.kode_lokasi','produk.nama_produk')
                ->join('retur_pembelian', 'returpembelian_detail.no_returpembelian', '=', 'retur_pembelian.no_returpembelian')
                ->join('produk','returpembelian_detail.kode_produk', '=', 'produk.id')
                ->whereBetween('retur_pembelian.tanggal_returpembelian', array($this->awal, $this->akhir))
                ->get()
            ]);
        }
    }
}