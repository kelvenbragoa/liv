<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Concerns\ManagesPrincipalStock;
use App\Http\Controllers\Controller;
use App\Models\StockCenter;
use App\Models\StockCenterProduct;
use App\Models\StockCenterTransfer;
use App\Models\StockCenterTransferItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockTransferController extends Controller
{
    use ManagesPrincipalStock;

    public function index()
    {
        $searchQuery = request('query');

        $stocktransfers = StockCenterTransfer::query()
            ->when(request('query'), function ($query, $searchQuery) {
                $query->where('ref', 'like', "%{$searchQuery}%");
            })
            ->with('stockcenterorigin')
            ->with('stockcenterdestination')
            ->orderBy('created_at', 'desc')
            ->paginate();

        return response()->json($stocktransfers);
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
            DB::transaction(function () use ($data) {
                $originId = (int) $data['stock_center_origin_id'];
                $destinationId = (int) $data['stock_center_destination_id'];

                if ($originId === $destinationId) {
                    throw new RuntimeException('O centro de origem e destino não podem ser iguais.');
                }

                foreach ($data['stockcenterproducts'] as $item) {
                    $qty = (int) ($item['quantity'] ?? 0);
                    if ($qty <= 0) {
                        continue;
                    }

                    $row = StockCenterProduct::where('id', $item['id'])->lockForUpdate()->first()
                        ?? StockCenterProduct::where('stock_center_id', $originId)
                            ->where('product_id', $item['product_id'])
                            ->lockForUpdate()
                            ->first();

                    if (!$row || $row->quantity < $qty) {
                        $name = $row?->product?->name ?? ("ID: " . ($item['product_id'] ?? '?'));
                        $available = $row?->quantity ?? 0;
                        throw new RuntimeException(
                            "Estoque insuficiente para {$name}: disponível {$available}, solicitado {$qty}."
                        );
                    }
                }

                $stockTransfer = StockCenterTransfer::create([
                    'ref' => $data['reference'],
                    'user_id' => Auth::user()->id,
                    'transfer_date' => $data['transfer_date'],
                    'stock_center_transfer_status_id' => 1,
                    'stock_center_origin_id' => $originId,
                    'stock_center_destination_id' => $destinationId,
                ]);

                foreach ($data['stockcenterproducts'] as $item) {
                    $qty = (int) ($item['quantity'] ?? 0);
                    if ($qty <= 0) {
                        continue;
                    }

                    $productId = (int) $item['product_id'];

                    $transferItem = StockCenterTransferItem::create([
                        'stock_center_transfer_id' => $stockTransfer->id,
                        'product_id' => $productId,
                        'quantity' => $qty,
                    ]);

                    $this->debitStock(
                        $originId,
                        $productId,
                        $qty,
                        StockMovement::REASON_TRANSFER_OUT,
                        StockCenterTransferItem::class,
                        (int) $transferItem->id
                    );

                    $this->creditStock(
                        $destinationId,
                        $productId,
                        $qty,
                        StockMovement::REASON_TRANSFER_IN,
                        StockCenterTransferItem::class,
                        (int) $transferItem->id,
                        null,
                        true
                    );
                }
            });
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return [
            'message' => 'success'
        ];
    }

    public function show(string $id)
    {
        $stocktransfer = StockCenterTransfer::with('stockcenterorigin')
            ->with('stockcenterdestination')
            ->with('itens.product')
            ->with('user')
            ->find($id);

        return response()->json($stocktransfer);
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        $data = $request->all();
        $category = StockCenterTransfer::find($id);
        $category->update($data);

        return response()->json($category);
    }

    public function destroy(string $id)
    {
        $category = StockCenterTransfer::find($id);
        $category->delete();

        return true;
    }

    public function products($id)
    {
        $stockcenterproducts = StockCenterProduct::with('product.category')
            ->with('stockcenter')
            ->with('product.subcategory')
            ->where('stock_center_id', $id)
            ->get();

        return [
            'stockcenterproducts' => $stockcenterproducts
        ];
    }
}
