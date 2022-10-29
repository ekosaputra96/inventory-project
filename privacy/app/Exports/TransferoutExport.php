<?php
 
namespace App\Exports;
 
use App\Models\tb_item_bulanan;
use App\Models\Transfer;
use App\Models\TransferDetail;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use DB;

class TransferoutExport implements FromView
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
                    return view('/admin/laporantransferout/excel', [
                    'data' => TransferDetail::on($konek)
                        ->select('transfer_detail.*','transfer.tanggal_transfer','transfer.status','transfer.kode_lokasi','produk.kode_kategori','produk.nama_produk','transfer.transfer_dari','transfer.transfer_tujuan')
                        ->join('transfer', 'transfer_detail.no_transfer', '=', 'transfer.no_transfer')
                        ->join('produk','transfer_detail.kode_produk', '=', 'produk.id')
                        ->whereBetween('transfer.tanggal_transfer', array($this->awal, $this->akhir))
                        ->where('transfer.status', $this->status)
                        ->where('produk.kode_kategori', $this->kategori)
                        ->where('transfer.kode_lokasi', $this->lokasi)
                        ->get(),
                    'konek' => $konek
                    ]);
                }
                else if($this->kategori == 'SEMUA'){
                    return view('/admin/laporantransferout/excel', [
                    'data' => TransferDetail::on($konek)
                        ->select('transfer_detail.*','transfer.tanggal_transfer','transfer.status','transfer.kode_lokasi','produk.kode_kategori','produk.nama_produk','transfer.transfer_dari','transfer.transfer_tujuan')
                        ->join('transfer', 'transfer_detail.no_transfer', '=', 'transfer.no_transfer')
                        ->join('produk','transfer_detail.kode_produk', '=', 'produk.id')
                        ->whereBetween('transfer.tanggal_transfer', array($this->awal, $this->akhir))
                        ->where('transfer.status', $this->status)
                        ->where('transfer.kode_lokasi', $this->lokasi)
                        ->get(),
                    'konek' => $konek
                    ]);
                }
            }else{
                if($this->kategori != 'SEMUA'){
                    return view('/admin/laporantransferout/excel', [
                    'data' => TransferDetail::on($konek)
                        ->select('transfer_detail.*','transfer.tanggal_transfer','transfer.status','transfer.kode_lokasi','produk.kode_kategori','produk.nama_produk','transfer.transfer_dari','transfer.transfer_tujuan')
                        ->join('transfer', 'transfer_detail.no_transfer', '=', 'transfer.no_transfer')
                        ->join('produk','transfer_detail.kode_produk', '=', 'produk.id')
                        ->whereBetween('transfer.tanggal_transfer', array($this->awal, $this->akhir))
                        ->where('produk.kode_kategori', $this->kategori)
                        ->where('transfer.kode_lokasi', $this->lokasi)
                        ->get(),
                    'konek' => $konek
                    ]);
                }
                else if($this->kategori == 'SEMUA'){
                    return view('/admin/laporantransferout/excel', [
                    'data' => TransferDetail::on($konek)
                        ->select('transfer_detail.*','transfer.tanggal_transfer','transfer.status','transfer.kode_lokasi','produk.kode_kategori','produk.nama_produk','transfer.transfer_dari','transfer.transfer_tujuan')
                        ->join('transfer', 'transfer_detail.no_transfer', '=', 'transfer.no_transfer')
                        ->join('produk','transfer_detail.kode_produk', '=', 'produk.id')
                        ->where('transfer.kode_lokasi', $this->lokasi)
                        ->whereBetween('transfer.tanggal_transfer', array($this->awal, $this->akhir))
                        ->get(),
                    'konek' => $konek
                    ]);
                }
            }
        }
        else{
            if($this->status != 'SEMUA'){
                if($this->kategori != 'SEMUA'){
                    return view('/admin/laporantransferout/excel', [
                    'data' => TransferDetail::on($konek)
                        ->select('transfer_detail.*','transfer.tanggal_transfer','transfer.status','transfer.kode_lokasi','produk.kode_kategori','produk.nama_produk','transfer.transfer_dari','transfer.transfer_tujuan')
                        ->join('transfer', 'transfer_detail.no_transfer', '=', 'transfer.no_transfer')
                        ->join('produk','transfer_detail.kode_produk', '=', 'produk.id')
                        ->whereBetween('transfer.tanggal_transfer', array($this->awal, $this->akhir))
                        ->where('transfer.status', $this->status)
                        ->where('produk.kode_kategori', $this->kategori)
                        ->get(),
                    'konek' => $konek
                    ]);
                }
                else if($this->kategori == 'SEMUA'){
                    return view('/admin/laporantransferout/excel', [
                    'data' => TransferDetail::on($konek)
                        ->select('transfer_detail.*','transfer.tanggal_transfer','transfer.status','transfer.kode_lokasi','produk.kode_kategori','produk.nama_produk','transfer.transfer_dari','transfer.transfer_tujuan')
                        ->join('transfer', 'transfer_detail.no_transfer', '=', 'transfer.no_transfer')
                        ->join('produk','transfer_detail.kode_produk', '=', 'produk.id')
                        ->whereBetween('transfer.tanggal_transfer', array($this->awal, $this->akhir))
                        ->where('transfer.status', $this->status)
                        ->get(),
                    'konek' => $konek
                    ]);
                }
            }else{
                if($this->kategori != 'SEMUA'){
                    return view('/admin/laporantransferout/excel', [
                    'data' => TransferDetail::on($konek)
                        ->select('transfer_detail.*','transfer.tanggal_transfer','transfer.status','transfer.kode_lokasi','produk.kode_kategori','produk.nama_produk','transfer.transfer_dari','transfer.transfer_tujuan')
                        ->join('transfer', 'transfer_detail.no_transfer', '=', 'transfer.no_transfer')
                        ->join('produk','transfer_detail.kode_produk', '=', 'produk.id')
                        ->whereBetween('transfer.tanggal_transfer', array($this->awal, $this->akhir))
                        ->where('produk.kode_kategori', $this->kategori)
                        ->get(),
                    'konek' => $konek
                    ]);
                }
                else if($this->kategori == 'SEMUA'){
                    return view('/admin/laporantransferout/excel', [
                    'data' => TransferDetail::on($konek)
                        ->select('transfer_detail.*','transfer.tanggal_transfer','transfer.status','transfer.kode_lokasi','produk.kode_kategori','produk.nama_produk','transfer.transfer_dari','transfer.transfer_tujuan')
                        ->join('transfer', 'transfer_detail.no_transfer', '=', 'transfer.no_transfer')
                        ->join('produk','transfer_detail.kode_produk', '=', 'produk.id')
                        ->whereBetween('transfer.tanggal_transfer', array($this->awal, $this->akhir))
                        ->get(),
                    'konek' => $konek
                    ]);
                }
            }
        }
    }
}