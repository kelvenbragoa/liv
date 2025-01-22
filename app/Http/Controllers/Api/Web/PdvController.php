<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
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

        $newBarItems = [];
        $newKitchenItems = [];

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
                $orderItem = OrderItem::create([
                    'order_id'=>$order->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'order_item_status_id' =>1,
                    'department_id' => $product->department_id,
                    'price'=>$product->price,
                    'total'=>$product->price * $item['quantity']
                ]);
                if ($product->department_id == 1) {
                    $newKitchenItems[] = $orderItem;
                } elseif ($product->department_id == 2) {
                    $newBarItems[] = $orderItem;
                }
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
                    $orderItem = OrderItem::create([
                        'order_id'=>$last_order->id,
                        'product_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'department_id' => $product->department_id,
                        'order_item_status_id' =>1,
                        'price'=>$product->price,
                        'total'=>$product->price * $item['quantity']
                    ]);
                    if ($product->department_id == 1) {
                        $newKitchenItems[] = $orderItem;
                    } elseif ($product->department_id == 2) {
                        $newBarItems[] = $orderItem;
                    }
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

        $order = Order::where('table_id',$table->id)->where('order_status_id',1)->first();
        $orderitens = OrderItem::where('order_id',$order->id)->get();

        $barItems = $orderitens->where('department_id', 2);
        $kitchenItems = $orderitens->where('department_id', 1);


        $pdf = Pdf::loadView('pdf.receipt', compact('order','orderitens','barItems','kitchenItems'))->setOptions([
            'defaultFont' => 'sans-serif',
            'isRemoteEnabled' => 'true'
        ]);
        return $pdf->setPaper([0, 0, 226.77, 841.89])->stream('receipt.pdf');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $categories = Category::with('sub_categories.products')->get();
        $order = Order::where('table_id', $id)->where('order_status_id', 1)->orWhere('order_status_id', 2)->first();
        $order_id = 0;
        if ($order) {
            $order_id = $order->id;
        }

        
        $total_consumed = Order::where('table_id', $id)->where('order_status_id', 1)->orWhere('order_status_id', 2)->sum('total');
        $payment_methods = PaymentMethod::all();
        $orderItems = OrderItem::where('order_id',$order_id)->with('product')->get();

        return response()->json([
            "categories"=>$categories,
            "total_consumed"=>$total_consumed,
            "payment_methods"=>$payment_methods,
            "order_items"=>$orderItems
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
                    'department_id' => $product->department_id,
                    'quantity' => $item['quantity'],
                    'order_item_status_id' =>1,
                    'price'=>$product->price,
                    'total'=>$product->price * $item['quantity']
                ]);
            }
            $total_consumed = $data['total'];
  
        // $table = Table::find($id);
        $orderitens = OrderItem::where('order_id',$order->id)->get();

        $payment = Payment::create([
            "order_id"=>$order->id,
            "payment_method_id"=>$data["payment_method_id"],
            "amount"=>$data['total'],
        ]);

        $barItems = $orderitens->where('department_id', 2);
        $kitchenItems = $orderitens->where('department_id', 1);

        $pdf = Pdf::loadView('pdf.receiptquicksell', compact('order','orderitens','barItems','kitchenItems'))->setOptions([
            // 'setPaper'=>'a4',
            // 'setPaper' => [0, 0, 226.77, 841.89],
            'defaultFont' => 'sans-serif',
            'isRemoteEnabled' => 'true'
        ]);
        return $pdf->setPaper([0, 0, 226.77, 841.89])->stream('receiptquicksell.pdf');
    }

    public function quicksell(){
        $categories = Category::with('sub_categories.products')->get();
        $total_consumed = 0;
        $payment_methods = PaymentMethod::all();
        return response()->json([
            "categories"=>$categories,
            "total_consumed"=>$total_consumed,
            "payment_methods"=>$payment_methods
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

    public function getreceipt(string $id) {

        $order = Order::where('table_id', $id)->where('order_status_id', 1)->first();
        if (!$order) {
            abort(404, 'Order not found');
        }
        $orderitens = OrderItem::with('product')->where('order_id', $order->id)->get();
        $table = $order->table; // Supondo que o relacionamento exista
    
        $pdf = Pdf::loadView('pdf.receiptgeneral', compact('order', 'orderitens', 'table'))->setOptions([
            'defaultFont' => 'sans-serif',
            'isRemoteEnabled' => true,
        ]);
    
        return $pdf->setPaper([0, 0, 226.77, 841.89])->stream('receiptgeneral.pdf');
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

        $pdf = Pdf::loadView('pdf.finalreceipt', compact('order','orderitens'))->setOptions([
            // 'setPaper'=>'a4',
            // 'setPaper' => [0, 0, 226.77, 841.89],
            'defaultFont' => 'sans-serif',
            'isRemoteEnabled' => 'true'
        ]);
        return $pdf->setPaper([0, 0, 226.77, 841.89])->stream('finalreceipt.pdf');



    }

    public function payaccount(Request $request){
        $data = $request->all();
        $table = Table::find($data['table_id']);
        $order = Order::where('table_id', $data['table_id'])->where('order_status_id', 2)->first();

        $order->update([
            "order_status_id"=>3
        ]);

        $table->update([
            "table_status_id"=>1
        ]);

        $orderitens = OrderItem::where('order_id',$order->id)->get();

        foreach($orderitens as $item){
            $item->update([
                'order_item_status_id'=>4
            ]);
        }

        $payment = Payment::create([
            "order_id"=>$order->id,
            "payment_method_id"=>$data["payment_method_id"],
            "amount"=>$order->total
        ]);

        $pdf = Pdf::loadView('pdf.customerreceipt', compact('order','orderitens','payment'))->setOptions([
            // 'setPaper'=>'a4',
            // 'setPaper' => [0, 0, 226.77, 841.89],
            'defaultFont' => 'sans-serif',
            'isRemoteEnabled' => 'true'
        ]);
        return $pdf->setPaper([0, 0, 226.77, 841.89])->stream('customerreceipt.pdf');

    }



    public function indexKitchen(){
        $order_itens_pending = OrderItem::where('order_item_status_id', 1)->where('department_id',1)->with('product')->with('order.table')->orderBy('created_at','asc')->get();
        $order_itens_getting_ready = OrderItem::where('order_item_status_id', 2)->where('department_id',1)->with('product')->with('order.table')->orderBy('updated_at','desc')->get();
        $order_itens_ready = OrderItem::where('order_item_status_id', 3)->where('department_id',1)->with('product')->with('order.table')->orderBy('updated_at','desc')->get();
        $order_itens_delivered = OrderItem::where('order_item_status_id', 4)->where('department_id',1)->with('product')->with('order.table')->orderBy('updated_at','desc')->get();

        return response()->json([
            "order_itens_pending"=>$order_itens_pending,
            "order_itens_getting_ready"=>$order_itens_getting_ready,
            "order_itens_ready"=>$order_itens_ready,
            "order_itens_delivered"=>$order_itens_delivered
        ]);
    }

    public function changestatus(string $id)
{
    $order_item = OrderItem::find($id);

    if (!$order_item) {
        return response()->json(['message' => 'Order item not found'], 404);
    }

    $currentStatus = $order_item->order_item_status_id;

    switch ($currentStatus) {
        case 1:
            $nextStatus = 2;
            break;
        case 2:
            $nextStatus = 3;
            break;
        case 3:
            $nextStatus = 4;
            break;
        case 4:
            $nextStatus = 4;
            break;
        default:
            return response()->json(['message' => 'Invalid status'], 400);
    }

    $order_item->update(['order_item_status_id' => $nextStatus]);


    $order_itens_pending = OrderItem::where('order_item_status_id', 1)->where('department_id',1)->with('product')->with('order.table')->orderBy('created_at','asc')->get();
    $order_itens_getting_ready = OrderItem::where('order_item_status_id', 2)->where('department_id',1)->with('product')->with('order.table')->orderBy('updated_at','desc')->get();
    $order_itens_ready = OrderItem::where('order_item_status_id', 3)->where('department_id',1)->with('product')->with('order.table')->orderBy('updated_at','desc')->get();
    $order_itens_delivered = OrderItem::where('order_item_status_id', 4)->where('department_id',1)->with('product')->with('order.table')->orderBy('updated_at','desc')->get();

    return response()->json([
        "order_itens_pending"=>$order_itens_pending,
        "order_itens_getting_ready"=>$order_itens_getting_ready,
        "order_itens_ready"=>$order_itens_ready,
        "order_itens_delivered"=>$order_itens_delivered
    ]);
}


public function indexBar(){
    $order_itens_pending = OrderItem::where('order_item_status_id', 1)->where('department_id',2)->with('product')->with('order.table')->orderBy('created_at','asc')->get();
    
    $order_itens_delivered = OrderItem::where('order_item_status_id', 4)->where('department_id',2)->with('product')->with('order.table')->orderBy('updated_at','desc')->get();

    return response()->json([
        "order_itens_pending"=>$order_itens_pending,
        
        "order_itens_delivered"=>$order_itens_delivered
    ]);
}

public function barchangestatus(string $id)
{
$order_item = OrderItem::find($id);

if (!$order_item) {
    return response()->json(['message' => 'Order item not found'], 404);
}

$currentStatus = $order_item->order_item_status_id;

switch ($currentStatus) {
    case 1:
        $nextStatus = 4;
        break;
    case 4:
        $nextStatus = 4;
        break;
    default:
        return response()->json(['message' => 'Invalid status'], 400);
}

$order_item->update(['order_item_status_id' => $nextStatus]);


$order_itens_pending = OrderItem::where('order_item_status_id', 1)->where('department_id',1)->with('product')->with('order.table')->orderBy('created_at','asc')->get();
$order_itens_delivered = OrderItem::where('order_item_status_id', 4)->where('department_id',1)->with('product')->with('order.table')->orderBy('updated_at','desc')->get();

return response()->json([
    "order_itens_pending"=>$order_itens_pending,
    "order_itens_delivered"=>$order_itens_delivered
]);
}
}
