<?php
 
namespace App\Exports;
 
use App\Models\tb_item_bulanan;
use App\Models\Pemakaian;
use App\Models\PemakaianDetail;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use DB;

class PemakaianExport implements FromView
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

	public function __construct(string $awal, string $akhir, string $status, string $tipe_pemakaian, string $kategori, string $get_lokasi, string $aset, string $asetalat, string $asetkapal, string $nama2, string $nama, string $dt, string $produk, string $namaproduk, string $partnumber, string $satuan,  string $kategoriproduk, string $harga, string $subtotal, string $semua)
	{
		$this->awal = $awal;
		$this->nama2 = $nama2;
		$this->nama = $nama;
		$this->akhir = $akhir;
		$this->status = $status;
		$this->tipe_pemakaian = $tipe_pemakaian;
		$this->kategori = $kategori;
		$this->dt = $dt;
		$this->lokasi = $get_lokasi;
		$this->aset = $aset;
		$this->asetalat = $asetalat;
		$this->asetkapal = $asetkapal;
		$this->produk = $produk;
		$this->namaproduk = $namaproduk;
		$this->partnumber = $partnumber;
		$this->satuan = $satuan;
		$this->kategoriproduk = $kategoriproduk;
		$this->harga = $harga;
		$this->subtotal = $subtotal;
		$this->semua = $semua;
	}

	public function view(): View
	{   
		$konek = self::konek();
		if($this->lokasi != 'SEMUA'){
			if($this->status != 'SEMUA' && $this->tipe_pemakaian == 'Alat'){
				if($this->kategori != 'SEMUA'){
					if ($this->asetalat != 'SEMUA'){
						return view('/admin/laporanpemakaian/excel_alat', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('alat', 'pemakaian.kode_alat', '=', 'alat.kode_alat')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('pemakaian.status', $this->status)
							->where('pemakaian.no_asset_alat', $this->asetalat)
							->where('produk.kode_kategori', $this->kategori)
							->where('pemakaian.kode_lokasi', $this->lokasi)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'aset'=>$this->asetalat,
						'nama2'=>$this->nama2,
						'nama'=>$this->nama,
						'dt'=>$this->dt,
						'kategori'=>$this->kategori,
						'awal'=>$this->awal,
						'akhir'=>$this->akhir,
						'produk'=>$this->produk,
						'namaproduk'=>$this->namaproduk,
						'partnumber'=>$this->partnumber,
						'satuan'=>$this->satuan,
						'kategoriproduk'=>$this->kategoriproduk,
						'harga'=>$this->harga,
						'subtotal'=>$this->subtotal,
						'semua'=>$this->semua,
						'konek'=>$konek
						]);
					}
					else{
						return view('/admin/laporanpemakaian/excel_alat', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('pemakaian.status', $this->status)
							->where('produk.kode_kategori', $this->kategori)
							->where('pemakaian.kode_lokasi', $this->lokasi)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'nama2'=>$this->nama2,
						'nama'=>$this->nama,
						'dt'=>$this->dt,
						'kategori'=>$this->kategori,
						'awal'=>$this->awal,
						'akhir'=>$this->akhir,
						'produk'=>$this->produk,
						'namaproduk'=>$this->namaproduk,
						'partnumber'=>$this->partnumber,
						'satuan'=>$this->satuan,
						'kategoriproduk'=>$this->kategoriproduk,
						'harga'=>$this->harga,
						'subtotal'=>$this->subtotal,
						'semua'=>$this->semua,
						'konek'=>$konek
						]);  
					}
				}
				else if($this->kategori == 'SEMUA'){
					if ($this->asetalat != 'SEMUA'){
						return view('/admin/laporanpemakaian/excel_alat', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('alat', 'pemakaian.kode_alat', '=', 'alat.kode_alat')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('pemakaian.status', $this->status)
							->where('pemakaian.no_asset_alat', $this->asetalat)
							->where('pemakaian.kode_lokasi', $this->lokasi)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'aset'=>$this->asetalat,
						'nama2'=>$this->nama2,
						'nama'=>$this->nama,
						'dt'=>$this->dt,
						'kategori'=>$this->kategori,
						'awal'=>$this->awal,
						'akhir'=>$this->akhir,
						'produk'=>$this->produk,
						'namaproduk'=>$this->namaproduk,
						'partnumber'=>$this->partnumber,
						'satuan'=>$this->satuan,
						'kategoriproduk'=>$this->kategoriproduk,
						'harga'=>$this->harga,
						'subtotal'=>$this->subtotal,
						'semua'=>$this->semua,
						'konek'=>$konek
						]);
					}
					else{
						return view('/admin/laporanpemakaian/excel_alat', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('pemakaian.status', $this->status)
							->where('pemakaian.kode_lokasi', $this->lokasi)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'nama2'=>$this->nama2,
						'nama'=>$this->nama,
						'dt'=>$this->dt,
						'kategori'=>$this->kategori,
						'awal'=>$this->awal,
						'akhir'=>$this->akhir,
						'produk'=>$this->produk,
						'namaproduk'=>$this->namaproduk,
						'partnumber'=>$this->partnumber,
						'satuan'=>$this->satuan,
						'kategoriproduk'=>$this->kategoriproduk,
						'harga'=>$this->harga,
						'subtotal'=>$this->subtotal,
						'konek'=>$konek,
						'semua'=>$this->semua
						]);
					}
				}
			}
			else if($this->status == 'SEMUA' && $this->tipe_pemakaian == 'Alat'){
				if($this->kategori != 'SEMUA'){
					if ($this->asetalat != 'SEMUA'){
						return view('/admin/laporanpemakaian/excel_alat', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('alat', 'pemakaian.kode_alat', '=', 'alat.kode_alat')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('pemakaian.no_asset_alat', $this->asetalat)
							->where('produk.kode_kategori', $this->kategori)
							->where('pemakaian.kode_lokasi', $this->lokasi)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'aset'=>$this->asetalat,
						'nama2'=>$this->nama2,
						'nama'=>$this->nama,
						'dt'=>$this->dt,
						'kategori'=>$this->kategori,
						'awal'=>$this->awal,
						'akhir'=>$this->akhir,
						'produk'=>$this->produk,
						'namaproduk'=>$this->namaproduk,
						'partnumber'=>$this->partnumber,
						'satuan'=>$this->satuan,
						'kategoriproduk'=>$this->kategoriproduk,
						'harga'=>$this->harga,
						'subtotal'=>$this->subtotal,
						'konek'=>$konek,
						'semua'=>$this->semua
						]);
					}
					else{
						return view('/admin/laporanpemakaian/excel_alat', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('produk.kode_kategori', $this->kategori)
							->where('pemakaian.kode_lokasi', $this->lokasi)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'nama2'=>$this->nama2,
						'nama'=>$this->nama,
						'dt'=>$this->dt,
						'kategori'=>$this->kategori,
						'awal'=>$this->awal,
						'akhir'=>$this->akhir,
						'produk'=>$this->produk,
						'namaproduk'=>$this->namaproduk,
						'partnumber'=>$this->partnumber,
						'satuan'=>$this->satuan,
						'kategoriproduk'=>$this->kategoriproduk,
						'harga'=>$this->harga,
						'subtotal'=>$this->subtotal,
						'konek'=>$konek,
						'semua'=>$this->semua
						]);  
					}
				}
				else if($this->kategori == 'SEMUA'){
					if ($this->asetalat != 'SEMUA'){
						return view('/admin/laporanpemakaian/excel_alat', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('alat', 'pemakaian.kode_alat', '=', 'alat.kode_alat')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('pemakaian.no_asset_alat', $this->asetalat)
							->where('pemakaian.kode_lokasi', $this->lokasi)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'aset'=>$this->asetalat,
						'nama2'=>$this->nama2,
						'nama'=>$this->nama,
						'dt'=>$this->dt,
						'kategori'=>$this->kategori,
						'awal'=>$this->awal,
						'akhir'=>$this->akhir,
						'produk'=>$this->produk,
						'namaproduk'=>$this->namaproduk,
						'partnumber'=>$this->partnumber,
						'satuan'=>$this->satuan,
						'kategoriproduk'=>$this->kategoriproduk,
						'harga'=>$this->harga,
						'subtotal'=>$this->subtotal,
						'konek'=>$konek,
						'semua'=>$this->semua
						]);
					}
					else{
						return view('/admin/laporanpemakaian/excel_alat', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.kode_lokasi')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('pemakaian.kode_lokasi', $this->lokasi)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'nama2'=>$this->nama2,
						'nama'=>$this->nama,
						'dt'=>$this->dt,
						'kategori'=>$this->kategori,
						'awal'=>$this->awal,
						'akhir'=>$this->akhir,
						'produk'=>$this->produk,
						'namaproduk'=>$this->namaproduk,
						'partnumber'=>$this->partnumber,
						'satuan'=>$this->satuan,
						'kategoriproduk'=>$this->kategoriproduk,
						'harga'=>$this->harga,
						'subtotal'=>$this->subtotal,
						'konek'=>$konek,
						'semua'=>$this->semua
						]); 
					}
				}
			}
			else if($this->status != 'SEMUA' && $this->tipe_pemakaian == 'Mobil'){
				if($this->kategori != 'SEMUA'){
					if ($this->aset != 'SEMUA'){
						return view('/admin/laporanpemakaian/excel_mobil', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('pemakaian.no_asset_mobil', $this->aset)
							->where('produk.kode_kategori', $this->kategori)
							->where('pemakaian.kode_lokasi', $this->lokasi)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'aset'=>$this->aset,
						'nama2'=>$this->nama2,
						'nama'=>$this->nama,
						'dt'=>$this->dt,
						'status'=>$this->status,
						'kategori'=>$this->kategori,
						'awal'=>$this->awal,
						'akhir'=>$this->akhir,
						'produk'=>$this->produk,
						'namaproduk'=>$this->namaproduk,
						'partnumber'=>$this->partnumber,
						'satuan'=>$this->satuan,
						'kategoriproduk'=>$this->kategoriproduk,
						'harga'=>$this->harga,
						'subtotal'=>$this->subtotal,
						'konek'=>$konek,
						'semua'=>$this->semua
						]);
					}else {
						return view('/admin/laporanpemakaian/excel_mobil', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('produk.kode_kategori', $this->kategori)
							->where('pemakaian.kode_lokasi', $this->lokasi)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'aset'=>$this->aset,
						'nama2'=>$this->nama2,
						'nama'=>$this->nama,
						'dt'=>$this->dt,
						'kategori'=>$this->kategori,
						'status'=>$this->status,
						'awal'=>$this->awal,
						'akhir'=>$this->akhir,
						'produk'=>$this->produk,
						'namaproduk'=>$this->namaproduk,
						'partnumber'=>$this->partnumber,
						'satuan'=>$this->satuan,
						'kategoriproduk'=>$this->kategoriproduk,
						'harga'=>$this->harga,
						'subtotal'=>$this->subtotal,
						'konek'=>$konek,
						'semua'=>$this->semua
						]);  
					}
				}
				else if($this->kategori == 'SEMUA'){
					if ($this->aset != 'SEMUA'){
						return view('/admin/laporanpemakaian/excel_mobil', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil','pemakaian.kode_lokasi')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('pemakaian.status', $this->status)
							->where('pemakaian.no_asset_mobil', $this->aset)
							->where('pemakaian.kode_lokasi', $this->lokasi)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'aset'=>$this->aset,
						'nama2'=>$this->nama2,
						'nama'=>$this->nama,
						'dt'=>$this->dt,
						'kategori'=>$this->kategori,
						'awal'=>$this->awal,
						'akhir'=>$this->akhir,
						'produk'=>$this->produk,
						'namaproduk'=>$this->namaproduk,
						'partnumber'=>$this->partnumber,
						'satuan'=>$this->satuan,
						'kategoriproduk'=>$this->kategoriproduk,
						'harga'=>$this->harga,
						'subtotal'=>$this->subtotal,
						'konek'=>$konek,
						'semua'=>$this->semua
						]);
					}else {
						return view('/admin/laporanpemakaian/excel_mobil', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil','pemakaian.kode_lokasi')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('pemakaian.status', $this->status)
							->where('pemakaian.kode_lokasi', $this->lokasi)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'aset'=>$this->aset,
						'nama2'=>$this->nama2,
						'nama'=>$this->nama,
						'dt'=>$this->dt,
						'kategori'=>$this->kategori,
						'awal'=>$this->awal,
						'akhir'=>$this->akhir,
						'produk'=>$this->produk,
						'namaproduk'=>$this->namaproduk,
						'partnumber'=>$this->partnumber,
						'satuan'=>$this->satuan,
						'kategoriproduk'=>$this->kategoriproduk,
						'harga'=>$this->harga,
						'subtotal'=>$this->subtotal,
						'konek'=>$konek,
						'semua'=>$this->semua
						]);
					}
				}
			}
			else if($this->status == 'SEMUA' && $this->tipe_pemakaian == 'Mobil'){
				if($this->kategori != 'SEMUA'){
					if ($this->aset != 'SEMUA'){
						return view('/admin/laporanpemakaian/excel_mobil', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('produk.kode_kategori', $this->kategori)
							->where('pemakaian.no_asset_mobil', $this->aset)
							->where('pemakaian.kode_lokasi', $this->lokasi)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'aset'=>$this->aset,
						'nama2'=>$this->nama2,
						'nama'=>$this->nama,
						'stat'=>$this->status,
						'dt'=>$this->dt,
						'kategori'=>$this->kategori,
						'awal'=>$this->awal,
						'akhir'=>$this->akhir,
						'produk'=>$this->produk,
						'namaproduk'=>$this->namaproduk,
						'partnumber'=>$this->partnumber,
						'satuan'=>$this->satuan,
						'kategoriproduk'=>$this->kategoriproduk,
						'harga'=>$this->harga,
						'subtotal'=>$this->subtotal,
						'konek'=>$konek,
						'semua'=>$this->semua
						]);
					}else {
						return view('/admin/laporanpemakaian/excel_mobil', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('produk.kode_kategori', $this->kategori)
							->where('pemakaian.kode_lokasi', $this->lokasi)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'aset'=>$this->aset,
						'nama2'=>$this->nama2,
						'nama'=>$this->nama,
						'stat'=>$this->status,
						'dt'=>$this->dt,
						'kategori'=>$this->kategori,
						'awal'=>$this->awal,
						'akhir'=>$this->akhir,
						'produk'=>$this->produk,
						'namaproduk'=>$this->namaproduk,
						'partnumber'=>$this->partnumber,
						'satuan'=>$this->satuan,
						'kategoriproduk'=>$this->kategoriproduk,
						'harga'=>$this->harga,
						'subtotal'=>$this->subtotal,
						'konek'=>$konek,
						'semua'=>$this->semua
						]);
					}
				}
				else if($this->kategori == 'SEMUA'){
					if ($this->aset != 'SEMUA'){
						return view('/admin/laporanpemakaian/excel_mobil', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('pemakaian.no_asset_mobil', $this->aset)
							->where('pemakaian.kode_lokasi', $this->lokasi)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'aset'=>$this->aset,
						'nama2'=>$this->nama2,
						'nama'=>$this->nama,
						'dt'=>$this->dt,
						'stat'=>$this->status,
						'kategori'=>$this->kategori,
						'awal'=>$this->awal,
						'akhir'=>$this->akhir,
						'produk'=>$this->produk,
						'namaproduk'=>$this->namaproduk,
						'partnumber'=>$this->partnumber,
						'satuan'=>$this->satuan,
						'kategoriproduk'=>$this->kategoriproduk,
						'harga'=>$this->harga,
						'subtotal'=>$this->subtotal,
						'konek'=>$konek,
						'semua'=>$this->semua
						]);
					}else {
						return view('/admin/laporanpemakaian/excel_mobil', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('pemakaian.kode_lokasi', $this->lokasi)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'aset'=>$this->aset,
						'nama2'=>$this->nama2,
						'nama'=>$this->nama,
						'dt'=>$this->dt,
						'stat'=>$this->status,
						'kategori'=>$this->kategori,
						'awal'=>$this->awal,
						'akhir'=>$this->akhir,
						'produk'=>$this->produk,
						'namaproduk'=>$this->namaproduk,
						'partnumber'=>$this->partnumber,
						'satuan'=>$this->satuan,
						'kategoriproduk'=>$this->kategoriproduk,
						'harga'=>$this->harga,
						'subtotal'=>$this->subtotal,
						'konek'=>$konek,
						'semua'=>$this->semua
						]);
					}
				}
			}
			else if($this->status != 'SEMUA' && $this->tipe_pemakaian == 'Kapal'){
				if($this->kategori != 'SEMUA'){
					if ($this->asetkapal != 'SEMUA'){
						return view('/admin/laporanpemakaian/excel_kapal', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('pemakaian.no_asset_kapal', $this->asetkapal)
							->where('pemakaian.status', $this->status)
							->where('produk.kode_kategori', $this->kategori)
							->where('pemakaian.kode_lokasi', $this->lokasi)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'nama2'=>$this->nama2,
						'nama'=>$this->nama,
						'dt'=>$this->dt,
						'kategori'=>$this->kategori,
						'awal'=>$this->awal,
						'akhir'=>$this->akhir,
						'produk'=>$this->produk,
						'namaproduk'=>$this->namaproduk,
						'partnumber'=>$this->partnumber,
						'satuan'=>$this->satuan,
						'kategoriproduk'=>$this->kategoriproduk,
						'harga'=>$this->harga,
						'subtotal'=>$this->subtotal,
						'konek'=>$konek,
						'semua'=>$this->semua
						]);
					}else{
						return view('/admin/laporanpemakaian/excel_kapal', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('pemakaian.status', $this->status)
							->where('produk.kode_kategori', $this->kategori)
							->where('pemakaian.kode_lokasi', $this->lokasi)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'nama2'=>$this->nama2,
						'nama'=>$this->nama,
						'dt'=>$this->dt,
						'kategori'=>$this->kategori,
						'awal'=>$this->awal,
						'akhir'=>$this->akhir,
						'produk'=>$this->produk,
						'namaproduk'=>$this->namaproduk,
						'partnumber'=>$this->partnumber,
						'satuan'=>$this->satuan,
						'kategoriproduk'=>$this->kategoriproduk,
						'harga'=>$this->harga,
						'subtotal'=>$this->subtotal,
						'konek'=>$konek,
						'semua'=>$this->semua
						]);
					}
				}
				else if($this->kategori == 'SEMUA'){
					if ($this->asetkapal != 'SEMUA'){
						return view('/admin/laporanpemakaian/excel_kapal', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('pemakaian.no_asset_kapal', $this->asetkapal)
							->where('pemakaian.status', $this->status)
							->where('pemakaian.kode_lokasi', $this->lokasi)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'nama2'=>$this->nama2,
						'nama'=>$this->nama,
						'dt'=>$this->dt,
						'kategori'=>$this->kategori,
						'awal'=>$this->awal,
						'akhir'=>$this->akhir,
						'produk'=>$this->produk,
						'namaproduk'=>$this->namaproduk,
						'partnumber'=>$this->partnumber,
						'satuan'=>$this->satuan,
						'kategoriproduk'=>$this->kategoriproduk,
						'harga'=>$this->harga,
						'subtotal'=>$this->subtotal,
						'konek'=>$konek,
						'semua'=>$this->semua
						]);
					}else{
						return view('/admin/laporanpemakaian/excel_kapal', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('pemakaian.status', $this->status)
							->where('pemakaian.kode_lokasi', $this->lokasi)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'nama2'=>$this->nama2,
						'nama'=>$this->nama,
						'dt'=>$this->dt,
						'kategori'=>$this->kategori,
						'awal'=>$this->awal,
						'akhir'=>$this->akhir,
						'produk'=>$this->produk,
						'namaproduk'=>$this->namaproduk,
						'partnumber'=>$this->partnumber,
						'satuan'=>$this->satuan,
						'kategoriproduk'=>$this->kategoriproduk,
						'harga'=>$this->harga,
						'subtotal'=>$this->subtotal,
						'konek'=>$konek,
						'semua'=>$this->semua
						]);
					}
				}
			}
			else if($this->status == 'SEMUA' && $this->tipe_pemakaian == 'Kapal'){
				if($this->kategori != 'SEMUA'){
					if ($this->asetkapal != 'SEMUA'){
						return view('/admin/laporanpemakaian/excel_kapal', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('pemakaian.no_asset_kapal', $this->asetkapal)
							->where('produk.kode_kategori', $this->kategori)
							->where('pemakaian.kode_lokasi', $this->lokasi)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'nama2'=>$this->nama2,
						'nama'=>$this->nama,
						'dt'=>$this->dt,
						'kategori'=>$this->kategori,
						'awal'=>$this->awal,
						'akhir'=>$this->akhir,
						'produk'=>$this->produk,
						'namaproduk'=>$this->namaproduk,
						'partnumber'=>$this->partnumber,
						'satuan'=>$this->satuan,
						'kategoriproduk'=>$this->kategoriproduk,
						'harga'=>$this->harga,
						'subtotal'=>$this->subtotal,
						'konek'=>$konek,
						'semua'=>$this->semua
						]);  
					}else{
						return view('/admin/laporanpemakaian/excel_kapal', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('produk.kode_kategori', $this->kategori)
							->where('pemakaian.kode_lokasi', $this->lokasi)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'nama2'=>$this->nama2,
						'nama'=>$this->nama,
						'dt'=>$this->dt,
						'kategori'=>$this->kategori,
						'awal'=>$this->awal,
						'akhir'=>$this->akhir,
						'produk'=>$this->produk,
						'namaproduk'=>$this->namaproduk,
						'partnumber'=>$this->partnumber,
						'satuan'=>$this->satuan,
						'kategoriproduk'=>$this->kategoriproduk,
						'harga'=>$this->harga,
						'subtotal'=>$this->subtotal,
						'konek'=>$konek,
						'semua'=>$this->semua
						]);  
					}
				}
				else if($this->kategori == 'SEMUA'){
					if ($this->asetkapal != 'SEMUA'){
						return view('/admin/laporanpemakaian/excel_kapal', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('pemakaian.no_asset_kapal', $this->asetkapal)
							->where('pemakaian.kode_lokasi', $this->lokasi)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'nama2'=>$this->nama2,
						'nama'=>$this->nama,
						'dt'=>$this->dt,
						'kategori'=>$this->kategori,
						'awal'=>$this->awal,
						'akhir'=>$this->akhir,
						'produk'=>$this->produk,
						'namaproduk'=>$this->namaproduk,
						'partnumber'=>$this->partnumber,
						'satuan'=>$this->satuan,
						'kategoriproduk'=>$this->kategoriproduk,
						'harga'=>$this->harga,
						'subtotal'=>$this->subtotal,
						'konek'=>$konek,
						'semua'=>$this->semua
						]);  
					}else{
						return view('/admin/laporanpemakaian/excel_kapal', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('pemakaian.kode_lokasi', $this->lokasi)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'nama2'=>$this->nama2,
						'nama'=>$this->nama,
						'dt'=>$this->dt,
						'kategori'=>$this->kategori,
						'awal'=>$this->awal,
						'akhir'=>$this->akhir,
						'produk'=>$this->produk,
						'namaproduk'=>$this->namaproduk,
						'partnumber'=>$this->partnumber,
						'satuan'=>$this->satuan,
						'kategoriproduk'=>$this->kategoriproduk,
						'harga'=>$this->harga,
						'subtotal'=>$this->subtotal,
						'konek'=>$konek,
						'semua'=>$this->semua
						]);  
					}
				}
			}
			else if($this->status != 'SEMUA' && $this->tipe_pemakaian == 'Other'){
				if($this->kategori != 'SEMUA'){
					return view('/admin/laporanpemakaian/excel_other', [
					'data' => PemakaianDetail::on($konek)
						->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk')
						->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
						->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
						->where('pemakaian.type', $this->tipe_pemakaian)
						->where('pemakaian.status', $this->status)
						->where('produk.kode_kategori', $this->kategori)
						->where('pemakaian.kode_lokasi', $this->lokasi)
						->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
						->get(),
					'nama2'=>$this->nama2,
					'nama'=>$this->nama,
					'dt'=>$this->dt,
					'kategori'=>$this->kategori,
					'awal'=>$this->awal,
					'akhir'=>$this->akhir,
					'produk'=>$this->produk,
					'namaproduk'=>$this->namaproduk,
					'partnumber'=>$this->partnumber,
					'satuan'=>$this->satuan,
					'kategoriproduk'=>$this->kategoriproduk,
					'harga'=>$this->harga,
					'subtotal'=>$this->subtotal,
					'konek'=>$konek,
					'semua'=>$this->semua
					]);
				}
				else if($this->kategori == 'SEMUA'){
					return view('/admin/laporanpemakaian/excel_other', [
					'data' => PemakaianDetail::on($konek)
						->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk')
						->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
						->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
						->where('pemakaian.type', $this->tipe_pemakaian)
						->where('pemakaian.status', $this->status)
						->where('pemakaian.kode_lokasi', $this->lokasi)
						->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
						->get(),
					'nama2'=>$this->nama2,
					'nama'=>$this->nama,
					'dt'=>$this->dt,
					'kategori'=>$this->kategori,
					'awal'=>$this->awal,
					'akhir'=>$this->akhir,
					'produk'=>$this->produk,
					'namaproduk'=>$this->namaproduk,
					'partnumber'=>$this->partnumber,
					'satuan'=>$this->satuan,
					'kategoriproduk'=>$this->kategoriproduk,
					'harga'=>$this->harga,
					'subtotal'=>$this->subtotal,
					'konek'=>$konek,
					'semua'=>$this->semua
					]);
				}
			}
			else if($this->status == 'SEMUA' && $this->tipe_pemakaian == 'Other'){
				if($this->kategori != 'SEMUA'){
					return view('/admin/laporanpemakaian/excel_other', [
					'data' => PemakaianDetail::on($konek)
						->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk')
						->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
						->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
						->where('pemakaian.type', $this->tipe_pemakaian)
						->where('produk.kode_kategori', $this->kategori)
						->where('pemakaian.kode_lokasi', $this->lokasi)
						->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
						->get(),
					'nama2'=>$this->nama2,
					'nama'=>$this->nama,
					'dt'=>$this->dt,
					'kategori'=>$this->kategori,
					'awal'=>$this->awal,
					'akhir'=>$this->akhir,
					'produk'=>$this->produk,
					'namaproduk'=>$this->namaproduk,
					'partnumber'=>$this->partnumber,
					'satuan'=>$this->satuan,
					'kategoriproduk'=>$this->kategoriproduk,
					'harga'=>$this->harga,
					'subtotal'=>$this->subtotal,
					'konek'=>$konek,
					'semua'=>$this->semua
					]);  
				}
				else if($this->kategori == 'SEMUA'){
					return view('/admin/laporanpemakaian/excel_other', [
					'data' => PemakaianDetail::on($konek)
						->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk')
						->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
						->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
						->where('pemakaian.type', $this->tipe_pemakaian)
						->where('pemakaian.kode_lokasi', $this->lokasi)
						->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
						->get(),
					'nama2'=>$this->nama2,
					'nama'=>$this->nama,
					'dt'=>$this->dt,
					'kategori'=>$this->kategori,
					'awal'=>$this->awal,
					'akhir'=>$this->akhir,
					'produk'=>$this->produk,
					'namaproduk'=>$this->namaproduk,
					'partnumber'=>$this->partnumber,
					'satuan'=>$this->satuan,
					'kategoriproduk'=>$this->kategoriproduk,
					'harga'=>$this->harga,
					'subtotal'=>$this->subtotal,
					'konek'=>$konek,
					'semua'=>$this->semua
					]);  
				}
			}
			else if($this->status != 'SEMUA' && $this->tipe_pemakaian == 'SEMUA'){
				if($this->kategori != 'SEMUA'){
					return view('/admin/laporanpemakaian/excel', [
					'data' => PemakaianDetail::on($konek)
						->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal','mobils.nopol','mobils.no_asset_mobil','alat.nama_alat','alat.no_asset_alat')
						->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
						->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
						->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
						->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
						->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
						->where('pemakaian.status', $this->status)
						->where('produk.kode_kategori', $this->kategori)
						->where('pemakaian.kode_lokasi', $this->lokasi)
						->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
						->get(),
					'nama2'=>$this->nama2,
					'tipe'=>$this->tipe_pemakaian,
					'nama'=>$this->nama,
					'dt'=>$this->dt,
					'kategori'=>$this->kategori,
					'awal'=>$this->awal,
					'akhir'=>$this->akhir,
					'produk'=>$this->produk,
					'namaproduk'=>$this->namaproduk,
					'partnumber'=>$this->partnumber,
					'satuan'=>$this->satuan,
					'kategoriproduk'=>$this->kategoriproduk,
					'harga'=>$this->harga,
					'subtotal'=>$this->subtotal,
					'konek'=>$konek,
					'semua'=>$this->semua
					]);
				}
				else if($this->kategori == 'SEMUA'){
					return view('/admin/laporanpemakaian/excel', [
					'data' => PemakaianDetail::on($konek)
						->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal','mobils.nopol','mobils.no_asset_mobil','alat.nama_alat','alat.no_asset_alat')
						->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
						->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
						->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
						->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
						->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
						->where('pemakaian.status', $this->status)
						->where('pemakaian.kode_lokasi', $this->lokasi)
						->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
						->get(),
					'nama2'=>$this->nama2,
					'tipe'=>$this->tipe_pemakaian,
					'nama'=>$this->nama,
					'dt'=>$this->dt,
					'kategori'=>$this->kategori,
					'awal'=>$this->awal,
					'akhir'=>$this->akhir,
					'produk'=>$this->produk,
					'namaproduk'=>$this->namaproduk,
					'partnumber'=>$this->partnumber,
					'satuan'=>$this->satuan,
					'kategoriproduk'=>$this->kategoriproduk,
					'harga'=>$this->harga,
					'subtotal'=>$this->subtotal,
					'konek'=>$konek,
					'semua'=>$this->semua
					]);
				}
			}
			else if($this->status == 'SEMUA' && $this->tipe_pemakaian == 'SEMUA'){
				if($this->kategori != 'SEMUA'){
					return view('/admin/laporanpemakaian/excel', [
					'data' => PemakaianDetail::on($konek)
						->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal','mobils.nopol','mobils.no_asset_mobil','alat.nama_alat','alat.no_asset_alat')
						->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
						->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
						->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
						->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
						->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
						->where('produk.kode_kategori', $this->kategori)
						->where('pemakaian.kode_lokasi', $this->lokasi)
						->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
						->get(),
					'nama2'=>$this->nama2,
					'tipe'=>$this->tipe_pemakaian,
					'nama'=>$this->nama,
					'dt'=>$this->dt,
					'kategori'=>$this->kategori,
					'awal'=>$this->awal,
					'akhir'=>$this->akhir,
					'produk'=>$this->produk,
					'namaproduk'=>$this->namaproduk,
					'partnumber'=>$this->partnumber,
					'satuan'=>$this->satuan,
					'kategoriproduk'=>$this->kategoriproduk,
					'harga'=>$this->harga,
					'subtotal'=>$this->subtotal,
					'konek'=>$konek,
					'semua'=>$this->semua
					]);  
				}
				else if($this->kategori == 'SEMUA'){
					return view('/admin/laporanpemakaian/excel', [
					'data' => PemakaianDetail::on($konek)
						->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal','mobils.nopol','mobils.no_asset_mobil','alat.nama_alat','alat.no_asset_alat')
						->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
						->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
						->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
						->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
						->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
						->where('pemakaian.kode_lokasi', $this->lokasi)
						->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
						->get(),
					'nama2'=>$this->nama2,
					'tipe'=>$this->tipe_pemakaian,
					'nama'=>$this->nama,
					'dt'=>$this->dt,
					'kategori'=>$this->kategori,
					'awal'=>$this->awal,
					'akhir'=>$this->akhir,
					'produk'=>$this->produk,
					'namaproduk'=>$this->namaproduk,
					'partnumber'=>$this->partnumber,
					'satuan'=>$this->satuan,
					'kategoriproduk'=>$this->kategoriproduk,
					'harga'=>$this->harga,
					'subtotal'=>$this->subtotal,
					'konek'=>$konek,
					'semua'=>$this->semua
					]);  
				}
			}
			else{
				if($this->kategori != 'SEMUA'){
					return view('/admin/laporanpemakaian/excel', [
					'data' => PemakaianDetail::on($konek)
						->select('pemakaian_detail.*','pemakaian.*','pemakaian.type','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal','mobils.nopol','mobils.no_asset_mobil','alat.nama_alat','alat.no_asset_alat')
						->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
						->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
						->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
						->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
						->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
						->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
						->where('produk.kode_kategori', $this->kategori)
						->where('pemakaian.kode_lokasi', $this->lokasi)
						->get(),
					'nama2'=>$this->nama2,
					'tipe'=>$this->tipe_pemakaian,
					'nama'=>$this->nama,
					'dt'=>$this->dt,
					'kategori'=>$this->kategori,
					'awal'=>$this->awal,
					'akhir'=>$this->akhir,
					'produk'=>$this->produk,
					'namaproduk'=>$this->namaproduk,
					'partnumber'=>$this->partnumber,
					'satuan'=>$this->satuan,
					'kategoriproduk'=>$this->kategoriproduk,
					'harga'=>$this->harga,
					'subtotal'=>$this->subtotal,
					'konek'=>$konek,
					'semua'=>$this->semua
					]); 
				}
				else if($this->kategori == 'SEMUA'){
					return view('/admin/laporanpemakaian/excel', [
					'data' => PemakaianDetail::on($konek)
						->select('pemakaian_detail.*','pemakaian.*','pemakaian.type','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal','mobils.nopol','mobils.no_asset_mobil','alat.nama_alat','alat.no_asset_alat')
						->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
						->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
						->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
						->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
						->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
						->where('pemakaian.kode_lokasi', $this->lokasi)
						->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
						->get(),
					'nama2'=>$this->nama2,
					'tipe'=>$this->tipe_pemakaian,
					'nama'=>$this->nama,
					'dt'=>$this->dt,
					'kategori'=>$this->kategori,
					'awal'=>$this->awal,
					'akhir'=>$this->akhir,
					'produk'=>$this->produk,
					'namaproduk'=>$this->namaproduk,
					'partnumber'=>$this->partnumber,
					'satuan'=>$this->satuan,
					'kategoriproduk'=>$this->kategoriproduk,
					'harga'=>$this->harga,
					'subtotal'=>$this->subtotal,
					'konek'=>$konek,
					'semua'=>$this->semua
					]); 
				}
			}
		}
		else{
			if($this->status != 'SEMUA' && $this->tipe_pemakaian == 'Alat'){
				if($this->kategori != 'SEMUA'){
					if ($this->asetalat != 'SEMUA'){
						return view('/admin/laporanpemakaian/excel_alat', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('alat', 'pemakaian.kode_alat', '=', 'alat.kode_alat')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('pemakaian.status', $this->status)
							->where('pemakaian.no_asset_alat', $this->asetalat)
							->where('produk.kode_kategori', $this->kategori)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'aset'=>$this->asetalat,
					    'nama2'=>$this->nama2,
					    'nama'=>$this->nama,
    					'dt'=>$this->dt,
    					'kategori'=>$this->kategori,
    					'awal'=>$this->awal,
    					'akhir'=>$this->akhir,
    					'produk'=>$this->produk,
    					'namaproduk'=>$this->namaproduk,
    					'partnumber'=>$this->partnumber,
    					'satuan'=>$this->satuan,
    					'kategoriproduk'=>$this->kategoriproduk,
    					'harga'=>$this->harga,
    					'subtotal'=>$this->subtotal,
    					'konek'=>$konek,
    					'semua'=>$this->semua
						]);
					}
					else{
						return view('/admin/laporanpemakaian/excel_alat', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('pemakaian.status', $this->status)
							->where('produk.kode_kategori', $this->kategori)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
					    'aset'=>$this->asetalat,
					    'nama2'=>$this->nama2,
					    'nama'=>$this->nama,
    					'dt'=>$this->dt,
    					'kategori'=>$this->kategori,
    					'awal'=>$this->awal,
    					'akhir'=>$this->akhir,
    					'produk'=>$this->produk,
    					'namaproduk'=>$this->namaproduk,
    					'partnumber'=>$this->partnumber,
    					'satuan'=>$this->satuan,
    					'kategoriproduk'=>$this->kategoriproduk,
    					'harga'=>$this->harga,
    					'subtotal'=>$this->subtotal,
    					'konek'=>$konek,
    					'semua'=>$this->semua
						]);  
					}
				}
				else if($this->kategori == 'SEMUA'){
					if ($this->asetalat != 'SEMUA'){
						return view('/admin/laporanpemakaian/excel_alat', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('alat', 'pemakaian.kode_alat', '=', 'alat.kode_alat')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('pemakaian.status', $this->status)
							->where('pemakaian.no_asset_alat', $this->asetalat)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'aset'=>$this->asetalat,
					    'nama2'=>$this->nama2,
					    'nama'=>$this->nama,
    					'dt'=>$this->dt,
    					'kategori'=>$this->kategori,
    					'awal'=>$this->awal,
    					'akhir'=>$this->akhir,
    					'produk'=>$this->produk,
    					'namaproduk'=>$this->namaproduk,
    					'partnumber'=>$this->partnumber,
    					'satuan'=>$this->satuan,
    					'kategoriproduk'=>$this->kategoriproduk,
    					'harga'=>$this->harga,
    					'subtotal'=>$this->subtotal,
    					'konek'=>$konek,
    					'semua'=>$this->semua
						]);
					}
					else{
						return view('/admin/laporanpemakaian/excel_alat', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('pemakaian.status', $this->status)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
					    'nama2'=>$this->nama2,
					    'nama'=>$this->nama,
    					'dt'=>$this->dt,
    					'kategori'=>$this->kategori,
    					'awal'=>$this->awal,
    					'akhir'=>$this->akhir,
    					'produk'=>$this->produk,
    					'namaproduk'=>$this->namaproduk,
    					'partnumber'=>$this->partnumber,
    					'satuan'=>$this->satuan,
    					'kategoriproduk'=>$this->kategoriproduk,
    					'harga'=>$this->harga,
    					'subtotal'=>$this->subtotal,
    					'konek'=>$konek,
    					'semua'=>$this->semua
						]);
					}
				}
			}
			else if($this->status == 'SEMUA' && $this->tipe_pemakaian == 'Alat'){
				if($this->kategori != 'SEMUA'){
					if ($this->asetalat != 'SEMUA'){
						return view('/admin/laporanpemakaian/excel_alat', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('alat', 'pemakaian.kode_alat', '=', 'alat.kode_alat')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('pemakaian.no_asset_alat', $this->asetalat)
							->where('produk.kode_kategori', $this->kategori)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'aset'=>$this->asetalat,
					    'nama2'=>$this->nama2,
					    'nama'=>$this->nama,
    					'dt'=>$this->dt,
    					'kategori'=>$this->kategori,
    					'awal'=>$this->awal,
    					'akhir'=>$this->akhir,
    					'produk'=>$this->produk,
    					'namaproduk'=>$this->namaproduk,
    					'partnumber'=>$this->partnumber,
    					'satuan'=>$this->satuan,
    					'kategoriproduk'=>$this->kategoriproduk,
    					'harga'=>$this->harga,
    					'subtotal'=>$this->subtotal,
    					'konek'=>$konek,
    					'semua'=>$this->semua
						]);
					}
					else{
						return view('/admin/laporanpemakaian/excel_alat', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('produk.kode_kategori', $this->kategori)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
					    'nama2'=>$this->nama2,
					    'nama'=>$this->nama,
    					'dt'=>$this->dt,
    					'kategori'=>$this->kategori,
    					'awal'=>$this->awal,
    					'akhir'=>$this->akhir,
    					'produk'=>$this->produk,
    					'namaproduk'=>$this->namaproduk,
    					'partnumber'=>$this->partnumber,
    					'satuan'=>$this->satuan,
    					'kategoriproduk'=>$this->kategoriproduk,
    					'harga'=>$this->harga,
    					'subtotal'=>$this->subtotal,
    					'konek'=>$konek,
    					'semua'=>$this->semua
						]);  
					}
				}
				else if($this->kategori == 'SEMUA'){
					if ($this->asetalat != 'SEMUA'){
						return view('/admin/laporanpemakaian/excel_alat', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('alat', 'pemakaian.kode_alat', '=', 'alat.kode_alat')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('pemakaian.no_asset_alat', $this->asetalat)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'aset'=>$this->asetalat,
					    'nama2'=>$this->nama2,
					    'nama'=>$this->nama,
    					'dt'=>$this->dt,
    					'kategori'=>$this->kategori,
    					'awal'=>$this->awal,
    					'akhir'=>$this->akhir,
    					'produk'=>$this->produk,
    					'namaproduk'=>$this->namaproduk,
    					'partnumber'=>$this->partnumber,
    					'satuan'=>$this->satuan,
    					'kategoriproduk'=>$this->kategoriproduk,
    					'harga'=>$this->harga,
    					'subtotal'=>$this->subtotal,
    					'konek'=>$konek,
    					'semua'=>$this->semua
						]);
					}
					else{
						return view('/admin/laporanpemakaian/excel_alat', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','produk.kode_kategori','produk.nama_produk','alat.nama_alat','alat.no_asset_alat','pemakaian.kode_lokasi')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
					    'nama2'=>$this->nama2,
					    'nama'=>$this->nama,
    					'dt'=>$this->dt,
    					'kategori'=>$this->kategori,
    					'awal'=>$this->awal,
    					'akhir'=>$this->akhir,
    					'produk'=>$this->produk,
    					'namaproduk'=>$this->namaproduk,
    					'partnumber'=>$this->partnumber,
    					'satuan'=>$this->satuan,
    					'kategoriproduk'=>$this->kategoriproduk,
    					'harga'=>$this->harga,
    					'subtotal'=>$this->subtotal,
    					'konek'=>$konek,
    					'semua'=>$this->semua
						]); 
					}
				}
			}
			else if($this->status != 'SEMUA' && $this->tipe_pemakaian == 'Mobil'){
				if($this->kategori != 'SEMUA'){
					if ($this->aset != 'SEMUA'){
						return view('/admin/laporanpemakaian/excel_mobil', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('pemakaian.no_asset_mobil', $this->aset)
							->where('produk.kode_kategori', $this->kategori)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'aset'=>$this->aset,
					    'nama2'=>$this->nama2,
					    'nama'=>$this->nama,
    					'dt'=>$this->dt,
    					'kategori'=>$this->kategori,
    					'awal'=>$this->awal,
    					'akhir'=>$this->akhir,
    					'produk'=>$this->produk,
    					'namaproduk'=>$this->namaproduk,
    					'partnumber'=>$this->partnumber,
    					'satuan'=>$this->satuan,
    					'kategoriproduk'=>$this->kategoriproduk,
    					'harga'=>$this->harga,
    					'subtotal'=>$this->subtotal,
    					'konek'=>$konek,
    					'semua'=>$this->semua
						]);
					}else {
						return view('/admin/laporanpemakaian/excel_mobil', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('produk.kode_kategori', $this->kategori)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'aset'=>$this->aset,
					    'nama2'=>$this->nama2,
					    'nama'=>$this->nama,
    					'dt'=>$this->dt,
    					'stat'=>$this->status,
    					'kategori'=>$this->kategori,
    					'awal'=>$this->awal,
    					'akhir'=>$this->akhir,
    					'produk'=>$this->produk,
    					'namaproduk'=>$this->namaproduk,
    					'partnumber'=>$this->partnumber,
    					'satuan'=>$this->satuan,
    					'kategoriproduk'=>$this->kategoriproduk,
    					'harga'=>$this->harga,
    					'subtotal'=>$this->subtotal,
    					'konek'=>$konek,
    					'semua'=>$this->semua
						]);  
					}
				}
				else if($this->kategori == 'SEMUA'){
					if ($this->aset != 'SEMUA'){
						return view('/admin/laporanpemakaian/excel_mobil', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil','pemakaian.kode_lokasi')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('pemakaian.status', $this->status)
							->where('pemakaian.no_asset_mobil', $this->aset)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'aset'=>$this->aset,
					    'nama2'=>$this->nama2,
					    'nama'=>$this->nama,
    					'dt'=>$this->dt,
    					'kategori'=>$this->kategori,
    					'awal'=>$this->awal,
    					'akhir'=>$this->akhir,
    					'produk'=>$this->produk,
    					'namaproduk'=>$this->namaproduk,
    					'partnumber'=>$this->partnumber,
    					'satuan'=>$this->satuan,
    					'kategoriproduk'=>$this->kategoriproduk,
    					'harga'=>$this->harga,
    					'subtotal'=>$this->subtotal,
    					'konek'=>$konek,
    					'semua'=>$this->semua
						]);
					}else {
						return view('/admin/laporanpemakaian/excel_mobil', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.tanggal_pemakaian','pemakaian.status','pemakaian.pemakai','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil','pemakaian.kode_lokasi')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('pemakaian.status', $this->status)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'aset'=>$this->aset,
					    'nama2'=>$this->nama2,
					    'nama'=>$this->nama,
    					'dt'=>$this->dt,
    					'stat'=>$this->status,
    					'kategori'=>$this->kategori,
    					'awal'=>$this->awal,
    					'akhir'=>$this->akhir,
    					'produk'=>$this->produk,
    					'namaproduk'=>$this->namaproduk,
    					'partnumber'=>$this->partnumber,
    					'satuan'=>$this->satuan,
    					'kategoriproduk'=>$this->kategoriproduk,
    					'harga'=>$this->harga,
    					'subtotal'=>$this->subtotal,
    					'konek'=>$konek,
    					'semua'=>$this->semua
						]);
					}
				}
			}
			else if($this->status == 'SEMUA' && $this->tipe_pemakaian == 'Mobil'){
				if($this->kategori != 'SEMUA'){
					if ($this->aset != 'SEMUA'){
						return view('/admin/laporanpemakaian/excel_mobil', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('produk.kode_kategori', $this->kategori)
							->where('pemakaian.no_asset_mobil', $this->aset)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'aset'=>$this->aset,
					    'nama2'=>$this->nama2,
					    'nama'=>$this->nama,
    					'dt'=>$this->dt,
    					'stat'=>$this->status,
    					'kategori'=>$this->kategori,
    					'awal'=>$this->awal,
    					'akhir'=>$this->akhir,
    					'produk'=>$this->produk,
    					'namaproduk'=>$this->namaproduk,
    					'partnumber'=>$this->partnumber,
    					'satuan'=>$this->satuan,
    					'kategoriproduk'=>$this->kategoriproduk,
    					'harga'=>$this->harga,
    					'subtotal'=>$this->subtotal,
    					'konek'=>$konek,
    					'semua'=>$this->semua
						]);
					}else {
						return view('/admin/laporanpemakaian/excel_mobil', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('produk.kode_kategori', $this->kategori)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'aset'=>$this->aset,
					    'nama2'=>$this->nama2,
					    'nama'=>$this->nama,
					    'stat'=>$this->status,
    					'dt'=>$this->dt,
    					'kategori'=>$this->kategori,
    					'awal'=>$this->awal,
    					'akhir'=>$this->akhir,
    					'produk'=>$this->produk,
    					'namaproduk'=>$this->namaproduk,
    					'partnumber'=>$this->partnumber,
    					'satuan'=>$this->satuan,
    					'kategoriproduk'=>$this->kategoriproduk,
    					'harga'=>$this->harga,
    					'subtotal'=>$this->subtotal,
    					'konek'=>$konek,
    					'semua'=>$this->semua
						]);
					}
				}
				else if($this->kategori == 'SEMUA'){
					if ($this->aset != 'SEMUA'){
						return view('/admin/laporanpemakaian/excel_mobil', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('pemakaian.no_asset_mobil', $this->aset)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'aset'=>$this->aset,
					    'nama2'=>$this->nama2,
					    'nama'=>$this->nama,
    					'dt'=>$this->dt,
    					'stat'=>$this->status,
    					'kategori'=>$this->kategori,
    					'awal'=>$this->awal,
    					'akhir'=>$this->akhir,
    					'produk'=>$this->produk,
    					'namaproduk'=>$this->namaproduk,
    					'partnumber'=>$this->partnumber,
    					'satuan'=>$this->satuan,
    					'kategoriproduk'=>$this->kategoriproduk,
    					'harga'=>$this->harga,
    					'subtotal'=>$this->subtotal,
    					'konek'=>$konek,
    					'semua'=>$this->semua
						]);
					}else {
						return view('/admin/laporanpemakaian/excel_mobil', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','mobils.nopol','mobils.no_asset_mobil')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
						'aset'=>$this->aset,
					    'nama2'=>$this->nama2,
					    'nama'=>$this->nama,
    					'dt'=>$this->dt,
    					'stat'=>$this->status,
    					'kategori'=>$this->kategori,
    					'awal'=>$this->awal,
    					'akhir'=>$this->akhir,
    					'produk'=>$this->produk,
    					'namaproduk'=>$this->namaproduk,
    					'partnumber'=>$this->partnumber,
    					'satuan'=>$this->satuan,
    					'kategoriproduk'=>$this->kategoriproduk,
    					'harga'=>$this->harga,
    					'subtotal'=>$this->subtotal,
    					'konek'=>$konek,
    					'semua'=>$this->semua
						]);
					}
				}
			}
			else if($this->status != 'SEMUA' && $this->tipe_pemakaian == 'Kapal'){
				if($this->kategori != 'SEMUA'){
					if ($this->asetkapal != 'SEMUA'){
						return view('/admin/laporanpemakaian/excel_kapal', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('pemakaian.no_asset_kapal', $this->asetkapal)
							->where('pemakaian.status', $this->status)
							->where('produk.kode_kategori', $this->kategori)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
					    'nama2'=>$this->nama2,
					    'nama'=>$this->nama,
    					'dt'=>$this->dt,
    					'kategori'=>$this->kategori,
    					'awal'=>$this->awal,
    					'akhir'=>$this->akhir,
    					'produk'=>$this->produk,
    					'namaproduk'=>$this->namaproduk,
    					'partnumber'=>$this->partnumber,
    					'satuan'=>$this->satuan,
    					'kategoriproduk'=>$this->kategoriproduk,
    					'harga'=>$this->harga,
    					'subtotal'=>$this->subtotal,
    					'konek'=>$konek,
    					'semua'=>$this->semua
						]);
					}else{
						return view('/admin/laporanpemakaian/excel_kapal', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('pemakaian.status', $this->status)
							->where('produk.kode_kategori', $this->kategori)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
					    'nama2'=>$this->nama2,
					    'nama'=>$this->nama,
    					'dt'=>$this->dt,
    					'kategori'=>$this->kategori,
    					'awal'=>$this->awal,
    					'akhir'=>$this->akhir,
    					'produk'=>$this->produk,
    					'namaproduk'=>$this->namaproduk,
    					'partnumber'=>$this->partnumber,
    					'satuan'=>$this->satuan,
    					'kategoriproduk'=>$this->kategoriproduk,
    					'harga'=>$this->harga,
    					'subtotal'=>$this->subtotal,
    					'konek'=>$konek,
    					'semua'=>$this->semua
						]);
					}
				}
				else if($this->kategori == 'SEMUA'){
					if ($this->asetkapal != 'SEMUA'){
						return view('/admin/laporanpemakaian/excel_kapal', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('pemakaian.no_asset_kapal', $this->asetkapal)
							->where('pemakaian.status', $this->status)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
					    'nama2'=>$this->nama2,
					    'nama'=>$this->nama,
    					'dt'=>$this->dt,
    					'kategori'=>$this->kategori,
    					'awal'=>$this->awal,
    					'akhir'=>$this->akhir,
    					'produk'=>$this->produk,
    					'namaproduk'=>$this->namaproduk,
    					'partnumber'=>$this->partnumber,
    					'satuan'=>$this->satuan,
    					'kategoriproduk'=>$this->kategoriproduk,
    					'harga'=>$this->harga,
    					'subtotal'=>$this->subtotal,
    					'konek'=>$konek,
    					'semua'=>$this->semua
						]);
					}else{
						return view('/admin/laporanpemakaian/excel_kapal', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('pemakaian.status', $this->status)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
					    'nama2'=>$this->nama2,
					    'nama'=>$this->nama,
    					'dt'=>$this->dt,
    					'kategori'=>$this->kategori,
    					'awal'=>$this->awal,
    					'akhir'=>$this->akhir,
    					'produk'=>$this->produk,
    					'namaproduk'=>$this->namaproduk,
    					'partnumber'=>$this->partnumber,
    					'satuan'=>$this->satuan,
    					'kategoriproduk'=>$this->kategoriproduk,
    					'harga'=>$this->harga,
    					'subtotal'=>$this->subtotal,
    					'konek'=>$konek,
    					'semua'=>$this->semua
						]);
					}
				}
			}
			else if($this->status == 'SEMUA' && $this->tipe_pemakaian == 'Kapal'){
				if($this->kategori != 'SEMUA'){
					if ($this->asetkapal != 'SEMUA'){
						return view('/admin/laporanpemakaian/excel_kapal', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('pemakaian.no_asset_kapal', $this->asetkapal)
							->where('produk.kode_kategori', $this->kategori)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
					    'nama2'=>$this->nama2,
					    'nama'=>$this->nama,
    					'dt'=>$this->dt,
    					'kategori'=>$this->kategori,
    					'awal'=>$this->awal,
    					'akhir'=>$this->akhir,
    					'produk'=>$this->produk,
    					'namaproduk'=>$this->namaproduk,
    					'partnumber'=>$this->partnumber,
    					'satuan'=>$this->satuan,
    					'kategoriproduk'=>$this->kategoriproduk,
    					'harga'=>$this->harga,
    					'subtotal'=>$this->subtotal,
    					'konek'=>$konek,
    					'semua'=>$this->semua
						]);  
					}else{
						return view('/admin/laporanpemakaian/excel_kapal', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->where('produk.kode_kategori', $this->kategori)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
					    'nama2'=>$this->nama2,
					    'nama'=>$this->nama,
    					'dt'=>$this->dt,
    					'kategori'=>$this->kategori,
    					'awal'=>$this->awal,
    					'akhir'=>$this->akhir,
    					'produk'=>$this->produk,
    					'namaproduk'=>$this->namaproduk,
    					'partnumber'=>$this->partnumber,
    					'satuan'=>$this->satuan,
    					'kategoriproduk'=>$this->kategoriproduk,
    					'harga'=>$this->harga,
    					'subtotal'=>$this->subtotal,
    					'konek'=>$konek,
    					'semua'=>$this->semua
						]);  
					}
				}
				else if($this->kategori == 'SEMUA'){
					if ($this->asetkapal != 'SEMUA'){
						return view('/admin/laporanpemakaian/excel_kapal', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
							->where('pemakaian.no_asset_kapal', $this->asetkapal)
							->where('pemakaian.type', $this->tipe_pemakaian)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
					    'nama2'=>$this->nama2,
					    'nama'=>$this->nama,
    					'dt'=>$this->dt,
    					'kategori'=>$this->kategori,
    					'awal'=>$this->awal,
    					'akhir'=>$this->akhir,
    					'produk'=>$this->produk,
    					'namaproduk'=>$this->namaproduk,
    					'partnumber'=>$this->partnumber,
    					'satuan'=>$this->satuan,
    					'kategoriproduk'=>$this->kategoriproduk,
    					'harga'=>$this->harga,
    					'subtotal'=>$this->subtotal,
    					'konek'=>$konek,
    					'semua'=>$this->semua
						]);  
					}else{
						return view('/admin/laporanpemakaian/excel_kapal', [
						'data' => PemakaianDetail::on($konek)
							->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal')
							->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
							->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
							->leftjoin('kapal', 'pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
							->where('pemakaian.type', $this->tipe_pemakaian)
							->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
							->get(),
					    'nama2'=>$this->nama2,
					    'nama'=>$this->nama,
    					'dt'=>$this->dt,
    					'kategori'=>$this->kategori,
    					'awal'=>$this->awal,
    					'akhir'=>$this->akhir,
    					'produk'=>$this->produk,
    					'namaproduk'=>$this->namaproduk,
    					'partnumber'=>$this->partnumber,
    					'satuan'=>$this->satuan,
    					'kategoriproduk'=>$this->kategoriproduk,
    					'harga'=>$this->harga,
    					'subtotal'=>$this->subtotal,
    					'konek'=>$konek,
    					'semua'=>$this->semua
						]); 
					}
				}
			}
			else if($this->status != 'SEMUA' && $this->tipe_pemakaian == 'Other'){
				if($this->kategori != 'SEMUA'){
					return view('/admin/laporanpemakaian/excel_other', [
					'data' => PemakaianDetail::on($konek)
						->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk')
						->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
						->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
						->where('pemakaian.type', $this->tipe_pemakaian)
						->where('pemakaian.status', $this->status)
						->where('produk.kode_kategori', $this->kategori)
						->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
						->get(),
					    'nama2'=>$this->nama2,
					    'nama'=>$this->nama,
    					'dt'=>$this->dt,
    					'kategori'=>$this->kategori,
    					'awal'=>$this->awal,
    					'akhir'=>$this->akhir,
    					'produk'=>$this->produk,
    					'namaproduk'=>$this->namaproduk,
    					'partnumber'=>$this->partnumber,
    					'satuan'=>$this->satuan,
    					'kategoriproduk'=>$this->kategoriproduk,
    					'harga'=>$this->harga,
    					'subtotal'=>$this->subtotal,
    					'konek'=>$konek,
    					'semua'=>$this->semua
					]);
				}
				else if($this->kategori == 'SEMUA'){
					return view('/admin/laporanpemakaian/excel_other', [
					'data' => PemakaianDetail::on($konek)
						->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk')
						->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
						->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
						->where('pemakaian.type', $this->tipe_pemakaian)
						->where('pemakaian.status', $this->status)
						->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
						->get(),
					    'nama2'=>$this->nama2,
					    'nama'=>$this->nama,
    					'dt'=>$this->dt,
    					'kategori'=>$this->kategori,
    					'awal'=>$this->awal,
    					'akhir'=>$this->akhir,
    					'produk'=>$this->produk,
    					'namaproduk'=>$this->namaproduk,
    					'partnumber'=>$this->partnumber,
    					'satuan'=>$this->satuan,
    					'kategoriproduk'=>$this->kategoriproduk,
    					'harga'=>$this->harga,
    					'subtotal'=>$this->subtotal,
    					'konek'=>$konek,
    					'semua'=>$this->semua
					]);
				}
			}
			else if($this->status == 'SEMUA' && $this->tipe_pemakaian == 'Other'){
				if($this->kategori != 'SEMUA'){
					return view('/admin/laporanpemakaian/excel_other', [
					'data' => PemakaianDetail::on($konek)
						->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk')
						->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
						->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
						->where('pemakaian.type', $this->tipe_pemakaian)
						->where('produk.kode_kategori', $this->kategori)
						->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
						->get(),
					    'nama2'=>$this->nama2,
					    'nama'=>$this->nama,
    					'dt'=>$this->dt,
    					'kategori'=>$this->kategori,
    					'awal'=>$this->awal,
    					'akhir'=>$this->akhir,
    					'produk'=>$this->produk,
    					'namaproduk'=>$this->namaproduk,
    					'partnumber'=>$this->partnumber,
    					'satuan'=>$this->satuan,
    					'kategoriproduk'=>$this->kategoriproduk,
    					'harga'=>$this->harga,
    					'subtotal'=>$this->subtotal,
    					'konek'=>$konek,
    					'semua'=>$this->semua
					]);  
				}
				else if($this->kategori == 'SEMUA'){
					return view('/admin/laporanpemakaian/excel_other', [
					'data' => PemakaianDetail::on($konek)
						->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk')
						->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
						->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
						->where('pemakaian.type', $this->tipe_pemakaian)
						->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
						->get(),
					    'nama2'=>$this->nama2,
					    'nama'=>$this->nama,
    					'dt'=>$this->dt,
    					'kategori'=>$this->kategori,
    					'awal'=>$this->awal,
    					'akhir'=>$this->akhir,
    					'produk'=>$this->produk,
    					'namaproduk'=>$this->namaproduk,
    					'partnumber'=>$this->partnumber,
    					'satuan'=>$this->satuan,
    					'kategoriproduk'=>$this->kategoriproduk,
    					'harga'=>$this->harga,
    					'subtotal'=>$this->subtotal,
    					'konek'=>$konek,
    					'semua'=>$this->semua
					]);  
				}
			}
			else if($this->status != 'SEMUA' && $this->tipe_pemakaian == 'SEMUA'){
				if($this->kategori != 'SEMUA'){
					return view('/admin/laporanpemakaian/excel', [
					'data' => PemakaianDetail::on($konek)
						->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal','mobils.nopol','mobils.no_asset_mobil','alat.nama_alat','alat.no_asset_alat')
						->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
						->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
						->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
						->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
						->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
						->where('pemakaian.status', $this->status)
						->where('produk.kode_kategori', $this->kategori)
						->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
						->get(),
					    'nama2'=>$this->nama2,
					    'tipe'=>$this->tipe_pemakaian,
					    'nama'=>$this->nama,
    					'dt'=>$this->dt,
    					'kategori'=>$this->kategori,
    					'awal'=>$this->awal,
    					'akhir'=>$this->akhir,
    					'produk'=>$this->produk,
    					'namaproduk'=>$this->namaproduk,
    					'partnumber'=>$this->partnumber,
    					'satuan'=>$this->satuan,
    					'kategoriproduk'=>$this->kategoriproduk,
    					'harga'=>$this->harga,
    					'subtotal'=>$this->subtotal,
    					'konek'=>$konek,
    					'semua'=>$this->semua
					]);
				}
				else if($this->kategori == 'SEMUA'){
					return view('/admin/laporanpemakaian/excel', [
					'data' => PemakaianDetail::on($konek)
						->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal','mobils.nopol','mobils.no_asset_mobil','alat.nama_alat','alat.no_asset_alat')
						->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
						->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
						->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
						->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
						->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
						->where('pemakaian.status', $this->status)
						->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
						->get(),
					    'nama2'=>$this->nama2,
					    'tipe'=>$this->tipe_pemakaian,
					    'nama'=>$this->nama,
    					'dt'=>$this->dt,
    					'kategori'=>$this->kategori,
    					'awal'=>$this->awal,
    					'akhir'=>$this->akhir,
    					'produk'=>$this->produk,
    					'namaproduk'=>$this->namaproduk,
    					'partnumber'=>$this->partnumber,
    					'satuan'=>$this->satuan,
    					'kategoriproduk'=>$this->kategoriproduk,
    					'harga'=>$this->harga,
    					'subtotal'=>$this->subtotal,
    					'konek'=>$konek,
    					'semua'=>$this->semua
					]);
				}
			}
			else if($this->status == 'SEMUA' && $this->tipe_pemakaian == 'SEMUA'){
				if($this->kategori != 'SEMUA'){
					return view('/admin/laporanpemakaian/excel', [
					'data' => PemakaianDetail::on($konek)
						->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal','mobils.nopol','mobils.no_asset_mobil','alat.nama_alat','alat.no_asset_alat')
						->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
						->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
						->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
						->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
						->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
						->where('produk.kode_kategori', $this->kategori)
						->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
						->get(),
					    'nama2'=>$this->nama2,
					    'tipe'=>$this->tipe_pemakaian,
					    'nama'=>$this->nama,
    					'dt'=>$this->dt,
    					'kategori'=>$this->kategori,
    					'awal'=>$this->awal,
    					'akhir'=>$this->akhir,
    					'produk'=>$this->produk,
    					'namaproduk'=>$this->namaproduk,
    					'partnumber'=>$this->partnumber,
    					'satuan'=>$this->satuan,
    					'kategoriproduk'=>$this->kategoriproduk,
    					'harga'=>$this->harga,
    					'subtotal'=>$this->subtotal,
    					'konek'=>$konek,
    					'semua'=>$this->semua
					]);  
				}
				else if($this->kategori == 'SEMUA'){
					return view('/admin/laporanpemakaian/excel', [
					'data' => PemakaianDetail::on($konek)
						->select('pemakaian_detail.*','pemakaian.*','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal','mobils.nopol','mobils.no_asset_mobil','alat.nama_alat','alat.no_asset_alat')
						->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
						->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
						->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
						->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
						->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
						->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
						->get(),
					    'nama2'=>$this->nama2,
					    'tipe'=>$this->tipe_pemakaian,
					    'nama'=>$this->nama,
    					'dt'=>$this->dt,
    					'kategori'=>$this->kategori,
    					'awal'=>$this->awal,
    					'akhir'=>$this->akhir,
    					'produk'=>$this->produk,
    					'namaproduk'=>$this->namaproduk,
    					'partnumber'=>$this->partnumber,
    					'satuan'=>$this->satuan,
    					'kategoriproduk'=>$this->kategoriproduk,
    					'harga'=>$this->harga,
    					'subtotal'=>$this->subtotal,
    					'konek'=>$konek,
    					'semua'=>$this->semua
					]);  
				}
			}
			else{
				if($this->kategori != 'SEMUA'){
					return view('/admin/laporanpemakaian/excel', [
					'data' => PemakaianDetail::on($konek)
						->select('pemakaian_detail.*','pemakaian.*','pemakaian.type','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal','mobils.nopol','mobils.no_asset_mobil','alat.nama_alat','alat.no_asset_alat')
						->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
						->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
						->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
						->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
						->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
						->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
						->where('produk.kode_kategori', $this->kategori)
						->get(),
					    'nama2'=>$this->nama2,
					    'tipe'=>$this->tipe_pemakaian,
					    'nama'=>$this->nama,
    					'dt'=>$this->dt,
    					'kategori'=>$this->kategori,
    					'awal'=>$this->awal,
    					'akhir'=>$this->akhir,
    					'produk'=>$this->produk,
    					'namaproduk'=>$this->namaproduk,
    					'partnumber'=>$this->partnumber,
    					'satuan'=>$this->satuan,
    					'kategoriproduk'=>$this->kategoriproduk,
    					'harga'=>$this->harga,
    					'subtotal'=>$this->subtotal,
    					'konek'=>$konek,
    					'semua'=>$this->semua
					]); 
				}
				else if($this->kategori == 'SEMUA'){
					return view('/admin/laporanpemakaian/excel', [
					'data' => PemakaianDetail::on($konek)
						->select('pemakaian_detail.*','pemakaian.*','pemakaian.type','produk.kode_kategori','produk.nama_produk','kapal.nama_kapal','kapal.no_asset_kapal','mobils.nopol','mobils.no_asset_mobil','alat.nama_alat','alat.no_asset_alat')
						->join('pemakaian', 'pemakaian_detail.no_pemakaian', '=', 'pemakaian.no_pemakaian')
						->join('produk','pemakaian_detail.kode_produk', '=', 'produk.id')
						->leftjoin('mobils', 'pemakaian.kode_mobil', '=', 'mobils.kode_mobil')
						->leftjoin('alat','pemakaian.kode_alat', '=', 'alat.kode_alat')
						->leftjoin('kapal','pemakaian.kode_kapal', '=', 'kapal.kode_kapal')
						->whereBetween('pemakaian.tanggal_pemakaian', array($this->awal, $this->akhir))
						->get(),
					    'nama2'=>$this->nama2,
					    'tipe'=>$this->tipe_pemakaian,
					    'nama'=>$this->nama,
    					'dt'=>$this->dt,
    					'kategori'=>$this->kategori,
    					'awal'=>$this->awal,
    					'akhir'=>$this->akhir,
    					'produk'=>$this->produk,
    					'namaproduk'=>$this->namaproduk,
    					'partnumber'=>$this->partnumber,
    					'satuan'=>$this->satuan,
    					'kategoriproduk'=>$this->kategoriproduk,
    					'harga'=>$this->harga,
    					'subtotal'=>$this->subtotal,
    					'konek'=>$konek,
    					'semua'=>$this->semua
					]); 
				}
			}
		}
	}
}