<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PdvController extends Controller
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
            ->paginate();

            return response()->json($tables);
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
        $data = $request->all();
        $table = Table::find($data['table_id']);
        // foreach($data['products'] as $item){
        //     dd($item['quantity']);
        // }
        // dd($data);
        $counter = 0;

        if($table->table_status_id == 1){
            $order =  Order::create([
                'table_id' => $table->id,
                // 'user_id' => Auth::user()->id,
                'user_id'=>1,
                'total'=>$data['total'],
                'order_status_id' => 1
            ]);
            foreach($data['products'] as $item){
                OrderItem::create([
                    'order_id'=>$order->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'order_item_status_id' =>1,
                ]);
            }
        }
        if($table->table_status_id == 2){

            $last_order = Order::where('table_id',$table->id)->where('order_status_id',1)->first();
            $last_order->update([
                'total'=>$data['total']
            ]);
            foreach($data['products'] as $item){
                $orderItem = OrderItem::where('order_id', $last_order->id)->where('product_id', $item['id'])->first();
                if($orderItem){
                    $orderItem->update([
                        'quantity' => $orderItem->quantity + $item['quantity'],
                    ]);
                }else{
                    OrderItem::create([
                        'order_id'=>$last_order->id,
                        'product_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'order_item_status_id' =>1,
                    ]);
                }
                
            }
        }
        $table->update([
            'table_status_id'=>2
        ]);
        

        return response()->json([
            'table'=>$table
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $categories = Category::with('sub_categories.products')->get();
        return response()->json(["categories"=>$categories]);
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
