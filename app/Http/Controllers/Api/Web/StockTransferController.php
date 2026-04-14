<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Models\StockCenter;
use App\Models\StockCenterProduct;
use App\Models\StockCenterTransfer;
use App\Models\StockCenterTransferItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockTransferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

        $stocktransfers = StockCenterTransfer::query()
            ->when(request('query'),function($query,$searchQuery){
                $query->where('ref','like',"%{$searchQuery}%");
            })
            ->with('stockcenterorigin')
            ->with('stockcenterdestination')
            ->orderBy('created_at','desc')
            ->paginate();

            return response()->json($stocktransfers);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $stockcenters = StockCenter::all();
        return response()->json(["stockcenters"=>$stockcenters]);
       
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $data = $request->all();

        // Validar se stockcenterproducts existe e não está vazio
        if (!isset($data['stockcenterproducts']) || !is_array($data['stockcenterproducts']) || empty($data['stockcenterproducts'])) {
            return response()->json([
                'message' => 'Nenhum produto foi selecionado. Por favor, selecione pelo menos um produto.'
            ], 422);
        }

        // Validar estoque antes de processar
        $insufficientStock = [];
        foreach($data['stockcenterproducts'] as $item){
            // Ignorar produtos com quantidade 0
            if (!isset($item['quantity']) || $item['quantity'] <= 0) {
                continue;
            }

            $stockcenterproduct = StockCenterProduct::find($item['id']);
            if (!$stockcenterproduct) {
                continue;
            }

            if ($stockcenterproduct->quantity < $item['quantity']) {
                $insufficientStock[] = [
                    'product' => $stockcenterproduct->product->name ?? "ID: {$item['product_id']}",
                    'available' => $stockcenterproduct->quantity,
                    'requested' => $item['quantity']
                ];
            }
        }

        if (!empty($insufficientStock)) {
            $errorMessage = "Estoque insuficiente para os seguintes produtos:\n";
            foreach ($insufficientStock as $stock) {
                $errorMessage .= "- {$stock['product']}: disponível {$stock['available']}, solicitado {$stock['requested']}\n";
            }
            return response()->json([
                'message' => $errorMessage,
                'insufficient_stock' => $insufficientStock
            ], 422);
        }

        // dd($data);


        
        $stock_transfer = StockCenterTransfer::create([
            'ref'=>$data['reference'],
            'user_id'=>Auth::user()->id,
            'transfer_date'=>$data['transfer_date'],
            'stock_center_transfer_status_id'=>1,
            'stock_center_origin_id'=>$data['stock_center_origin_id'],
            'stock_center_destination_id'=>$data['stock_center_destination_id'],
        ]);

        foreach($data['stockcenterproducts'] as $item){

            // Ignorar produtos com quantidade 0
            if (!isset($item['quantity']) || $item['quantity'] <= 0) {
                continue;
            }

            //origin

            $stockcenterproduct = StockCenterProduct::find($item['id']);

            $last_quantity = $stockcenterproduct->quantity;

            // $product = Product::find($item['product_id']);
            // $product_last_quantity = $product->quantity;


            // //stock origin
            // if($data['stock_center_origin_id'] == 1){
            //     $product->update([
            //         'quantity'=>$product_last_quantity - $item['quantity']
            //     ]);
            // }
            $stockcenterproduct->update([
                'quantity'=> $last_quantity - $item['quantity']
            ]);

            

            //stock destination
            $stockcenterproductdestination = StockCenterProduct::where('stock_center_id',$data['stock_center_destination_id'])->where('product_id',$item['product_id'])->first();
            $stockcenterproductdestination_last_quantity = $stockcenterproductdestination->quantity;

            $stockcenterproductdestination->update([
                'quantity'=> $stockcenterproductdestination_last_quantity + $item['quantity']
            ]);

            

            // $product_destination = Product::find($item['product_id']);

            // if($data['stock_center_destination_id'] == 1){
            //     $product_destination->update([
            //         'quantity'=>$stockcenterproductdestination_last_quantity + $item['quantity']
            //     ]);
            // }

          

            $stockTransferItem = StockCenterTransferItem::create([
                // 'stock_center_origin_id'=>$data['stock_center_origin_id'],
                // 'stock_center_destination_id'=>$data['stock_center_destination_id'],
                'stock_center_transfer_id'=>$stock_transfer->id,
                'product_id'=>$item['product_id'],
                'quantity'=>$item['quantity'],
            ]);


            


        }

        return [
            'message'=>'success'
        ];
        return response()->json($category);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $stocktransfer = StockCenterTransfer::
        with('stockcenterorigin')
        ->with('stockcenterdestination')
        ->with('itens.product')
        ->with('user')
        ->find($id);

        return response()->json($stocktransfer);
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
        $data = $request->all();
        $category = StockCenterTransfer::find($id);
        $category->update($data);
        return response()->json($category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        $category = StockCenterTransfer::find($id);
        $category->delete();
        return true;
    }

    public function products($id){
        $stockcenterproducts = StockCenterProduct::with('product.category')->with('stockcenter')->with('product.subcategory')->where('stock_center_id',$id)->get();
        return [
            'stockcenterproducts' => $stockcenterproducts
        ];
    }
}
