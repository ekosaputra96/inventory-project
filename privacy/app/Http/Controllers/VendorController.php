<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Vendor;
use App\Models\VendorCoa;
use App\Models\VendorCounter;
use App\Models\Pembelian;
use App\Models\tb_akhir_bulan;
use App\Models\MasterLokasi;
use App\Models\Company;
use App\Models\Coa;
use App\Models\Systemsetup;
use Carbon;
use DB;

class VendorController extends Controller
{
    public function konek()
    {
        $compa = auth()->user()->kode_company;
        if ($compa == '01'){
            $koneksi = 'mysqldepo';
        }else if ($compa == '02'){
            $koneksi = 'mysqlpbm';
        }else if ($compa == '99'){
            $koneksi = 'mysqlpbmlama';
        }else if ($compa == '0401'){
            $koneksi = 'mysqlgutjkt';
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
    
    public function index()
    {
        $konek = self::konek();
        $create_url = route('vendor.create');

        $Coa = Coa::select('coa.kode_coa', DB::raw("concat(coa.account,' - ',coa.ac_description) as coas"))->join('u5611458_gui_general_ledger_laravel.coa_detail','coa.kode_coa','=','u5611458_gui_general_ledger_laravel.coa_detail.kode_coa')->where('u5611458_gui_general_ledger_laravel.coa_detail.kode_company', auth()->user()->kode_company)->pluck('coas','coa.kode_coa');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        $level = auth()->user()->level;
        return view('admin.vendor.index',compact('create_url','period', 'nama_lokasi','nama_company','Coa'));
        
    }

    public function anyData()
    {
        $level = auth()->user()->level;
            return Datatables::of(Vendor::with('coa')->orderby('nama_vendor','asc'))->make(true);
    }

    public function getcoa()
    {
        //Coa Hutang Usaha
        $get_setup = Systemsetup::find('18');
        $kode_coa = $get_setup->kode_setup;
        $output = [
            'kode_coa'=>$kode_coa,
        ];
        return response()->json($output);
    }

    public function store(Request $request)
    {
        $datas= $request->all();
        if($request->type == '1'){
            $datas['kode_coa'] = $request->kode_coa;

            $nama_vendor = $request->nama_vendor;
            $cek_vendor = Vendor::where('nama_vendor',$nama_vendor)->first();
            if($cek_vendor == null){
                Vendor::create($datas);
                //KONVERSI DIGUNAKAN UNTUK KONVERSI SIMBOL '&' AGAR TIDAK EROR SAAT TARIL EXCEL
                // $konversi_simbol = Vendor::where('nama_vendor', 'LIKE', '%&%')->update(['nama_vendor' => DB::raw("REPLACE(nama_vendor,  '&', 'DAN')")]);
                // $konversi_simbol2 = Vendor::where('nama_vendor_po', 'LIKE', '%&%')->update(['nama_vendor_po' => DB::raw("REPLACE(nama_vendor_po,  '&', 'DAN')")]);
                $konversi_simbol3 = Vendor::where('alamat', 'LIKE', '%&%')->update(['alamat' => DB::raw("REPLACE(alamat,  '&', 'DAN')")]);
                // $konversi_simbol4 = Vendor::where('nama_kontak', 'LIKE', '%&%')->update(['nama_kontak' => DB::raw("REPLACE(nama_kontak,  '&', 'DAN')")]);
            }else{
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'Nama Vendor Sudah Ada',
                ];
                return response()->json($message);
            }  
        }else{
            $nama_vendor = $request->nama_vendor;
            $cek_vendor = Vendor::where('nama_vendor',$nama_vendor)->first();
            if($cek_vendor == null){
                Vendor::create($datas);
                //KONVERSI DIGUNAKAN UNTUK KONVERSI SIMBOL '&' AGAR TIDAK EROR SAAT TARIL EXCEL
                // $konversi_simbol = Vendor::where('nama_vendor', 'LIKE', '%&%')->update(['nama_vendor' => DB::raw("REPLACE(nama_vendor,  '&', 'DAN')")]);
                // $konversi_simbol2 = Vendor::where('nama_vendor_po', 'LIKE', '%&%')->update(['nama_vendor_po' => DB::raw("REPLACE(nama_vendor_po,  '&', 'DAN')")]);
                $konversi_simbol3 = Vendor::where('alamat', 'LIKE', '%&%')->update(['alamat' => DB::raw("REPLACE(alamat,  '&', 'DAN')")]);
                // $konversi_simbol4 = Vendor::where('nama_kontak', 'LIKE', '%&%')->update(['nama_kontak' => DB::raw("REPLACE(nama_kontak,  '&', 'DAN')")]);
            }else{
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'Nama Vendor Sudah Ada',
                ];
                return response()->json($message);
            }
        }
        $vendor = Vendor::orderBy('created_at', 'desc')->first();
        $compan = Company::where('status', 'Aktif')->get();

        foreach ($compan as $row) {
            $isicoa = [
                'kode_vendor'=>$vendor->id,
                'kode_coa'=>$vendor->kode_coa,
                'kode_company'=>$row->kode_company,
            ];
            VendorCoa::create($isicoa);
        }

        $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => 'Data telah disimpan.'
        ];
        return response()->json($message);
    }

    public function edit_vendor()
    {
        $id = request()->id;
        $data = Vendor::find($id);
        $output = array(
            'id'=>$data->id,
            'type'=>$data->type,
            'email'=>$data->email,
            'nama_vendor'=>$data->nama_vendor,
            'nama_vendor_po'=>$data->nama_vendor_po,
            'alamat'=>$data->alamat,
            'telp'=>$data->telp,
            'hp'=>$data->hp,
            'norek_vendor'=>$data->norek_vendor,
            'nama_kontak'=>$data->nama_kontak,
            'npwp'=>$data->npwp,
            'kode_coa'=>$data->kode_coa,
            'status'=>$data->status,
            'pkp'=>$data->pkp,
        );
        return response()->json($output);
    }

    public function detail($kodevendor)
    {
        $konek = self::konek();
        $kode_vendor = $kodevendor;

        $list_url= route('vendor.index');
        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        $vendor = Vendor::find($kode_vendor);
        // $coa = Coa::on('mysql4')->select('kode_coa', DB::raw("concat(account,' - ',ac_description) as coa"))->where('position','DETAIL')->pluck('coa','kode_coa');
        $coa = Coa::on('mysql4')->select('coa.kode_coa', DB::raw("concat(coa.account,' - ',coa.ac_description) as coa"))->join('u5611458_gui_general_ledger_laravel.coa_detail','u5611458_gui_general_ledger_laravel.coa.kode_coa','=','u5611458_gui_general_ledger_laravel.coa_detail.kode_coa')->where('u5611458_gui_general_ledger_laravel.coa_detail.kode_company', auth()->user()->kode_company)->where('position','DETAIL')->pluck('coa','coa.kode_coa');

        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        $com = auth()->user()->kode_company;
            
        return view('admin.vendor.indexcoa', compact('vendor','kode_vendor','list_url','period', 'nama_lokasi', 'coa', 'nama_company'));
        
    }

    public function getDatabyID()
    {
        return Datatables::of(VendorCoa::with('coa')->where('kode_vendor',request()->kode_vendor)->where('kode_company', auth()->user()->kode_company))->make(true);
    }


    public function updateAjax(Request $request)
    {
        $konek = self::konek();
        $id = $request->id;
        $datas= $request->all();
        
        $cekpembelian = Pembelian::on($konek)->where('kode_vendor', $id)->first();
        if ($cekpembelian != null){
            $message = [
                'success' => false,
                'title' => 'Update',
                'message' => 'Vendor sudah ada dalam Transaksi Pembelian.'
            ];
            return response()->json($message);
        }
        
        if($request->type == '1'){
            $datas['kode_coa'] = $request->kode_coa;
            Vendor::find($request->id)->update($datas);
            
            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data telah di Update.'
            ];
            return response()->json($message);
        }else{
            Vendor::find($request->id)->update($datas);
           
            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data telah di Update.'
            ];
            return response()->json($message);
        }
    }

    public function hapus_vendor()
    {   
        $konek = self::konek();
        $id = request()->id;
        $vendor = Vendor::find(request()->id);
        
        $cekpembelian = Pembelian::on($konek)->where('kode_vendor', $id)->first();
        if ($cekpembelian != null){
            $message = [
                'success' => false,
                'title' => 'Update',
                'message' => 'Vendor sudah ada dalam Transaksi Pembelian.'
            ];
            return response()->json($message);
        }
        
        $vendor->delete();
        $message = [
            'success' => false,
            'title' => 'Update',
            'message' => 'Penghapusan dilakukan.'
        ];
        return response()->json($message);
    }

    public function store_coa(Request $request)
    {
        $cek = VendorCoa::where('kode_vendor', $request->kode_vendor)->where('kode_company', auth()->user()->kode_company)->first();
        if ($cek != null) {
            $message = [
                'success' => false,
                'title' => 'Update',
                'message' => 'Daftar COA sudah ada.'
            ];
            return response()->json($message);
        }else {
            $simpan_coa = [
                'kode_vendor'=>$request->kode_vendor,
                'kode_coa'=>$request->kode_coa,
                'kode_company'=>auth()->user()->kode_company,
            ];
            VendorCoa::create($simpan_coa);

            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Berhasil Disimpan.'
            ];
            return response()->json($message);
        }
    }

    public function edit_coa()
    {
        $ubah = [
            'kode_coa'=>request()->kode_coa,
        ];

        VendorCoa::where('id', request()->id)->update($ubah);
        $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => 'Data telah diubah.'
        ];
        return response()->json($message);
    }

    public function Showdetail()
    {
        $vendorcoa = VendorCoa::with('coa')->where('kode_vendor', request()->id)->where('kode_company', auth()->user()->kode_company)->get();
        $output = array();

        foreach ($vendorcoa as $row){
            $account = $row->coa->account;
            $desc = $row->coa->ac_description;

            $result[] = array(
                'kode_coa'=>$account,
                'ac_description'=>$desc,
            );
        }

        return response()->json($result);
    }
}
