<?php

use App\Http\Controllers\Api\Public\MenuDigitalController;
use App\Http\Controllers\Api\Mobile\AuthMobileController;
use App\Http\Controllers\Api\Mobile\TableMobileController;
use App\Http\Controllers\Api\Web\AuthWebController;
use App\Http\Controllers\Api\Web\CashRegisterController;
use App\Http\Controllers\Api\Web\CategoryController;
use App\Http\Controllers\Api\Web\CenterStocksController;
use App\Http\Controllers\Api\Web\CustomerController;
use App\Http\Controllers\Api\Web\EntryNotesController;
use App\Http\Controllers\Api\Web\ExitNotesController;
use App\Http\Controllers\Api\Web\InventoriesController;
use App\Http\Controllers\Api\Web\OrderController;
use App\Http\Controllers\Api\Web\PaymentController;
use App\Http\Controllers\Api\Web\PaymentMethodController;
use App\Http\Controllers\Api\Web\PdvController;
use App\Http\Controllers\Api\Web\ProductController;
use App\Http\Controllers\Api\Web\ReservationController;
use App\Http\Controllers\Api\Web\StockTransferController;
use App\Http\Controllers\Api\Web\SubCategoryController;
use App\Http\Controllers\Api\Web\SuppliersController;
use App\Http\Controllers\Api\Web\TableController;
use App\Http\Controllers\Api\Web\UserController;
use App\Http\Middleware\Sanctum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('login',[AuthWebController::class,'login']);
Route::post('/barman-login', [AuthMobileController::class, 'login']);

Route::get('menu-digital', [MenuDigitalController::class, 'index']);


Route::middleware([Sanctum::class])->group(function () {

    
    Route::get('auxiliar-product/{id}',[StockTransferController::class,'products']);

    Route::get('auxiliar',[InventoriesController::class,'center']);

    Route::get('entrynotes/export', [EntryNotesController::class, 'export']);
    Route::resource('entrynotes', EntryNotesController::class);
    Route::get('exitnotes/export', [ExitNotesController::class, 'export']);
    Route::resource('exitnotes', ExitNotesController::class);

    Route::get('inventories/export', [InventoriesController::class, 'export']);
    Route::resource('inventories', InventoriesController::class);

    Route::get('stocktransfers/export', [StockTransferController::class, 'export']);
    Route::resource('stocktransfers', StockTransferController::class);

    Route::get('centerstocks/export', [CenterStocksController::class, 'export']);
    Route::resource('centerstocks', CenterStocksController::class);
    Route::get('suppliers/export', [SuppliersController::class, 'export']);
    Route::resource('suppliers', SuppliersController::class);

    Route::get('categories/export', [CategoryController::class, 'export']);
    Route::resource('categories', CategoryController::class);
    Route::get('subcategories/export', [SubCategoryController::class, 'export']);
    Route::resource('subcategories', SubCategoryController::class);
    Route::get('products/export', [ProductController::class, 'export']);
    Route::resource('products', ProductController::class);
    Route::get('customers/export', [CustomerController::class, 'export']);
    Route::resource('customers', CustomerController::class);
    Route::get('tables/export', [TableController::class, 'export']);
    Route::resource('tables', TableController::class);
    Route::post('logout',[AuthWebController::class,'logout']);


    Route::get('paymentmethods/export', [PaymentMethodController::class, 'export']);
    Route::resource('paymentmethods', PaymentMethodController::class);

    Route::get('users/export', [UserController::class, 'export']);
    Route::resource('users', UserController::class);
    Route::resource('reservations', ReservationController::class);

    Route::resource('pdv', PdvController::class);
    Route::get('pdvquicksellslist',[PdvController::class,'listquicksells']);


    Route::get('pdv/quicksell',[PdvController::class,'quicksell']);
    Route::post('pdv/quicksell',[PdvController::class,'savequicksell']);
    

    Route::post('getreceiptkitchen/{id}',[PdvController::class,'getreceiptkitchen']);

    Route::post('getreceipt/{id}',[PdvController::class,'getreceipt']);
    Route::post('getorderreceipt/{id}',[PdvController::class,'getorderreceipt']);
    Route::post('getquickreceipt/{id}',[PdvController::class,'getquickreceipt']);

    Route::get('pdv/closeaccount/{id}',[PdvController::class,'closeaccount']);
    Route::post('getfinalreceipt/{id}',[PdvController::class,'getfinalreceipt']);
    Route::post('getcustomerreceipt/{id}',[PdvController::class,'getcustomerreceipt']);

    Route::post('payaccount',[PdvController::class,'payaccount']);
    Route::post('order/report',[OrderController::class,'report']);



    Route::get('payments/export', [PaymentController::class, 'export']);
    Route::resource('payments', PaymentController::class);
    Route::get('orders/export', [OrderController::class, 'export']);
    Route::resource('orders', OrderController::class);

    Route::post('orderitem/{id}',[OrderController::class,'deleteorderitem']);
    Route::post('quickorderdelete/{id}',[OrderController::class,'deleteorder']);

    Route::get('cashregisters/report',[CashRegisterController::class,'report']);
    Route::get('cashregisters/reportevento',[CashRegisterController::class,'reportevento']);
    Route::get('cashregisters/reportstock',[CashRegisterController::class,'reportstock']);
    Route::get('cashregisters/reporttrash',[CashRegisterController::class,'reporttrash']);
    Route::get('cashregisters/reportexcel',[CashRegisterController::class,'reportexcel']);
    Route::get('cashregisters/reporteventoexcel',[CashRegisterController::class,'reporteventoexcel']);
    Route::get('cashregisters/reportstockexcel',[CashRegisterController::class,'reportstockexcel']);
    Route::get('cashregisters/reporttrashexcel',[CashRegisterController::class,'reporttrashexcel']);


    Route::get('centerstock/report/{id}',[CenterStocksController::class,'reportstock']);
    Route::get('centerstock/reconcile',[CenterStocksController::class,'reconcile']);






    Route::get('pdvkitchen',[PdvController::class,'indexKitchen']);

    Route::get('changestatus/{id}',[PdvController::class,'changestatus']);

    Route::get('pdvbar',[PdvController::class,'indexBar']);

    Route::get('barchangestatus/{id}',[PdvController::class,'barchangestatus']);


    Route::post('cashregisters/open',[CashRegisterController::class,'open']);
    // Route::resource('cashregisters', CashRegisterController::class);

    Route::get('cashregister/export', [CashRegisterController::class, 'export']);
    Route::get('cashregister/create', [CashRegisterController::class, 'create']);
    Route::get('cashregister', [CashRegisterController::class, 'index']);
    Route::get('cashregister/{id}', [CashRegisterController::class, 'show']);


    Route::post('cashregisters/close',[CashRegisterController::class,'close']);

    Route::get('cashregisters/dashboard',[CashRegisterController::class,'dashboard']);

    Route::get('cashregisters/dailydashboard',[CashRegisterController::class,'dailydashboard']);
    Route::get('daily/quicksellreport',[CashRegisterController::class,'quicksellreportdaily']);
    Route::get('daily/tablesellreport',[CashRegisterController::class,'tablesellreportdaily']);
    Route::get('daily/paymentreport',[CashRegisterController::class,'paymentreportdaily']);


    

    Route::get('cashregisters/quicksellreport',[CashRegisterController::class,'quicksellreport']);
    Route::get('cashregisters/tablesellreport',[CashRegisterController::class,'tablesellreport']);
    Route::get('cashregisters/paymentreport',[CashRegisterController::class,'paymentreport']);



    Route::resource('mobile-tables', TableMobileController::class);

    Route::post('createorder',[TableMobileController::class,'createorder']);

    Route::get('consumption/{id}',[TableMobileController::class,'consumption']);

    Route::get('closeaccount/{id}',[TableMobileController::class,'closeaccount']);

    Route::post('mobile-savequicksell',[TableMobileController::class,'savequicksell']);




    Route::get('mobile-cashregister/open',[TableMobileController::class,'openCashRegister']);

    Route::post('mobile-cashregister/close',[TableMobileController::class,'closeCashRegister']);

    Route::get('mobile-home',[TableMobileController::class,'home']);


    Route::get('quicksellpdv',[TableMobileController::class,'quicksell']);

    Route::get('getuserdetails/{token}',[AuthMobileController::class,'getuserdetails']);




});







