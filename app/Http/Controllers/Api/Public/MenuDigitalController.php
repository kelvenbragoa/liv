<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;

class MenuDigitalController extends Controller
{
    public function index()
    {
        $categories = Category::query()
            ->whereHas('products', function ($query) {
                $query->whereHas('stockCenterProducts', function ($stockQuery) {
                    $stockQuery->where('quantity', '>', 0)
                        ->whereHas('stockcenter', function ($centerQuery) {
                            $centerQuery->where('is_principal_stock', 1);
                        });
                });
            })
            ->with(['products' => function ($query) {
                $query->with('subcategory')
                    ->withQuantityInPrincipalStock()
                    ->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        $mapped = $categories->map(function ($category) {
            $products = $category->products
                ->filter(fn ($product) => (int) ($product->quantity_in_principal_stock ?? 0) > 0)
                ->values()
                ->map(function ($product) use ($category) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'price' => (float) ($product->price ?? 0),
                        'subcategory_name' => $product->subcategory?->name,
                        'category_name' => $category->name,
                    ];
                });

            return [
                'id' => $category->id,
                'name' => $category->name,
                'products' => $products->values(),
            ];
        })->filter(fn ($category) => $category['products']->isNotEmpty())->values();

        $totalProducts = $mapped->sum(fn ($category) => $category['products']->count());

        return response()->json([
            'restaurant' => [
                'name' => config('app.name', 'LIV BEIRA'),
            ],
            'metrics' => [
                'categories_count' => $mapped->count(),
                'products_count' => $totalProducts,
            ],
            'categories' => $mapped,
        ]);
    }
}
