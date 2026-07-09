<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Concerns\ManagesPrincipalStock;
use App\Http\Controllers\Controller;
use App\Models\StockCenter;
use App\Models\StockCenterProduct;
use App\Models\StockCenterTransfer;
use App\Models\StockCenterTransferItem;
use App\Models\StockCenterTransferStatus;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockTransferController extends Controller
{
    use ManagesPrincipalStock;

    private function buildFilteredQuery(Request $request)
    {
        $sortBy = $request->input('sort_by', 'created_at');
        $allowedSort = [
            'id',
            'ref',
            'transfer_date',
            'stock_center_origin_id',
            'stock_center_destination_id',
            'stock_center_transfer_status_id',
            'user_id',
            'items_count',
            'total_quantity',
            'created_at',
        ];

        if (! in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'created_at';
        }

        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        return StockCenterTransfer::query()
            ->when($request->filled('query'), function ($query) use ($request) {
                $search = $request->input('query');
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where('id', 'like', "%{$search}%")
                        ->orWhere('ref', 'like', "%{$search}%")
                        ->orWhereHas('stockcenterorigin', function ($originQuery) use ($search) {
                            $originQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('stockcenterdestination', function ($destinationQuery) use ($search) {
                            $destinationQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('stock_center_origin_id'), function ($query) use ($request) {
                $query->where('stock_center_origin_id', $request->integer('stock_center_origin_id'));
            })
            ->when($request->filled('stock_center_destination_id'), function ($query) use ($request) {
                $query->where('stock_center_destination_id', $request->integer('stock_center_destination_id'));
            })
            ->when($request->filled('stock_center_transfer_status_id'), function ($query) use ($request) {
                $query->where('stock_center_transfer_status_id', $request->integer('stock_center_transfer_status_id'));
            })
            ->when($request->filled('user_id'), function ($query) use ($request) {
                $query->where('user_id', $request->integer('user_id'));
            })
            ->when($request->filled('transfer_from'), function ($query) use ($request) {
                $query->whereDate('transfer_date', '>=', $request->input('transfer_from'));
            })
            ->when($request->filled('transfer_to'), function ($query) use ($request) {
                $query->whereDate('transfer_date', '<=', $request->input('transfer_to'));
            })
            ->when($request->filled('created_from'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->input('created_from'));
            })
            ->when($request->filled('created_to'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->input('created_to'));
            })
            ->with(['stockcenterorigin', 'stockcenterdestination', 'user', 'status'])
            ->withCount('itens as items_count')
            ->withSum('itens as total_quantity', 'quantity')
            ->orderBy($sortBy, $sortDir);
    }

    public function index(Request $request)
    {
        $perPage = min(max((int) $request->input('per_page', 15), 5), 100);

        $stocktransfers = $this->buildFilteredQuery($request)
            ->paginate($perPage)
            ->withQueryString();

        return response()->json($stocktransfers);
    }

    /**
     * Export filtered stock transfers for Excel download.
     */
    public function export(Request $request)
    {
        $stocktransfers = $this->buildFilteredQuery($request)->get();

        $data = $stocktransfers->map(function ($transfer) {
            return [
                'id' => $transfer->id,
                'ref' => $transfer->ref,
                'origin' => $transfer->stockcenterorigin?->name,
                'destination' => $transfer->stockcenterdestination?->name,
                'user' => $transfer->user?->name,
                'status' => $transfer->status?->name,
                'items_count' => $transfer->items_count ?? 0,
                'total_quantity' => $transfer->total_quantity ?? 0,
                'transfer_date' => $transfer->transfer_date?->format('d/m/Y'),
                'created_at' => $transfer->created_at?->format('d/m/Y H:i'),
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
            'statuses' => StockCenterTransferStatus::orderBy('name')->get(),
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
        $stocktransfer = StockCenterTransfer::with([
            'stockcenterorigin',
            'stockcenterdestination',
            'itens.product',
            'user',
            'status',
        ])->find($id);

        if (! $stocktransfer) {
            return response()->json(['message' => 'Transferência não encontrada.'], 404);
        }

        $items = $stocktransfer->itens;
        $totalQuantity = (int) $items->sum('quantity');

        return response()->json([
            'transfer' => [
                'id' => $stocktransfer->id,
                'ref' => $stocktransfer->ref,
                'transfer_date' => $stocktransfer->transfer_date,
                'created_at' => $stocktransfer->created_at,
            ],
            'origin' => $stocktransfer->stockcenterorigin ? [
                'id' => $stocktransfer->stockcenterorigin->id,
                'name' => $stocktransfer->stockcenterorigin->name,
            ] : null,
            'destination' => $stocktransfer->stockcenterdestination ? [
                'id' => $stocktransfer->stockcenterdestination->id,
                'name' => $stocktransfer->stockcenterdestination->name,
            ] : null,
            'status' => $stocktransfer->status ? [
                'id' => $stocktransfer->status->id,
                'name' => $stocktransfer->status->name,
            ] : null,
            'user' => $stocktransfer->user ? [
                'id' => $stocktransfer->user->id,
                'name' => $stocktransfer->user->name,
            ] : null,
            'metrics' => [
                'items_count' => $items->count(),
                'total_quantity' => $totalQuantity,
            ],
            'items' => $items->map(function ($item) use ($stocktransfer) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product?->name,
                    'quantity' => (int) ($item->quantity ?? 0),
                    'origin_name' => $stocktransfer->stockcenterorigin?->name,
                    'destination_name' => $stocktransfer->stockcenterdestination?->name,
                ];
            })->values(),
        ]);
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
        return response()->json(['message' => 'Operação não permitida.'], 403);
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
