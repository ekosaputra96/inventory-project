<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Route::get('/', function () {
//    return view('welcome');
//});

Auth::routes();

Route::redirect('/','home');
Route::get('/home', 'HomeController@index')->name('home');
Route::get('start','StartController@index')->name('start');
Route::post('start', 'StartController@go_to')->name('start.go_to');
Route::post('start', 'StartController@change')->name('start.change');
// Route::post('login', 'LoginController@check')->name('login.check');
Route::post('/home', 'HomeController@savechat')->name('home.savechat');

Route::get('start2','Start2Controller@index')->name('start2');
Route::post('start2', 'Start2Controller@go_to')->name('start2.go_to');
Route::post('start2', 'Start2Controller@change')->name('start2.change');

Route::get('start3','Start3Controller@index')->name('start3');
Route::post('start3', 'Start3Controller@go_to')->name('start3.go_to');
Route::post('start3', 'Start3Controller@change')->name('start3.change');

Route::get('start4','Start4Controller@index')->name('start4');
Route::post('start4', 'Start4Controller@go_to')->name('start4.go_to');
Route::post('start4', 'Start4Controller@change')->name('start4.change');

Route::get('start5','Start5Controller@index')->name('start5');
Route::post('start5', 'Start5Controller@go_to')->name('start5.go_to');
Route::post('start5', 'Start5Controller@change')->name('start5.change');

// Auth()->loginUsingId(1);
// dd(Auth()->user()->kode_company);
// Route::get('testing', function ()
// {
//     dd(Auth()->user()->kode_company);
// });


Route::middleware(['auth'])->prefix('admin')->group(function () {

    Route::get('export', 'MyController@export')->name('export');
    Route::get('importExportView', 'MyController@importExportView');
    Route::post('import', 'MyController@import')->name('import');

    // /**
    //  * Lokasi Rak
    //  */
    Route::get('lokasirak/anydata', 'LokasiRakController@anyData')->name('lokasirak.data');
    Route::post('lokasirak/updateAjax', 'LokasiRakController@updateAjax')->name('lokasirak.updateajax');
    Route::post('lokasirak/hapus_lokasirak', 'LokasiRakController@hapus_lokasirak')->name('lokasirak.hapus_lokasirak');
    Route::post('lokasirak/edit_lokasirak', 'LokasiRakController@edit_lokasirak')->name('lokasirak.edit_lokasirak');
    Route::post('lokasirak/stockproduk', 'LokasiRakController@stockProduk')->name('lokasirak.stockproduk');
    Route::post('lokasirak/selectpart/', 'LokasiRakController@selectpart')->name('lokasirak.selectpart');
    Route::resource('lokasirak','LokasiRakController');

     /**
     * Satuan
     */
    Route::get('satuan/anydata', 'SatuanController@anyData')->name('satuan.data');
    Route::post('satuan/updateAjax', 'SatuanController@updateAjax')->name('satuan.ajaxupdate');
    Route::post('satuan/hapus_satuan', 'SatuanController@hapus_satuan')->name('satuan.hapus_satuan');
    Route::post('satuan/edit_satuan', 'SatuanController@edit_satuan')->name('satuan.edit_satuan');
    Route::resource('satuan', 'SatuanController');
    
    /**
     * Permintaan Kasbon
     */
    Route::get('kasbon/anydata', 'KasbonController@anyData')->name('kasbon.data');
    Route::get('kasbon/exportpdf','KasbonController@exportPDF')->name('kasbon.export');
    Route::post('kasbon/updateAjax', 'KasbonController@updateAjax')->name('kasbon.ajaxupdate');
    Route::post('kasbon/hapus_kasbon', 'KasbonController@hapus_kasbon')->name('kasbon.hapus_kasbon');
    Route::post('kasbon/edit_kasbon', 'KasbonController@edit_kasbon')->name('kasbon.edit_kasbon');
    Route::post('kasbon/approve', 'KasbonController@approve')->name('kasbon.approve');
    Route::post('kasbon/Post', 'KasbonController@Post')->name('kasbon.post');
    Route::post('kasbon/Unpost', 'KasbonController@Unpost')->name('kasbon.unpost');
    Route::resource('kasbon', 'KasbonController');

    /**
     * Permintaan Kasbon 1
     */

    Route::get('kasbon1/getkasbon', 'Kasbon1Controller@getKasbon')->name('kasbon1.getkasbon');
    Route::post('kasbon1/postkasbon', 'Kasbon1Controller@postKasbon')->name('kasbon1.postkasbon');
    Route::post('kasbon1/unpostkasbon', 'Kasbon1Controller@unpostKasbon')->name('kasbon1.unpostkasbon');
    Route::post('kasbon1/approvedkasbon', 'Kasbon1Controller@approvedKasbon')->name('kasbon1.approvedkasbon');
    Route::get('kasbon1/exportpdf/{id_pkb}', 'Kasbon1Controller@exportPdf')->name('kasbon1.exportpdf');
    Route::resource('kasbon1', 'Kasbon1Controller');

     /**
     * Konversi
     */
    Route::post('konversi/satuan_produk', 'KonversiController@satuan_produk')->name('konversi.satuan_produk');
    Route::post('konversi/getkode', 'KonversiController@getkode')->name('konversi.getkode');
    Route::post('konversi/satuan_produk2', 'KonversiController@satuan_produk2')->name('konversi.satuan_produk2');
    Route::post('konversi/satuan_produk3', 'KonversiController@satuan_produk3')->name('konversi.satuan_produk3');
    Route::get('konversi/anydata', 'KonversiController@anyData')->name('konversi.data');
    Route::post('konversi/updateAjax', 'KonversiController@updateAjax')->name('konversi.ajaxupdate');
    Route::post('konversi/hapus_konversi', 'KonversiController@hapus_konversi')->name('konversi.hapus_konversi');
    Route::post('konversi/edit_konversi', 'KonversiController@edit_konversi')->name('konversi.edit_konversi');
    Route::post('konversi/getkode', 'KonversiController@getkode')->name('konversi.getkode');
    Route::resource('konversi', 'KonversiController');

    /**
     * Katalog
     */
    Route::get('katalog/anydata', 'KatalogController@anyData')->name('katalog.data');
    Route::post('katalog/updateAjax', 'KatalogController@updateAjax')->name('katalog.ajaxupdate');
    Route::post('katalog/hapus_katalog', 'KatalogController@hapus_katalog')->name('katalog.hapus_katalog');
    Route::post('katalog/edit_katalog', 'KatalogController@edit_katalog')->name('katalog.edit_katalog');
    Route::resource('katalog', 'KatalogController');

    /**
     * COA
     */
    Route::get('coa/anydata', 'CoaController@anyData')->name('coa.data');
    Route::post('coa/updateAjax', 'CoaController@updateAjax')->name('coa.ajaxupdate');
    Route::post('coa/hapus_coa', 'CoaController@hapus_coa')->name('coa.hapus_coa');
    Route::post('coa/edit_coa', 'CoaController@edit_coa')->name('coa.edit_coa');
    Route::post('coa/showsub', 'CoaController@showsub')->name('coa.showsub');
    Route::resource('coa', 'CoaController');
    
    /**
     * Kartu-Stock
     */
    Route::get('kartustok/exportpdf','KartustokController@exportPDF')->name('kartustok.export');
    Route::post('kartustok/change', 'KartustokController@change')->name('kartustok.change');
    Route::get('kartustok/anydata', 'KartustokController@anyData')->name('kartustok.data');
    Route::post('kartustok/updateAjax', 'KartustokController@updateAjax')->name('kartustok.ajaxupdate');
    Route::resource('kartustok', 'KartustokController');
    
    /**
     * Re-Open
     */
    Route::post('reopen/change2', 'ReopenController@change2')->name('reopen.change2');
    Route::post('reopen/change', 'ReopenController@change')->name('reopen.change');
    Route::get('reopen/anydata', 'ReopenController@anyData')->name('reopen.data');
    Route::post('reopen/updateAjax', 'ReopenController@updateAjax')->name('reopen.ajaxupdate');
    Route::resource('reopen', 'ReopenController');

    /**
     * Endofmonth
     */
    Route::post('endofmonth/change', 'EndofmonthController@change')->name('endofmonth.change');
    Route::get('endofmonth/anydata', 'EndofmonthController@anyData')->name('endofmonth.data');
    Route::post('endofmonth/updateAjax', 'EndofmonthController@updateAjax')->name('endofmonth.ajaxupdate');
    Route::resource('endofmonth', 'EndofmonthController');
    
    /**
     * Endofmonthpart
     */
    Route::post('endofmonthpart/change', 'EndofmonthpartController@change')->name('endofmonthpart.change');
    Route::get('endofmonthpart/anydata', 'EndofmonthpartController@anyData')->name('endofmonthpart.data');
    Route::post('endofmonthpart/updateAjax', 'EndofmonthpartController@updateAjax')->name('endofmonthpart.ajaxupdate');
    Route::resource('endofmonthpart', 'EndofmonthpartController');
    
    /**
     * Check Monthly
     */
    Route::post('checkmonthly/change', 'CheckmonthlyController@change')->name('checkmonthly.change');
    Route::get('checkmonthly/anydata', 'CheckmonthlyController@anyData')->name('checkmonthly.data');
    Route::post('checkmonthly/updateAjax', 'CheckmonthlyController@updateAjax')->name('checkmonthly.ajaxupdate');
    Route::resource('checkmonthly', 'CheckmonthlyController');
    
    /**
     * Check Monthly Part
     */
    Route::post('checkmonthlypart/change', 'CheckmonthlypartController@change')->name('checkmonthlypart.change');
    Route::post('checkmonthlypart/selectpart/', 'CheckmonthlypartController@selectpart')->name('checkmonthlypart.selectpart');
    Route::get('checkmonthlypart/anydata', 'CheckmonthlypartController@anyData')->name('checkmonthlypart.data');
    Route::post('checkmonthlypart/updateAjax', 'CheckmonthlypartController@updateAjax')->name('checkmonthlypart.ajaxupdate');
    Route::resource('checkmonthlypart', 'CheckmonthlypartController');

    /**
     * Check Penerimaan
     */
    Route::post('checkpenerimaan/change', 'CheckpenerimaanController@change')->name('checkpenerimaan.change');
    Route::get('checkpenerimaan/anydata', 'CheckpenerimaanController@anyData')->name('checkpenerimaan.data');
    Route::post('checkpenerimaan/updateAjax', 'CheckpenerimaanController@updateAjax')->name('checkpenerimaan.ajaxupdate');
    Route::resource('checkpenerimaan', 'CheckpenerimaanController');

    /**
     * Check Pemakaian
     */
    Route::post('checkpemakaian/change', 'CheckpemakaianController@change')->name('checkpemakaian.change');
    Route::get('checkpemakaian/anydata', 'CheckpemakaianController@anyData')->name('checkpemakaian.data');
    Route::post('checkpemakaian/updateAjax', 'CheckpemakaianController@updateAjax')->name('checkpemakaian.ajaxupdate');
    Route::resource('checkpemakaian', 'CheckpemakaianController');

    /**
     * Check Pemakaian Ban
     */
    Route::post('checkpemakaianban/change', 'CheckpemakaianbanController@change')->name('checkpemakaianban.change');
    Route::get('checkpemakaianban/anydata', 'CheckpemakaianbanController@anyData')->name('checkpemakaianban.data');
    Route::post('checkpemakaianban/updateAjax', 'CheckpemakaianbanController@updateAjax')->name('checkpemakaianban.ajaxupdate');
    Route::resource('checkpemakaianban', 'CheckpemakaianbanController');

    /**
     * Check Penjualan
     */
    Route::post('checkpenjualan/change', 'CheckpenjualanController@change')->name('checkpenjualan.change');
    Route::get('checkpenjualan/anydata', 'CheckpenjualanController@anyData')->name('checkpenjualan.data');
    Route::post('checkpenjualan/updateAjax', 'CheckpenjualanController@updateAjax')->name('checkpenjualan.ajaxupdate');
    Route::resource('checkpenjualan', 'CheckpenjualanController');
    
    /**
     * Merek
     */
    Route::get('merek/anydata', 'MerekController@anyData')->name('merek.data');
    Route::post('merek/updateAjax', 'MerekController@updateAjax')->name('merek.ajaxupdate');
    Route::post('merek/hapus_merek', 'MerekController@hapus_merek')->name('merek.hapus_merek');
    Route::post('merek/edit_merek', 'MerekController@edit_merek')->name('merek.edit_merek');
    Route::resource('merek', 'MerekController');

     /**
     * Ukuran
     */
    Route::get('ukuran/anydata', 'UkuranController@anyData')->name('ukuran.data');
    Route::post('ukuran/updateAjax', 'UkuranController@updateAjax')->name('ukuran.ajaxupdate');
    Route::post('ukuran/hapus_ukuran', 'UkuranController@hapus_ukuran')->name('ukuran.hapus_ukuran');
    Route::post('ukuran/edit_ukuran', 'UkuranController@edit_ukuran')->name('ukuran.edit_ukuran');
    Route::resource('ukuran', 'UkuranController');

     /**
     * Kategori Produk
     */
    Route::get('kategoriproduk/anydata', 'KategoriProdukController@anyData')->name('kategoriproduk.data');
    Route::post('kategoriproduk/updateAjax', 'KategoriProdukController@updateAjax')->name('kategoriproduk.ajaxupdate');
    Route::post('kategoriproduk/hapus_kategori', 'KategoriProdukController@hapus_kategori')->name('kategoriproduk.hapus_kategori');
    Route::post('kategoriproduk/edit_kategori', 'KategoriProdukController@edit_kategori')->name('kategoriproduk.edit_kategori');
    Route::resource('kategoriproduk', 'KategoriProdukController');
    
    /**
     * Unit
     */
    Route::get('unit/anydata', 'UnitController@anyData')->name('unit.data');
    Route::post('unit/updateAjax', 'UnitController@updateAjax')->name('unit.ajaxupdate');
    Route::post('unit/hapus_unit', 'UnitController@hapus_unit')->name('unit.hapus_unit');
    Route::post('unit/edit_unit', 'UnitController@edit_unit')->name('unit.edit_unit');
    Route::resource('unit', 'UnitController');

    /**
     * Vendor
     */
    Route::get('vendor/anydata', 'VendorController@anyData')->name('vendor.data');
    Route::post('vendor/getcoa', 'VendorController@getcoa')->name('vendor.getcoa');
    Route::post('vendor/showdetail', 'VendorController@Showdetail')->name('vendor.showdetail');
    Route::post('vendor/updateAjax', 'VendorController@updateAjax')->name('vendor.ajaxupdate');
    Route::post('vendor/hapus_vendor', 'VendorController@hapus_vendor')->name('vendor.hapus_vendor');
    Route::post('vendor/edit_vendor', 'VendorController@edit_vendor')->name('vendor.edit_vendor');
    Route::get('vendor/{kodevendor}/detail', 'VendorController@detail')->name('vendor.detail');
    Route::get('vendor/getDatabyID', 'VendorController@getDatabyID')->name('vendor.dataDetail');
    Route::post('vendor/store_coa', 'VendorController@store_coa')->name('vendor.store_coa');
    Route::post('vendor/edit_coa', 'VendorController@edit_coa')->name('vendor.edit_coa');
    Route::post('vendor/hapus_coa', 'VendorController@hapus_coa')->name('vendor.hapus_coa');
    Route::resource('vendor', 'VendorController');

    /**
     * Customer
     */
    Route::get('customer/anydata', 'CustomerController@anyData')->name('customer.data');
    Route::post('customer/updateAjax', 'CustomerController@updateAjax')->name('customer.ajaxupdate');
    Route::post('customer/hapus_customer', 'CustomerController@hapus_customer')->name('customer.hapus_customer');
    Route::post('customer/edit_customer', 'CustomerController@edit_customer')->name('customer.edit_customer');
    Route::resource('customer', 'CustomerController');

    /**
     * Company
     */
    Route::get('company/anydata', 'CompanyController@anyData')->name('company.data');
    Route::post('company/updateAjax', 'CompanyController@updateAjax')->name('company.ajaxupdate');
    Route::post('company/hapus_company', 'CompanyController@hapus_company')->name('company.hapus_company');
    Route::post('company/edit_company', 'CompanyController@edit_company')->name('company.edit_company');
    Route::resource('company', 'CompanyController');

    // Route to company 2
    Route::get('/company1/getcompanies', 'Company1Controller@getcompanies')->name('company1.getcompanies');
    Route::resource('company1', 'Company1Controller');

    /**
     * Produk
     */
    
    Route::get('produk/anydata', 'ProdukController@anyData')->name('produk.data');
    Route::post('produk/getkode', 'ProdukController@getkode')->name('produk.getkode');
    Route::post('produk/getkode2', 'ProdukController@getkode2')->name('produk.getkode2');
    Route::post('produk/updateAjax', 'ProdukController@updateAjax')->name('produk.ajaxupdate');
    Route::post('produk/hapus_produk', 'ProdukController@hapus_produk')->name('produk.hapus_produk');
    Route::post('produk/edit_produk', 'ProdukController@edit_produk')->name('produk.edit_produk');
    Route::post('produk/show_produk', 'ProdukController@show_produk')->name('produk.show_produk');
    Route::post('produk/showstock', 'ProdukController@showstock')->name('produk.showstock');
    Route::post('produk/showmonthly', 'ProdukController@Showmonthly')->name('produk.showmonthly');
    Route::get('produk/exportpdf','ProdukController@exportPDF')->name('produk.export');
    Route::get('produk/exportexcel','ProdukController@exportexcel')->name('produk.exportexcel');
    Route::post('produk/showdetail', 'ProdukController@showdetail')->name('produk.showdetail');
    Route::post('produk/showhistory', 'ProdukController@showhistory')->name('produk.showhistory');
    Route::resource('produk', 'ProdukController');

    // Produk1
    Route::get('produk1/exportpdf', 'Produk1Controller@exportPDF')->name('produk1.export');
    Route::get('produk1/exportexcel','Produk1Controller@exportexcel')->name('produk1.exportexcel');
    Route::get('produk1/getproducts', 'Produk1Controller@getProducts')->name('produk1.getproducts');
    Route::get('produk1/showstock/{id}', 'Produk1Controller@showStock')->name('produk1.showstock');
    Route::get('produk1/showhistory/{id}', 'Produk1Controller@showHistory')->name('produk1.showHistory');
    Route::get('produk1/showmonthly/{id}', 'Produk1Controller@showMonthly')->name('produk1.showmonthly');
    Route::get('produk1/detail/{id}', 'Produk1Controller@showDetail')->name('produk1.showdetail');
    Route::resource('produk1', 'Produk1Controller');

    /**
     * Pemakaian
     */
    Route::get('pemakaian/getDatapreview', 'PemakaianController@getDatapreview')->name('pemakaian.getDatapreview');
    Route::get('pemakaian/previewpo', 'PemakaianController@previewpo')->name('pemakaian.previewpo');
    Route::get('pemakaian/printpreview','PemakaianController@printpreview')->name('pemakaian.printpreview');
    Route::post('pemakaian/getkode', 'PemakaianController@getkode')->name('pemakaian.getkode');
    Route::post('pemakaian/getnopol', 'PemakaianController@getnopol')->name('pemakaian.getnopol');
    Route::post('pemakaian/getmobil', 'PemakaianController@getmobil')->name('pemakaian.getmobil');
    Route::post('pemakaian/getalat', 'PemakaianController@getalat')->name('pemakaian.getalat');
    Route::post('pemakaian/getwoalat', 'PemakaianController@getwoalat')->name('pemakaian.getwoalat');
    Route::post('pemakaian/getkapal', 'PemakaianController@getkapal')->name('pemakaian.getkapal');
    Route::post('pemakaian/getmobil2', 'PemakaianController@getmobil2')->name('pemakaian.getmobil2');
    Route::post('pemakaian/getalat2', 'PemakaianController@getalat2')->name('pemakaian.getalat2');
    Route::post('pemakaian/getkapal2', 'PemakaianController@getkapal2')->name('pemakaian.getkapal2');
    Route::post('pemakaian/getkodealat2', 'PemakaianController@getkodealat2')->name('pemakaian.getkodealat2');
    Route::post('pemakaian/getkodemobil2', 'PemakaianController@getkodemobil2')->name('pemakaian.getkodemobil2');
    Route::post('pemakaian/getkodekapal2', 'PemakaianController@getkodekapal2')->name('pemakaian.getkodekapal2');
    Route::post('pemakaian/hitungjurnal', 'PemakaianController@hitungjurnal')->name('pemakaian.hitungjurnal');
    Route::post('pemakaian/ttd_buat', 'PemakaianController@ttd_buat')->name('pemakaian.ttd_buat');
    Route::post('pemakaian/ttd_terima', 'PemakaianController@ttd_terima')->name('pemakaian.ttd_terima');

    Route::get('pemakaian/export2','PemakaianController@export2')->name('pemakaian.export2');
    Route::get('pemakaian/exportpdf3','PemakaianController@exportPDF3')->name('pemakaian.export3');
    Route::get('pemakaian/anydata', 'PemakaianController@anyData')->name('pemakaian.data');
    Route::get('pemakaian/limitos', 'PemakaianController@limitos')->name('pemakaian.limitos');
    Route::get('pemakaian/grandios', 'PemakaianController@grandios')->name('pemakaian.grandios');
    Route::post('pemakaian/cekjurnal2', 'PemakaianController@cekjurnal2')->name('pemakaian.cekjurnal2');
    Route::get('pemakaian/getDatajurnal2', 'PemakaianController@getDatajurnal2')->name('pemakaian.getDatajurnal2');
    Route::get('pemakaian/historia', 'PemakaianController@historia')->name('pemakaian.historia');
    
    Route::post('pemakaian/edit_pemakaian', 'PemakaianController@edit_pemakaian')->name('pemakaian.edit_pemakaian');
    Route::post('pemakaian/hapus_pemakaian', 'PemakaianController@hapus_pemakaian')->name('pemakaian.hapus_pemakaian');
    Route::post('pemakaian/updateAjax', 'PemakaianController@updateAjax')->name('pemakaian.updateajax');
    Route::get('pemakaian/{pemakaian}/detail', 'PemakaianController@detail')->name('pemakaian.detail');
    Route::post('pemakaian/postingulang', 'PemakaianController@postingulang')->name('pemakaian.postingulang');
    Route::post('pemakaian/posting', 'PemakaianController@posting')->name('pemakaian.posting');
    Route::post('pemakaian/unposting', 'PemakaianController@unposting')->name('pemakaian.unposting');
    Route::post('pemakaian/showdetail', 'PemakaianController@Showdetail')->name('pemakaian.showdetail');
    Route::post('pemakaian/kalkulasi_jurnal', 'PemakaianController@kalkulasi_jurnal')->name('pemakaian.kalkulasi_jurnal');
    Route::resource('pemakaian', 'PemakaianController');

    // /**
    //  * Pemakaian Detail
    //  */
    Route::post('pemakaiandetail/stockproduk', 'PemakaiandetailController@stockProduk')->name('pemakaiandetail.stockproduk');
    Route::post('pemakaiandetail/getharga', 'PemakaiandetailController@getharga')->name('pemakaiandetail.getharga');
    Route::post('pemakaiandetail/qtyproduk2', 'PemakaiandetailController@qtyProduk2')->name('pemakaiandetail.qtyproduk2');
    Route::post('pemakaiandetail/gethpp', 'PemakaiandetailController@gethpp')->name('pemakaiandetail.gethpp');
    Route::post('pemakaiandetail/hapusall', 'PemakaiandetailController@hapusall')->name('pemakaiandetail.hapusall');
    Route::post('pemakaiandetail/qtycheck', 'PemakaiandetailController@qtycheck')->name('pemakaiandetail.qtycheck');
    Route::get('pemakaiandetail/getDatabyID', 'PemakaiandetailController@getDatabyID')->name('pemakaiandetail.dataDetail');
    Route::post('pemakaiandetail/selectAjax/', 'PemakaiandetailController@selectAjax')->name('pemakaiandetail.selectAjax');
    Route::post('pemakaiandetail/selectpart/', 'PemakaiandetailController@selectpart')->name('pemakaiandetail.selectpart');
    Route::post('pemakaiandetail/updateAjax', 'PemakaiandetailController@updateAjax')->name('pemakaiandetail.updateajax');
    Route::post('pemakaiandetail/check', 'PemakaiandetailController@check')->name('pemakaiandetail.check');
    Route::resource('pemakaiandetail', 'PemakaiandetailController');
    
    /**
     * Pemakaianban
     */
    Route::get('pemakaianban/export2','PemakaianbanController@export2')->name('pemakaianban.export2');
    Route::get('pemakaianban/exportpdf2','PemakaianbanController@exportPDF2')->name('pemakaianban.export2');
    Route::get('pemakaianban/historia', 'PemakaianbanController@historia')->name('pemakaianban.historia');
    Route::post('pemakaianban/cekjurnal2', 'PemakaianbanController@cekjurnal2')->name('pemakaianban.cekjurnal2');
    Route::get('pemakaianban/getDatajurnal2', 'PemakaianbanController@getDatajurnal2')->name('pemakaianban.getDatajurnal2');
    Route::post('pemakaianban/getkode', 'PemakaianbanController@getkode')->name('pemakaianban.getkode');
    Route::post('pemakaianban/getmobil', 'PemakaianbanController@getmobil')->name('pemakaianban.getmobil');
    Route::post('pemakaianban/getmobil2', 'PemakaianbanController@getmobil2')->name('pemakaianban.getmobil2');
    Route::post('pemakaianban/getalat', 'PemakaianbanController@getalat')->name('pemakaianban.getalat');
    Route::post('pemakaianban/getalat2', 'PemakaianbanController@getalat2')->name('pemakaianban.getalat2');
    Route::get('pemakaianban/anydata', 'PemakaianbanController@anyData')->name('pemakaianban.data');
    Route::get('pemakaianban/limitos', 'PemakaianbanController@limitos')->name('pemakaianban.limitos');
     Route::get('pemakaianban/grandios', 'PemakaianbanController@grandios')->name('pemakaianban.grandios');
    Route::post('pemakaianban/edit_pemakaianban', 'PemakaianbanController@edit_pemakaianban')->name('pemakaianban.edit_pemakaianban');
    Route::post('pemakaianban/hapus_pemakaianban', 'PemakaianbanController@hapus_pemakaianban')->name('pemakaianban.hapus_pemakaianban');
    Route::post('pemakaianban/updateAjax', 'PemakaianbanController@updateAjax')->name('pemakaianban.updateajax');
    Route::get('pemakaianban/{pemakaianban}/detail', 'PemakaianbanController@detail')->name('pemakaianban.detail');
    Route::post('pemakaianban/posting', 'PemakaianbanController@posting')->name('pemakaianban.posting');
    Route::post('pemakaianban/unposting', 'PemakaianbanController@unposting')->name('pemakaianban.unposting');
    Route::post('pemakaianban/showdetail', 'PemakaianbanController@Showdetail')->name('pemakaianban.showdetail');
    Route::resource('pemakaianban', 'PemakaianbanController');
    
    // /**
    //  * Pemakaian Ban Detail
    //  */
    Route::post('pemakaianbandetail/stockproduk', 'PemakaianbandetailController@stockProduk')->name('pemakaianbandetail.stockproduk');
    Route::post('pemakaianbandetail/getharga', 'PemakaianbandetailController@getharga')->name('pemakaianbandetail.getharga');
    Route::post('pemakaianbandetail/gethpp', 'PemakaianbandetailController@gethpp')->name('pemakaianbandetail.gethpp');
    Route::post('pemakaianbandetail/getharga2', 'PemakaianbandetailController@getharga2')->name('pemakaianbandetail.getharga2');
    Route::post('pemakaianbandetail/qtyproduk2', 'PemakaianbandetailController@qtyProduk2')->name('pemakaianbandetail.qtyproduk2');
    Route::post('pemakaianbandetail/qtycheck', 'PemakaianbandetailController@qtycheck')->name('pemakaianbandetail.qtycheck');
    Route::get('pemakaianbandetail/getDatabyID', 'PemakaianbandetailController@getDatabyID')->name('pemakaianbandetail.dataDetail');
    Route::post('pemakaianbandetail/selectAjax/', 'PemakaianbandetailController@selectAjax')->name('pemakaianbandetail.selectAjax');
    Route::post('pemakaianbandetail/selectpart/', 'PemakaianbandetailController@selectpart')->name('pemakaianbandetail.selectpart');
    Route::post('pemakaianbandetail/selectpart2/', 'PemakaianbandetailController@selectpart2')->name('pemakaianbandetail.selectpart2');
    Route::post('pemakaianbandetail/selectpart3/', 'PemakaianbandetailController@selectpart3')->name('pemakaianbandetail.selectpart3');
    Route::post('pemakaianbandetail/updateAjax', 'PemakaianbandetailController@updateAjax')->name('pemakaianbandetail.updateajax');
    Route::post('pemakaianbandetail/store2', 'PemakaianbandetailController@store2')->name('pemakaianbandetail.store2');
    Route::post('pemakaianbandetail/check', 'PemakaianbandetailController@check')->name('pemakaianbandetail.check');
    Route::resource('pemakaianbandetail', 'PemakaianbandetailController');

    // /**
    //  * Penjualan
    //  */
    // Route::get('penjualan/exportpdf','PenjualanController@exportPDF')->name('penjualan.export');
    // Route::get('penjualan/anydata', 'PenjualanController@anyData')->name('penjualan.data');
    // Route::post('penjualan/getkode', 'PenjualanController@getkode')->name('penjualan.getkode');
    // Route::post('penjualan/edit_penjualan', 'PenjualanController@edit_penjualan')->name('penjualan.edit_penjualan');
    // Route::post('penjualan/hapus_penjualan', 'PenjualanController@hapus_penjualan')->name('penjualan.hapus_penjualan');
    // Route::post('penjualan/updateAjax', 'PenjualanController@updateAjax')->name('penjualan.ajaxupdate');
    // Route::get('penjualan/{penjualan}/detail', 'PenjualanController@detail')->name('penjualan.detail');
    // Route::post('penjualan/posting', 'PenjualanController@posting')->name('penjualan.posting');
    // Route::post('penjualan/unposting', 'PenjualanController@unposting')->name('penjualan.unposting');
    // Route::post('penjualan/showdetail', 'PenjualanController@Showdetail')->name('penjualan.showdetail');
    // Route::resource('penjualan', 'PenjualanController');

    // // /**
    // //  * Penjualan Detail
    // //  */
    // Route::post('penjualandetail/stockproduk', 'PenjualandetailController@stockProduk')->name('penjualandetail.stockproduk');
    // Route::post('penjualandetail/getharga', 'PenjualandetailController@getharga')->name('penjualandetail.getharga');
    // Route::post('penjualandetail/qtyproduk2', 'PenjualandetailController@qtyProduk2')->name('penjualandetail.qtyproduk2');
    // Route::post('penjualandetail/qtycheck', 'PenjualandetailController@qtycheck')->name('penjualandetail.qtycheck');
    // Route::get('penjualandetail/getDatabyID', 'PenjualandetailController@getDatabyID')->name('penjualandetail.dataDetail');
    // Route::post('penjualandetail/selectAjax/', 'PenjualandetailController@selectAjax')->name('penjualandetail.selectAjax');
    // Route::post('penjualandetail/selectpart/', 'PenjualandetailController@selectpart')->name('penjualandetail.selectpart');
    // Route::post('penjualandetail/selectsatuan/', 'PenjualandetailController@selectsatuan')->name('penjualandetail.selectsatuan');
    // Route::post('penjualandetail/updateAjax', 'PenjualandetailController@updateAjax')->name('penjualandetail.updateajax');
    // Route::post('penjualandetail/check', 'PenjualandetailController@check')->name('penjualandetail.check');
    // Route::resource('penjualandetail', 'PenjualandetailController');

    /**
     * Transfer
     */
    Route::post('transfer/getkode', 'TransferController@getkode')->name('transfer.getkode');
    Route::post('transfer/getnama', 'TransferController@getnama')->name('transfer.getnama');
    Route::get('transfer/historia', 'TransferController@historia')->name('transfer.historia');
    Route::get('transfer/cetakPDF', 'TransferController@cetakPDF')->name('transfer.cetak');
    Route::get('transfer/exportpdf2','TransferController@exportPDF2')->name('transfer.export2');
    Route::post('transfer/cekjurnal2', 'TransferController@cekjurnal2')->name('transfer.cekjurnal2');
    Route::get('transfer/getDatajurnal2', 'TransferController@getDatajurnal2')->name('transfer.getDatajurnal2');
    Route::get('transfer/anydata', 'TransferController@anyData')->name('transfer.data');
    Route::post('transfer/edit_transfer', 'TransferController@edit_transfer')->name('transfer.edit_transfer');
    Route::post('transfer/hapus_transfer', 'TransferController@hapus_transfer')->name('transfer.hapus_transfer');
    Route::post('transfer/updateAjax', 'TransferController@updateAjax')->name('transfer.updateajax');
    Route::get('transfer/{transfer}/detail', 'TransferController@detail')->name('transfer.detail');
    Route::post('transfer/posting', 'TransferController@posting')->name('transfer.posting');
    Route::post('transfer/unposting', 'TransferController@unposting')->name('transfer.unposting');
    Route::post('transfer/showdetail', 'TransferController@Showdetail')->name('transfer.showdetail');
    Route::resource('transfer', 'TransferController');

    // /**
    //  * Transfer Detail
    //  */
    Route::post('transferdetail/stockproduk', 'TransferDetailController@stockProduk')->name('transferdetail.stockproduk');
    Route::post('transferdetail/getharga', 'TransferDetailController@getharga')->name('transferdetail.getharga');
    Route::post('transferdetail/qtyproduk2', 'TransferDetailController@qtyProduk2')->name('transferdetail.qtyproduk2');
    Route::post('transferdetail/qtycheck', 'TransferDetailController@qtycheck')->name('transferdetail.qtycheck');
    Route::get('transferdetail/getDatabyID', 'TransferDetailController@getDatabyID')->name('transferdetail.dataDetail');
    Route::post('transferdetail/selectAjax/', 'TransferDetailController@selectAjax')->name('transferdetail.selectAjax');
    Route::post('transferdetail/selectpart/', 'TransferDetailController@selectpart')->name('transferdetail.selectpart');
    Route::post('transferdetail/updateAjax', 'TransferDetailController@updateAjax')->name('transferdetail.updateajax');
    Route::post('transferdetail/check', 'TransferDetailController@check')->name('transferdetail.check');
    Route::get('transferdetail/detail', 'TransferDetailController@detail')->name('transferdetail.detail');
    Route::resource('transferdetail', 'TransferDetailController');

    /**
     * Transferin
     */
    Route::get('transferin/anydata', 'TransferInController@anyData')->name('transferin.data');
    Route::get('transferin/historia', 'TransferInController@historia')->name('transferin.historia');
    Route::post('transferin/getkode', 'TransferInController@getkode')->name('transferin.getkode');
    Route::post('transferin/edit_transferin', 'TransferInController@edit_transferin')->name('transferin.edit_transferin');
    Route::post('transferin/hapus_transferin', 'TransferInController@hapus_transferin')->name('transferin.hapus_transferin');
    Route::post('transferin/qtyproduk', 'TransferInController@qtyProduk')->name('transferin.qtyproduk');
    Route::get('transferin/cetakPDF', 'TransferInController@cetakPDF')->name('transferin.cetak');
    Route::get('transferin/exportpdf2','TransferInController@exportPDF2')->name('transferin.export2');
    Route::post('transferin/cekjurnal2', 'TransferInController@cekjurnal2')->name('transferin.cekjurnal2');
    Route::get('transferin/getDatajurnal2', 'TransferInController@getDatajurnal2')->name('transferin.getDatajurnal2');
    Route::post('transferin/updateAjax', 'TransferInController@updateAjax')->name('transferin.updateajax');
    Route::get('transferin/{transferin}/detail', 'TransferInController@detail')->name('transferin.detail');
    Route::post('transferin/posting', 'TransferInController@posting')->name('transferin.posting');
    Route::post('transferin/unposting', 'TransferInController@unposting')->name('transferin.unposting');
    Route::post('transferin/showdetail', 'TransferInController@Showdetail')->name('transferin.showdetail');
    Route::resource('transferin', 'TransferInController');

     // /**
    //  * Transferin Detail
    //  */
    Route::post('transferindetail/qtyproduk2', 'TransferInDetailController@qtyProduk2')->name('transferindetail.qtyproduk2');
    Route::post('transferindetail/qtyproduk', 'TransferInDetailController@qtyProduk')->name('transferindetail.qtyproduk');
    Route::post('transferindetail/qtycheck', 'TransferInDetailController@qtycheck')->name('transferindetail.qtycheck');
    Route::get('transferindetail/getDatabyID', 'TransferInDetailController@getDatabyID')->name('transferindetail.dataDetail');
    Route::post('transferindetail/selectAjax/', 'TransferInDetailController@selectAjax')->name('transferindetail.selectAjax');
    Route::post('transferindetail/updateAjax', 'TransferInDetailController@updateAjax')->name('transferindetail.updateajax');
    Route::post('transferindetail/selectpart/', 'TransferInDetailController@selectpart')->name('transferindetail.selectpart');
    Route::post('transferindetail/getharga', 'TransferInDetailController@getharga')->name('transferindetail.getharga');
    Route::get('transferindetail/getdata', 'TransferInDetailController@getdata')->name('transferindetail.getdata');
    Route::get('transferindetail/detail', 'TransferInDetailController@detail')->name('transferindetail.detail');
    Route::resource('transferindetail', 'TransferInDetailController');

     /**
     * Penerimaan
     */
    Route::get('penerimaan/exportpdf','PenerimaanController@exportPDF')->name('penerimaan.export');
    Route::get('penerimaan/exportpdf2','PenerimaanController@exportPDF2')->name('penerimaan.export2');
    Route::get('penerimaan/anydata', 'PenerimaanController@anyData')->name('penerimaan.data');
    Route::get('penerimaan/historia', 'PenerimaanController@historia')->name('penerimaan.historia');
    Route::get('penerimaan/limitos', 'PenerimaanController@limitos')->name('penerimaan.limitos');
    Route::get('penerimaan/grandios', 'PenerimaanController@grandios')->name('penerimaan.grandios');
    Route::post('penerimaan/cekjurnal2', 'PenerimaanController@cekjurnal2')->name('penerimaan.cekjurnal2');
    Route::get('penerimaan/getDatajurnal2', 'PenerimaanController@getDatajurnal2')->name('penerimaan.getDatajurnal2');
    Route::post('penerimaan/hitungjurnal', 'PenerimaanController@hitungjurnal')->name('penerimaan.hitungjurnal');
    Route::post('penerimaan/getkode', 'PenerimaanController@getkode')->name('penerimaan.getkode');
    Route::post('penerimaan/edit_penerimaan', 'PenerimaanController@edit_penerimaan')->name('penerimaan.edit_penerimaan');
    Route::post('penerimaan/hapus_penerimaan', 'PenerimaanController@hapus_penerimaan')->name('penerimaan.hapus_penerimaan');
    Route::post('penerimaan/updateAjax', 'PenerimaanController@updateAjax')->name('penerimaan.updateajax');
    Route::get('penerimaan/{penerimaan}/detail', 'PenerimaanController@detail')->name('penerimaan.detail');
    Route::post('penerimaan/posting', 'PenerimaanController@posting')->name('penerimaan.posting');
    Route::post('penerimaan/unposting', 'PenerimaanController@unposting')->name('penerimaan.unposting');
    Route::post('penerimaan/showdetail', 'PenerimaanController@Showdetail')->name('penerimaan.showdetail');
    Route::resource('penerimaan', 'PenerimaanController');

     // /**
    //  * Penerimaan Detail
    //  */
    Route::post('penerimaandetail/qtyproduk2', 'PenerimaandetailController@qtyProduk2')->name('penerimaandetail.qtyproduk2');
    Route::post('penerimaandetail/getharga', 'PenerimaandetailController@getharga')->name('penerimaandetail.getharga');
    Route::post('penerimaandetail/getlanded', 'PenerimaandetailController@getlanded')->name('penerimaandetail.getlanded');
    Route::post('penerimaandetail/isipart', 'PenerimaandetailController@isipart')->name('penerimaandetail.isipart');
    Route::post('penerimaandetail/checkpart', 'PenerimaandetailController@checkpart')->name('penerimaandetail.checkpart');
    Route::post('penerimaandetail/qtyproduk', 'PenerimaandetailController@qtyProduk')->name('penerimaandetail.qtyproduk');
    Route::post('penerimaandetail/qtycheck', 'PenerimaandetailController@qtycheck')->name('penerimaandetail.qtycheck');
    Route::get('penerimaandetail/getDatabyID', 'PenerimaandetailController@getDatabyID')->name('penerimaandetail.dataDetail');
    Route::post('penerimaandetail/selectAjax/', 'PenerimaandetailController@selectAjax')->name('penerimaandetail.selectAjax');
    Route::post('penerimaandetail/updateAjax', 'PenerimaandetailController@updateAjax')->name('penerimaandetail.updateajax');
    Route::get('penerimaandetail/detail', 'PenerimaandetailController@detail')->name('penerimaandetail.detail');
    Route::resource('penerimaandetail', 'PenerimaandetailController');

    // /**
    //  * Pembelian
    //  */
    Route::get('pembelian/getDatapreview', 'PembelianController@getDatapreview')->name('pembelian.getDatapreview');
    Route::get('pembelian/previewpo', 'PembelianController@previewpo')->name('pembelian.previewpo');
    Route::get('pembelian/printpreview','PembelianController@printpreview')->name('pembelian.printpreview');
    Route::post('pembelian/ttd_buat', 'PembelianController@ttd_buat')->name('pembelian.ttd_buat');
    Route::post('pembelian/ttd_periksa', 'PembelianController@ttd_periksa')->name('pembelian.ttd_periksa');
    Route::post('pembelian/ttd_setuju', 'PembelianController@ttd_setuju')->name('pembelian.ttd_setuju');
    Route::post('pembelian/ttd_tahu', 'PembelianController@ttd_tahu')->name('pembelian.ttd_tahu');
    Route::get('pembelian/exportpdf','PembelianController@exportPDF')->name('pembelian.export');
    Route::get('pembelian/anydata', 'PembelianController@anyData')->name('pembelian.data');
    Route::get('pembelian/limitos', 'PembelianController@limitos')->name('pembelian.limitos');
    Route::get('pembelian/historia', 'PembelianController@historia')->name('pembelian.historia');
    Route::post('pembelian/getkode', 'PembelianController@getkode')->name('pembelian.getkode');
    Route::post('pembelian/get_ppn', 'PembelianController@get_ppn')->name('pembelian.get_ppn');
    Route::post('pembelian/get_ppn2', 'PembelianController@get_ppn2')->name('pembelian.get_ppn2');
    Route::post('pembelian/edit_pembelian', 'PembelianController@edit_pembelian')->name('pembelian.edit_pembelian');
    Route::post('pembelian/hapus_pembelian', 'PembelianController@hapus_pembelian')->name('pembelian.hapus_pembelian');
    Route::post('pembelian/void_pembelian', 'PembelianController@void_pembelian')->name('pembelian.void_pembelian');
    Route::post('pembelian/updateAjax', 'PembelianController@updateAjax')->name('pembelian.updateajax');
    Route::get('pembelian/{pembelian}/detail', 'PembelianController@detail')->name('pembelian.detail');
    Route::post('pembelian/Post', 'PembelianController@Post')->name('pembelian.post');
    Route::post('pembelian/Unpost', 'PembelianController@Unpost')->name('pembelian.unpost');
    Route::post('pembelian/Approve', 'PembelianController@Approve')->name('pembelian.approve');
    Route::post('pembelian/Disapprove', 'PembelianController@Disapprove')->name('pembelian.disapprove');
    Route::post('pembelian/showdetail', 'PembelianController@Showdetail')->name('pembelian.showdetail');
    Route::resource('pembelian','PembelianController');

     // /**
    //  * Pembelian Detail
    //  */
    Route::post('pembeliandetail/stockproduk', 'PembeliandetailController@stockProduk')->name('pembeliandetail.stockproduk');
    Route::post('pembeliandetail/satuankonversi', 'PembeliandetailController@satuankonversi')->name('pembeliandetail.satuankonversi');
    Route::post('pembeliandetail/selectAjax/', 'PembeliandetailController@selectAjax')->name('pembeliandetail.selectAjax');
    Route::post('pembeliandetail/selectAjax2/', 'PembeliandetailController@selectAjax2')->name('pembeliandetail.selectAjax2');
    Route::get('pembeliandetail/getDatabyID', 'PembeliandetailController@getDatabyID')->name('pembeliandetail.dataDetail');
    Route::post('pembeliandetail/updateAjax', 'PembeliandetailController@updateAjax')->name('pembeliandetail.updateajax');
    Route::resource('pembeliandetail', 'PembeliandetailController');
    
    // /**
    //  * Work Order
    //  */
    Route::get('workorder/exportpdf','WorkOrderController@exportPDF')->name('workorder.export');
    Route::get('workorder/anydata', 'WorkOrderController@anyData')->name('workorder.data');
    Route::get('workorder/limitos', 'WorkOrderController@limitos')->name('workorder.limitos');
    Route::get('workorder/historia', 'WorkOrderController@historia')->name('workorder.historia');
    Route::post('workorder/hitungdate', 'WorkOrderController@hitungdate')->name('workorder.hitungdate');
    Route::post('workorder/getkode', 'WorkOrderController@getkode')->name('workorder.getkode');
    Route::post('workorder/get_ppn', 'WorkOrderController@get_ppn')->name('workorder.get_ppn');
    Route::post('workorder/get_ppn2', 'WorkOrderController@get_ppn2')->name('workorder.get_ppn2');
    Route::post('workorder/edit_pembelian', 'WorkOrderController@edit_pembelian')->name('workorder.edit_pembelian');
    Route::post('workorder/hapus_pembelian', 'WorkOrderController@hapus_pembelian')->name('workorder.hapus_pembelian');
    Route::post('workorder/void_pembelian', 'WorkOrderController@void_pembelian')->name('workorder.void_pembelian');
    Route::post('workorder/updateAjax', 'WorkOrderController@updateAjax')->name('workorder.updateajax');
    Route::get('workorder/{pembelian}/detail', 'WorkOrderController@detail')->name('workorder.detail');
    Route::post('workorder/Post', 'WorkOrderController@Post')->name('workorder.post');
    Route::post('workorder/Unpost', 'WorkOrderController@Unpost')->name('workorder.unpost');
    Route::post('workorder/Close', 'WorkOrderController@Close')->name('workorder.close');
    Route::post('workorder/Approve', 'WorkOrderController@Approve')->name('workorder.approve');
    Route::post('workorder/Disapprove', 'WorkOrderController@Disapprove')->name('workorder.disapprove');
    Route::post('workorder/showdetail', 'WorkOrderController@Showdetail')->name('workorder.showdetail');
    Route::resource('workorder','WorkOrderController');

     // /**
    //  * Work Order Detail
    //  */
    Route::post('workorderdetail/stockproduk', 'WorkorderDetailController@stockProduk')->name('workorderdetail.stockproduk');
    Route::post('workorderdetail/satuankonversi', 'WorkorderDetailController@satuankonversi')->name('workorderdetail.satuankonversi');
    Route::post('workorderdetail/selectAjax/', 'WorkorderDetailController@selectAjax')->name('workorderdetail.selectAjax');
    Route::get('workorderdetail/getDatabyID', 'WorkorderDetailController@getDatabyID')->name('workorderdetail.dataDetail');
    Route::post('workorderdetail/updateAjax', 'WorkorderDetailController@updateAjax')->name('workorderdetail.updateajax');
    Route::post('workorderdetail/selectpart/', 'WorkorderDetailController@selectpart')->name('workorderdetail.selectpart');
    Route::resource('workorderdetail', 'WorkorderDetailController');
    
    // /**
    //  * Memo
    //  */
    Route::get('memo/getDatapreview', 'MemoController@getDatapreview')->name('memo.getDatapreview');
    Route::get('memo/previewpo', 'MemoController@previewpo')->name('memo.previewpo');
    Route::get('memo/printpreview','MemoController@printpreview')->name('memo.printpreview');
    Route::post('memo/ttd_buat', 'MemoController@ttd_buat')->name('memo.ttd_buat');
    Route::post('memo/ttd_periksa', 'MemoController@ttd_periksa')->name('memo.ttd_periksa');
    Route::post('memo/ttd_setuju', 'MemoController@ttd_setuju')->name('memo.ttd_setuju');
    Route::post('memo/ttd_tahu', 'MemoController@ttd_tahu')->name('memo.ttd_tahu');
    Route::get('memo/exportpdf','MemoController@exportPDF')->name('memo.export');
    Route::get('memo/anydata', 'MemoController@anyData')->name('memo.data');
    Route::get('memo/limitos', 'MemoController@limitos')->name('memo.limitos');
    Route::get('memo/historia', 'MemoController@historia')->name('memo.historia');
    Route::post('memo/getkode', 'MemoController@getkode')->name('memo.getkode');
    Route::post('memo/get_ppn', 'MemoController@get_ppn')->name('memo.get_ppn');
    Route::post('memo/get_ppn2', 'MemoController@get_ppn2')->name('memo.get_ppn2');
    Route::post('memo/edit_pembelian', 'MemoController@edit_memo')->name('memo.edit_memo');
    Route::post('memo/hapus_pembelian', 'MemoController@hapus_memo')->name('memo.hapus_memo');
    Route::post('memo/void_pembelian', 'MemoController@void_memo')->name('memo.void_memo');
    Route::post('memo/updateAjax', 'MemoController@updateAjax')->name('memo.updateajax');
    Route::get('memo/{memo}/detail', 'MemoController@detail')->name('memo.detail');
    Route::post('memo/closing', 'MemoController@closing')->name('memo.closing');
    Route::post('memo/Post', 'MemoController@Post')->name('memo.post');
    Route::post('memo/Unpost', 'MemoController@Unpost')->name('memo.unpost');
    Route::post('memo/Approve', 'MemoController@Approve')->name('memo.approve');
    Route::post('memo/Disapprove', 'MemoController@Disapprove')->name('memo.disapprove');
    Route::post('memo/showdetail', 'MemoController@Showdetail')->name('memo.showdetail');
    Route::resource('memo','MemoController');

     // /**
    //  * Memo Detail
    //  */
    Route::post('memodetail/stockproduk', 'MemodetailController@stockProduk')->name('memodetail.stockproduk');
    Route::post('memodetail/satuankonversi', 'MemodetailController@satuankonversi')->name('memodetail.satuankonversi');
    Route::post('memodetail/selectAjax/', 'MemodetailController@selectAjax')->name('memodetail.selectAjax');
    Route::get('memodetail/getDatabyID', 'MemodetailController@getDatabyID')->name('memodetail.dataDetail');
    Route::post('memodetail/updateAjax', 'MemodetailController@updateAjax')->name('memodetail.updateajax');
    Route::resource('memodetail', 'MemodetailController');

     // /**
    //  * Request
    //  */
    Route::get('requestpembelian/getDatapreview', 'RequestpembelianController@getDatapreview')->name('requestpembelian.getDatapreview');
    Route::get('requestpembelian/previewpo', 'RequestpembelianController@previewpo')->name('requestpembelian.previewpo');
    Route::get('requestpembelian/printpreview','RequestpembelianController@printpreview')->name('requestpembelian.printpreview');
    Route::post('requestpembelian/ttd_buat', 'RequestpembelianController@ttd_buat')->name('requestpembelian.ttd_buat');
    Route::post('requestpembelian/ttd_periksa', 'RequestpembelianController@ttd_periksa')->name('requestpembelian.ttd_periksa');
    Route::post('requestpembelian/ttd_setuju', 'RequestpembelianController@ttd_setuju')->name('requestpembelian.ttd_setuju');
    Route::post('requestpembelian/ttd_tahu', 'RequestpembelianController@ttd_tahu')->name('requestpembelian.ttd_tahu');
    Route::get('requestpembelian/exportpdf','RequestpembelianController@exportPDF')->name('requestpembelian.export');
    Route::get('requestpembelian/anydata', 'RequestpembelianController@anyData')->name('requestpembelian.data');
    Route::get('requestpembelian/limitos', 'RequestpembelianController@limitos')->name('requestpembelian.limitos');
    Route::get('requestpembelian/historia', 'RequestpembelianController@historia')->name('requestpembelian.historia');
    Route::post('requestpembelian/getkode', 'RequestpembelianController@getkode')->name('requestpembelian.getkode');
    Route::post('requestpembelian/get_ppn', 'RequestpembelianController@get_ppn')->name('requestpembelian.get_ppn');
    Route::post('requestpembelian/get_ppn2', 'RequestpembelianController@get_ppn2')->name('requestpembelian.get_ppn2');
    Route::post('requestpembelian/edit_request', 'RequestpembelianController@edit_request')->name('requestpembelian.edit_request');
    Route::post('requestpembelian/hapus_request', 'RequestpembelianController@hapus_request')->name('requestpembelian.hapus_request');
    Route::post('requestpembelian/void_request', 'RequestpembelianController@void_request')->name('requestpembelian.void_request');
    Route::post('requestpembelian/updateAjax', 'RequestpembelianController@updateAjax')->name('requestpembelian.updateajax');
    Route::get('requestpembelian/{requestpembelian}/detail', 'RequestpembelianController@detail')->name('requestpembelian.detail');
    Route::post('requestpembelian/closing', 'RequestpembelianController@closing')->name('requestpembelian.closing');
    Route::post('requestpembelian/Post', 'RequestpembelianController@Post')->name('requestpembelian.post');
    Route::post('requestpembelian/Unpost', 'RequestpembelianController@Unpost')->name('requestpembelian.unpost');
    Route::post('requestpembelian/Approve', 'RequestpembelianController@Approve')->name('requestpembelian.approve');
    Route::post('requestpembelian/Disapprove', 'RequestpembelianController@Disapprove')->name('requestpembelian.disapprove');
    Route::post('requestpembelian/showdetail', 'RequestpembelianController@Showdetail')->name('requestpembelian.showdetail');
    Route::resource('requestpembelian','RequestpembelianController');

    // /**
    //  * Request Detail
    //  */
    Route::post('requestpembeliandetail/stockproduk', 'RequestpembeliandetailController@stockProduk')->name('requestpembeliandetail.stockproduk');
    Route::post('requestpembeliandetail/satuankonversi', 'RequestpembeliandetailController@satuankonversi')->name('requestpembeliandetail.satuankonversi');
    Route::post('requestpembeliandetail/selectAjax/', 'RequestpembeliandetailController@selectAjax')->name('requestpembeliandetail.selectAjax');
    Route::get('requestpembeliandetail/getDatabyID', 'RequestpembeliandetailController@getDatabyID')->name('requestpembeliandetail.dataDetail');
    Route::get('requestpembeliandetail/getDatabyID2', 'RequestpembeliandetailController@getDatabyID2')->name('requestpembeliandetail.dataDetail2');
    Route::post('requestpembeliandetail/updateAjax', 'RequestpembeliandetailController@updateAjax')->name('requestpembeliandetail.updateajax');
    Route::resource('requestpembeliandetail', 'RequestpembeliandetailController');

    // /**
    //  * Master Lokasi
    //  */
    Route::get('masterlokasi/anydata', 'MasterLokasiController@anyData')->name('masterlokasi.data');
    Route::post('masterlokasi/updateAjax', 'MasterLokasiController@updateAjax')->name('masterlokasi.updateajax');
    Route::post('masterlokasi/hapus_lokasi', 'MasterLokasiController@hapus_lokasi')->name('masterlokasi.hapus_lokasi');
    Route::post('masterlokasi/edit_lokasi', 'MasterLokasiController@edit_lokasi')->name('masterlokasi.edit_lokasi');
    Route::get('masterlokasi/{pembelian}/detail', 'MasterLokasiController@detail')->name('masterlokasi.detail');
    Route::resource('masterlokasi','MasterLokasiController');

    // /**
    //  * Adjusment
    //  */
    Route::get('adjustment/exportpdf','AdjusmentController@exportPDF')->name('adjustment.export');
    Route::get('adjustment/exportpdf3','AdjusmentController@exportPDF3')->name('adjustment.export3');
    Route::get('adjustment/historia', 'AdjusmentController@historia')->name('adjustment.historia');
    Route::post('adjustment/getkode', 'AdjusmentController@getkode')->name('adjustment.getkode');
    Route::post('adjustment/showdetail', 'AdjusmentController@Showdetail')->name('adjustment.showdetail');
    Route::get('adjustment/anydata', 'AdjusmentController@anyData')->name('adjustment.data');
    Route::post('adjustment/edit_adjustment', 'AdjusmentController@edit_adjustment')->name('adjustment.edit_adjustment');
    Route::post('adjustment/hapus_adjustment', 'AdjusmentController@hapus_adjustment')->name('adjustment.hapus_adjustment');
    Route::post('adjustment/updateAdjusment', 'AdjusmentController@updateAdjusment')->name('adjustment.updateAdjusment');
    Route::get('adjustment/{adjustment}/detail', 'AdjusmentController@detail')->name('adjustment.detail');
    Route::post('adjustment/posting', 'AdjusmentController@posting')->name('adjustment.posting');
    Route::post('adjustment/unposting', 'AdjusmentController@unposting')->name('adjustment.unposting');
    Route::post('adjustment/cekjurnal2', 'AdjusmentController@cekjurnal2')->name('adjustment.cekjurnal2');
    Route::get('adjustment/getDatajurnal2', 'AdjusmentController@getDatajurnal2')->name('adjustment.getDatajurnal2');
    Route::resource('adjustment','AdjusmentController');

    // /**
    //  * Adjustment Detail
    //  */
    Route::post('adjustmentdetail/stockproduk', 'AdjusmentdetailController@stockProduk')->name('adjustmentdetail.stockproduk');
    Route::post('adjustmentdetail/getharga', 'AdjusmentdetailController@getharga')->name('adjustmentdetail.getharga');
    Route::post('adjustmentdetail/qtycheck', 'AdjusmentdetailController@qtycheck')->name('adjustmentdetail.qtycheck');
    Route::get('adjustmentdetail/getDatabyID', 'AdjusmentdetailController@getDatabyID')->name('adjustmentdetail.dataDetail');
    Route::post('adjustmentdetail/selectAjax/', 'AdjusmentdetailController@selectAjax')->name('adjustmentdetail.selectAjax');
    Route::post('adjustmentdetail/selectpart/', 'AdjusmentdetailController@selectpart')->name('adjustmentdetail.selectpart');
    Route::post('adjustmentdetail/updateAjax', 'AdjusmentdetailController@updateAjax')->name('adjustmentdetail.updateajax');
    Route::get('adjustmentdetail/detail', 'AdjusmentdetailController@detail')->name('adjustmentdetail.detail');
    Route::resource('adjustmentdetail', 'AdjusmentdetailController');

    //**
    //  * Laporan Pembelian
    //  */
    Route::get('laporanpembelian/exportpdf','LaporanpembelianController@exportPDF')->name('laporanpembelian.export');
    Route::resource('laporanpembelian', 'LaporanpembelianController');

    //**
    //  * Laporan Retur Pembelian
    //  */
    Route::get('laporanreturpembelian/exportpdf','LaporanreturpembelianController@exportPDF')->name('laporanreturpembelian.export');
    Route::resource('laporanreturpembelian', 'LaporanreturpembelianController');


    // /**
    //  * Laporan Pemakaian
    //  */
    Route::get('laporanpemakaian/exportpdf','LaporanpemakaianController@exportPDF')->name('laporanpemakaian.export');
    Route::resource('laporanpemakaian', 'LaporanpemakaianController');
    
    // /**
    //  * Laporan Maintenance Record
    //  */
    Route::get('laporanmaintenance/exportpdf','LaporanMaintenanceController@exportPDF')->name('laporanmaintenance.export');
    Route::resource('laporanmaintenance', 'LaporanMaintenanceController');

    // /**
    //  * Laporan Pemakaian QTY
    //  */
    Route::get('laporanpemakaianqty/exportpdf','LaporanpemakaianqtyController@exportPDF')->name('laporanpemakaianqty.export');
    Route::resource('laporanpemakaianqty', 'LaporanpemakaianqtyController');

    //**
    //  * Laporan Pemakaian Ban
    //  */
    Route::get('laporanpemakaianban/exportpdf','LaporanpemakaianbanController@exportPDF')->name('laporanpemakaianban.export');
    Route::resource('laporanpemakaianban', 'LaporanpemakaianbanController');

    // /**
    //  * Laporan Penjualan
    //  */
    Route::get('laporanpenjualan/exportpdf','LaporanpenjualanController@exportPDF')->name('laporanpenjualan.export');
    Route::resource('laporanpenjualan', 'LaporanpenjualanController');

    //**
    //  * Laporan Retur Penjualan
    //  */
    Route::get('laporanreturpenjualan/exportpdf','LaporanreturpenjualanController@exportPDF')->name('laporanreturpenjualan.export');
    Route::resource('laporanreturpenjualan', 'LaporanreturpenjualanController');

    // /**
    //  * Laporan TransferIn
    //  */
    Route::get('laporantransferin/exportpdf','LaporantransferinController@exportPDF')->name('laporantransferin.export');
    Route::resource('laporantransferin', 'LaporantransferinController');

    // /**
    //  * Laporan TransferOut
    //  */
    Route::get('laporantransferout/exportpdf','LaporantransferoutController@exportPDF')->name('laporantransferout.export');
    Route::resource('laporantransferout', 'LaporantransferoutController');
    
    // /**
    //  * Laporan Pemakaian Produk
    //  */
    Route::get('laporanpemakaianproduk/exportpdf','LaporanpemakaianprodukController@exportPDF')->name('laporanpemakaianproduk.export');
    Route::resource('laporanpemakaianproduk', 'LaporanpemakaianprodukController');

    // /**
    //  * Laporan Penerimaan
    //  */
    Route::get('laporanpenerimaan/exportpdf','LaporanpenerimaanController@exportPDF')->name('laporanpenerimaan.export');
    Route::resource('laporanpenerimaan', 'LaporanpenerimaanController');

    // /**
    //  * Laporan Adjustment
    //  */
    Route::get('laporanadjustment/exportpdf','LaporanadjustmentController@exportPDF')->name('laporanadjustment.export');
    Route::resource('laporanadjustment', 'LaporanadjustmentController');
    
    // /**
    //  * Laporan Opname
    //  */
    Route::get('laporanopname/exportpdf','LaporanopnameController@exportPDF')->name('laporanopname.export');
    Route::resource('laporanopname', 'LaporanopnameController');
    
    // /**
    //  * Laporan Produk
    //  */
    Route::get('laporanproduk/exportpdf','LaporanprodukController@exportPDF')->name('laporanproduk.export');
    Route::resource('laporanproduk', 'LaporanprodukController');

    // /**
    //  * Laporan Produk Bulanan
    //  */
    Route::get('laporanprodukbulanan/exportpdf','LaporanprodukbulananController@exportPDF')->name('laporanprodukbulanan.export');
    Route::resource('laporanprodukbulanan', 'LaporanprodukbulananController');

    /**
     * Alat
     */
    Route::get('alat/anydata', 'AlatController@anyData')->name('alat.data');
    Route::post('alat/updateAjax', 'AlatController@updateAjax')->name('alat.ajaxupdate');
    Route::post('alat/hapus_alat', 'AlatController@hapus_alat')->name('alat.hapus_alat');
    Route::post('alat/edit_alat', 'AlatController@edit_alat')->name('alat.edit_alat');
    Route::get('alat/getDatabyID', 'AlatController@getDatabyID')->name('alat.dataDetail');
    Route::get('alat/exportexcel','AlatController@exportexcel')->name('alat.exportexcel');
    Route::get('alat/{kode}/detaillokasi', 'AlatController@detaillokasi')->name('alat.detaillokasi');
    Route::post('alat/store_lokasi', 'AlatController@store_lokasi')->name('alat.store_lokasi');
    Route::resource('alat', 'AlatController');

    /**
     * Kapal
     */
    Route::get('kapal/anydata', 'KapalController@anyData')->name('kapal.data');
    Route::post('kapal/updateAjax', 'KapalController@updateAjax')->name('kapal.ajaxupdate');
    Route::post('kapal/hapus_kapal', 'KapalController@hapus_kapal')->name('kapal.hapus_kapal');
    Route::post('kapal/edit_kapal', 'KapalController@edit_kapal')->name('kapal.edit_kapal');
    Route::get('kapal/getDatabyID', 'KapalController@getDatabyID')->name('kapal.dataDetail');
    Route::get('kapal/exportexcel','KapalController@exportexcel')->name('kapal.exportexcel');
    Route::get('kapal/{kode}/detaillokasi', 'KapalController@detaillokasi')->name('kapal.detaillokasi');
    Route::post('kapal/store_lokasi', 'KapalController@store_lokasi')->name('kapal.store_lokasi');
    Route::resource('kapal', 'KapalController');

    /**
     * Jenis
     */
    Route::get('jenis/anydata', 'JenisController@anyData')->name('jenis.data');
    Route::post('jenis/updateAjax', 'JenisController@updateAjax')->name('jenis.ajaxupdate');
    Route::resource('jenis', 'JenisController');
    
    /**
     * Jasa
     */
    Route::get('jasa/anydata', 'JasaController@anyData')->name('jasa.data');
    Route::post('jasa/updateAjax', 'JasaController@updateAjax')->name('jasa.ajaxupdate');
    Route::post('jasa/hapus_jasa', 'JasaController@hapus_jasa')->name('jasa.hapus_jasa');
    Route::post('jasa/edit_jasa', 'JasaController@edit_jasa')->name('jasa.edit_jasa');
    Route::resource('jasa', 'JasaController');

    /**
     * Non-Stock
     */
    Route::get('nonstock/anydata', 'NonstockController@anyData')->name('nonstock.data');
    Route::post('nonstock/updateAjax', 'NonstockController@updateAjax')->name('nonstock.ajaxupdate');
    Route::post('nonstock/hapus_nonstock', 'NonstockController@hapus_nonstock')->name('nonstock.hapus_nonstock');
    Route::post('nonstock/edit_nonstock', 'NonstockController@edit_nonstock')->name('nonstock.edit_nonstock');
    Route::resource('nonstock', 'NonstockController');

    /**
     * Opname
     */
    Route::get('opname/exportpdf','OpnameController@exportPDF')->name('opname.export');
    Route::get('opname/exportpdf3','OpnameController@exportPDF3')->name('opname.export3');
    Route::get('opname/anydata', 'OpnameController@anyData')->name('opname.data');
    Route::get('opname/historia', 'OpnameController@historia')->name('opname.historia');
    Route::post('opname/getkode', 'OpnameController@getkode')->name('opname.getkode');
    Route::post('opname/edit_opname', 'OpnameController@edit_opname')->name('opname.edit_opname');
    Route::post('opname/hapus_opname', 'OpnameController@hapus_opname')->name('opname.hapus_opname');
    Route::post('opname/showdetail', 'OpnameController@Showdetail')->name('opname.showdetail');
    Route::post('opname/updateAjax', 'OpnameController@updateAjax')->name('opname.updateajax');
    Route::get('opname/{opname}/detail', 'OpnameController@detail')->name('opname.detail');
    Route::post('opname/posting', 'OpnameController@posting')->name('opname.posting');
    Route::post('opname/unposting', 'OpnameController@unposting')->name('opname.unposting');
    Route::post('opname/cekjurnal2', 'OpnameController@cekjurnal2')->name('opname.cekjurnal2');
    Route::get('opname/getDatajurnal2', 'OpnameController@getDatajurnal2')->name('opname.getDatajurnal2');
    Route::resource('opname','OpnameController');

     // /**
    //  * Opname Detail
    //  */
    Route::get('opnamedetail/exportpdf','OpnamedetailController@exportPDF')->name('opnamedetail.export');
    Route::post('opnamedetail/getdata', 'OpnamedetailController@getdata')->name('opnamedetail.getdata');
    Route::post('opnamedetail/stockproduk', 'OpnamedetailController@stockProduk')->name('opnamedetail.stockproduk');
    Route::post('opnamedetail/satuanproduk', 'OpnamedetailController@satuanproduk')->name('opnamedetail.satuanproduk');
    Route::post('opnamedetail/createall', 'OpnamedetailController@createall')->name('opnamedetail.createall');
    Route::post('opnamedetail/hitungselisih', 'OpnamedetailController@hitungselisih')->name('opnamedetail.hitungselisih');
    Route::post('opnamedetail/hapusdetail', 'OpnamedetailController@hapusdetail')->name('opnamedetail.hapusdetail');
    Route::post('opnamedetail/hapusitem', 'OpnamedetailController@hapusitem')->name('opnamedetail.hapusitem');
    Route::get('opnamedetail/getDatabyID', 'OpnamedetailController@getDatabyID')->name('opnamedetail.dataDetail');
    Route::post('opnamedetail/selectpart/', 'OpnamedetailController@selectpart')->name('opnamedetail.selectpart');
    Route::post('opnamedetail/getharga', 'OpnamedetailController@getharga')->name('opnamedetail.getharga');
    Route::post('opnamedetail/updateAjax', 'OpnamedetailController@updateAjax')->name('opnamedetail.updateajax');
    Route::resource('opnamedetail', 'OpnamedetailController');

     /**
     * Retur Pembelian
     */
    Route::get('returpembelian/exportpdf','ReturpembelianController@exportPDF')->name('returpembelian.export');
    Route::get('returpembelian/exportpdf2','ReturpembelianController@exportPDF2')->name('returpembelian.export2');
    Route::get('returpembelian/anydata', 'ReturpembelianController@anyData')->name('returpembelian.data');
    Route::get('returpembelian/historia', 'ReturpembelianController@historia')->name('returpembelian.historia');
    Route::post('returpembelian/cekjurnal2', 'ReturpembelianController@cekjurnal2')->name('returpembelian.cekjurnal2');
    Route::get('returpembelian/getDatajurnal2', 'ReturpembelianController@getDatajurnal2')->name('returpembelian.getDatajurnal2');
    Route::post('returpembelian/getkode', 'ReturpembelianController@getkode')->name('returpembelian.getkode');
    Route::post('returpembelian/getpo', 'ReturpembelianController@getpo')->name('returpembelian.getpo');
    Route::post('returpembelian/getpo1', 'ReturpembelianController@getpo1')->name('returpembelian.getpo1');
    Route::post('returpembelian/getkode', 'ReturpembelianController@getkode')->name('returpembelian.getkode');
    Route::post('returpembelian/edit_returpembelian', 'ReturpembelianController@edit_returpembelian')->name('returpembelian.edit_returpembelian');
    Route::post('returpembelian/hapus_returpembelian', 'ReturpembelianController@hapus_returpembelian')->name('returpembelian.hapus_returpembelian');
    Route::post('returpembelian/updateAjax', 'ReturpembelianController@updateAjax')->name('returpembelian.updateajax');
    Route::get('returpembelian/{returpembelian}/detail', 'ReturpembelianController@detail')->name('returpembelian.detail');
    Route::post('returpembelian/posting', 'ReturpembelianController@posting')->name('returpembelian.posting');
    Route::post('returpembelian/unposting', 'ReturpembelianController@unposting')->name('returpembelian.unposting');
    Route::post('returpembelian/showdetail', 'ReturpembelianController@Showdetail')->name('returpembelian.showdetail');
    Route::resource('returpembelian', 'ReturpembelianController');

    // /**
    //  * Retur Pembelian Detail
    //  */
    Route::post('returpembeliandetail/getinfo', 'ReturpembelianDetailController@getinfo')->name('returpembeliandetail.getinfo');
    Route::post('returpembeliandetail/getstock', 'ReturpembelianDetailController@getstock')->name('returpembeliandetail.getstock');
    Route::post('returpembeliandetail/qtyproduk', 'ReturpembelianDetailController@qtyProduk')->name('returpembeliandetail.qtyproduk');
    Route::get('returpembeliandetail/getDatabyID', 'ReturpembelianDetailController@getDatabyID')->name('returpembeliandetail.dataDetail');
    Route::post('returpembeliandetail/updateAjax', 'ReturpembelianDetailController@updateAjax')->name('returpembeliandetail.updateajax');
    Route::get('returpembeliandetail/detail', 'ReturpembelianDetailController@detail')->name('returpembeliandetail.detail');
    Route::resource('returpembeliandetail', 'ReturpembelianDetailController');
    
    
    /**
     * Retur Pemakaian
     */
    Route::get('returpemakaian/exportpdf','ReturpemakaianController@exportPDF')->name('returpemakaian.export');
    Route::get('returpemakaian/exportpdf2','ReturpemakaianController@exportPDF2')->name('returpemakaian.export2');
    Route::get('returpemakaian/anydata', 'ReturpemakaianController@anyData')->name('returpemakaian.data');
    Route::get('returpemakaian/historia', 'ReturpemakaianController@historia')->name('returpemakaian.historia');
    Route::post('returpemakaian/cekjurnal2', 'ReturpemakaianController@cekjurnal2')->name('returpemakaian.cekjurnal2');
    Route::get('returpemakaian/getDatajurnal2', 'ReturpemakaianController@getDatajurnal2')->name('returpemakaian.getDatajurnal2');
    Route::post('returpemakaian/getkode', 'ReturpemakaianController@getkode')->name('returpemakaian.getkode');
    Route::post('returpemakaian/getpo', 'ReturpemakaianController@getpo')->name('returpemakaian.getpo');
    Route::post('returpemakaian/getpo1', 'ReturpemakaianController@getpo1')->name('returpemakaian.getpo1');
    Route::post('returpemakaian/getkode', 'ReturpemakaianController@getkode')->name('returpemakaian.getkode');
    Route::post('returpemakaian/edit_returpemakaian', 'ReturpemakaianController@edit_returpemakaian')->name('returpemakaian.edit_returpemakaian');
    Route::post('returpemakaian/hapus_returpemakaian', 'ReturpemakaianController@hapus_returpemakaian')->name('returpemakaian.hapus_returpemakaian');
    Route::post('returpemakaian/updateAjax', 'ReturpemakaianController@updateAjax')->name('returpemakaian.updateajax');
    Route::get('returpemakaian/{returpembelian}/detail', 'ReturpemakaianController@detail')->name('returpemakaian.detail');
    Route::post('returpemakaian/posting', 'ReturpemakaianController@posting')->name('returpemakaian.posting');
    Route::post('returpemakaian/unposting', 'ReturpemakaianController@unposting')->name('returpemakaian.unposting');
    Route::post('returpemakaian/showdetail', 'ReturpemakaianController@Showdetail')->name('returpemakaian.showdetail');
    Route::resource('returpemakaian', 'ReturpemakaianController');

    // /**
    //  * Retur Pemakaian Detail
    //  */
    Route::post('returpemakaiandetail/getinfo', 'ReturpemakaianDetailController@getinfo')->name('returpemakaiandetail.getinfo');
    Route::post('returpemakaiandetail/getstock', 'ReturpemakaianDetailController@getstock')->name('returpemakaiandetail.getstock');
    Route::post('returpemakaiandetail/qtyproduk', 'ReturpemakaianDetailController@qtyProduk')->name('returpemakaiandetail.qtyproduk');
    Route::get('returpemakaiandetail/getDatabyID', 'ReturpemakaianDetailController@getDatabyID')->name('returpemakaiandetail.dataDetail');
    Route::post('returpemakaiandetail/updateAjax', 'ReturpemakaianDetailController@updateAjax')->name('returpemakaiandetail.updateajax');
    Route::get('returpemakaiandetail/detail', 'ReturpemakaianDetailController@detail')->name('returpemakaiandetail.detail');
    Route::resource('returpemakaiandetail', 'ReturpemakaianDetailController');


    /**
     * Retur Penjualan
     */
    Route::get('returjual/exportpdf','ReturpenjualanController@exportPDF')->name('returjual.export');
    Route::post('returjual/getkode', 'ReturpenjualanController@getkode')->name('returjual.getkode');
    Route::post('returjual/getcustomer', 'ReturpenjualanController@getcustomer')->name('returjual.getcustomer');
    Route::post('returjual/getcustomer2', 'ReturpenjualanController@getcustomer2')->name('returjual.getcustomer2');
    Route::get('returjual/anydata', 'ReturpenjualanController@anyData')->name('returjual.data');
    Route::post('returjual/edit_retur_jual', 'ReturpenjualanController@edit_retur_jual')->name('returjual.edit_retur_jual');
    Route::post('returjual/hapus_penjualan', 'ReturpenjualanController@hapus_penjualan')->name('returjual.hapus_penjualan');
    Route::post('returjual/updateAjax', 'ReturpenjualanController@updateAjax')->name('returjual.updateajax');
    Route::get('returjual/{returpenjualan}/detail', 'ReturpenjualanController@detail')->name('returjual.detail');
    Route::post('returjual/posting', 'ReturpenjualanController@posting')->name('returjual.posting');
    Route::post('returjual/unposting', 'ReturpenjualanController@unposting')->name('returjual.unposting');
    Route::post('returjual/showdetail', 'ReturpenjualanController@Showdetail')->name('returjual.showdetail');
    Route::resource('returjual', 'ReturpenjualanController');

    // /**
    //  * Retur Penjualan Detail
    //  */
    Route::post('returjualdetail/stockproduk', 'ReturpenjualanDetailController@stockProduk')->name('returjualdetail.stockproduk');
    Route::post('returjualdetail/getstock', 'ReturpenjualanDetailController@getstock')->name('returjualdetail.getstock');
    Route::post('returjualdetail/qtyproduk2', 'ReturpenjualanDetailController@qtyProduk2')->name('returjualdetail.qtyproduk2');
    Route::post('returjualdetail/qtycheck', 'ReturpenjualanDetailController@qtycheck')->name('returjualdetail.qtycheck');
    Route::post('returjualdetail/qtycheck2', 'ReturpenjualanDetailController@qtycheck2')->name('returjualdetail.qtycheck2');
    Route::get('returjualdetail/getDatabyID', 'ReturpenjualanDetailController@getDatabyID')->name('returjualdetail.dataDetail');
    Route::post('returjualdetail/updateAjax', 'ReturpenjualanDetailController@updateAjax')->name('returjualdetail.updateajax');
    Route::post('returjualdetail/check', 'ReturpenjualanDetailController@check')->name('returjualdetail.check');
    Route::resource('returjualdetail', 'ReturpenjualanDetailController');

    /**
     * Signature
     */
    Route::get('signature/anydata', 'SignatureController@anyData')->name('signature.data');
    Route::post('signature/updateAjax', 'SignatureController@updateAjax')->name('signature.ajaxupdate');
    Route::post('signature/hapus_signature', 'SignatureController@hapus_signature')->name('signature.hapus_signature');
    Route::post('signature/edit_signature', 'SignatureController@edit_signature')->name('signature.edit_signature');
    Route::resource('signature', 'SignatureController');

    /**
     * Catatan PO
     */
    Route::get('catatanpo/anydata', 'CatatanpoController@anyData')->name('catatanpo.data');
    Route::post('catatanpo/updateAjax', 'CatatanpoController@updateAjax')->name('catatanpo.ajaxupdate');
    Route::post('catatanpo/hapus_catatanpo', 'CatatanpoController@hapus_catatanpo')->name('catatanpo.hapus_catatanpo');
    Route::post('catatanpo/edit_catatanpo', 'CatatanpoController@edit_catatanpo')->name('catatanpo.edit_catatanpo');
    Route::resource('catatanpo', 'CatatanpoController');

    /**
     * Jenis Mobil
     */
    Route::get('jenismobil/anydata', 'JenismobilController@anyData')->name('jenismobil.data');
    Route::post('jenismobil/updateAjax', 'JenismobilController@updateAjax')->name('jenismobil.ajaxupdate');
    Route::post('jenismobil/hapus_jenismobil', 'JenismobilController@hapus_jenismobil')->name('jenismobil.hapus_jenismobil');
    Route::post('jenismobil/edit_jenismobil', 'JenismobilController@edit_jenismobil')->name('jenismobil.edit_jenismobil');
    Route::resource('jenismobil', 'JenismobilController');

    /**
     * Mobil
     */
    Route::get('mobil/anydata', 'MobilController@anyData')->name('mobil.data');
    Route::post('mobil/getkode', 'MobilController@getkode')->name('mobil.getkode');
    Route::post('mobil/updateAjax', 'MobilController@updateAjax')->name('mobil.ajaxupdate');
    Route::post('mobil/hapus_mobil', 'MobilController@hapus_mobil')->name('mobil.hapus_mobil');
    Route::post('mobil/edit_mobil', 'MobilController@edit_mobil')->name('mobil.edit_mobil');
    Route::get('mobil/exportexcel','MobilController@exportexcel')->name('mobil.exportexcel');
    Route::get('mobil/getDatabyID', 'MobilController@getDatabyID')->name('mobil.dataDetail');
    Route::get('mobil/{kode}/detaillokasi', 'MobilController@detaillokasi')->name('mobil.detaillokasi');
    Route::post('mobil/store_lokasi', 'MobilController@store_lokasi')->name('mobil.store_lokasi');
    Route::resource('mobil', 'MobilController');
    
    /**
     * Setup Akses Transaksi
     */
    Route::get('setupakses/anydata', 'SetupaksesController@anyData')->name('setupakses.data');
    Route::post('setupakses/updateAjax', 'SetupaksesController@updateAjax')->name('setupakses.ajaxupdate');
    Route::post('setupakses/hapus_bank', 'SetupaksesController@hapus_bank')->name('setupakses.hapus_bank');
    Route::post('setupakses/edit_bank', 'SetupaksesController@edit_bank')->name('setupakses.edit_bank');
    Route::resource('setupakses', 'SetupaksesController');
    
    /**
     * Setup Folder
     */
    Route::get('setupfolder/anydata', 'SetupfolderController@anyData')->name('setupfolder.data');
    Route::post('setupfolder/updateAjax', 'SetupfolderController@updateAjax')->name('setupfolder.ajaxupdate');
    Route::post('setupfolder/hapus_bank', 'SetupfolderController@hapus_bank')->name('setupfolder.hapus_bank');
    Route::post('setupfolder/edit_bank', 'SetupfolderController@edit_bank')->name('setupfolder.edit_bank');
    Route::resource('setupfolder', 'SetupfolderController');

    /**
     * Transaksi Setup
     */
    Route::get('transaksisetup/anydata', 'TransaksisetupController@anyData')->name('transaksisetup.data');
    Route::post('transaksisetup/updateAjax', 'TransaksisetupController@updateAjax')->name('transaksisetup.ajaxupdate');
    Route::post('transaksisetup/hapus_transaksisetup', 'TransaksisetupController@hapus_transaksisetup')->name('transaksisetup.hapus_transaksisetup');
    Route::post('transaksisetup/edit_transaksisetup', 'TransaksisetupController@edit_transaksisetup')->name('transaksisetup.edit_transaksisetup');
    Route::resource('transaksisetup', 'TransaksisetupController');

    /**
     * Tax Setup
     */
    Route::get('taxsetup/anydata', 'TaxSetupController@anyData')->name('taxsetup.data');
    Route::post('taxsetup/updateAjax', 'TaxSetupController@updateAjax')->name('taxsetup.ajaxupdate');
    Route::post('taxsetup/hapus_taxsetup', 'TaxSetupController@hapus_taxsetup')->name('taxsetup.hapus_taxsetup');
    Route::post('taxsetup/edit_taxsetup', 'TaxSetupController@edit_taxsetup')->name('taxsetup.edit_taxsetup');
    Route::resource('taxsetup', 'TaxSetupController');
    
    /**
     * Recalculate Pemakaian
     */
    Route::post('recalculatepemakaian/change', 'RecalculatepemakaianController@change')->name('recalculatepemakaian.change');
    Route::get('recalculatepemakaian/anydata', 'RecalculatepemakaianController@anyData')->name('recalculatepemakaian.data');
    Route::resource('recalculatepemakaian', 'RecalculatepemakaianController');

    // /**
    //  * disassembling
    //  */
    Route::post('disassembling/getharga', 'DisassemblingController@getharga')->name('disassembling.getharga');
    Route::post('disassembling/qtyproduk2', 'DisassemblingController@qtyProduk2')->name('disassembling.qtyproduk2');
    Route::post('disassembling/selectpart/', 'DisassemblingController@selectpart')->name('disassembling.selectpart');
    Route::get('disassembling/exportpdf','DisassemblingController@exportPDF')->name('disassembling.export');
    Route::get('disassembling/exportpdf3','DisassemblingController@exportPDF3')->name('disassembling.export3');
    Route::post('disassembling/showdetail', 'DisassemblingController@Showdetail')->name('disassembling.showdetail');
    Route::get('disassembling/anydata', 'DisassemblingController@anyData')->name('disassembling.data');
    Route::post('disassembling/edit_disassembling', 'DisassemblingController@edit_disassembling')->name('disassembling.edit_disassembling');
    Route::post('disassembling/hapus_disassembling', 'DisassemblingController@hapus_disassembling')->name('disassembling.hapus_disassembling');
    Route::post('disassembling/updateAjax', 'DisassemblingController@updateAjax')->name('disassembling.updateAjax');
    Route::get('disassembling/{disassembling}/detail', 'DisassemblingController@detail')->name('disassembling.detail');
    Route::post('disassembling/posting', 'DisassemblingController@posting')->name('disassembling.posting');
    Route::post('disassembling/unposting', 'DisassemblingController@unposting')->name('disassembling.unposting');
    Route::post('disassembling/cekjurnal2', 'DisassemblingController@cekjurnal2')->name('disassembling.cekjurnal2');
    Route::get('disassembling/getDatajurnal2', 'DisassemblingController@getDatajurnal2')->name('disassembling.getDatajurnal2');
    Route::resource('disassembling','DisassemblingController');

    // /**
    //  * disassembling Detail
    //  */
    Route::post('disassemblingdetail/getharga', 'DisassemblingdetailController@getharga')->name('disassemblingdetail.getharga');
    Route::post('disassemblingdetail/selectpart/', 'DisassemblingdetailController@selectpart')->name('disassemblingdetail.selectpart');
    Route::get('disassemblingdetail/getDatabyID', 'DisassemblingdetailController@getDatabyID')->name('disassemblingdetail.dataDetail');
    Route::post('disassemblingdetail/updateAjax', 'DisassemblingdetailController@updateAjax')->name('disassemblingdetail.updateajax');
    Route::get('disassemblingdetail/detail', 'DisassemblingdetailController@detail')->name('disassemblingdetail.detail');
    Route::resource('disassemblingdetail', 'DisassemblingdetailController');


    // /**
    //  * Assembling
    //  */
    Route::post('assembling/getharga', 'AssemblingController@getharga')->name('assembling.getharga');
    Route::post('assembling/selectpart/', 'AssemblingController@selectpart')->name('assembling.selectpart');
    Route::get('assembling/exportpdf','AssemblingController@exportPDF')->name('assembling.export');
    Route::get('assembling/exportpdf3','AssemblingController@exportPDF3')->name('assembling.export3');
    Route::post('assembling/showdetail', 'AssemblingController@Showdetail')->name('assembling.showdetail');
    Route::get('assembling/anydata', 'AssemblingController@anyData')->name('assembling.data');
    Route::post('assembling/edit_assembling', 'AssemblingController@edit_assembling')->name('assembling.edit_assembling');
    Route::post('assembling/hapus_assembling', 'AssemblingController@hapus_assembling')->name('assembling.hapus_assembling');
    Route::post('assembling/updateAjax', 'AssemblingController@updateAjax')->name('assembling.updateAjax');
    Route::get('assembling/{assembling}/detail', 'AssemblingController@detail')->name('assembling.detail');
    Route::post('assembling/posting', 'AssemblingController@posting')->name('assembling.posting');
    Route::post('assembling/unposting', 'AssemblingController@unposting')->name('assembling.unposting');
    Route::post('assembling/cekjurnal2', 'AssemblingController@cekjurnal2')->name('assembling.cekjurnal2');
    Route::get('assembling/getDatajurnal2', 'AssemblingController@getDatajurnal2')->name('assembling.getDatajurnal2');
    Route::resource('assembling','AssemblingController');

    // /**
    //  * Assembling Detail
    //  */
    Route::post('assemblingdetail/qtyproduk2', 'AssemblingdetailController@qtyProduk2')->name('assemblingdetail.qtyproduk2');
    Route::post('assemblingdetail/getharga', 'AssemblingdetailController@getharga')->name('assemblingdetail.getharga');
    Route::post('assemblingdetail/selectpart/', 'AssemblingdetailController@selectpart')->name('assemblingdetail.selectpart');
    Route::get('assemblingdetail/getDatabyID', 'AssemblingdetailController@getDatabyID')->name('assemblingdetail.dataDetail');
    Route::post('assemblingdetail/updateAjax', 'AssemblingdetailController@updateAjax')->name('assemblingdetail.updateajax');
    Route::get('assemblingdetail/detail', 'AssemblingdetailController@detail')->name('assemblingdetail.detail');
    Route::resource('assemblingdetail', 'AssemblingdetailController');

     /**
     * Users
     */
    Route::resource('users', 'UsersController');

    /*
     * Roles
     */
    Route::resource('roles', 'RolesController');

    /*
    * Permissions
    */
    Route::resource('permissions', 'PermissionsController');

});