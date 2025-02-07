<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\CashRegister;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TableMobileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

            $tables = Table::query()
            ->when(request('query'),function($query,$searchQuery){
                $query->where('name','like',"%{$searchQuery}%");
            })
            ->with('status')
            ->orderBy('name','asc')
            ->get();

            return response()->json($tables);
    }

    public function createorder(Request $request){

        $data = $request->all();
        $table = Table::find($data['table_id']);

        // $openCashRegister = CashRegister::where('user_id', Auth::id())
        // ->where('status_id', 1) // 1 = Aberto
        // ->first();

        // if (!$openCashRegister) {
        //     return response()->json([
        //         'message' => 'Não é possível realizar a venda. Abra o caixa primeiro.'
        //     ], 403); // Código HTTP 403 - Proibido
        // }

        if($table->table_status_id == 5){
            return response()->json(['message'=>'A mesa esta finalizada.'],200);
        }
        $product = Product::find($data['product_id']);

        if($table->table_status_id == 1){
            $order =  Order::create([
                'table_id' => $table->id,
                // 'user_id' => Auth::user()->id,
                'user_id'=>$data['user_id'],
                'total'=>$data['quantity'] * $product->price,
                'order_status_id' => 1,
                // 'cash_register_id' => $openCashRegister->id
            ]);

                $product = Product::find($data['product_id']);
                $orderItem = OrderItem::create([
                    'order_id'=>$order->id,
                    'product_id' => $data['product_id'],
                    'quantity' => $data['quantity'],
                    'order_item_status_id' =>1,
                    'department_id' => $product->department_id,
                    'price'=>$product->price,
                    'total'=>$data['quantity'] * $product->price,
                    'user_id'=>Auth::user()->id,
                    // 'cash_register_id' => $openCashRegister->id
                ]);
        }

        if($table->table_status_id == 2){

            $last_order = Order::where('table_id',$table->id)->where('order_status_id',1)->first();
            
                
                // $orderItem = OrderItem::where('order_id', $last_order->id)->where('product_id', $data['product_id'])->first();
                $product = Product::find($data['product_id']);

                // if($orderItem){
                //     $quantity_updated = $orderItem->quantity + $data['quantity'];
                    
                //     $orderItem->update([
                //         'quantity' => $quantity_updated,
                //         'total'=>$orderItem->price * $quantity_updated,
                //     ]);
                // }else{
                    $orderItem = OrderItem::create([
                        'order_id'=>$last_order->id,
                        'product_id' => $data['product_id'],
                        'quantity' => $data['quantity'],
                        'department_id' => $product->department_id,
                        'order_item_status_id' =>1,
                        'price'=>$product->price,
                        'total'=>$product->price * $data['quantity'],
                        'user_id'=>Auth::user()->id,
                        // 'cash_register_id' => $openCashRegister->id
                    ]);
                // }

                $last_order->update([
                    'total'=>OrderItem::where('order_id', $last_order->id)->sum('total')
                ]);
                $total_consumed = OrderItem::where('order_id', $last_order->id)->sum('total');
            }

            
            

            $table->update([
                'table_status_id'=>2
            ]);

            return response()->json([
                'message'=>"Produto adiconado a conta"
            ]);

    }

    /**
     * Show the form for creating a new resource.
     */

    public function consumption(string $id){
        $table = Table::find($id);

        $order = Order::where('table_id', $table->id)
        ->where(function($query) {
            $query->where('order_status_id', 1)
                ->orWhere('order_status_id', 2);
        })
        ->with('itens.product')
        ->with('status')
        ->with('table')
        ->with('user')
        ->first();
        $order_id = 0;
        if ($order) {
            $order_id = $order->id;
        }

        
       

        return response()->json([
            "order"=>$order
        ]);

    }

    public function closeaccount(string $id){
        $table = Table::find($id);
        $order = Order::where('table_id', $id)->where('order_status_id', 1)->first();

        $order->update([
            "order_status_id"=>2
        ]);

        $table->update([
            "table_status_id"=>5
        ]);

        $orderitens = OrderItem::where('order_id',$order->id)->get();

        return response()->json([
            "message"=>"Conta fechada com sucesso",
        ]);



    }
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
