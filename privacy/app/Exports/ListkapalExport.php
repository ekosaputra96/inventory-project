<?php
 
namespace App\Exports;
 
use App\Models\tb_item_bulanan;
use App\Models\tb_produk_history;
use App\Models\Kapal;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ListkapalExport implements FromView
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

   	public function __construct(string $kode_company)
    {
        $this->kode_company = $kode_company;
    }

    public function view(): View
    {   
        $konek = self::konek();
        return view('/admin/kapal/listkapalexcel', [
                    'data' => Kapal::on($konek)
                        ->orderBy('kode_kapal')
                        ->get()
        ]);
    }
}