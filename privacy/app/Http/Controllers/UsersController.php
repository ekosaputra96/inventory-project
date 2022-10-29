<?php

namespace App\Http\Controllers;

use App\DataTables\UsersDataTable;
use Illuminate\Support\Facades\Hash;
use App\Permission;
use App\Role;
use App\User;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Models\tb_akhir_bulan;
use App\Models\MasterLokasi;
use Carbon;
use DB;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
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
    
    public function index(UsersDataTable $dataTable)
    {
        $konek = self::konek();
        $testing = User::with('roles')->take(2)->get();
        $create_url = route('users.create');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        $level = auth()->user()->level;
        return $dataTable->render('admin.users.index',compact('create_url','period', 'nama_lokasi','nama_company'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $konek = self::konek();
        $roles = Role::all();
        $list_url = route('users.index');
        $Company= Company::where('status','Aktif')->pluck('nama_company','kode_company');
        $Roles= Role::pluck('display_name','id');

        $Lokasi = MasterLokasi::select('kode_lokasi', DB::raw("concat(kode_lokasi,' - ',nama_lokasi) as lokasi"))->pluck('lokasi','kode_lokasi');
        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;

        return view('admin.users.create', compact('list_url','roles','Company','period', 'nama_lokasi','Lokasi','nama_company','Roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $role = $request->roles;
        $get_role = Role::where('id',$role)->first(); 
        $name = $get_role->name;
        $lokasi = $request->kode_lokasi;
        $username = $request->username;

        $this->validate($request, [
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create($request->all());
        $user->level = $name;
        $user->kode_lokasi = $lokasi;
        $user->username = $username;
        $user->save();

        if ($request->has('roles')){
            $user->syncRoles($request->roles);
        }

        $req = $request->all();
        $req['password'] = bcrypt($request->password);
        $user->update($req);

        return redirect()->route('users.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        $konek = self::konek();
        $list_url = route('users.index');
        $roles = Role::pluck('display_name','id');
        $Company= Company::where('status','Aktif')->pluck('nama_company','kode_company');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;
        $Lokasi = MasterLokasi::pluck('nama_lokasi','kode_lokasi');

        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;
        
        $level = auth()->user()->level;
        if($level == 'superadministrator' || $level == 'user_tina'){   
            return view('admin.users.edit',compact('user','list_url','roles','Company','period', 'nama_lokasi','Lokasi','nama_company'));
        }else{
            return view('admin.users.edit2',compact('user','list_url','roles','Company','period', 'nama_lokasi','Lokasi','nama_company'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $level = auth()->user()->level;
        $username = $request->username;
        
        if($level == 'superadministrator' || $level == 'user_tina'){   
            $role = $request->roles;
            $get_role = Role::where('id',$role)->first(); 
            $name = $get_role->name;
            $lokasi = $request->kode_lokasi;
            $username = $request->username;

            if ($request->has('password') && $request->password != null){

                $req = $request->all();
                $req['password'] = Hash::make($request->password);
                $user->update($req);

                $user->level = $name;
                $user->kode_lokasi = $lokasi;
                $user->username = $username;
                $user->update();
            }else {
                $user->update($request->except('password'));

                $user->level = $name;
                $user->kode_lokasi = $lokasi;
                $user->username = $username;
                $user->update();
            }

            if ($request->has('roles')){
                $user->syncRoles($request->roles);
            }

            return redirect()->route('users.index');
        }else{
            if ($request->has('password') && $request->password != null){

                $req = $request->all();
                $req['password'] = Hash::make($request->password);
                $user->update($req);

                $user->username = $username;
                $user->update();
            }else {
                $user->update($request->except('password'));

                $user->username = $username;
                $user->update();
            }

            return redirect()->route('users.index');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        try {
            $user->delete();

            $message = [
                'success' => true,
                'title' => 'Hapus',
                'message' => 'User ['.$user->name.'] berhasil dihapus.'
            ];
            return response()->json($message);

        }catch (\Exception $exception){
            $message = [
                'success' => false,
                'title' => 'Hapus',
                'message' => 'User gagal dihapus.'
            ];
            return response()->json($message);
        }
    }
}
