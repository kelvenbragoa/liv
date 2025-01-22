<?php

use App\Http\Controllers\Api\Mobile\AuthMobileController;
use App\Http\Controllers\Api\Mobile\TableMobileController;
use App\Http\Controllers\Api\Web\AuthWebController;
use App\Http\Controllers\Api\Web\CategoryController;
use App\Http\Controllers\Api\Web\CustomerController;
use App\Http\Controllers\Api\Web\OrderController;
use App\Http\Controllers\Api\Web\PaymentController;
use App\Http\Controllers\Api\Web\PaymentMethodController;
use App\Http\Controllers\Api\Web\PdvController;
use App\Http\Controllers\Api\Web\ProductController;
use App\Http\Controllers\Api\Web\ReservationController;
use App\Http\Controllers\Api\Web\SubCategoryController;
use App\Http\Controllers\Api\Web\TableController;
use App\Http\Controllers\Api\Web\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('login',[AuthWebController::class,'login']);

Route::resource('categories', CategoryController::class);
Route::resource('subcategories', SubCategoryController::class);
Route::resource('products', ProductController::class);
Route::resource('customers', CustomerController::class);
Route::resource('tables', TableController::class);

Route::resource('paymentmethods', PaymentMethodController::class);

Route::resource('users', UserController::class);
Route::resource('reservations', ReservationController::class);

Route::resource('pdv', PdvController::class);
Route::get('pdv/quicksell',[PdvController::class,'quicksell']);
Route::post('pdv/quicksell',[PdvController::class,'savequicksell']);


Route::post('getreceipt/{id}',[PdvController::class,'getreceipt']);

Route::get('pdv/closeaccount/{id}',[PdvController::class,'closeaccount']);

Route::post('payaccount',[PdvController::class,'payaccount']);



Route::resource('payments', PaymentController::class);
Route::resource('orders', OrderController::class);


Route::post('/barman-login', [AuthMobileController::class, 'login']);

Route::resource('mobile-tables', TableMobileController::class);

Route::post('createorder',[TableMobileController::class,'createorder']);

Route::get('consumption/{id}',[TableMobileController::class,'consumption']);

Route::get('pdvkitchen',[PdvController::class,'indexKitchen']);

Route::get('changestatus/{id}',[PdvController::class,'changestatus']);








