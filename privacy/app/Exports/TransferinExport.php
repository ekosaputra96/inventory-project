<?php
 
namespace App\Exports;
 
use App\Models\tb_item_bulanan;
use App\Models\TransferIn;
use App\Models\TransferInDetail;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use DB;

class TransferinExport implements FromView
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
        if($this->lokasi != 'SEMUA'){
            if($this->status != 'SEMUA'){
                if($this->kategori != 'SEMUA'){
                    return view('/admin/laporantransferin/excel', [
                    'data' => TransferInDetail::on($konek)
                        ->select('transfer_in_detail.*','transfer_in.tanggal_transfer','transfer_in.status','transfer_in.no_trf_in','transfer_in.kode_lokasi','transfer_in.no_transfer','transfer_in.transfer_dari','produk.kode_kategori','produk.nama_produk','produk.kode_satuan')
                        ->join('transfer_in', 'transfer_in_detail.no_trf_in', '=', 'transfer_in.no_trf_in')
                        ->join('produk','transfer_in_detail.kode_produk', '=', 'produk.id')
                        ->whereBetween('transfer_in.tanggal_transfer', array($this->awal, $this->akhir))
                        ->where('transfer_in.status', $this->status)
                        ->where('produk.kode_kategori', $this->kategori)
                        ->where('transfer_in.kode_lokasi', $this->lokasi)
                        ->get()
                    ]);
                }
                else if($this->kategori == 'SEMUA'){
                    return view('/admin/laporantransferin/excel', [
                    'data' => TransferInDetail::on($konek)
                        ->select('transfer_in_detail.*','transfer_in.tanggal_transfer','transfer_in.status','transfer_in.no_trf_in','transfer_in.kode_lokasi','transfer_in.no_transfer','transfer_in.transfer_dari','produk.kode_kategori','produk.nama_produk','produk.kode_satuan')
                        ->join('transfer_in', 'transfer_in_detail.no_trf_in', '=', 'transfer_in.no_trf_in')
                        ->join('produk','transfer_in_detail.kode_produk', '=', 'produk.id')
                        ->whereBetween('transfer_in.tanggal_transfer', array($this->awal, $this->akhir))
                        ->where('transfer_in.status', $this->status)
                        ->where('transfer_in.kode_lokasi', $this->lokasi)
                        ->get()
                    ]);
                }
            }else{
                if($this->kategori != 'SEMUA'){
                    return view('/admin/laporantransferin/excel', [
                    'data' => TransferInDetail::on($konek)
                        ->select('transfer_in_detail.*','transfer_in.tanggal_transfer','transfer_in.status','transfer_in.no_trf_in','transfer_in.kode_lokasi','transfer_in.no_transfer','transfer_in.transfer_dari','produk.kode_kategori','produk.nama_produk','produk.kode_satuan')
                        ->join('transfer_in', 'transfer_in_detail.no_trf_in', '=', 'transfer_in.no_trf_in')
                        ->join('produk','transfer_in_detail.kode_produk', '=', 'produk.id')
                        ->whereBetween('transfer_in.tanggal_transfer', array($this->awal, $this->akhir))
                        ->where('produk.kode_kategori', $this->kategori)
                        ->where('transfer_in.kode_lokasi', $this->lokasi)
                        ->get()
                    ]);
                }
                else if($this->kategori == 'SEMUA'){
                    return view('/admin/laporantransferin/excel', [
                    'data' => TransferInDetail::on($konek)
                        ->select('transfer_in_detail.*','transfer_in.tanggal_transfer','transfer_in.status','transfer_in.no_trf_in','transfer_in.kode_lokasi','transfer_in.no_transfer','transfer_in.transfer_dari','produk.kode_kategori','produk.nama_produk','produk.kode_satuan')
                        ->join('transfer_in', 'transfer_in_detail.no_trf_in', '=', 'transfer_in.no_trf_in')
                        ->join('produk','transfer_in_detail.kode_produk', '=', 'produk.id')
                        ->where('transfer_in.kode_lokasi', $this->lokasi)
                        ->whereBetween('transfer_in.tanggal_transfer', array($this->awal, $this->akhir))
                        ->get()
                    ]);
                }
            }
        }
        else{
            if($this->status != 'SEMUA'){
                if($this->kategori != 'SEMUA'){
                    return view('/admin/laporantransferin/excel', [
                    'data' => TransferInDetail::on($konek)
                        ->select('transfer_in_detail.*','transfer_in.tanggal_transfer','transfer_in.status','transfer_in.no_trf_in','transfer_in.kode_lokasi','transfer_in.no_transfer','transfer_in.transfer_dari','produk.kode_kategori','produk.nama_produk','produk.kode_satuan')
                        ->join('transfer_in', 'transfer_in_detail.no_trf_in', '=', 'transfer_in.no_trf_in')
                        ->join('produk','transfer_in_detail.kode_produk', '=', 'produk.id')
                        ->whereBetween('transfer_in.tanggal_transfer', array($this->awal, $this->akhir))
                        ->where('transfer_in.status', $this->status)
                        ->where('produk.kode_kategori', $this->kategori)
                        ->get()
                    ]);
                }
                else if($this->kategori == 'SEMUA'){
                    return view('/admin/laporantransferin/excel', [
                    'data' => TransferInDetail::on($konek)
                        ->select('transfer_in_detail.*','transfer_in.tanggal_transfer','transfer_in.status','transfer_in.no_trf_in','transfer_in.kode_lokasi','transfer_in.no_transfer','transfer_in.transfer_dari','produk.kode_kategori','produk.nama_produk','produk.kode_satuan')
                        ->join('transfer_in', 'transfer_in_detail.no_trf_in', '=', 'transfer_in.no_trf_in')
                        ->join('produk','transfer_in_detail.kode_produk', '=', 'produk.id')
                        ->whereBetween('transfer_in.tanggal_transfer', array($this->awal, $this->akhir))
                        ->where('transfer_in.status', $this->status)
                        ->get()
                    ]);
                }
            }else{
                if($this->kategori != 'SEMUA'){
                    return view('/admin/laporantransferin/excel', [
                    'data' => TransferInDetail::on($konek)
                        ->select('transfer_in_detail.*','transfer_in.tanggal_transfer','transfer_in.status','transfer_in.no_trf_in','transfer_in.kode_lokasi','transfer_in.no_transfer','transfer_in.transfer_dari','produk.kode_kategori','produk.nama_produk','produk.kode_satuan')
                        ->join('transfer_in', 'transfer_in_detail.no_trf_in', '=', 'transfer_in.no_trf_in')
                        ->join('produk','transfer_in_detail.kode_produk', '=', 'produk.id')
                        ->whereBetween('transfer_in.tanggal_transfer', array($this->awal, $this->akhir))
                        ->where('produk.kode_kategori', $this->kategori)
                        ->get()
                    ]);
                }
                else if($this->kategori == 'SEMUA'){
                    return view('/admin/laporantransferin/excel', [
                    'data' => TransferInDetail::on($konek)
                        ->select('transfer_in_detail.*','transfer_in.tanggal_transfer','transfer_in.status','transfer_in.no_trf_in','transfer_in.kode_lokasi','transfer_in.no_transfer','transfer_in.transfer_dari','produk.kode_kategori','produk.nama_produk','produk.kode_satuan')
                        ->join('transfer_in', 'transfer_in_detail.no_trf_in', '=', 'transfer_in.no_trf_in')
                        ->join('produk','transfer_in_detail.kode_produk', '=', 'produk.id')
                        ->whereBetween('transfer_in.tanggal_transfer', array($this->awal, $this->akhir))
                        ->get()
                    ]);
                }
            }
        }
    }
}