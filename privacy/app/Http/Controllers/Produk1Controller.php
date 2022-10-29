<?php

namespace App\Http\Controllers;

use App\Exports\Listproduk1Export;
use DB;
use PDF;
use Carbon\Carbon;
use App\Models\Unit;
use App\Models\Merek;
use App\Models\Produk;
use App\Models\satuan;
use App\Models\Ukuran;
use App\Models\Company;
use App\Models\Konversi;
use App\Models\LokasiRak;
use App\Models\MasterLokasi;
use App\Models\OpnameDetail;
use App\Models\user_history;
use Illuminate\Http\Request;
use App\Models\ProdukCounter;
use App\Models\KategoriProduk;
use App\Models\tb_akhir_bulan;
use App\Models\TransferDetail;
use App\Exports\Monthly1Export;
use App\Models\PemakaianDetail;
use App\Models\PembelianDetail;
use App\Models\PenjualanDetail;
use App\Models\tb_item_bulanan;
use App\Models\AdjustmentDetail;
use App\Models\PenerimaanDetail;
use App\Models\TransferInDetail;
use Yajra\DataTables\DataTables;
use App\Models\tb_produk_history;
use App\Models\PemakaianbanDetail;
use Maatwebsite\Excel\Facades\Excel;

class Produk1Controller extends Controller
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

    // getting active periode
    public function getPeriode(){
        return tb_akhir_bulan::on($this->connection())->where('status_periode', 'Open')->orWhere('reopen_status', 'true')->first()->periode;
    }

    // exporting products list to excel
    public function exportexcel(){
        // getting company name from Company table
        $get_nama_company = Company::find(auth()->user()->kode_company)->nama_company;
        return Excel::download(new Listproduk1Export(auth()->user()->kode_company, $get_nama_company), 'List_Produk_'.$get_nama_company.'.xlsx');
    }

    /**
     * Display show detail produk per page  of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function showDetail($id){
        // getting nama lokasi from MasterLokasi table
        $nama_lokasi = MasterLokasi::where('kode_lokasi', auth()->user()->kode_lokasi)->first()->nama_lokasi;

        // getting period
        $period = $this->getPeriode();

        // getting the produk using id with eager loading
        $produk = Produk::on($this->connection())->with('kategoriproduk', 'merek', 'satuan', 'company', 'ukuran')->find($id);
        
        // getting detail of the product from tb_item_bulanan
        $detail = tb_item_bulanan::on($this->connection())->where('kode_produk', $id)->where('kode_lokasi', auth()->user()->kode_lokasi)->where('periode', $period)->first();

        // cek ending stock
        $ending_stock = $detail->ending_stock;

        // getting produk category from KategoriProduk table make it array
        $kategori = KategoriProduk::pluck('nama_kategori', 'kode_kategori');

        // getting unit with its kode from Unit table
        $unit = Unit::pluck('nama_unit', 'kode_unit');

        // getting merek code with its name from Merek table
        $merek = Merek::on($this->connection())->pluck('nama_merek', 'kode_merek');

        // getting ukuran from table Ukuran
        $ukuran = Ukuran::on($this->connection())->pluck('nama_ukuran', 'kode_ukuran');

        // getting satuan with its code from satuan table
        $satuan = satuan::pluck('nama_satuan', 'kode_satuan');

        // getting ending stock
        if($produk->tipe_produk == 'Serial' && $detail != null){
            if($produk->kode_kategori == 'UNIT' || $produk->kode_kategori == 'BAN'){
                $total = tb_item_bulanan::on($this->connection())->where('kode_produk', $id)->where('kode_lokasi', auth()->user()->kode_lokasi)->where('periode', $period)->where('ending_stock', 1)->get();
                $ending_stock = count($total);
            }
        }

        // getting merek
        if($produk->kode_merek == null || $produk->kode_merek == '' || $produk->kode_merek == '-'){
            $produk->kode_merek = "Not Set";
        }

        // getting kode ukuran
        if($produk->kode_ukuran == null || $produk->kode_ukuran == '' || $produk->kode_ukuran = '-'){
            $produk->kode_ukuran = "Not Set";
        }

        // getting kode satuan
        if($produk->kode_satuan == null || $produk->kode_satuan == '' || $produk->kode_satuan = '-'){
            $produk->kode_satuan = "Not Set";
        }

        // getting kode unit
        if($produk->kode_unit == null || $produk->kode_unit == '' || $produk->kode_unit = '-'){
            $produk->kode_unit = "Not Set";
        }

        return view('admin.produk1.detail', compact('nama_lokasi', 'period', 'produk', 'ending_stock', 'detail', 'kategori', 'unit', 'merek', 'ukuran', 'satuan'));
    }

    /**
     * Display show monthly  of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function showMonthly($id){
        // init data
        $data = null;

        // getting periode
        $period = $this->getPeriode();

        // check if the logged in user is in 'HO'
        if(auth()->user()->kode_lokasi == 'HO'){
            $data =  tb_item_bulanan::where('kode_produk', $id)->where('kode_lokasi', auth()->user()->kode_lokasi)->whereBetween('periode', [$period, date('Y-m-d')])->first();
        }else{
            // if user is not 'HO'
            $data =  tb_item_bulanan::where('kode_produk', $id)->whereBetween('periode', [$period, date('Y-m-d')])->first();
        }
        // check if data is null
        if($data == null){
            $data = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Transaksi tidak ada',
            ];
        }else{
            $data['success'] = true;
        }
        return response()->json($data);
    }

    /**
     * Display show history of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function showHistory($id){
        // init data
        $data = null;

        // get periode
        $period = $this->getPeriode();

        // check the logged in user lokasi if 'HO'
        if(auth()->user()->kode_lokasi == 'HO'){
            // getting data from tb_produk_history table
            $data = tb_produk_history::on($this->connection())->select('tanggal_transaksi', 'no_transaksi', 'qty_transaksi', 'total_transaksi', 'created_by')->where('kode_produk', $id)->where('kode_lokasi', auth()->user()->kode_lokasi)->whereBetween('tanggal_transaksi', [$period, date('Y-m-d')])->latest()->first();
        }else{
            // if not HO
            $data = tb_produk_history::on($this->connection())->select('tanggal_transaksi', 'no_transaksi', 'qty_transaksi', 'total_transaksi', 'created_by')->where('kode_produk', $id)->whereBetween('tanggal_transaksi', [$this->getPeriode(), date('Y-m-d')])->latest()->first();
        }

        // check if data is null
        if($data == null){
            $data = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Transaksi tidak ada',
            ];
        }else{
            $data['success'] = true;
        }
        // adding periode
        $data['periode'] = Carbon::parse($period)->format('F Y');
        return response()->json($data);
    }

    /**
     * Display showstock of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function showStock($id){
        // getting period
        $period = tb_akhir_bulan::on($this->connection())->where('status_periode', 'Open')->orWhere('reopen_status', 'true')->first()->periode;

        // init data
        $data = null;

        // if the logged in user's location is 'HO'
        if(auth()->user()->kode_lokasi == 'HO'){
            $data = tb_item_bulanan::on($this->connection())->select('kode_produk', 'partnumber', 'no_mesin', 'kode_lokasi', 'ending_stock', 'hpp')->where('kode_produk', $id)->where('periode', $period)->first();
        }else{
            $data = tb_item_bulanan::on($this->connection())->select('kode_produk', 'partnumber', 'no_mesin', 'kode_lokasi', 'ending_stock', 'hpp')->where('kode_lokasi', auth()->user()->kode_lokasi)->where('kode_produk', $id)->where('periode', $period)->first();
        }

        // check if data is null
        if($data == null){
            $data = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Maaf data terkait tidak ada'
            ];
        }
        return response()->json($data);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getProducts()
    {
        // getting period from tb_akhir_bulan
        $period = tb_akhir_bulan::where('reopen_status', 'true')
            ->orwhere('status_periode', 'Open')
            ->first()->periode;

        // getting level of the logged in user
        $level = auth()->user()->level;

        // cek if the logged in user is from sany level
        if ($level == 'sany') {
            return Datatables::of(
                Produk::on($this->connection())
                    ->select('produk.id', 'produk.nama_produk', 'produk.partnumber', 'merek.nama_merek', DB::raw('SUM(tb_item_bulanan.ending_stock) as totalstock'))
                    ->join('tb_item_bulanan', 'produk.id', '=', 'tb_item_bulanan.kode_produk')
                    ->join('merek', 'produk.kode_merek', '=', 'merek.kode_merek')
                    ->where('tb_item_bulanan.periode', $period)
                    ->where('merek.nama_merek', 'SANY')
                    ->groupBy('produk.id'),
            )->make(true);
        } else {
            return Datatables::of(Produk::on($this->connection())->with('kategoriproduk', 'merek', 'satuan', 'company', 'ukuran'))->make(true);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // getting nama lokasi from MasterLokasi table
        $nama_lokasi = MasterLokasi::where('kode_lokasi', auth()->user()->kode_lokasi)->first()->nama_lokasi;

        // getting period from tb_akhir_bulan table and parse it using Carbon
        $period = tb_akhir_bulan::on($this->connection())
            ->where('reopen_status', 'true')
            ->orWhere('status_periode', 'Open')
            ->first()->periode;

        $period = Carbon::parse($period)->format('F Y');

        // user level
        $level = auth()->user()->level;

        // getting produk category from KategoriProduk table make it array
        $kategori = KategoriProduk::pluck('nama_kategori', 'kode_kategori');

        // getting unit with its kode from Unit table
        $unit = Unit::pluck('nama_unit', 'kode_unit');

        // getting merek code with its name from Merek table
        $merek = Merek::on($this->connection())->pluck('nama_merek', 'kode_merek');

        // getting satuan with its code from satuan table
        $satuan = satuan::pluck('nama_satuan', 'kode_satuan');

        // getting ukuran from table Ukuran
        $ukuran = Ukuran::on($this->connection())->pluck('nama_ukuran', 'kode_ukuran');

        // getting all produk from Produk table
        $produk = Produk::on($this->connection())->pluck('nama_produk', 'id')->sort();

        // getting lokasi from tb_akhir_bulan
        $lokasi = tb_item_bulanan::on($this->connection())->pluck('kode_lokasi', 'kode_lokasi');

        return view('admin.produk1.index', compact('nama_lokasi', 'period', 'level', 'kategori', 'unit', 'merek', 'satuan', 'ukuran', 'produk', 'lokasi'));
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
        // cek produk if it exists or not
        $isProductExisted = Produk::on($this->connection())
            ->where('nama_produk', $request->nama_produk)
            ->first();
        // if the product does not exist
        if (!$isProductExisted) {
            // insert data to the database
            Produk::on($this->connection())->create($request->all());

            // check if there is double product
            $removeDouble = Produk::select(DB::raw('count(nama_produk) as counter'))
                ->where('nama_produk', $request->nama_produk)
                ->groupBy('nama_produk')
                ->first();

            if ($removeDouble->counter > 1) {
                Produk::where('nama_produk', $request->nama_produk)
                    ->latest()
                    ->delete();
            }

            // getting the id of the product with the name_produk from $request->nama_produk
            $product_id = Produk::select('id')
                ->where('nama_produk', $request->nama_produk)
                ->first()->id;

            // getting satuan with its content
            $satuan = satuan::find($request->kode_satuan);

            // insert into Konversi
            Konversi::on($this->connection())->create([
                'kode_produk' => $product_id,
                'kode_satuan' => $request->kode_satuan,
                'satuan_terbesar' => $satuan->nama_satuan,
                'nilai_konversi' => 1,
                'kode_satuanterkecil' => $request->kode_satuan,
                'satuan_terkecil' => $satuan->nama_satuan,
            ]);

            // getting period form tb_akhir_bulan
            $period = tb_akhir_bulan::on($this->connection())
                ->where('status_periode', 'Open')
                ->orwhere('reopen_status', 'true')
                ->first()->periode;

            // create data monthly for the new produk
            if ($request->tipe_produk != 'Serial') {
                // if not Serial like Ban that has its serial number
                tb_item_bulanan::on($this->connection())->create([
                    'periode' => $period,
                    'kode_produk' => $product_id,
                    'partnumber' => $request->partnumber,
                    'no_mesin' => '-',
                    'begin_stock' => 0,
                    'begin_amount' => 0,
                    'in_stock' => 0,
                    'in_amount' => 0,
                    'out_stock' => 0,
                    'out_amount' => 0,
                    'sale_stock' => 0,
                    'sale_amount' => 0,
                    'trf_in' => 0,
                    'trf_in_amount' => 0,
                    'trf_out' => 0,
                    'trf_out_amount' => 0,
                    'adjustment_stock' => 0,
                    'adjustment_amount' => 0,
                    'stock_opname' => 0,
                    'amount_opname' => 0,
                    'retur_beli_stock' => 0,
                    'retur_beli_amount' => 0,
                    'retur_jual_stock' => 0,
                    'retur_jual_amount' => 0,
                    'disassembling_stock' => 0,
                    'disassembling_amount' => 0,
                    'assembling_stock' => 0,
                    'assembling_amount' => 0,
                    'ending_stock' => 0,
                    'ending_amount' => 0,
                    'hpp' => 0,
                    'kode_lokasi' => auth()->user()->kode_lokasi,
                    'kode_company' => auth()->user()->kode_company,
                ]);
            } else {
                // if not serial and kategori product is not UNIT nor BAN
                if ($request->kode_kategori != 'UNIT' && $request->kode_kategori != 'BAN') {
                    tb_item_bulanan::on($this->connection())->create([
                        'periode' => $period,
                        'kode_produk' => $product_id,
                        'partnumber' => $request->partnumber,
                        'no_mesin' => '-',
                        'begin_stock' => 0,
                        'begin_amount' => 0,
                        'in_stock' => 0,
                        'in_amount' => 0,
                        'out_stock' => 0,
                        'out_amount' => 0,
                        'sale_stock' => 0,
                        'sale_amount' => 0,
                        'trf_in' => 0,
                        'trf_in_amount' => 0,
                        'trf_out' => 0,
                        'trf_out_amount' => 0,
                        'adjustment_stock' => 0,
                        'adjustment_amount' => 0,
                        'stock_opname' => 0,
                        'amount_opname' => 0,
                        'retur_beli_stock' => 0,
                        'retur_beli_amount' => 0,
                        'retur_jual_stock' => 0,
                        'retur_jual_amount' => 0,
                        'disassembling_stock' => 0,
                        'disassembling_amount' => 0,
                        'assembling_stock' => 0,
                        'assembling_amount' => 0,
                        'ending_stock' => 0,
                        'ending_amount' => 0,
                        'hpp' => 0,
                        'kode_lokasi' => auth()->user()->kode_lokasi,
                        'kode_company' => auth()->user()->kode_company,
                    ]);
                }
            }
            $msg = [
                'success' => true,
                'title' => 'Berhasil',
                'message' => 'Data telah di simpan',
            ];
        } else {
            // if product exists
            $msg = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Produk sudah ada',
            ];
        }
        return response()->json($msg);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($idProduk)
    {
        // getting the produk using idProduk with eager loading
        $produk = Produk::on($this->connection())->with('kategoriproduk', 'merek', 'satuan', 'company', 'ukuran')->find($idProduk);

        // getting the period
        $period = tb_akhir_bulan::on($this->connection())->where('status_periode', 'Open')->orwhere('reopen_status', 'true')->first()->periode;
        
        // getting detail of the product from tb_item_bulanan
        $detail = tb_item_bulanan::on($this->connection())->where('kode_produk', $idProduk)->where('kode_lokasi', auth()->user()->kode_lokasi)->where('periode', $period)->first();

        // cek ending stock
        $ending_stock = $detail->ending_stock;

        // getting ending stock
        if($produk->tipe_produk == 'Serial' && $detail != null){
            if($produk->kode_kategori == 'UNIT' || $produk->kode_kategori == 'BAN'){
                $total = tb_item_bulanan::on($this->connection())->where('kode_produk', $idProduk)->where('kode_lokasi', auth()->user()->kode_lokasi)->where('periode', $period)->where('ending_stock', 1)->get();
                $ending_stock = count($total);
            }
        }

        // getting merek
        if($produk->kode_merek == null || $produk->kode_merek == '' || $produk->kode_merek == '-'){
            $produk->kode_merek = "Not Set";
        }

        // getting kode ukuran
        if($produk->kode_ukuran == null || $produk->kode_ukuran == '' || $produk->kode_ukuran = '-'){
            $produk->kode_ukuran = "Not Set";
        }

        // getting kode satuan
        if($produk->kode_satuan == null || $produk->kode_satuan == '' || $produk->kode_satuan = '-'){
            $produk->kode_satuan = "Not Set";
        }

        // getting kode unit
        if($produk->kode_unit == null || $produk->kode_unit == '' || $produk->kode_unit = '-'){
            $produk->kode_unit = "Not Set";
        }

        return response()->json([
            'kode_produk'=> $produk->id,
            'nama_produk'=> $produk->nama_produk,
            'tipe_produk'=> $produk->tipe_produk,
            'kode_kategori'=> $produk->kategoriproduk->nama_kategori,
            'kode_merek'=> $produk->kode_merek,
            'kode_ukuran'=> $produk->kode_ukuran,
            'kode_satuan'=> $produk->kode_satuan,
            'kode_company'=> isset($produk->company->nama_company) ? $produk->company->nama_company : 'Not Set',
            'partnumber'=> $produk->partnumber,
            'harga_beli'=> $produk->harga_beli,
            'harga_jual'=> $produk->harga_jual,
            'hpp'=> $detail->hpp,
            'stok'=> $ending_stock,
            'stat'=> $produk->stat,
            'kode_unit' => $produk->kode_unit,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($idProduk)
    {
        //
        return response()->json(Produk::on($this->connection())->find($idProduk));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $idProduk)
    {
        // pembelian number from PembelianDetail table
        $no_pembelian = PembelianDetail::on($this->connection())->where('kode_produk', $idProduk)->first();

        // getting penerimaan number from PenerimaanDetail
        $no_penerimaan = PenerimaanDetail::on($this->connection())->where('kode_produk', $idProduk)->first();

        // getting pemakaian number from PemakaianDetail
        $no_pemakaian = PemakaianDetail::on($this->connection())->where('kode_produk', $idProduk)->first();

        // getting pemakaianban number from PemakaianbanDetail
        $no_pemakaianban = PemakaianbanDetail::on($this->connection())->where('kode_produk', $idProduk)->first();

        // getting opname number from OpnameDetail
        $no_opname = OpnameDetail::on($this->connection())->where('kode_produk', $idProduk)->first();

        // getting adjustment detail numbuer from AdjustmentDetail
        $no_adjustment = AdjustmentDetail::on($this->connection())->where('kode_produk', $idProduk)->first();

        // getting penjualan number from PenjualanDetail
        $no_penjualan = PenjualanDetail::on($this->connection())->where('kode_produk', $idProduk)->first();

        // getting transfer in number from TransferInDetail
        $no_trfin = TransferInDetail::on($this->connection())->where('kode_produk', $idProduk)->first();

        // getting transfer out number from TransferDetail
        $no_trfout = TransferDetail::on($this->connection())->where('kode_produk', $idProduk)->first();

        // getting no konversi from Konversi 
        $no_konversi = Konversi::on($this->connection())->where('kode_produk', $idProduk)->count();

        // getting rack location from LokasiRak table
        $lokasi_rak = LokasiRak::on($this->connection())->where('kode_produk', $idProduk)->first();
    
        // check if the produk is already in transaction then return false
        if($no_pembelian != null || $no_penerimaan != null || $no_pemakaian != null || $no_pemakaianban != null || $no_opname != null || $no_adjustment != null || $no_penjualan != null || $no_trfin != null || $no_trfout != null){
            $message = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Produk ['.$request->nama_produk_edit.'] sudah ada dalam transaksi'
            ];
            
            return response()->json($message);
        }

        // check user's level
        if(auth()->user()->level != 'superadministrator' && auth()->user()->level != 'user_rince' && auth()->user()->level != 'user_thomas'){
            if($no_pembelian == null && $no_pemakaian == null && $no_opname == null && $no_adjustment == null && $lokasi_rak == null && $no_penerimaan == null && $no_pemakaianban == null && $no_penjualan == null && $no_trfin == null && $no_trfout == null && $no_konversi <= 1){
                // update produk table
                Produk::on($this->connection())->find($idProduk)->update([
                    'nama_produk' => $request->nama_produk_edit,
                    'tipe_produk' => $request->tipe_produk_edit,
                    'kode_kategori' => $request->kode_kategori_edit,
                    'kode_unit' => $request->kode_unit_edit,
                    'kode_merek' => $request->kode_merek_edit,
                    'kode_ukuran' => $request->kode_ukuran_edit,
                    'kode_satuan' => $request->kode_satuan_edit,
                    'partnumber' => $request->partnumber_edit,
                    'harga_beli' => $request->harga_beli_edit,
                    'harga_jual' => $request->harga_jual_edit,
                    'min_qty' => $request->min_qty_edit,
                    'max_qty' => $request->max_qty_edit,
                    'stat' => $request->stat_edit,
                ]);

                // getting satuan name from Satuan table
                $get_nama_satuan = satuan::find($request->kode_satuan_edit)->nama_satuan;
                Konversi::on($this->connection())->where('kode_produk', $idProduk)->update([
                    'kode_satuan' => $request->kode_satuan_edit,
                    'satuan_terbesar' => $get_nama_satuan,
                    'kode_satuanterkecil' => $request->kode_satuan_edit,
                    'satuan_terkecil' => $get_nama_satuan
                ]);

                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data telah diupdate 1'
                ];
            }else{
                $message = [
                    'success' => false,
                    'title' => 'Gagal',
                    'message' => 'Produk ['.$request->nama_produk_edit.'] dipakai dalam transaksi'
                ];
            }
            return response()->json($message);
        }else{
            // if there is the largest unit conversion then it needs to be removed first
            if($no_konversi <= 1){
                // update produk table
                Produk::on($this->connection())->find($idProduk)->update([
                    'nama_produk' => $request->nama_produk_edit,
                    'tipe_produk' => $request->tipe_produk_edit,
                    'kode_kategori' => $request->kode_kategori_edit,
                    'kode_unit' => $request->kode_unit_edit,
                    'kode_merek' => $request->kode_merek_edit,
                    'kode_ukuran' => $request->kode_ukuran_edit,
                    'kode_satuan' => $request->kode_satuan_edit,
                    'partnumber' => $request->partnumber_edit,
                    'harga_beli' => $request->harga_beli_edit,
                    'harga_jual' => $request->harga_jual_edit,
                    'min_qty' => $request->min_qty_edit,
                    'max_qty' => $request->max_qty_edit,
                    'stat' => $request->stat_edit,
                ]);
                
                // get satuan nama
                $get_nama_satuan = satuan::find($request->kode_satuan_edit)->nama_satuan;
                Konversi::on($this->connection())->where('kode_produk', $idProduk)->update([
                    'kode_satuan' => $request->kode_satuan_edit,
                    'satuan_terbesar' => $get_nama_satuan,
                    'kode_satuanterkecil' => $request->kode_satuan_edit,
                    'satuan_terkecil' => $get_nama_satuan
                ]);
                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data telah diupdate 2'
                ];
            }else{
                $message = [
                    'success' => false,
                    'title' => 'Gagal',
                    'message' => 'Produk ['.$request->nama_produk_edit.'] memiliki satuan terbesar. Silahkan hapus satuan terbesar dahulu'
                ];
            }
            user_history::on($this->connection())->create([
                'nama' => auth()->user()->name,
                'aksi' => 'Edit Produk : '.$request->nama_produk_edit,
                'created_by' => auth()->user()->name,
                'updated_by' => auth()->user()->name
            ]);
            return response()->json($message);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($idProduk)
    {
        // getting produk from Produk table
        $produk = Produk::on($this->connection())->find($idProduk);

        // pembelian number from PembelianDetail table
        $no_pembelian = PembelianDetail::on($this->connection())->where('kode_produk', $idProduk)->first();

        // getting penerimaan number from PenerimaanDetail
        $no_penerimaan = PenerimaanDetail::on($this->connection())->where('kode_produk', $idProduk)->first();

        // getting pemakaian number from PemakaianDetail
        $no_pemakaian = PemakaianDetail::on($this->connection())->where('kode_produk', $idProduk)->first();

        // getting pemakaianban number from PemakaianbanDetail
        $no_pemakaianban = PemakaianbanDetail::on($this->connection())->where('kode_produk', $idProduk)->first();

        // getting opname number from OpnameDetail
        $no_opname = OpnameDetail::on($this->connection())->where('kode_produk', $idProduk)->first();

        // getting adjustment detail numbuer from AdjustmentDetail
        $no_adjustment = AdjustmentDetail::on($this->connection())->where('kode_produk', $idProduk)->first();

        // getting penjualan number from PenjualanDetail
        $no_penjualan = PenjualanDetail::on($this->connection())->where('kode_produk', $idProduk)->first();

        // getting transfer in number from TransferInDetail
        $no_trfin = TransferInDetail::on($this->connection())->where('kode_produk', $idProduk)->first();

        // getting transfer out number from TransferDetail
        $no_trfout = TransferDetail::on($this->connection())->where('kode_produk', $idProduk)->first();

        // getting no konversi from Konversi 
        $no_konversi = Konversi::on($this->connection())->where('kode_produk', $idProduk)->count();

        // getting rack location from LokasiRak table
        $lokasi_rak = LokasiRak::on($this->connection())->where('kode_produk', $idProduk)->first();

        if($no_pembelian == null && $no_pemakaian == null && $no_opname == null && $no_adjustment == null && $lokasi_rak == null && $no_penerimaan == null && $no_pemakaianban == null && $no_penjualan == null && $no_trfin == null && $no_trfout == null){
            $bulanan = tb_item_bulanan::on($this->connection())->where('kode_produk', $idProduk)->first();
            if($bulanan != null){
                $bulanan->delete();
            }

            // update produkcounter by descresing one
            $prefix = strtoupper($produk->kode_produk[0]);
            $produk_index = ProdukCounter::on($this->connection())->where('index', $prefix)->first();
            $jml_final = $produk_index->jumlah - 1;
            ProdukCounter::on($this->connection())->where('index', $prefix)->update([
                'jumlah' => $jml_final
            ]);

            // delete konversi
            Konversi::on($this->connection())->where('kode_produk', $idProduk)->delete();

            $produk->delete();

            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data ['.$produk->nama_produk.'] telah dihapus'
            ];
        }else{
            $message = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Data ['.$produk->nama_produk.'] dipakai dalam transaksi'
            ];
        }
        user_history::on($this->connection())->create([
            'nama' => auth()->user()->name,
            'aksi' => 'Hapus Produk : '.$produk->nama_produk.'.',
            'created_by' => auth()->user()->name,
            'updated_by' => auth()->user()->name
        ]);

        return response()->json($message);
    }

    // export to PDF / excel
    public function exportPDF(Request $request){
        // getting nama produk from Produk table
        $get_nama_produk = Produk::on($this->connection())->select('nama_produk')->find($request->kode_produk_detail)->nama_produk;

        // getting nama company from Company table
        $get_nama_company = Company::select('nama_company')->find(auth()->user()->kode_company)->nama_company;

        // set kode_produk
        $kode_produk = $request->kode_produk_detail;

        // set kode lokasi detail 
        $lokasi = $request->kode_lokasi_detail;

        // checklist field_detail
        $pemakaian = false;
        $penerimaan = false;
        $penjualan = false;
        $adjustment = false;
        $opname = false;
        $transferin = false;
        $transferout = false;
        $returbeli = false;
        $returjual = false;
        $disassembling = false;
        $assembling = false;
        $semua = false;
        
        foreach ($request->field_detail as $item) {
            if($item == 'Pemakaian'){
                $pemakaian = true;
            }else if($item == 'Penerimaan'){
                $penerimaan = true;
            }else if($item == 'Penjualan'){
                $penjualan = true;
            }else if($item == 'Adjustment'){
                $adjustment = true;
            }else if($item == 'Opname'){
                $opname = true;
            }else if($item == 'Transfer_In'){
                $transferin = true;
            }else if($item == 'Transfer_Out'){
                $transferout = true;
            }else if($item == 'Retur_Beli'){
                $returbeli = true;
            }else if($item == 'Retur_Jual'){
                $returjual = true;
            }else if($item == 'Disassembling'){
                $disassembling = true;
            }else if($item == 'Assembling'){
                $assembling = true;
            }else if($item == 'SEMUA'){
                $semua = true;
            }
        }


        // determining the location of the logged in user
        if(auth()->user()->kode_lokasi == 'HO'){
            // check if kode_lokasi_detail is not 'SEMUA'
            if($request->kode_lokasi_detail != 'SEMUA'){
                // getting nama lokasi from MasterLokasi
                $nama_lokasi = MasterLokasi::select('nama_lokasi')->find(auth()->user()->kode_lokasi)->nama_lokasi;

                // getting awal date
                $awal = Carbon::parse($request->tanggal_awal_detail)->format('F Y');

                // getting akhir date
                $akhir = Carbon::parse($request->tanggal_akhir_detail)->format('F Y');

                // check if the format is PDF
                if($request->format_detail == 'PDF'){
                    // check history_detail if it is 'Monthly'
                    if($request->history_detail == 'Monthly'){
                        

                        // getting monthly report from tb_item_bulanan
                        $monthlyreport = tb_item_bulanan::on($this->connection())->where('kode_produk', $request->kode_produk_detail)->where('kode_lokasi', $request->kode_lokasi_detail)->whereBetween('periode', [$request->tanggal_awal_detail, $request->tanggal_akhir_detail])->orderBy('periode', 'asc')->get();

                        
                        return PDF::loadView('/admin/produk1/monthly', compact('monthlyreport', 'kode_produk', 'awal', 'akhir','get_nama_produk', 'nama_lokasi', 'lokasi', 'get_nama_company', 'pemakaian', 'penerimaan', 'penjualan', 'adjustment', 'opname', 'transferin', 'transferout', 'returbeli', 'returjual', 'disassembling', 'assembling', 'semua'))->setPaper('legal', 'landscape')->stream('Laporan_bulanan_'. $get_nama_produk .'.pdf');
                        // return response()->json($monthlyreport);
                    }else{
                         // set tanggal awal detail
                        $tanggal_awal_detail = Carbon::parse($request->tanggal_awal_detail)->format('d F Y');

                        // set tanggal akhir detail
                        $tanggal_akhir_detail = Carbon::parse($request->tanggal_akhir_detail)->format('d F Y');

                        // if history_detail is not 'Monthly'
                        $transaksi = tb_produk_history::on($this->connection())->where('kode_produk', $kode_produk)->where('kode_lokasi', auth()->user()->kode_lokasi)->whereBetween('tanggal_transaksi', [$request->tanggal_awal_detail, $request->tanggal_akhir_detail])->orderBy('created_at', 'asc')->get();

                        return PDF::loadView('admin/produk1/transaksi', compact('transaksi', 'kode_produk', 'tanggal_awal_detail', 'tanggal_akhir_detail', 'get_nama_produk', 'nama_lokasi', 'get_nama_company', 'lokasi'))->setPaper([0, 0, 684, 792])->stream('Laporan_Transaksi_'.$get_nama_produk.'.pdf');
                    }
                }else{
                    // if format_detail is Excel
                    if($request->history_detail == 'Monthly'){
                        // monthly report
                        return Excel::download(new Monthly1Export($kode_produk, $lokasi, $request->tanggal_awal_detail, $request->tanggal_akhir_detail, $request->history_detail, $get_nama_produk, $pemakaian, $penerimaan, $penjualan, $adjustment, $opname, $transferin, $transferout, $returbeli, $returjual, $disassembling, $assembling, $semua), 'Laporan_Bulanan'.$get_nama_produk.'.xlsx');
                    }else{
                        // transaction report
                        return Excel::download(new Monthly1Export($kode_produk, $lokasi, $request->tanggal_awal_detail, $request->tanggal_akhir_detail, $request->history_detail, $get_nama_produk), 'Laporan_Bulanan'.$get_nama_produk.'.xlsx');
                    }
                }
            }else{
                // if kode_lokasi_detail is 'SEMUA'

                // getting nama lokasi from MasterLokasi
                $nama_lokasi = MasterLokasi::select('nama_lokasi')->find(auth()->user()->kode_lokasi)->nama_lokasi;

                // getting awal date
                $awal = Carbon::parse($request->tanggal_awal_detail)->format('F Y');

                // getting akhir date
                $akhir = Carbon::parse($request->tanggal_akhir_detail)->format('F Y');

                // check if the format is pdf
                if($request->format_detail == 'PDF'){
                    // check if history_detail is 'Monthly'
                    if($request->history_detail == 'Monthly'){
                        // getting monthly report from tb_item_bulanan
                        $monthlyreport = tb_item_bulanan::on($this->connection())->where('kode_produk', $request->kode_produk_detail)->whereBetween('periode', [$request->tanggal_awal_detail, $request->tanggal_akhir_detail])->orderBy('periode', 'asc')->get();

                        
                        return PDF::loadView('/admin/produk1/monthly', compact('monthlyreport', 'kode_produk', 'awal', 'akhir','get_nama_produk', 'nama_lokasi', 'lokasi', 'get_nama_company', 'pemakaian', 'penerimaan', 'penjualan', 'adjustment', 'opname', 'transferin', 'transferout', 'returbeli', 'returjual', 'disassembling', 'assembling', 'semua'))->setPaper('legal', 'landscape')->stream('Laporan_bulanan_'. $get_nama_produk .'.pdf');
                    }else{
                        // check if history_detail 'Transaction'
                        // set tanggal awal detail
                        $tanggal_awal_detail = Carbon::parse($request->tanggal_awal_detail)->format('d F Y');

                        // set tanggal akhir detail
                        $tanggal_akhir_detail = Carbon::parse($request->tanggal_akhir_detail)->format('d F Y');

                        // if history_detail is not 'Monthly'
                        $transaksi = tb_produk_history::on($this->connection())->where('kode_produk', $kode_produk)->whereBetween('tanggal_transaksi', [$request->tanggal_awal_detail, $request->tanggal_akhir_detail])->orderBy('created_at', 'asc')->get();

                        return PDF::loadView('admin/produk1/transaksi', compact('transaksi', 'kode_produk', 'tanggal_awal_detail', 'tanggal_akhir_detail', 'get_nama_produk', 'nama_lokasi', 'get_nama_company', 'lokasi'))->setPaper([0, 0, 684, 792])->stream('Laporan_Transaksi_'.$get_nama_produk.'.pdf');
                    }
                }else{
                    // if the format is excel
                    // check if monthly report
                    if($request->history_detail == 'Monthly'){
                        return Excel::download(new Monthly1Export($kode_produk, $lokasi, $request->tanggal_awal_detail, $request->tanggal_akhir_detail, $request->history_detail, $get_nama_produk, $pemakaian, $penerimaan, $penjualan, $adjustment, $opname, $transferin, $transferout, $returbeli, $returjual, $disassembling, $assembling, $semua), 'Laporan_Bulanan'.$get_nama_produk.'.xlsx');
                    }else{
                        // if transaction report
                        return Excel::download(new Monthly1Export($kode_produk, $lokasi, $request->tanggal_awal_detail, $request->tanggal_akhir_detail, $request->history_detail, $get_nama_produk), 'Laporan_Transaction'.$get_nama_produk.'.xlsx');
                    }
                }
            }
        }else{
            // if kode_lokasi is not HO
            // getting nama lokasi from MasterLokasi
            $nama_lokasi = MasterLokasi::select('nama_lokasi')->find(auth()->user()->kode_lokasi)->nama_lokasi;

            // getting awal date
            $awal = Carbon::parse($request->tanggal_awal_detail)->format('F Y');

            // getting akhir date
            $akhir = Carbon::parse($request->tanggal_akhir_detail)->format('F Y');
            // check if the format is PDF
            if($request->format_detail == 'PDF'){
                // check history_detail if it is 'Monthly'
                if($request->history_detail == 'Monthly'){
                    

                    // getting monthly report from tb_item_bulanan
                    $monthlyreport = tb_item_bulanan::on($this->connection())->where('kode_produk', $request->kode_produk_detail)->where('kode_lokasi', $request->kode_lokasi_detail)->whereBetween('periode', [$request->tanggal_awal_detail, $request->tanggal_akhir_detail])->orderBy('periode', 'asc')->get();

                    
                    return PDF::loadView('/admin/produk1/monthly', compact('monthlyreport', 'kode_produk', 'awal', 'akhir','get_nama_produk', 'nama_lokasi', 'lokasi', 'get_nama_company', 'pemakaian', 'penerimaan', 'penjualan', 'adjustment', 'opname', 'transferin', 'transferout', 'returbeli', 'returjual', 'disassembling', 'assembling', 'semua'))->setPaper('legal', 'landscape')->stream('Laporan_bulanan_'. $get_nama_produk .'.pdf');
                }else{
                    // getting transaction report
                     // set tanggal awal detail
                    $tanggal_awal_detail = Carbon::parse($request->tanggal_awal_detail)->format('d F Y');

                    // set tanggal akhir detail
                    $tanggal_akhir_detail = Carbon::parse($request->tanggal_akhir_detail)->format('d F Y');

                    // if history_detail is not 'Monthly'
                    $transaksi = tb_produk_history::on($this->connection())->where('kode_produk', $kode_produk)->where('kode_lokasi', auth()->user()->kode_lokasi)->whereBetween('tanggal_transaksi', [$request->tanggal_awal_detail, $request->tanggal_akhir_detail])->orderBy('created_at', 'asc')->get();

                    return PDF::loadView('admin/produk1/transaksi', compact('transaksi', 'kode_produk', 'tanggal_awal_detail', 'tanggal_akhir_detail', 'get_nama_produk', 'nama_lokasi', 'get_nama_company', 'lokasi'))->setPaper([0, 0, 684, 792])->stream('Laporan_Transaksi_'.$get_nama_produk.'.pdf');
                }
            }else{
                // if format_detail is Excel
                if($request->history_detail == 'Monthly'){
                    // monthly report
                    return Excel::download(new Monthly1Export($kode_produk, $lokasi, $request->tanggal_awal_detail, $request->tanggal_akhir_detail, $request->history_detail, $get_nama_produk, $pemakaian, $penerimaan, $penjualan, $adjustment, $opname, $transferin, $transferout, $returbeli, $returjual, $disassembling, $assembling, $semua), 'Laporan_Bulanan'.$get_nama_produk.'.xlsx');
                }else{
                    // transaction report
                    return Excel::download(new Monthly1Export($kode_produk, $lokasi, $request->tanggal_awal_detail, $request->tanggal_akhir_detail, $request->history_detail, $get_nama_produk), 'Laporan_Bulanan'.$get_nama_produk.'.xlsx');
                }
            }
        }
        return response()->json($request->all());
    }
}
