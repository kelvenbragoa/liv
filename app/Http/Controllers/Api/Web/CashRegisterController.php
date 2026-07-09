<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Models\CashRegister;
use App\Models\CashRegisterStatus;
use App\Models\CreditSettlement;
use App\Models\DailyStockSnapshot;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\StockSnapshot;
use App\Models\Table;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CashRegisterController extends Controller
{
    private function buildFilteredQuery(Request $request)
    {
        $sortBy = $request->input('sort_by', 'opened_at');
        $allowedSort = [
            'id',
            'opening_balance',
            'closing_balance',
            'cash_register_status_id',
            'user_id',
            'opened_at',
            'closed_at',
            'order_itens_total',
            'created_at',
        ];

        if (! in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'opened_at';
        }

        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        return CashRegister::query()
            ->when($request->filled('query'), function ($query) use ($request) {
                $search = $request->input('query');
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where('id', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('cash_register_status_id'), function ($query) use ($request) {
                $query->where('cash_register_status_id', $request->integer('cash_register_status_id'));
            })
            ->when($request->filled('user_id'), function ($query) use ($request) {
                $query->where('user_id', $request->integer('user_id'));
            })
            ->when($request->filled('opened_from'), function ($query) use ($request) {
                $query->whereDate('opened_at', '>=', $request->input('opened_from'));
            })
            ->when($request->filled('opened_to'), function ($query) use ($request) {
                $query->whereDate('opened_at', '<=', $request->input('opened_to'));
            })
            ->when($request->filled('closed_from'), function ($query) use ($request) {
                $query->whereDate('closed_at', '>=', $request->input('closed_from'));
            })
            ->when($request->filled('closed_to'), function ($query) use ($request) {
                $query->whereDate('closed_at', '<=', $request->input('closed_to'));
            })
            ->with(['user', 'status'])
            ->withSum('orderitens as order_itens_total', 'total')
            ->orderBy($sortBy, $sortDir);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = min(max((int) $request->input('per_page', 15), 5), 100);

        $cashRegisters = $this->buildFilteredQuery($request)
            ->paginate($perPage)
            ->withQueryString();

        return response()->json($cashRegisters);
    }

    /**
     * Export filtered cash registers for Excel download.
     */
    public function export(Request $request)
    {
        $cashRegisters = $this->buildFilteredQuery($request)->get();

        $data = $cashRegisters->map(function ($cashRegister) {
            return [
                'id' => $cashRegister->id,
                'user' => $cashRegister->user?->name,
                'order_itens_total' => $cashRegister->order_itens_total ?? 0,
                'closing_balance' => $cashRegister->closing_balance,
                'opening_balance' => $cashRegister->opening_balance,
                'status' => $cashRegister->status?->name,
                'opened_at' => $cashRegister->opened_at
                    ? Carbon::parse($cashRegister->opened_at)->format('d/m/Y H:i')
                    : null,
                'closed_at' => $cashRegister->closed_at
                    ? Carbon::parse($cashRegister->closed_at)->format('d/m/Y H:i')
                    : null,
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
            'statuses' => CashRegisterStatus::orderBy('name')->get(),
            'users' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function open(Request $request)
{
    $data = $request->all();
    $today = now()->toDateString();

    // Verifica se o usuário já tem caixa aberto
    $existingCashRegister = CashRegister::where('user_id', Auth::id())
        ->where('cash_register_status_id', 1)
        ->first();

    if ($existingCashRegister) {
        return response()->json([
            'message' => 'Já existe um caixa aberto para este usuário.',
            'cash_register' => $existingCashRegister
        ], 400);
    }

    // Cria o caixa
    $cashRegister = CashRegister::create([
        'user_id' => Auth::id(),
        'cash_register_status_id' => 1,
        'opening_balance' => $data['opening_balance'],
        'opened_at' => now(),
    ]);

    // Carrega os produtos com stock atual
    $products = Product::withQuantityInPrincipalStock()->get();

    // Snapshot por caixa (stock visível ao vendedor)
    $snapshots = [];
    foreach ($products as $product) {
        $snapshots[] = [
            'product_id' => $product->id,
            'cash_register_id' => $cashRegister->id,
            'quantity' => $product->quantity_in_principal_stock,
            'date' => $today,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
    StockSnapshot::insert($snapshots);

    // Snapshot diário geral (só se ainda não existir)
    $alreadySnapshotted = DailyStockSnapshot::where('date', $today)->exists();

    if (!$alreadySnapshotted) {
        $dailySnapshots = [];
        foreach ($products as $product) {
            $dailySnapshots[] = [
                'product_id' => $product->id,
                'quantity' => $product->quantity_in_principal_stock,
                'date' => $today,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DailyStockSnapshot::insert($dailySnapshots);
    }

    return response()->json($cashRegister);
}

    // public function open(Request $request){
    //     $data = $request->all();
    //     $existingCashRegister = CashRegister::where('user_id', Auth::user()->id)
    //     ->where('cash_register_status_id', 1)
    //     ->first();

    //     if ($existingCashRegister) {
    //         return response()->json([
    //             'message' => 'Já existe um caixa aberto para este usuário.',
    //             'cash_register' => $existingCashRegister
    //         ], 400);
    //     }

    //     $today = Carbon::today();
    //     $existingCash = CashRegister::whereDate('opened_at', $today)->first();

    //     if (!$existingCash) {
    //         // $products = Product::withQuantityInPrincipalStock()->get();
    //         // foreach ($products as $product) {
    //         //     // StockSnapshot::create([
    //         //     //     'product_id' => $product->id,
    //         //     //     'quantity' => $product->quantity_in_principal_stock,
    //         //     //     'date' => $today,
    //         //     // ]);
    //         // }
    //     }
    //     $cashregister = CashRegister::create([
    //         'user_id' => Auth::user()->id,
    //         'cash_register_status_id' => 1,
    //         'opening_balance'=>$data['opening_balance'],
    //         'opened_at' => now(),
    //     ]);

    //     return response()->json($cashregister);
    // }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //

        $data = $request->all();
        $existingCashRegister = CashRegister::where('user_id', Auth::user()->id)
        ->where('cash_register_status_id', 1)
        ->first();

        if ($existingCashRegister) {
            return response()->json([
                'message' => 'Já existe um caixa aberto para este usuário.',
                'cash_register' => $existingCashRegister
            ], 400);
        }
        $cashregister = CashRegister::create([
            'user_id' => Auth::user()->id,
            'cash_register_status_id' => 1,
            'opening_balance'=>$data['opening_balance'],
            'opened_at' => now(),
        ]);

        return response()->json($cashregister);
    }

    public function close(Request $request)
    {

        $data = $request->all();
        $cashRegister = CashRegister::
            where('user_id', Auth::user()->id)
            ->where('cash_register_status_id', 1) // 1 = Aberto
            ->first();

        if (!$cashRegister) {
            return response()->json([
                'message' => 'Nenhum caixa aberto encontrado para este usuário.'
            ], 404);
        }

        $order = Order::where('cash_register_id', $cashRegister->id)
        ->where(function ($query) {
            $query->where('order_status_id', 1)
                ->orWhere('order_status_id', 2);
        })
        ->first();
        if ($order) {
                return response()->json([
                    'message' => 'Existe uma encomenda que não foi finalizada. Por Favor Finalize e Feche a sua conta.'
                ], 404);
        }



        // Calcular o total de vendas realizadas durante o período do caixa
        $totalSales = OrderItem::where('cash_register_id', $cashRegister->id)
            ->sum('total');

        // Atualizar informações do caixa
        $cashRegister->update([
            'closing_balance' => $data['closing_balance'],
            'automatic_closing_balance' => $totalSales,
            'closed_at' => now(),
            'difference'=> $totalSales - $data['closing_balance'],
            'cash_register_status_id' => 2 // 2 = Fechado
        ]);

        // // Calcular a diferença entre o saldo esperado e o real
        // $expectedBalance = $cashRegister->opening_balance + $totalSales;
        // $difference = $request->closing_balance - $expectedBalance;

        return response()->json([
            'message' => 'Caixa fechado com sucesso!',
            'cash_register' => $cashRegister,
            // 'total_sales' => $totalSales,
            // 'expected_balance' => $expectedBalance,
            // 'difference' => $difference
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
            $cashRegister = CashRegister::find($id);
        
            if (!$cashRegister) {
                return response()->json([
                    'message' => 'Nenhum caixa aberto encontrado para este usuário.'
                ], 404);
            }
        
            // Consultas agrupadas
            $orders = Order::with('itens') // Carrega os itens de cada pedido
                ->where('cash_register_id', $cashRegister->id)
                ->get();
        
            $orderItems = OrderItem::where('cash_register_id', $cashRegister->id)->get();
        
            $orderItemsTable = OrderItem::whereHas('order', function ($query) {
                $query->whereNotNull('table_id');
            })->where('cash_register_id', $cashRegister->id)->get();
        
            // $orderItems = $orders->flatMap->itens; // Coleta todos os itens em uma coleção única
        
            // Cálculos gerais
            $totalSales = $orderItems->sum('total');
            $totalOrders = $orderItems->count();
            $averageTicket = $totalOrders > 0 ? $totalSales / $totalOrders : 0;
        
            // Separação de vendas rápidas e por mesa
            $tableOrders = $orders->whereNotNull('table_id');
            $quickSellOrders = $orders->whereNull('table_id');
        
            $totalOrderTables = $tableOrders->count();
            $totalOrderQuickSell = $quickSellOrders->count();
        
            $totalOrderTablesAmount = $orderItemsTable->sum('total');
            $totalOrderQuickSellAmount = $quickSellOrders->flatMap->itens->sum('total');
        
            $payments = Payment::where('cash_register_id',$cashRegister->id);
            $totalpayments = $payments->count();
            $totalpaymentsamount = $payments->sum('amount');
        
        
            return response()->json([
                'cash_register' => $cashRegister,
                'total_sales' => $totalSales,
                'total_orders' => $totalOrders,
                'total_tables' => $totalOrderTables,
                'total_quick_sell' => $totalOrderQuickSell,
                'average_ticket' => round($averageTicket, 2),
                'total_tables_amount' => $totalOrderTablesAmount,
                'total_quick_sell_amount' => $totalOrderQuickSellAmount,
                'total_payments' => $totalpayments,
                'total_payments_amount' => $totalpaymentsamount,
        
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

    public function dashboard()
    {
        $cashRegister = CashRegister::where('user_id', Auth::user()->id)
            ->where('cash_register_status_id', 1) // 1 = Aberto
            ->first();

        if (!$cashRegister) {
            return response()->json([
                'message' => 'aberto encontrado para este usuário.'
            ], 404);
        }

        // Consultas agrupadas
        $orders = Order::with('itens') // Carrega os itens de cada pedido
            ->where('cash_register_id', $cashRegister->id)
            ->get();

        $orderItems = OrderItem::where('cash_register_id', $cashRegister->id)->get();

        $orderItemsTable = OrderItem::whereHas('order', function ($query) {
            $query->whereNotNull('table_id');
        })->where('cash_register_id', $cashRegister->id)->get();

        // $orderItems = $orders->flatMap->itens; // Coleta todos os itens em uma coleção única

        // Cálculos gerais
        $totalSales = $orderItems->sum('total');
        $totalOrders = $orderItems->count();
        $averageTicket = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        // Separação de vendas rápidas e por mesa
        $tableOrders = $orders->whereNotNull('table_id');
        $quickSellOrders = $orders->whereNull('table_id');

        $totalOrderTables = $tableOrders->count();
        $totalOrderQuickSell = $quickSellOrders->count();

        $totalOrderTablesAmount = $orderItemsTable->sum('total');
        $totalOrderQuickSellAmount = $quickSellOrders->flatMap->itens->sum('total');

        $payments = Payment::where('cash_register_id',$cashRegister->id);
        $totalpayments = $payments->count();
        $totalpaymentsamount = $payments->sum('amount');


        return response()->json([
            'cash_register' => $cashRegister,
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
            'total_tables' => $totalOrderTables,
            'total_quick_sell' => $totalOrderQuickSell,
            'average_ticket' => round($averageTicket, 2),
            'total_tables_amount' => $totalOrderTablesAmount,
            'total_quick_sell_amount' => $totalOrderQuickSellAmount,
            'total_payments' => $totalpayments,
            'total_payments_amount' => $totalpaymentsamount,

        ]);
    }



public function paymentreport(){
    $userQuery = request('user');
    if($userQuery){
        $cashRegister = CashRegister::find($userQuery);
    }else{
        $cashRegister = CashRegister::where('user_id', Auth::user()->id)
        ->where('cash_register_status_id', 1) // 1 = Aberto
        ->first();
    }

    if (!$cashRegister) {
        return response()->json([
            'message' => 'Nenhum caixa aberto encontrado para este usuário.'
        ], 404);
    }
    $payments = Payment::with('method')->with('order.table')->where('cash_register_id',$cashRegister->id)->paginate(100);

    return response()->json($payments);
}

public function tablesellreport(){
    $userQuery = request('user');
    if($userQuery){
        $cashRegister = CashRegister::find($userQuery);
    }else{
        $cashRegister = CashRegister::where('user_id', Auth::user()->id)
        ->where('cash_register_status_id', 1) // 1 = Aberto
        ->first();
    }

    if (!$cashRegister) {
        return response()->json([
            'message' => 'Nenhum caixa aberto encontrado para este usuário.'
        ], 404);
    }
    $orderItemsTable = OrderItem::where('cash_register_id', $cashRegister->id)->get();

    $groupedOrderItems = $orderItemsTable->groupBy('order_id');

    // Obter os IDs dos pedidos únicos
    $orderIds = $groupedOrderItems->keys();

    // Buscar os pedidos relacionados
    $orders = Order::whereIn('id', $orderIds)->whereNotNull('table_id')
    ->with([
        'itens.product',    // Relacionamento com o produto de cada item
        'itens.status',     // Status de cada item
        'itens.user',       // Usuário responsável pelo item
        'table',            // Mesa associada ao pedido
        'status',           // Status do pedido
        'user'              // Usuário que fez o pedido
    ])
    ->paginate(100);

    return response()->json( $orders);
}

public function quicksellreport(){
    $userQuery = request('user');
    if($userQuery){
        $cashRegister = CashRegister::find($userQuery);
    }else{
        $cashRegister = CashRegister::where('user_id', Auth::user()->id)
        ->where('cash_register_status_id', 1) // 1 = Aberto
        ->first();
    }
    

    if (!$cashRegister) {
        return response()->json([
            'message' => 'Nenhum caixa aberto encontrado para este usuário.'
        ], 404);
    }
    $orders = Order::
    with([
        'itens.product',    // Relacionamento com o produto de cada item
        'itens.status',     // Status de cada item
        'itens.user',       // Usuário responsável pelo item
        'table',            // Mesa associada ao pedido
        'status',           // Status do pedido
        'user'              // Usuário que fez o pedido
    ])
        ->where('cash_register_id', $cashRegister->id)->whereNull('table_id')
        ->paginate(100);

    return response()->json($orders);
}

public function dailydashboard()
{
    $dateURL = request('date');

    $date = $dateURL ? date('Y-m-d', strtotime($dateURL)) : date('Y-m-d');

    $cashRegister = CashRegister::with('orderitens')
    ->with('user')
    ->with('status')->whereDate('created_at', $date)->get();

    $cashRegister->transform(function ($cash) {
        $cash->order_itens_total = $cash->orderitens->sum('total');
        return $cash;
    });

    $cashRegisterId = $cashRegister->pluck('id');

    $orders = Order::with('itens')
        ->whereIn('cash_register_id', $cashRegisterId)
        ->get();

    $orderItems = $orders->flatMap->itens;

    $orderItemsTable = $orderItems->filter(function ($item) {
        return $item->order->table_id !== null;
    });

    // Consumo bruto (stock/operação) — inclui crédito e interno
    $totalSales = $orderItems->sum('total');
    $totalOrders = $orderItems->count();

    $tableOrders = $orders->whereNotNull('table_id');
    $quickSellOrders = $orders->whereNull('table_id');

    $totalOrderTables = $tableOrders->count();
    $totalOrderQuickSell = $quickSellOrders->count();

    $totalOrderTablesAmount = $orderItemsTable->sum('total');
    $totalOrderQuickSellAmount = $quickSellOrders->flatMap->itens->sum('total');

    // Pagamentos que entram em receita (exclui crédito e consumo interno)
    $revenuePaymentsQuery = Payment::whereIn('cash_register_id', $cashRegisterId)
        ->whereHas('method', function ($query) {
            $query->where('counts_in_revenue', 1);
        });
    $totalPayments = (clone $revenuePaymentsQuery)->count();
    $totalPaymentsAmount = (clone $revenuePaymentsQuery)->sum('amount');

    // Crédito emitido no dia
    $creditPaymentsQuery = Payment::whereIn('cash_register_id', $cashRegisterId)
        ->whereHas('method', function ($query) {
            $query->where('is_credit', 1);
        });
    $totalCreditIssued = (clone $creditPaymentsQuery)->sum('amount');
    $totalCreditCount = (clone $creditPaymentsQuery)->count();

    // Consumo interno / cortesia
    $internalPaymentsQuery = Payment::whereIn('cash_register_id', $cashRegisterId)
        ->whereHas('method', function ($query) {
            $query->where('counts_in_revenue', 0)->where('is_credit', 0);
        });
    $totalInternalConsumption = (clone $internalPaymentsQuery)->sum('amount');
    $totalInternalCount = (clone $internalPaymentsQuery)->count();

    // Liquidações de crédito no dia (caixas do dia)
    $creditSettlementsQuery = CreditSettlement::whereIn('cash_register_id', $cashRegisterId);
    $totalCreditSettled = (clone $creditSettlementsQuery)->sum('amount_paid');
    $totalCreditSettlementsCount = (clone $creditSettlementsQuery)->count();

    // Receita real = pagamentos que contam + liquidações
    $totalRevenue = $totalPaymentsAmount + $totalCreditSettled;

    // Crédito ainda em aberto (pedidos status Crédito = 4), descontando liquidações
    $openCreditPayments = Payment::whereHas('method', function ($query) {
            $query->where('is_credit', 1);
        })
        ->whereHas('order', function ($query) {
            $query->where('order_status_id', 4);
        })
        ->get();

    $totalCreditOpenBalance = $openCreditPayments->sum(function ($payment) {
        $paid = CreditSettlement::where('payment_id', $payment->id)->sum('amount_paid');
        return max(0, (float) $payment->amount - (float) $paid);
    });
    $totalCreditOpenCount = $openCreditPayments->count();

    $averageTicket = $totalPayments > 0 ? $totalRevenue / $totalPayments : 0;

    return response()->json([
        'cash_register' => $cashRegister,
        'total_sales' => $totalSales,
        'total_orders' => $totalOrders,
        'total_tables' => $totalOrderTables,
        'total_quick_sell' => $totalOrderQuickSell,
        'average_ticket' => round($averageTicket, 2),
        'total_tables_amount' => $totalOrderTablesAmount,
        'total_quick_sell_amount' => $totalOrderQuickSellAmount,
        'total_payments' => $totalPayments,
        'total_payments_amount' => $totalPaymentsAmount,
        'total_revenue' => $totalRevenue,
        'total_credit_issued' => $totalCreditIssued,
        'total_credit_count' => $totalCreditCount,
        'total_credit_settled' => $totalCreditSettled,
        'total_credit_settlements_count' => $totalCreditSettlementsCount,
        'total_credit_open_balance' => $totalCreditOpenBalance,
        'total_credit_open_count' => $totalCreditOpenCount,
        'total_internal_consumption' => $totalInternalConsumption,
        'total_internal_count' => $totalInternalCount,
    ]);
}

public function paymentreportdaily(){
    $dateURL = request('date');

    $date = $dateURL ? date('Y-m-d', strtotime($dateURL)) : date('Y-m-d');

    $cashRegister = CashRegister::whereDate('created_at', $date)->get();
    $cashRegisterId = $cashRegister->pluck('id');


    $payments = Payment::with('method')->with('order.table')->whereIn('cash_register_id',$cashRegisterId)->paginate(100);

    return response()->json($payments);
}

public function tablesellreportdaily(){
    
    $dateURL = request('date');

    $date = $dateURL ? date('Y-m-d', strtotime($dateURL)) : date('Y-m-d');

    $cashRegister = CashRegister::whereDate('created_at', $date)->get();

    $cashRegisterId = $cashRegister->pluck('id');
    $orderItemsTable = OrderItem::whereIn('cash_register_id', $cashRegisterId)->get();

    $groupedOrderItems = $orderItemsTable->groupBy('order_id');

    // Obter os IDs dos pedidos únicos
    $orderIds = $groupedOrderItems->keys();

    // Buscar os pedidos relacionados
    $orders = Order::whereIn('id', $orderIds)->whereNotNull('table_id')
    ->with([
        'itens.product',    // Relacionamento com o produto de cada item
        'itens.status',     // Status de cada item
        'itens.user',       // Usuário responsável pelo item
        'table',            // Mesa associada ao pedido
        'status',           // Status do pedido
        'user'              // Usuário que fez o pedido
    ])
    ->paginate(100);

    return response()->json( $orders);
}

public function quicksellreportdaily(){
    $dateURL = request('date');

    $date = $dateURL ? date('Y-m-d', strtotime($dateURL)) : date('Y-m-d');

    $cashRegister = CashRegister::whereDate('created_at', $date)->get();

    $cashRegisterId = $cashRegister->pluck('id');
    $orders = Order::
    with([
        'itens.product',    // Relacionamento com o produto de cada item
        'itens.status',     // Status de cada item
        'itens.user',       // Usuário responsável pelo item
        'table',            // Mesa associada ao pedido
        'status',           // Status do pedido
        'user'              // Usuário que fez o pedido
    ])
        ->whereIn('cash_register_id', $cashRegisterId)->whereNull('table_id')
        ->paginate(100);

    return response()->json($orders);
}


public function report(){

    $dateURL = request('date');

    $date = $dateURL ? date('Y-m-d', strtotime($dateURL)) : date('Y-m-d');

    $cashRegister = CashRegister::with('orderitens')
    ->with('user')
    ->with('status')->whereDate('created_at', $date)->get();

    $cashRegister->transform(function ($cash) {
        $cash->order_itens_total = $cash->orderitens->sum('total');
        return $cash;
    });

    $cashRegisterId = $cashRegister->pluck('id');

    $orders = Order::with('itens')
        ->whereIn('cash_register_id', $cashRegisterId)
        ->get();

    $orderItems = $orders->flatMap->itens;

    $orderItemsTable = $orderItems->filter(function ($item) {
        return $item->order->table_id !== null;
    });

    $totalSales = $orderItems->sum('total');
    $totalOrders = $orderItems->count();

    $tableOrders = $orders->whereNotNull('table_id');
    $quickSellOrders = $orders->whereNull('table_id');

    $totalOrderTables = $tableOrders->count();
    $totalOrderQuickSell = $quickSellOrders->count();

    $totalOrderTablesAmount = $orderItemsTable->sum('total');
    $totalOrderQuickSellAmount = $quickSellOrders->flatMap->itens->sum('total');

    $revenuePaymentsQuery = Payment::whereIn('cash_register_id', $cashRegisterId)
        ->whereHas('method', fn ($q) => $q->where('counts_in_revenue', 1));
    $totalPayments = (clone $revenuePaymentsQuery)->count();
    $totalPaymentsAmount = (clone $revenuePaymentsQuery)->sum('amount');

    $totalCreditIssued = Payment::whereIn('cash_register_id', $cashRegisterId)
        ->whereHas('method', fn ($q) => $q->where('is_credit', 1))
        ->sum('amount');

    $totalInternalConsumption = Payment::whereIn('cash_register_id', $cashRegisterId)
        ->whereHas('method', fn ($q) => $q->where('counts_in_revenue', 0)->where('is_credit', 0))
        ->sum('amount');

    $totalCreditSettled = CreditSettlement::whereIn('cash_register_id', $cashRegisterId)->sum('amount_paid');
    $totalRevenue = $totalPaymentsAmount + $totalCreditSettled;

    $ticket = $totalPayments > 0 ? round($totalRevenue / $totalPayments, 2) : 0;

    $paymentsReport = Payment::with('method')->with('order.table')
        ->whereIn('cash_register_id', $cashRegisterId)
        ->whereHas('method', fn ($q) => $q->where('counts_in_revenue', 1))
        ->get();

    $creditsReport = Payment::with('method')->with('order.table')->with('customer')
        ->whereIn('cash_register_id', $cashRegisterId)
        ->whereHas('method', fn ($q) => $q->where('is_credit', 1))
        ->get();

    $internalReport = Payment::with('method')->with('order.table')
        ->whereIn('cash_register_id', $cashRegisterId)
        ->whereHas('method', fn ($q) => $q->where('counts_in_revenue', 0)->where('is_credit', 0))
        ->get();

    $orderItemsTableReport = OrderItem::whereIn('cash_register_id', $cashRegisterId)->get();
    $groupedOrderItems = $orderItemsTableReport->groupBy('order_id');
    $orderIds = $groupedOrderItems->keys();
    $ordersReport = Order::whereIn('id', $orderIds)->whereNotNull('table_id')->with(['itens.product','itens.status','itens.user','table','status','user'])->get();
    $quickOrderReport = Order::with(['itens.product','itens.status','itens.user','table','status','user'])->whereIn('cash_register_id', $cashRegisterId)->whereNull('table_id')->get();

    $pdf = Pdf::loadView('pdf.report', compact(
        'cashRegister',
        'totalSales',
        'totalOrders',
        'totalOrderTables',
        'totalOrderQuickSell',
        'ticket',
        'totalOrderTablesAmount',
        'totalOrderQuickSellAmount',
        'totalPayments',
        'totalPaymentsAmount',
        'totalRevenue',
        'totalCreditIssued',
        'totalCreditSettled',
        'totalInternalConsumption',
        'paymentsReport',
        'creditsReport',
        'internalReport',
        'ordersReport',
        'quickOrderReport'
    ))->setOptions([
        'setPaper'=>'a8',
        'defaultFont' => 'sans-serif',
        'isRemoteEnabled' => 'true'
    ]);
    return $pdf->setPaper('a4')->stream('report.pdf');
}

public function reportevento()
{
    $dateURL = request('date');
    $date = $dateURL ? date('Y-m-d', strtotime($dateURL)) : date('Y-m-d');

    $cashRegister = CashRegister::whereDate('created_at', $date)->get();
    $cashRegisterId = $cashRegister->pluck('id');

    $allOrderItems = OrderItem::with(['product', 'order'])
        ->whereIn('cash_register_id', $cashRegisterId)
        ->get();

    $totalSales = $allOrderItems->sum('total');

    $revenuePayments = Payment::whereIn('cash_register_id', $cashRegisterId)
        ->whereHas('method', fn ($q) => $q->where('counts_in_revenue', 1))
        ->get();
    $totalRevenuePayments = $revenuePayments->sum('amount');

    $creditPayments = Payment::with(['customer', 'order.table'])
        ->whereIn('cash_register_id', $cashRegisterId)
        ->whereHas('method', fn ($q) => $q->where('is_credit', 1))
        ->get();
    $totalCreditIssued = $creditPayments->sum('amount');

    $internalOrderIds = Payment::whereIn('cash_register_id', $cashRegisterId)
        ->whereHas('method', fn ($q) => $q->where('counts_in_revenue', 0)->where('is_credit', 0))
        ->pluck('order_id');
    $totalInternalConsumption = Payment::whereIn('cash_register_id', $cashRegisterId)
        ->whereHas('method', fn ($q) => $q->where('counts_in_revenue', 0)->where('is_credit', 0))
        ->sum('amount');

    $totalCreditSettled = CreditSettlement::whereIn('cash_register_id', $cashRegisterId)->sum('amount_paid');
    $totalRevenue = $totalRevenuePayments + $totalCreditSettled;
    $shareBase = max(0, $totalSales - $totalInternalConsumption);

    $creditsReport = $creditPayments->map(function ($payment) {
        $settled = CreditSettlement::where('payment_id', $payment->id)->sum('amount_paid');
        $payment->amount_settled = (float) $settled;
        $payment->amount_balance = max(0, (float) $payment->amount - (float) $settled);
        return $payment;
    });

    $totalCreditOpenBalance = $creditsReport->sum('amount_balance');

    $revenueOrderIds = $revenuePayments->pluck('order_id');

    // Produtos para promotor: apenas vendas pagas (counts_in_revenue), sem crédito nem interno
    $paidItems = $allOrderItems->filter(fn ($item) => $revenueOrderIds->contains($item->order_id));

    $buildProductRows = function ($items, $departmentId) {
        return $items
            ->filter(fn ($item) => (int) ($item->product->department_id ?? $item->department_id) === $departmentId)
            ->groupBy('product_id')
            ->map(function ($group) {
                $first = $group->first();
                return (object) [
                    'product_id' => $first->product_id,
                    'product' => $first->product,
                    'total_quantity' => $group->sum('quantity'),
                    'total_value' => $group->sum('total'),
                ];
            })
            ->filter(fn ($row) => $row->total_quantity > 0)
            ->sortBy(fn ($row) => $row->product->name ?? '')
            ->values();
    };

    $productsBar = $buildProductRows($paidItems, 2);
    $productsKitchen = $buildProductRows($paidItems, 1);

    $pdf = Pdf::loadView('pdf.reportevento', compact(
        'date',
        'totalSales',
        'totalInternalConsumption',
        'shareBase',
        'totalRevenuePayments',
        'totalCreditSettled',
        'totalRevenue',
        'totalCreditIssued',
        'totalCreditOpenBalance',
        'productsBar',
        'productsKitchen',
        'creditsReport'
    ))->setOptions([
        'defaultFont' => 'sans-serif',
        'isRemoteEnabled' => 'true'
    ]);

    return $pdf->setPaper('a4', 'landscape')->stream('reportevento.pdf');
}


public function reportstock(){

    $dateURL = request('date');

    $date = $dateURL ? date('Y-m-d', strtotime($dateURL)) : date('Y-m-d');

    $cashRegister = CashRegister::with('orderitens')
    ->with('user')
    ->with('status')->whereDate('created_at', $date)->get();

    $cashRegister->transform(function ($cash) {
        $cash->order_itens_total = $cash->orderitens->sum('total');
        return $cash;
    });

    $cashRegisterId = $cashRegister->pluck('id');


    
    $orderItemsTableReportKitchen = OrderItem::whereIn('cash_register_id', $cashRegisterId)
    ->whereHas('product', function ($query) {
        $query->where('department_id', 1);
    })
    ->select('product_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(total) as total_value'))
    ->groupBy('product_id')
    ->with(['product' => function ($query) {
        $query->withQuantityInPrincipalStock(); // Adiciona a quantidade do estoque principal
    }])
    ->get();

    // Adicionar quantidade inicial do daily_stock_snapshots para cada produto da cozinha
    foreach ($orderItemsTableReportKitchen as $item) {
        $dailySnapshot = \App\Models\DailyStockSnapshot::where('product_id', $item->product_id)
            ->where('date', $date)
            ->first();
        $item->initial_stock_quantity = $dailySnapshot ? $dailySnapshot->quantity : 0;
        $item->initial_created = $dailySnapshot ? $dailySnapshot->created_at : null;
    }

    $orderItemsTableReportBar = OrderItem::whereIn('cash_register_id', $cashRegisterId)
    ->whereHas('product', function ($query) {
        $query->where('department_id', 2);
    })
    ->select('product_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(total) as total_value'))
    ->groupBy('product_id')
    ->with(['product' => function ($query) {
        $query->withQuantityInPrincipalStock(); // Adiciona a quantidade do estoque principal
    }])
    ->get();

    // Adicionar quantidade inicial do daily_stock_snapshots para cada produto do bar
    foreach ($orderItemsTableReportBar as $item) {
        $dailySnapshot = \App\Models\DailyStockSnapshot::where('product_id', $item->product_id)
            ->where('date', $date)
            ->first();
        $item->initial_stock_quantity = $dailySnapshot ? $dailySnapshot->quantity : 0;
        $item->initial_created = $dailySnapshot ? $dailySnapshot->created_at : null;
    }

    $pdf = Pdf::loadView('pdf.reportstock', compact('orderItemsTableReportKitchen','orderItemsTableReportBar'))->setOptions([
        'setPaper'=>'a4',
        // 'setPaper' => [0, 0, 640, 2376],
        'defaultFont' => 'sans-serif',
        'isRemoteEnabled' => 'true'
    ]);
    return $pdf->setPaper('a4')->stream('report.pdf');
}


public function reporttrash(){

    $dateURL = request('date');

    $date = $dateURL ? date('Y-m-d', strtotime($dateURL)) : date('Y-m-d');

    $cashRegister = CashRegister::with('orderitens')
    ->with('user')
    ->with('status')->whereDate('created_at', $date)->get();

    $cashRegister->transform(function ($cash) {
        $cash->order_itens_total = $cash->orderitens->sum('total');
        return $cash;
    });

    $cashRegisterId = $cashRegister->pluck('id');

    $orderItems = OrderItem::onlyTrashed()->whereIn('cash_register_id', $cashRegisterId)->get();

    
    $totalSales = $orderItems->sum('total');
    


    


    $pdf = Pdf::loadView('pdf.reporttrash', compact('cashRegister','totalSales','orderItems'))->setOptions([
        'setPaper'=>'a8',
        // 'setPaper' => [0, 0, 640, 2376],
        'defaultFont' => 'sans-serif',
        'isRemoteEnabled' => 'true'
    ]);
    return $pdf->setPaper('a4')->stream('reporttrash.pdf');
}

private function excelDownload(string $filename, array $sheets): \Symfony\Component\HttpFoundation\StreamedResponse
{
    return response()->streamDownload(function () use ($sheets) {
        echo "\xEF\xBB\xBF"; // UTF-8 BOM for Excel
        echo '<html><head><meta charset="UTF-8"></head><body>';
        foreach ($sheets as $sheet) {
            echo '<h3>' . e($sheet['title']) . '</h3>';
            echo '<table border="1" cellspacing="0" cellpadding="4">';
            if (!empty($sheet['headers'])) {
                echo '<tr>';
                foreach ($sheet['headers'] as $header) {
                    echo '<th>' . e($header) . '</th>';
                }
                echo '</tr>';
            }
            foreach ($sheet['rows'] as $row) {
                echo '<tr>';
                foreach ($row as $cell) {
                    echo '<td>' . e((string) $cell) . '</td>';
                }
                echo '</tr>';
            }
            echo '</table><br/>';
        }
        echo '</body></html>';
    }, $filename, [
        'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
    ]);
}

private function cashRegisterIdsForDate(?string $dateURL): array
{
    $date = $dateURL ? date('Y-m-d', strtotime($dateURL)) : date('Y-m-d');
    return CashRegister::whereDate('created_at', $date)->pluck('id')->all();
}

public function reportexcel()
{
    $date = request('date') ? date('Y-m-d', strtotime(request('date'))) : date('Y-m-d');
    $cashRegisterId = $this->cashRegisterIdsForDate(request('date'));

    $cashRegister = CashRegister::with(['user', 'status', 'orderitens'])
        ->whereDate('created_at', $date)
        ->get();

    $cashRegister->transform(function ($cash) {
        $cash->order_itens_total = $cash->orderitens->sum('total');
        return $cash;
    });

    $orders = Order::with(['itens', 'table', 'status', 'user'])
        ->whereIn('cash_register_id', $cashRegisterId)
        ->get();

    $orderItems = $orders->flatMap->itens;
    $orderItemsTable = $orderItems->filter(fn ($item) => $item->order->table_id !== null);

    $totalSales = $orderItems->sum('total');
    $tableOrders = $orders->whereNotNull('table_id');
    $quickSellOrders = $orders->whereNull('table_id');
    $totalOrderTables = $tableOrders->count();
    $totalOrderQuickSell = $quickSellOrders->count();
    $totalOrderTablesAmount = $orderItemsTable->sum('total');
    $totalOrderQuickSellAmount = $quickSellOrders->flatMap->itens->sum('total');

    $paymentsRevenue = Payment::with(['method', 'order.table'])
        ->whereIn('cash_register_id', $cashRegisterId)
        ->whereHas('method', fn ($q) => $q->where('counts_in_revenue', 1))
        ->get();

    $credits = Payment::with(['customer', 'order.table'])
        ->whereIn('cash_register_id', $cashRegisterId)
        ->whereHas('method', fn ($q) => $q->where('is_credit', 1))
        ->get();

    $internal = Payment::with(['method', 'order.table'])
        ->whereIn('cash_register_id', $cashRegisterId)
        ->whereHas('method', fn ($q) => $q->where('counts_in_revenue', 0)->where('is_credit', 0))
        ->get();

    $totalPayments = $paymentsRevenue->count();
    $totalPaymentsAmount = $paymentsRevenue->sum('amount');
    $totalCreditSettled = CreditSettlement::whereIn('cash_register_id', $cashRegisterId)->sum('amount_paid');
    $totalRevenue = $totalPaymentsAmount + $totalCreditSettled;
    $totalCreditIssued = $credits->sum('amount');
    $totalInternalConsumption = $internal->sum('amount');
    $ticket = $totalPayments > 0 ? round($totalRevenue / $totalPayments, 2) : 0;

    $ordersReport = $orders->whereNotNull('table_id');
    $quickOrderReport = $orders->whereNull('table_id');

    return $this->excelDownload("Relatorio-Casa-{$date}.xls", [
        [
            'title' => 'Relatório de Vendas — Casa',
            'headers' => ['Descrição', 'Valor'],
            'rows' => [
                ['Consumo total (operação)', number_format($totalSales, 2, ',', '.') . ' MT'],
                ['Receita real (pagamentos + liquidações)', number_format($totalRevenue, 2, ',', '.') . ' MT'],
                ['Pagamentos (counts_in_revenue)', $totalPayments . ' | ' . number_format($totalPaymentsAmount, 2, ',', '.') . ' MT'],
                ['Crédito emitido', number_format($totalCreditIssued, 2, ',', '.') . ' MT'],
                ['Crédito liquidado', number_format($totalCreditSettled, 2, ',', '.') . ' MT'],
                ['Consumo interno', number_format($totalInternalConsumption, 2, ',', '.') . ' MT'],
                ['Total de Pedidos Em Mesa', $totalOrderTables . ' | ' . number_format($totalOrderTablesAmount, 2, ',', '.') . ' MT'],
                ['Total de Pedidos Venda Rápida', $totalOrderQuickSell . ' | ' . number_format($totalOrderQuickSellAmount, 2, ',', '.') . ' MT'],
                ['Ticket médio (receita)', $ticket . ' MT'],
            ],
        ],
        [
            'title' => 'Caixa',
            'headers' => ['ID', 'Usuário', 'Valor', 'Valor Final', 'Diferença', 'Estado', 'Abertura', 'Fechamento'],
            'rows' => $cashRegister->map(fn ($item) => [
                $item->id,
                $item->user->name ?? '',
                $item->order_itens_total . ' MT',
                $item->closing_balance . ' MT',
                ($item->difference ?? 0) . ' MT',
                $item->status->name ?? '',
                (string) $item->opened_at,
                $item->closed_at ? (string) $item->closed_at : '-',
            ])->all(),
        ],
        [
            'title' => 'Pagamentos (Receita)',
            'headers' => ['ID', 'Venda', 'Pedido', 'Metodo de Pagamento', 'Valor', 'Data'],
            'rows' => $paymentsRevenue->map(fn ($p) => [
                $p->id,
                $p->order_id,
                $p->order->table->name ?? 'Pedido Rápido',
                $p->method->name ?? '',
                $p->amount . ' MT',
                (string) $p->created_at,
            ])->all(),
        ],
        [
            'title' => 'Créditos Emitidos',
            'headers' => ['ID', 'Cliente', 'Pedido', 'Valor', 'Data'],
            'rows' => $credits->map(fn ($p) => [
                $p->id,
                $p->customer->name ?? '—',
                $p->order->table->name ?? ('Pedido #' . $p->order_id),
                $p->amount . ' MT',
                (string) $p->created_at,
            ])->all(),
        ],
        [
            'title' => 'Consumo Interno',
            'headers' => ['ID', 'Pedido', 'Método', 'Valor', 'Data'],
            'rows' => $internal->map(fn ($p) => [
                $p->id,
                $p->order->table->name ?? ('Pedido #' . $p->order_id),
                $p->method->name ?? '—',
                $p->amount . ' MT',
                (string) $p->created_at,
            ])->all(),
        ],
        [
            'title' => 'Vendas Em Mesa',
            'headers' => ['ID', 'Pedido', 'Garçom', 'Estado', 'Itens', 'Valor', 'Data'],
            'rows' => $ordersReport->map(fn ($item) => [
                $item->id,
                $item->table->name ?? '',
                $item->user->name ?? '',
                $item->status->name ?? '',
                count($item->itens),
                $item->total,
                (string) $item->created_at,
            ])->all(),
        ],
        [
            'title' => 'Vendas Rápidas',
            'headers' => ['ID', 'Pedido', 'Garçom', 'Estado', 'Itens', 'Valor', 'Data'],
            'rows' => $quickOrderReport->map(fn ($item) => [
                $item->id,
                'Pedido Rápido',
                $item->user->name ?? '',
                $item->status->name ?? '',
                count($item->itens),
                $item->total,
                (string) $item->created_at,
            ])->all(),
        ],
    ]);
}

public function reporteventoexcel()
{
    $date = request('date') ? date('Y-m-d', strtotime(request('date'))) : date('Y-m-d');
    $cashRegisterId = $this->cashRegisterIdsForDate(request('date'));

    $allOrderItems = OrderItem::with(['product', 'order'])->whereIn('cash_register_id', $cashRegisterId)->get();
    $totalSales = $allOrderItems->sum('total');

    $revenuePayments = Payment::whereIn('cash_register_id', $cashRegisterId)
        ->whereHas('method', fn ($q) => $q->where('counts_in_revenue', 1))
        ->get();
    $totalRevenuePayments = $revenuePayments->sum('amount');

    $creditPayments = Payment::with(['customer', 'order.table'])
        ->whereIn('cash_register_id', $cashRegisterId)
        ->whereHas('method', fn ($q) => $q->where('is_credit', 1))
        ->get();
    $totalCreditIssued = $creditPayments->sum('amount');

    $totalInternalConsumption = Payment::whereIn('cash_register_id', $cashRegisterId)
        ->whereHas('method', fn ($q) => $q->where('counts_in_revenue', 0)->where('is_credit', 0))
        ->sum('amount');

    $totalCreditSettled = CreditSettlement::whereIn('cash_register_id', $cashRegisterId)->sum('amount_paid');
    $totalRevenue = $totalRevenuePayments + $totalCreditSettled;
    $shareBase = max(0, $totalSales - $totalInternalConsumption);

    $creditsReport = $creditPayments->map(function ($payment) {
        $settled = CreditSettlement::where('payment_id', $payment->id)->sum('amount_paid');
        return [
            $payment->id,
            $payment->customer->name ?? '—',
            $payment->order->table->name ?? ('Pedido #' . $payment->order_id),
            number_format($payment->amount, 2, ',', '.') . ' MT',
            number_format($settled, 2, ',', '.') . ' MT',
            number_format(max(0, (float) $payment->amount - (float) $settled), 2, ',', '.') . ' MT',
            (string) $payment->created_at,
        ];
    });

    $totalCreditOpenBalance = $creditPayments->sum(function ($payment) {
        $settled = CreditSettlement::where('payment_id', $payment->id)->sum('amount_paid');
        return max(0, (float) $payment->amount - (float) $settled);
    });

    $revenueOrderIds = $revenuePayments->pluck('order_id');
    $paidItems = $allOrderItems->filter(fn ($item) => $revenueOrderIds->contains($item->order_id));

    $buildRows = function ($items, $departmentId) {
        return $items
            ->filter(fn ($item) => (int) ($item->product->department_id ?? $item->department_id) === $departmentId)
            ->groupBy('product_id')
            ->map(function ($group) {
                $first = $group->first();
                return [
                    $first->product->name ?? 'Desconhecido',
                    $group->sum('quantity'),
                    number_format($first->product->price ?? 0, 2, ',', '.') . ' MT',
                    number_format($group->sum('total'), 2, ',', '.') . ' MT',
                ];
            })
            ->sortBy(0)
            ->values()
            ->all();
    };

    return $this->excelDownload("Relatorio-Evento-{$date}.xls", [
        [
            'title' => 'Relatório Evento / Promotor',
            'headers' => ['Descrição', 'Valor'],
            'rows' => [
                ['Consumo total (operação)', number_format($totalSales, 2, ',', '.') . ' MT'],
                ['(-) Consumo interno', number_format($totalInternalConsumption, 2, ',', '.') . ' MT'],
                ['Base de partilha (consumo − interno)', number_format($shareBase, 2, ',', '.') . ' MT'],
                ['Receita já cobrada (pagamentos)', number_format($totalRevenuePayments, 2, ',', '.') . ' MT'],
                ['Crédito liquidado no dia', number_format($totalCreditSettled, 2, ',', '.') . ' MT'],
                ['Receita real (cash)', number_format($totalRevenue, 2, ',', '.') . ' MT'],
                ['Crédito emitido no dia', number_format($totalCreditIssued, 2, ',', '.') . ' MT'],
                ['Crédito em aberto (saldo)', number_format($totalCreditOpenBalance, 2, ',', '.') . ' MT'],
            ],
        ],
        [
            'title' => 'Vendas por Produto — Bar (apenas pagos)',
            'headers' => ['Produto', 'Qtd. Vend.', 'P. Venda', 'V. Total'],
            'rows' => $buildRows($paidItems, 2),
        ],
        [
            'title' => 'Vendas por Produto — Cozinha (apenas pagos)',
            'headers' => ['Produto', 'Qtd. Vend.', 'P. Venda', 'V. Total'],
            'rows' => $buildRows($paidItems, 1),
        ],
        [
            'title' => 'Créditos do Dia',
            'headers' => ['ID', 'Cliente', 'Pedido / Mesa', 'Total', 'Liquidado', 'Saldo', 'Data'],
            'rows' => $creditsReport->all(),
        ],
    ]);
}

public function reportstockexcel()
{
    $date = request('date') ? date('Y-m-d', strtotime(request('date'))) : date('Y-m-d');
    $cashRegisterId = $this->cashRegisterIdsForDate(request('date'));

    $build = function ($departmentId) use ($cashRegisterId, $date) {
        $items = OrderItem::whereIn('cash_register_id', $cashRegisterId)
            ->whereHas('product', fn ($q) => $q->where('department_id', $departmentId))
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(total) as total_value'))
            ->groupBy('product_id')
            ->with(['product' => fn ($q) => $q->withQuantityInPrincipalStock()])
            ->get();

        return $items->map(function ($item) use ($date) {
            $snapshot = DailyStockSnapshot::where('product_id', $item->product_id)->where('date', $date)->first();
            $buy = $item->product->buy_price ?? 0;
            $qty = $item->total_quantity ?? 0;
            $total = $item->total_value ?? 0;
            return [
                $item->product->name ?? 'Desconhecido',
                $snapshot->quantity ?? 0,
                $qty,
                number_format($item->product->price ?? 0, 2, ',', '.') . ' MT',
                number_format($total, 2, ',', '.') . ' MT',
                $item->product->quantity_in_principal_stock ?? 0,
                number_format($buy, 2, ',', '.') . ' MT',
                number_format($buy * $qty, 2, ',', '.') . ' MT',
                number_format($total - ($buy * $qty), 2, ',', '.') . ' MT',
            ];
        })->all();
    };

    return $this->excelDownload("Relatorio-Stock-{$date}.xls", [
        [
            'title' => 'Relatório de Stocks',
            'headers' => ['Nota'],
            'rows' => [
                ['Data do relatório: ' . $date],
            ],
        ],
        [
            'title' => 'Produtos Stock Bar',
            'headers' => ['Produto', 'Qtd. Inicial', 'Qtd. Vend.', 'P. Venda', 'V. Total', 'Stock Atual', 'P. Compra', 'T. Compra', 'Lucro'],
            'rows' => $build(2),
        ],
        [
            'title' => 'Produtos Stock Cozinha',
            'headers' => ['Produto', 'Qtd. Inicial', 'Qtd. Vend.', 'P. Venda', 'V. Total', 'Stock Atual', 'P. Compra', 'T. Compra', 'Lucro'],
            'rows' => $build(1),
        ],
    ]);
}

public function reporttrashexcel()
{
    $date = request('date') ? date('Y-m-d', strtotime(request('date'))) : date('Y-m-d');
    $cashRegisterId = $this->cashRegisterIdsForDate(request('date'));

    $orderItems = OrderItem::onlyTrashed()
        ->with(['product', 'order', 'user', 'deliveredby', 'updatedby'])
        ->whereIn('cash_register_id', $cashRegisterId)
        ->get();

    $totalSales = $orderItems->sum('total');

    return $this->excelDownload("Relatorio-Lixeira-{$date}.xls", [
        [
            'title' => 'Relatório de Vendas - Bar',
            'headers' => ['Descrição', 'Valor'],
            'rows' => [
                ['Valor de Venda', $totalSales . ' MT'],
            ],
        ],
        [
            'title' => 'Vendas',
            'headers' => ['ID', 'Produto', 'Quantidade', 'Total', 'Pedido', 'Garçom', 'Entregue por', 'Usuario', 'Data'],
            'rows' => $orderItems->map(fn ($item) => [
                $item->id,
                $item->product->name ?? '',
                $item->quantity,
                $item->total . ' MT',
                $item->order->id ?? '',
                $item->user->name ?? '',
                $item->deliveredby->name ?? 'N/A',
                $item->updatedby->name ?? 'N/A',
                (string) $item->updated_at,
            ])->all(),
        ],
    ]);
}

}
