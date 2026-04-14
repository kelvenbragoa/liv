<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\GlobalController;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Buscar categorias que têm produtos
    $categories = Category::whereHas('products')->limit(4)->get();
    
    // Para cada categoria, buscar produtos aleatórios
    $categories->each(function($category) {
        $category->randomProducts = $category->products()->inRandomOrder()->limit(3)->get();
    });
    
    // Buscar produtos especiais (aleatórios)
    $specialProducts = Product::with('category', 'subcategory')
        ->inRandomOrder()
        ->limit(5)
        ->get();
    
    return view('homepage', compact('categories', 'specialProducts'));
});

Route::get('/homepage', function () {
    // Buscar categorias que têm produtos
    $categories = Category::whereHas('products')->limit(4)->get();
    
    // Para cada categoria, buscar produtos aleatórios
    $categories->each(function($category) {
        $category->randomProducts = $category->products()->inRandomOrder()->limit(3)->get();
    });
    
    // Buscar produtos especiais (aleatórios)
    $specialProducts = Product::with('category', 'subcategory')
        ->inRandomOrder()
        ->limit(5)
        ->get();
    
    return view('homepage', compact('categories', 'specialProducts'));
});


Route::get('/relatorio',[GlobalController::class,'relatorio']);


//Last route to overlap every route hitting laravel route

Route::get('{view}', ApplicationController::class)->where('view','(.*)');