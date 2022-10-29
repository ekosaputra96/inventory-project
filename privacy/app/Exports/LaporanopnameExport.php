<?php
 
namespace App\Exports;
 
use App\Models\tb_item_bulanan;
use App\Models\Opname;
use App\Models\OpnameDetail;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use DB;

class laporanopnameExport implements FromView
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

   	public function __construct(string $awal, string $akhir, string $status, string $lokasi)
    {
        $this->awal = $awal;
        $this->akhir = $akhir;
        $this->status = $status;
        $this->lokasi = $lokasi;
    }

    public function view(): View
    {   
        $konek = self::konek();
        return view('/admin/laporanopname/excel', [
            'data' => OpnameDetail::on($konek)
                ->select('opname_detail.*','opname.tanggal_opname','opname.status','opname.kode_lokasi','produk.nama_produk','produk.kode_kategori')
                ->join('opname', 'opname_detail.no_opname', '=', 'opname.no_opname')
                ->join('produk','opname_detail.kode_produk', '=', 'produk.id')
                ->whereBetween('opname.tanggal_opname', array($this->awal, $this->akhir))
                ->where('opname.status', $this->status)
                ->where('opname.kode_lokasi', $this->lokasi)
                ->get()
        ]);
    }
}