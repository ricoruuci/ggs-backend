<?php

use App\Http\Controllers\AP\Master\SupplierController;
use App\Http\Controllers\AP\Activity\KonsinyasiController;
use App\Http\Controllers\AP\Activity\PurchaseOrderController;
use App\Http\Controllers\AP\Activity\OtorisasiPembelianController;
use App\Http\Controllers\AR\Activity\PenjualanController;
use App\Http\Controllers\AR\Activity\SalesOrderController;
use App\Http\Controllers\AR\Master\CustomerController;
use App\Http\Controllers\AR\Master\SalesController;
use App\Http\Controllers\AR\Report\RekapSOController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\IN\Master\ItemController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;


Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::patch('changepass', [AuthController::class, 'changePass'])->middleware('auth:sanctum');

//Dashboard 
Route::get('dashboard', [DashboardController::class, 'getGrafikPenjualan'])->middleware('auth:sanctum');
Route::get('dashboard/tahun', [DashboardController::class, 'getSalesYear'])->middleware('auth:sanctum');
Route::get('dashboard/total', [DashboardController::class, 'getTotal'])->middleware('auth:sanctum');
Route::get('dashboard/user', [DashboardController::class, 'getUserAktif'])->middleware('auth:sanctum');
Route::get('dashboard/netcash', [DashboardController::class, 'getHutangPiutang'])->middleware('auth:sanctum');
Route::get('dashboard/jual', [DashboardController::class, 'getJualTahunan'])->middleware('auth:sanctum');


//Master Supplier
Route::post('supplier', [SupplierController::class, 'insertData'])->middleware('auth:sanctum');
Route::get('supplier', [SupplierController::class, 'getListData'])->middleware('auth:sanctum');
Route::patch('supplier', [SupplierController::class, 'updateAllData'])->middleware('auth:sanctum');
Route::delete('supplier', [SupplierController::class, 'deleteData'])->middleware('auth:sanctum');

//Master Sales
Route::get('sales', [SalesController::class, 'getListData'])->middleware('auth:sanctum');
Route::post('sales', [SalesController::class, 'insertData'])->middleware('auth:sanctum');
Route::patch('sales', [SalesController::class, 'updateAllData'])->middleware('auth:sanctum');
Route::delete('sales', [SalesController::class, 'deleteData'])->middleware('auth:sanctum');

//Master Customer
Route::post('customer', [CustomerController::class, 'insertData'])->middleware('auth:sanctum');
Route::get('customer', [CustomerController::class, 'getListData'])->middleware('auth:sanctum');
Route::patch('customer', [CustomerController::class, 'updateAllData'])->middleware('auth:sanctum');
Route::delete('customer', [CustomerController::class, 'deleteData'])->middleware('auth:sanctum');

//Sales Order
Route::post('salesorder', [SalesOrderController::class, 'insertData'])->middleware('auth:sanctum');
Route::get('salesorder', [SalesOrderController::class, 'getListData'])->middleware('auth:sanctum');
Route::patch('salesorder', [SalesOrderController::class, 'updateAllData'])->middleware('auth:sanctum');
Route::delete('salesorder', [SalesOrderController::class, 'deleteData'])->middleware('auth:sanctum');
Route::get('salesorder/item', [SalesOrderController::class, 'getListBarangSO'])->middleware('auth:sanctum');
Route::get('listsoblmpo', [SalesOrderController::class, 'getListSOBlmPO'])->middleware('auth:sanctum');
Route::patch('salesorder/otorisasi', [SalesOrderController::class, 'updateJenis'])->middleware('auth:sanctum');
// Route::get('salesorder/otorisasi', [SalesOrderController::class, 'getListOto'])->middleware('auth:sanctum');

Route::post('penjualan', [PenjualanController::class, 'insertData'])->middleware('auth:sanctum');
Route::patch('penjualan', [PenjualanController::class, 'updateAllData'])->middleware('auth:sanctum');
Route::delete('penjualan', [PenjualanController::class, 'deleteData'])->middleware('auth:sanctum');
Route::get('penjualan', [PenjualanController::class, 'getListData'])->middleware('auth:sanctum');
Route::get('penjualan/cari-so', [PenjualanController::class, 'cariSO'])->middleware('auth:sanctum');
Route::get('penjualan/cari-detail', [PenjualanController::class, 'cariDetail'])->middleware('auth:sanctum');
Route::get('penjualan/cari-sn', [PenjualanController::class, 'cariSN'])->middleware('auth:sanctum');
Route::get('penjualan/cekbayar', [PenjualanController::class, 'cekBayar'])->middleware('auth:sanctum');
Route::get('penjualan/carifps', [PenjualanController::class, 'cariFPS'])->middleware('auth:sanctum');
Route::get('penjualan/caripi', [PenjualanController::class, 'cariPi'])->middleware('auth:sanctum');

//Purchase Order
Route::post('purchaseorder', [PurchaseOrderController::class, 'insertData'])->middleware('auth:sanctum');
Route::get('purchaseorder', [PurchaseOrderController::class, 'getListData'])->middleware('auth:sanctum');
Route::patch('purchaseorder', [PurchaseOrderController::class, 'updateAllData'])->middleware('auth:sanctum');
Route::delete('purchaseorder', [PurchaseOrderController::class, 'deleteData'])->middleware('auth:sanctum');

//otorisasi Pembelian
Route::get('purchase/otorisasi', [OtorisasiPembelianController::class, 'getListOto'])->middleware('auth:sanctum');
Route::patch('purchase/otorisasi', [OtorisasiPembelianController::class, 'updateData'])->middleware('auth:sanctum');

//Konsinyasi 
Route::post('grn', [KonsinyasiController::class, 'insertData'])->middleware('auth:sanctum');
Route::get('grn', [KonsinyasiController::class, 'getListData'])->middleware('auth:sanctum');
Route::patch('grn', [KonsinyasiController::class, 'updateAllData'])->middleware('auth:sanctum');
Route::delete('grn', [KonsinyasiController::class, 'deleteData'])->middleware('auth:sanctum');
Route::get('listpoblmgrn', [KonsinyasiController::class, 'getListPO'])->middleware('auth:sanctum');
Route::get('listpodt', [KonsinyasiController::class, 'getListPODt'])->middleware('auth:sanctum');
Route::get('autosn', [KonsinyasiController::class, 'generateSN'])->middleware('auth:sanctum');

//Laporan Sales Order
Route::get('rekapso', [RekapSOController::class, 'getRekapSO'])->middleware('auth:sanctum');


//Master Barang
Route::get('item', [ItemController::class, 'getListData'])->middleware('auth:sanctum');
Route::post('item', [ItemController::class, 'insertData'])->middleware('auth:sanctum');
Route::patch('item', [ItemController::class, 'updateAllData'])->middleware('auth:sanctum');
Route::delete('item', [ItemController::class, 'deleteData'])->middleware('auth:sanctum');
