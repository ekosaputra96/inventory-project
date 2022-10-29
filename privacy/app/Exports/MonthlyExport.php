<?php
 
namespace App\Exports;
 
use App\Models\tb_item_bulanan;
use App\Models\tb_produk_history;
use App\Models\Produk;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class MonthlyExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */

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

   	public function __construct(string $kode_produk, string $lokasi, string $tanggal_awal, string $tanggal_akhir, string $show)
    {
        $this->kode_produk = $kode_produk;
        $this->lokasi = $lokasi;
        $this->tanggal_awal = $tanggal_awal;
        $this->tanggal_akhir = $tanggal_akhir;
        $this->show = $show;
    }

    public function view(): View
    {   
        $konek = self::konek();
        if($this->lokasi != 'SEMUA'){
            if($this->show == 'Monthly'){
                return view('/admin/produk/monthlyexcel', [
                    'data' => tb_item_bulanan::on($konek)
                        ->with('produk')
                        ->select('tb_item_bulanan.*','produk.nama_produk')
                        ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                        ->whereBetween('tb_item_bulanan.periode', array($this->tanggal_awal, $this->tanggal_akhir))
                        ->where('tb_item_bulanan.kode_lokasi', $this->lokasi)
                        ->where('tb_item_bulanan.kode_produk', $this->kode_produk)
                        ->orderBy('tb_item_bulanan.periode','asc')
                        ->get()
                ]);  
            }else{
                return view('/admin/produk/transaksiexcel', [
                    'data' => tb_produk_history::on($konek)
                        ->with('produk')
                        ->select('tb_produk_history.*','produk.nama_produk')
                        ->join('produk','tb_produk_history.kode_produk', '=', 'produk.id')
                        ->whereBetween('tb_produk_history.tanggal_transaksi', array($this->tanggal_awal, $this->tanggal_akhir))
                        ->where('tb_produk_history.kode_lokasi', $this->lokasi)
                        ->where('tb_produk_history.kode_produk', $this->kode_produk)
                        ->orderBy('tb_produk_history.created_at','asc')
                        ->get()
                ]);  
            }
        }else{
            if($this->show == 'Monthly'){
                return view('/admin/produk/monthlyexcel', [
                    'data' => tb_item_bulanan::on($konek)
                        ->with('produk')
                        ->select('tb_item_bulanan.*','produk.nama_produk')
                        ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                        ->whereBetween('tb_item_bulanan.periode', array($this->tanggal_awal, $this->tanggal_akhir))
                        ->where('tb_item_bulanan.kode_produk', $this->kode_produk)
                        ->orderBy('tb_item_bulanan.periode','asc')
                        ->get()
                ]);  
            }else{
                return view('/admin/produk/transaksiexcel', [
                    'data' => tb_produk_history::on($konek)
                        ->with('produk')
                        ->select('tb_produk_history.*','produk.nama_produk')
                        ->join('produk','tb_produk_history.kode_produk', '=', 'produk.id')
                        ->whereBetween('tb_produk_history.tanggal_transaksi', array($this->tanggal_awal, $this->tanggal_akhir))
                        ->where('tb_produk_history.kode_produk', $this->kode_produk)
                        ->orderBy('tb_produk_history.created_at','asc')
                        ->get()
                ]);  
            }
        }
    }
}