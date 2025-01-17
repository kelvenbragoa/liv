<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;


class GlobalController extends Controller
{
    //
    public function receipt(){

        $order = Order::where('table_id',1)->where('order_status_id',1)->first();
        $orderitens = OrderItem::where('order_id',$order->id)->get();

        $pdf = Pdf::loadView('pdf.receipt', compact('order','orderitens'))->setOptions([
            'setPaper'=>'a8',
            // 'setPaper' => [0, 0, 640, 2376],
            'defaultFont' => 'sans-serif',
            'isRemoteEnabled' => 'true'
        ]);
        return $pdf->setPaper('a4')->stream('receipt.pdf');
    }
}
