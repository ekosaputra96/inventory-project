<?php
 
namespace App\Exports;
 
use App\Models\tb_item_bulanan;
use App\Models\ReturPenjualan;
use App\Models\ReturPenjualanDetail;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use DB;

class ReturpenjualanExport implements FromView
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
            return view('/admin/laporanreturpenjualan/excel', [
            'data' => ReturPenjualanDetail::on($konek)
                ->select('retur_jual_detail.*','retur_jual.tgl_retur_jual','retur_jual.status','retur_jual.kode_lokasi','produk.nama_produk')
                ->join('retur_jual', 'retur_jual_detail.no_retur_jual', '=', 'retur_jual.no_retur_jual')
                ->join('produk','retur_jual_detail.kode_produk', '=', 'produk.id')
                ->whereBetween('retur_jual.tgl_retur_jual', array($this->awal, $this->akhir))
                ->where('retur_jual.status', $this->status)
                ->get()
            ]);
        }else{
            return view('/admin/laporanreturpenjualan/excel', [
            'data' => ReturPenjualanDetail::on($konek)
                ->select('retur_jual_detail.*','retur_jual.tgl_retur_jual','retur_jual.status','retur_jual.kode_lokasi','produk.nama_produk')
                ->join('retur_jual', 'retur_jual_detail.no_retur_jual', '=', 'retur_jual.no_retur_jual')
                ->join('produk','retur_jual_detail.kode_produk', '=', 'produk.id')
                ->whereBetween('retur_jual.tgl_retur_jual', array($this->awal, $this->akhir))
                ->get()
            ]);
        }
    }
}