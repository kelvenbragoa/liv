<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Concerns\ManagesPrincipalStock;
use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryItem;
use App\Models\StockCenter;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoriesController extends Controller
{
    use ManagesPrincipalStock;

    public function index()
    {
        $searchQuery = request('query');

        $inventories = Inventory::query()
            ->when(request('query'), function ($query, $searchQuery) {
                $query->where('ref', 'like', "%{$searchQuery}%");
            })
            ->with('stockcenter')
            ->orderBy('created_at', 'desc')
            ->paginate();

        return response()->json($inventories);
    }

    public function create()
    {
        $stockcenters = StockCenter::all();

        return response()->json(["stockcenters" => $stockcenters]);
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
                        'last_quantity' => 0, // actualizado a seguir com o valor real
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
        $inventory = Inventory::with('stockcenter')
            ->with('itens.product')
            ->with('user')
            ->find($id);

        return response()->json($inventory);
    }

    public function edit(string $id)
    {
        $inventory = Inventory::find($id);

        return $inventory;
    }

    public function update(Request $request, string $id)
    {
        $data = $request->all();
        $inventory = Inventory::find($id);
        $inventory->update($data);

        return $inventory;
    }

    public function destroy(string $id)
    {
        $inventory = Inventory::find($id);
        $inventory->delete();

        return true;
    }

    public function center()
    {
        //
    }
}
