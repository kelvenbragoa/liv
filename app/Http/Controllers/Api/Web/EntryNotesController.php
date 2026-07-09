<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Concerns\ManagesPrincipalStock;
use App\Http\Controllers\Controller;
use App\Models\EntryNoteItem;
use App\Models\EntryNotes;
use App\Models\StockCenter;
use App\Models\StockCenterProduct;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class EntryNotesController extends Controller
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

        return EntryNotes::query()
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

        $entrynotes = $this->buildFilteredQuery($request)
            ->paginate($perPage)
            ->withQueryString();

        return response()->json($entrynotes);
    }

    /**
     * Export filtered entry notes for Excel download.
     */
    public function export(Request $request)
    {
        $entrynotes = $this->buildFilteredQuery($request)->get();

        $data = $entrynotes->map(function ($entrynote) {
            return [
                'id' => $entrynote->id,
                'ref' => $entrynote->ref,
                'stock_center' => $entrynote->stockcenter?->name,
                'supplier' => $entrynote->supplier?->name,
                'document_ref' => $entrynote->document_ref,
                'serie' => $entrynote->serie,
                'user' => $entrynote->user?->name,
                'products_number' => $entrynote->products_number,
                'items_count' => $entrynote->items_count ?? 0,
                'total_quantity' => $entrynote->total_quantity ?? 0,
                'created_at' => $entrynote->created_at?->format('d/m/Y H:i'),
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
            $entrynote = DB::transaction(function () use ($data) {
                $entrynote = EntryNotes::create([
                    'user_id' => Auth::user()->id,
                    'ref' => $data['reference'],
                    'document_ref' => $data['document_reference'],
                    'serie' => $data['serie'],
                    'supplier_id' => $data['stock_supplier_id'],
                    'stock_center_id' => $data['stock_center_id'],
                    'products_number' => count($data['stockcenterproducts']),
                ]);

                $stockCenterId = (int) $data['stock_center_id'];

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

                    $entryNoteItem = EntryNoteItem::create([
                        'stock_center_id' => $stockCenterId,
                        'entry_note_id' => $entrynote->id,
                        'product_id' => $productId,
                        'quantity' => $qty,
                        'last_quantity' => $lastQuantity,
                    ]);

                    $this->creditStock(
                        $stockCenterId,
                        $productId,
                        $qty,
                        StockMovement::REASON_ENTRY,
                        EntryNoteItem::class,
                        (int) $entryNoteItem->id
                    );
                }

                return $entrynote;
            });
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($entrynote);
    }

    public function show(string $id)
    {
        $entrynote = EntryNotes::with('stockcenter')
            ->with('user')
            ->with('supplier')
            ->with('itens.product')
            ->find($id);

        return response()->json($entrynote);
    }

    public function edit(string $id)
    {
        $entrynote = EntryNotes::find($id);

        return response()->json($entrynote);
    }

    public function update(Request $request, string $id)
    {
        $data = $request->all();
        $entrynote = EntryNotes::find($id);
        $entrynote->update($data);

        return response()->json($entrynote);
    }

    public function destroy(string $id)
    {
        return response()->json(['message' => 'Operação não permitida.'], 403);
    }
}
