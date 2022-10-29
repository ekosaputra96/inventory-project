<?php
 
namespace App\Exports;
 
use App\Models\tb_produk_history;
use App\Models\tb_item_bulanan;
use App\Models\Produk;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Carbon;

class KartustokExport implements FromView
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

   	public function __construct(string $request2, string $cetak, string $pilih, string $request, string $lokasi, string $total_begin, string $total_ending, string $total_amount_begin, string $total_amount_ending)
    {
        $this->request2 = $request2;
        $this->req = $request;
        $this->lokasi = $lokasi;
        $this->cetak = $cetak;
        $this->pilih = $pilih;
        $this->total_begin = $total_begin;
        $this->total_ending = $total_ending;
        $this->total_amount_begin = $total_amount_begin;
        $this->total_amount_ending = $total_amount_ending;
    }

    public function view(): View
    {   
        $konek = static::konek();
        $lokasi2 = auth()->user()->kode_lokasi;

        $tanggal_baru = $this->request2;
        $bulan = Carbon\Carbon::parse($tanggal_baru)->format('m');
        $tahun = Carbon\Carbon::parse($tanggal_baru)->format('Y');
        $nama_bulan = Carbon\Carbon::parse($tanggal_baru)->format('F');

        $tanggal_awal = tb_item_bulanan::on($konek)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

        if ($this->pilih == 'Produk'){
            $request = $this->req;
            $produk = Produk::on($konek)->where('id',$request)->first();
            $nama_produk = $produk->nama_produk;
            $kode_satuan = $produk->kode_satuan;
            $tipe_produk = $produk->tipe_produk;
            $kode_kategori = $produk->kode_kategori;
            $total_begin = 0;
            $total_ending = 0;
            $total_amount_begin = 0;
            $total_amount_ending = 0;

            if ($lokasi2 == 'HO') {
                $lokasi = $this->lokasi;
                if ($lokasi != 'SEMUA') {
                    $request4 = $this->lokasi;
                    $request2 = $this->request2;
                    $tanggal = $tanggal_awal->periode;
                    $get_lokasi = auth()->user()->kode_lokasi;
                    return view('/admin/kartustok/excel', [
                        'kartustok_cetak' => tb_produk_history::on($konek)->orderBy('created_at','asc')->where('kode_produk',$request)->where('kode_lokasi',$request4)->whereMonth('tanggal_transaksi', $bulan)->whereYear('tanggal_transaksi', $tahun)->where('tanggal_transaksi', '<=', $request2)->get(),
                        'kartustok_saldo' => tb_item_bulanan::on($konek)->where('kode_produk',$request)->where('kode_lokasi',$request4)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first(),
                        'request' => $request,
                        'nama_produk' => $nama_produk,
                        'bulan' => $bulan,
                        'tahun' => $tahun,
                        'request2' => $request2,
                        'lokasi' => $lokasi,
                        'kode_satuan' => $kode_satuan,
                        'tipe_produk' => $tipe_produk,
                        'kode_kategori' => $kode_kategori,
                        'total_begin' => $this->total_begin,
                        'total_ending' => $this->total_ending,
                        'total_amount_begin' => $this->total_amount_begin,
                        'total_amount_ending' => $this->total_amount_ending 
                    ]);
                }else {
                    $request4 = $this->lokasi;
                    $request2 = $this->request2;
                    $tanggal = $tanggal_awal->periode;
                    $get_lokasi = auth()->user()->kode_lokasi;
                    return view('/admin/kartustok/excel', [
                        'kartustok_cetak' => tb_produk_history::on($konek)->orderBy('created_at','asc')->where('kode_produk',$request)->whereMonth('tanggal_transaksi', $bulan)->whereYear('tanggal_transaksi', $tahun)->where('tanggal_transaksi', '<=', $request2)->get(),
                        'kartustok_saldo' => tb_item_bulanan::on($konek)->where('kode_produk',$request)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first(),
                        'request' => $request,
                        'nama_produk' => $nama_produk,
                        'bulan' => $bulan,
                        'tahun' => $tahun,
                        'request2' => $request2,
                        'lokasi' => $lokasi,
                        'kode_satuan' => $kode_satuan,
                        'tipe_produk' => $tipe_produk,
                        'kode_kategori' => $kode_kategori,
                        'total_begin' => $this->total_begin,
                        'total_ending' => $this->total_ending,
                        'total_amount_begin' => $this->total_amount_begin,
                        'total_amount_ending' => $this->total_amount_ending 
                    ]);
                }
            }else {
                $request4 = $this->lokasi;
                $request2 = $this->request2;
                $tanggal = $tanggal_awal->periode;
                $get_lokasi = auth()->user()->kode_lokasi;
                return view('/admin/kartustok/excel', [
                    'kartustok_cetak' => tb_produk_history::on($konek)->orderBy('created_at','asc')->where('kode_produk',$request)->where('kode_lokasi',$lokasi2)->whereMonth('tanggal_transaksi', $bulan)->whereYear('tanggal_transaksi', $tahun)->where('tanggal_transaksi', '<=', $request2)->get(),
                    'kartustok_saldo' => tb_item_bulanan::on($konek)->where('kode_produk',$request)->where('kode_lokasi',$lokasi2)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first(),
                    'request' => $request,
                    'nama_produk' => $nama_produk,
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'request2' => $request2,
                    'lokasi' => $get_lokasi,
                    'kode_satuan' => $kode_satuan,
                    'tipe_produk' => $tipe_produk,
                    'kode_kategori' => $kode_kategori,
                    'total_begin' => $this->total_begin,
                    'total_ending' => $this->total_ending,
                    'total_amount_begin' => $this->total_amount_begin,
                    'total_amount_ending' => $this->total_amount_ending 
                ]);
            }
        }else {
            $kode_kategori = $this->req;
            if ($lokasi2 == 'HO'){
                $lokasi = $this->lokasi;
                if ($lokasi != 'SEMUA') {
                    $request4 = $this->lokasi;
                    $request2 = $this->request2;
                    $tanggal = $tanggal_awal->periode;
                    $get_lokasi = auth()->user()->kode_lokasi;
                    return view('/admin/kartustok/excel2', [
                        'kartustok_cetak' => tb_produk_history::on($konek)
                            ->select('tb_produk_history.*','produk.id','produk.nama_produk','produk.kode_kategori','produk.kode_satuan')
                            ->join('produk','tb_produk_history.kode_produk', '=', 'produk.id')
                            ->where('produk.kode_kategori', $kode_kategori)
                            ->where('tb_produk_history.kode_lokasi', $request4)
                            ->whereMonth('tb_produk_history.tanggal_transaksi', $bulan)
                            ->whereYear('tb_produk_history.tanggal_transaksi', $tahun)
                            ->where('tb_produk_history.tanggal_transaksi', '<=', $request2)
                            ->orderBy('produk.id','asc')
                            ->orderBy('tb_produk_history.created_at','asc')
                            ->get(),
                        'kartustok_saldo' => tb_item_bulanan::on($konek)
                            ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori','produk.kode_satuan')
                            ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                            ->where('produk.kode_kategori', $kode_kategori)
                            ->where('tb_item_bulanan.kode_lokasi', $request4)
                            ->whereMonth('tb_item_bulanan.periode', $bulan)
                            ->whereYear('tb_item_bulanan.periode', $tahun)
                            ->orderBy('produk.id','asc')
                            ->get(),
                        'request' => $kode_kategori,
                        'bulan' => $bulan,
                        'tahun' => $tahun,
                        'request2' => $request2,
                        'lokasi' => $lokasi,
                        'kode_kategori' => $kode_kategori
                    ]);
                }else {
                    $request4 = $this->lokasi;
                    $request2 = $this->request2;
                    $tanggal = $tanggal_awal->periode;
                    $get_lokasi = auth()->user()->kode_lokasi;
                    return view('/admin/kartustok/excel2', [
                        'kartustok_cetak' => tb_produk_history::on($konek)
                            ->select('tb_produk_history.*','produk.id','produk.nama_produk','produk.kode_kategori','produk.kode_satuan')
                            ->join('produk','tb_produk_history.kode_produk', '=', 'produk.id')
                            ->where('produk.kode_kategori', $kode_kategori)
                            ->whereMonth('tb_produk_history.tanggal_transaksi', $bulan)
                            ->whereYear('tb_produk_history.tanggal_transaksi', $tahun)
                            ->where('tb_produk_history.tanggal_transaksi', '<=', $request2)
                            ->orderBy('produk.id','asc')
                            ->orderBy('tb_produk_history.created_at','asc')
                            ->get(),
                        'kartustok_saldo' => tb_item_bulanan::on($konek)
                            ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori','produk.kode_satuan')
                            ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                            ->where('produk.kode_kategori', $kode_kategori)
                            ->whereMonth('tb_item_bulanan.periode', $bulan)
                            ->whereYear('tb_item_bulanan.periode', $tahun)
                            ->orderBy('produk.id','asc')
                            ->get(),
                        'request' => $kode_kategori,
                        'bulan' => $bulan,
                        'tahun' => $tahun,
                        'request2' => $request2,
                        'lokasi' => $lokasi,
                        'kode_kategori' => $kode_kategori
                    ]);
                }
            }else {
                $request4 = $this->lokasi;
                $request2 = $this->request2;
                $tanggal = $tanggal_awal->periode;
                $get_lokasi = auth()->user()->kode_lokasi;
                return view('/admin/kartustok/excel2', [
                    'kartustok_cetak' => tb_produk_history::on($konek)
                        ->select('tb_produk_history.*','produk.id','produk.nama_produk','produk.kode_kategori','produk.kode_satuan')
                        ->join('produk','tb_produk_history.kode_produk', '=', 'produk.id')
                        ->where('produk.kode_kategori', $kode_kategori)
                        ->where('tb_produk_history.kode_lokasi', $get_lokasi)
                        ->whereMonth('tb_produk_history.tanggal_transaksi', $bulan)
                        ->whereYear('tb_produk_history.tanggal_transaksi', $tahun)
                        ->where('tb_produk_history.tanggal_transaksi', '<=', $request2)
                        ->orderBy('produk.id','asc')
                        ->orderBy('tb_produk_history.created_at','asc')
                        ->get(),
                    'kartustok_saldo' => tb_item_bulanan::on($konek)
                        ->select('tb_item_bulanan.*','produk.id','produk.nama_produk','produk.kode_kategori','produk.kode_satuan')
                        ->join('produk','tb_item_bulanan.kode_produk', '=', 'produk.id')
                        ->where('produk.kode_kategori', $kode_kategori)
                        ->where('tb_item_bulanan.kode_lokasi', $get_lokasi)
                        ->whereMonth('tb_item_bulanan.periode', $bulan)
                        ->whereYear('tb_item_bulanan.periode', $tahun)
                        ->orderBy('produk.id','asc')
                        ->get(),
                    'request' => $kode_kategori,
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'request2' => $request2,
                    'lokasi' => auth()->user()->kode_lokasi,
                    'kode_kategori' => $kode_kategori
                ]);
            }
        }

    }
}