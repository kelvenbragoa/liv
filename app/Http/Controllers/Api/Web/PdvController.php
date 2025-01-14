<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;


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
        $total_consumed = 0;


        if($table->table_status_id == 1){
            $order =  Order::create([
                'table_id' => $table->id,
                // 'user_id' => Auth::user()->id,
                'user_id'=>1,
                'total'=>$data['total'],
                'order_status_id' => 1
            ]);
            foreach($data['products'] as $item){
                $product = Product::find($item['id']);
                OrderItem::create([
                    'order_id'=>$order->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'order_item_status_id' =>1,
                    'price'=>$product->price,
                    'total'=>$product->price * $item['quantity']
                ]);
            }
            $total_consumed = $data['total'];
        }
        if($table->table_status_id == 2){

            $last_order = Order::where('table_id',$table->id)->where('order_status_id',1)->first();
            
            foreach($data['products'] as $item){
                
                $orderItem = OrderItem::where('order_id', $last_order->id)->where('product_id', $item['id'])->first();
                $product = Product::find($item['id']);

                if($orderItem){
                    $quantity_updated = $orderItem->quantity + $item['quantity'];
                    
                    $orderItem->update([
                        'quantity' => $quantity_updated,
                        'total'=>$orderItem->price * $quantity_updated,
                    ]);
                }else{
                    OrderItem::create([
                        'order_id'=>$last_order->id,
                        'product_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'order_item_status_id' =>1,
                        'price'=>$product->price,
                        'total'=>$product->price * $item['quantity']
                    ]);
                }
                
            }

            $last_order->update([
                'total'=>OrderItem::where('order_id', $last_order->id)->sum('total')
            ]);
            $total_consumed = OrderItem::where('order_id', $last_order->id)->sum('total');
        }
        $table->update([
            'table_status_id'=>2
        ]);

        // $table = Table::find($id);
        $order = Order::where('table_id',$table->id)->where('order_status_id',1)->first();
        $orderitens = OrderItem::where('order_id',$order->id)->get();

        $pdf = Pdf::loadView('pdf.receipt', compact('table','order','orderitens'))->setOptions([
            'setPaper'=>'a4',
            'defaultFont' => 'sans-serif',
            'isRemoteEnabled' => 'true'
        ]);
        return $pdf->setPaper('a4')->stream('receipt.pdf');
        
        // $this->getreceipt($table->id);
        // return response()->json([
        //     'table'=>$table,
        //     'total_consumed'=>$total_consumed
        // ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $categories = Category::with('sub_categories.products')->get();
        $total_consumed = Order::where('table_id', $id)->where('order_status_id', 1)->sum('total');
        return response()->json([
            "categories"=>$categories,
            "total_consumed"=>$total_consumed
        ]);
    }

    public function savequicksell(Request $request){
        $data = $request->all();
        $total_consumed = 0;


            $order =  Order::create([
                // 'user_id' => Auth::user()->id,
                'user_id'=>1,
                'total'=>$data['total'],
                'order_status_id' => 1
            ]);
            foreach($data['products'] as $item){
                $product = Product::find($item['id']);
                OrderItem::create([
                    'order_id'=>$order->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'order_item_status_id' =>1,
                    'price'=>$product->price,
                    'total'=>$product->price * $item['quantity']
                ]);
            }
            $total_consumed = $data['total'];
  
        // $table = Table::find($id);
        $orderitens = OrderItem::where('order_id',$order->id)->get();

        $pdf = Pdf::loadView('pdf.receiptquicksell', compact('order','orderitens'))->setOptions([
            // 'setPaper'=>'a4',
            'setPaper' => [0, 0, 640, 2376],
            'defaultFont' => 'sans-serif',
            'isRemoteEnabled' => 'true'
        ]);
        return $pdf->setPaper('a4')->stream('receiptquicksell.pdf');
    }

    public function quicksell(){
        $categories = Category::with('sub_categories.products')->get();
        $total_consumed = 0;
        return response()->json([
            "categories"=>$categories,
            "total_consumed"=>$total_consumed
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

    public function getreceipt($id){
        $table = Table::find($id);
        $order = Order::where('table_id',$table->id)->where('order_status_id',1)->first();
        $orderitens = OrderItem::where('order_id',$order->id)->get();

        $pdf = Pdf::loadView('pdf.receipt', compact('table','order','orderitens'))->setOptions([
            'setPaper'=>'a4',
            'defaultFont' => 'sans-serif',
            'isRemoteEnabled' => 'true'
        ]);
        return $pdf->setPaper('a4')->stream('receipt.pdf');
    }
}
