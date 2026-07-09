<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Status;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    private function buildFilteredQuery(Request $request)
    {
        $sortBy = $request->input('sort_by', 'name');
        $allowedSort = ['id', 'name', 'status_id', 'counts_in_revenue', 'is_credit', 'created_at'];

        if (! in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'name';
        }

        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        return PaymentMethod::query()
            ->when($request->filled('query'), function ($query) use ($request) {
                $search = $request->input('query');
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->filled('status_id'), function ($query) use ($request) {
                $query->where('status_id', $request->integer('status_id'));
            })
            ->when($request->has('counts_in_revenue') && $request->input('counts_in_revenue') !== null && $request->input('counts_in_revenue') !== '', function ($query) use ($request) {
                $query->where('counts_in_revenue', (int) $request->input('counts_in_revenue'));
            })
            ->when($request->has('is_credit') && $request->input('is_credit') !== null && $request->input('is_credit') !== '', function ($query) use ($request) {
                $query->where('is_credit', (int) $request->input('is_credit'));
            })
            ->when($request->filled('created_from'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->input('created_from'));
            })
            ->when($request->filled('created_to'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->input('created_to'));
            })
            ->with('status')
            ->orderBy($sortBy, $sortDir);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = min(max((int) $request->input('per_page', 15), 5), 100);

        $paymentmethods = $this->buildFilteredQuery($request)
            ->paginate($perPage)
            ->withQueryString();

        return response()->json($paymentmethods);
    }

    /**
     * Export filtered payment methods for Excel download.
     */
    public function export(Request $request)
    {
        $paymentmethods = $this->buildFilteredQuery($request)->get();

        $data = $paymentmethods->map(function ($paymentmethod) {
            return [
                'id' => $paymentmethod->id,
                'name' => $paymentmethod->name,
                'status' => $paymentmethod->status?->name,
                'counts_in_revenue' => $paymentmethod->counts_in_revenue ? 'Sim' : 'Não',
                'is_credit' => $paymentmethod->is_credit ? 'Sim' : 'Não',
                'created_at' => $paymentmethod->created_at?->format('d/m/Y H:i'),
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
            'statuses' => Status::orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $paymentmethod = PaymentMethod::create([
            'name' => $data['name'],
            'status_id' => 1,
        ]);

        return response()->json($paymentmethod);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $paymentmethod = PaymentMethod::with('status')->find($id);

        return response()->json($paymentmethod);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $paymentmethod = PaymentMethod::with('status')->find($id);

        return response()->json([
            'paymentmethod' => $paymentmethod,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->all();
        $paymentmethod = PaymentMethod::find($id);
        $paymentmethod->update($data);

        return response()->json($paymentmethod);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $paymentmethod = PaymentMethod::find($id);

        if (! $paymentmethod) {
            return response()->json(['message' => 'Método de pagamento não encontrado.'], 404);
        }

        $hasPayments = Payment::where('payment_method_id', $paymentmethod->id)->exists();

        if ($hasPayments) {
            return response()->json(['message' => 'Não é possível eliminar o método, existem pagamentos associados.'], 404);
        }

        $paymentmethod->delete();

        return response()->json(['message' => 'Método de pagamento removido com sucesso.']);
    }
}
