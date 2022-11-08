<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Kasbon;
use App\Models\Company;
use App\Models\MasterLokasi;
use App\Models\user_history;
use Illuminate\Http\Request;
use App\Models\tb_akhir_bulan;
use PDF;
use Terbilang;
use Yajra\DataTables\DataTables;

class Kasbon1Controller extends Controller
{
    // get the connection according to the logged in user
    public function connection()
    {
        // getting company code from the logged in user
        $company_code = auth()->user()->kode_company;

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
            case '0401':
                $connection = 'mysqlgutjkt';
                break;
            case '03':
                $connection = 'mysql';
                break;
            case '04':
                $connection = 'mysqlgut';
                break;
            case '05':
                $connection = 'mysqlsub';
                break;
            default:
                abort(404, 'Connection is not found !');
                break;
        }
        return $connection;
    }

    /**
     * return formatted date.
     *
     * 
     */
    public function formatDate(string $tgl){
        return \Carbon\Carbon::parse($tgl)->format('d F Y');
    }

    /**
     * return kasbon data to datatables.
     *
     * 
     */
    public function getKasbon(){
        return DataTables::of(Kasbon::on($this->connection())->latest())->make(true);
    }

    /**
     * return the count of kasbon.
     *
     * 
     */
    public function checkKasbon(string $tgl){
        return Kasbon::on($this->connection())->where('tanggal_permintaan', 'like', $tgl. '%')->where('status', 'OPEN')->count();
    
    }
    /**
     * return boolean for checking period.
     *
     * 
     */
    public function periodeChecker(string $tgl){
        // format $tgl to Y-m
        $tgl = Carbon::parse($tgl)->format('Y-m');
        // check period from tb_akhir_bulan
        $checkPeriod = tb_akhir_bulan::on($this->connection())->where('periode', 'like', $tgl.'%')->first();
        if($checkPeriod != null){
            if($checkPeriod->status_periode == 'Open'){
                return true;
            }
        }
        return false;
    }

    /**
     * creating new user history to user_history table.
     *
     * 
     */
    public function createUserHistory(string $aksi, string $no_pkb) {
        user_history::on($this->connection())->create([
            'nama' => auth()->user()->name,
            'aksi' => $aksi.' Kasbon No. Transfer: '.$no_pkb.'.',
            'created_by' => auth()->user()->name,
            'updated_by' => auth()->user()->name
        ]);
    }

    /**
     * creating new message for response.
     *
     * 
     */
    public function message(bool $success, string $title, string $msg){
        return [
            'success' => $success,
            'title' => $title,
            'message' => $msg
        ];
    }
    
    /**
     * To approve the selected kasbon 'APPROVED'.
     *
     * @return \Illuminate\Http\Response
     */
    public function approvedKasbon(Request $request){
        // set init
        $message = null;

        // check if logged user is superadministrator
        if(auth()->user()->level == 'superadministrator'){
            // get the selected kasbon from database
            $getKasbon = Kasbon::on($this->connection())->find($request->no_pkb);
            
            if($getKasbon->status == 'POSTED' && $getKasbon->status != 'OPEN'){
                $getKasbon->status = 'APPROVED';
                $getKasbon->save();

                $message = $this->message(true, 'Berhasil', $request->no_pkb.' berhasil diapproved');
            }else{
                $message = $this->message(false, 'Gagal', $request->no_pkb.' masin open / sudah diapproved');
            }
        }else{
            // if the logged in user is not superadministrator
            $message = $this->message(false, 'Gagal', 'Not Authorized');
        }
        return response()->json($message);
    }

    /**
     * To export kasbon to pdf file.
     *
     * @return \Illuminate\Http\Response
     */

    public function exportPdf($id_pkb){

        // get the kasbon according to $id_pkb
        $kasbon = Kasbon::on($this->connection())->find($id_pkb);

        if($kasbon == null){
            return redirect(route('kasbon1.index'));
        }

        // get lokasi name according to logged in user from masterlokasi table
        $get_nama_lokasi = MasterLokasi::select('nama_lokasi')->find(auth()->user()->kode_lokasi)->nama_lokasi;

        // get company name according to logged in user from Company table
        $get_nama_company = Company::select('nama_company')->find(auth()->user()->kode_company)->nama_company;

        // format tanggal permintaan
        $tanggal_permintaan_format = $this->formatDate($kasbon->tanggal_permintaan);

        return PDF::loadView('/admin/kasbon1/pdf', compact('kasbon', 'get_nama_lokasi', 'get_nama_company', 'tanggal_permintaan_format'))->setPaper([0, 0, 684, 792], 'potrait')->stream('Permintaan_Kasbon_'.$id_pkb.'.pdf');
    }

    /**
     * To unpost the selected kasbon from 'POSTED' to 'OPEN'.
     *
     * @return \Illuminate\Http\Response
     */
    public function unpostKasbon(Request $request){
        // set init
        $message = null;

        // check if logged user is superadministrator
        if(auth()->user()->level == 'superadministrator'){
            // get the selected kasbon from database
            $getKasbon = Kasbon::on($this->connection())->find($request->no_pkb);
            
            if($getKasbon->status == 'POSTED'){
                $getKasbon->status = 'OPEN';
                $getKasbon->save();

                $message = $this->message(true, 'Berhasil', $request->no_pkb.' berhasil diunposting');
            }else{
                $message = $this->message(false, 'Gagal', $request->no_pkb.' sudah diunposting/approved');
            }
        }else{
            // if the logged in user is not superadministrator
            $message = $this->message(false, 'Gagal', 'Not Authorized');
        }
        return response()->json($message);
    }
    
    /**
     * To post the selected kasbon from 'OPEN' to 'POSTED'.
     *
     * @return \Illuminate\Http\Response
     */
    public function postKasbon(Request $request){
        // set init
        $message = null;

        // check if logged user is superadministrator
        if(auth()->user()->level == 'superadministrator'){
            // get the selected kasbon from database
            $getKasbon = Kasbon::on($this->connection())->find($request->no_pkb);
            
            if($getKasbon->status == 'OPEN'){
                $getKasbon->status = 'POSTED';
                $getKasbon->save();

                $message = $this->message(true, 'Berhasil', $request->no_pkb.' berhasil diposting');
            }else{
                $message = $this->message(false, 'Gagal', $request->no_pkb.' sudah diposting/approved');
            }
        }else{
            // if the logged in user is not superadministrator
            $message = $this->message(false, 'Gagal', 'Not Authorized');
        }
        return response()->json($message);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // getting nama lokasi from MasterLokasi
        $nama_lokasi = MasterLokasi::select('nama_lokasi')->where('kode_lokasi', auth()->user()->kode_lokasi)->first()->nama_lokasi;

        // getting period from tb_akhir_bulan
        $period = tb_akhir_bulan::on($this->connection())->select('periode')->where('status_periode', 'Open')->orWhere('reopen_status', 'true')->first()->periode;

        $period = Carbon::parse($period)->format('F Y');


        return view('admin.kasbon1.index', compact('nama_lokasi', 'period'));
    }

    /**
     * return terbilang to client.
     *
     * @return \Illuminate\Http\Response
     */
    public function terbilang($angka){
        return response()->json([
            'terbilang' => ucwords(Terbilang::make(intval($angka), ' rupiah'))
        ]);
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
        // init message
        $message = null;
        // check if there is re-open
        $reopen = tb_akhir_bulan::on($this->connection())->where('reopen_status', 'true')->first();

        // if re-open is not null
        if($reopen != null){
            // format reopen periode date
            $reopenPeriode = Carbon::parse($reopen->periode)->format('Y-m');

            if($this->checkKasbon($reopenPeriode) >= 1){
                $message = $this->message(false, 'Gagal', 'Masih ada Transaksi PKB yang OPEN');
            }
        }else{
            // if re-open is null
            // getting periode from tb_akhir_bulan
            $period = tb_akhir_bulan::on($this->connection())->where('status_periode', 'Open')->first()->periode;

            // format periode to tanggal permintaan
            $period = Carbon::parse($period)->format('Y-m');
            if($this->checkKasbon($period) >= 1){
                $message = $this->message(false, 'Gagal', 'Masih ada Transaksi PKB yang OPEN');
            }
        }

        // if period is valid
        if($this->periodeChecker($request->tanggal_permintaan_add) && $this->checkKasbon($period) == 0){
            // insert Kasbon to the database
            Kasbon::on($this->connection())->create([
                'nama_pemohon' => $request->nama_pemohon_add,
                'tanggal_permintaan' => $request->tanggal_permintaan_add,
                'nilai' => $request->nilai_add,
                'keterangan' => $request->keterangan_add,
            ]);

            // getting no PKB from Kasbon
            $no_pkb = Kasbon::on($this->connection())->select('no_pkb')->where('nama_pemohon', $request->nama_pemohon_add)->where('tanggal_permintaan', $request->tanggal_permintaan_add)->latest()->first()->no_pkb;

            // insert the action to user_history
            $this->createUserHistory('Simpan', $no_pkb);

            $message = $this->message(true, 'Simpan', 'Data telah disimpan');
        }else if($this->periodeChecker($request->tanggal_permintaan_add) == false && $this->checkKasbon($period) == 0){
            // if not valid
            $message = $this->message(false, 'Gagal', 'Periode '. Carbon::parse($request->tanggal_permintaan_add)->format('F Y'). ' Telah ditutup / Belum dibuka');
        }
        return response()->json($message);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // getting nama lokasi from MasterLokasi
        $nama_lokasi = MasterLokasi::select('nama_lokasi')->where('kode_lokasi', auth()->user()->kode_lokasi)->first()->nama_lokasi;

        // getting period from tb_akhir_bulan
        $period = tb_akhir_bulan::on($this->connection())->select('periode')->where('status_periode', 'Open')->orWhere('reopen_status', 'true')->first()->periode;

        $period = Carbon::parse($period)->format('F Y');

        // get the kasbon according to $id_pkb
        $kasbon = Kasbon::on($this->connection())->find($id);

        if($kasbon == null){
            return redirect(route('kasbon1.index'));
        }

        return view('admin.kasbon1.detail', compact('nama_lokasi', 'period', 'kasbon'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // getting the kasbon data using $id (no_pkb)
        $data = Kasbon::on($this->connection())->select('no_pkb', 'nama_pemohon', 'tanggal_permintaan', 'status', 'nilai', 'keterangan', 'created_at', 'updated_at', 'created_by', 'updated_by')->find($id);
        return response()->json($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // init message
        $message = null;

        // check the period of the tanggal_permintaan to tb_akhir_tahun
        if($this->periodeChecker($request->tanggal_permintaan_edit)){
            // updating the selected Kasbon
            Kasbon::on($this->connection())->find($id)->update([
                'nama_pemohon' => $request->nama_pemohon_edit,
                'nilai' => $request->nilai_edit,
                'tanggal_permintaan' => $request->tanggal_permintaan_edit,
                'keterangan' => $request->keterangan_edit,
            ]);

            // creating user history
            $this->createUserHistory('Edit', $id);

            // send successfull message
            $message = $this->message(true, 'Update', 'Data telah di update');
        }else{
            // if tanggal_permintaan and current period doesn't match
            $message = $this->message(false, 'Gagal', 'Periode '.Carbon::parse($request->tanggal_permintaan_edit)->format('F Y').' telah ditutup / belum dibuka');
        }
        return response()->json($message);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // init message
        $message = null;

        // get the selected kasbon from Kasbon table
        $kasbon = Kasbon::on($this->connection())->find($id);

        // check the period of the tanggal_permintaan to tb_akhir_tahun
        if($this->periodeChecker($kasbon->tanggal_permintaan)){
            // delete the Kasbon
            $kasbon->delete();

            // creating user history
            $this->createUserHistory('Hapus', $id);

            // generating message
            $message = $this->message(true, 'Delete', 'Data '.$id.' telah dihapus');
        }else{
            // if the current periode and tanggal permintaan doesn't match
            $message = $this->message(false, 'Gagal', 'Periode '.Carbon::parse($kasbon->tanggal_permintaan)->format('F Y').' telah ditutup / belum dibuka');
        }
        return response()->json($message);
    }
}
