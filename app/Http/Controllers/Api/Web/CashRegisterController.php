<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Models\CashRegister;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashRegisterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //

        $existingCashRegister = CashRegister::where('user_id', Auth::user()->id)
        ->where('status_id', 1)
        ->first();

        if ($existingCashRegister) {
            return response()->json([
                'message' => 'Já existe um caixa aberto para este usuário.',
                'cash_register' => $existingCashRegister
            ], 400);
        }
        $cashregister = CashRegister::create([
            'user_id' => Auth::user()->id,
            'status_id' => 1,
            'opening_balance'=>0,
            'closing_balance'=>0,
            'opened_at' => now(),
        ]);

        return response()->json($cashregister);
    }

    public function close(Request $request)
    {
        $cashRegister = CashRegister::
            where('user_id', Auth::id())
            ->where('status_id', 1) // 1 = Aberto
            ->first();

        if (!$cashRegister) {
            return response()->json([
                'message' => 'Nenhum caixa aberto encontrado para este usuário.'
            ], 404);
        }



        // Calcular o total de vendas realizadas durante o período do caixa
        $totalSales = OrderItem::where('cash_register_id', $cashRegister->id)
            ->sum('total');

        // Atualizar informações do caixa
        $cashRegister->update([
            'closing_balance' => $totalSales,
            'closed_at' => now(),
            'status_id' => 2 // 2 = Fechado
        ]);

        // // Calcular a diferença entre o saldo esperado e o real
        // $expectedBalance = $cashRegister->opening_balance + $totalSales;
        // $difference = $request->closing_balance - $expectedBalance;

        return response()->json([
            'message' => 'Caixa fechado com sucesso!',
            // 'cash_register' => $cashRegister,
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
}
