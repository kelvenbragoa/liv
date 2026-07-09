<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Concerns\ManagesPrincipalStock;
use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryItem;
use App\Models\StockCenter;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoriesController extends Controller
{
    use ManagesPrincipalStock;

    private function buildFilteredQuery(Request $request)
    {
        $sortBy = $request->input('sort_by', 'created_at');
        $allowedSort = [
            'id',
            'ref',
            'stock_center_id',
            'user_id',
            'products_number',
            'items_count',
            'total_quantity',
            'total_adjustment',
            'created_at',
        ];

        if (! in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'created_at';
        }

        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $query = Inventory::query()
            ->when($request->filled('query'), function ($query) use ($request) {
                $search = $request->input('query');
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where('id', 'like', "%{$search}%")
                        ->orWhere('ref', 'like', "%{$search}%")
                        ->orWhereHas('stockcenter', function ($centerQuery) use ($search) {
                            $centerQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('stock_center_id'), function ($query) use ($request) {
                $query->where('stock_center_id', $request->integer('stock_center_id'));
            })
            ->when($request->filled('user_id'), function ($query) use ($request) {
                $query->where('user_id', $request->integer('user_id'));
            })
            ->when($request->filled('created_from'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->input('created_from'));
            })
            ->when($request->filled('created_to'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->input('created_to'));
            })
            ->with(['stockcenter', 'user'])
            ->withCount('itens as items_count')
            ->withSum('itens as total_quantity', 'quantity')
            ->select('inventories.*')
            ->selectSub(function ($sub) {
                $sub->from('inventory_items')
                    ->selectRaw('COALESCE(SUM(quantity - last_quantity), 0)')
                    ->whereColumn('inventory_items.inventory_id', 'inventories.id');
            }, 'total_adjustment');

        if ($sortBy === 'total_adjustment') {
            $query->orderBy('total_adjustment', $sortDir);
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $perPage = min(max((int) $request->input('per_page', 15), 5), 100);

        $inventories = $this->buildFilteredQuery($request)
            ->paginate($perPage)
            ->withQueryString();

        return response()->json($inventories);
    }

    /**
     * Export filtered inventories for Excel download.
     */
    public function export(Request $request)
    {
        $inventories = $this->buildFilteredQuery($request)->get();

        $data = $inventories->map(function ($inventory) {
            return [
                'id' => $inventory->id,
                'ref' => $inventory->ref,
                'stock_center' => $inventory->stockcenter?->name,
                'user' => $inventory->user?->name,
                'products_number' => $inventory->products_number,
                'items_count' => $inventory->items_count ?? 0,
                'total_quantity' => $inventory->total_quantity ?? 0,
                'total_adjustment' => $inventory->total_adjustment ?? 0,
                'created_at' => $inventory->created_at?->format('d/m/Y H:i'),
            ];
        });

        return response()->json([
            'data' => $data,
            'total' => $data->count(),
        ]);
    }

    public function create()
    {
        return response()->json([
            'stockcenters' => StockCenter::orderBy('name')->get(),
            'users' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        if (!isset($data['stockcenterproducts']) || !is_array($data['stockcenterproducts']) || empty($data['stockcenterproducts'])) {
            return response()->json([
                'message' => 'Nenhum produto foi selecionado. Por favor, selecione pelo menos um produto.'
            ], 422);
        }

        try {
            $inventory = DB::transaction(function () use ($data) {
                $stockCenterId = (int) $data['stock_center_id'];

                $inventory = Inventory::create([
                    'user_id' => Auth::user()->id,
                    'ref' => $data['reference'],
                    'stock_center_id' => $stockCenterId,
                    'products_number' => count($data['stockcenterproducts']),
                ]);

                foreach ($data['stockcenterproducts'] as $item) {
                    $newQty = (int) ($item['quantity'] ?? 0);
                    $productId = (int) $item['product_id'];

                    $inventoryItem = InventoryItem::create([
                        'stock_center_id' => $stockCenterId,
                        'inventory_id' => $inventory->id,
                        'product_id' => $productId,
                        'quantity' => $newQty,
                        'last_quantity' => 0,
                    ]);

                    $lastQuantity = $this->setStockAbsolute(
                        $stockCenterId,
                        $productId,
                        $newQty,
                        StockMovement::REASON_INVENTORY,
                        InventoryItem::class,
                        (int) $inventoryItem->id
                    );

                    $inventoryItem->update([
                        'last_quantity' => $lastQuantity,
                    ]);
                }

                return $inventory;
            });
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($inventory);
    }

    public function show(string $id)
    {
        $inventory = Inventory::with(['stockcenter', 'itens.product', 'user'])->find($id);

        if (! $inventory) {
            return response()->json(['message' => 'Inventário não encontrado.'], 404);
        }

        $items = $inventory->itens;
        $mappedItems = $items->map(function ($item) {
            $last = (int) ($item->last_quantity ?? 0);
            $qty = (int) ($item->quantity ?? 0);

            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product?->name,
                'last_quantity' => $last,
                'quantity' => $qty,
                'adjustment' => $qty - $last,
            ];
        });

        $positiveAdjustments = (int) $mappedItems->where('adjustment', '>', 0)->sum('adjustment');
        $negativeAdjustments = (int) abs($mappedItems->where('adjustment', '<', 0)->sum('adjustment'));
        $netAdjustment = (int) $mappedItems->sum('adjustment');

        return response()->json([
            'inventory' => [
                'id' => $inventory->id,
                'ref' => $inventory->ref,
                'products_number' => $inventory->products_number,
                'obs' => $inventory->obs,
                'created_at' => $inventory->created_at,
            ],
            'stock_center' => $inventory->stockcenter ? [
                'id' => $inventory->stockcenter->id,
                'name' => $inventory->stockcenter->name,
            ] : null,
            'user' => $inventory->user ? [
                'id' => $inventory->user->id,
                'name' => $inventory->user->name,
            ] : null,
            'metrics' => [
                'items_count' => $items->count(),
                'products_number' => (int) ($inventory->products_number ?? 0),
                'positive_adjustments' => $positiveAdjustments,
                'negative_adjustments' => $negativeAdjustments,
                'net_adjustment' => $netAdjustment,
                'items_with_difference' => $mappedItems->where('adjustment', '!=', 0)->count(),
            ],
            'items' => $mappedItems->values(),
        ]);
    }

    public function edit(string $id)
    {
        $inventory = Inventory::find($id);

        return response()->json($inventory);
    }

    public function update(Request $request, string $id)
    {
        $data = $request->all();
        $inventory = Inventory::find($id);
        $inventory->update($data);

        return response()->json($inventory);
    }

    public function destroy(string $id)
    {
        return response()->json(['message' => 'Operação não permitida.'], 403);
    }

    public function center()
    {
        //
    }
}
