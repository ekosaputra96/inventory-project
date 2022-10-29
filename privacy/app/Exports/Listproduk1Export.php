<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Models\Produk;

class Listproduk1Export implements FromView
{
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

    public function __construct(string $kode_company, string $nama_company){
        $this->kode_company = $kode_company;
        $this->nama_company = $nama_company;
    }

    public function view(): View {
        // getting data from Produk table
        $data = Produk::on($this->connection())->orderBy('id')->get();

        return view('/admin/produk1/listprodukexcel', [
            'data' => $data,
            'nama_company' => $this->nama_company
        ]);
    }
}
