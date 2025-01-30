<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        // $data = $request->all();
        // $imageName = null;
        // if($request->has('image')){
        //     $imageName = time().'.'.$request->image->extension();
        //     $request->file('image')->storeAs('public/image',$imageName);
        //     $imageName = 'image/' . $imageName;
        // }
        // $category = Category::find($data['category_id']);
        // $product = Product::create([
        //     'category_id' => $data['category_id'],
        //     'sub_category_id' => $data['sub_category_id'],
        //     'department_id' => $category->department_id,
        //     'price' => $data['price'],
        //     'name'=>$data['name'],
        //     'image'=>$imageName,
        // ]);
        // return response()->json($product);

        // Validação dos dados
    $validated = $request->validate([
        'category_id' => 'required|exists:categories,id',
        'sub_category_id' => 'nullable|exists:sub_categories,id',
        'price' => 'required|numeric|min:0',
        'name' => 'required|string|max:255',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

        // Verificar se a categoria existe
        $category = Category::find($validated['category_id']);
        if (!$category) {
            return response()->json(['error' => 'Categoria não encontrada'], 404);
        }
        $imageName = null;


        // Processamento da imagem (se houver)
        // if ($request->hasFile('image')) {
        //     $imagePath = $request->file('image')->store('public/image');
        //     $imageName = str_replace('public/', '', $imagePath); // Remover "public/" para facilitar o uso com Storage::url()
        // }
        if ($request->hasFile('image')) {
            $imageName = time().'.'.$request->file('image')->getClientOriginalExtension();
            $request->file('image')->move(public_path('image'), $imageName);
            $imageName = 'image/' . $imageName; // Caminho relativo para acessar via URL
        }
        // if ($request->hasFile('image')) {
        //     $imagePath = $request->file('image')->store('public/image');
        //     $imageName = str_replace('public/', 'storage/', $imagePath); // Ajustar caminho para acesso via URL
        // }

        // Criar o produto
        $product = Product::create([
            'category_id' => $validated['category_id'],
            'sub_category_id' => $validated['sub_category_id'],
            'department_id' => $category->department_id,
            'price' => $validated['price'],
            'name' => $validated['name'],
            'image' => $imageName,
        ]);

        // Retornar a resposta JSON
        return response()->json([
            'message' => 'Produto criado com sucesso',
            'product' => $product,
            'image_url' => $imageName ? Storage::url($imageName) : null,
        ], 201);
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

        $category = Category::find($data['category_id']);
        if (!$category) {
            return response()->json(['error' => 'Categoria não encontrada'], 404);
        }

        $imageName = $product->image;

        if ($request->hasFile('image')) {
            $imageName = time().'.'.$request->file('image')->getClientOriginalExtension();
            $request->file('image')->move(public_path('image'), $imageName);
            $imageName = 'image/' . $imageName; // Caminho relativo para acessar via URL
        }

        $data['image'] = $imageName;
        $data['department_id'] = $category->department_id;
        
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
