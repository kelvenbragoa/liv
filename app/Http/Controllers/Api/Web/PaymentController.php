<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    private function buildFilteredQuery(Request $request)
    {
        $sortBy = $request->input('sort_by', 'created_at');
        $allowedSort = ['id', 'amount', 'order_id', 'payment_method_id', 'customer_id', 'created_at'];

        if (! in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'created_at';
        }

        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        return Payment::query()
            ->when($request->filled('query'), function ($query) use ($request) {
                $search = $request->input('query');
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where('id', 'like', "%{$search}%")
                        ->orWhere('order_id', 'like', "%{$search}%")
                        ->orWhere('amount', 'like', "%{$search}%")
                        ->orWhereHas('method', function ($methodQuery) use ($search) {
                            $methodQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('payment_method_id'), function ($query) use ($request) {
                $query->where('payment_method_id', $request->integer('payment_method_id'));
            })
            ->when($request->filled('order_id'), function ($query) use ($request) {
                $query->where('order_id', $request->integer('order_id'));
            })
            ->when($request->filled('created_from'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->input('created_from'));
            })
            ->when($request->filled('created_to'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->input('created_to'));
            })
            ->with(['method', 'customer'])
            ->orderBy($sortBy, $sortDir);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = min(max((int) $request->input('per_page', 15), 5), 100);

        $payments = $this->buildFilteredQuery($request)
            ->paginate($perPage)
            ->withQueryString();

        return response()->json($payments);
    }

    /**
     * Export filtered payments for Excel download.
     */
    public function export(Request $request)
    {
        $payments = $this->buildFilteredQuery($request)->get();

        $data = $payments->map(function ($payment) {
            return [
                'id' => $payment->id,
                'amount' => $payment->amount,
                'payment_method' => $payment->method?->name,
                'order_id' => $payment->order_id,
                'customer' => $payment->customer?->name,
                'created_at' => $payment->created_at?->format('d/m/Y H:i'),
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
            'payment_methods' => PaymentMethod::orderBy('name')->get(),
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
        $payment = Payment::query()->with(['method', 'customer', 'order'])->find($id);

        if (! $payment) {
            return response()->json(['message' => 'Pagamento não encontrado.'], 404);
        }

        $orderPayments = Payment::query()
            ->where('order_id', $payment->order_id)
            ->with('method')
            ->get(['id', 'order_id', 'payment_method_id', 'amount', 'created_at']);

        $methodUsageCount = Payment::query()->where('payment_method_id', $payment->payment_method_id)->count();
        $methodUsageTotal = (float) (Payment::query()->where('payment_method_id', $payment->payment_method_id)->sum('amount') ?? 0);
        $orderPaymentsTotal = (float) $orderPayments->sum('amount');
        $orderTotal = (float) ($payment->order?->total ?? 0);

        return response()->json([
            'payment' => [
                'id' => $payment->id,
                'amount' => $payment->amount,
                'created_at' => $payment->created_at,
            ],
            'method' => $payment->method ? [
                'id' => $payment->method->id,
                'name' => $payment->method->name,
            ] : null,
            'customer' => $payment->customer ? [
                'id' => $payment->customer->id,
                'name' => $payment->customer->name,
            ] : null,
            'order' => $payment->order ? [
                'id' => $payment->order->id,
                'total' => $payment->order->total,
                'created_at' => $payment->order->created_at,
            ] : null,
            'metrics' => [
                'method_usage_count' => $methodUsageCount,
                'method_usage_total' => $methodUsageTotal,
                'order_payments_count' => $orderPayments->count(),
                'order_payments_total' => $orderPaymentsTotal,
                'order_total' => $orderTotal,
                'order_balance' => $orderTotal - $orderPaymentsTotal,
            ],
            'order_payments' => $orderPayments,
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
        return response()->json(['message' => 'Operação não permitida.'], 403);
    }
}
