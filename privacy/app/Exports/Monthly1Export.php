<?php

namespace App\Exports;

use App\Models\tb_item_bulanan;
use App\Models\tb_produk_history;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class Monthly1Export implements FromView {
    // get the connection according to the logged in user
    public function connection()
    {
        // getting company code from the logged in user
        $company_code = substr(auth()->user()->kode_company, 0, 2);

        // getting connection name according to company code
        switch ($company_code) {
            case '01':
                $connection = 'mysqldepo';
                break;
            case '02':
                $connection = 'mysqlpbm';
                break;
            case '99':
                $connection = 'mysqlpbmlama';
                break;
            case '03':
                $connection = 'mysqlemkl';
                break;
            case '22':
                $connection = 'mysqlskt';
                break;
            case '04':
                $connection = 'mysqlgut';
                break;
            case '05':
                $connection = 'mysql';
                break;
            case '06':
                $connection = 'mysqlinfra';
                break;
            default:
                abort(404, 'Connection is not found !');
                break;
        }
        return $connection;
    }

    // the constructor for the class
    public function __construct(string $kode_produk, string $lokasi, string $tanggal_awal, string $tanggal_akhir, string $show, string $get_nama_produk, bool $pemakaian = false, bool $penerimaan = false, bool $penjualan = false, bool $adjustment = false, bool $opname = false, bool $transferin = false, bool $transferout = false, bool $returbeli = false, bool $returjual = false, bool $disassembling = false, bool $assembling = false, bool $semua = false)
    {
        // set all the parameter
        $this->kode_produk = $kode_produk;
        $this->lokasi = $lokasi;
        $this->tanggal_awal = $tanggal_awal;
        $this->tanggal_akhir = $tanggal_akhir;
        $this->show = $show;
        $this->get_nama_produk = $get_nama_produk;
        $this->pemakaian = $pemakaian;
        $this->penerimaan = $penerimaan;
        $this->penjualan = $penjualan;
        $this->adjustment = $adjustment;
        $this->opname = $opname;
        $this->transferin = $transferin;
        $this->transferout = $transferout;
        $this->returbeli = $returbeli;
        $this->returjual = $returjual;
        $this->disassembling = $disassembling;
        $this->assembling = $assembling;
        $this->semua = $semua;
    }

    public function view(): View {
        
        // check if the lokasi is not 'SEMUA'
        if($this->lokasi != 'SEMUA'){
            // check if show is 'Monthly'

            if($this->show == 'Monthly'){
                // getting data from tb_item_bulanan
                $data = tb_item_bulanan::on($this->connection())->with('produk')->whereBetween('periode', [$this->tanggal_awal, $this->tanggal_akhir])->where('kode_produk', $this->kode_produk)->where('kode_lokasi', auth()->user()->kode_lokasi)->orderBy('periode', 'asc')->get();
                return view('/admin/produk1/monthlyexcel', [
                    'data' => $data,
                    'nama_produk' => $this->get_nama_produk,
                    'pemakaian' => $this->pemakaian,
                    'penerimaan' => $this->penerimaan,
                    'penjualan' => $this->penjualan,
                    'adjustment' => $this->adjustment,
                    'opname' => $this->opname,
                    'transferin' => $this->transferin,
                    'transferout' => $this->transferout,
                    'returbeli' => $this->returbeli,
                    'returjual' => $this->returjual,
                    'disassembling' => $this->disassembling,
                    'assembling' => $this->assembling,
                    'semua' => $this->semua,
                    'lokasi' =>$this->lokasi,
                ]);
            }else{
                // if show is 'Transaksi'
                // getting data from tb_produk_history
                $data = tb_produk_history::on($this->connection())->with('produk')->where('kode_produk', $this->kode_produk)->whereBetween('tanggal_transaksi', [$this->tanggal_awal, $this->tanggal_akhir])->where('kode_lokasi', auth()->user()->kode_lokasi)->orderBy('created_at', 'asc')->get();

                return view('/admin/produk1/transaksiexcel', [
                    'data' => $data,
                    'nama_produk' => $this->get_nama_produk
                ]);
            }
        }else{
            // if the lokasi is not 'SEMUA'
            // check if the show is 'Monthly'
            if($this->show == 'Monthly'){
                // getting data from tb_item_bulanan
                $data = tb_item_bulanan::on($this->connection())->with('produk')->whereBetween('periode', [$this->tanggal_awal, $this->tanggal_akhir])->where('kode_produk', $this->kode_produk)->orderBy('periode', 'asc')->get();
                return view('/admin/produk1/monthlyexcel', [
                    'data' => $data,
                    'nama_produk' => $this->get_nama_produk,
                    'pemakaian' => $this->pemakaian,
                    'penerimaan' => $this->penerimaan,
                    'penjualan' => $this->penjualan,
                    'adjustment' => $this->adjustment,
                    'opname' => $this->opname,
                    'transferin' => $this->transferin,
                    'transferout' => $this->transferout,
                    'returbeli' => $this->returbeli,
                    'returjual' => $this->returjual,
                    'disassembling' => $this->disassembling,
                    'assembling' => $this->assembling,
                    'semua' => $this->semua,
                    'lokasi' =>$this->lokasi,
                ]);
            }else{
                // check if the show is 'Transaksi'
                // getting data from tb_produk_history
                $data = tb_produk_history::on($this->connection())->with('produk')->where('kode_produk', $this->kode_produk)->whereBetween('tanggal_transaksi', [$this->tanggal_awal, $this->tanggal_akhir])->orderBy('created_at', 'asc')->get();

                return view('/admin/produk1/transaksiexcel', [
                    'data' => $data,
                    'nama_produk' => $this->get_nama_produk
                ]);
            }
        }
    }
}