<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Models\EntryNotes;
use App\Models\ExitNotes;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SuppliersController extends Controller
{
    private function buildFilteredQuery(Request $request)
    {
        $sortBy = $request->input('sort_by', 'name');
        $allowedSort = [
            'id',
            'name',
            'email',
            'mobile',
            'address',
            'city',
            'country',
            'nuit',
            'website',
            'created_at',
        ];

        if (! in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'name';
        }

        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        return Supplier::query()
            ->when($request->filled('query'), function ($query) use ($request) {
                $search = $request->input('query');
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhere('nuit', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('country', 'like', "%{$search}%")
                        ->orWhere('website', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('country'), function ($query) use ($request) {
                $query->where('country', $request->input('country'));
            })
            ->when($request->filled('created_from'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->input('created_from'));
            })
            ->when($request->filled('created_to'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->input('created_to'));
            })
            ->orderBy($sortBy, $sortDir);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = min(max((int) $request->input('per_page', 15), 5), 100);

        $suppliers = $this->buildFilteredQuery($request)
            ->paginate($perPage)
            ->withQueryString();

        return response()->json($suppliers);
    }

    /**
     * Export filtered suppliers for Excel download.
     */
    public function export(Request $request)
    {
        $suppliers = $this->buildFilteredQuery($request)->get();

        $data = $suppliers->map(function ($supplier) {
            return [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'address' => $supplier->address,
                'city' => $supplier->city,
                'country' => $supplier->country,
                'email' => $supplier->email,
                'mobile' => $supplier->mobile,
                'nuit' => $supplier->nuit,
                'website' => $supplier->website,
                'created_at' => $supplier->created_at?->format('d/m/Y H:i'),
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
        return response()->json([
            'countries' => Supplier::query()
                ->whereNotNull('country')
                ->where('country', '!=', '')
                ->distinct()
                ->orderBy('country')
                ->pluck('country')
                ->values(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $supplier = Supplier::create($data);

        return response()->json($supplier);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $supplier = Supplier::query()->whereKey($id)->first();

        if (! $supplier) {
            return response()->json(['message' => 'Fornecedor não encontrado.'], 404);
        }

        $entryNotesCount = EntryNotes::query()->where('supplier_id', $supplier->id)->count();
        $exitNotesCount = ExitNotes::query()->where('supplier_id', $supplier->id)->count();
        $purchaseOrdersCount = PurchaseOrder::query()->where('supplier_id', $supplier->id)->count();
        $purchaseOrdersTotal = (float) (PurchaseOrder::query()->where('supplier_id', $supplier->id)->sum('total_value') ?? 0);
        $latestPurchaseOrderDate = PurchaseOrder::query()
            ->where('supplier_id', $supplier->id)
            ->max('order_date');

        return response()->json([
            'supplier' => [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'address' => $supplier->address,
                'city' => $supplier->city,
                'country' => $supplier->country,
                'email' => $supplier->email,
                'mobile' => $supplier->mobile,
                'nuit' => $supplier->nuit,
                'website' => $supplier->website,
                'created_at' => $supplier->created_at,
            ],
            'metrics' => [
                'entry_notes_count' => $entryNotesCount,
                'exit_notes_count' => $exitNotesCount,
                'purchase_orders_count' => $purchaseOrdersCount,
                'purchase_orders_total' => $purchaseOrdersTotal,
                'latest_purchase_order_date' => $latestPurchaseOrderDate,
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $supplier = Supplier::find($id);

        return response()->json([
            'supplier' => $supplier,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->all();
        $supplier = Supplier::find($id);
        $supplier->update($data);

        return response()->json($supplier);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $supplier = Supplier::find($id);

        if (! $supplier) {
            return response()->json(['message' => 'Fornecedor não encontrado.'], 404);
        }

        $hasEntryNotes = EntryNotes::where('supplier_id', $supplier->id)->exists();
        $hasExitNotes = ExitNotes::where('supplier_id', $supplier->id)->exists();
        $hasPurchaseOrders = PurchaseOrder::where('supplier_id', $supplier->id)->exists();

        if ($hasEntryNotes) {
            return response()->json(['message' => 'Não é possível eliminar o fornecedor, existem notas de entrada associadas.'], 422);
        }

        if ($hasExitNotes) {
            return response()->json(['message' => 'Não é possível eliminar o fornecedor, existem notas de saída associadas.'], 422);
        }

        if ($hasPurchaseOrders) {
            return response()->json(['message' => 'Não é possível eliminar o fornecedor, existem encomendas de compra associadas.'], 422);
        }

        $supplier->delete();

        return response()->json(['message' => 'Fornecedor removido com sucesso.']);
    }
}
