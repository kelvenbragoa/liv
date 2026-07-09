<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Table;
use App\Models\TableLogChange;
use App\Models\TableStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TableController extends Controller
{
    private function buildFilteredQuery(Request $request)
    {
        $sortBy = $request->input('sort_by', 'name');
        $allowedSort = ['id', 'name', 'capacity', 'monthly_limit', 'table_status_id', 'created_at'];

        if (! in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'name';
        }

        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        return Table::query()
            ->when($request->filled('query'), function ($query) use ($request) {
                $search = $request->input('query');
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->filled('table_status_id'), function ($query) use ($request) {
                $query->where('table_status_id', $request->integer('table_status_id'));
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

        $tables = $this->buildFilteredQuery($request)
            ->paginate($perPage)
            ->withQueryString();

        return response()->json($tables);
    }

    /**
     * Export filtered tables for Excel download.
     */
    public function export(Request $request)
    {
        $tables = $this->buildFilteredQuery($request)->get();

        $data = $tables->map(function ($table) {
            return [
                'id' => $table->id,
                'name' => $table->name,
                'capacity' => $table->capacity,
                'monthly_limit' => $table->monthly_limit == 0 ? 'Sem limite' : $table->monthly_limit,
                'status' => $table->status?->name,
                'created_at' => $table->created_at?->format('d/m/Y H:i'),
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
            'statuses' => TableStatus::orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $table = Table::create([
            'name' => $data['name'],
            'capacity' => $data['capacity'],
            'monthly_limit' => $data['monthly_limit'] ?? 0,
            'table_status_id' => 1,
        ]);

        return response()->json($table);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $table = Table::query()->with('status')->find($id);

        if (! $table) {
            return response()->json(['message' => 'Mesa não encontrada.'], 404);
        }

        $monthly = Order::selectRaw("
        MONTH(created_at) as month_number,
        MONTHNAME(created_at) as month_name,
        SUM(total) as total
    ")
            ->where('table_id', $table->id)
            ->whereYear('created_at', now()->year)
            ->groupByRaw('MONTH(created_at), MONTHNAME(created_at)')
            ->orderByRaw('MONTH(created_at)')
            ->get();

        $orders = Order::query()
            ->where('table_id', $table->id)
            ->with(['status', 'user'])
            ->orderByDesc('created_at')
            ->get(['id', 'table_id', 'user_id', 'order_status_id', 'total', 'created_at']);

        $openOrdersCount = $orders->whereIn('order_status_id', [1, 2])->count();
        $ordersCount = $orders->count();
        $ordersTotal = (float) $orders->sum('total');
        $averageTicket = $ordersCount > 0 ? $ordersTotal / $ordersCount : 0;
        $lastOrder = $orders->first();

        $monthlyTotal = (float) (Order::query()
            ->where('table_id', $table->id)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('total') ?? 0);

        return response()->json([
            'table' => [
                'id' => $table->id,
                'name' => $table->name,
                'capacity' => $table->capacity,
                'monthly_limit' => $table->monthly_limit,
                'created_at' => $table->created_at,
            ],
            'status' => $table->status ? [
                'id' => $table->status->id,
                'name' => $table->status->name,
            ] : null,
            'metrics' => [
                'orders_count' => $ordersCount,
                'open_orders_count' => $openOrdersCount,
                'orders_total' => $ordersTotal,
                'average_ticket' => $averageTicket,
                'monthly_total' => $monthlyTotal,
                'monthly_limit' => (float) ($table->monthly_limit ?? 0),
                'monthly_balance' => (float) ($table->monthly_limit ?? 0) - $monthlyTotal,
                'last_order_at' => $lastOrder?->created_at,
            ],
            'orders' => $orders,
            'logs' => TableLogChange::where('table_id', $id)->with('user')->orderBy('created_at', 'desc')->paginate(),
            'chart' => $monthly,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $table = Table::with('status')->find($id);

        return response()->json([
            'table' => $table,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->all();
        $table = Table::find($id);

        if (array_key_exists('monthly_limit', $data) && $data['monthly_limit'] != $table->monthly_limit) {
            TableLogChange::create([
                'table_id' => $table->id,
                'old_limit' => $table->monthly_limit,
                'new_limit' => $data['monthly_limit'],
                'user_id' => Auth::id(),
            ]);
        }
        $table->update($data);

        return response()->json($table);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $table = Table::find($id);

        if (! $table) {
            return response()->json(['message' => 'Mesa não encontrada.'], 404);
        }

        $existOrder = Order::where('table_id', $table->id)->first();

        if ($existOrder) {
            return response()->json(['message' => 'Não é possível eliminar a mesa, existem pedidos associados.'], 404);
        }

        $table->delete();

        return response()->json(['message' => 'Mesa removida com sucesso.']);
    }
}
