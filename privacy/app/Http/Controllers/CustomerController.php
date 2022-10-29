<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Customer;
use App\Models\Penjualan;
use App\Models\tb_akhir_bulan;
use App\Models\MasterLokasi;
use App\Models\Company;
use Carbon;
use DB;

class CustomerController extends Controller
{
    public function konek()
    {
        $compa2 = auth()->user()->kode_company;
        $compa = substr($compa2,0,2);
        if ($compa == '01'){
            $koneksi = 'mysqldepo';
        }else if ($compa == '02'){
            $koneksi = 'mysql_front_pbm';
        }else if ($compa == '99'){
            $koneksi = 'mysqlpbmlama';
        }else if ($compa == '03'){
            $koneksi = 'mysql_front_emkl';
        }else if ($compa == '22'){
            $koneksi = 'mysqlskt';
        }else if ($compa == '04'){
            $koneksi = 'mysqlgut';
        }else if ($compa == '05'){
            $koneksi = 'mysql_front_sub';
        }else if ($compa == '06'){
            $koneksi = 'mysql_front_inf';
        }
        return $koneksi;
    }

    public function index()
    {
        $konek = self::konek();
        $create_url = route('customer.create');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;
        
        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        return view('admin.customer.index',compact('create_url','period', 'nama_lokasi','nama_company'));
        
    }

    public function anyData()
    {
        $konek = self::konek();
        return Datatables::of(Customer::on($konek)->with('coa')->orderby('nama_customer','asc'))->make(true);
    }

    public function store(Request $request)
    {
        $konek = self::konek();
        $nama_customer = $request->nama_customer;
        $cek_customer = Customer::on($konek)->where('nama_customer',$nama_customer)->first();
        if($cek_customer == null){
            Customer::on($konek)->create($request->all());

                //KONVERSI DIGUNAKAN UNTUK KONVERSI SIMBOL '&' AGAR TIDAK EROR SAAT TARIL EXCEL
            $konversi_simbol = Customer::where('nama_customer', 'LIKE', '%&%')->update(['nama_customer' => DB::raw("REPLACE(nama_customer,  '&', 'DAN')")]);

            $konversi_simbol2 = Customer::where('nama_customer_po', 'LIKE', '%&%')->update(['nama_customer_po' => DB::raw("REPLACE(nama_customer_po,  '&', 'DAN')")]);

            $konversi_simbol3 = Customer::where('alamat', 'LIKE', '%&%')->update(['alamat' => DB::raw("REPLACE(alamat,  '&', 'DAN')")]);

            $konversi_simbol4 = Customer::where('alamat2', 'LIKE', '%&%')->update(['alamat2' => DB::raw("REPLACE(alamat2,  '&', 'DAN')")]);

            $konversi_simbol5 = Customer::where('alamat3', 'LIKE', '%&%')->update(['alamat3' => DB::raw("REPLACE(alamat3,  '&', 'DAN')")]);

            $konversi_simbol6 = Customer::where('alamat4', 'LIKE', '%&%')->update(['alamat4' => DB::raw("REPLACE(alamat4,  '&', 'DAN')")]);

            $konversi_simbol7 = Customer::where('nama_kontak', 'LIKE', '%&%')->update(['nama_kontak' => DB::raw("REPLACE(nama_kontak,  '&', 'DAN')")]);

            $message = [
                'success' => true,
                'title' => 'Simpan',
                'message' => 'Data telah disimpan.'
            ];
            return response()->json($message);
        }
        else{
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Nama customer Sudah Ada',
            ];
            return response()->json($message);
        }  
    }

    public function edit_customer()
    {
        $konek = self::konek();
        $id = request()->id;
        $data = Customer::on($konek)->find($id);
        $output = array(
            'id'=>$data->id,
            'nama_customer'=>$data->nama_customer,
            'nama_customer_po'=>$data->nama_customer_po,
            'alamat'=>$data->alamat,
            'kota'=>$data->kota,
            'kode_pos'=>$data->kode_pos,
            'telp'=>$data->telp,
            'fax'=>$data->fax,
            'hp'=>$data->hp,
            'nama_kontak'=>$data->nama_kontak,
            'npwp'=>$data->npwp,
            'no_kode_pajak'=>$data->no_kode_pajak,
            'status'=>$data->status,
        );
        return response()->json($output);
    }

    public function updateAjax(Request $request)
    {
        $konek = self::konek();
        $id = $request->id;
        $cek_customer2 = Penjualan::on($konek)->where('kode_customer',$id)->first();
        Customer::on($konek)->find($request->id)->update($request->all());
        
        $message = [
            'success' => true,
            'title' => 'Update',
            'message' => 'Data telah di Update.'
        ];
        return response()->json($message);
    }

    public function hapus_customer()
    {   
        $konek = self::konek();
        $id = request()->id;
        $customer = Customer::on($konek)->find(request()->id);
        $cek_customer2 = Penjualan::on($konek)->where('kode_customer',$id)->first();

        if ($cek_customer2 == null){
            $customer->delete();

            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data ['.$customer->nama_customer.'] telah dihapus.'
            ];
            return response()->json($message);
        } else {
            $message = [
                'success' => false,
                'title' => 'Update',
                'message' => 'Data ['.$customer->nama_customer.'] dipakai dalam transaksi.'
            ];
            return response()->json($message);
        }
        
    }

}
