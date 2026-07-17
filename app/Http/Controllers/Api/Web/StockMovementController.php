<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockCenter;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    private const REASON_LABELS = [
        StockMovement::REASON_SALE => 'Venda',
        StockMovement::REASON_SALE_CANCEL => 'Cancelamento de venda',
        StockMovement::REASON_ADJUSTMENT => 'Ajuste',
        StockMovement::REASON_ENTRY => 'Nota de entrada',
        StockMovement::REASON_EXIT => 'Nota de saída',
        StockMovement::REASON_TRANSFER_OUT => 'Transferência (saída)',
        StockMovement::REASON_TRANSFER_IN => 'Transferência (entrada)',
        StockMovement::REASON_INVENTORY => 'Inventário',
    ];

    private function reasonLabel(?string $reason): string
    {
        if (! $reason) {
            return '—';
        }

        return self::REASON_LABELS[$reason] ?? $reason;
    }

    private function referenceLabel(?string $type, ?int $id): ?string
    {
        if (! $type && ! $id) {
            return null;
        }

        $short = $type ? class_basename($type) : 'Ref';

        return $id ? "{$short} #{$id}" : $short;
    }

    private function buildFilteredQuery(Request $request)
    {
        $sortBy = $request->input('sort_by', 'created_at');
        $allowedSort = [
            'id',
            'created_at',
            'quantity',
            'quantity_before',
            'quantity_after',
            'direction',
            'reason',
            'product_id',
            'stock_center_id',
            'user_id',
        ];

        if (! in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'created_at';
        }

        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        return StockMovement::query()
            ->when($request->filled('query'), function ($query) use ($request) {
                $search = $request->input('query');
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where('id', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhere('reference_id', 'like', "%{$search}%")
                        ->orWhere('reason', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($productQuery) use ($search) {
                            $productQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('stockCenter', function ($centerQuery) use ($search) {
                            $centerQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('product_id'), function ($query) use ($request) {
                $query->where('product_id', $request->integer('product_id'));
            })
            ->when($request->filled('stock_center_id'), function ($query) use ($request) {
                $query->where('stock_center_id', $request->integer('stock_center_id'));
            })
            ->when($request->filled('user_id'), function ($query) use ($request) {
                $query->where('user_id', $request->integer('user_id'));
            })
            ->when($request->filled('direction'), function ($query) use ($request) {
                $direction = $request->input('direction');
                if (in_array($direction, [StockMovement::DIRECTION_IN, StockMovement::DIRECTION_OUT], true)) {
                    $query->where('direction', $direction);
                }
            })
            ->when($request->filled('reason'), function ($query) use ($request) {
                $query->where('reason', $request->input('reason'));
            })
            ->when($request->filled('created_from'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->input('created_from'));
            })
            ->when($request->filled('created_to'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->input('created_to'));
            })
            ->with(['product:id,name', 'stockCenter:id,name', 'user:id,name'])
            ->orderBy($sortBy, $sortDir);
    }

    private function mapMovement(StockMovement $movement): array
    {
        $signedQty = $movement->direction === StockMovement::DIRECTION_OUT
            ? -1 * (int) $movement->quantity
            : (int) $movement->quantity;

        return [
            'id' => $movement->id,
            'direction' => $movement->direction,
            'direction_label' => $movement->direction === StockMovement::DIRECTION_IN ? 'Entrada' : 'Saída',
            'quantity' => (int) $movement->quantity,
            'signed_quantity' => $signedQty,
            'quantity_before' => (int) $movement->quantity_before,
            'quantity_after' => (int) $movement->quantity_after,
            'reason' => $movement->reason,
            'reason_label' => $this->reasonLabel($movement->reason),
            'reference_type' => $movement->reference_type,
            'reference_id' => $movement->reference_id,
            'reference_label' => $this->referenceLabel($movement->reference_type, $movement->reference_id),
            'notes' => $movement->notes,
            'created_at' => $movement->created_at,
            'product' => $movement->product ? [
                'id' => $movement->product->id,
                'name' => $movement->product->name,
            ] : null,
            'stock_center' => $movement->stockCenter ? [
                'id' => $movement->stockCenter->id,
                'name' => $movement->stockCenter->name,
            ] : null,
            'user' => $movement->user ? [
                'id' => $movement->user->id,
                'name' => $movement->user->name,
            ] : null,
        ];
    }

    public function index(Request $request)
    {
        $perPage = min(max((int) $request->input('per_page', 15), 5), 100);

        $paginator = $this->buildFilteredQuery($request)
            ->paginate($perPage)
            ->withQueryString();

        $paginator->setCollection(
            $paginator->getCollection()->map(fn (StockMovement $movement) => $this->mapMovement($movement))
        );

        $baseQuery = $this->buildFilteredQuery($request);
        $inQty = (clone $baseQuery)->where('direction', StockMovement::DIRECTION_IN)->sum('quantity');
        $outQty = (clone $baseQuery)->where('direction', StockMovement::DIRECTION_OUT)->sum('quantity');

        return response()->json([
            'data' => $paginator->items(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'summary' => [
                'total_in' => (int) $inQty,
                'total_out' => (int) $outQty,
                'net' => (int) $inQty - (int) $outQty,
            ],
        ]);
    }

    public function export(Request $request)
    {
        $movements = $this->buildFilteredQuery($request)->get();

        $data = $movements->map(function (StockMovement $movement) {
            $mapped = $this->mapMovement($movement);

            return [
                'id' => $mapped['id'],
                'created_at' => $movement->created_at?->format('d/m/Y H:i'),
                'product' => $mapped['product']['name'] ?? null,
                'stock_center' => $mapped['stock_center']['name'] ?? null,
                'direction' => $mapped['direction_label'],
                'reason' => $mapped['reason_label'],
                'quantity' => $mapped['quantity'],
                'signed_quantity' => $mapped['signed_quantity'],
                'quantity_before' => $mapped['quantity_before'],
                'quantity_after' => $mapped['quantity_after'],
                'reference' => $mapped['reference_label'],
                'user' => $mapped['user']['name'] ?? null,
                'notes' => $mapped['notes'],
            ];
        });

        return response()->json([
            'data' => $data,
            'total' => $data->count(),
        ]);
    }

    public function create()
    {
        $reasons = collect(self::REASON_LABELS)
            ->map(fn ($label, $value) => ['value' => $value, 'label' => $label])
            ->values();

        return response()->json([
            'stockcenters' => StockCenter::orderBy('name')->get(['id', 'name']),
            'users' => User::orderBy('name')->get(['id', 'name']),
            'products' => Product::orderBy('name')->get(['id', 'name']),
            'reasons' => $reasons,
            'directions' => [
                ['value' => StockMovement::DIRECTION_IN, 'label' => 'Entrada'],
                ['value' => StockMovement::DIRECTION_OUT, 'label' => 'Saída'],
            ],
        ]);
    }
}
