<?php

use Illuminate\Support\Facades\Route;


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



require __DIR__ . '/auth.php';
//Untuk Dashboard
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

// Dashboard
Route::get('/', 'App\Http\Controllers\DashboardController@index')->middleware(['auth']);

Route::get('/dashboard', 'App\Http\Controllers\DashboardController@dashboard_plain')->middleware(['auth']);
// Chart Dashboard
Route::get('/chart', 'App\Http\Controllers\DashboardController@chart')->middleware(['auth']);

// Periode
Route::post('/periode', 'App\Http\Controllers\PeriodeController@index')->middleware(['auth'])->name('periode');

//User Edit
Route::get('/profile', 'App\Http\Controllers\ProfileController@index')->middleware(['auth']);
Route::post('/profile/update', 'App\Http\Controllers\ProfileController@update')->middleware(['auth']);
Route::post('/profile/setting/update', 'App\Http\Controllers\ProfileController@updateSetting')->middleware(['auth']);

////////
// Master Account
Route::get('/account', 'App\Http\Controllers\FMaster\AccountController@index')->middleware(['auth'])->name('account');

Route::get('/account/create', 'App\Http\Controllers\FMaster\AccountController@create')->middleware(['auth'])->name('account/create');
Route::get('/raccount', 'App\Http\Controllers\FReport\RAccountController@report')->middleware(['auth'])->name('raccount');
// GET ACCOUNT
Route::get('/get-account', 'App\Http\Controllers\FMaster\AccountController@getAccount')->middleware(['auth'])->name('get-account');
Route::get('/account/browse', 'App\Http\Controllers\FMaster\AccountController@browse')->middleware(['auth'])->name('accoumt/browse');
Route::get('/account/browse_nacno', 'App\Http\Controllers\FMaster\AccountController@browse_nacno')->middleware(['auth'])->name('accoumt/browse_nacno');


Route::get('/account/browsecash', 'App\Http\Controllers\FMaster\AccountController@browsecash')->middleware(['auth'])->name('accoumt/browsecash');
Route::get('/account/browsebank', 'App\Http\Controllers\FMaster\AccountController@browsebank')->middleware(['auth'])->name('accoumt/browsebank');
Route::get('/account/browsecashbank', 'App\Http\Controllers\FMaster\AccountController@browsecashbank')->middleware(['auth'])->name('accoumt/browsecashbank');
Route::get('/account/browseallacc', 'App\Http\Controllers\FMaster\AccountController@browseallacc')->middleware(['auth'])->name('accoumt/browseallacc');
Route::get('/get-account-report', 'App\Http\Controllers\FReport\RAccountController@getAccountReport')->middleware(['auth'])->name('get-account-report');
Route::post('/jasper-account-report', 'App\Http\Controllers\FReport\RAccountController@jasperAccountReport')->middleware(['auth']);
Route::get('account/cekacc', 'App\Http\Controllers\FMaster\AccountController@cekacc')->middleware(['auth']);
Route::get('account/browseKel', 'App\Http\Controllers\FMaster\AccountController@browseKel')->middleware(['auth']);
// Dynamic Account
Route::get('/account/edit', 'App\Http\Controllers\FMaster\AccountController@edit')->middleware(['auth'])->name('account.edit');
Route::post('/account/update/{account}', 'App\Http\Controllers\FMaster\AccountController@update')->middleware(['auth'])->name('account.update');
Route::get('/account/delete/{account}', 'App\Http\Controllers\FMaster\AccountController@destroy')->middleware(['auth'])->name('account.delete');

Route::get('/rrl', 'App\Http\Controllers\FReport\RRlController@report')->middleware(['auth'])->name('rrl');
Route::get('/get-rl-report', 'App\Http\Controllers\FReport\RRlController@getRlReport')->middleware(['auth'])->name('get-rl-report');
Route::post('/jasper-rl-report', 'App\Http\Controllers\FReport\RRlController@jasperRlReport')->middleware(['auth']);

Route::get('/rnera', 'App\Http\Controllers\FReport\RNeraController@report')->middleware(['auth'])->name('rnera');
Route::get('/get-nera-report', 'App\Http\Controllers\FReport\RNeraController@getNeraReport')->middleware(['auth'])->name('get-nera-report');
Route::post('/jasper-nera-report', 'App\Http\Controllers\FReport\RNeraController@jasperNeraReport')->middleware(['auth']);




/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


// Master Brg
Route::get('/brg', 'App\Http\Controllers\Master\BrgController@index')->middleware(['auth'])->name('brg');
Route::post('/brg/store', 'App\Http\Controllers\Master\BrgController@store')->middleware(['auth'])->name('brg/store');
// GET brg
Route::get('/get-brg', 'App\Http\Controllers\Master\BrgController@getBrg')->middleware(['auth'])->name('get-brg');
Route::get('/brg/print', 'App\Http\Controllers\Master\BrgController@Print')->middleware(['auth'])->name('brg.print');
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
Route::get('/komisi/browse_kode', 'App\Http\Controllers\Master\KomisiController@browse_kode')->middleware(['auth'])->name('komisi/browse_kode');
Route::get('/komisi/browse_sub', 'App\Http\Controllers\Master\KomisiController@browse_sub')->middleware(['auth'])->name('komisi/browse_sub');
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
Route::get('/sup/cetak','App\Http\Controllers\Master\SuplierController@cetak')->middleware(['auth']);
// Dynamic Daftar Suplier

Route::get('/sup/edit', 'App\Http\Controllers\Master\SuplierController@edit')->middleware(['auth'])->name('sup.edit');
Route::post('/sup/update/{sup}', 'App\Http\Controllers\Master\SuplierController@update')->middleware(['auth'])->name('sup.update');
Route::get('/sup/delete/{sup}', 'App\Http\Controllers\Master\SuplierController@destroy')->middleware(['auth'])->name('sup.delete');

///////////////////////

// Master Data Barang Food Center
Route::get('/dbrg', 'App\Http\Controllers\Master\DataBrgController@index')->middleware(['auth'])->name('dbrg');
Route::post('/dbrg/store', 'App\Http\Controllers\Master\DataBrgController@store')->middleware(['auth'])->name('dbrg/store');
// GET Data Barang
Route::get('/get-dbrg', 'App\Http\Controllers\Master\DataBrgController@getDataBrg')->middleware(['auth'])->name('get-dbrg');
Route::get('/dbrg/browse', 'App\Http\Controllers\Master\DataBrgController@browse')->middleware(['auth']);
Route::get('/dbrg/cetak','App\Http\Controllers\Master\DataBrgController@cetak')->middleware(['auth']);
// Dynamic Data Barang

Route::get('/dbrg/edit', 'App\Http\Controllers\Master\DataBrgController@edit')->middleware(['auth'])->name('dbrg.edit');
Route::post('/dbrg/update/{dbrg}', 'App\Http\Controllers\Master\DataBrgController@update')->middleware(['auth'])->name('dbrg.update');
Route::get('/dbrg/delete/{dbrg}', 'App\Http\Controllers\Master\DataBrgController@destroy')->middleware(['auth'])->name('dbrg.delete');

///////////////////////

// Master Data Barang Kasir
Route::get('/dbrg2', 'App\Http\Controllers\Master\DataBrg2Controller@index')->middleware(['auth'])->name('dbrg2');
Route::post('/dbrg2/store', 'App\Http\Controllers\Master\DataBrg2Controller@store')->middleware(['auth'])->name('dbrg2/store');
// GET Data Barang Kasir
Route::get('/get-dbrg2', 'App\Http\Controllers\Master\DataBrg2Controller@getDataBrg2')->middleware(['auth'])->name('get-dbrg2');
Route::get('/dbrg2/cetak','App\Http\Controllers\Master\DataBrg2Controller@cetak')->middleware(['auth']);
// Dynamic Data Barang Kasir

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
Route::get('/get-kode', 'App\Http\Controllers\Master\EdcController@getKode')->middleware(['auth'])->name('get-kode');

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
Route::get('/gsub/browse_barang', 'App\Http\Controllers\Master\GantiSubController@browse_barang')->middleware(['auth'])->name('gsub/browse_barang');
// Dynamic Ganti Sub Item

Route::get('/gsub/edit', 'App\Http\Controllers\Master\GantiSubController@edit')->middleware(['auth'])->name('gsub.edit');
Route::post('/gsub/update/{gsub}', 'App\Http\Controllers\Master\GantiSubController@update')->middleware(['auth'])->name('gsub.update');
Route::get('/gsub/delete/{gsub}', 'App\Http\Controllers\Master\GantiSubController@destroy')->middleware(['auth'])->name('gsub.delete');
Route::get('/gsub/posting/{id}', 'App\Http\Controllers\Master\GantiSubController@posting')->middleware(['auth'])->name('gsub.posting');

///////////////////////

// Master Hapus Barang Lama Kosong
Route::get('/hbrg', 'App\Http\Controllers\Master\HapusBrgController@index')->middleware(['auth'])->name('hbrg');
Route::post('/hbrg/store', 'App\Http\Controllers\Master\HapusBrgController@store')->middleware(['auth'])->name('hbrg/store');
// GET Hapus Barang Lama Kosong
Route::get('/get-hbrg', 'App\Http\Controllers\Master\HapusBrgController@getHbrg')->middleware(['auth'])->name('get-hbrg');
Route::post('/hbrg/clear', 'App\Http\Controllers\Master\HapusBrgController@clear')->middleware(['auth'])->name('hbrg.clear');
Route::post('/hbrg/proses', 'App\Http\Controllers\Master\HapusBrgController@proses')->middleware(['auth'])->name('hbrg.proses');
Route::get('/hbrg/cetak','App\Http\Controllers\Master\HapusBrgController@cetak')->middleware(['auth']);
// Dynamic Hapus Barang Lama Kosong

Route::get('/hbrg/edit', 'App\Http\Controllers\Master\HapusBrgController@edit')->middleware(['auth'])->name('hbrg.edit');
Route::post('/hbrg/update/{hbrg}', 'App\Http\Controllers\Master\HapusBrgController@update')->middleware(['auth'])->name('hbrg.update');
Route::get('/hbrg/delete/{hbrg}', 'App\Http\Controllers\Master\HapusBrgController@destroy')->middleware(['auth'])->name('hbrg.delete');

///////////////////////

// Master Hapus Barang
Route::get('/hbrg2', 'App\Http\Controllers\Master\HapusBrg2Controller@index')->middleware(['auth'])->name('hbrg2');
Route::post('/hbrg2/store', 'App\Http\Controllers\Master\HapusBrg2Controller@store')->middleware(['auth'])->name('hbrg2/store');
// GET Hapus Barang
Route::get('/get-hbrg2', 'App\Http\Controllers\Master\HapusBrg2Controller@getHbrg2')->middleware(['auth'])->name('get-hbrg2');
Route::post('/hbrg2/clear', 'App\Http\Controllers\Master\HapusBrg2Controller@clear')->middleware(['auth'])->name('hbrg2.clear');
Route::post('/hbrg2/proses', 'App\Http\Controllers\Master\HapusBrg2Controller@proses')->middleware(['auth'])->name('hbrg2.proses');
Route::get('/hbrg2/cetak','App\Http\Controllers\Master\HapusBrg2Controller@cetak')->middleware(['auth']);
// Dynamic Hapus Barang

Route::get('/hbrg2/edit', 'App\Http\Controllers\Master\HapusBrg2Controller@edit')->middleware(['auth'])->name('hbrg2.edit');
Route::post('/hbrg2/update/{hbrg2}', 'App\Http\Controllers\Master\HapusBrg2Controller@update')->middleware(['auth'])->name('hbrg2.update');
Route::get('/hbrg2/delete/{hbrg2}', 'App\Http\Controllers\Master\HapusBrg2Controller@destroy')->middleware(['auth'])->name('hbrg2.delete');

///////////////////////

// Master Invoice Agenda
Route::get('/invoice', 'App\Http\Controllers\Master\InvoiceController@index')->middleware(['auth'])->name('invoice');
Route::post('/invoice/store', 'App\Http\Controllers\Master\InvoiceController@store')->middleware(['auth'])->name('invoice.store');
// GET Invoice Agenda
Route::get('/get-invoice', 'App\Http\Controllers\Master\InvoiceController@getInvoice')->middleware(['auth'])->name('get-invoice');
Route::get('/get-kiri', 'App\Http\Controllers\Master\InvoiceController@getKiri')->middleware(['auth'])->name('get-kiri');
Route::get('/invoice/next-nomor', 'App\Http\Controllers\Master\InvoiceController@getNextNomor')->middleware(['auth'])->name('get-next-nomor');
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
Route::get('/brg-jasa/print/{jasa:NO_BUKTI}','App\Http\Controllers\Master\BrgJasaController@Print')->middleware(['auth']);
Route::get('/brg-jasa/printlap', 'App\Http\Controllers\Master\BrgJasaController@PrintLap')->middleware(['auth'])->name('brg-jasa.printlap');
Route::get('/brg-jasa/browse_dept', 'App\Http\Controllers\Master\BrgJasaController@browse_dept')->middleware(['auth'])->name('brg-jasa/browse_dept');
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
Route::get('/brg-jasa-pa/print', 'App\Http\Controllers\Master\BrgJasaPaController@Print')->middleware(['auth'])->name('brg-jasa-pa.print');
Route::get('/brg-jasa-pa/printlap', 'App\Http\Controllers\Master\BrgJasaPaController@PrintLap')->middleware(['auth'])->name('brg-jasa-pa.printlap');
Route::get('/brg-jasa/browse_dept_pa', 'App\Http\Controllers\Master\BrgJasaPaController@browse_dept_pa')->middleware(['auth'])->name('brg-jasa/browse_dept_pa');
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
Route::get('/rekanan/cetak','App\Http\Controllers\Master\RekananController@cetak')->middleware(['auth']);
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
Route::get('/get-dataubahno/{kode}', 'App\Http\Controllers\Master\ImportSqlController@getDataUbahno')->middleware(['auth'])->name('get-dataubahno');
Route::post('/proses-import', 'App\Http\Controllers\Master\ImportSqlController@prosesImport')->middleware(['auth'])->name('proses-import');

Route::get('/import_sql/edit', 'App\Http\Controllers\Master\ImportSqlController@edit')->middleware(['auth'])->name('import_sql.edit');
Route::post('/import_sql/update/{ImportSql}', 'App\Http\Controllers\Master\ImportSqlController@update')->middleware(['auth'])->name('import_sql.update');
Route::get('/import_sql/delete/{ImportSql}', 'App\Http\Controllers\Master\ImportSqlController@destroy')->middleware(['auth'])->name('import_sql.delete');
Route::get('/import_sql/cetak/{import:NO_ID}','App\Http\Controllers\Master\ImportSqlController@cetak')->middleware(['auth']);

///////////////////////
// Master Report Penjualan Rekanan
Route::get('/rjual-rekanan', 'App\Http\Controllers\Master\RjualRekananController@index')->middleware(['auth'])->name('rjual-rekanan');
Route::post('/rjual-rekanan/store', 'App\Http\Controllers\Master\RjualRekananController@store')->middleware(['auth'])->name('rjual-rekanan/store');
// GET Report Penjualan Rekanan
Route::get('/get-rjual-rekanan', 'App\Http\Controllers\Master\RjualRekananController@getRjualRekanan')->middleware(['auth'])->name('get-rjual-rekanan');
Route::get('/rjual-rekanan/print', 'App\Http\Controllers\Master\RjualRekananController@Print')->middleware(['auth'])->name('rjual-rekanan.print');
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
Route::get('/update-hrg-beli/getHargaAwal', 'App\Http\Controllers\Master\UpdateHrgBeliController@getHargaAwal')->middleware(['auth'])->name('update-hrg-beli/getHargaAwal');
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
///////////////////////////////

///////////////////////
// Master Ubah Jadwal SO
Route::get('/sog', 'App\Http\Controllers\Master\SogController@index')->middleware(['auth'])->name('sog');
Route::post('/sog/store', 'App\Http\Controllers\Master\SogController@store')->middleware(['auth'])->name('sog/store');
// GET sog
Route::get('/get-sog', 'App\Http\Controllers\Master\SogController@getSog')->middleware(['auth'])->name('get-sog');
Route::post('/sog/update', 'App\Http\Controllers\Master\SogController@updateTanggal')->middleware(['auth'])->name('sog.updateTanggal');
/////////////////////////

/////////////////////// TIARA PEMBELIAN //////////////////////////
// Report Kartu Stok Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/rkartustok', 'App\Http\Controllers\OReport\RKartuStokController@report')->name('rkartustok');
    Route::get('/get-kartustok-report', 'App\Http\Controllers\OReport\RKartuStokController@getKartuStokReport')->name('get-kartustok-report');
    Route::post('/jasper-kartustok-report', 'App\Http\Controllers\OReport\RKartuStokController@jasperKartuStokReport')->name('jasper-kartustok-report');
    Route::get('/api/get-barang-list', 'App\Http\Controllers\OReport\RKartuStokController@getBarangList')->name('api.get-barang-list');
    Route::get('/rkartustok/reset-filter', function () {session()->forget(['filter_cbg','filter_per','filter_kd_brg','filter_jenis']); return redirect()->route('rkartustok');})->name('rkartustok.reset');
});


// report barang SPM
Route::get('/rbarangspm', 'App\Http\Controllers\OReport\RBarangSPMController@report')->middleware(['auth'])->name('rbarangspm');
Route::get('/get-barangspm-report', 'App\Http\Controllers\OReport\RBarangSPMController@getBarangSPMReport')->middleware(['auth'])->name('get-barangspm-report');
Route::post('jasper-barangspm-report', 'App\Http\Controllers\OReport\RBarangSPMController@jasperBarangSPMReport')->middleware(['auth']);
// Barang FC
Route::get('/rbarangfc', 'App\Http\Controllers\OReport\RBarangFCController@report')->middleware(['auth'])->name('rbarangfc');
Route::get('/get-barangfc-report', 'App\Http\Controllers\OReport\RBarangFCController@getBarangFCReport')->middleware(['auth'])->name('get-barangfc-report');
Route::post('jasper-barangfc-report', 'App\Http\Controllers\OReport\RBarangFCController@jasperBarangFCReport')->middleware(['auth']);

// Hadiah
Route::get('/rhadiah', 'App\Http\Controllers\OReport\RHadiahController@report')->middleware(['auth'])->name('rhadiah');
Route::get('/get-hadiah-report', 'App\Http\Controllers\OReport\RHadiahController@getHadiahReport')->middleware(['auth'])->name('get-hadiah-report');
Route::post('jasper-hadiah-report', 'App\Http\Controllers\OReport\RHadiahController@jasperHadiahReport')->middleware(['auth']);

// Barang Macet Kosong
Route::get('/rbarangmacetkosong', 'App\Http\Controllers\OReport\RBarangMacetKosongController@report')->middleware(['auth'])->name('rbarangmacetkosong');
Route::get('/get-barangmacetkosong-report', 'App\Http\Controllers\OReport\RBarangMacetKosongController@getBarangMacetKosongReport')->middleware(['auth'])->name('get-barangmacetkosong-report');
Route::post('/jasper-barangmacetkosong-report', 'App\Http\Controllers\OReport\RBarangMacetKosongController@jasperBarangMacetKosongReport')->middleware(['auth'])->name('jasper-barangmacetkosong-report');
Route::get('/ajax-jenis-report', 'App\Http\Controllers\OReport\RBarangMacetKosongController@getJenisReportAjax')->middleware(['auth'])->name('ajax-jenis-report');

// Cek Perubahan LPH
Route::get('/rcekperubahanlph', 'App\Http\Controllers\OReport\RCekPerubahanLPHController@report')->middleware(['auth'])->name('rcekperubahanlph');
Route::get('/get-cekperubahanlph-report', 'App\Http\Controllers\OReport\RCekPerubahanLPHController@getCekPerubahanLPHReport')->middleware(['auth'])->name('get-cekperubahanlph-report');
Route::post('/jasper-cekperubahanlph-report', 'App\Http\Controllers\OReport\RCekPerubahanLPHController@jasperCekPerubahanLPHReport')->middleware(['auth'])->name('jasper-cekperubahanlph-report');

// Sinkronisasi Data Center
Route::get('/rsinkrondc', 'App\Http\Controllers\OReport\RSinkronDCController@report')->middleware(['auth'])->name('rsinkrondc');
Route::get('/get-sinkrondc-report', 'App\Http\Controllers\OReport\RSinkronDCController@getSinkronDCReport')->middleware(['auth'])->name('get-sinkrondc-report');
Route::post('/jasper-sinkrondc-report', 'App\Http\Controllers\OReport\RSinkronDCController@jasperSinkronDCReport')->middleware(['auth'])->name('jasper-sinkrondc-report');

// Cek Perubahan DTR2
Route::get('/rperubahandtr2', 'App\Http\Controllers\OReport\RPerubahanDTR2Controller@report')->middleware(['auth'])->name('rperubahandtr2');
Route::get('/get-perubahandtr2-report', 'App\Http\Controllers\OReport\RPerubahanDTR2Controller@getPerubahanDTR2Report')->middleware(['auth'])->name('get-perubahandtr2-report');
Route::post('/jasper-perubahandtr2-report', 'App\Http\Controllers\OReport\RPerubahanDTR2Controller@jasperPerubahanDTR2Report')->middleware(['auth'])->name('jasper-perubahandtr2-report');

// Pemantauan DTR Khusus
Route::get('/rpemantauandtrkhusus', 'App\Http\Controllers\OReport\RPemantauanDTRKhususController@report')->middleware(['auth'])->name('rpemantauandtrkhusus');
Route::get('/get-pemantauandtrkhusus-report', 'App\Http\Controllers\OReport\RPemantauanDTRKhususController@getPemantauanDTRKhususReport')->middleware(['auth'])->name('get-pemantauandtrkhusus-report');
Route::post('/jasper-pemantauandtrkhusus-report', 'App\Http\Controllers\OReport\RPemantauanDTRKhususController@jasperPemantauanDTRKhususReport')->middleware(['auth'])->name('jasper-pemantauandtrkhusus-report');

// Report Stok Nol
Route::get('/rstoknol', 'App\Http\Controllers\OReport\RStokNolController@report')->middleware(['auth'])->name('rstoknol');
Route::get('/get-stoknol-report', 'App\Http\Controllers\OReport\RStokNolController@getStokNolReport')->middleware(['auth'])->name('get-stoknol-report');
Route::post('jasper-stoknol-report', 'App\Http\Controllers\OReport\RStokNolController@jasperStokNolReport')->middleware(['auth'])->name('jasper-stoknol-report');

// Barang Supplier
Route::get('/rbarangsupplier', 'App\Http\Controllers\OReport\RBarangSupplierController@report')->middleware(['auth'])->name('rbarangsupplier');
Route::get('/get-barangsupplier-report', 'App\Http\Controllers\OReport\RBarangSupplierController@getBarangSupplierReport')->middleware(['auth'])->name('get-barangsupplier-report');
Route::post('/jasper-barangsupplier-report', 'App\Http\Controllers\OReport\RBarangSupplierController@jasperBarangSupplierReport')->middleware(['auth'])->name('jasper-barangsupplier-report');

// Stock Kosong
Route::get('/rstockkosong', 'App\Http\Controllers\OReport\RStockKosongController@report')->middleware(['auth'])->name('rstockkosong');
Route::get('/get-stockkosong-report', 'App\Http\Controllers\OReport\RStockKosongController@getStockKosongReport')->middleware(['auth'])->name('get-stockkosong-report');
Route::post('/jasper-stockkosong-report', 'App\Http\Controllers\OReport\RStockKosongController@jasperStockKosongReport')->middleware(['auth'])->name('jasper-stockkosong-report');

// Pemantauan Barang
Route::get('/rpemantauanbarang', 'App\Http\Controllers\OReport\RPemantauanBarangController@report')->middleware(['auth'])->name('rpemantauanbarang');
Route::get('/get-pemantauanbarang-report', 'App\Http\Controllers\OReport\RPemantauanBarangController@getPemantauanBarangReport')->middleware(['auth'])->name('get-pemantauanbarang-report');
Route::post('/jasper-pemantauanbarang-report', 'App\Http\Controllers\OReport\RPemantauanBarangController@jasperPemantauanBarangReport')->middleware(['auth'])->name('jasper-pemantauanbarang-report');

// RLakuPerHari
Route::get('/rlakuperhari', 'App\Http\Controllers\OReport\RLakuPerHariController@report')->middleware(['auth'])->name('rlakuperhari');
Route::get('/get-lakuperhari-report', 'App\Http\Controllers\OReport\RLakuPerHariController@getLakuPerHariReport')->middleware(['auth'])->name('get-lakuperhari-report');
Route::post('/jasper-lakuperhari-report', 'App\Http\Controllers\OReport\RLakuPerHariController@jasperLakuPerHariReport')->middleware(['auth'])->name('jasper-lakuperhari-report');

// RODCBelumDilayani
Route::get('/rodcbelum', 'App\Http\Controllers\OReport\RODCBelumDilayaniController@report')->middleware(['auth'])->name('rodcbelum');
Route::get('/get-odcbelum-report', 'App\Http\Controllers\OReport\RODCBelumDilayaniController@getODCBelumDilayaniReport')->middleware(['auth'])->name('get-odcbelum-report');
Route::post('/jasper-odcbelum-report', 'App\Http\Controllers\OReport\RODCBelumDilayaniController@jasperODCBelumDilayaniReport')->middleware(['auth'])->name('jasper-odcbelum-report');


Route::get('/roordernonkode3', 'App\Http\Controllers\OReport\ROrderNonKode3Controller@report')->middleware(['auth'])->name('roordernonkode3');
Route::get('/get-ordernonkode3-report', 'App\Http\Controllers\OReport\ROrderNonKode3Controller@getOrderNonKode3Report')->middleware(['auth'])->name('get-ordernonkode3-report');
Route::post('/jasper-ordernonkode3-report', 'App\Http\Controllers\OReport\ROrderNonKode3Controller@jasperOrderNonKode3Report')->middleware(['auth'])->name('jasper-ordernonkode3-report');

// RRencanaOrderKode8
Route::get('/rrencanaorderkode8', 'App\Http\Controllers\OReport\RRencanaOrderKode8Controller@report')->middleware(['auth'])->name('rrencanaorderkode8');
Route::get('/get-rencanaorderkode8-report', 'App\Http\Controllers\OReport\RRencanaOrderKode8Controller@getRencanaOrderKode8Report')->middleware(['auth'])->name('get-rencanaorderkode8-report');
Route::post('/jasper-rencanaorderkode8-report', 'App\Http\Controllers\OReport\RRencanaOrderKode8Controller@jasperRencanaOrderKode8Report')->middleware(['auth'])->name('jasper-rencanaorderkode8-report');


// RKasirBantu
Route::get('/rkasirbantu', 'App\Http\Controllers\OReport\RKasirBantuController@report')->middleware(['auth'])->name('rkasirbantu');
Route::get('/get-kasirbantu-report', 'App\Http\Controllers\OReport\RKasirBantuController@getKasirBantuReport')->middleware(['auth'])->name('get-kasirbantu-report');
Route::post('/jasper-kasirbantu-report', 'App\Http\Controllers\OReport\RKasirBantuController@jasperKasirBantuReport')->middleware(['auth'])->name('jasper-kasirbantu-report');
Route::get('/get-kasirbantu-report-ajax', 'App\Http\Controllers\OReport\RKasirBantuController@getKasirBantuReportAjax')->name('get-kasirbantu-report-ajax');
// Routes tambahan untuk AJAX
Route::get('/get-kasir-list/{cbg}', 'App\Http\Controllers\OReport\RKasirBantuController@getKasirList')->middleware(['auth']);
Route::get('/get-periode-list/{cbg}', 'App\Http\Controllers\OReport\RKasirBantuController@getPeriodeList')->middleware(['auth']);
Route::get('/search-kasirbantu', 'App\Http\Controllers\OReport\RKasirBantuController@searchKasirBantu')->middleware(['auth']);

// Penjualan Baru
Route::get('/rpenjualanbaru', 'App\Http\Controllers\OReport\RPenjualanBaruController@report')->middleware(['auth'])->name('rpenjualanbaru');
Route::get('/get-penjualanbaru-report', 'App\Http\Controllers\OReport\RPenjualanBaruController@getPenjualanBaruReport')->middleware(['auth'])->name('get-penjualanbaru-report');
Route::post('/jasper-penjualanbaru-report', 'App\Http\Controllers\OReport\RPenjualanBaruController@jasperPenjualanBaruReport')->middleware(['auth'])->name('jasper-penjualanbaru-report');

// Penjualan PH
Route::get('/rpenjualanph', 'App\Http\Controllers\OReport\RPenjualanPHController@report')->middleware(['auth'])->name('rpenjualanph');
Route::get('/get-penjualanph-report', 'App\Http\Controllers\OReport\RPenjualanPHController@getPenjualanPHReport')->middleware(['auth'])->name('get-penjualanph-report');
Route::post('/jasper-penjualanph-report', 'App\Http\Controllers\OReport\RPenjualanPHController@jasperPenjualanPHReport')->middleware(['auth'])->name('jasper-penjualanph-report');

// Cetak Ulang Struk
Route::get('/rcetakulangstruk', 'App\Http\Controllers\OReport\RCetakUlangStrukController@report')->middleware(['auth'])->name('rcetakulangstruk');
Route::get('/get-cetakulangstruk-report', 'App\Http\Controllers\OReport\RCetakUlangStrukController@getCetakUlangStrukReport')->middleware(['auth'])->name('get-cetakulangstruk-report');
Route::post('/jasper-cetakulangstruk-report', 'App\Http\Controllers\OReport\RCetakUlangStrukController@jasperCetakUlangStrukReport')->middleware(['auth'])->name('jasper-cetakulangstruk-report');
// API endpoints untuk AJAX calls
Route::get('/api-get-transaksi-cetakulang', 'App\Http\Controllers\OReport\RCetakUlangStrukController@apiGetTransaksi')->middleware(['auth']);
Route::get('/api-get-detail-transaksi', 'App\Http\Controllers\OReport\RCetakUlangStrukController@apiGetDetailTransaksi')->middleware(['auth']);
Route::get('/api-get-thermal-print', 'App\Http\Controllers\OReport\RCetakUlangStrukController@apiGetThermalPrint')->middleware(['auth']);

// Diskon Hadiah Berjalan
Route::get('/rdiskonhadiahberjalan', 'App\Http\Controllers\OReport\RDiskonHadiahBerjalanController@report')->middleware(['auth'])->name('rdiskonhadiahberjalan');
Route::get('/get-diskonhadiahberjalan-report', 'App\Http\Controllers\OReport\RDiskonHadiahBerjalanController@getDiskonHadiahBerjalanReport')->middleware(['auth'])->name('get-diskonhadiahberjalan-report');
Route::post('/jasper-diskonhadiahberjalan-report', 'App\Http\Controllers\OReport\RDiskonHadiahBerjalanController@jasperDiskonHadiahBerjalanReport')->middleware(['auth'])->name('jasper-diskonhadiahberjalan-report');
// API endpoints untuk AJAX calls
Route::get('/api-get-diskonhadiahberjalan', 'App\Http\Controllers\OReport\RDiskonHadiahBerjalanController@apiGetDiskonHadiahBerjalan')->middleware(['auth']);
Route::get('/api-get-detail-diskonhadiahberjalan', 'App\Http\Controllers\OReport\RDiskonHadiahBerjalanController@apiGetDetailDiskonHadiahBerjalan')->middleware(['auth']);
Route::get('/api-get-thermal-print-diskonhadiahberjalan', 'App\Http\Controllers\OReport\RDiskonHadiahBerjalanController@apiGetThermalPrintDiskonHadiahBerjalan')->middleware(['auth']);

// Jackpot Point
Route::get('/rjackpopoint', 'App\Http\Controllers\OReport\RJackpoPointController@report')->middleware(['auth'])->name('rjackpopoint');
Route::get('/get-jackpopoint-report', 'App\Http\Controllers\OReport\RJackpoPointController@getJackpoPointReport')->middleware(['auth'])->name('get-jackpopoint-report');
Route::post('/jasper-jackpopoint-report', 'App\Http\Controllers\OReport\RJackpoPointController@jasperJackpoPointReport')->middleware(['auth'])->name('jasper-jackpopoint-report');
// API endpoints untuk AJAX calls
Route::get('/api-get-jackpopoint', 'App\Http\Controllers\OReport\RJackpoPointController@apiGetJackpoPoint')->middleware(['auth']);
Route::get('/api-get-detail-jackpopoint', 'App\Http\Controllers\OReport\RJackpoPointController@apiGetDetailJackpoPoint')->middleware(['auth']);
Route::get('/api-get-thermal-print-jackpopoint', 'App\Http\Controllers\OReport\RJackpoPointController@apiGetThermalPrintJackpoPoint')->middleware(['auth']);


// Report Cetak Ulang Cashback
Route::get('/rcetakulangcashback', 'App\Http\Controllers\OReport\RCetakUlangCashbackController@report')->middleware(['auth'])->name('rcetakulangcashback');
Route::get('/get-cetakulangcashback-report', 'App\Http\Controllers\OReport\RCetakUlangCashbackController@getCetakUlangCashbackReport')->middleware(['auth'])->name('get-cetakulangcashback-report');
Route::post('/jasper-cetakulangcashback-report', 'App\Http\Controllers\OReport\RCetakUlangCashbackController@jasperCetakUlangCashbackReport')->middleware(['auth'])->name('jasper-cetakulangcashback-report');
// API endpoints untuk AJAX calls
Route::get('/api-get-cetakulangcashback', 'App\Http\Controllers\OReport\RCetakUlangCashbackController@apiGetCetakUlangCashback')->middleware(['auth']);
Route::get('/api-get-detail-cetakulangcashback', 'App\Http\Controllers\OReport\RCetakUlangCashbackController@apiGetDetailCetakUlangCashback')->middleware(['auth']);
Route::get('/api-get-thermal-print-cetakulangcashback', 'App\Http\Controllers\OReport\RCetakUlangCashbackController@apiGetThermalPrintCetakUlangCashback')->middleware(['auth']);

// Report Barcode
Route::get('/rbarcode', 'App\Http\Controllers\OReport\RBarcodeController@report')->middleware(['auth'])->name('rbarcode');
Route::get('/get-barcode-report', 'App\Http\Controllers\OReport\RBarcodeController@getBarcodeReport')->middleware(['auth'])->name('get-barcode-report');
Route::post('/jasper-barcode-report', 'App\Http\Controllers\OReport\RBarcodeController@jasperBarcodeReport')->middleware(['auth'])->name('jasper-barcode-report');

// Report Sales Penjualan SPM
Route::get('/rsalespenjualanspm', 'App\Http\Controllers\OReport\RSalesPenjualanSPMController@report')->middleware(['auth'])->name('rsalespenjualanspm');
Route::get('/get-salespenjualanspm-report', 'App\Http\Controllers\OReport\RSalesPenjualanSPMController@getSalesPenjualanSPMReport')->middleware(['auth'])->name('get-salespenjualanspm-report');
Route::post('/jasper-salespenjualanspm-report', 'App\Http\Controllers\OReport\RSalesPenjualanSPMController@jasperSalesPenjualanSPMReport')->middleware(['auth'])->name('jasper-salespenjualanspm-report');

// Report Sales Penjualan EDC
Route::get('/rsalespenjualanedc', 'App\Http\Controllers\OReport\RSalesPenjualanEDCController@report')->middleware(['auth'])->name('rsalespenjualanedc');
Route::get('/get-salespenjualanedc-report', 'App\Http\Controllers\OReport\RSalesPenjualanEDCController@getSalesPenjualanEDCReport')->middleware(['auth'])->name('get-salespenjualanedc-report');
Route::post('/jasper-salespenjualanedc-report', 'App\Http\Controllers\OReport\RSalesPenjualanEDCController@jasperSalesPenjualanEDCReport')->middleware(['auth'])->name('jasper-salespenjualanedc-report');

// Penjualan Sales Manager
// Route::get('/rsalesmanager', 'App\Http\Controllers\OReport\RSalesManagerController@report')->middleware(['auth'])->name('rsalesmanager');
// Route::get('/get-salesmanager-report', 'App\Http\Controllers\OReport\RSalesManagerController@getSalesManagerReport')->middleware(['auth'])->name('get-salesmanager-report');
// Route::post('/jasper-salesmanager-report', 'App\Http\Controllers\OReport\RSalesManagerController@jasperSalesManagerReport')->middleware(['auth'])->name('jasper-salesmanager-report');

Route::get('/rsalesmanager', 'App\Http\Controllers\OReport\RSalesManagerController@report')->middleware(['auth'])->name('rsalesmanager');
Route::get('/get-salesmanager-report', 'App\Http\Controllers\OReport\RSalesManagerController@getSalesManagerReport')->middleware(['auth'])->name('get-salesmanager-report');
Route::post('/jasper-salesmanager-report', 'App\Http\Controllers\OReport\RSalesManagerController@jasperSalesManagerReport')->middleware(['auth'])->name('jasper-salesmanager-report');
Route::get('/get-salesmanager-report-ajax', 'App\Http\Controllers\OReport\RSalesManagerController@getSalesManagerReportAjax')->middleware(['auth'])->name('get-salesmanager-report-ajax');
// Routes tambahan untuk AJAX
Route::get('/get-salesmanager-list/{cbg}', 'App\Http\Controllers\OReport\RSalesManagerController@getSalesManagerList')->middleware(['auth']);
Route::get('/get-periode-list/{cbg}', 'App\Http\Controllers\OReport\RSalesManagerController@getPeriodeList')->middleware(['auth']);
Route::get('/search-salesmanager', 'App\Http\Controllers\OReport\RSalesManagerController@searchSalesManager')->middleware(['auth']);

// Penjualan Barang Obral VIP
Route::get('/rbarangobralvip', 'App\Http\Controllers\OReport\RBarangObralVipController@report')->middleware(['auth'])->name('rbarangobralvip');
Route::get('/get-barangobralvip-report', 'App\Http\Controllers\OReport\RBarangObralVipController@getBarangObralVipReport')->middleware(['auth'])->name('get-barangobralvip-report');
Route::post('/jasper-barangobralvip-report', 'App\Http\Controllers\OReport\RBarangObralVipController@jasperBarangObralVipReport')->middleware(['auth'])->name('jasper-barangobralvip-report');

// Penjualan Kasir Grab
Route::get('/rkasirgrab', 'App\Http\Controllers\OReport\RKasirGrabController@report')->middleware(['auth'])->name('rkasirgrab');
Route::get('/get-kasirgrab-report', 'App\Http\Controllers\OReport\RKasirGrabController@getSalesKasirGrabReport')->middleware(['auth'])->name('get-kasirgrab-report');
Route::post('/jasper-kasirgrab-report', 'App\Http\Controllers\OReport\RKasirGrabController@jasperKasirGrabReport')->middleware(['auth'])->name('jasper-kasirgrab-report');
Route::get('/get-kasirgrab', 'App\Http\Controllers\OReport\RKasirGrabController@getKasirgrab')->middleware(['auth'])->name('get-kasirgrab');

// Penerimaan Gudang
Route::get('/rpenerimaangudang', 'App\Http\Controllers\OReport\RPenerimaanGudangController@report')->middleware(['auth'])->name('rpenerimaangudang');
Route::get('/get-penerimaangudang-report', 'App\Http\Controllers\OReport\RPenerimaanGudangController@getPenerimaanGudangReport')->middleware(['auth'])->name('get-penerimaangudang-report');
Route::post('/jasper-penerimaangudang-report', 'App\Http\Controllers\OReport\RPenerimaanGudangController@jasperPenerimaanGudangReport')->middleware(['auth'])->name('jasper-penerimaangudang-report');

// Barang GrabMart
Route::get('/rbaranggrabmart', 'App\Http\Controllers\OReport\RBarangGrabMartController@report')->middleware(['auth'])->name('rbaranggrabmart');
Route::get('/get-baranggrabmart-report', 'App\Http\Controllers\OReport\RBarangGrabMartController@getBarangGrabMartReport')->middleware(['auth'])->name('get-baranggrabmart-report');
Route::post('/jasper-baranggrabmart-report', 'App\Http\Controllers\OReport\RBarangGrabMartController@jasperBarangGrabMartReport')->middleware(['auth'])->name('jasper-baranggrabmart-report');

// Barang HappyFresh
Route::get('/rbaranghappyfresh', 'App\Http\Controllers\OReport\RBarangHappyFreshController@report')->middleware(['auth'])->name('rbaranghappyfresh');
Route::get('/get-baranghappyfresh-report', 'App\Http\Controllers\OReport\RBarangHappyFreshController@getBarangHappyFreshReport')->middleware(['auth'])->name('get-baranghappyfresh-report');
Route::post('/jasper-baranghappyfresh-report', 'App\Http\Controllers\OReport\RBarangHappyFreshController@jasperBarangHappyFreshReport')->middleware(['auth'])->name('jasper-baranghappyfresh-report');

// Barang Baru Belum Datang
Route::get('/rbarangbarubelumdatang', 'App\Http\Controllers\OReport\RBarangBaruBelumDatangController@report')->middleware(['auth'])->name('rbarangbarubelumdatang');
Route::get('/get-barangbarubelumdatang-report', 'App\Http\Controllers\OReport\RBarangBaruBelumDatangController@getBarangBaruBelumDatangReport')->middleware(['auth'])->name('get-barangbarubelumdatang-report');
Route::post('/jasper-barangbarubelumdatang-report', 'App\Http\Controllers\OReport\RBarangBaruBelumDatangController@jasperBarangBaruBelumDatangReport')->middleware(['auth'])->name('jasper-barangbarubelumdatang-report');

// Survei Harga Jual Expired
Route::get('/rsurveihargajualexpired', 'App\Http\Controllers\OReport\RSurveiHargaJualExpiredController@report')->middleware(['auth'])->name('rsurveihargajualexpired');
Route::get('/get-surveihargajualexpired-report', 'App\Http\Controllers\OReport\RSurveiHargaJualExpiredController@getSurveiHargaJualExpiredReport')->middleware(['auth'])->name('get-surveihargajualexpired-report');
Route::post('/jasper-surveihargajualexpired-report', 'App\Http\Controllers\OReport\RSurveiHargaJualExpiredController@jasperSurveiHargaJualExpiredReport')->middleware(['auth'])->name('jasper-surveihargajualexpired-report');


//////////////  logistik ///////////////////
// Master Order Logistik
Route::get('/lorderlogistik', 'App\Http\Controllers\Master\LOrderLogistikController@index')->middleware(['auth'])->name('lorderlogistik');
Route::get('/lorderlogistik/edit', 'App\Http\Controllers\Master\LOrderLogistikController@edit')->middleware(['auth'])->name('lorderlogistik.edit');
Route::get('/get-lorderlogistik', 'App\Http\Controllers\Master\LOrderLogistikController@getOrderLogistik')->middleware(['auth'])->name('get-lorderlogistik');
Route::post('/lorderlogistik/store', 'App\Http\Controllers\Master\LOrderLogistikController@store')->middleware(['auth'])->name('lorderlogistik.store');
Route::get('/lorderlogistik/browse', 'App\Http\Controllers\Master\LOrderLogistikController@browse')->middleware(['auth'])->name('lorderlogistik.browse');
Route::get('/lorderlogistik/barang-detail', 'App\Http\Controllers\Master\LOrderLogistikController@getBarangDetail')->middleware(['auth'])->name('lorderlogistik.barang-detail');
Route::post('/lorderlogistik/print', 'App\Http\Controllers\Master\LOrderLogistikController@printOrder')->middleware(['auth'])->name('lorderlogistik.print');
Route::get('/lorderlogistik/cek', 'App\Http\Controllers\Master\LOrderLogistikController@cekOrder')->middleware(['auth'])->name('lorderlogistik.cek');
Route::get('/lorderlogistik/delete/{lorderlogistik}', 'App\Http\Controllers\Master\LOrderLogistikController@destroy')->middleware(['auth'])->name('lorderlogistik.delete');


// Master Memberi Tanda Bintang Logistik - Optimized Routes
Route::get('/lmemberitandabintang', 'App\Http\Controllers\Master\LMemberiTandaBintangController@index')->middleware(['auth'])->name('lmemberitandabintang');
Route::get('/lmemberitandabintang/edit', 'App\Http\Controllers\Master\LMemberiTandaBintangController@edit')->middleware(['auth'])->name('lmemberitandabintang.edit');
Route::get('/get-lmemberitandabintang', 'App\Http\Controllers\Master\LMemberiTandaBintangController@getMemberiTandaBintang')->middleware(['auth'])->name('get-lmemberitandabintang');
Route::post('/lmemberitandabintang/store', 'App\Http\Controllers\Master\LMemberiTandaBintangController@store')->middleware(['auth'])->name('lmemberitandabintang.store');
Route::get('/lmemberitandabintang/browse', 'App\Http\Controllers\Master\LMemberiTandaBintangController@browse')->middleware(['auth'])->name('lmemberitandabintang.browse');
Route::get('/lmemberitandabintang/barang-detail', 'App\Http\Controllers\Master\LMemberiTandaBintangController@getBarangDetail')->middleware(['auth'])->name('lmemberitandabintang.barang-detail');
Route::get('/lmemberitandabintang/cek', 'App\Http\Controllers\Master\LMemberiTandaBintangController@cekOrder')->middleware(['auth'])->name('lmemberitandabintang.cek');
Route::post('/lmemberitandabintang/print', 'App\Http\Controllers\Master\LMemberiTandaBintangController@printOrder')->middleware(['auth'])->name('lmemberitandabintang.print');
Route::get('/lmemberitandabintang/delete/{lmemberitandabintang}', 'App\Http\Controllers\Master\LMemberiTandaBintangController@destroy')->middleware(['auth'])->name('lmemberitandabintang.delete');


// Route untuk Logistik Entry Realisasi
Route::group(['middleware' => ['auth']], function () {
    Route::get('/lentryrealisasi', 'App\Http\Controllers\Master\LEntryRealisasiController@index')
        ->name('lentryrealisasi');
    Route::get('/lentryrealisasi/edit', 'App\Http\Controllers\Master\LEntryRealisasiController@edit')
        ->name('lentryrealisasi.edit');
    Route::get('/get-lentryrealisasi', 'App\Http\Controllers\Master\LEntryRealisasiController@getEntryRealisasi')
        ->name('get-lentryrealisasi');
    Route::post('/lentryrealisasi/store', 'App\Http\Controllers\Master\LEntryRealisasiController@store')
        ->name('lentryrealisasi.store');
    Route::get('/lentryrealisasi/browse', 'App\Http\Controllers\Master\LEntryRealisasiController@browse')
        ->name('lentryrealisasi.browse');
    Route::get('/lentryrealisasi/barang-detail', 'App\Http\Controllers\Master\LEntryRealisasiController@getBarangDetail')
        ->name('lentryrealisasi.barang-detail');
    Route::get('/lentryrealisasi/load-order', 'App\Http\Controllers\Master\LEntryRealisasiController@loadFromOrder')
        ->name('lentryrealisasi.load-order');
    Route::get('/lentryrealisasi/cek', 'App\Http\Controllers\Master\LEntryRealisasiController@cekOrder')
        ->name('lentryrealisasi.cek');
    Route::post('/lentryrealisasi/print', 'App\Http\Controllers\Master\LEntryRealisasiController@printOrder')
        ->name('lentryrealisasi.print');
    Route::get('/lentryrealisasi/delete/{lentryrealisasi}', 'App\Http\Controllers\Master\LEntryRealisasiController@destroy')
        ->name('lentryrealisasi.delete');
});


// Master Entry Transaksi Routes
Route::group(['prefix' => 'lentrytransaksi', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\Master\LEntryTransaksiController@index')->name('lentrytransaksi');
    Route::get('/edit', 'App\Http\Controllers\Master\LEntryTransaksiController@edit')->name('lentrytransaksi.edit');
    Route::get('/get-data', 'App\Http\Controllers\Master\LEntryTransaksiController@getEntryTransaksi')->name('lentrytransaksi.get-data');
    Route::post('/store', 'App\Http\Controllers\Master\LEntryTransaksiController@store')->name('lentrytransaksi.store');
    Route::delete('/delete/{no_bukti}', 'App\Http\Controllers\Master\LEntryTransaksiController@destroy')->name('lentrytransaksi.delete');
    Route::get('/browse', 'App\Http\Controllers\Master\LEntryTransaksiController@browse')->name('lentrytransaksi.browse');
    Route::get('/barang-detail', 'App\Http\Controllers\Master\LEntryTransaksiController@getBarangDetail')->name('lentrytransaksi.barang-detail');
    Route::get('/cek', 'App\Http\Controllers\Master\LEntryTransaksiController@cekOrder')->name('lentrytransaksi.cek');
    Route::post('/print', 'App\Http\Controllers\Master\LEntryTransaksiController@printOrder')->name('lentrytransaksi.print');
    Route::post('/toggle-print', 'App\Http\Controllers\Master\LEntryTransaksiController@togglePrint')->name('lentrytransaksi.toggle-print');
    Route::post('/batch-print', 'App\Http\Controllers\Master\LEntryTransaksiController@batchPrint')->name('lentrytransaksi.batch-print');
});

// Master Kartu Stock
Route::get('/lkartustock', 'App\Http\Controllers\Master\LKartuStockController@index')->middleware(['auth'])->name('lkartustock');
Route::get('/lkartustock/edit', 'App\Http\Controllers\Master\LKartuStockController@edit')->middleware(['auth'])->name('lkartustock.edit');
Route::get('/get-lkartustock', 'App\Http\Controllers\Master\LKartuStockController@getKartuStock')->middleware(['auth'])->name('get-lkartustock');
Route::post('/lkartustock/store', 'App\Http\Controllers\Master\LKartuStockController@store')->middleware(['auth'])->name('lkartustock.store');
Route::get('/lkartustock/browse', 'App\Http\Controllers\Master\LKartuStockController@browse')->middleware(['auth'])->name('lkartustock.browse');
Route::get('/lkartustock/barang-detail', 'App\Http\Controllers\Master\LKartuStockController@getBarangDetail')->middleware(['auth'])->name('lkartustock.barang-detail');
Route::get('/lkartustock/cek', 'App\Http\Controllers\Master\LKartuStockController@cekOrder')->middleware(['auth'])->name('lkartustock.cek');
Route::post('/lkartustock/print', 'App\Http\Controllers\Master\LKartuStockController@printOrder')->middleware(['auth'])->name('lkartustock.print');
Route::get('/lkartustock/delete/{lkartustock}', 'App\Http\Controllers\Master\LKartuStockController@destroy')->middleware(['auth'])->name('lkartustock.delete');

// Master Posting Order Logistik
Route::get('/lpostingorderlogistik', 'App\Http\Controllers\Master\LPostingOrderLogistikController@index')->middleware(['auth'])->name('lpostingorderlogistik');
Route::get('/lpostingorderlogistik/edit', 'App\Http\Controllers\Master\LPostingOrderLogistikController@edit')->middleware(['auth'])->name('lpostingorderlogistik.edit');
Route::get('/get-lpostingorderlogistik', 'App\Http\Controllers\Master\LPostingOrderLogistikController@getPostingOrderLogistik')->middleware(['auth'])->name('get-lpostingorderlogistik');
Route::post('/lpostingorderlogistik/store', 'App\Http\Controllers\Master\LPostingOrderLogistikController@store')->middleware(['auth'])->name('lpostingorderlogistik.store');
Route::get('/lpostingorderlogistik/browse', 'App\Http\Controllers\Master\LPostingOrderLogistikController@browse')->middleware(['auth'])->name('lpostingorderlogistik.browse');
Route::get('/lpostingorderlogistik/barang-detail', 'App\Http\Controllers\Master\LPostingOrderLogistikController@getBarangDetail')->middleware(['auth'])->name('lpostingorderlogistik.barang-detail');
Route::get('/lpostingorderlogistik/cek', 'App\Http\Controllers\Master\LPostingOrderLogistikController@cekOrder')->middleware(['auth'])->name('lpostingorderlogistik.cek');
Route::post('/lpostingorderlogistik/print', 'App\Http\Controllers\Master\LPostingOrderLogistikController@printOrder')->middleware(['auth'])->name('lpostingorderlogistik.print');
Route::get('/lpostingorderlogistik/delete/{lpostingorderlogistik}', 'App\Http\Controllers\Master\LPostingOrderLogistikController@destroy')->middleware(['auth'])->name('lpostingorderlogistik.delete');

// Master Posting Transaksi Logistik
Route::get('/lpostingtransaksilogistik', 'App\Http\Controllers\Master\LPostingTransaksiLogistikController@index')->middleware(['auth'])->name('lpostingtransaksilogistik');
Route::get('/lpostingtransaksilogistik/edit', 'App\Http\Controllers\Master\LPostingTransaksiLogistikController@edit')->middleware(['auth'])->name('lpostingtransaksilogistik.edit');
Route::get('/get-lpostingtransaksilogistik', 'App\Http\Controllers\Master\LPostingTransaksiLogistikController@getPostingTransaksiLogistik')->middleware(['auth'])->name('get-lpostingtransaksilogistik');
Route::post('/lpostingtransaksilogistik/store', 'App\Http\Controllers\Master\LPostingTransaksiLogistikController@store')->middleware(['auth'])->name('lpostingtransaksilogistik.store');
Route::get('/lpostingtransaksilogistik/browse', 'App\Http\Controllers\Master\LPostingTransaksiLogistikController@browse')->middleware(['auth'])->name('lpostingtransaksilogistik.browse');
Route::get('/lpostingtransaksilogistik/barang-detail', 'App\Http\Controllers\Master\LPostingTransaksiLogistikController@getBarangDetail')->middleware(['auth'])->name('lpostingtransaksilogistik.barang-detail');
Route::get('/lpostingtransaksilogistik/cek', 'App\Http\Controllers\Master\LPostingTransaksiLogistikController@cekOrder')->middleware(['auth'])->name('lpostingtransaksilogistik.cek');
Route::post('/lpostingtransaksilogistik/print', 'App\Http\Controllers\Master\LPostingTransaksiLogistikController@printOrder')->middleware(['auth'])->name('lpostingtransaksilogistik.print');
Route::get('/lpostingtransaksilogistik/delete/{lpostingtransaksilogistik}', 'App\Http\Controllers\Master\LPostingTransaksiLogistikController@destroy')->middleware(['auth'])->name('lpostingtransaksilogistik.delete');



//////////// promo dan hadiah //////////////

// transaksi pembayaran piutang
Route::group(['prefix' => 'phpembayaranpiutang', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\Master\PHPembayaranPiutangController@index')->name('phpembayaranpiutang');
    Route::get('/edit', 'App\Http\Controllers\Master\PHPembayaranPiutangController@edit')->name('phpembayaranpiutang.edit');
    Route::get('/get-data', 'App\Http\Controllers\Master\PHPembayaranPiutangController@getPembayaranPiutang')->name('phpembayaranpiutang/get-data');
    Route::post('/store', 'App\Http\Controllers\Master\PHPembayaranPiutangController@store')->name('phpembayaranpiutang.store');
    Route::delete('/delete/{no_bukti}', 'App\Http\Controllers\Master\PHPembayaranPiutangController@destroy')->name('phpembayaranpiutang.delete');
    Route::get('/browse', 'App\Http\Controllers\Master\PHPembayaranPiutangController@browse')->name('phpembayaranpiutang.browse');
    Route::get('/detail', 'App\Http\Controllers\Master\PHPembayaranPiutangController@getDetail')->name('phpembayaranpiutang.detail');
    Route::get('/print/{no_bukti}', 'App\Http\Controllers\Master\PHPembayaranPiutangController@printPembayaran')->name('phpembayaranpiutang.print');
});

Route::group(['prefix' => 'phpoinkresek', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\Master\PHPoinKresekController@index')->name('phpoinkresek');
    Route::post('/save-config', 'App\Http\Controllers\Master\PHPoinKresekController@saveConfig')->name('phpoinkresek.save-config');
    Route::get('/get-config', 'App\Http\Controllers\Master\PHPoinKresekController@getConfig')->name('phpoinkresek.get-config');
});

Route::group(['prefix' => 'phpoinfc', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\Master\PHPoinKresekController@indexFC')->name('phpoinfc');
    Route::post('/save-config', 'App\Http\Controllers\Master\PHPoinKresekController@saveConfig')->name('phpoinfc.save-config');
    Route::get('/get-config', 'App\Http\Controllers\Master\PHPoinKresekController@getConfig')->name('phpoinfc.get-config');
});

// Master Poin Bonus
Route::get('/phpoinbonus', 'App\Http\Controllers\Master\PHPoinBonusController@index')->middleware(['auth'])->name('phpoinbonus');
Route::get('/phpoinbonus/edit', 'App\Http\Controllers\Master\PHPoinBonusController@edit')->middleware(['auth'])->name('phpoinbonus.edit');
Route::get('/get-phpoinbonus', 'App\Http\Controllers\Master\PHPoinBonusController@getPoinBonus')->middleware(['auth'])->name('get-phpoinbonus');
Route::post('/phpoinbonus/store', 'App\Http\Controllers\Master\PHPoinBonusController@store')->middleware(['auth'])->name('phpoinbonus.store');


// Program Promosi Hadiah
Route::group(['prefix' => 'phprogrampromosihadiah', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\Master\PHProgramPromosiHadiahController@index')->name('phprogrampromosihadiah');
    Route::get('/edit', 'App\Http\Controllers\Master\PHProgramPromosiHadiahController@edit')->name('phprogrampromosihadiah.edit');
    Route::get('/get-data', 'App\Http\Controllers\Master\PHProgramPromosiHadiahController@getProgramPromosiHadiah')->name('phprogrampromosihadiah.get-data');
    Route::post('/store', 'App\Http\Controllers\Master\PHProgramPromosiHadiahController@store')->name('phprogrampromosihadiah.store');
    Route::delete('/delete/{no_bukti}', 'App\Http\Controllers\Master\PHProgramPromosiHadiahController@destroy')->name('phprogrampromosihadiah.delete');
    Route::get('/browse', 'App\Http\Controllers\Master\PHProgramPromosiHadiahController@browse')->name('phprogrampromosihadiah.browse');
    Route::get('/detail', 'App\Http\Controllers\Master\PHProgramPromosiHadiahController@getDetail')->name('phprogrampromosihadiah.detail');
    Route::get('/search', 'App\Http\Controllers\Master\PHProgramPromosiHadiahController@searchPromosi')->name('phprogrampromosihadiah.search');
    Route::post('/update-masks', 'App\Http\Controllers\Master\PHProgramPromosiHadiahController@updateMasks')->name('phprogrampromosihadiah.update-masks');
    Route::post('/clear-masks', 'App\Http\Controllers\Master\PHProgramPromosiHadiahController@clearMasks')->name('phprogrampromosihadiah.clear-masks');
    Route::post('/print', 'App\Http\Controllers\Master\PHProgramPromosiHadiahController@printPromosi')->name('phprogrampromosihadiah.print');
});


Route::group(['prefix' => 'phpengesahanfile', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\Master\PHPengesahanFileController@index')->name('phpengesahanfile');
    Route::post('/cek-file', 'App\Http\Controllers\Master\PHPengesahanFileController@cekFile')->name('phpengesahanfile.cek-file');
    Route::post('/proses-file', 'App\Http\Controllers\Master\PHPengesahanFileController@prosesFile')->name('phpengesahanfile.proses-file');
    Route::post('/get-cetak', 'App\Http\Controllers\Master\PHPengesahanFileController@getCetak')->name('phpengesahanfile.get-cetak');
    Route::post('/create-tabel', 'App\Http\Controllers\Master\PHPengesahanFileController@createTabel')->name('phpengesahanfile.create-tabel');
});

// Route untuk Laporan Promo Gayan - Penjualan
Route::get('/rpromoGayan/penjualan', 'App\Http\Controllers\Master\PHPartisipasiSupplierController@indexPenjualan')
    ->middleware(['auth'])
    ->name('rpromoGayan.penjualan');
Route::get('/rpromoGayan/peritem', 'App\Http\Controllers\Master\PHPartisipasiSupplierController@indexPerItem')
    ->middleware(['auth'])
    ->name('rpromoGayan.peritem');
Route::post('/rpromoGayan/cetak', 'App\Http\Controllers\Master\PHPartisipasiSupplierController@cetak')
    ->middleware(['auth'])
    ->name('rpromoGayan.cetak');

// Promo dan Hadiah Turun Harga
Route::group(['prefix' => 'phturanharga', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\Master\PHTurunHargaController@index')->name('phturanharga');
    Route::get('/edit', 'App\Http\Controllers\Master\PHTurunHargaController@edit')->name('phturanharga.edit');
    Route::get('/get-data', 'App\Http\Controllers\Master\PHTurunHargaController@getData')->name('phturanharga.get-data');
    Route::post('/store', 'App\Http\Controllers\Master\PHTurunHargaController@store')->name('phturanharga.store');
    Route::delete('/delete/{no_bukti}', 'App\Http\Controllers\Master\PHTurunHargaController@destroy')->name('phturanharga.delete');
    Route::get('/browse', 'App\Http\Controllers\Master\PHTurunHargaController@browse')->name('phturanharga.browse');
    Route::get('/detail', 'App\Http\Controllers\Master\PHTurunHargaController@getDetail')->name('phturanharga.detail');
    Route::post('/print', 'App\Http\Controllers\Master\PHTurunHargaController@printTurunHarga')->name('phturanharga.print');
});

// Promo dan Hadiah Harga VIP
Route::group(['prefix' => 'phhargavip', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\Master\PHHargaVIPController@index')->name('phhargavip');
    Route::get('/edit', 'App\Http\Controllers\Master\PHHargaVIPController@edit')->name('phhargavip.edit');
    Route::get('/get-data', 'App\Http\Controllers\Master\PHHargaVIPController@getData')->name('phhargavip.get-data');
    Route::post('/store', 'App\Http\Controllers\Master\PHHargaVIPController@store')->name('phhargavip.store');
    Route::get('/delete/{no_bukti}', 'App\Http\Controllers\Master\PHHargaVIPController@destroy')->name('phhargavip.delete');
    Route::get('/browse', 'App\Http\Controllers\Master\PHHargaVIPController@browse')->name('phhargavip.browse');
    Route::get('/detail', 'App\Http\Controllers\Master\PHHargaVIPController@getDetail')->name('phhargavip.detail');
    Route::get('/print', 'App\Http\Controllers\Master\PHHargaVIPController@printHargaVIP')->name('phhargavip.print');
});

// Promo dan Hadiah - Penukaran Hadiah
Route::group(['prefix' => 'phpenukaranhadiah', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\Master\PHPenukaranHadiahController@index')->name('phpenukaranhadiah');
    Route::get('/edit', 'App\Http\Controllers\Master\PHPenukaranHadiahController@edit')->name('phpenukaranhadiah.edit');
    Route::get('/get-data', 'App\Http\Controllers\Master\PHPenukaranHadiahController@getData')->name('phpenukaranhadiah.get-data');
    Route::post('/store', 'App\Http\Controllers\Master\PHPenukaranHadiahController@store')->name('phpenukaranhadiah.store');
    Route::get('/browse', 'App\Http\Controllers\Master\PHPenukaranHadiahController@browse')->name('phpenukaranhadiah.browse');
    Route::get('/detail', 'App\Http\Controllers\Master\PHPenukaranHadiahController@getDetail')->name('phpenukaranhadiah.detail');
    Route::get('/sales-data', 'App\Http\Controllers\Master\PHPenukaranHadiahController@getSalesData')->name('phpenukaranhadiah.sales-data');
    Route::post('/print', 'App\Http\Controllers\Master\PHPenukaranHadiahController@printPenukaranHadiah')->name('phpenukaranhadiah.print');
});


// Promo dan Hadiah - Daftar Barang Hadiah (Master Data)
Route::group(['prefix' => 'phdaftarbaranghadiah', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\Master\PHDaftarBarangHadiahController@index')
        ->name('phdaftarbaranghadiah');
    Route::get('/edit', 'App\Http\Controllers\Master\PHDaftarBarangHadiahController@edit')
        ->name('phdaftarbaranghadiah.edit');
    Route::get('/get-data', 'App\Http\Controllers\Master\PHDaftarBarangHadiahController@getData')
        ->name('phdaftarbaranghadiah.get-data');
    Route::post('/store', 'App\Http\Controllers\Master\PHDaftarBarangHadiahController@store')
        ->name('phdaftarbaranghadiah.store');
    Route::delete('/delete', 'App\Http\Controllers\Master\PHDaftarBarangHadiahController@delete')
        ->name('phdaftarbaranghadiah.delete');
    Route::get('/browse', 'App\Http\Controllers\Master\PHDaftarBarangHadiahController@browse')
        ->name('phdaftarbaranghadiah.browse');
    Route::get('/detail', 'App\Http\Controllers\Master\PHDaftarBarangHadiahController@getDetail')
        ->name('phdaftarbaranghadiah.detail');
    Route::get('/supplier', 'App\Http\Controllers\Master\PHDaftarBarangHadiahController@browseSupplier')
        ->name('phdaftarbaranghadiah.supplier');
    Route::post('/print', 'App\Http\Controllers\Master\PHDaftarBarangHadiahController@printDaftarBarangHadiah')
        ->name('phdaftarbaranghadiah.print');
});

// Promo dan Hadiah - Terima Hadiah Supplier
Route::group(['prefix' => 'phterimahadiahsupplier', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\Master\PHTerimaHadiahSupplierController@index')->name('phterimahadiahsupplier');
    Route::get('/edit', 'App\Http\Controllers\Master\PHTerimaHadiahSupplierController@edit')->name('phterimahadiahsupplier.edit');

    // Data operations
    Route::get('/get-data', 'App\Http\Controllers\Master\PHTerimaHadiahSupplierController@getData')->name('phterimahadiahsupplier.get-data');
    Route::post('/store', 'App\Http\Controllers\Master\PHTerimaHadiahSupplierController@store')->name('phterimahadiahsupplier.store');

    // Lookup and browse
    Route::get('/browse', 'App\Http\Controllers\Master\PHTerimaHadiahSupplierController@browse')->name('phterimahadiahsupplier.browse');
    Route::get('/detail', 'App\Http\Controllers\Master\PHTerimaHadiahSupplierController@getDetail')->name('phterimahadiahsupplier.detail');
    Route::get('/sales-data', 'App\Http\Controllers\Master\PHTerimaHadiahSupplierController@getSalesData')->name('phterimahadiahsupplier.sales-data');

    // Print operations
    Route::post('/print', 'App\Http\Controllers\Master\PHTerimaHadiahSupplierController@printTerimaHadiahSupplier')->name('phterimahadiahsupplier.print');
});

Route::group(['prefix' => 'phtransaksitransferhadiah', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\Master\PHTransaksiTransferHadiahController@index')->name('phtransaksitransferhadiah');
    Route::get('/edit', 'App\Http\Controllers\Master\PHTransaksiTransferHadiahController@edit')->name('phtransaksitransferhadiah.edit');
    Route::get('/get-data', 'App\Http\Controllers\Master\PHTransaksiTransferHadiahController@getData')->name('phtransaksitransferhadiah.get-data');
    Route::post('/store', 'App\Http\Controllers\Master\PHTransaksiTransferHadiahController@store')->name('phtransaksitransferhadiah.store');
    Route::get('/browse', 'App\Http\Controllers\Master\PHTransaksiTransferHadiahController@browse')->name('phtransaksitransferhadiah.browse');
    Route::get('/detail', 'App\Http\Controllers\Master\PHTransaksiTransferHadiahController@getDetail')->name('phtransaksitransferhadiah.detail');
    Route::post('/print', 'App\Http\Controllers\Master\PHTransaksiTransferHadiahController@printTransaksiTransferHadiah')->name('phtransaksitransferhadiah.print');
});

// Promo dan Hadiah - Terima Hadiah dari TGZ
Route::group(['prefix' => 'phterimahadiahdaritgz', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\Master\PHTerimaHadiahDariTGZController@index')->name('phterimahadiahdaritgz');
    Route::get('/get-data', 'App\Http\Controllers\Master\PHTerimaHadiahDariTGZController@getData')->name('phterimahadiahdaritgz.get-data');
    Route::post('/store', 'App\Http\Controllers\Master\PHTerimaHadiahDariTGZController@store')->name('phterimahadiahdaritgz.store');
    Route::get('/browse', 'App\Http\Controllers\Master\PHTerimaHadiahDariTGZController@browse')->name('phterimahadiahdaritgz.browse');
    Route::get('/detail', 'App\Http\Controllers\Master\PHTerimaHadiahDariTGZController@getDetail')->name('phterimahadiahdaritgz.detail');
    Route::get('/load-agenda', 'App\Http\Controllers\Master\PHTerimaHadiahDariTGZController@loadFromAgenda')->name('phterimahadiahdaritgz.load-agenda');
    Route::post('/print', 'App\Http\Controllers\Master\PHTerimaHadiahDariTGZController@printTerimaHadiahDariTGZ')->name('phterimahadiahdaritgz.print');
});

// Promo dan Hadiah - Stop Program Hadiah
Route::group(['prefix' => 'phstopprogramhadiah', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\Master\PHStopProgramHadiahController@index')
        ->name('phstopprogramhadiah');
    Route::get('/get-data', 'App\Http\Controllers\Master\PHStopProgramHadiahController@getData')
        ->name('phstopprogramhadiah.get-data');
    Route::get('/browse', 'App\Http\Controllers\Master\PHStopProgramHadiahController@browse')
        ->name('phstopprogramhadiah.browse');
    Route::get('/detail', 'App\Http\Controllers\Master\PHStopProgramHadiahController@getDetail')
        ->name('phstopprogramhadiah.detail');
    Route::get('/supplier', 'App\Http\Controllers\Master\PHStopProgramHadiahController@getSupplier')
        ->name('phstopprogramhadiah.supplier');
    Route::post('/store', 'App\Http\Controllers\Master\PHStopProgramHadiahController@store')
        ->name('phstopprogramhadiah.store');
    Route::delete('/delete', 'App\Http\Controllers\Master\PHStopProgramHadiahController@destroy')
        ->name('phstopprogramhadiah.delete');
    Route::get('/print', 'App\Http\Controllers\Master\PHStopProgramHadiahController@printStopProgramHadiah')
        ->name('phstopprogramhadiah.print');
});

Route::group(['prefix' => 'phstokopnamehadiah', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\Master\PHStokOpnameHadiahController@index')->name('phstokopnamehadiah');
    Route::get('/get-data', 'App\Http\Controllers\Master\PHStokOpnameHadiahController@getData')->name('phstokopnamehadiah.get-data');
    Route::post('/store', 'App\Http\Controllers\Master\PHStokOpnameHadiahController@store')->name('phstokopnamehadiah.store');
    Route::get('/browse', 'App\Http\Controllers\Master\PHStokOpnameHadiahController@browse')->name('phstokopnamehadiah.browse');
    Route::get('/detail', 'App\Http\Controllers\Master\PHStokOpnameHadiahController@getDetail')->name('phstokopnamehadiah.detail');
    Route::post('/print', 'App\Http\Controllers\Master\PHStokOpnameHadiahController@printStokOpnameHadiah')->name('phstokopnamehadiah.print');
});

// Promo dan Hadiah - Stok Opname Koreksi Hadiah
Route::group(['prefix' => 'phstokopnamekoreksihadiah', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\Master\PHStokOpnameKoreksiHadiahController@index')->name('phstokopnamekoreksihadiah');
    Route::get('/create', 'App\Http\Controllers\Master\PHStokOpnameKoreksiHadiahController@create')->name('phstokopnamekoreksihadiah.create');
    Route::get('/edit', 'App\Http\Controllers\Master\PHStokOpnameKoreksiHadiahController@edit')->name('phstokopnamekoreksihadiah.edit');
    Route::get('/get-data', 'App\Http\Controllers\Master\PHStokOpnameKoreksiHadiahController@getData')->name('phstokopnamekoreksihadiah.get-data');
    Route::post('/store', 'App\Http\Controllers\Master\PHStokOpnameKoreksiHadiahController@store')->name('phstokopnamekoreksihadiah.store');
    Route::get('/browse', 'App\Http\Controllers\Master\PHStokOpnameKoreksiHadiahController@browse')->name('phstokopnamekoreksihadiah.browse');
    Route::get('/detail', 'App\Http\Controllers\Master\PHStokOpnameKoreksiHadiahController@getDetail')->name('phstokopnamekoreksihadiah.detail');
    Route::get('/check-posted', 'App\Http\Controllers\Master\PHStokOpnameKoreksiHadiahController@checkPosted')->name('phstokopnamekoreksihadiah.check-posted');
    Route::post('/print', 'App\Http\Controllers\Master\PHStokOpnameKoreksiHadiahController@printStokOpnameKoreksiHadiah')->name('phstokopnamekoreksihadiah.print');
});

Route::group(['prefix' => 'phsokoreksimanual', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\Master\PHSOKoreksiManualController@index')->name('phsokoreksimanual');
    Route::get('/create', 'App\Http\Controllers\Master\PHSOKoreksiManualController@create')->name('phsokoreksimanual.create');
    Route::get('/get-data', 'App\Http\Controllers\Master\PHSOKoreksiManualController@getData')->name('phsokoreksimanual.get-data');
    Route::post('/store', 'App\Http\Controllers\Master\PHSOKoreksiManualController@store')->name('phsokoreksimanual.store');
    Route::get('/browse', 'App\Http\Controllers\Master\PHSOKoreksiManualController@browse')->name('phsokoreksimanual.browse');
    Route::get('/detail', 'App\Http\Controllers\Master\PHSOKoreksiManualController@getDetail')->name('phsokoreksimanual.detail');
    Route::post('/print', 'App\Http\Controllers\Master\PHSOKoreksiManualController@printStokOpnameKoreksiManual')->name('phsokoreksimanual.print');
});

/// posting : koreksi toko, koresksi gudang, posting koreksi manual
Route::get('/phkoreksitoko', 'App\Http\Controllers\master\PostingKoreksiController@index')->middleware(['auth'])->name('phkoreksitoko');
Route::get('/phkoreksigudang', 'App\Http\Controllers\master\PostingKoreksiController@index')->middleware(['auth'])->name('phkoreksigudang');
Route::get('/phpostingkoreksimanual', 'App\Http\Controllers\master\PostingKoreksiController@index')->middleware(['auth'])->name('phpostingkoreksimanual');
Route::get('/posting-koreksi/data', 'App\Http\Controllers\master\PostingKoreksiController@getData')->middleware(['auth'])->name('posting-koreksi.data');
Route::post('/posting-koreksi/store', 'App\Http\Controllers\master\PostingKoreksiController@store')->middleware(['auth'])->name('posting-koreksi.store');

Route::group(['prefix' => 'phundiansupplier', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\Master\PHUndianSupplierController@index')->name('phundiansupplier');
    Route::post('/save-config', 'App\Http\Controllers\Master\PHUndianSupplierController@saveConfig')->name('phundiansupplier.save-config');
    Route::get('/get-config', 'App\Http\Controllers\Master\PHUndianSupplierController@getConfig')->name('phundiansupplier.get-config');
});

Route::group(['prefix' => 'phdataundiancustomer', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\Master\PHDataUndianCustomerController@index')->name('phdataundiancustomer');
    Route::post('/save-config', 'App\Http\Controllers\Master\PHDataUndianCustomerController@saveConfig')->name('phdataundiancustomer.save-config');
    Route::get('/get-config', 'App\Http\Controllers\Master\PHDataUndianCustomerController@getConfig')->name('phdataundiancustomer.get-config');
    Route::post('/check-customer', 'App\Http\Controllers\Master\PHDataUndianCustomerController@checkCustomer')->name('phdataundiancustomer.check-customer');
});

Route::group(['prefix' => 'phhadiahcashback', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\Master\PHHadiahCashbackController@index')->name('phhadiahcashback');
    Route::post('/save-config', 'App\Http\Controllers\Master\PHHadiahCashbackController@saveConfig')->name('phhadiahcashback.save-config');
    Route::get('/get-config', 'App\Http\Controllers\Master\PHHadiahCashbackController@getConfig')->name('phhadiahcashback.get-config');
    Route::post('/check-customer', 'App\Http\Controllers\Master\PHHadiahCashbackController@checkCustomer')->name('phhadiahcashback.check-customer');
    Route::get('/print', 'App\Http\Controllers\Master\PHHadiahCashbackController@print')->name('phhadiahcashback.print');
    Route::get('/get-data', 'App\Http\Controllers\Master\PHHadiahCashbackController@getData')->name('phhadiahcashback.get-data');
});

Route::group(['prefix' => 'phentrypenyewatempat', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\Master\PHEntryPenyewaTempatController@index')->name('phentrypenyewatempat');
    Route::post('/save-config', 'App\Http\Controllers\Master\PHEntryPenyewaTempatController@saveConfig')->name('phentrypenyewatempat.save-config');
    Route::get('/get-config', 'App\Http\Controllers\Master\PHEntryPenyewaTempatController@getConfig')->name('phentrypenyewatempat.get-config');
    Route::post('/check-customer', 'App\Http\Controllers\Master\PHEntryPenyewaTempatController@checkCustomer')->name('phentrypenyewatempat.check-customer');
    Route::get('/get-rekening', 'App\Http\Controllers\Master\PHEntryPenyewaTempatController@getRekening')->name('phentrypenyewatempat.get-rekening');
    Route::get('/print-kontrak', 'App\Http\Controllers\Master\PHEntryPenyewaTempatController@printKontrak')->name('phentrypenyewatempat.print-kontrak');
    Route::post('/export-data', 'App\Http\Controllers\Master\PHEntryPenyewaTempatController@exportData')->name('phentrypenyewatempat.export-data');
});

Route::group(['prefix' => 'phlaporanpersetujuan', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\Master\PHLaporanPersetujuanController@index')->name('phlaporanpersetujuan');
    Route::post('/get-data', 'App\Http\Controllers\Master\PHLaporanPersetujuanController@getData')->name('phlaporanpersetujuan.get-data');
    Route::post('/export-excel', 'App\Http\Controllers\Master\PHLaporanPersetujuanController@exportExcel')->name('phlaporanpersetujuan.export-excel');
    Route::get('/print', 'App\Http\Controllers\Master\PHLaporanPersetujuanController@print')->middleware(['auth'])->name('phlaporanpersetujuan.print');
});


Route::get('/promo-hadiah/rincian-penagihan', 'App\Http\Controllers\Master\PHRincianPenagihanController@index')->name('phrincianpenagihan.get-data');
Route::post('/promo-hadiah/rincian-penagihan/get-data', 'App\Http\Controllers\Master\PHRincianPenagihanController@getData')->name('phrincianpenagihan.get-data');
Route::post('/promo-hadiah/rincian-penagihan/export-excel', 'App\Http\Controllers\Master\PHRincianPenagihanController@exportExcel')->name('phrincianpenagihan.export-excel');
Route::post('/promo-hadiah/rincian-penagihan/cetak-ulang', 'App\Http\Controllers\Master\PHRincianPenagihanController@cetakUlang')->name('phrincianpenagihan.cetak-ulang');


// ---------- transaksi -----------------


// =============================================
//  Transaksi Posting Kasir
// =============================================
Route::get('/tpostingkasir', 'App\Http\Controllers\OTransaksi\TPostingKasirController@index')->middleware(['auth'])->name('tpostingkasir');
Route::get('/tpostingkasir/browse', 'App\Http\Controllers\OTransaksi\TPostingKasirController@browse')->middleware(['auth'])->name('tpostingkasir_browse');
Route::get('/tpostingkasir/get-posting', 'App\Http\Controllers\OTransaksi\TPostingKasirController@gettpostingkasir_posting')->middleware(['auth'])->name('get-tpostingkasir-post');
Route::post('/tpostingkasir/posting-bulk', 'App\Http\Controllers\OTransaksi\TPostingKasirController@posting_bulk')->middleware(['auth'])->name('tpostingkasir_posting_bulk');
Route::post('/tpostingkasir/posting', 'App\Http\Controllers\OTransaksi\TPostingKasirController@posting')->middleware(['auth'])->name('tpostingkasir_posting');
Route::post('/tpostingkasir/unposting', 'App\Http\Controllers\OTransaksi\TPostingKasirController@unposting')->middleware(['auth'])->name('tpostingkasir_unposting');
Route::get('/tpostingkasir/jasper', 'App\Http\Controllers\OTransaksi\TPostingKasirController@jasper')->middleware(['auth'])->name('tpostingkasir_jasper');
// =============================================
//  End Transaksi Posting Kasir
// =============================================

// =============================================
//  Transaksi Posting Report
// =============================================
Route::get('/tpostingreport', 'App\Http\Controllers\OTransaksi\TPostingReportController@index')->middleware(['auth'])->name('tpostingreport');
Route::get('/tpostingreport/browse', 'App\Http\Controllers\OTransaksi\TPostingReportController@browse')->middleware(['auth'])->name('tpostingreport_browse');
Route::get('/tpostingreport/get-posting', 'App\Http\Controllers\OTransaksi\TPostingReportController@gettpostingreport_posting')->middleware(['auth'])->name('get-tpostingreport-post');
Route::post('/tpostingreport/posting-bulk', 'App\Http\Controllers\OTransaksi\TPostingReportController@posting_bulk')->middleware(['auth'])->name('tpostingreport_posting_bulk');
Route::post('/tpostingreport/posting', 'App\Http\Controllers\OTransaksi\TPostingReportController@posting')->middleware(['auth'])->name('tpostingreport_posting');
Route::post('/tpostingreport/unposting', 'App\Http\Controllers\OTransaksi\TPostingReportController@unposting')->middleware(['auth'])->name('tpostingreport_unposting');
Route::get('/tpostingreport/jasper', 'App\Http\Controllers\OTransaksi\TPostingReportController@jasper')->middleware(['auth'])->name('tpostingreport_jasper');
// =============================================
//  End Transaksi Posting Report
// =============================================

// =============================================
//  Transaksi Posting Akhir Bulan
// =============================================
Route::get('/tpostingakhirbulan', 'App\Http\Controllers\OTransaksi\TPostingAkhirBulanController@index')->middleware(['auth'])->name('tpostingakhirbulan');
Route::get('/tpostingakhirbulan/browse', 'App\Http\Controllers\OTransaksi\TPostingAkhirBulanController@browse')->middleware(['auth'])->name('tpostingakhirbulan_browse');
Route::get('/tpostingakhirbulan/get-posting', 'App\Http\Controllers\OTransaksi\TPostingAkhirBulanController@gettpostingakhirbulan_posting')->middleware(['auth'])->name('get-tpostingakhirbulan-post');
Route::post('/tpostingakhirbulan/posting-bulk', 'App\Http\Controllers\OTransaksi\TPostingAkhirBulanController@posting_bulk')->middleware(['auth'])->name('tpostingakhirbulan_posting_bulk');
Route::post('/tpostingakhirbulan/posting', 'App\Http\Controllers\OTransaksi\TPostingAkhirBulanController@posting')->middleware(['auth'])->name('tpostingakhirbulan_posting');
Route::post('/tpostingakhirbulan/unposting', 'App\Http\Controllers\OTransaksi\TPostingAkhirBulanController@unposting')->middleware(['auth'])->name('tpostingakhirbulan_unposting');
Route::get('/tpostingakhirbulan/jasper', 'App\Http\Controllers\OTransaksi\TPostingAkhirBulanController@jasper')->middleware(['auth'])->name('tpostingakhirbulan_jasper');
// =============================================
//  End Transaksi Posting Akhir Bulan
// =============================================


// =============================================
//  Transaksi Order Kepembelian (BIASA & TANPA DC)
// =============================================
Route::prefix('TOrderKepembelian')->middleware(['auth'])->group(function () {
    Route::get('/{jns_trans?}', 'App\Http\Controllers\OTransaksi\TOrderKepembelianController@index')
        ->name('TOrderKepembelian');
    Route::get('/{jns_trans}/edit', 'App\Http\Controllers\OTransaksi\TOrderKepembelianController@edit')
        ->name('TOrderKepembelian.edit');
    Route::get('/{jns_trans}/get-data', 'App\Http\Controllers\OTransaksi\TOrderKepembelianController@getData')
        ->name('get-TOrderKepembelian');
    Route::post('/{jns_trans}/store', 'App\Http\Controllers\OTransaksi\TOrderKepembelianController@store')
        ->name('TOrderKepembelian.store');
    Route::post('/{jns_trans}/update/{id}', 'App\Http\Controllers\OTransaksi\TOrderKepembelianController@update')
        ->name('TOrderKepembelian.update');
    Route::get('/{jns_trans}/delete/{id}', 'App\Http\Controllers\OTransaksi\TOrderKepembelianController@destroy')
        ->name('TOrderKepembelian.delete');
    Route::get('/{jns_trans}/browse', 'App\Http\Controllers\OTransaksi\TOrderKepembelianController@browse')
        ->name('TOrderKepembelian.browse');
    Route::get('/{jns_trans}/browse-hari', 'App\Http\Controllers\OTransaksi\TOrderKepembelianController@browseHari')
        ->name('TOrderKepembelian.browse_hari');
    Route::get('/{jns_trans}/browsesupz', 'App\Http\Controllers\OTransaksi\TOrderKepembelianController@browsesupz')
        ->name('TOrderKepembelian.browsesupz');
    Route::get('/{jns_trans}/ceksup', 'App\Http\Controllers\OTransaksi\TOrderKepembelianController@ceksup')
        ->name('TOrderKepembelian.ceksup');
    Route::get('/{jns_trans}/get-select-kodes', 'App\Http\Controllers\OTransaksi\TOrderKepembelianController@getSelectKodes')
        ->name('TOrderKepembelian.get-select-kodes');
    Route::post('/{jns_trans}/proses', 'App\Http\Controllers\OTransaksi\TOrderKepembelianController@proses')
        ->name('TOrderKepembelian.proses');
    Route::post('/{jns_trans}/posting', 'App\Http\Controllers\OTransaksi\TOrderKepembelianController@posting')
        ->name('TOrderKepembelian.posting');
    Route::get('/{jns_trans}/validate-barang', 'App\Http\Controllers\OTransaksi\TOrderKepembelianController@validateBarang')
        ->name('TOrderKepembelian.validate-barang');
    Route::get('/{jns_trans}/browse-page', 'App\Http\Controllers\OTransaksi\TOrderKepembelianController@browsePage')
        ->name('TOrderKepembelian.browse-page');
    Route::get('/{jns_trans}/print', 'App\Http\Controllers\OTransaksi\TOrderKepembelianController@cetak')
        ->name('TOrderKepembelian.print');
});
// =============================================
//  End Transaksi Order Kepembelian (BIASA & TANPA DC)
// =============================================

// =============================================
//  Transaksi Order Koreksi Pembelian
// =============================================
Route::group(['prefix' => 'torderkoreksipembelian', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\OTransaksi\TOrderKoreksiPembelianController@index')->name('torderkoreksipembelian');
    Route::get('/edit', 'App\Http\Controllers\OTransaksi\TOrderKoreksiPembelianController@edit')->name('torderkoreksipembelian.edit');
    Route::get('/get-data', 'App\Http\Controllers\OTransaksi\TOrderKoreksiPembelianController@getOrderKoreksiPembelian')->name('torderkoreksipembelian.get-data');
    Route::post('/store', 'App\Http\Controllers\OTransaksi\TOrderKoreksiPembelianController@store')->name('torderkoreksipembelian.store');
    Route::get('/delete/{no_bukti}', 'App\Http\Controllers\OTransaksi\TOrderKoreksiPembelianController@destroy')->name('torderkoreksipembelian.delete');
    Route::get('/browse', 'App\Http\Controllers\OTransaksi\TOrderKoreksiPembelianController@browse')->name('torderkoreksipembelian.browse');
    Route::get('/detail', 'App\Http\Controllers\OTransaksi\TOrderKoreksiPembelianController@getDetail')->name('torderkoreksipembelian.detail');
    Route::get('/print', 'App\Http\Controllers\OTransaksi\TOrderKoreksiPembelianController@printOrderKoreksiPembelian')->name('torderkoreksipembelian.print');
    Route::post('/proses-sub', 'App\Http\Controllers\OTransaksi\TOrderKoreksiPembelianController@prosesSub')->name('proses.sub');

});
// =============================================
//  End Transaksi Order Koreksi Pembelian
// =============================================

// =============================================
//  Transaksi Cetak SP Kode 5
// =============================================
Route::get('/tcetakspkode5', 'App\Http\Controllers\OTransaksi\TCetakSPKode5Controller@index')->middleware(['auth'])->name('tcetakspkode5');
Route::get('/tcetakspkode5/browse', 'App\Http\Controllers\OTransaksi\TCetakSPKode5Controller@browse')->middleware(['auth'])->name('tcetakspkode5_browse');
Route::get('/tcetakspkode5/get-data', 'App\Http\Controllers\OTransaksi\TCetakSPKode5Controller@getTCetakSPKode5Data')->middleware(['auth'])->name('get-tcetakspkode5-data');
Route::post('/tcetakspkode5/store', 'App\Http\Controllers\OTransaksi\TCetakSPKode5Controller@store')->middleware(['auth'])->name('tcetakspkode5_store');
Route::post('/tcetakspkode5/load-from-bukti', 'App\Http\Controllers\OTransaksi\TCetakSPKode5Controller@loadFromBukti')->middleware(['auth'])->name('tcetakspkode5_load_bukti');
Route::post('/tcetakspkode5/proses', 'App\Http\Controllers\OTransaksi\TCetakSPKode5Controller@proses')->middleware(['auth'])->name('tcetakspkode5_proses');
Route::get('/tcetakspkode5/jasper', 'App\Http\Controllers\OTransaksi\TCetakSPKode5Controller@jasper')->middleware(['auth'])->name('tcetakspkode5_jasper');
Route::get('/tcetakspkode5/cetak_ulang', 'App\Http\Controllers\OTransaksi\TCetakSPKode5Controller@cetak_ulang')->middleware(['auth'])->name('tcetakspkode5.cetak_ulang');

// =============================================
//  End Transaksi Cetak SP Kode 5
// =============================================

// =============================================
//  Transaksi Buat Orderan Kue Basah
// =============================================
Route::get('/tbuatorderankuebasah', 'App\Http\Controllers\OTransaksi\TBuatOrderanKueBasahController@index')->middleware(['auth'])->name('tbuatorderankuebasah');
Route::get('/tbuatorderankuebasah/browse', 'App\Http\Controllers\OTransaksi\TBuatOrderanKueBasahController@browse')->middleware(['auth'])->name('tbuatorderankuebasah_browse');
Route::get('/tbuatorderankuebasah/get-data', 'App\Http\Controllers\OTransaksi\TBuatOrderanKueBasahController@getTBuatOrderanKueBasahData')->middleware(['auth'])->name('get-tbuatorderankuebasah-data');
Route::post('/tbuatorderankuebasah/store', 'App\Http\Controllers\OTransaksi\TBuatOrderanKueBasahController@store')->middleware(['auth'])->name('tbuatorderankuebasah_store');
Route::post('/tbuatorderankuebasah/load-from-bukti', 'App\Http\Controllers\OTransaksi\TBuatOrderanKueBasahController@loadFromBukti')->middleware(['auth'])->name('tbuatorderankuebasah_load_bukti');
Route::post('/tbuatorderankuebasah/proses', 'App\Http\Controllers\OTransaksi\TBuatOrderanKueBasahController@proses')->middleware(['auth'])->name('tbuatorderankuebasah_proses');
Route::get('/tbuatorderankuebasah/jasper', 'App\Http\Controllers\OTransaksi\TBuatOrderanKueBasahController@jasper')->middleware(['auth'])->name('tbuatorderankuebasah_jasper');
// =============================================
//  End Transaksi Buat Orderan Kue Basah
// =============================================

// =============================================
//  Transaksi SPKO ke TGZ
// =============================================
Route::group(['prefix' => 'tspkoketgz', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\OTransaksi\TSPKOKeTGZController@index')->name('tspkoketgz');
    Route::get('/edit', 'App\Http\Controllers\OTransaksi\TSPKOKeTGZController@edit')->name('tspkoketgz.edit');
    Route::get('/get-data', 'App\Http\Controllers\OTransaksi\TSPKOKeTGZController@getSPKOKeTGZ')->name('tspkoketgz.get-data');
    Route::post('/store', 'App\Http\Controllers\OTransaksi\TSPKOKeTGZController@store')->name('tspkoketgz.store');
    Route::delete('/delete/{no_bukti}', 'App\Http\Controllers\OTransaksi\TSPKOKeTGZController@destroy')->name('tspkoketgz.delete');
    Route::get('/browse', 'App\Http\Controllers\OTransaksi\TSPKOKeTGZController@browse')->name('tspkoketgz.browse');
    Route::get('/detail', 'App\Http\Controllers\OTransaksi\TSPKOKeTGZController@getDetail')->name('tspkoketgz.detail');
    Route::post('/print', 'App\Http\Controllers\OTransaksi\TSPKOKeTGZController@printSPKOKeTGZ')->name('tspkoketgz.print');
});
// =============================================
//  End Transaksi SPKO ke TGZ
// =============================================

// =============================================
//  Transaksi SPKO ke DC Tunjungsari
// =============================================
Route::group(['prefix' => 'tspkokedctunjungsari', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\OTransaksi\TSPKOKeDCTunjungsariController@index')->name('tspkokedctunjungsari');
    Route::get('/edit', 'App\Http\Controllers\OTransaksi\TSPKOKeDCTunjungsariController@edit')->name('tspkokedctunjungsari.edit');
    Route::get('/get-data', 'App\Http\Controllers\OTransaksi\TSPKOKeDCTunjungsariController@getData')->name('tspkokedctunjungsari.get-data');
    Route::post('/store', 'App\Http\Controllers\OTransaksi\TSPKOKeDCTunjungsariController@store')->name('tspkokedctunjungsari.store');
    Route::delete('/delete/{no_bukti}', 'App\Http\Controllers\OTransaksi\TSPKOKeDCTunjungsariController@destroy')->name('tspkokedctunjungsari.delete');
    Route::get('/browse', 'App\Http\Controllers\OTransaksi\TSPKOKeDCTunjungsariController@browse')->name('tspkokedctunjungsari.browse');
    Route::get('/detail', 'App\Http\Controllers\OTransaksi\TSPKOKeDCTunjungsariController@getDetail')->name('tspkokedctunjungsari.detail');
    Route::post('/proses', 'App\Http\Controllers\OTransaksi\TSPKOKeDCTunjungsariController@proses')->name('tspkokedctunjungsari.proses');
    Route::post('/print', 'App\Http\Controllers\OTransaksi\TSPKOKeDCTunjungsariController@print')->name('tspkokedctunjungsari.print');
    Route::post('/export', 'App\Http\Controllers\OTransaksi\TSPKOKeDCTunjungsariController@export')->name('tspkokedctunjungsari.export');
});
// =============================================
//  End Transaksi SPKO ke DC Tunjungsari
// =============================================

// =============================================
//  Transaksi Orderan Pelanggan
// =============================================
Route::group(['prefix' => 'torderanpelanggan', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\OTransaksi\TOrderanPelangganController@index')->name('torderanpelanggan');
    Route::get('/edit', 'App\Http\Controllers\OTransaksi\TOrderanPelangganController@edit')->name('torderanpelanggan.edit');
    Route::get('/get-data', 'App\Http\Controllers\OTransaksi\TOrderanPelangganController@getOrderanPelanggan')->name('torderanpelanggan.get-data');
    Route::post('/store', 'App\Http\Controllers\OTransaksi\TOrderanPelangganController@store')->name('torderanpelanggan.store');
    Route::delete('/delete/{no_bukti}', 'App\Http\Controllers\OTransaksi\TOrderanPelangganController@destroy')->name('torderanpelanggan.delete');
    Route::get('/browse', 'App\Http\Controllers\OTransaksi\TOrderanPelangganController@browse')->name('torderanpelanggan.browse');
    Route::get('/detail', 'App\Http\Controllers\OTransaksi\TOrderanPelangganController@getDetail')->name('torderanpelanggan.detail');
    Route::post('/print', 'App\Http\Controllers\OTransaksi\TOrderanPelangganController@printOrderanPelanggan')->name('torderanpelanggan.print');
});
// =============================================
//  End Transaksi Orderan Pelanggan
// =============================================

// =============================================
//  Transaksi Orderan Manual
// =============================================
Route::group(['prefix' => 'torderanmanual', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\OTransaksi\TOrderanManualController@index')->name('torderanmanual');
    Route::get('/edit', 'App\Http\Controllers\OTransaksi\TOrderanManualController@edit')->name('torderanmanual.edit');
    Route::get('/get-data', 'App\Http\Controllers\OTransaksi\TOrderanManualController@getOrderanManual')->name('torderanmanual.get-data');
    Route::post('/store', 'App\Http\Controllers\OTransaksi\TOrderanManualController@store')->name('torderanmanual.store');
    Route::delete('/delete/{no_bukti}', 'App\Http\Controllers\OTransaksi\TOrderanManualController@destroy')->name('torderanmanual.delete');
    Route::get('/browse', 'App\Http\Controllers\OTransaksi\TOrderanManualController@browse')->name('torderanmanual.browse');
    Route::get('/detail', 'App\Http\Controllers\OTransaksi\TOrderanManualController@getDetail')->name('torderanmanual.detail');
    Route::post('/print', 'App\Http\Controllers\OTransaksi\TOrderanManualController@printOrderanManual')->name('torderanmanual.print');
});
// =============================================
//  End Transaksi Orderan Manual
// =============================================

// =============================================
//  Transaksi Orderan Toko GD Transit
// =============================================
Route::group(['prefix' => 'torderantokogdtransit', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\OTransaksi\TOrderanTokoGDTransitController@index')->name('torderantokogdtransit');
    Route::get('/edit', 'App\Http\Controllers\OTransaksi\TOrderanTokoGDTransitController@edit')->name('torderantokogdtransit.edit');
    Route::get('/get-data', 'App\Http\Controllers\OTransaksi\TOrderanTokoGDTransitController@getOrderanTokoGDTransit')->name('torderantokogdtransit.get-data');
    Route::post('/store', 'App\Http\Controllers\OTransaksi\TOrderanTokoGDTransitController@store')->name('torderantokogdtransit.store');
    Route::delete('/delete/{no_bukti}', 'App\Http\Controllers\OTransaksi\TOrderanTokoGDTransitController@destroy')->name('torderantokogdtransit.delete');
    Route::get('/browse', 'App\Http\Controllers\OTransaksi\TOrderanTokoGDTransitController@browse')->name('torderantokogdtransit.browse');
    Route::get('/detail', 'App\Http\Controllers\OTransaksi\TOrderanTokoGDTransitController@getDetail')->name('torderantokogdtransit.detail');
    Route::get('/ambil-data', 'App\Http\Controllers\OTransaksi\TOrderanTokoGDTransitController@ambilData')->name('torderantokogdtransit.ambilData');
    Route::get('/print', 'App\Http\Controllers\OTransaksi\TOrderanTokoGDTransitController@printOrderanTokoGDTransit')->name('torderantokogdtransit.print');
});
// =============================================
//  End Transaksi Orderan Toko GD Transit
// =============================================

// =============================================
//  Transaksi Cetak LBKK / LBTAT
// =============================================
Route::get('/tcetaklbkklbtat', 'App\Http\Controllers\OTransaksi\TCetakLBKKLBTATController@index')->middleware(['auth'])->name('tcetaklbkklbtat');
Route::get('/tcetaklbkklbtat/get-data', 'App\Http\Controllers\OTransaksi\TCetakLBKKLBTATController@getTCetakLBKKLBTATData')->middleware(['auth'])->name('get-tcetaklbkklbtat-data');
Route::post('/tcetaklbkklbtat/proses', 'App\Http\Controllers\OTransaksi\TCetakLBKKLBTATController@proses')->middleware(['auth'])->name('tcetaklbkklbtat_proses');
Route::get('/tcetaklbkklbtat/jasper', 'App\Http\Controllers\OTransaksi\TCetakLBKKLBTATController@jasper')->middleware(['auth'])->name('tcetaklbkklbtat_jasper');
// =============================================
//  End Transaksi Cetak LBKK / LBTAT
// =============================================

// =============================================
//  Transaksi Cetak LBKK / LBTAT Baru
// =============================================
Route::get('/tcetaklbkklbtatbaru', 'App\Http\Controllers\OTransaksi\TCetakLBKKLBTATBaruController@index')->middleware(['auth'])->name('tcetaklbkklbtatbaru');
Route::get('/tcetaklbkklbtatbaru/get-data', 'App\Http\Controllers\OTransaksi\TCetakLBKKLBTATBaruController@getTCetakLBKKLBTATBaruData')->middleware(['auth'])->name('get-tcetaklbkklbtatbaru-data');
Route::post('/tcetaklbkklbtatbaru/proses', 'App\Http\Controllers\OTransaksi\TCetakLBKKLBTATBaruController@proses')->middleware(['auth'])->name('tcetaklbkklbtatbaru_proses');
Route::get('/tcetaklbkklbtatbaru/jasper', 'App\Http\Controllers\OTransaksi\TCetakLBKKLBTATBaruController@jasper')->middleware(['auth'])->name('tcetaklbkklbtatbaru_jasper');
// =============================================
//  End Transaksi Cetak LBKK / LBTAT Baru
// =============================================

// =============================================
//  Transaksi Penanganan LBTAT
// =============================================
Route::get('/tpenangananlbtat', 'App\Http\Controllers\OTransaksi\TPenangananLBTATController@index')
    ->middleware(['auth'])
    ->name('tpenangananlbtat');
Route::get('/tpenangananlbtat/get-data', 'App\Http\Controllers\OTransaksi\TPenangananLBTATController@getTPenangananLBTATData')
    ->middleware(['auth'])
    ->name('get-tpenangananlbtat-data');
Route::post('/tpenangananlbtat/proses', 'App\Http\Controllers\OTransaksi\TPenangananLBTATController@proses')
    ->middleware(['auth'])
    ->name('tpenangananlbtat_proses');
Route::get('/tpenangananlbtat/jasper', 'App\Http\Controllers\OTransaksi\TPenangananLBTATController@jasper')
    ->middleware(['auth'])
    ->name('tpenangananlbtat_jasper');
// =============================================
//  Transaksi Proses Stock Opname
// =============================================
Route::get('/tprosesstockopname', 'App\Http\Controllers\OTransaksi\TPenangananLBTATController@index')
    ->middleware(['auth'])
    ->name('tprosesstockopname');
Route::get('/tprosesstockopname/get-data', 'App\Http\Controllers\OTransaksi\TPenangananLBTATController@getTProsesStockOpnameData')
    ->middleware(['auth'])
    ->name('get-tprosesstockopname-data');
Route::post('/tprosesstockopname/create', 'App\Http\Controllers\OTransaksi\TPenangananLBTATController@createStockOpname')
    ->middleware(['auth'])
    ->name('tprosesstockopname.create');
Route::post('/tprosesstockopname/proses', 'App\Http\Controllers\OTransaksi\TPenangananLBTATController@proses')
    ->middleware(['auth'])
    ->name('tprosesstockopname_proses');
Route::get('/tprosesstockopname/jasper', 'App\Http\Controllers\OTransaksi\TPenangananLBTATController@jasper')
    ->middleware(['auth'])
    ->name('tprosesstockopname_jasper');
Route::get('/tprosesstockopname/export', 'App\Http\Controllers\OTransaksi\TPenangananLBTATController@exportSO')
    ->middleware(['auth'])
    ->name('tprosesstockopname.export');
Route::post('/tprosesstockopname/import', 'App\Http\Controllers\OTransaksi\TPenangananLBTATController@importSO')
    ->middleware(['auth'])
    ->name('tprosesstockopname.import');
Route::post('/tprosesstockopname/checkall', 'App\Http\Controllers\OTransaksi\TPenangananLBTATController@checkAll')
    ->middleware(['auth'])
    ->name('tprosesstockopname.checkall');
// =============================================
//  End Transaksi Penanganan LBTAT
// =============================================


// =============================================
//  Transaksi Koreksi Toko Manual
// =============================================
Route::group(['prefix' => 'tkoreksitokomanual', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\OTransaksi\TKoreksiTokoManualController@index')
        ->name('tkoreksitokomanual');
    Route::get('/edit', 'App\Http\Controllers\OTransaksi\TKoreksiTokoManualController@edit')
        ->name('tkoreksitokomanual.edit');
    Route::get('/get-data', 'App\Http\Controllers\OTransaksi\TKoreksiTokoManualController@getKoreksiTokoManual')
        ->name('tkoreksitokomanual.get-data');
    Route::post('/store', 'App\Http\Controllers\OTransaksi\TKoreksiTokoManualController@store')
        ->name('tkoreksitokomanual.store');
    Route::delete('/delete/{no_bukti}', 'App\Http\Controllers\OTransaksi\TKoreksiTokoManualController@destroy')
        ->name('tkoreksitokomanual.delete');
    Route::get('/browse', 'App\Http\Controllers\OTransaksi\TKoreksiTokoManualController@browse')
        ->name('tkoreksitokomanual.browse');
    Route::get('/detail', 'App\Http\Controllers\OTransaksi\TKoreksiTokoManualController@getDetail')
        ->name('tkoreksitokomanual.detail');
    Route::get('/print', 'App\Http\Controllers\OTransaksi\TKoreksiTokoManualController@printKoreksiTokoManual')
        ->name('tkoreksitokomanual.print');
});
// =============================================
//  End Transaksi Koreksi Toko Manual
// =============================================

// =============================================
//  Transaksi Posting Koreksi Toko
// =============================================
Route::get('/tpostingkoreksitoko', 'App\Http\Controllers\OTransaksi\TPostingKoreksiTokoController@index')->middleware(['auth'])->name('tpostingkoreksitoko');
Route::get('/tpostingkoreksitoko/get-posting', 'App\Http\Controllers\OTransaksi\TPostingKoreksiTokoController@gettpostingkoreksitoko_posting')->middleware(['auth'])->name('get-tpostingkoreksitoko-post');
Route::post('/tpostingkoreksitoko/posting-bulk', 'App\Http\Controllers\OTransaksi\TPostingKoreksiTokoController@posting_bulk')->middleware(['auth'])->name('tpostingkoreksitoko_posting_bulk');
Route::get('/tpostingkoreksitoko/jasper', 'App\Http\Controllers\OTransaksi\TPostingKoreksiTokoController@jasper')->middleware(['auth'])->name('tpostingkoreksitoko_jasper');
// =============================================
//  End Transaksi Posting Koreksi Toko
// =============================================


// =============================================
//  Transaksi Pengembalian ke Gudang (Gudang Umum & DC Tanjungsari)
// =============================================
Route::group(['prefix' => 'tpengembaliankegudang', 'middleware' => ['auth']], function () {
    Route::get('/{tipe?}', 'App\Http\Controllers\OTransaksi\TPengembalianKeGudangController@index')
        ->where('tipe', 'gudangumum|dctanjungsari')
        ->name('tpengembaliankegudang');
    Route::get('/{tipe?}/edit', 'App\Http\Controllers\OTransaksi\TPengembalianKeGudangController@edit')
        ->where('tipe', 'gudangumum|dctanjungsari')
        ->name('tpengembaliankegudang.edit');
    Route::get('/{tipe?}/get-data', 'App\Http\Controllers\OTransaksi\TPengembalianKeGudangController@getPengembalianKeGudang')
        ->where('tipe', 'gudangumum|dctanjungsari')
        ->name('tpengembaliankegudang.get-data');
    Route::post('/{tipe?}/store', 'App\Http\Controllers\OTransaksi\TPengembalianKeGudangController@store')
        ->where('tipe', 'gudangumum|dctanjungsari')
        ->name('tpengembaliankegudang.store');
    Route::delete('/{tipe?}/delete/{no_bukti}', 'App\Http\Controllers\OTransaksi\TPengembalianKeGudangController@destroy')
        ->where('tipe', 'gudangumum|dctanjungsari')
        ->name('tpengembaliankegudang.delete');
    Route::get('/{tipe?}/browse', 'App\Http\Controllers\OTransaksi\TPengembalianKeGudangController@browse')
        ->where('tipe', 'gudangumum|dctanjungsari')
        ->name('tpengembaliankegudang.browse');
    Route::get('/{tipe?}/detail', 'App\Http\Controllers\OTransaksi\TPengembalianKeGudangController@getDetail')
        ->where('tipe', 'gudangumum|dctanjungsari')
        ->name('tpengembaliankegudang.detail');
    Route::post('/{tipe?}/print', 'App\Http\Controllers\OTransaksi\TPengembalianKeGudangController@printPengembalianKeGudang')
        ->where('tipe', 'gudangumum|dctanjungsari')
        ->name('tpengembaliankegudang.print');
    Route::post('/{tipe?}/update-print', 'App\Http\Controllers\OTransaksi\TPengembalianKeGudangController@updatePrint')
        ->where('tipe', 'gudangumum|dctanjungsari')
        ->name('tpengembaliankegudang.update-print');
});
// =============================================
//  End Transaksi Pengembalian ke Gudang
// =============================================


// =============================================
//  Transaksi Proses Stock Opname
// =============================================
Route::group(['prefix' => 'tprosesstockopname', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\OTransaksi\TProsesStockOpnameController@index')
        ->name('tprosesstockopname');
    Route::get('/edit', 'App\Http\Controllers\OTransaksi\TProsesStockOpnameController@edit')
        ->name('tprosesstockopname.edit');
    Route::get('/get-data', 'App\Http\Controllers\OTransaksi\TProsesStockOpnameController@getProsesStockOpname')
        ->name('tprosesstockopname.get-data');
    Route::post('/store', 'App\Http\Controllers\OTransaksi\TProsesStockOpnameController@store')
        ->name('tprosesstockopname.store');
    Route::delete('/delete/{no_bukti}', 'App\Http\Controllers\OTransaksi\TProsesStockOpnameController@destroy')
        ->name('tprosesstockopname.delete');
    Route::get('/browse', 'App\Http\Controllers\OTransaksi\TProsesStockOpnameController@browse')
        ->name('tprosesstockopname.browse');
    Route::get('/detail', 'App\Http\Controllers\OTransaksi\TProsesStockOpnameController@getDetail')
        ->name('tprosesstockopname.detail');
    Route::post('/print', 'App\Http\Controllers\OTransaksi\TProsesStockOpnameController@printProsesStockOpname')
        ->name('tprosesstockopname.print');
});
// =============================================
//  End Transaksi Proses Stock Opname
// =============================================

// =============================================
//  Transaksi Koreksi Stok Opname
// =============================================
Route::group(['prefix' => 'tkoreksistokopname', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\OTransaksi\TKoreksiStokOpnameController@index')
        ->name('tkoreksistokopname');
    Route::get('/edit', 'App\Http\Controllers\OTransaksi\TKoreksiStokOpnameController@edit')
        ->name('tkoreksistokopname.edit');
    Route::get('/get-data', 'App\Http\Controllers\OTransaksi\TKoreksiStokOpnameController@getKoreksiStokOpname')
        ->name('tkoreksistokopname.get-data');
    Route::post('/store', 'App\Http\Controllers\OTransaksi\TKoreksiStokOpnameController@store')
        ->name('tkoreksistokopname.store');
    Route::delete('/delete/{no_bukti}', 'App\Http\Controllers\OTransaksi\TKoreksiStokOpnameController@destroy')
        ->name('tkoreksistokopname.delete');
    Route::get('/browse', 'App\Http\Controllers\OTransaksi\TKoreksiStokOpnameController@browse')
        ->name('tkoreksistokopname.browse');
    Route::get('/detail', 'App\Http\Controllers\OTransaksi\TKoreksiStokOpnameController@getDetail')
        ->name('tkoreksistokopname.detail');
    Route::post('/print', 'App\Http\Controllers\OTransaksi\TKoreksiStokOpnameController@printKoreksiStokOpname')
        ->name('tkoreksistokopname.print');
});
// =============================================
//  End Transaksi Koreksi Stok Opname
// =============================================

// =============================================
//  Transaksi Posting Stok Opname
// =============================================
Route::get('/tpostingstokopname', 'App\Http\Controllers\OTransaksi\TPostingStokOpnameController@index')
    ->middleware(['auth'])
    ->name('tpostingstokopname');
Route::get('/tpostingstokopname/get-posting', 'App\Http\Controllers\OTransaksi\TPostingStokOpnameController@gettpostingstokopname_posting')
    ->middleware(['auth'])
    ->name('get-tpostingstokopname-post');
Route::post('/tpostingstokopname/posting-bulk', 'App\Http\Controllers\OTransaksi\TPostingStokOpnameController@posting_bulk')
    ->middleware(['auth'])
    ->name('tpostingstokopname_posting_bulk');
Route::get('/tpostingstokopname/jasper', 'App\Http\Controllers\OTransaksi\TPostingStokOpnameController@jasper')
    ->middleware(['auth'])
    ->name('tpostingstokopname_jasper');
// =============================================
//  End Transaksi Posting Stok Opname
// =============================================


// =============================================
//  Transaksi Pelaksanaan Perubahan Harga VIP
// =============================================
Route::get('/ubahhrgvip', 'App\Http\Controllers\OTransaksi\UbahhrgvipController@index')->middleware(['auth'])->name('ubahhrgvip');
Route::get('/get-ubahhrgvip', 'App\Http\Controllers\OTransaksi\UbahhrgvipController@getUbahhrgvip')->middleware(['auth'])->name('get-ubahhrgvip');
Route::get('ubahhrg/print', 'App\Http\Controllers\OTransaksi\UbahhrgvipController@print')->middleware(['auth'])->name('ubahhrgvip.print');
// =============================================
//  End Transaksi Pelaksanaan Perubahan Harga VIP
// =============================================

// =============================================
//  Transaksi Rekap Label Harian
// =============================================
Route::get('/rkplabel', 'App\Http\Controllers\OTransaksi\RkplabelController@index')->middleware(['auth'])->name('rkplabel');
Route::get('/get-rkplabel', 'App\Http\Controllers\OTransaksi\RkplabelController@getRkplabel')->middleware(['auth'])->name('get-rkplabel');
// =============================================
//  End Transaksi Rekap Label Harian
// =============================================

// =============================================
// =============================================
//  Transaksi Data Barang 6C
// =============================================
Route::get('/tdatabarang6c', 'App\Http\Controllers\OTransaksi\TDataBarang6CController@index')
    ->middleware(['auth'])
    ->name('tdatabarang6c');
Route::post('/tdatabarang6c/cari', 'App\Http\Controllers\OTransaksi\TDataBarang6CController@cari_barang')
    ->middleware(['auth'])
    ->name('tdatabarang6c_cari');
Route::get('/tdatabarang6c/detail/{kd_brg}', 'App\Http\Controllers\OTransaksi\TDataBarang6CController@detail')
    ->middleware(['auth'])
    ->name('tdatabarang6c_detail');
// =============================================
//  End Transaksi Data Barang 6C
// =============================================

// =============================================
//  Transaksi Data Barang 1-1
// =============================================
Route::get('/tdatabarang1-1', 'App\Http\Controllers\OTransaksi\TDataBarang11Controller@index')
    ->middleware(['auth'])
    ->name('tdatabarang1-1');
Route::post('/tdatabarang1-1/cari', 'App\Http\Controllers\OTransaksi\TDataBarang11Controller@cari_barang')
    ->middleware(['auth'])
    ->name('tdatabarang1-1_cari');
Route::get('/tdatabarang1-1/detail/{kd_brg}', 'App\Http\Controllers\OTransaksi\TDataBarang11Controller@detail')
    ->middleware(['auth'])
    ->name('tdatabarang1-1_detail');
// =============================================
//  End Transaksi Data Barang 1-1
// =============================================

// =============================================
//  Transaksi Ambil Data Survey Penjualan
// =============================================
Route::get('/tambildatasurveypenjualan', 'App\Http\Controllers\OTransaksi\TAmbilDataSurveyPenjualanController@index')
    ->middleware(['auth'])
    ->name('tambildatasurveypenjualan');

Route::post('/tambildatasurveypenjualan/cari', 'App\Http\Controllers\OTransaksi\TAmbilDataSurveyPenjualanController@cari_data')
    ->middleware(['auth'])
    ->name('tambildatasurveypenjualan_cari');

Route::get('/tambildatasurveypenjualan/detail/{id}', 'App\Http\Controllers\OTransaksi\TAmbilDataSurveyPenjualanController@detail')
    ->middleware(['auth'])
    ->name('tambildatasurveypenjualan_detail');
// =============================================
//  End Transaksi Ambil Data Survey Penjualan
// =============================================

// =============================================
//  Transaksi Input Survey Penjualan
// =============================================
Route::group(['prefix' => 'tinputsurveypenjualan', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\OTransaksi\TInputSurveyPenjualanController@index')
        ->name('tinputsurveypenjualan');
    Route::get('/edit', 'App\Http\Controllers\OTransaksi\TInputSurveyPenjualanController@edit')
        ->name('tinputsurveypenjualan.edit');
    Route::get('/get-data', 'App\Http\Controllers\OTransaksi\TInputSurveyPenjualanController@getInputSurveyPenjualan')
        ->name('tinputsurveypenjualan.get-data');
    Route::post('/store', 'App\Http\Controllers\OTransaksi\TInputSurveyPenjualanController@store')
        ->name('tinputsurveypenjualan.store');
    Route::delete('/delete/{no_bukti}', 'App\Http\Controllers\OTransaksi\TInputSurveyPenjualanController@destroy')
        ->name('tinputsurveypenjualan.delete');
    Route::get('/browse', 'App\Http\Controllers\OTransaksi\TInputSurveyPenjualanController@browse')
        ->name('tinputsurveypenjualan.browse');
    Route::get('/detail', 'App\Http\Controllers\OTransaksi\TInputSurveyPenjualanController@getDetail')
        ->name('tinputsurveypenjualan.detail');
    Route::post('/print', 'App\Http\Controllers\OTransaksi\TInputSurveyPenjualanController@printInputSurveyPenjualan')
        ->name('tinputsurveypenjualan.print');
});
// =============================================
//  End Transaksi Input Survey Penjualan
// =============================================

// =============================================
// Transaksi Posting Survey Penjualan
// =============================================
Route::get('/tpostingsurveypenjualan', 'App\Http\Controllers\OTransaksi\TPostingSurveyPenjualanController@index')
    ->middleware(['auth'])
    ->name('tpostingsurveypenjualan');
Route::post('/tpostingsurveypenjualan/cari', 'App\Http\Controllers\OTransaksi\TPostingSurveyPenjualanController@cari_data')
    ->middleware(['auth'])
    ->name('tpostingsurveypenjualan_cari');
Route::get('/tpostingsurveypenjualan/detail/{id}', 'App\Http\Controllers\OTransaksi\TPostingSurveyPenjualanController@detail')
    ->middleware(['auth'])
    ->name('tpostingsurveypenjualan_detail');
// =============================================
// End Transaksi Posting Survey Penjualan
// =============================================

// =============================================
// Master Perubahan Barang KMS
// =============================================

Route::get('/perkem', 'App\Http\Controllers\Master\PerkemController@index')->middleware(['auth'])->name('perkem');
Route::post('/perkem/store', 'App\Http\Controllers\Master\PerkemController@store')->middleware(['auth'])->name('perkem/store');
Route::get('/rperkem', 'App\Http\Controllers\OReport\RPerkemController@report')->middleware(['auth'])->name('rperkem');

Route::get('/get-perkem', 'App\Http\Controllers\Master\PerkemController@getPerkem')->middleware(['auth'])->name('get-perkem');


Route::post('/perkem/tampil', 'App\Http\Controllers\Master\PerkemController@tampil')->middleware(['auth'])->name('perkem-tampil');
Route::post('/perkem/proses', 'App\Http\Controllers\Master\PerkemController@proses')->middleware(['auth'])->name('perkem-proses');

// =============================================
// End Master Perubahan Barang KMS
// =============================================


// =============================================
// Master Greet
// =============================================

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

// =============================================
// End Master Greet
// =============================================

// =============================================
// Master Export Import SQL
// =============================================

Route::get('/expim', 'App\Http\Controllers\Master\ExpimController@expim')->middleware(['auth'])->name('expim');
Route::post('/expim/import', 'App\Http\Controllers\Master\ExpimController@import')->middleware(['auth'])->name('expim.import');

// =============================================
// End Master Export Import SQL
// =============================================

// =============================================
// Master Export Jadwal SO
// =============================================

Route::get('/exso', 'App\Http\Controllers\Master\ExsoController@exso')->middleware(['auth'])->name('exso');
Route::post('/exso/export', 'App\Http\Controllers\Master\ExsoController@export')->middleware(['auth'])->name('exso.export');

// =============================================
// End Master Export Jadwal SO
// =============================================


// =============================================
//  Transaksi Obral Super Market
// =============================================
Route::group(['prefix' => 'tobralsupermarket', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\OTransaksi\TObralSuperMarketController@index')
        ->name('tobralsupermarket');
    Route::get('/edit', 'App\Http\Controllers\OTransaksi\TObralSuperMarketController@edit')
        ->name('tobralsupermarket.edit');
    Route::get('/get-data', 'App\Http\Controllers\OTransaksi\TObralSuperMarketController@getObralSuperMarket')
        ->name('tobralsupermarket.get-data');
    Route::post('/store', 'App\Http\Controllers\OTransaksi\TObralSuperMarketController@store')
        ->name('tobralsupermarket.store');
    Route::delete('/delete/{no_bukti}', 'App\Http\Controllers\OTransaksi\TObralSuperMarketController@destroy')
        ->name('tobralsupermarket.delete');
    Route::get('/browse', 'App\Http\Controllers\OTransaksi\TObralSuperMarketController@browse')
        ->name('tobralsupermarket.browse');
    Route::get('/detail', 'App\Http\Controllers\OTransaksi\TObralSuperMarketController@getDetail')
        ->name('tobralsupermarket.detail');
    Route::post('/print', 'App\Http\Controllers\OTransaksi\TObralSuperMarketController@printObralSuperMarket')
        ->name('tobralsupermarket.print');
    Route::get('/get-diskon-info', 'App\Http\Controllers\OTransaksi\TObralSuperMarketController@getDiskonInfo')
        ->name('tobralsupermarket.get-diskon-info');
});
// =============================================
//  End Transaksi Obral Super Market
// =============================================


// =============================================
//  Transaksi Entry Flash Sale
// =============================================
Route::group(['prefix' => 'tentryflashsale', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\OTransaksi\TObralSuperMarketController@index')
        ->name('tentryflashsale');
    Route::get('/edit', 'App\Http\Controllers\OTransaksi\TObralSuperMarketController@edit')
        ->name('tentryflashsale.edit');
    Route::get('/get-data', 'App\Http\Controllers\OTransaksi\TObralSuperMarketController@getEntryFlashSale')
        ->name('tentryflashsale.get-data');
    Route::post('/store', 'App\Http\Controllers\OTransaksi\TObralSuperMarketController@store')
        ->name('tentryflashsale.store');
    Route::delete('/delete/{no_bukti}', 'App\Http\Controllers\OTransaksi\TObralSuperMarketController@destroy')
        ->name('tentryflashsale.delete');
    Route::get('/browse', 'App\Http\Controllers\OTransaksi\TObralSuperMarketController@browse')
        ->name('tentryflashsale.browse');
    Route::get('/detail', 'App\Http\Controllers\OTransaksi\TObralSuperMarketController@getDetail')
        ->name('tentryflashsale.detail');
    Route::post('/print', 'App\Http\Controllers\OTransaksi\TObralSuperMarketController@printEntryFlashSale')
        ->name('tentryflashsale.print');
    Route::get('/get-diskon-info', 'App\Http\Controllers\OTransaksi\TObralSuperMarketController@getDiskonInfo')
        ->name('tentryflashsale.get-diskon-info');
});
// =============================================
//  End Transaksi Entry Flash Sale
// =============================================

// =============================================
//  Transaksi Pelaksanaan Obral Super
// =============================================
Route::get('/tpelaksanaanobralsuper', 'App\Http\Controllers\OTransaksi\TPelaksanaanObralSuperController@index')
    ->middleware(['auth'])
    ->name('tpelaksanaanobralsuper');
Route::post('/tpelaksanaanobralsuper/cari', 'App\Http\Controllers\OTransaksi\TPelaksanaanObralSuperController@cari_data')
    ->middleware(['auth'])
    ->name('tpelaksanaanobralsuper_cari');
Route::get('/tpelaksanaanobralsuper/detail/{id}', 'App\Http\Controllers\OTransaksi\TPelaksanaanObralSuperController@detail')
    ->middleware(['auth'])
    ->name('tpelaksanaanobralsuper_detail');
Route::get('/tpelaksanaanobralsuper/tampil', 'App\Http\Controllers\OTransaksi\TPelaksanaanObralSuperController@tampil')
    ->middleware(['auth'])
    ->name('tpelaksanaanobralsuper_tampil');
// =============================================
//  End Transaksi Pelaksanaan Obral Super
// =============================================

// =============================================
//  Transaksi Posting Flash Sale
// =============================================
Route::get('/postingflashsale', 'App\Http\Controllers\OTransaksi\TPelaksanaanObralSuperController@index')
    ->middleware(['auth'])
    ->name('postingflashsale');
Route::post('/postingflashsale/cari', 'App\Http\Controllers\OTransaksi\TPelaksanaanObralSuperController@cari_data')
    ->middleware(['auth'])
    ->name('postingflashsale_cari');
Route::get('/postingflashsale/detail/{id}', 'App\Http\Controllers\OTransaksi\TPelaksanaanObralSuperController@detail')
    ->middleware(['auth'])
    ->name('postingflashsale_detail');
// =============================================
//  End Transaksi Posting Flash Sale
// =============================================

// =============================================
//  Transaksi Obral Food Centre
// =============================================
Route::group(['prefix' => 'tobralfoodcentre', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\OTransaksi\TObralFoodCentreController@index')
        ->name('tobralfoodcentre');
    Route::get('/edit', 'App\Http\Controllers\OTransaksi\TObralFoodCentreController@edit')
        ->name('tobralfoodcentre.edit');
    Route::get('/get-data', 'App\Http\Controllers\OTransaksi\TObralFoodCentreController@getObralFoodCentre')
        ->name('tobralfoodcentre.get-data');
    Route::post('/store', 'App\Http\Controllers\OTransaksi\TObralFoodCentreController@store')
        ->name('tobralfoodcentre.store');
    Route::delete('/delete/{no_bukti}', 'App\Http\Controllers\OTransaksi\TObralFoodCentreController@destroy')
        ->name('tobralfoodcentre.delete');
    Route::get('/browse', 'App\Http\Controllers\OTransaksi\TObralFoodCentreController@browse')
        ->name('tobralfoodcentre.browse');
    Route::get('/detail', 'App\Http\Controllers\OTransaksi\TObralFoodCentreController@getDetail')
        ->name('tobralfoodcentre.detail');
    Route::post('/print', 'App\Http\Controllers\OTransaksi\TObralFoodCentreController@printObralFoodCentre')
        ->name('tobralfoodcentre.print');
    Route::get('/get-diskon-info', 'App\Http\Controllers\OTransaksi\TObralFoodCentreController@getDiskonInfo')
        ->name('tobralfoodcentre.get-diskon-info');
});
// =============================================
//  End Transaksi Obral Food Centre
// =============================================


// =============================================
//  Transaksi Laporan Barang Flash Sale
// =============================================
Route::get('/laporanbarangflashsale', 'App\Http\Controllers\OTransaksi\TLaporanBarangFlashSaleController@index')
    ->middleware(['auth'])
    ->name('laporanbarangflashsale');

Route::post('/laporanbarangflashsale/cari', 'App\Http\Controllers\OTransaksi\TLaporanBarangFlashSaleController@cari_data')
    ->middleware(['auth'])
    ->name('laporanbarangflashsale_cari');

Route::get('/laporanbarangflashsale/detail/{id}', 'App\Http\Controllers\OTransaksi\TLaporanBarangFlashSaleController@detail')
    ->middleware(['auth'])
    ->name('laporanbarangflashsale_detail');
// =============================================
//  End Transaksi Laporan Barang Flash Sale
// =============================================


// =============================================
// Transaksi Pembelian Beda Harga
// =============================================
Route::get('/tpembelianbedaharga', 'App\Http\Controllers\OTransaksi\TPembelianBedaHargaController@index')
->middleware(['auth'])
->name('pembelianbedaharga');
Route::post('/tpembelianbedaharga/cari', 'App\Http\Controllers\OTransaksi\TPembelianBedaHargaController@cari_data')
->middleware(['auth'])
->name('pembelianbedaharga_cari');
Route::post('/tpembelianbedaharga/update-gol', 'App\Http\Controllers\OTransaksi\TPembelianBedaHargaController@update_gol')
->middleware(['auth'])
->name('pembelianbedaharga_update_gol');
Route::post('/tpembelianbedaharga/proses', 'App\Http\Controllers\OTransaksi\TPembelianBedaHargaController@proses')
->middleware(['auth'])
->name('pembelianbedaharga_proses');
Route::post('/tpembelianbedaharga/cetak', 'App\Http\Controllers\OTransaksi\TPembelianBedaHargaController@cetak')
->middleware(['auth'])
->name('pembelianbedaharga_cetak');
Route::post('/tpembelianbedaharga/lookup-supplier', 'App\Http\Controllers\OTransaksi\TPembelianBedaHargaController@lookup_supplier')
->middleware(['auth'])
->name('pembelianbedaharga_lookup_supplier');
Route::post('/tpembelianbedaharga/lookup-barang', 'App\Http\Controllers\OTransaksi\TPembelianBedaHargaController@lookup_barang')
->middleware(['auth'])
->name('pembelianbedaharga_lookup_barang');
// =============================================
// End Transaksi Pembelian Beda Harga
// =============================================



// =============================================
//  Transaksi Pengajuan Harga Fresh Food
// =============================================
Route::get('/tpengajuanhargafreshfood', 'App\Http\Controllers\OTransaksi\TPengajuanHargaFreshFoodController@index')
    ->middleware(['auth'])
    ->name('pengajuanhargafreshfood');
Route::post('/tpengajuanhargafreshfood/cari', 'App\Http\Controllers\OTransaksi\TPengajuanHargaFreshFoodController@cari_data')
    ->middleware(['auth'])
    ->name('pengajuanhargafreshfood_cari');
Route::post('/tpengajuanhargafreshfood/proses', 'App\Http\Controllers\OTransaksi\TPengajuanHargaFreshFoodController@proses')
    ->middleware(['auth'])
    ->name('pengajuanhargafreshfood_proses');
Route::get('/tpengajuanhargafreshfood/detail/{no_bukti}', 'App\Http\Controllers\OTransaksi\TPengajuanHargaFreshFoodController@detail')
    ->middleware(['auth'])
    ->name('pengajuanhargafreshfood_detail');
// =============================================
//  End Transaksi Pengajuan Harga Fresh Food
// =============================================


// =============================================
// Transaksi Tidak Order Fresh Food
// =============================================
Route::get('/ttidakorderfreshfood', 'App\Http\Controllers\OTransaksi\TTidakOrderFreshFoodController@index')
->middleware(['auth'])
->name('tidakorderfreshfood');
Route::post('/ttidakorderfreshfood/cari', 'App\Http\Controllers\OTransaksi\TTidakOrderFreshFoodController@cari_data')
->middleware(['auth'])
->name('tidakorderfreshfood_cari');
Route::post('/ttidakorderfreshfood/proses', 'App\Http\Controllers\OTransaksi\TTidakOrderFreshFoodController@proses')
->middleware(['auth'])
->name('tidakorderfreshfood_proses');
Route::get('/ttidakorderfreshfood/detail/{no_bukti}', 'App\Http\Controllers\OTransaksi\TTidakOrderFreshFoodController@detail')
->middleware(['auth'])
->name('tidakorderfreshfood_detail');
Route::post('/ttidakorderfreshfood/cari-barang', 'App\Http\Controllers\OTransaksi\TTidakOrderFreshFoodController@cari_barang')
->middleware(['auth'])
->name('tidakorderfreshfood_cari_barang');
Route::post('/ttidakorderfreshfood/lookup-barang', 'App\Http\Controllers\OTransaksi\TTidakOrderFreshFoodController@lookup_barang')
->middleware(['auth'])
->name('tidakorderfreshfood_lookup_barang');
// =============================================
// End Transaksi Tidak Order Fresh Food
// =============================================

// =============================================
// Transaksi Order Lebih Fresh Food
// =============================================
Route::get('/torderlebihfreshfood', 'App\Http\Controllers\OTransaksi\TOrderLebihFreshFoodController@index')
->middleware(['auth'])
->name('orderlebihfreshfood');
Route::post('/torderlebihfreshfood/cari', 'App\Http\Controllers\OTransaksi\TOrderLebihFreshFoodController@cari_data')
->middleware(['auth'])
->name('orderlebihfreshfood_cari');
Route::post('/torderlebihfreshfood/lookup-barang', 'App\Http\Controllers\OTransaksi\TOrderLebihFreshFoodController@lookup_barang')
->middleware(['auth'])
->name('orderlebihfreshfood_lookup_barang');
Route::post('/torderlebihfreshfood/proses', 'App\Http\Controllers\OTransaksi\TOrderLebihFreshFoodController@proses')
->middleware(['auth'])
->name('orderlebihfreshfood_proses');
// =============================================
// End Transaksi Order Lebih Fresh Food
// =============================================

// // =============================================
// //  Transaksi Order Lebih Fresh Food
// // =============================================
// Route::get('/torderlebihfreshfood', 'App\Http\Controllers\OTransaksi\TOrderLebihFreshFoodController@index')
//     ->middleware(['auth'])
//     ->name('orderlebihfreshfood');
// Route::post('/torderlebihfreshfood/cari', 'App\Http\Controllers\OTransaksi\TOrderLebihFreshFoodController@cari_data')
//     ->middleware(['auth'])
//     ->name('orderlebihfreshfood_cari');
// Route::post('/torderlebihfreshfood/proses', 'App\Http\Controllers\OTransaksi\TOrderLebihFreshFoodController@proses')
//     ->middleware(['auth'])
//     ->name('orderlebihfreshfood_proses');
// // =============================================
// //  End Transaksi Order Lebih Fresh Food
// // =============================================

// =============================================
//  Transaksi Entry Presentase ORD FF Online
// =============================================
Route::get('/tentrypresentaseordffonline', 'App\Http\Controllers\OTransaksi\TEntryPresentaseORDFFOnlineController@index')
    ->middleware(['auth'])
    ->name('entrypresentaseordffonline');

Route::get('/tentrypresentaseordffonline/edit/{namafile}', 'App\Http\Controllers\OTransaksi\TEntryPresentaseORDFFOnlineController@edit')
    ->middleware(['auth'])
    ->name('entrypresentaseordffonline_edit');

Route::post('/tentrypresentaseordffonline/cari', 'App\Http\Controllers\OTransaksi\TEntryPresentaseORDFFOnlineController@cari_data')
    ->middleware(['auth'])
    ->name('entrypresentaseordffonline_cari');

Route::get('/tentrypresentaseordffonline/detail/{namafile}', 'App\Http\Controllers\OTransaksi\TEntryPresentaseORDFFOnlineController@detail')
    ->middleware(['auth'])
    ->name('entrypresentaseordffonline_detail');

Route::post('/tentrypresentaseordffonline/proses', 'App\Http\Controllers\OTransaksi\TEntryPresentaseORDFFOnlineController@proses')
    ->middleware(['auth'])
    ->name('entrypresentaseordffonline_proses');
// =============================================
//  End Transaksi Entry Presentase ORD FF Online
// =============================================


// =============================================
// Transaksi Order Lebih Hari Raya Online
// =============================================
Route::get('/torderlebihharirayaonline', 'App\Http\Controllers\OTransaksi\TOrderLebihHariRayaOnlineController@index')
->middleware(['auth'])
->name('orderlebihharirayaonline');
Route::get('/torderlebihharirayaonline/edit/{namafile}', 'App\Http\Controllers\OTransaksi\TOrderLebihHariRayaOnlineController@edit')
->middleware(['auth'])
->name('orderlebihharirayaonline_edit');
Route::post('/torderlebihharirayaonline/cari', 'App\Http\Controllers\OTransaksi\TOrderLebihHariRayaOnlineController@cari_data')
->middleware(['auth'])
->name('orderlebihharirayaonline_cari');
Route::post('/torderlebihharirayaonline/detail/{namafile}', 'App\Http\Controllers\OTransaksi\TOrderLebihHariRayaOnlineController@detail')
->middleware(['auth'])
->name('orderlebihharirayaonline_detail');
Route::post('/torderlebihharirayaonline/proses', 'App\Http\Controllers\OTransaksi\TOrderLebihHariRayaOnlineController@proses')
->middleware(['auth'])
->name('orderlebihharirayaonline_proses');
Route::post('/torderlebihharirayaonline/lookup-barang', 'App\Http\Controllers\OTransaksi\TOrderLebihHariRayaOnlineController@lookup_barang')
->middleware(['auth'])
->name('orderlebihharirayaonline_lookup_barang');
Route::post('/api/search-barang', 'App\Http\Controllers\OTransaksi\TOrderLebihHariRayaOnlineController@searchBarang')
->middleware(['auth']);
// =============================================
// End Transaksi Order Lebih Hari Raya Online
// =============================================

// =============================================
//  Transaksi LHKode3 Trial
// =============================================
Route::get('/tlhkkode3trial', 'App\Http\Controllers\OTransaksi\TLHKode3TrialController@index')
    ->middleware(['auth'])
    ->name('lhkkode3trial');
Route::post('/tlhkkode3trial/cari', 'App\Http\Controllers\OTransaksi\TLHKode3TrialController@cari_data')
    ->middleware(['auth'])
    ->name('lhkkode3trial_cari');
Route::post('/tlhkkode3trial/ambil-data', 'App\Http\Controllers\OTransaksi\TLHKode3TrialController@ambil_data')
    ->middleware(['auth'])
    ->name('lhkkode3trial_ambil');
Route::post('/tlhkkode3trial/proses', 'App\Http\Controllers\OTransaksi\TLHKode3TrialController@proses')
    ->middleware(['auth'])
    ->name('lhkkode3trial_proses');
Route::get('/tlhkkode3trial/detail', 'App\Http\Controllers\OTransaksi\TLHKode3TrialController@detail')
    ->middleware(['auth'])
    ->name('lhkkode3trial_detail');
// =============================================
//  End Transaksi LHKode3 Trial
// =============================================


// =============================================
//  Transaksi LP HFF Mingguan
// =============================================
Route::get('/tlphffmingguan', 'App\Http\Controllers\OTransaksi\TLPHFFMingguanController@index')
    ->middleware(['auth'])
    ->name('lphffmingguan');
Route::post('/tlphffmingguan/cari', 'App\Http\Controllers\OTransaksi\TLPHFFMingguanController@cari_data')
    ->middleware(['auth'])
    ->name('lphffmingguan_cari');
Route::post('/tlphffmingguan/ambil-data', 'App\Http\Controllers\OTransaksi\TLPHFFMingguanController@ambil_data')
    ->middleware(['auth'])
    ->name('lphffmingguan_ambil');
Route::post('/tlphffmingguan/proses', 'App\Http\Controllers\OTransaksi\TLPHFFMingguanController@proses')
    ->middleware(['auth'])
    ->name('lphffmingguan_proses');
Route::get('/tlphffmingguan/detail/{no_bukti}', 'App\Http\Controllers\OTransaksi\TLPHFFMingguanController@detail')
    ->middleware(['auth'])
    ->name('lphffmingguan_detail');
// =============================================
//  End Transaksi LP HFF Mingguan
// =============================================


// =============================================
// Transaksi Kirim LPH K3
// =============================================
Route::get('/tkirimlphk3', 'App\Http\Controllers\OTransaksi\TKirimLPHK3Controller@index')
    ->middleware(['auth'])
    ->name('kirimlphk3');
Route::post('/tkirimlphk3/cari', 'App\Http\Controllers\OTransaksi\TKirimLPHK3Controller@cari_data')
    ->middleware(['auth'])
    ->name('kirimlphk3_cari');
Route::post('/tkirimlphk3/proses', 'App\Http\Controllers\OTransaksi\TKirimLPHK3Controller@proses')
    ->middleware(['auth'])
    ->name('kirimlphk3_proses');
Route::get('/tkirimlphk3/detail/{no_bukti?}', 'App\Http\Controllers\OTransaksi\TKirimLPHK3Controller@detail')
    ->middleware(['auth'])
    ->name('kirimlphk3_detail');
// =============================================
// End Transaksi Kirim LPH K3
// =============================================


// =============================================
//  Transaksi Update Master Barang
// =============================================

// Halaman Index
Route::get('/tupdatemasterbarang', 'App\Http\Controllers\OTransaksi\TUpdateMasterBarangController@index')
    ->middleware(['auth'])
    ->name('updatemasterbarang');
Route::post('/tupdatemasterbarang/cari', 'App\Http\Controllers\OTransaksi\TUpdateMasterBarangController@cari_data')
    ->middleware(['auth'])
    ->name('updatemasterbarang_cari');
Route::post('/tupdatemasterbarang/proses', 'App\Http\Controllers\OTransaksi\TUpdateMasterBarangController@proses')
    ->middleware(['auth'])
    ->name('updatemasterbarang_proses');
// =============================================
//  End Transaksi Update Master Barang
// =============================================


// =============================================
//  Transaksi Update DTB
// =============================================

// Halaman Index
Route::get('/tupdatedtb', 'App\Http\Controllers\OTransaksi\TUpdateDTBController@index')
    ->middleware(['auth'])
    ->name('updatedtb');
Route::post('/tupdatedtb/cari', 'App\Http\Controllers\OTransaksi\TUpdateDTBController@cari_data')
    ->middleware(['auth'])
    ->name('updatedtb_cari');
Route::post('/tupdatedtb/proses', 'App\Http\Controllers\OTransaksi\TUpdateDTBController@proses')
    ->middleware(['auth'])
    ->name('updatedtb_proses');
Route::post('/tupdatedtb/import', 'App\Http\Controllers\OTransaksi\TUpdateDTBController@importExcel')
    ->middleware(['auth'])
    ->name('updatedtb_import');
Route::get('/tupdatedtb/export', 'App\Http\Controllers\OTransaksi\TUpdateDTBController@exportExcel')
    ->middleware(['auth'])
    ->name('updatedtb_export');
// =============================================
//  End Transaksi Update DTB
// =============================================


// =============================================
//  Transaksi Ambil Order Kode 3
// =============================================
// Halaman Index
Route::get('/tambilorderkode3', 'App\Http\Controllers\OTransaksi\TAmbilOrderKode3Controller@index')
    ->middleware(['auth'])
    ->name('ambilorderkode3');
Route::post('/tambilorderkode3/cari', 'App\Http\Controllers\OTransaksi\TAmbilOrderKode3Controller@cari_data')
    ->middleware(['auth'])
    ->name('ambilorderkode3_cari');
Route::post('/tambilorderkode3/proses', 'App\Http\Controllers\OTransaksi\TAmbilOrderKode3Controller@proses')
    ->middleware(['auth'])
    ->name('ambilorderkode3_proses');
Route::post('/tambilorderkode3/search', 'App\Http\Controllers\OTransaksi\TAmbilOrderKode3Controller@searchBarang')
    ->middleware(['auth'])
    ->name('ambilorderkode3_search');
// =============================================
//  End Transaksi Ambil Order Kode 3
// =============================================


// =============================================
//  Transaksi Kirim Data Timbangan
// =============================================
Route::get('/tkirimdatatimbangan', 'App\Http\Controllers\OTransaksi\TKirimDataTimbanganController@index')
    ->middleware(['auth'])
    ->name('kirimdatatimbangan');
Route::post('/tkirimdatatimbangan/cari', 'App\Http\Controllers\OTransaksi\TKirimDataTimbanganController@cari_data')
    ->middleware(['auth'])
    ->name('kirimdatatimbangan_cari');
Route::post('/tkirimdatatimbangan/cari-semua', 'App\Http\Controllers\OTransaksi\TKirimDataTimbanganController@cari_semua')
    ->middleware(['auth'])
    ->name('kirimdatatimbangan_cari_semua');
Route::post('/tkirimdatatimbangan/proses', 'App\Http\Controllers\OTransaksi\TKirimDataTimbanganController@proses')
    ->middleware(['auth'])
    ->name('kirimdatatimbangan_proses');
Route::post('/tkirimdatatimbangan/search', 'App\Http\Controllers\OTransaksi\TKirimDataTimbanganController@searchBarang')
    ->middleware(['auth'])
    ->name('kirimdatatimbangan_search');
// =============================================
//  End Transaksi Kirim Data Timbangan
// =============================================



// =============================================
//  Transaksi Barang Prioritas
// =============================================
Route::get('/tbarangprioritas', 'App\Http\Controllers\OTransaksi\TBarangPrioritasController@index')
    ->middleware(['auth'])
    ->name('barangprioritas');

Route::post('/tbarangprioritas/tampil', 'App\Http\Controllers\OTransaksi\TBarangPrioritasController@tampil')
    ->middleware(['auth'])
    ->name('barangprioritas_tampil');

Route::post('/tbarangprioritas/refresh', 'App\Http\Controllers\OTransaksi\TBarangPrioritasController@refresh')
    ->middleware(['auth'])
    ->name('barangprioritas_refresh');

Route::get('/tbarangprioritas/print', 'App\Http\Controllers\OTransaksi\TBarangPrioritasController@print')
    ->middleware(['auth'])
    ->name('barangprioritas_print');

Route::post('/tbarangprioritas/proses', 'App\Http\Controllers\OTransaksi\TBarangPrioritasController@proses')
    ->middleware(['auth'])
    ->name('barangprioritas_proses');
// =============================================
//  End Transaksi Barang Prioritas
// =============================================

// =============================================
//  Transaksi Pengajuan Perubahan
// =============================================
Route::get('/tpengajuanperubahan', 'App\Http\Controllers\OTransaksi\TPengajuanPerubahanController@index')
    ->middleware(['auth'])
    ->name('pengajuanperubahan');
Route::get('/tpengajuanperubahan/edit/{no_bukti}', 'App\Http\Controllers\OTransaksi\TPengajuanPerubahanController@edit')
    ->middleware(['auth'])
    ->name('pengajuanperubahan_edit');
Route::post('/tpengajuanperubahan/cari', 'App\Http\Controllers\OTransaksi\TPengajuanPerubahanController@cari_data')
    ->middleware(['auth'])
    ->name('pengajuanperubahan_cari');
Route::post('/tpengajuanperubahan/detail/{no_bukti}', 'App\Http\Controllers\OTransaksi\TPengajuanPerubahanController@detail')
    ->middleware(['auth'])
    ->name('pengajuanperubahan_detail');
Route::post('/tpengajuanperubahan/proses', 'App\Http\Controllers\OTransaksi\TPengajuanPerubahanController@proses')
    ->middleware(['auth'])
    ->name('pengajuanperubahan_proses');
Route::post('/tpengajuanperubahan/search-barang', 'App\Http\Controllers\OTransaksi\TPengajuanPerubahanController@searchBarang')
    ->middleware(['auth'])
    ->name('pengajuanperubahan_search_barang');
Route::get('/tpengajuanperubahan/tampil-barang', 'App\Http\Controllers\OTransaksi\TPengajuanPerubahanController@tampilBarang')
    ->middleware(['auth'])
    ->name('pengajuanperubahan_tampil_barang');
Route::get('/tpengajuanperubahan/print', 'App\Http\Controllers\OTransaksi\TPengajuanPerubahanController@print')->middleware(['auth'])->name('tpengajuanperubahan.print');
Route::post('/tpengajuanperubahan/usulan-save', 'App\Http\Controllers\OTransaksi\TPengajuanPerubahanController@usulan')->middleware(['auth'])->name('tpengajuanperubahan.usulan-save');

// =============================================
//  End Transaksi Pengajuan Perubahan
// =============================================


// =============================================
//  Transaksi Masa Tarik
// =============================================
Route::get('/tmasatarik', 'App\Http\Controllers\OTransaksi\TMasaTarikController@index')
    ->middleware(['auth'])
    ->name('masatarik');
Route::post('/tmasatarik/cari', 'App\Http\Controllers\OTransaksi\TMasaTarikController@cari_data')
    ->middleware(['auth'])
    ->name('masatarik_cari');
Route::post('/tmasatarik/cari-semua', 'App\Http\Controllers\OTransaksi\TMasaTarikController@cari_semua')
    ->middleware(['auth'])
    ->name('masatarik_cari_semua');
Route::post('/tmasatarik/proses', 'App\Http\Controllers\OTransaksi\TMasaTarikController@proses')
    ->middleware(['auth'])
    ->name('masatarik_proses');
Route::post('/tmasatarik/search', 'App\Http\Controllers\OTransaksi\TMasaTarikController@searchBarang')
    ->middleware(['auth'])
    ->name('masatarik_search');
// =============================================
//  End Transaksi Masa Tarik
// =============================================


// =============================================
//  Transaksi Perubahan Masa Tarik
// =============================================
Route::get('/permat', 'App\Http\Controllers\OTransaksi\PermatController@index')->middleware(['auth'])->name('permat');
Route::post('/permat/store', 'App\Http\Controllers\OTransaksi\PermatController@store')->middleware(['auth'])->name('permat/store');
// GET permat
Route::get('/get-permat', 'App\Http\Controllers\OTransaksi\PermatController@getPermat')->middleware(['auth'])->name('get-permat');

// Dynamic Brg
Route::get('/permat/edit', 'App\Http\Controllers\OTransaksi\PermatController@edit')->middleware(['auth'])->name('permat.edit');
Route::post('/permat/update/{permat}', 'App\Http\Controllers\OTransaksi\PermatController@update')->middleware(['auth'])->name('permat.update');
Route::get('/permat/delete/{permat}', 'App\Http\Controllers\OTransaksi\PermatController@destroy')->middleware(['auth'])->name('permat.delete');
// =============================================
//  End Transaksi Perubahan Masa Traik
// =============================================

// =============================================
// Transaksi Proses Barang Baru (Usulan perubahan harga)
// =============================================

Route::get('/perharga', 'App\Http\Controllers\OTransaksi\PerhargaController@index')->middleware(['auth'])->name('perharga');
Route::post('/perharga/store', 'App\Http\Controllers\OTransaksi\PerhargaController@store')->middleware(['auth'])->name('perharga/store');
Route::get('/rperharga', 'App\Http\Controllers\OReport\RPerhargaController@report')->middleware(['auth'])->name('rperharga');

Route::get('/get-perharga', 'App\Http\Controllers\OTransaksi\PerhargaController@getPerharga')->middleware(['auth'])->name('get-perharga');


Route::post('/perharga/tampil', 'App\Http\Controllers\OTransaksi\PerhargaController@tampil')->middleware(['auth'])->name('perharga-tampil');
Route::post('/perharga/proses', 'App\Http\Controllers\OTransaksi\PerhargaController@proses')->middleware(['auth'])->name('perharga-proses');

// =============================================
// End Transaksi Proses Barang Baru (Usulan perubahan harga)
// =============================================

// =============================================
// Transaksi Perubahan Barcode
// =============================================

Route::get('/perbar', 'App\Http\Controllers\OTransaksi\PerbarController@index')->middleware(['auth'])->name('perbar');
Route::post('/perbar/store', 'App\Http\Controllers\OTransaksi\PerbarController@store')->middleware(['auth'])->name('perbar/store');
Route::get('/rperbar', 'App\Http\Controllers\OReport\RPerbarController@report')->middleware(['auth'])->name('rperbar');

Route::get('/get-perbar', 'App\Http\Controllers\OTransaksi\PerbarController@getPerbar')->middleware(['auth'])->name('get-perbar');
Route::get('/get-barang', 'App\Http\Controllers\OTransaksi\PerbarController@getBarang')->middleware(['auth'])->name('get-barang');

Route::post('/perbar/tampil', 'App\Http\Controllers\OTransaksi\PerbarController@tampil')->middleware(['auth'])->name('perbar-tampil');
Route::post('/perbar/proses', 'App\Http\Controllers\OTransaksi\PerbarController@proses')->middleware(['auth'])->name('perbar-proses');

// =============================================
// End Transaksi Perubahan Barcode
// =============================================

// =============================================
// Transaksi Posting Perubahan
// =============================================

Route::get('/posthisto', 'App\Http\Controllers\OTransaksi\PosthistoController@index')->middleware(['auth'])->name('posthisto');
Route::post('/posthisto/store', 'App\Http\Controllers\OTransaksi\PosthistoController@store')->middleware(['auth'])->name('posthisto/store');
Route::get('/rposthisto', 'App\Http\Controllers\OReport\RPosthistoController@report')->middleware(['auth'])->name('rposthisto');

Route::get('/get-posthisto', 'App\Http\Controllers\OTransaksi\PosthistoController@getPosthisto')->middleware(['auth'])->name('get-posthisto');

Route::post('/posthisto/posting', 'App\Http\Controllers\OTransaksi\PosthistoController@posting')->middleware(['auth'])->name('posthisto-posting');

// =============================================
// End Transaksi Posting Perubahan
// =============================================


// =============================================
//  Transaksi Bete Bete
// =============================================
Route::get('/tbetebete', 'App\Http\Controllers\OTransaksi\TBeteBeteController@index')
    ->middleware(['auth'])
    ->name('betebete');
Route::post('/tbetebete/cari', 'App\Http\Controllers\OTransaksi\TBeteBeteController@cari_data')
    ->middleware(['auth'])
    ->name('betebete_cari');
Route::post('/tbetebete/proses', 'App\Http\Controllers\OTransaksi\TBeteBeteController@proses')
    ->middleware(['auth'])
    ->name('betebete_proses');

// =============================================
//  End Transaksi Bete Bete
// =============================================

// =============================================
//  Transaksi Usulan LPH Periode
// =============================================
Route::get('/tusulanlphperiode', 'App\Http\Controllers\OTransaksi\TUsulanLPHPeriodeController@index')
    ->middleware(['auth'])
    ->name('usulanlphperiode');
Route::get('/tusulanlphperiode/edit/{sub}', 'App\Http\Controllers\OTransaksi\TUsulanLPHPeriodeController@edit')
    ->middleware(['auth'])
    ->name('usulanlphperiode_edit');
Route::post('/tusulanlphperiode/cari', 'App\Http\Controllers\OTransaksi\TUsulanLPHPeriodeController@cari_data')
    ->middleware(['auth'])
    ->name('usulanlphperiode_cari');
Route::post('/tusulanlphperiode/detail/{sub}', 'App\Http\Controllers\OTransaksi\TUsulanLPHPeriodeController@detail')
    ->middleware(['auth'])
    ->name('usulanlphperiode_detail');
Route::post('/tusulanlphperiode/proses', 'App\Http\Controllers\OTransaksi\TUsulanLPHPeriodeController@proses')
    ->middleware(['auth'])
    ->name('usulanlphperiode_proses');
// =============================================
//  End Transaksi Usulan LPH Periode
// =============================================


// =============================================
//  Transaksi LPH Hari Raya
// =============================================

Route::get('/tlphhariraya', 'App\Http\Controllers\OTransaksi\TLPHHariRayaController@index')
    ->middleware(['auth'])
    ->name('lphhariraya');

Route::get('/tlphhariraya/edit/{no_bukti}', 'App\Http\Controllers\OTransaksi\TLPHHariRayaController@edit')
    ->middleware(['auth'])
    ->name('lphhariraya_edit');

Route::post('/tlphhariraya/cari', 'App\Http\Controllers\OTransaksi\TLPHHariRayaController@cari_data')
    ->middleware(['auth'])
    ->name('lphhariraya_cari');

Route::post('/tlphhariraya/detail/{no_bukti}', 'App\Http\Controllers\OTransaksi\TLPHHariRayaController@detail')
    ->middleware(['auth'])
    ->name('lphhariraya_detail');

Route::post('/tlphhariraya/proses', 'App\Http\Controllers\OTransaksi\TLPHHariRayaController@proses')
    ->middleware(['auth'])
    ->name('lphhariraya_proses');

Route::post('/tlphhariraya/search-barang', 'App\Http\Controllers\OTransaksi\TLPHHariRayaController@searchBarang')
    ->middleware(['auth'])
    ->name('lphhariraya_search_barang');

// =============================================
//  End Transaksi LPH Hari Raya
// =============================================


// =============================================
//  Transaksi Usulan Hapus Barang
// =============================================

Route::get('/tusulanhapusbarang', 'App\Http\Controllers\OTransaksi\TUsulanHapusBarangController@index')
    ->middleware(['auth'])
    ->name('usulanhapusbarang');

Route::post('/tusulanhapusbarang/cari', 'App\Http\Controllers\OTransaksi\TUsulanHapusBarangController@cari_data')
    ->middleware(['auth'])
    ->name('usulanhapusbarang_cari');

Route::post('/tusulanhapusbarang/proses', 'App\Http\Controllers\OTransaksi\TUsulanHapusBarangController@proses')
    ->middleware(['auth'])
    ->name('usulanhapusbarang_proses');

// =============================================
//  End Transaksi Usulan Hapus Barang
// =============================================

// =============================================
//  Transaksi Penambahan Barang Baru
// =============================================
Route::get('/tpenambahanbarangbaru', 'App\Http\Controllers\OTransaksi\TPenambahanBarangBaruController@index')
    ->middleware(['auth'])
    ->name('penambahanbarangbaru');
Route::get('/tpenambahanbarangbaru/edit/{no_bukti}', 'App\Http\Controllers\OTransaksi\TPenambahanBarangBaruController@edit')
    ->middleware(['auth'])
    ->name('penambahanbarangbaru_edit');
Route::post('/tpenambahanbarangbaru/cari', 'App\Http\Controllers\OTransaksi\TPenambahanBarangBaruController@cari_data')
    ->middleware(['auth'])
    ->name('penambahanbarangbaru_cari');
Route::post('/tpenambahanbarangbaru/detail/{no_bukti}', 'App\Http\Controllers\OTransaksi\TPenambahanBarangBaruController@detail')
    ->middleware(['auth'])
    ->name('penambahanbarangbaru_detail');
Route::post('/tpenambahanbarangbaru/proses', 'App\Http\Controllers\OTransaksi\TPenambahanBarangBaruController@proses')
    ->middleware(['auth'])
    ->name('penambahanbarangbaru_proses');
Route::post('/tpenambahanbarangbaru/search-barang', 'App\Http\Controllers\OTransaksi\TPenambahanBarangBaruController@searchBarang')
    ->middleware(['auth'])
    ->name('penambahanbarangbaru_search_barang');
Route::post('/tpenambahanbarangbaru/get-ksp', 'App\Http\Controllers\OTransaksi\TPenambahanBarangBaruController@getKspFile')
    ->middleware(['auth'])
    ->name('penambahanbarangbaru_get_ksp');
// =============================================
//  End Transaksi Penambahan Barang Baru
// =============================================


// =============================================
//  Transaksi Pelaksanaan Turun Harga
// =============================================
Route::get('/tpelaksanaanturunharga', 'App\Http\Controllers\OTransaksi\TPelaksanaanTurunHargaController@index')
    ->middleware(['auth'])
    ->name('pelaksanaanturunharga');
Route::post('/tpelaksanaanturunharga/cari', 'App\Http\Controllers\OTransaksi\TPelaksanaanTurunHargaController@cari_data')
    ->middleware(['auth'])
    ->name('pelaksanaanturunharga_cari');
Route::post('/tpelaksanaanturunharga/proses', 'App\Http\Controllers\OTransaksi\TPelaksanaanTurunHargaController@proses')
    ->middleware(['auth'])
    ->name('pelaksanaanturunharga_proses');
// =============================================
//  End Transaksi Pelaksanaan Turun Harga
// =============================================


// =============================================
//  Transaksi Cetak Label Harga
// =============================================
Route::get('/tcetaklabelharga', 'App\Http\Controllers\OTransaksi\TCetakLabelHargaController@index')
    ->middleware(['auth'])
    ->name('cetaklabelharga');

Route::post('/tcetaklabelharga/cari', 'App\Http\Controllers\OTransaksi\TCetakLabelHargaController@cari_data')
    ->middleware(['auth'])
    ->name('cetaklabelharga_cari');

Route::post('/tcetaklabelharga/proses', 'App\Http\Controllers\OTransaksi\TCetakLabelHargaController@proses')
    ->middleware(['auth'])
    ->name('cetaklabelharga_proses');

Route::get('/tcetaklabelharga/print', 'App\Http\Controllers\OTransaksi\TCetakLabelHargaController@printLabel')
    ->middleware(['auth'])
    ->name('cetaklabelharga_print');

// =============================================
//  End Transaksi Cetak Label Harga
// =============================================

// =============================================
// Operational Transaksi - Pembayaran Piutang
// =============================================
Route::group(['prefix' => 'TPembayaranPiutang', 'middleware' => ['auth']], function () {
    Route::get('/', 'App\Http\Controllers\OTransaksi\TPembayaranPiutangController@index')->name('TPembayaranPiutang');
    Route::get('/edit', 'App\Http\Controllers\OTransaksi\TPembayaranPiutangController@edit')->name('TPembayaranPiutang.edit');
    Route::get('/get-data', 'App\Http\Controllers\OTransaksi\TPembayaranPiutangController@getPembayaranPiutang')->name('TPembayaranPiutang.get-data');
    Route::post('/store', 'App\Http\Controllers\OTransaksi\TPembayaranPiutangController@store')->name('TPembayaranPiutang.store');
    Route::post('/update/{id}', 'App\Http\Controllers\OTransaksi\TPembayaranPiutangController@update')->name('TPembayaranPiutang.update');
    Route::get('/delete/{id}', 'App\Http\Controllers\OTransaksi\TPembayaranPiutangController@destroy')->name('TPembayaranPiutang.delete');
    Route::get('/browse', 'App\Http\Controllers\OTransaksi\TPembayaranPiutangController@browse')->name('TPembayaranPiutang.browse');
    Route::get('/browsesupz', 'App\Http\Controllers\OTransaksi\TPembayaranPiutangController@browsesupz')->name('TPembayaranPiutang.browsesupz');
    Route::get('/browseAccount', 'App\Http\Controllers\OTransaksi\TPembayaranPiutangController@browseAccount')->name('TPembayaranPiutang.browseAccount');
    Route::get('/ceksup', 'App\Http\Controllers\OTransaksi\TPembayaranPiutangController@ceksup')->name('TPembayaranPiutang.ceksup');
    Route::get('/getSelectKodes', 'App\Http\Controllers\OTransaksi\TPembayaranPiutangController@getSelectKodes')->name('TPembayaranPiutang.getSelectKodes');
    Route::post('/validateInvoice', 'App\Http\Controllers\OTransaksi\TPembayaranPiutangController@validateInvoice')->name('TPembayaranPiutang.validateInvoice');
});

// =================================================
//  End Operational Transaksi - Pembayaran Piutang
// =================================================

///route tambahan
Route::post('/set-flag-session', function (\Illuminate\Http\Request $request) {
    $flag = $request->input('flag');
    if ($flag) {
        session(['flag' => $flag]);
        return response()->json(['success' => true, 'flag' => $flag]);
    }
    return response()->json(['success' => false, 'message' => 'Flag not provided'], 400);
})->middleware(['auth'])->name('set-flag-session');
