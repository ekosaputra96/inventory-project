<?php
 
namespace App\Exports;
 
use App\Models\tb_item_bulanan;
use App\Models\Opname;
use App\Models\OpnameDetail;
use App\Models\tb_akhir_bulan;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use DB;
use Carbon;

class FormopnameExport implements FromView
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

   	public function __construct(string $no_opname)
    {
        $this->no_opname = $no_opname;
    }

    public function view(): View
    {   
        $konek = self::konek();
        $kode_lokasi = auth()->user()->kode_lokasi;

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_jalan->periode)->year;
        $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_jalan->periode)->month;
        $tanggal = '01';
        $tanggal_baru = Carbon\Carbon::createFromDate($tahun, $bulan, $tanggal)->toDateString();
        return view('/admin/opnamedetail/excel', [
        'data' => tb_item_bulanan::on($konek)
            ->select('tb_item_bulanan.*','produk.nama_produk','produk.stat')
            ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
            ->where('periode', $tanggal_baru)
            ->where('kode_lokasi', $kode_lokasi)
            ->where('produk.stat','Aktif')
            ->get(),
        'no_opname' => $this->no_opname
        ]);
    }
}