<?php
  
namespace App\Imports;
  
use App\Models\OpnameDetail;
use Maatwebsite\Excel\Concerns\ToModel;
use Alert;
  
class UsersImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public function konek()
    {
        $compa2 = auth()->user()->kode_company;
        $compa = substr($compa2,0,2);
        if ($compa == '01'){
            $koneksi = 'mysqldepo';
        }else if ($compa == '02'){
            $koneksi = 'mysqlpbm';
        }else if ($compa == '22'){
            $koneksi = 'mysqlskt';
        }else if ($compa == '03'){
            $koneksi = 'mysqlemkl';   
        }else if ($compa == '04'){
            $koneksi = 'mysqlgut';
        }else if ($compa == '05'){
            $koneksi = 'mysql';
        }else if ($compa == '06'){
            $koneksi = 'mysqlinfra';
        }
        return $koneksi;
    }

    public function model(array $row)
    {
        // dd($row[0]);
        // if ($row[0] != "No Opname"){
        //     exit("Form tidak sesuai");
        // }
        
        $konek = self::konek();
        return OpnameDetail::on($konek)->create([
            'no_opname' => $row[0],
            'kode_produk' => $row[1],
            'partnumber' => substr($row[3],1),
            'no_mesin' => $row[4],  
            'hpp' => $row[5], 
            'stok' => $row[6],
            'qty_checker1' => $row[7], 
            'qty_checker2' => $row[8], 
            'qty_checker3' => $row[9],  
        ],
        alert()->success('Input Data Excel','BERHASIL!')->persistent('Close'));

        
    }
}