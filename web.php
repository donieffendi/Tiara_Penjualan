<?php

use Illuminate\Support\Facades\Mail;
use App\Mail\SendEmail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OTransaksi\Prbn8Controller;
use App\Http\Controllers\OTransaksi\SpoxController;

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



// Dashboard
Route::get('/', 'App\Http\Controllers\DashboardController@index')->middleware(['auth']);

// Route::get('/dashboard', 'App\Http\Controllers\DashboardController@dashboard_plain')->middleware(['auth']);
// // Chart Dashboard
// Route::get('/chart', 'App\Http\Controllers\DashboardController@chart')->middleware(['auth']);


// Route::get('/', function () {
//     return view('dashboard');
// })->middleware(['auth'])->name('dashboard');

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth'])->name('dashboard');

// Static Route

// Periode
Route::post('/periode', 'App\Http\Controllers\PeriodeController@index')->middleware(['auth'])->name('periode');

//User Edit

Route::get('/profile', 'App\Http\Controllers\ProfileController@index')->middleware(['auth']);
Route::post('/profile/update', 'App\Http\Controllers\ProfileController@update')->middleware(['auth']);
Route::post('/profile/setting/update', 'App\Http\Controllers\ProfileController@updateSetting')->middleware(['auth']);


///////////////////////

// Operational Transaksi Orderk
Route::get('/orderk', 'App\Http\Controllers\OTransaksi\OrderkController@index')->middleware(['auth'])->name('orderk');
Route::post('/orderk/store', 'App\Http\Controllers\OTransaksi\OrderkController@store')->middleware(['auth'])->name('orderk/store');
Route::get('/orderk/create', 'App\Http\Controllers\OTransaksi\OrderkController@create')->middleware(['auth'])->name('orderk/create');
Route::get('/get-orderk', 'App\Http\Controllers\OTransaksi\OrderkController@getOrderk')->middleware(['auth'])->name('get-orderk');
Route::get('/rorderk', 'App\Http\Controllers\OReport\ROrderkController@report')->middleware(['auth'])->name('rorderk');
Route::get('/get-orderk-report', 'App\Http\Controllers\OReport\ROderkController@getOrderkReport')->middleware(['auth'])->name('get-orderk-report');

Route::get('/orderk/show/{orderk}', 'App\Http\Controllers\OTransaksi\OrderkController@show')->name('orderkid');
Route::get('/orderk/edit', 'App\Http\Controllers\OTransaksi\OrderkController@edit')->name('orderk.edit');
Route::post('/orderk/update/{orderk}', 'App\Http\Controllers\OTransaksi\OrderkController@update')->name('orderk.update');
Route::get('/orderk/delete/{orderk}', 'App\Http\Controllers\OTransaksi\OrderkController@destroy')->name('orderk.delete');

Route::get('/orderk/browseSo', 'App\Http\Controllers\OTransaksi\OrderkController@browseSo')->middleware(['auth']);
Route::get('/orderk/browse', 'App\Http\Controllers\OTransaksi\OrderkController@browse')->middleware(['auth']);
Route::get('/orderk/browse_detail', 'App\Http\Controllers\OTransaksi\OrderkController@browse_detail')->middleware(['auth']);
Route::get('/orderk/browseFo', 'App\Http\Controllers\OTransaksi\OrderkController@browseFo')->middleware(['auth']);
Route::get('/orderk/browseFod', 'App\Http\Controllers\OTransaksi\OrderkController@browseFod')->middleware(['auth']);
Route::get('/orderk/browseFodPrs', 'App\Http\Controllers\OTransaksi\OrderkController@browseFodPrs')->middleware(['auth']);
Route::get('/orderk/browseOrderkxd', 'App\Http\Controllers\OTransaksi\OrderkController@browseOrderkxd')->middleware(['auth']);
Route::get('/orderk/browsePakai', 'App\Http\Controllers\OTransaksi\OrderkController@browsePakai')->middleware(['auth']);

Route::post('/jasper-orderk-report', 'App\Http\Controllers\OReport\ROrderkController@jasperOrderkReport')->middleware(['auth']);
Route::get('/jsorderkc/{orderk:NO_ID}', 'App\Http\Controllers\OTransaksi\OrderkController@jsorderkc')->middleware(['auth']);


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Master Brg
Route::get('/brg', 'App\Http\Controllers\Master\BrgController@index')->middleware(['auth'])->name('brg');
Route::post('/brg/store', 'App\Http\Controllers\Master\BrgController@store')->middleware(['auth'])->name('brg/store');
    // GET brg
    Route::get('/get-brg', 'App\Http\Controllers\Master\BrgController@getBrg')->middleware(['auth'])->name('get-brg');
    
// Dynamic Brg

Route::get('/brg/edit', 'App\Http\Controllers\Master\BrgController@edit')->middleware(['auth'])->name('brg.edit');
Route::post('/brg/update/{brg}', 'App\Http\Controllers\Master\BrgController@update')->middleware(['auth'])->name('brg.update');
Route::get('/brg/delete/{brg}', 'App\Http\Controllers\Master\BrgController@destroy')->middleware(['auth'])->name('brg.delete');

///////////////////////

// Master Brg Baru
Route::get('/brg-baru', 'App\Http\Controllers\Master\BrgBaruController@index')->middleware(['auth'])->name('brg-baru');
Route::post('/brg-baru/store', 'App\Http\Controllers\Master\BrgBaruController@store')->middleware(['auth'])->name('brg-baru/store');
    // GET brg
    Route::get('/get-brg-baru', 'App\Http\Controllers\Master\BrgBaruController@getBrgBaru')->middleware(['auth'])->name('get-brg-baru');
    
// Dynamic Brg

Route::get('/brg-baru/edit', 'App\Http\Controllers\Master\BrgBaruController@edit')->middleware(['auth'])->name('brg-baru.edit');
Route::post('/brg-baru/update/{brg}', 'App\Http\Controllers\Master\BrgBaruController@update')->middleware(['auth'])->name('brg-baru.update');
Route::get('/brg-baru/delete/{brg}', 'App\Http\Controllers\Master\BrgBaruController@destroy')->middleware(['auth'])->name('brg-baru.delete');

///////////////////////

// Master Customer
Route::get('/cust', 'App\Http\Controllers\Master\CustomerController@index')->middleware(['auth'])->name('cust');
Route::post('/cust/store', 'App\Http\Controllers\Master\CustomerController@store')->middleware(['auth'])->name('cust/store');
    // GET Customer
    Route::get('/get-cust', 'App\Http\Controllers\Master\CustomerController@getCust')->middleware(['auth'])->name('get-cust');
    
// Dynamic Customer

Route::get('/cust/edit', 'App\Http\Controllers\Master\CustomerController@edit')->middleware(['auth'])->name('cust.edit');
Route::post('/cust/update/{cust}', 'App\Http\Controllers\Master\CustomerController@update')->middleware(['auth'])->name('cust.update');
Route::get('/cust/delete/{cust}', 'App\Http\Controllers\Master\CustomerController@destroy')->middleware(['auth'])->name('cust.delete');

///////////////////////

// Master Daftar Bank Pembayran
Route::get('/bank-byr', 'App\Http\Controllers\Master\BankBayarController@index')->middleware(['auth'])->name('cust');
Route::post('/bank-byr/store', 'App\Http\Controllers\Master\BankBayarController@store')->middleware(['auth'])->name('bank-byr/store');
    // GET Daftar Bank Pembayran
    Route::get('/get-bank', 'App\Http\Controllers\Master\BankBayarController@getBank')->middleware(['auth'])->name('get-bank');
    
// Dynamic Daftar Bank Pembayran

Route::get('/bank-byr/edit', 'App\Http\Controllers\Master\BankBayarController@edit')->middleware(['auth'])->name('bank-byr.edit');
Route::post('/bank-byr/update/{masbank}', 'App\Http\Controllers\Master\BankBayarController@update')->middleware(['auth'])->name('bank-byr.update');
Route::get('/bank-byr/delete/{masbank}', 'App\Http\Controllers\Master\BankBayarController@destroy')->middleware(['auth'])->name('bank-byr.delete');

///////////////////////

// Master Daftar Hari Raya
Route::get('/hraya', 'App\Http\Controllers\Master\HariRayaController@index')->middleware(['auth'])->name('hraya');
Route::post('/hraya/store', 'App\Http\Controllers\Master\HariRayaController@store')->middleware(['auth'])->name('hraya/store');
    // GET Daftar Hari Raya
    Route::get('/get-hraya', 'App\Http\Controllers\Master\HariRayaController@getHariRaya')->middleware(['auth'])->name('get-hraya');
    
// Dynamic Daftar Hari Raya

Route::get('/hraya/edit', 'App\Http\Controllers\Master\HariRayaController@edit')->middleware(['auth'])->name('hraya.edit');
Route::post('/hraya/update/{hraya}', 'App\Http\Controllers\Master\HariRayaController@update')->middleware(['auth'])->name('hraya.update');
Route::get('/hraya/delete/{hraya}', 'App\Http\Controllers\Master\HariRayaController@destroy')->middleware(['auth'])->name('hraya.delete');

///////////////////////

// Master Daftar Komisi
Route::get('/komisi', 'App\Http\Controllers\Master\KomisiController@index')->middleware(['auth'])->name('komisi');
Route::post('/komisi/store', 'App\Http\Controllers\Master\KomisiController@store')->middleware(['auth'])->name('komisi/store');
    // GET Daftar Daftar Komisi
    Route::get('/get-komisi', 'App\Http\Controllers\Master\KomisiController@getKomisi')->middleware(['auth'])->name('get-komisi');
    
// Dynamic Daftar Daftar Komisi

Route::get('/komisi/edit', 'App\Http\Controllers\Master\KomisiController@edit')->middleware(['auth'])->name('komisi.edit');
Route::post('/komisi/update/{komisi}', 'App\Http\Controllers\Master\KomisiController@update')->middleware(['auth'])->name('komisi.update');
Route::get('/komisi/delete/{komisi}', 'App\Http\Controllers\Master\KomisiController@destroy')->middleware(['auth'])->name('komisi.delete');

///////////////////////

// Master Daftar Suplier
Route::get('/sup', 'App\Http\Controllers\Master\SuplierController@index')->middleware(['auth'])->name('sup');
Route::post('/sup/store', 'App\Http\Controllers\Master\SuplierController@store')->middleware(['auth'])->name('sup/store');
    // GET Daftar Suplier
    Route::get('/get-sup', 'App\Http\Controllers\Master\SuplierController@getSup')->middleware(['auth'])->name('get-sup');
    
// Dynamic Daftar Suplier

Route::get('/sup/edit', 'App\Http\Controllers\Master\SuplierController@edit')->middleware(['auth'])->name('sup.edit');
Route::post('/sup/update/{sup}', 'App\Http\Controllers\Master\SuplierController@update')->middleware(['auth'])->name('sup.update');
Route::get('/sup/delete/{sup}', 'App\Http\Controllers\Master\SuplierController@destroy')->middleware(['auth'])->name('sup.delete');

///////////////////////

// Master Data Barang
Route::get('/dbrg', 'App\Http\Controllers\Master\DataBrg2Controller@index')->middleware(['auth'])->name('dbrg');
Route::post('/dbrg/store', 'App\Http\Controllers\Master\DataBrg2Controller@store')->middleware(['auth'])->name('dbrg/store');
// GET Data Barang
Route::get('/get-dbrg', 'App\Http\Controllers\Master\DataBrg2Controller@getDataBrg')->middleware(['auth'])->name('get-dbrg');
    
// Dynamic Data Barang

Route::get('/dbrg/edit', 'App\Http\Controllers\Master\DataBrg2Controller@edit')->middleware(['auth'])->name('dbrg.edit');
Route::post('/dbrg/update/{dbrg}', 'App\Http\Controllers\Master\DataBrg2Controller@update')->middleware(['auth'])->name('dbrg.update');
Route::get('/dbrg/delete/{dbrg}', 'App\Http\Controllers\Master\DataBrg2Controller@destroy')->middleware(['auth'])->name('dbrg.delete');

///////////////////////

// Master Data Barang (2)
Route::get('/dbrg2', 'App\Http\Controllers\Master\DataBrg2Controller@index')->middleware(['auth'])->name('dbrg2');
Route::post('/dbrg2/store', 'App\Http\Controllers\Master\DataBrg2Controller@store')->middleware(['auth'])->name('dbrg2/store');
    // GET Data Barang (2)
    Route::get('/get-dbrg2', 'App\Http\Controllers\Master\DataBrg2Controller@getDataBrg2')->middleware(['auth'])->name('get-dbrg2');
    
// Dynamic Data Barang (2)

Route::get('/dbrg2/edit', 'App\Http\Controllers\Master\DataBrg2Controller@edit')->middleware(['auth'])->name('dbrg2.edit');
Route::post('/dbrg2/update/{dbrg}', 'App\Http\Controllers\Master\DataBrg2Controller@update')->middleware(['auth'])->name('dbrg2.update');
Route::get('/dbrg2/delete/{dbrg}', 'App\Http\Controllers\Master\DataBrg2Controller@destroy')->middleware(['auth'])->name('dbrg2.delete');

///////////////////////

// Master Suplier Food Center
Route::get('/sup-food-center', 'App\Http\Controllers\Master\SupFCController@index')->middleware(['auth'])->name('sup-food-center');
Route::post('/sup-food-center/store', 'App\Http\Controllers\Master\SupFCController@store')->middleware(['auth'])->name('sup-food-center/store');
    // GET Suplier Food Center
    Route::get('/get-fcenter', 'App\Http\Controllers\Master\SupFCController@getSupFC')->middleware(['auth'])->name('get-fcenter');
    
// Dynamic Suplier Food Center

Route::get('/sup-food-center/edit', 'App\Http\Controllers\Master\SupFCController@edit')->middleware(['auth'])->name('sup-food-center.edit');
Route::post('/sup-food-center/update/{sfc}', 'App\Http\Controllers\Master\SupFCController@update')->middleware(['auth'])->name('sup-food-center.update');
Route::get('/sup-food-center/delete/{sfc}', 'App\Http\Controllers\Master\SupFCController@destroy')->middleware(['auth'])->name('sup-food-center.delete');

///////////////////////

// Master EDC
Route::get('/edc', 'App\Http\Controllers\Master\EdcController@index')->middleware(['auth'])->name('edc');
Route::post('/edc/store', 'App\Http\Controllers\Master\EdcController@store')->middleware(['auth'])->name('edc/store');
    // GET EDC
    Route::get('/get-edc', 'App\Http\Controllers\Master\EdcController@getEdc')->middleware(['auth'])->name('get-edc');
    
// Dynamic EDC

Route::get('/edc/edit', 'App\Http\Controllers\Master\EdcController@edit')->middleware(['auth'])->name('edc.edit');
Route::post('/edc/update/{edc}', 'App\Http\Controllers\Master\EdcController@update')->middleware(['auth'])->name('edc.update');
Route::get('/edc/delete/{edc}', 'App\Http\Controllers\Master\EdcController@destroy')->middleware(['auth'])->name('edc.delete');

///////////////////////

// Master Ganti Sub Item
Route::get('/gsub', 'App\Http\Controllers\Master\GantiSubController@index')->middleware(['auth'])->name('gsub');
Route::post('/gsub/store', 'App\Http\Controllers\Master\GantiSubController@store')->middleware(['auth'])->name('gsub/store');
    // GET Ganti Sub Item
    Route::get('/get-gsub', 'App\Http\Controllers\Master\GantiSubController@getSub')->middleware(['auth'])->name('get-gsub');
    
// Dynamic Ganti Sub Item

Route::get('/gsub/edit', 'App\Http\Controllers\Master\GantiSubController@edit')->middleware(['auth'])->name('gsub.edit');
Route::post('/gsub/update/{gsub}', 'App\Http\Controllers\Master\GantiSubController@update')->middleware(['auth'])->name('gsub.update');
Route::get('/gsub/delete/{gsub}', 'App\Http\Controllers\Master\GantiSubController@destroy')->middleware(['auth'])->name('gsub.delete');

///////////////////////

// Master Hapus Barang
Route::get('/hbrg', 'App\Http\Controllers\Master\HapusBrgController@index')->middleware(['auth'])->name('hbrg');
Route::post('/hbrg/store', 'App\Http\Controllers\Master\HapusBrgController@store')->middleware(['auth'])->name('hbrg/store');
    // GET Hapus Barang
    Route::get('/get-hbrg', 'App\Http\Controllers\Master\HapusBrgController@getHbrg')->middleware(['auth'])->name('get-hbrg');
    
// Dynamic Hapus Barang

Route::get('/hbrg/edit', 'App\Http\Controllers\Master\HapusBrgController@edit')->middleware(['auth'])->name('hbrg.edit');
Route::post('/hbrg/update/{hbrg}', 'App\Http\Controllers\Master\HapusBrgController@update')->middleware(['auth'])->name('hbrg.update');
Route::get('/hbrg/delete/{hbrg}', 'App\Http\Controllers\Master\HapusBrgController@destroy')->middleware(['auth'])->name('hbrg.delete');

///////////////////////

// Master Invoice Agenda
Route::get('/invoice', 'App\Http\Controllers\Master\InvoiceController@index')->middleware(['auth'])->name('invoice');
Route::post('/invoice/store', 'App\Http\Controllers\Master\InvoiceController@store')->middleware(['auth'])->name('invoice/store');
    // GET Invoice Agenda
    Route::get('/get-invoice', 'App\Http\Controllers\Master\InvoiceController@getInvoice')->middleware(['auth'])->name('get-invoice');
    
// Dynamic Invoice Agenda

Route::get('/invoice/edit', 'App\Http\Controllers\Master\InvoiceController@edit')->middleware(['auth'])->name('invoice.edit');
Route::post('/invoice/update/{invoice}', 'App\Http\Controllers\Master\InvoiceController@update')->middleware(['auth'])->name('invoice.update');
Route::get('/invoice/delete/{invoice}', 'App\Http\Controllers\Master\InvoiceController@destroy')->middleware(['auth'])->name('invoice.delete');

///////////////////////

// Master Keperluan Barang & Jasa
Route::get('/brg-jasa', 'App\Http\Controllers\Master\BrgJasaController@index')->middleware(['auth'])->name('brg-jasa');
Route::post('/brg-jasa/store', 'App\Http\Controllers\Master\BrgJasaController@store')->middleware(['auth'])->name('brg-jasa/store');
    // GET Keperluan Barang & Jasa
    Route::get('/get-brg-jasa', 'App\Http\Controllers\Master\BrgJasaController@getBrgJasa')->middleware(['auth'])->name('get-brg-jasa');
    
// Dynamic Keperluan Barang & Jasa

Route::get('/brg-jasa/edit', 'App\Http\Controllers\Master\BrgJasaController@edit')->middleware(['auth'])->name('brg-jasa.edit');
Route::post('/brg-jasa/update/{brgJasa}', 'App\Http\Controllers\Master\BrgJasaController@update')->middleware(['auth'])->name('brg-jasa.update');
Route::get('/brg-jasa/delete/{brgJasa}', 'App\Http\Controllers\Master\BrgJasaController@destroy')->middleware(['auth'])->name('brg-jasa.delete');

///////////////////////

// Master Keperluan Barang & Jasa PA
Route::get('/brg-jasa-pa', 'App\Http\Controllers\Master\BrgJasaPaController@index')->middleware(['auth'])->name('brg-jasa-pa');
Route::post('/brg-jasa-pa/store', 'App\Http\Controllers\Master\BrgJasaPaController@store')->middleware(['auth'])->name('brg-jasa-pa/store');
    // GET Keperluan Barang & Jasa PA
    Route::get('/get-brgJasa-pa', 'App\Http\Controllers\Master\BrgJasaPaController@getBrgJasaPA')->middleware(['auth'])->name('get-brgJasa-pa');
    
// Dynamic Keperluan Barang & Jasa PA

Route::get('/brg-jasa-pa/edit', 'App\Http\Controllers\Master\BrgJasaPaController@edit')->middleware(['auth'])->name('brg-jasa-pa.edit');
Route::post('/brg-jasa-pa/update/{brgJasa}', 'App\Http\Controllers\Master\BrgJasaPaController@update')->middleware(['auth'])->name('brg-jasa-pa.update');
Route::get('/brg-jasa-pa/delete/{brgJasa}', 'App\Http\Controllers\Master\BrgJasaPaController@destroy')->middleware(['auth'])->name('brg-jasa-pa.delete');

///////////////////////

// Master Margin Kasir
Route::get('/margin-ksr', 'App\Http\Controllers\Master\MarginKsrController@index')->middleware(['auth'])->name('margin-ksr');
Route::post('/margin-ksr/store', 'App\Http\Controllers\Master\MarginKsrController@store')->middleware(['auth'])->name('margin-ksr/store');
    // GET Margin Kasir
Route::get('/get-margin-ksr', 'App\Http\Controllers\Master\MarginKsrController@getMarginKsr')->middleware(['auth'])->name('get-margin-ksr');
    
// Dynamic Margin Kasir

Route::get('/margin-ksr/edit', 'App\Http\Controllers\Master\MarginKsrController@edit')->middleware(['auth'])->name('margin-ksr.edit');
Route::post('/margin-ksr/update/{MrgKsr}', 'App\Http\Controllers\Master\MarginKsrController@update')->middleware(['auth'])->name('margin-ksr.update');
Route::get('/margin-ksr/delete/{MrgKsr}', 'App\Http\Controllers\Master\MarginKsrController@destroy')->middleware(['auth'])->name('margin-ksr.delete');

///////////////////////
// Master Rekanan
Route::get('/rekanan', 'App\Http\Controllers\Master\RekananController@index')->middleware(['auth'])->name('rekanan');
Route::post('/rekanan/store', 'App\Http\Controllers\Master\RekananController@store')->middleware(['auth'])->name('rekanan/store');
// GET Rekanan
Route::get('/get-rekanan', 'App\Http\Controllers\Master\RekananController@getRekanan')->middleware(['auth'])->name('get-rekanan');
    
// Dynamic Rekanan

Route::get('/rekanan/edit', 'App\Http\Controllers\Master\RekananController@edit')->middleware(['auth'])->name('rekanan.edit');
Route::post('/rekanan/update/{rekanan}', 'App\Http\Controllers\Master\RekananController@update')->middleware(['auth'])->name('rekanan.update');
Route::get('/rekanan/delete/{rekanan}', 'App\Http\Controllers\Master\RekananController@destroy')->middleware(['auth'])->name('rekanan.delete');

///////////////////////
// Master Supplier Sewa
Route::get('/sup-sewa', 'App\Http\Controllers\Master\SupSewaController@index')->middleware(['auth'])->name('sup-sewa');
Route::post('/sup-sewa/store', 'App\Http\Controllers\Master\SupSewaController@store')->middleware(['auth'])->name('sup-sewa/store');
// GET Supplier Sewa
Route::get('/get-sup-sewa', 'App\Http\Controllers\Master\SupSewaController@getSupSewa')->middleware(['auth'])->name('get-sup-sewa');
    
// Dynamic Supplier Sewa

Route::get('/sup-sewa/edit', 'App\Http\Controllers\Master\SupSewaController@edit')->middleware(['auth'])->name('sup-sewa.edit');
Route::post('/sup-sewa/update/{SupSewa}', 'App\Http\Controllers\Master\SupSewaController@update')->middleware(['auth'])->name('sup-sewa.update');
Route::get('/sup-sewa/delete/{SupSewa}', 'App\Http\Controllers\Master\SupSewaController@destroy')->middleware(['auth'])->name('sup-sewa.delete');

///////////////////////
// Master Pengajuan Perubahan Barang
Route::get('/perubahan_brg', 'App\Http\Controllers\Master\PerubahanBrgController@index')->middleware(['auth'])->name('perubahan_brg');
Route::post('/perubahan_brg/store', 'App\Http\Controllers\Master\PerubahanBrgController@store')->middleware(['auth'])->name('perubahan_brg/store');
// GET Pengajuan Perubahan Barang
Route::get('/get-perubahan_brg', 'App\Http\Controllers\Master\PerubahanBrgController@getPerubahanBrg')->middleware(['auth'])->name('get-perubahan_brg');
    
// Dynamic Pengajuan Perubahan Barang

Route::get('/perubahan_brg/edit', 'App\Http\Controllers\Master\PerubahanBrgController@edit')->middleware(['auth'])->name('perubahan_brg.edit');
Route::post('/perubahan_brg/update/{PerubahanBrg}', 'App\Http\Controllers\Master\PerubahanBrgController@update')->middleware(['auth'])->name('perubahan_brg.update');
Route::get('/perubahan_brg/delete/{PerubahanBrg}', 'App\Http\Controllers\Master\PerubahanBrgController@destroy')->middleware(['auth'])->name('perubahan_brg.delete');

///////////////////////
// Master Pengajuan Perubahan Supplier
Route::get('/perubahan_sup', 'App\Http\Controllers\Master\PerubahanSupController@index')->middleware(['auth'])->name('perubahan_sup');
Route::post('/perubahan_sup/store', 'App\Http\Controllers\Master\PerubahanSupController@store')->middleware(['auth'])->name('perubahan_sup/store');
// GET Pengajuan Perubahan Supplier
Route::get('/get-perubahan_sup', 'App\Http\Controllers\Master\PerubahanSupController@getPerubahanSup')->middleware(['auth'])->name('get-perubahan_sup');
    
// Dynamic Pengajuan Perubahan Supplier

Route::get('/perubahan_sup/edit', 'App\Http\Controllers\Master\PerubahanSupController@edit')->middleware(['auth'])->name('perubahan_sup.edit');
Route::post('/perubahan_sup/update/{PerubahanSup}', 'App\Http\Controllers\Master\PerubahanSupController@update')->middleware(['auth'])->name('perubahan_sup.update');
Route::get('/perubahan_sup/delete/{PerubahanSup}', 'App\Http\Controllers\Master\PerubahanSupController@destroy')->middleware(['auth'])->name('perubahan_sup.delete');

///////////////////////
// Master Import SQL
Route::get('/import_sql', 'App\Http\Controllers\Master\ImportSqlController@index')->middleware(['auth'])->name('import_sql');
Route::post('/import_sql/store', 'App\Http\Controllers\Master\ImportSqlController@store')->middleware(['auth'])->name('import_sql/store');
// GET Import SQL
Route::get('/get-import-sql', 'App\Http\Controllers\Master\ImportSqlController@getImportSql')->middleware(['auth'])->name('get-import-sql');
    
// Dynamic Import SQL

Route::get('/import_sql/edit', 'App\Http\Controllers\Master\ImportSqlController@edit')->middleware(['auth'])->name('import_sql.edit');
Route::post('/import_sql/update/{ImportSql}', 'App\Http\Controllers\Master\ImportSqlController@update')->middleware(['auth'])->name('import_sql.update');
Route::get('/import_sql/delete/{ImportSql}', 'App\Http\Controllers\Master\ImportSqlController@destroy')->middleware(['auth'])->name('import_sql.delete');

///////////////////////
// Master Report Penjualan Rekanan
Route::get('/rjual-rekanan', 'App\Http\Controllers\Master\RjualRekananController@index')->middleware(['auth'])->name('rjual-rekanan');
Route::post('/rjual-rekanan/store', 'App\Http\Controllers\Master\RjualRekananController@store')->middleware(['auth'])->name('rjual-rekanan/store');
// GET Report Penjualan Rekanan
Route::get('/get-rjual-rekanan', 'App\Http\Controllers\Master\RjualRekananController@getRjualRekanan')->middleware(['auth'])->name('get-rjual-rekanan');
    
// Dynamic Report Penjualan Rekanan

Route::get('/rjual-rekanan/edit', 'App\Http\Controllers\Master\RjualRekananController@edit')->middleware(['auth'])->name('rjual-rekanan.edit');
Route::post('/rjual-rekanan/update/{RjualRekanan}', 'App\Http\Controllers\Master\RjualRekananController@update')->middleware(['auth'])->name('rjual-rekanan.update');
Route::get('/rjual-rekanan/delete/{RjualRekanan}', 'App\Http\Controllers\Master\RjualRekananController@destroy')->middleware(['auth'])->name('rjual-rekanan.delete');

///////////////////////
// Master Sub
Route::get('/sub', 'App\Http\Controllers\Master\SubController@index')->middleware(['auth'])->name('sub');
Route::post('/sub/store', 'App\Http\Controllers\Master\SubController@store')->middleware(['auth'])->name('sub/store');
// GET Sub
Route::get('/get-sub', 'App\Http\Controllers\Master\SubController@getSub')->middleware(['auth'])->name('get-sub');
Route::get('/sub/ceksub', 'App\Http\Controllers\Master\SubController@cekSub')->middleware(['auth'])->name('sub/ceksub');

// Dynamic Sub

Route::get('/sub/edit', 'App\Http\Controllers\Master\SubController@edit')->middleware(['auth'])->name('sub.edit');
Route::post('/sub/update/{sub}', 'App\Http\Controllers\Master\SubController@update')->middleware(['auth'])->name('sub.update');
Route::get('/sub/delete/{sub}', 'App\Http\Controllers\Master\SubController@destroy')->middleware(['auth'])->name('sub.delete');

///////////////////////
// Master Update Harga Beli
Route::get('/update-hrg-beli', 'App\Http\Controllers\Master\UpdateHrgBeliController@index')->middleware(['auth'])->name('update-hrg-beli');
Route::post('/update-hrg-beli/store', 'App\Http\Controllers\Master\UpdateHrgBeliController@store')->middleware(['auth'])->name('update-hrg-beli/store');
// GET Update Harga Beli
Route::get('/get-update-hrg-beli', 'App\Http\Controllers\Master\UpdateHrgBeliController@getUpdateHrgBeli')->middleware(['auth'])->name('get-update-hrg-beli');
    
// Dynamic Update Harga Beli

Route::get('/update-hrg-beli/edit', 'App\Http\Controllers\Master\UpdateHrgBeliController@edit')->middleware(['auth'])->name('update-hrg-beli.edit');
Route::post('/update-hrg-beli/update/{UpdateHrgBeli}', 'App\Http\Controllers\Master\UpdateHrgBeliController@update')->middleware(['auth'])->name('update-hrg-beli.update');
Route::get('/update-hrg-beli/delete/{UpdateHrgBeli}', 'App\Http\Controllers\Master\UpdateHrgBeliController@destroy')->middleware(['auth'])->name('update-hrg-beli.delete');

///////////////////////
// Master Usulan Barang Kasir Rekanan
Route::get('/usl-brg-rekanan', 'App\Http\Controllers\Master\BarangKasirRekananController@index')->middleware(['auth'])->name('usl-brg-rekanan');
Route::post('/usl-brg-rekanan/store', 'App\Http\Controllers\Master\BarangKasirRekananController@store')->middleware(['auth'])->name('usl-brg-rekanan/store');
// GET Usulan Barang Kasir Rekanan
Route::get('/get-usl-brg-rekanan', 'App\Http\Controllers\Master\BarangKasirRekananController@getUsulanBrgRekanan')->middleware(['auth'])->name('get-usl-brg-rekanan');
    
// Dynamic Usulan Barang Kasir Rekanan

Route::get('/usl-brg-rekanan/edit', 'App\Http\Controllers\Master\BarangKasirRekananController@edit')->middleware(['auth'])->name('usl-brg-rekanan.edit');
Route::post('/usl-brg-rekanan/update/{UsulBrgRekanan}', 'App\Http\Controllers\Master\BarangKasirRekananController@update')->middleware(['auth'])->name('usl-brg-rekanan.update');
Route::get('/usl-brg-rekanan/delete/{UsulBrgRekanan}', 'App\Http\Controllers\Master\BarangKasirRekananController@destroy')->middleware(['auth'])->name('usl-brg-rekanan.delete');

///////////////////////
// Master Usulan Barang Kasir Td
Route::get('/usl-brg-td', 'App\Http\Controllers\Master\BarangKasirTdController@index')->middleware(['auth'])->name('usl-brg-td');
Route::post('/usl-brg-td/store', 'App\Http\Controllers\Master\BarangKasirTdController@store')->middleware(['auth'])->name('usl-brg-td/store');
// GET Usulan Barang Kasir Td
Route::get('/get-usl-brg-td', 'App\Http\Controllers\Master\BarangKasirTdController@getUsulanBrgTd')->middleware(['auth'])->name('get-usl-brg-td');
    
// Dynamic Usulan Barang Kasir Td

Route::get('/usl-brg-td/edit', 'App\Http\Controllers\Master\BarangKasirTdController@edit')->middleware(['auth'])->name('usl-brg-td.edit');
Route::post('/uslBrgTd-proses', 'App\Http\Controllers\Master\BarangKasirTdController@proses')->middleware(['auth'])->name('uslBrgTd-proses');

///////////////////////
// Master Usulan Barang Kasir Hf
Route::get('/usl-brg-hf', 'App\Http\Controllers\Master\BarangKasirHfController@index')->middleware(['auth'])->name('usl-brg-hf');
Route::post('/usl-brg-hf/store', 'App\Http\Controllers\Master\BarangKasirHfController@store')->middleware(['auth'])->name('usl-brg-hf/store');
// GET Usulan Barang Kasir Hf
Route::get('/get-usl-brg-hf', 'App\Http\Controllers\Master\BarangKasirHfController@getUsulanBrghf')->middleware(['auth'])->name('get-usl-brg-hf');
    
// Dynamic Usulan Barang Kasir Hf

Route::get('/usl-brg-hf/edit', 'App\Http\Controllers\Master\BarangKasirHfController@edit')->middleware(['auth'])->name('usl-brg-hf.edit');
Route::post('/uslBrghf-proses', 'App\Http\Controllers\Master\BarangKasirHfController@proses')->middleware(['auth'])->name('uslBrghf-proses');

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

///////////////////////
// Transaksi Pesanan Khusus Outlet
Route::get('/psn-outlet', 'App\Http\Controllers\OTransaksi\PsnOutletController@index')->middleware(['auth'])->name('psn-outlet');
Route::post('/psn-outlet/store', 'App\Http\Controllers\OTransaksi\PsnOutletController@store')->middleware(['auth'])->name('psn-outlet/store');
// GET Pesanan Khusus Outlet
Route::get('/get-psn-outlet', 'App\Http\Controllers\OTransaksi\PsnOutletController@getPesanOutlet')->middleware(['auth'])->name('get-psn-outlet');
    
// Transaksi Pesanan Khusus Outlet

Route::get('/psn-outlet/edit', 'App\Http\Controllers\OTransaksi\PsnOutletController@edit')->middleware(['auth'])->name('psn-outlet.edit');
Route::post('/psn-outlet/update/{PsnOutlet}', 'App\Http\Controllers\OTransaksi\PsnOutletController@update')->middleware(['auth'])->name('psn-outlet.update');
Route::get('/psn-outlet/delete/{PsnOutlet}', 'App\Http\Controllers\OTransaksi\PsnOutletController@destroy')->middleware(['auth'])->name('psn-outlet.delete');

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Operational orderpbl

Route::get('/orderpbl', 'App\Http\Controllers\OTransaksi\OrderpblController@index')->middleware(['auth'])->name('orderpbl');
Route::post('/orderpbl/store', 'App\Http\Controllers\OTransaksi\OrderpblController@store')->middleware(['auth'])->name('orderpbl/store');
Route::get('/rorderpbl', 'App\Http\Controllers\OReport\ROrderpblController@report')->middleware(['auth'])->name('rorderpbl');

    Route::get('/get-orderpbl', 'App\Http\Controllers\OTransaksi\OrderpblController@getorderpbl')->middleware(['auth'])->name('get-orderpbl');
    Route::get('/orderpbl/browse', 'App\Http\Controllers\OTransaksi\OrderpblController@browse')->middleware(['auth']);
// Dynamic orderpbl
Route::get('/orderpbl/edit', 'App\Http\Controllers\OTransaksi\OrderpblController@edit')->middleware(['auth'])->name('orderpbl.edit');
Route::post('/orderpbl/update/{khusus}', 'App\Http\Controllers\OTransaksi\OrderpblController@update')->middleware(['auth'])->name('orderpbl.update');
Route::get('/orderpbl/delete/{khusus}', 'App\Http\Controllers\OTransaksi\OrderpblController@destroy')->middleware(['auth'])->name('orderpbl.delete');

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Operational orderpjl

Route::get('/orderpjl', 'App\Http\Controllers\OTransaksi\OrderpjlController@index')->middleware(['auth'])->name('orderpjl');
Route::post('/orderpjl/store', 'App\Http\Controllers\OTransaksi\OrderpjlController@store')->middleware(['auth'])->name('orderpjl/store');
Route::get('/rorderpjl', 'App\Http\Controllers\OReport\ROrderpjlController@report')->middleware(['auth'])->name('rorderpjl');

    Route::get('/get-orderpjl', 'App\Http\Controllers\OTransaksi\OrderpjlController@getOrderpjl')->middleware(['auth'])->name('get-orderpjl');
    Route::get('/orderpjl/browse', 'App\Http\Controllers\OTransaksi\OrderpjlController@browse')->middleware(['auth']);
// Dynamic orderpjl
Route::get('/orderpjl/edit', 'App\Http\Controllers\OTransaksi\OrderpjlController@edit')->middleware(['auth'])->name('orderpjl.edit');
Route::post('/orderpjl/update/{orderpjl}', 'App\Http\Controllers\OTransaksi\OrderpjlController@update')->middleware(['auth'])->name('orderpjl.update');
Route::get('/orderpjl/delete/{orderpjl}', 'App\Http\Controllers\OTransaksi\OrderpjlController@destroy')->middleware(['auth'])->name('orderpjl.delete');

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Operational Orderan Customer
Route::get('/ordercust', 'App\Http\Controllers\OTransaksi\OrdercustController@index')->middleware(['auth'])->name('ordercust');
Route::post('/ordercust/store', 'App\Http\Controllers\OTransaksi\OrdercustController@store')->middleware(['auth'])->name('ordercust/store');
Route::get('/rordercust', 'App\Http\Controllers\OReport\ROrdercustController@report')->middleware(['auth'])->name('rordercust');
    // GET Orederan Customer
    Route::get('/get-ordercust', 'App\Http\Controllers\OTransaksi\OrdercustController@getOrdercust')->middleware(['auth'])->name('get-ordercust');
    Route::get('/ordercust/browse', 'App\Http\Controllers\OTransaksi\OrdercustController@browse')->middleware(['auth'])->name('ordercust/browse');
    Route::get('/get-ordercust-report', 'App\Http\Controllers\OReport\ROrdercustController@getordercustReport')->middleware(['auth'])->name('get-ordercust-report');
    Route::post('/jasper-ordercust-report', 'App\Http\Controllers\OReport\ROrdercustController@jasperordercustReport')->middleware(['auth'])->name('jasper-ordercust-report');
    Route::get('ordercust/cekordercust', 'App\Http\Controllers\OTransaksi\OrdercustController@cekordercust')->middleware(['auth']);
	Route::get('ordercust/get-select-kodes', 'App\Http\Controllers\OTransaksi\OrdercustController@getSelectKodes')->middleware(['auth']);
// Dynamic Orderan Customer
Route::get('/ordercust/edit', 'App\Http\Controllers\OTransaksi\OrdercustController@edit')->middleware(['auth'])->name('ordercust.edit');
Route::post('/ordercust/update/{no_bukti}', 'App\Http\Controllers\OTransaksi\OrdercustController@update')->middleware(['auth'])->name('ordercust.update');
Route::get('/ordercust/delete/{no_bukti}', 'App\Http\Controllers\OTransaksi\OrdercustController@destroy')->middleware(['auth'])->name('ordercust.delete');

/////////////////////////////////////////////////////

// Operational Orderan Manual
Route::get('/orderman', 'App\Http\Controllers\OTransaksi\OrdermanController@index')->middleware(['auth'])->name('orderman');
Route::post('/orderman/store', 'App\Http\Controllers\OTransaksi\OrdermanController@store')->middleware(['auth'])->name('orderman/store');
Route::get('/rorderman', 'App\Http\Controllers\OReport\ROrdermanController@report')->middleware(['auth'])->name('rorderman');
    // GET Orederan Manual
    Route::get('/get-orderman', 'App\Http\Controllers\OTransaksi\OrdermanController@getorderman')->middleware(['auth'])->name('get-orderman');
    Route::get('/orderman/browse', 'App\Http\Controllers\OTransaksi\OrdermanController@browse')->middleware(['auth'])->name('orderman/browse');
    Route::get('/get-orderman-report', 'App\Http\Controllers\OReport\ROrdermanController@getordermanReport')->middleware(['auth'])->name('get-orderman-report');
    Route::post('/jasper-orderman-report', 'App\Http\Controllers\OReport\ROrdermanController@jasperordermanReport')->middleware(['auth'])->name('jasper-orderman-report');
    Route::get('orderman/cekorderman', 'App\Http\Controllers\OTransaksi\OrdermanController@cekorderman')->middleware(['auth']);
	Route::get('orderman/get-select-kodes', 'App\Http\Controllers\OTransaksi\OrdermanController@getSelectKodes')->middleware(['auth']);
// Dynamic Orderan Manual
Route::get('/orderman/edit', 'App\Http\Controllers\OTransaksi\OrdermanController@edit')->middleware(['auth'])->name('orderman.edit');
Route::post('/orderman/update/{no_bukti}', 'App\Http\Controllers\OTransaksi\OrdermanController@update')->middleware(['auth'])->name('orderman.update');
Route::get('/orderman/delete/{no_bukti}', 'App\Http\Controllers\OTransaksi\OrdermanController@destroy')->middleware(['auth'])->name('orderman.delete');
//////////////////////////////////////////////////////

// Master Greet
Route::get('/greet', 'App\Http\Controllers\Master\GreetController@index')->middleware(['auth'])->name('greet');
Route::post('/greet/store', 'App\Http\Controllers\Master\GreetController@store')->middleware(['auth'])->name('greet/store');
Route::get('/rgreet', 'App\Http\Controllers\OReport\RGreetController@report')->middleware(['auth'])->name('rgreet');
    // GET Master Greet
    Route::get('/get-greet', 'App\Http\Controllers\Master\GreetController@getgreet')->middleware(['auth'])->name('get-greet');
    Route::get('/greet/browse', 'App\Http\Controllers\Master\GreetController@browse')->middleware(['auth'])->name('greet/browse');
    Route::get('/get-greet-report', 'App\Http\Controllers\OReport\RGreetController@getgreetReport')->middleware(['auth'])->name('get-greet-report');
    Route::post('/jasper-greet-report', 'App\Http\Controllers\OReport\RGreetController@jaspergreetReport')->middleware(['auth'])->name('jasper-greet-report');
    Route::get('greet/cekgreet', 'App\Http\Controllers\Master\GreetController@cekgreet')->middleware(['auth']);
	Route::get('greet/get-select-kodes', 'App\Http\Controllers\Master\GreetController@getSelectKodes')->middleware(['auth']);
// Dynamic Master Greet
Route::get('/greet/edit/{baris}', 'App\Http\Controllers\Master\GreetController@edit')->middleware(['auth'])->name('greet.edit');
Route::post('/greet/update/{baris}', 'App\Http\Controllers\Master\GreetController@update')->middleware(['auth'])->name('greet.update');
Route::get('/greet/delete/{baris}', 'App\Http\Controllers\Master\GreetController@destroy')->middleware(['auth'])->name('greet.delete');
//////////////////////////////////////////////////////

// Master Expim
Route::get('/expim', 'App\Http\Controllers\Master\ExpimController@expim')->middleware(['auth'])->name('expim');
//////////////////////////////////////////////////////

//Transaksi Order Kue Basah
Route::get('/ordkode8', 'App\Http\Controllers\OTransaksi\Ordkode8Controller@index')->middleware(['auth'])->name('ordkode8');
Route::get('/get-ordkode8', 'App\Http\Controllers\OTransaksi\Ordkode8Controller@getOrdkode8')->middleware(['auth'])->name('get-ordkode8');

//Transaksi Pelaksanaan Perubahan Harga VIP
Route::get('/ubahhrgvip', 'App\Http\Controllers\OTransaksi\UbahhrgvipController@index')->middleware(['auth'])->name('ubahhrgvip');
Route::get('/get-ubahhrgvip', 'App\Http\Controllers\OTransaksi\UbahhrgvipController@getUbahhrgvip')->middleware(['auth'])->name('get-ubahhrgvip');
Route::get('ubahhrg/print', 'App\Http\Controllers\OTransaksi\UbahhrgvipController@print')->middleware(['auth'])->name('ubahhrgvip.print');

//Transaksi Rekap Label Harian
Route::get('/rkplabel', 'App\Http\Controllers\OTransaksi\RkplabelController@index')->middleware(['auth'])->name('rkplabel');
Route::get('/get-rkplabel', 'App\Http\Controllers\OTransaksi\RkplabelController@getRkplabel')->middleware(['auth'])->name('get-rkplabel');
require __DIR__.'/auth.php';