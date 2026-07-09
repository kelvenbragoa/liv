<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Department;
use App\Models\StockCenterProduct;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    private function buildFilteredQuery(Request $request)
    {
        $sortBy = $request->input('sort_by', 'name');
        $allowedSort = ['id', 'name', 'created_at', 'department_id', 'sub_categories_count', 'products_count'];

        if (! in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'name';
        }

        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        return Category::query()
            ->when($request->filled('query'), function ($query) use ($request) {
                $search = $request->input('query');
                $query->where('name', 'like', "%{$search}%");
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
            ->with('department')
            ->withCount(['sub_categories', 'products'])
            ->orderBy($sortBy, $sortDir);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = min(max((int) $request->input('per_page', 15), 5), 100);

        $categories = $this->buildFilteredQuery($request)
            ->paginate($perPage)
            ->withQueryString();

        return response()->json($categories);
    }

    /**
     * Export filtered categories for Excel download.
     */
    public function export(Request $request)
    {
        $categories = $this->buildFilteredQuery($request)->get();

        $data = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'department' => $category->department?->name,
                'sub_categories_count' => $category->sub_categories_count,
                'products_count' => $category->products_count,
                'created_at' => $category->created_at?->format('d/m/Y H:i'),
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
        $departments = Department::all();

        return response()->json([
            'departments' => $departments
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $data = $request->all();
        $category = Category::create([
            'department_id' => $data['department_id'],
            'name'=>$data['name'],
            'image'=>null,
        ]);
        return response()->json($category);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = Category::query()
            ->with('department')
            ->withCount(['sub_categories', 'products'])
            ->withAvg('products as products_avg_price', 'price')
            ->find($id);

        if (! $category) {
            return response()->json(['message' => 'Categoria não encontrada.'], 404);
        }

        $subcategoryBreakdown = $category->sub_categories()
            ->withCount('products')
            ->orderBy('products_count', 'desc')
            ->orderBy('name')
            ->get(['id', 'name', 'created_at']);

        $products = $category->products()
            ->withQuantityInPrincipalStock()
            ->orderByDesc('quantity_in_principal_stock')
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'created_at', 'sub_category_id']);

        $productsTotalStock = (int) $products->sum('quantity_in_principal_stock');
        $zeroStockProducts = $products->filter(fn ($product) => (int) ($product->quantity_in_principal_stock ?? 0) <= 0)->count();
        $lowStockProducts = $products->filter(function ($product) {
            $stock = (int) ($product->quantity_in_principal_stock ?? 0);
            return $stock > 0 && $stock <= 5;
        })->count();

        $topProductsByStock = $products->take(8)->values();

        $subcategoryBreakdown = $subcategoryBreakdown->map(function ($subcategory) {
            $subcategoryStock = StockCenterProduct::query()
                ->whereHas('stockcenter', function ($query) {
                    $query->where('is_principal_stock', 1);
                })
                ->whereHas('product', function ($query) use ($subcategory) {
                    $query->where('sub_category_id', $subcategory->id);
                })
                ->sum('quantity');

            $subcategory->total_stock = (int) $subcategoryStock;

            return $subcategory;
        });

        return response()->json([
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'created_at' => $category->created_at,
            ],
            'department' => $category->department ? [
                'id' => $category->department->id,
                'name' => $category->department->name,
            ] : null,
            'metrics' => [
                'sub_categories_count' => (int) ($category->sub_categories_count ?? 0),
                'products_count' => (int) ($category->products_count ?? 0),
                'products_total_stock' => $productsTotalStock,
                'products_avg_price' => (float) ($category->products_avg_price ?? 0),
                'zero_stock_products' => $zeroStockProducts,
                'low_stock_products' => $lowStockProducts,
            ],
            'subcategory_breakdown' => $subcategoryBreakdown,
            'top_products_by_stock' => $topProductsByStock,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $category = Category::query()->whereKey($id)->first();
        $departments = Department::all();

        return response()->json([
            'departments' => $departments,
            'category' => $category
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->all();
        $category = Category::query()->whereKey($id)->first();

        if (! $category) {
            return response()->json(['message' => 'Categoria não encontrada.'], 404);
        }

        $category->update($data);

        return response()->json($category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::query()->whereKey($id)->first();

        if (! $category) {
            return response()->json(['message' => 'Categoria não encontrada.'], 404);
        }

        $existCategory = SubCategory::query()->where('category_id', $category->id)->first();
        if ($existCategory) {
            return response()->json(['message' => 'Não é possível a categoria, existe sub categorias associadas'], 404);
        }
        Category::destroy($category->id);

        return response()->json(['message' => 'Categoria removida com sucesso.']);
    }
}
