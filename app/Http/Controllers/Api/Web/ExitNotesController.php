<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Concerns\ManagesPrincipalStock;
use App\Http\Controllers\Controller;
use App\Models\ExitNoteItem;
use App\Models\ExitNotes;
use App\Models\StockCenter;
use App\Models\StockCenterProduct;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ExitNotesController extends Controller
{
    use ManagesPrincipalStock;

    private function buildFilteredQuery(Request $request)
    {
        $sortBy = $request->input('sort_by', 'created_at');
        $allowedSort = [
            'id',
            'ref',
            'document_ref',
            'serie',
            'stock_center_id',
            'supplier_id',
            'user_id',
            'products_number',
            'items_count',
            'total_quantity',
            'created_at',
        ];

        if (! in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'created_at';
        }

        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        return ExitNotes::query()
            ->when($request->filled('query'), function ($query) use ($request) {
                $search = $request->input('query');
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where('id', 'like', "%{$search}%")
                        ->orWhere('ref', 'like', "%{$search}%")
                        ->orWhere('document_ref', 'like', "%{$search}%")
                        ->orWhere('serie', 'like', "%{$search}%")
                        ->orWhereHas('stockcenter', function ($centerQuery) use ($search) {
                            $centerQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                            $supplierQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('stock_center_id'), function ($query) use ($request) {
                $query->where('stock_center_id', $request->integer('stock_center_id'));
            })
            ->when($request->filled('supplier_id'), function ($query) use ($request) {
                $query->where('supplier_id', $request->integer('supplier_id'));
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
            ->with(['stockcenter', 'supplier', 'user'])
            ->withCount('itens as items_count')
            ->withSum('itens as total_quantity', 'quantity')
            ->orderBy($sortBy, $sortDir);
    }

    public function index(Request $request)
    {
        $perPage = min(max((int) $request->input('per_page', 15), 5), 100);

        $exitnotes = $this->buildFilteredQuery($request)
            ->paginate($perPage)
            ->withQueryString();

        return response()->json($exitnotes);
    }

    /**
     * Export filtered exit notes for Excel download.
     */
    public function export(Request $request)
    {
        $exitnotes = $this->buildFilteredQuery($request)->get();

        $data = $exitnotes->map(function ($exitnote) {
            return [
                'id' => $exitnote->id,
                'ref' => $exitnote->ref,
                'stock_center' => $exitnote->stockcenter?->name,
                'supplier' => $exitnote->supplier?->name,
                'document_ref' => $exitnote->document_ref,
                'serie' => $exitnote->serie,
                'user' => $exitnote->user?->name,
                'products_number' => $exitnote->products_number,
                'items_count' => $exitnote->items_count ?? 0,
                'total_quantity' => $exitnote->total_quantity ?? 0,
                'created_at' => $exitnote->created_at?->format('d/m/Y H:i'),
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
            'suppliers' => Supplier::orderBy('name')->get(),
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
            $exitnote = DB::transaction(function () use ($data) {
                $stockCenterId = (int) $data['stock_center_id'];

                foreach ($data['stockcenterproducts'] as $item) {
                    $qty = (int) ($item['quantity'] ?? 0);
                    if ($qty <= 0) {
                        continue;
                    }

                    $row = StockCenterProduct::where('id', $item['id'])->lockForUpdate()->first()
                        ?? StockCenterProduct::where('stock_center_id', $stockCenterId)
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

                $exitnote = ExitNotes::create([
                    'user_id' => Auth::user()->id,
                    'ref' => $data['reference'],
                    'document_ref' => $data['document_reference'],
                    'serie' => $data['serie'],
                    'supplier_id' => $data['stock_supplier_id'],
                    'stock_center_id' => $stockCenterId,
                    'products_number' => count($data['stockcenterproducts']),
                ]);

                foreach ($data['stockcenterproducts'] as $item) {
                    $qty = (int) ($item['quantity'] ?? 0);
                    if ($qty <= 0) {
                        continue;
                    }

                    $productId = (int) $item['product_id'];

                    $existing = StockCenterProduct::where('stock_center_id', $stockCenterId)
                        ->where('product_id', $productId)
                        ->lockForUpdate()
                        ->first();
                    $lastQuantity = (int) ($existing?->quantity ?? 0);

                    $exitNoteItem = ExitNoteItem::create([
                        'stock_center_id' => $stockCenterId,
                        'exit_note_id' => $exitnote->id,
                        'product_id' => $productId,
                        'quantity' => $qty,
                        'last_quantity' => $lastQuantity,
                    ]);

                    $this->debitStock(
                        $stockCenterId,
                        $productId,
                        $qty,
                        StockMovement::REASON_EXIT,
                        ExitNoteItem::class,
                        (int) $exitNoteItem->id
                    );
                }

                return $exitnote;
            });
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($exitnote);
    }

    public function show(string $id)
    {
        $exitnote = ExitNotes::with('stockcenter')
            ->with('user')
            ->with('supplier')
            ->with('itens.product')
            ->find($id);

        return response()->json($exitnote);
    }

    public function edit(string $id)
    {
        $exitnote = ExitNotes::find($id);

        return response()->json($exitnote);
    }

    public function update(Request $request, string $id)
    {
        $data = $request->all();
        $exitnote = ExitNotes::find($id);
        $exitnote->update($data);

        return response()->json($exitnote);
    }

    public function destroy(string $id)
    {
        return response()->json(['message' => 'Operação não permitida.'], 403);
    }
}
