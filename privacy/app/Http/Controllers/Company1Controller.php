<?php

namespace App\Http\Controllers;

use App\Company1;
use Carbon\Carbon;
use App\Models\Company;
use App\Models\MasterLokasi;
use Illuminate\Http\Request;
use App\Models\tb_akhir_bulan;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Datatables;


class Company1Controller extends Controller
{
    // get all companies
    public function getcompanies(){
        return Datatables::of(Company::orderBy('kode_company'))->make(true);
    }

    // get the connection according to the logged in user
    public function connection(){
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
                abort(404, "Connection is not found !");
                break;
        }
        return $connection;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {   
        // get location name from MasterLokasi table
        $nama_lokasi = MasterLokasi::where('kode_lokasi', auth()->user()->kode_lokasi)->first()->nama_lokasi;

        // get company name from Company table
        $nama_company = Company::where('kode_company', auth()->user()->kode_company)->first()->nama_company;

        // get periode from tb_akhir_bulan table
        $period = tb_akhir_bulan::on($this->connection())->where('reopen_status', 'true')->orwhere('status_periode', 'Open')->first()->period;
        $period = Carbon::parse($period)->format('F Y');

        // get company1/create url
        $create_url = route('company.create');

        // get company name from Company table

        $company = Company::select('kode_company', DB::raw("CONCAT(kode_company, ' - ', nama_company) as fullcompany"))->whereRaw('LENGTH(kode_company) = 2')->pluck('fullcompany', 'kode_company');

        // dd($company['02']);

        return view('admin.company1.index', compact('nama_lokasi', 'nama_company', 'period', 'create_url', 'company'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // check if the company name is already existed
        $nama_company = $request->nama_company;

        $cek_nama = Company::where('nama_company',$nama_company)->first();       
        if ($cek_nama==null){

            if ($request->tipe == "Cabang"){

                $comp = $request->kode_comp;
                $cek_comp = Company::where('kode_company', 'like', $comp.'%')->orderBy('kode_company','desc')->first();
                $data = Company::create($request->all());
                
                // check for company code
                if (strlen($cek_comp->kode_company) == 4){
                    // check if it already has 4 digits code
                    $kode = substr($cek_comp->kode_company,3);
                    $kode += 1;
                    $no = $request->all();
                    if (strlen($kode) == 2){
                        $kode2 = substr($cek_comp->kode_company,0,2);
                    }else {
                        $kode2 = substr($cek_comp->kode_company,0,3);
                    }
                    $no['kode_company'] = $kode2.$kode;
                }else {
                    // add new branch code for first time with 4 digits
                    $kode = $comp."01";
                    $no = $request->all();
                    $no['kode_company'] = $kode;
                }
                $data->update($no);
            }else {
                Company::create($request->all());
            }

            $message = [
                'success' => true,
                'title' => 'Simpan',
                'message' => 'Data telah Disimpan.'
            ];
        }
        else{
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Gagal! Nama Company Sudah Ada.'
            ];
        }
        return response()->json($message);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Company1  $company1
     * @return \Illuminate\Http\Response
     */
    public function show(Company1 $company1)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Company1  $company1
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        // getting the company from company code thougth Company table
        $data = Company::find($request->company1);
        return response()->json($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Company1  $company1
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        // message for front
        $message = [];
        // updating company
        $isUpdate = Company::find($request->company1)->update([
            'nama_company' => $request->nama_company_edit,
            'alamat' => $request->alamat_edit,
            'telp' => $request->telp_edit,
            'npwp' => $request->npwp_edit,
            'status' => $request->status_edit
        ]);
        if($isUpdate){
            $message['success'] = true;
            $message['title'] = 'Update';
            $message['message'] = 'Data telah di update';
        }else{
            $message['success'] = false;
            $message['title'] = 'Gagal';
            $message['message'] = 'Something Wrong !';
        }
        return response()->json($message);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Company1  $company1
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $message = [];

        // find the company using kode_company(company1) through Company database
        $isDeleted = Company::find($request->company1)->delete();

        if($isDeleted){
            $message['success'] = true;
            $message['message'] = 'Data telah di update';
        }else{
            $message['success'] = false;
            $message['message'] = 'Something Wrong !';
        }
        return response()->json($message);
    }
}
