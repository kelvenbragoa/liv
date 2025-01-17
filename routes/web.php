<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\GlobalController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/receipt',[GlobalController::class,'receipt']);


//Last route to overlap every route hitting laravel route

Route::get('{view}', ApplicationController::class)->where('view','(.*)');