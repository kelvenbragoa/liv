<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Concerns\ManagesPrincipalStock;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Table;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use RuntimeException;


class OrderController extends Controller
{
    use ManagesPrincipalStock;

    private function buildFilteredQuery(Request $request)
    {
        $sortBy = $request->input('sort_by', 'created_at');
        $allowedSort = ['id', 'total', 'table_id', 'order_status_id', 'user_id', 'created_at'];

        if (! in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'created_at';
        }

        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        return Order::query()
            ->when($request->filled('query'), function ($query) use ($request) {
                $search = $request->input('query');
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where('id', 'like', "%{$search}%")
                        ->orWhere('total', 'like', "%{$search}%")
                        ->orWhereHas('table', function ($tableQuery) use ($search) {
                            $tableQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('order_status_id'), function ($query) use ($request) {
                $query->where('order_status_id', $request->integer('order_status_id'));
            })
            ->when($request->has('quick_sale') && $request->input('quick_sale') !== null && $request->input('quick_sale') !== '', function ($query) use ($request) {
                if ($request->boolean('quick_sale')) {
                    $query->whereNull('table_id');
                } else {
                    $query->whereNotNull('table_id');
                }
            })
            ->when($request->filled('table_id'), function ($query) use ($request) {
                $query->where('table_id', $request->integer('table_id'));
            })
            ->when($request->filled('created_from'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->input('created_from'));
            })
            ->when($request->filled('created_to'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->input('created_to'));
            })
            ->with(['table', 'status', 'user'])
            ->orderBy($sortBy, $sortDir);
    }

    /**
     * Display a listing of the resource.
     */
    public function report(){
        $orders = Order::with('table')
        ->with('itens')
        ->with('status')
        ->orderBy('created_at','desc')
        ->get();
        $products = Product::get();
        $tables = Table::get();

        $users = User::get();

        $pdf = Pdf::loadView('pdf.report', compact('orders','products','tables','users'))->setOptions([
            'setPaper'=>'a4',
            // 'setPaper' => [0, 0, 226.77, 500],
            'defaultFont' => 'sans-serif',
            'isRemoteEnabled' => 'true'
        ]);
        return $pdf->stream('report.pdf');
    }

    public function index(Request $request)
    {
        $perPage = min(max((int) $request->input('per_page', 15), 5), 100);

        $orders = $this->buildFilteredQuery($request)
            ->paginate($perPage)
            ->withQueryString();

        return response()->json($orders);
    }

    /**
     * Export filtered orders for Excel download.
     */
    public function export(Request $request)
    {
        $orders = $this->buildFilteredQuery($request)->get();

        $data = $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'total' => $order->total,
                'table' => $order->table?->name ?? 'Venda rápida',
                'status' => $order->status?->name,
                'user' => $order->user?->name,
                'amount_tendered' => $order->amount_tendered,
                'change_amount' => $order->change_amount,
                'created_at' => $order->created_at?->format('d/m/Y H:i'),
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
            'order_statuses' => OrderStatus::orderBy('name')->get(),
            'tables' => Table::orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order = Order::query()
            ->with(['table', 'status', 'user'])
            ->find($id);

        if (! $order) {
            return response()->json(['message' => 'Pedido não encontrado.'], 404);
        }

        $items = OrderItem::query()
            ->where('order_id', $order->id)
            ->with(['product'])
            ->get(['id', 'order_id', 'product_id', 'quantity', 'price', 'total', 'created_at']);

        $payments = Payment::query()
            ->where('order_id', $order->id)
            ->with('method')
            ->get(['id', 'order_id', 'payment_method_id', 'amount', 'created_at']);

        $itemsCount = $items->count();
        $totalQuantity = (int) $items->sum('quantity');
        $distinctProducts = $items->pluck('product_id')->filter()->unique()->count();
        $paymentsTotal = (float) $payments->sum('amount');
        $paymentsCount = $payments->count();

        return response()->json([
            'order' => [
                'id' => $order->id,
                'total' => $order->total,
                'amount_tendered' => $order->amount_tendered,
                'change_amount' => $order->change_amount,
                'created_at' => $order->created_at,
            ],
            'table' => $order->table ? [
                'id' => $order->table->id,
                'name' => $order->table->name,
            ] : null,
            'status' => $order->status ? [
                'id' => $order->status->id,
                'name' => $order->status->name,
            ] : null,
            'user' => $order->user ? [
                'id' => $order->user->id,
                'name' => $order->user->name,
            ] : null,
            'metrics' => [
                'items_count' => $itemsCount,
                'total_quantity' => $totalQuantity,
                'distinct_products' => $distinctProducts,
                'payments_count' => $paymentsCount,
                'payments_total' => $paymentsTotal,
                'balance_remaining' => (float) ($order->total ?? 0) - $paymentsTotal,
            ],
            'items' => $items,
            'payments' => $payments,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }


    public function deleteorderitem(string $id){
        $orderitem = OrderItem::find($id);

        $table_id = $orderitem->order->table_id;
        $total = $orderitem->total;
        $quantity = $orderitem->quantity;
        $productId = (int) $orderitem->product_id;

        try {
            DB::transaction(function () use ($orderitem, $quantity, $productId, $total) {
                $this->creditPrincipalStock(
                    $productId,
                    (int) $quantity,
                    \App\Models\StockMovement::REASON_SALE_CANCEL,
                    OrderItem::class,
                    (int) $orderitem->id
                );

                $order = $orderitem->order;
                $orderitem->delete();

                $order->update([
                    'total' => $order->total - $total
                ]);
            });
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }

        $categories = Category::with(['sub_categories.products' => function($query) {
            $query->withQuantityInPrincipalStock();
        }])->get();
        $order = Order::where('table_id', $table_id)
        ->where(function($query) {
            $query->where('order_status_id', 1)
                ->orWhere('order_status_id', 2);
        })
        ->first();
        $order_id = 0;
        if ($order) {
            $order_id = $order->id;
        }

        
        $total_consumed = Order::where('table_id', $table_id)
        ->where(function($query) {
            $query->where('order_status_id', 1)
                ->orWhere('order_status_id', 2);
        })
        ->sum('total');

        $payment_methods = PaymentMethod::all();
        $orderItems = OrderItem::where('order_id',$order_id)->with('product')->get();

        return response()->json([
            "categories"=>$categories,
            "total_consumed"=>$total_consumed,
            "payment_methods"=>$payment_methods,
            "order_items"=>$orderItems
        ]);

       
    }


    public function deleteorder($id){
        $order = Order::find($id);

        try {
            DB::transaction(function () use ($order) {
                foreach ($order->itens as $item) {
                    $this->creditPrincipalStock(
                        (int) $item->product_id,
                        (int) $item->quantity,
                        \App\Models\StockMovement::REASON_SALE_CANCEL,
                        OrderItem::class,
                        (int) $item->id
                    );
                    $item->delete();
                }
                Payment::where('order_id', $order->id)->delete();
                $order->delete();
            });
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }

        return response()->noContent();
    }
}
