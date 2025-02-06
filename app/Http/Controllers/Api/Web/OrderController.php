<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

            $orders = Order::query()
            ->when(request('query'),function($query,$searchQuery){
                $query->where('id','like',"%{$searchQuery}%");
            })
            ->with('table')
            ->with('itens')
            ->with('status')
            ->orderBy('created_at','desc')
            ->paginate();

            return response()->json($orders);
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
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $order = Order::with('table')->with('itens')->with('status')->find($id);

        return response()->json($order);
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
        $orderitem->delete();

        $categories = Category::with('sub_categories.products')->get();
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
        $order->update([
            'total' => $order->total - $total
        ]);

        
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
}
