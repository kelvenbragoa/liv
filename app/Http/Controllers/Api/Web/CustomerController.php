<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Models\CreditSettlement;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    private function buildFilteredQuery(Request $request)
    {
        $sortBy = $request->input('sort_by', 'name');
        $allowedSort = ['id', 'name', 'email', 'mobile', 'tax_number', 'address', 'created_at'];

        if (! in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'name';
        }

        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        return Customer::query()
            ->when($request->filled('query'), function ($query) use ($request) {
                $search = $request->input('query');
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhere('tax_number', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
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

        $customers = $this->buildFilteredQuery($request)
            ->paginate($perPage)
            ->withQueryString();

        return response()->json($customers);
    }

    /**
     * Export filtered customers for Excel download.
     */
    public function export(Request $request)
    {
        $customers = $this->buildFilteredQuery($request)->get();

        $data = $customers->map(function ($customer) {
            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'mobile' => $customer->mobile,
                'address' => $customer->address,
                'tax_number' => $customer->tax_number,
                'created_at' => $customer->created_at?->format('d/m/Y H:i'),
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
        return response()->noContent();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $customer = Customer::create([
            'email' => $data['email'],
            'name' => $data['name'],
            'mobile' => $data['mobile'],
            'address' => $data['address'],
            'tax_number' => $data['tax_number'],
        ]);

        return response()->json($customer);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $customer = Customer::query()->whereKey($id)->first();

        if (! $customer) {
            return response()->json(['message' => 'Cliente não encontrado.'], 404);
        }

        $paymentsCount = Payment::query()->where('customer_id', $customer->id)->count();
        $paymentsTotal = (float) (Payment::query()->where('customer_id', $customer->id)->sum('amount') ?? 0);
        $reservationsCount = Reservation::query()->where('customer_id', $customer->id)->count();
        $creditSettlementsCount = CreditSettlement::query()->where('customer_id', $customer->id)->count();
        $creditRemaining = (float) (CreditSettlement::query()->where('customer_id', $customer->id)->sum('amount_remaining') ?? 0);
        $latestReservation = Reservation::query()
            ->where('customer_id', $customer->id)
            ->max('reservation_time');

        return response()->json([
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'mobile' => $customer->mobile,
                'address' => $customer->address,
                'tax_number' => $customer->tax_number,
                'created_at' => $customer->created_at,
            ],
            'metrics' => [
                'payments_count' => $paymentsCount,
                'payments_total' => $paymentsTotal,
                'reservations_count' => $reservationsCount,
                'credit_settlements_count' => $creditSettlementsCount,
                'credit_remaining' => $creditRemaining,
                'latest_reservation' => $latestReservation,
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $customer = Customer::find($id);

        return response()->json([
            'customer' => $customer,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->all();
        $customer = Customer::find($id);
        $customer->update($data);

        return response()->json($customer);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $customer = Customer::find($id);

        if (! $customer) {
            return response()->json(['message' => 'Cliente não encontrado.'], 404);
        }

        $hasPayment = Payment::where('customer_id', $customer->id)->exists();
        $hasReservation = Reservation::where('customer_id', $customer->id)->exists();
        $hasCreditSettlement = CreditSettlement::where('customer_id', $customer->id)->exists();

        if ($hasPayment) {
            return response()->json(['message' => 'Não é possível eliminar o cliente, existem pagamentos associados.'], 404);
        }

        if ($hasReservation) {
            return response()->json(['message' => 'Não é possível eliminar o cliente, existem reservas associadas.'], 404);
        }

        if ($hasCreditSettlement) {
            return response()->json(['message' => 'Não é possível eliminar o cliente, existem liquidações de crédito associadas.'], 404);
        }

        $customer->delete();

        return response()->json(['message' => 'Cliente removido com sucesso.']);
    }
}
