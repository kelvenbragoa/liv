<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

            $products = Product::query()
            ->when(request('query'),function($query,$searchQuery){
                $query->where('name','like',"%{$searchQuery}%");
            })
            ->with('category.department')
            ->with('subcategory')
            ->orderBy('name','asc')
            ->paginate();

            return response()->json($products);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $categories = Category::all();
        $sub_categories = SubCategory::all();


        return response()->json([
            'categories' => $categories,
            'sub_categories' => $sub_categories

        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $data = $request->all();
        $product = Product::create([
            'category_id' => $data['category_id'],
            'sub_category_id' => $data['sub_category_id'],
            'price' => $data['price'],
            'name'=>$data['name'],
            'image'=>null,
        ]);
        return response()->json($product);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $product = Product::
        with('category.department')
        ->with('subcategory')
        ->find($id);

        return response()->json($product);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $product = Product::find($id);
        $categories = Category::all();
        $subcategories = SubCategory::all();

        return response()->json([
            'categories' => $categories,
            'sub_categories' => $subcategories,
            'product' => $product
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();
        $product = Product::find($id);
        $product->update($data);
        return response()->json($product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $product = Product::find($id);
        $product->delete();
        return true;
    }
}
