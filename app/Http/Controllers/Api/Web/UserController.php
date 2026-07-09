<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Models\CashRegister;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    private function buildFilteredQuery(Request $request)
    {
        $sortBy = $request->input('sort_by', 'name');
        $allowedSort = ['id', 'name', 'email', 'role_id', 'created_at'];

        if (! in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'name';
        }

        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        return User::query()
            ->when($request->filled('query'), function ($query) use ($request) {
                $search = $request->input('query');
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('role_id'), function ($query) use ($request) {
                $query->where('role_id', $request->integer('role_id'));
            })
            ->when($request->filled('created_from'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->input('created_from'));
            })
            ->when($request->filled('created_to'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->input('created_to'));
            })
            ->with('role')
            ->orderBy($sortBy, $sortDir);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = min(max((int) $request->input('per_page', 15), 5), 100);

        $users = $this->buildFilteredQuery($request)
            ->paginate($perPage)
            ->withQueryString();

        return response()->json($users);
    }

    /**
     * Export filtered users for Excel download.
     */
    public function export(Request $request)
    {
        $users = $this->buildFilteredQuery($request)->get();

        $data = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role?->name,
                'created_at' => $user->created_at?->format('d/m/Y H:i'),
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
        $roles = Role::orderBy('name')->get();

        return response()->json([
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'role_id' => 'required',
            'password' => 'required|min:8',
        ]);
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => $data['role_id'],
        ]);

        return response()->json($user);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::with('role')->find($id);

        if (! $user) {
            return response()->json(['message' => 'Utilizador não encontrado.'], 404);
        }

        $ordersCount = Order::where('user_id', $user->id)->count();
        $orderItemsCount = OrderItem::where('user_id', $user->id)->count();
        $orderItemsTotal = (float) OrderItem::where('user_id', $user->id)->sum('total');
        $cashRegistersCount = CashRegister::where('user_id', $user->id)->count();
        $openCashRegistersCount = CashRegister::where('user_id', $user->id)
            ->where('cash_register_status_id', 1)
            ->count();

        $recentOrders = Order::where('user_id', $user->id)
            ->with(['status', 'table'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'total' => (float) ($order->total ?? 0),
                    'table_name' => $order->table?->name,
                    'status_name' => $order->status?->name,
                    'is_quick_sell' => $order->table_id === null,
                    'created_at' => $order->created_at,
                ];
            });

        $recentCashRegisters = CashRegister::where('user_id', $user->id)
            ->with('status')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($register) {
                return [
                    'id' => $register->id,
                    'status_name' => $register->status?->name,
                    'opening_balance' => (float) ($register->opening_balance ?? 0),
                    'closing_balance' => (float) ($register->closing_balance ?? 0),
                    'opened_at' => $register->opened_at ?? $register->created_at,
                    'closed_at' => $register->closed_at,
                ];
            });

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
                'email_verified_at' => $user->email_verified_at,
            ],
            'role' => $user->role ? [
                'id' => $user->role->id,
                'name' => $user->role->name,
            ] : null,
            'metrics' => [
                'orders_count' => $ordersCount,
                'order_items_count' => $orderItemsCount,
                'order_items_total' => $orderItemsTotal,
                'cash_registers_count' => $cashRegistersCount,
                'open_cash_registers_count' => $openCashRegistersCount,
                'latest_order_at' => Order::where('user_id', $user->id)->latest()->value('created_at'),
            ],
            'recent_orders' => $recentOrders,
            'recent_cash_registers' => $recentCashRegisters,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::find($id);
        $roles = Role::all();

        return response()->json([
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->all();
        $user = User::find($id);
        $user->update($data);

        return response()->json($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['message' => 'Utilizador não encontrado.'], 404);
        }

        $hasOrders = Order::where('user_id', $user->id)->exists();

        if ($hasOrders) {
            return response()->json(['message' => 'Não é possível eliminar o utilizador, existem encomendas associadas.'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'Utilizador removido com sucesso.']);
    }
}
