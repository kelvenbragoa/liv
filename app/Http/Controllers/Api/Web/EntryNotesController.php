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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class EntryNotesController extends Controller
{
    use ManagesPrincipalStock;

    public function index()
    {
        $searchQuery = request('query');

        $entrynotes = EntryNotes::query()
            ->when(request('query'), function ($query, $searchQuery) {
                $query->where('ref', 'like', "%{$searchQuery}%");
            })
            ->with('stockcenter')
            ->with('supplier')
            ->orderBy('created_at', 'desc')
            ->paginate();

        return response()->json($entrynotes);
    }

    public function create()
    {
        $suppliers = Supplier::get();
        $stockcenters = StockCenter::get();

        return response()->json([
            'suppliers' => $suppliers,
            'stockcenters' => $stockcenters
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
        $entrynote = EntryNotes::find($id);
        $entrynote->delete();

        return true;
    }
}
