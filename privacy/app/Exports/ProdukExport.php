<?php
 
namespace App\Exports;
 
use App\Models\Produk;
use App\Models\tb_item_bulanan;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ProdukExport implements FromView
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

    public function __construct(string $get_lokasi, string $bulan2, string $kategori, string $merek)
    {
        $this->bulan = $bulan2;
        $this->lokasi = $get_lokasi;
        $this->kategori = $kategori;
        $this->merek = $merek;
    }

    public function view(): View
    {   
        $konek = self::konek();
        if($this->lokasi != 'SEMUA'){
            if($this->kategori != 'SEMUA'){
                if ($this->merek != 'SEMUA'){
                    return view('/admin/produk/excel', [
                    'data' => tb_item_bulanan::on($konek)
                        ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori','produk.kode_satuan')
                        ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                        ->where('produk.kode_kategori', $this->kategori)
                        ->where('produk.kode_merek', $this->merek)
                        ->where('tb_item_bulanan.kode_lokasi', $this->lokasi)
                        ->where('tb_item_bulanan.periode', $this->bulan)
                        ->get()
                    ]);
                }else {
                    return view('/admin/produk/excel', [
                    'data' => tb_item_bulanan::on($konek)
                        ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori','produk.kode_satuan')
                        ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                        ->where('produk.kode_kategori', $this->kategori)
                        ->where('tb_item_bulanan.kode_lokasi', $this->lokasi)
                        ->where('tb_item_bulanan.periode', $this->bulan)
                        ->get()
                    ]); 
                }
            }
            else{
                if ($this->merek != 'SEMUA'){
                    return view('/admin/produk/excel', [
                    'data' => tb_item_bulanan::on($konek)
                        ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori','produk.kode_satuan')
                        ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                        ->where('produk.kode_merek', $this->merek)
                        ->where('tb_item_bulanan.kode_lokasi', $this->lokasi)
                        ->where('tb_item_bulanan.periode', $this->bulan)
                        ->get()
                    ]);
                }else {
                    return view('/admin/produk/excel', [
                    'data' => tb_item_bulanan::on($konek)
                        ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori','produk.kode_satuan')
                        ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                        ->where('tb_item_bulanan.kode_lokasi', $this->lokasi)
                        ->where('tb_item_bulanan.periode', $this->bulan)
                        ->get()
                    ]);
                }
            }    
        }
        else{
            if($this->kategori != 'SEMUA'){
                if ($this->merek != 'SEMUA'){
                    return view('/admin/produk/excel', [
                    'data' => tb_item_bulanan::on($konek)
                        ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori','produk.kode_satuan')
                        ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                        ->where('produk.kode_kategori', $this->kategori)
                        ->where('produk.kode_merek', $this->merek)
                        ->where('tb_item_bulanan.periode', $this->bulan)
                        ->get()
                    ]);
                }else {
                    return view('/admin/produk/excel', [
                    'data' => tb_item_bulanan::on($konek)
                        ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori','produk.kode_satuan')
                        ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                        ->where('produk.kode_kategori', $this->kategori)
                        ->where('tb_item_bulanan.periode', $this->bulan)
                        ->get()
                    ]);
                }
            }
            else{
                if ($this->merek != 'SEMUA'){
                    return view('/admin/produk/excel', [
                    'data' => tb_item_bulanan::on($konek)
                        ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori','produk.kode_satuan')
                        ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                        ->where('produk.kode_merek', $this->merek)
                        ->where('tb_item_bulanan.periode', $this->bulan)
                        ->get()
                    ]);
                }else {
                    return view('/admin/produk/excel', [
                    'data' => tb_item_bulanan::on($konek)
                        ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori','produk.kode_satuan')
                        ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                        ->where('tb_item_bulanan.periode', $this->bulan)
                        ->get()
                    ]);
                }
            }    
        }
    }
}