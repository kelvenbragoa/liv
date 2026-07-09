<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ExitNoteItem;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockCenter;
use App\Models\StockCenterProduct;
use App\Models\StockCenterTransferItem;
use App\Models\StockMovement;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    private function buildFilteredQuery(Request $request)
    {
        $sortBy = $request->input('sort_by', 'name');
        $allowedSort = [
            'id',
            'name',
            'created_at',
            'category_id',
            'sub_category_id',
            'department_id',
            'price',
            'buy_price',
            'quantity_in_principal_stock',
        ];

        if (! in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'name';
        }

        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        return Product::query()
            ->when($request->filled('query'), function ($query) use ($request) {
                $search = $request->input('query');
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request->integer('category_id'));
            })
            ->when($request->filled('sub_category_id'), function ($query) use ($request) {
                $query->where('sub_category_id', $request->integer('sub_category_id'));
            })
            ->when($request->filled('department_id'), function ($query) use ($request) {
                $query->where('department_id', $request->integer('department_id'));
            })
            ->when($request->filled('created_from'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->input('created_from'));
            })
            ->when($request->filled('created_to'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->input('created_to'));
            })
            ->with('category.department')
            ->with('subcategory')
            ->withQuantityInPrincipalStock()
            ->orderBy($sortBy, $sortDir);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = min(max((int) $request->input('per_page', 15), 5), 100);

        $products = $this->buildFilteredQuery($request)
            ->paginate($perPage)
            ->withQueryString();

        return response()->json($products);
    }

    /**
     * Export filtered products for Excel download.
     */
    public function export(Request $request)
    {
        $products = $this->buildFilteredQuery($request)->get();

        $data = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category?->name,
                'subcategory' => $product->subcategory?->name,
                'department' => $product->category?->department?->name,
                'price' => $product->price,
                'buy_price' => $product->buy_price,
                'stock' => $product->quantity_in_principal_stock,
                'created_at' => $product->created_at?->format('d/m/Y H:i'),
            ];
        });

        return response()->json([
            'data' => $data,
            'total' => $data->count(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $categories = Category::orderBy('name')->get();
        $sub_categories = SubCategory::orderBy('name')->get();


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
        ////
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
        'buy_price' => 'required|numeric|min:0',
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
            'buy_price' => $validated['buy_price'],
            'name' => $validated['name'],
            'image' => $imageName,
        ]);

        $allStockCenters = StockCenter::all();
        $allProducts = Product::all();

        foreach ($allStockCenters as $stockCenter) {
            foreach ($allProducts as $product) {
                $exists = StockCenterProduct::where('product_id', $product->id)
                    ->where('stock_center_id', $stockCenter->id)
                    ->exists();
                if (!$exists) {
                    StockCenterProduct::create([
                        'product_id' => $product->id,
                        'stock_center_id' => $stockCenter->id,
                        'quantity' => 0,
                    ]);
                }
            }
        }

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
        $product = Product::query()
            ->with('category.department')
            ->with('subcategory')
            ->withQuantityInPrincipalStock()
            ->find($id);

        if (! $product) {
            return response()->json(['message' => 'Produto não encontrado.'], 404);
        }

        $movements = StockMovement::query()
            ->where('product_id', $product->id)
            ->selectRaw('reason, COUNT(*) as total, SUM(quantity) as quantity_sum')
            ->groupBy('reason')
            ->orderByDesc('quantity_sum')
            ->get();

        return response()->json([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'buy_price' => $product->buy_price,
                'image' => $product->image,
                'created_at' => $product->created_at,
                'quantity_in_principal_stock' => (int) ($product->quantity_in_principal_stock ?? 0),
            ],
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
            ] : null,
            'subcategory' => $product->subcategory ? [
                'id' => $product->subcategory->id,
                'name' => $product->subcategory->name,
            ] : null,
            'department' => $product->category?->department ? [
                'id' => $product->category->department->id,
                'name' => $product->category->department->name,
            ] : null,
            'metrics' => [
                'gross_margin' => (float) ($product->price ?? 0) - (float) ($product->buy_price ?? 0),
                'markup_percent' => (float) ($product->buy_price ?? 0) > 0
                    ? (((float) ($product->price ?? 0) - (float) ($product->buy_price ?? 0)) / (float) $product->buy_price) * 100
                    : 0,
                'stock_status' => (int) ($product->quantity_in_principal_stock ?? 0) <= 0
                    ? 'ruptura'
                    : ((int) ($product->quantity_in_principal_stock ?? 0) <= 5 ? 'baixo' : 'normal'),
            ],
            'movement_summary' => $movements,
        ]);
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
        $existOrderForProduct = OrderItem::where('product_id', $product->id)->first();
        $existExitNotesForProduct = ExitNoteItem::where('product_id', $product->id)->first();
        $existEntryNotesProduct = OrderItem::where('product_id', $product->id)->first();
        $existInventoriesProduct = OrderItem::where('product_id', $product->id)->first();
        $existStockTransferProduct = StockCenterTransferItem::where('product_id', $product->id)->first();

        if (!$product) {
            return response()->json(['message' => 'Produto não encontrado'], 404);
        }
        if ($existOrderForProduct) {
            return response()->json(['message' => 'Não é possível excluir um produto que possui pedidos associados'], 404);
        }
        if ($existExitNotesForProduct) {
            return response()->json(['message' => 'Não é possível excluir um produto que possui notas de saída associadas'], 404);
        }
        if ($existEntryNotesProduct) {
            return response()->json(['message' => 'Não é possível excluir um produto que possui notas de entrada associadas'], 404);
        }
        if ($existInventoriesProduct) {
            return response()->json(['message' => 'Não é possível excluir um produto que possui inventários associados'], 404);
        }
        if ($existStockTransferProduct) {
            return response()->json(['message' => 'Não é possível excluir um produto que possui transferências associadas'], 404);
        }

        StockCenterProduct::where('product_id', $product->id)->delete();


        $product->delete();

        return response()->json(['message' => 'Produto removido com sucesso.']);
    }
}
