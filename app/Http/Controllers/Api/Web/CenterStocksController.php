<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockCenter;
use App\Models\StockCenterProduct;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;


class CenterStocksController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

            $stockcenter = StockCenter::query()
            ->when(request('query'),function($query,$searchQuery){
                $query->where('name','like',"%{$searchQuery}%");
            })
            ->orderBy('name','asc')
            ->paginate();

            return response()->json($stockcenter);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        // $departments = Department::all();

        // return response()->json([
        //     'departments' => $departments
        // ]);
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
        //

        $stockcenter = StockCenter::find($id);
        $stockcenter->delete();
        return true;
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
