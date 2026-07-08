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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ExitNotesController extends Controller
{
    use ManagesPrincipalStock;

    public function index()
    {
        $searchQuery = request('query');

        $exitnotes = ExitNotes::query()
            ->when(request('query'), function ($query, $searchQuery) {
                $query->where('ref', 'like', "%{$searchQuery}%");
            })
            ->with('stockcenter')
            ->with('supplier')
            ->orderBy('created_at', 'desc')
            ->paginate();

        return response()->json($exitnotes);
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
            $exitnote = DB::transaction(function () use ($data) {
                $stockCenterId = (int) $data['stock_center_id'];

                // Validação com lock dentro da mesma transação
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

        return $exitnote;
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
        $exitnote = ExitNotes::find($id);
        $exitnote->delete();

        return true;
    }
}
