<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    private function buildFilteredQuery(Request $request)
    {
        $sortBy = $request->input('sort_by', 'name');
        $allowedSort = ['id', 'name', 'created_at', 'category_id', 'department_id', 'products_count'];

        if (! in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'name';
        }

        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $query = SubCategory::query()
            ->when($request->filled('query'), function ($query) use ($request) {
                $search = $request->input('query');
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request->integer('category_id'));
            })
            ->when($request->filled('department_id'), function ($query) use ($request) {
                $query->whereHas('category', function ($categoryQuery) use ($request) {
                    $categoryQuery->where('department_id', $request->integer('department_id'));
                });
            })
            ->when($request->filled('created_from'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->input('created_from'));
            })
            ->when($request->filled('created_to'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->input('created_to'));
            })
            ->with('category.department')
            ->withCount('products');

        if ($sortBy === 'department_id') {
            $query->orderBy(
                Category::select('department_id')
                    ->whereColumn('categories.id', 'sub_categories.category_id'),
                $sortDir
            );
        } elseif ($sortBy === 'products_count') {
            $query->orderBy('products_count', $sortDir);
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        return $query;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = min(max((int) $request->input('per_page', 15), 5), 100);

        $subcategories = $this->buildFilteredQuery($request)
            ->paginate($perPage)
            ->withQueryString();

        return response()->json($subcategories);
    }

    /**
     * Export filtered subcategories for Excel download.
     */
    public function export(Request $request)
    {
        $subcategories = $this->buildFilteredQuery($request)->get();

        $data = $subcategories->map(function ($subcategory) {
            return [
                'id' => $subcategory->id,
                'name' => $subcategory->name,
                'category' => $subcategory->category?->name,
                'department' => $subcategory->category?->department?->name,
                'products_count' => $subcategory->products_count,
                'created_at' => $subcategory->created_at?->format('d/m/Y H:i'),
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
        $categories = Category::with('department')->orderBy('name')->get();

        return response()->json([
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $subcategory = SubCategory::create([
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'image' => null,
        ]);

        return response()->json($subcategory);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $subcategory = SubCategory::query()
            ->with('category.department')
            ->withCount('products')
            ->withAvg('products as products_avg_price', 'price')
            ->find($id);

        if (! $subcategory) {
            return response()->json(['message' => 'Subcategoria não encontrada.'], 404);
        }

        $products = $subcategory->products()
            ->withQuantityInPrincipalStock()
            ->orderByDesc('quantity_in_principal_stock')
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'created_at', 'category_id']);

        $productsTotalStock = (int) $products->sum('quantity_in_principal_stock');
        $zeroStockProducts = $products->filter(fn ($product) => (int) ($product->quantity_in_principal_stock ?? 0) <= 0)->count();
        $lowStockProducts = $products->filter(function ($product) {
            $stock = (int) ($product->quantity_in_principal_stock ?? 0);
            return $stock > 0 && $stock <= 5;
        })->count();

        $topProductsByStock = $products->take(8)->values();

        return response()->json([
            'subcategory' => [
                'id' => $subcategory->id,
                'name' => $subcategory->name,
                'created_at' => $subcategory->created_at,
            ],
            'category' => $subcategory->category ? [
                'id' => $subcategory->category->id,
                'name' => $subcategory->category->name,
            ] : null,
            'department' => $subcategory->category?->department ? [
                'id' => $subcategory->category->department->id,
                'name' => $subcategory->category->department->name,
            ] : null,
            'metrics' => [
                'products_count' => (int) ($subcategory->products_count ?? 0),
                'products_total_stock' => $productsTotalStock,
                'products_avg_price' => (float) ($subcategory->products_avg_price ?? 0),
                'zero_stock_products' => $zeroStockProducts,
                'low_stock_products' => $lowStockProducts,
            ],
            'top_products_by_stock' => $topProductsByStock,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $subcategory = SubCategory::find($id);
        $categories = Category::all();

        return response()->json([
            'categories' => $categories,
            'subcategory' => $subcategory,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->all();
        $subcategory = SubCategory::find($id);
        $subcategory->update($data);

        return response()->json($subcategory);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $subcategory = SubCategory::find($id);
        $existProduct = Product::where('sub_category_id', $subcategory->id)->first();

        if ($existProduct) {
            return response()->json(['message' => 'Não é possível eliminar a subcategoria, existem produtos associados.'], 404);
        }

        $subcategory->delete();

        return response()->json(['message' => 'Subcategoria removida com sucesso.']);
    }
}
