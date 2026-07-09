<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockCenter;
use App\Models\StockCenterProduct;
use App\Models\StockCenterTransfer;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;


class CenterStocksController extends Controller
{
    private function buildFilteredQuery(Request $request)
    {
        $sortBy = $request->input('sort_by', 'name');
        $allowedSort = [
            'id',
            'name',
            'code',
            'location',
            'maximum_capacity',
            'is_principal_stock',
            'products_count',
            'total_stock_quantity',
            'created_at',
        ];

        if (! in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'name';
        }

        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        return StockCenter::query()
            ->when($request->filled('query'), function ($query) use ($request) {
                $search = $request->input('query');
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            })
            ->when($request->has('is_principal_stock') && $request->input('is_principal_stock') !== null && $request->input('is_principal_stock') !== '', function ($query) use ($request) {
                $query->where('is_principal_stock', (int) $request->input('is_principal_stock'));
            })
            ->when($request->filled('created_from'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->input('created_from'));
            })
            ->when($request->filled('created_to'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->input('created_to'));
            })
            ->withCount('stockcenterproducts as products_count')
            ->withSum('stockcenterproducts as total_stock_quantity', 'quantity')
            ->orderBy($sortBy, $sortDir);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = min(max((int) $request->input('per_page', 15), 5), 100);

        $stockCenters = $this->buildFilteredQuery($request)
            ->paginate($perPage)
            ->withQueryString();

        return response()->json($stockCenters);
    }

    /**
     * Export filtered stock centers for Excel download.
     */
    public function export(Request $request)
    {
        $stockCenters = $this->buildFilteredQuery($request)->get();

        $data = $stockCenters->map(function ($stockCenter) {
            return [
                'id' => $stockCenter->id,
                'name' => $stockCenter->name,
                'code' => $stockCenter->code,
                'location' => $stockCenter->location,
                'maximum_capacity' => $stockCenter->maximum_capacity,
                'is_principal_stock' => $stockCenter->is_principal_stock ? 'Sim' : 'Não',
                'products_count' => $stockCenter->products_count ?? 0,
                'total_stock_quantity' => $stockCenter->total_stock_quantity ?? 0,
                'created_at' => $stockCenter->created_at?->format('d/m/Y H:i'),
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
        return response()->noContent();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $data = $request->all();
        if($data['is_principal_stock'] == 1){
            $principalstock = StockCenter::where('is_principal_stock',1)->first();
            if($principalstock){
                return response()->json(['message'=>'So pode haver um centro de stock principal de venda.'],400);
            }
        }
        $stockcenter = StockCenter::create($data);

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
        return response()->json($stockcenter);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $stockcenter = StockCenter::
        with('stockcenterproducts')
        ->find($id);

        $stockcenterProducts = StockCenterProduct::where('stock_center_id', $id)
        ->when(request('query'),function($query,$searchQuery){
            $query->whereHas('product',function($q) use ($searchQuery){
                $q->where('name','like',"%{$searchQuery}%");
            });
        })
        ->with('product.category')
        ->with('product.subcategory')
        ->paginate();
        // $products = Product::query()
        //     ->when(request('query'),function($query,$searchQuery){
        //         $query->where('name','like',"%{$searchQuery}%");
        //     })
        //     ->with('category.department')
        //     ->with('subcategory')
        //     ->orderBy('name','asc')
        //     ->paginate();


        return response()->json([
            "stockcenter"=>$stockcenter,
            "stockcenterproducts"=>$stockcenterProducts
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $stockcenter = StockCenter::find($id);

        return response()->json([
            'stockcenter' => $stockcenter
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();
        $stockcenter = StockCenter::find($id);
        $stockcenter->update($data);
        return response()->json($stockcenter);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $stockcenter = StockCenter::find($id);

        if (! $stockcenter) {
            return response()->json(['message' => 'Centro de stock não encontrado.'], 404);
        }

        if ($stockcenter->is_principal_stock) {
            return response()->json(['message' => 'Não é possível eliminar o centro de stock principal.'], 404);
        }

        $hasStock = StockCenterProduct::where('stock_center_id', $stockcenter->id)
            ->where('quantity', '>', 0)
            ->exists();

        if ($hasStock) {
            return response()->json(['message' => 'Não é possível eliminar o centro, existem produtos com stock.'], 404);
        }

        $hasTransfers = StockCenterTransfer::where('stock_center_origin_id', $stockcenter->id)
            ->orWhere('stock_center_destination_id', $stockcenter->id)
            ->exists();

        if ($hasTransfers) {
            return response()->json(['message' => 'Não é possível eliminar o centro, existem transferências associadas.'], 404);
        }

        StockCenterProduct::where('stock_center_id', $stockcenter->id)->delete();
        $stockcenter->delete();

        return response()->json(['message' => 'Centro de stock removido com sucesso.']);
    }

    public function reportstock($id){


        $stockcenterProductsBar = StockCenterProduct::where('stock_center_id', $id)
        ->when(request('query'),function($query,$searchQuery){
            $query->whereHas('product',function($q) use ($searchQuery){
                $q->where('name','like',"%{$searchQuery}%");
            });
        })
        ->whereHas('product', function ($query) {
            $query->where('department_id', 2);
        })
        ->with('product.category')
        ->with('product.subcategory')
        ->get();

        $stockcenterProductsKitchen = StockCenterProduct::where('stock_center_id', $id)
        ->when(request('query'),function($query,$searchQuery){
            $query->whereHas('product',function($q) use ($searchQuery){
                $q->where('name','like',"%{$searchQuery}%");
            });
        })
        ->whereHas('product', function ($query) {
            $query->where('department_id', 1);
        })
        ->with('product.category')
        ->with('product.subcategory')
        ->get();
    
        $pdf = Pdf::loadView('pdf.reportstockcenter', compact('stockcenterProductsBar','stockcenterProductsKitchen'))->setOptions([
            'setPaper'=>'a8',
            // 'setPaper' => [0, 0, 640, 2376],
            'defaultFont' => 'sans-serif',
            'isRemoteEnabled' => 'true'
        ]);
        return $pdf->setPaper('a4')->stream('report_stock_center.pdf');
    }

    /**
     * Reconciliação on_hand vs ledger (stock_movements).
     */
    public function reconcile(Request $request)
    {
        $centerId = $request->integer('center_id')
            ?: StockCenter::where('is_principal_stock', 1)->value('id');

        if (!$centerId) {
            return response()->json(['message' => 'Centro de stock não encontrado.'], 404);
        }

        $onlyDiff = $request->boolean('only_diff', true);

        $ledger = DB::table('stock_movements')
            ->selectRaw("
                product_id,
                SUM(CASE WHEN direction = 'in' THEN quantity ELSE 0 END) -
                SUM(CASE WHEN direction = 'out' THEN quantity ELSE 0 END) as ledger_qty
            ")
            ->where('stock_center_id', $centerId)
            ->groupBy('product_id')
            ->pluck('ledger_qty', 'product_id');

        $items = [];
        $diffCount = 0;

        $products = StockCenterProduct::with('product')
            ->where('stock_center_id', $centerId)
            ->orderBy('product_id')
            ->get();

        foreach ($products as $row) {
            $onHand = (int) $row->quantity;
            $ledgerQty = (int) ($ledger[$row->product_id] ?? 0);
            $diff = $onHand - $ledgerQty;

            if ($diff !== 0) {
                $diffCount++;
            }

            if ($onlyDiff && $diff === 0) {
                continue;
            }

            $items[] = [
                'product_id' => (int) $row->product_id,
                'product_name' => $row->product?->name,
                'on_hand' => $onHand,
                'ledger' => $ledgerQty,
                'diff' => $diff,
            ];
        }

        return response()->json([
            'stock_center_id' => (int) $centerId,
            'only_diff' => $onlyDiff,
            'total_products' => $products->count(),
            'diff_count' => $diffCount,
            'items' => $items,
        ]);
    }
}
