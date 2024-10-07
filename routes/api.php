<?php

use App\Http\Controllers\AR\Activity\SalesOrderController;
use App\Http\Controllers\AR\Master\CustomerController;
use App\Http\Controllers\AR\Master\SalesController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\IN\Master\ItemController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::patch('changepass', [AuthController::class, 'changePass'])->middleware('auth:sanctum');

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
Route::patch('otorisasiso', [SalesOrderController::class, 'updateJenis'])->middleware('auth:sanctum');
Route::get('listsoblmpo', [SalesOrderController::class, 'getListSOBlmPO'])->middleware('auth:sanctum');

//Master Barang
Route::get('item', [ItemController::class, 'getListData'])->middleware('auth:sanctum');
Route::get('itemso', [ItemController::class, 'getListBarangSO'])->middleware('auth:sanctum');
Route::post('item', [ItemController::class, 'insertData'])->middleware('auth:sanctum');
Route::patch('item', [ItemController::class, 'updateAllData'])->middleware('auth:sanctum');
Route::delete('item', [ItemController::class, 'deleteData'])->middleware('auth:sanctum');
