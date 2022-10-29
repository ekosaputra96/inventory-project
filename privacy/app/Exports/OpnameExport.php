<?php
 
namespace App\Exports;
 
use App\Models\tb_item_bulanan;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class OpnameExport implements FromView
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

   	public function __construct(string $request, string $req, string $get_lokasi, string $kategori, string $lokasi2)
    {
        $this->bulan = $request;
        $this->tahun = $req;
        $this->lokasi = $get_lokasi;
        $this->kategori = $kategori;
        $this->lokasi2 = $lokasi2;
    }

    public function view(): View
    {   
        $konek = static::konek();
        if ($this->lokasi2 != 'SEMUA'){
            if($this->kategori != 'SEMUA'){
                return view('/admin/opname/excel', [
                'data' => tb_item_bulanan::on($konek)->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori')->where('kode_lokasi', $this->lokasi)->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')->where('produk.kode_kategori', $this->kategori)->whereMonth('periode','like', '%'.$this->bulan.'%')->whereYear('periode', 'like', '%'.$this->tahun.'%')->get(),
                'lokasi2'=>$this->lokasi2
                ]);
            }else{
                return view('/admin/opname/excel', [
                'data' => tb_item_bulanan::on($konek)->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori')->where('kode_lokasi', $this->lokasi)->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')->whereMonth('periode','like', '%'.$this->bulan.'%')->whereYear('periode', 'like', '%'.$this->tahun.'%')->get(),
                'lokasi2'=>$this->lokasi2
                ]);
            }
        }else {
            if($this->kategori != 'SEMUA'){
                return view('/admin/opname/excel', [
                'data' => tb_item_bulanan::on($konek)->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori')->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')->where('produk.kode_kategori', $this->kategori)->whereMonth('periode','like', '%'.$this->bulan.'%')->whereYear('periode', 'like', '%'.$this->tahun.'%')->get(),
                'lokasi2'=>$this->lokasi2
                ]);
            }else{
                return view('/admin/opname/excel', [
                'data' => tb_item_bulanan::on($konek)->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori')->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')->whereMonth('periode','like', '%'.$this->bulan.'%')->whereYear('periode', 'like', '%'.$this->tahun.'%')->get(),
                'lokasi2'=>$this->lokasi2
                ]);
            }
        }
    }
}