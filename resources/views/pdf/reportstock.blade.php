<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Stocks</title>
    <style type="text/css">
        @page {
            size: A4 landscape;
            margin: 14px 12px 30px;
        }
        body {
            font-family: Verdana, Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 10px;
            color: #111827;
        }
        .container {
            width: 100%;
            margin: 0;
        }
        .header, .footer {
            background-color: #01090e;
            color: white;
            padding: 8px 10px;
            text-align: center;
        }
        .header img {
            max-width: 110px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 12px;
        }
        .info-table td {
            padding: 4px;
            vertical-align: middle;
        }
        .content {
            text-align: center;
            margin-top: 14px;
        }
        .content h3 {
            margin: 10px 0 6px;
            font-size: 14px;
        }
        .details, .cards {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed;
        }
        .details th, .details td {
            border: 1px solid #ddd;
            padding: 4px 5px;
            text-align: left;
            font-size: 9px;
            line-height: 1.25;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .details th {
            background-color: #1795ee;
            color: white;
        }
        .details.compact th,
        .details.compact td {
            font-size: 8px;
            padding: 4px;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .section-note {
            font-size: 10px;
        }
        .total-row th,
        .total-row td {
            font-weight: bold;
            background-color: #f3f4f6;
        }
        .col-product {
            width: 22%;
        }
        .col-qty {
            width: 8%;
        }
        .col-money {
            width: 11%;
        }
        .col-stock {
            width: 9%;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <div class="header">
        <table class="info-table">
            <tr>
                <td align="left" style="width: 40%; font-weight: bold;">Liv Beira</td>
                <td align="center">
                    <img src="https://liv.mtaxas.co.mz/image/liv.png" alt="Logo" class="logo"/>
                </td>
                <td align="right" style="width: 40%;">
                    <strong>Liv Beira</strong><br>
                    <a href="https://liv.mtaxas.co.mz" style="color:white;">liv.mtaxas.co.mz</a><br>
                    +258 84 000 0000<br>
                    Beira, Mozambique
                </td>
            </tr>
        </table>
    </div>

    <div class="container">
        <div class="content">
            <h3>Relatório de Stocks</h3>
                <small class="section-note">Data e hora de registro de quantidade atual dos produtos: {{ $orderItemsTableReportBar[0]['initial_created'] ?? 'N/A' }}</small>


            <h3>Produtos Stock Bar</h3>
            <table class="details compact">
                <tr>
                    <th class="col-product">Produto</th>
                    <th class="col-qty text-center">Qtd. Vend.</th>
                    <th class="col-money text-right">P. Venda</th>
                    <th class="col-money text-right">V. Total</th>
                    <th class="col-stock text-center">Qtd. Inicial</th>
                    <th class="col-stock text-center">Stock Atual</th>
                    <th class="col-money text-right">P. Compra</th>
                    <th class="col-money text-right">T. Compra</th>
                    <th class="col-money text-right">Lucro</th>
                </tr>
                @php
                    $total_geral_quantidade = 0;
                    $total_geral_valor = 0;
                @endphp
                @foreach ($orderItemsTableReportBar as $item)
                    <tr>
                        <td>{{ $item->product->name ?? 'Desconhecido' }}</td>
                        <td class="text-center">{{ $item->total_quantity }}</td>
                        <td class="text-right">{{ number_format($item->product->price ?? 0, 2, ',', '.') }} MT</td>
                        <td class="text-right">{{ number_format($item->total_value, 2, ',', '.') }} MT</td>
                        <td class="text-center">{{ $item->initial_stock_quantity ?? 0 }}</td>
                        <td class="text-center">{{ $item->product->quantity_in_principal_stock ?? 0 }}</td>
                        <td class="text-right">{{ number_format($item->product->buy_price ?? 0, 2, ',', '.') }} MT</td>
                        <td class="text-right">{{ number_format(($item->product->buy_price ?? 0) * ($item->total_quantity ?? 0), 2, ',', '.') }} MT</td>
                        <td class="text-right">{{ number_format($item->total_value - (($item->product->buy_price ?? 0) * ($item->total_quantity ?? 0)), 2, ',', '.') }} MT</td>
                    </tr>
                    @php
                        $total_geral_quantidade += $item->total_quantity;
                        $total_geral_valor += $item->total_value;
                    @endphp
                @endforeach
                <tr class="total-row">
                    <th>Total</th>
                    <td class="text-center">{{ $total_geral_quantidade }}</td>
                    <td></td>
                    <td class="text-right">{{ number_format($total_geral_valor, 2, ',', '.') }} MT</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                
            </table>

            <h3>Produtos Stock Cozinha</h3>
            <table class="details compact">
                <tr>
                    <th class="col-product">Produto</th>
                    <th class="col-qty text-center">Qtd. Vend.</th>
                    <th class="col-money text-right">P. Venda</th>
                    <th class="col-money text-right">V. Total</th>
                    <th class="col-stock text-center">Qtd. Inicial</th>
                    <th class="col-stock text-center">Stock Atual</th>
                    <th class="col-money text-right">P. Compra</th>
                    <th class="col-money text-right">T. Compra</th>
                    <th class="col-money text-right">Lucro</th>
                </tr>
                @php
                    $total_geral_quantidade_kitchen = 0;
                    $total_geral_valor_kitchen = 0;
                    $total_geral_compra_kitchen = 0;
                    $total_geral_lucro_kitchen = 0;
                @endphp
                @foreach ($orderItemsTableReportKitchen as $item)
                    @php
                        $total_compra_item_kitchen = ($item->product->buy_price ?? 0) * ($item->total_quantity ?? 0);
                        $lucro_item_kitchen = $item->total_value - $total_compra_item_kitchen;
                    @endphp
                    <tr>
                        <td>{{ $item->product->name ?? 'Desconhecido' }}</td>
                        <td class="text-center">{{ $item->total_quantity }}</td>
                        <td class="text-right">{{ number_format($item->product->price ?? 0, 2, ',', '.') }} MT</td>
                        <td class="text-right">{{ number_format($item->total_value, 2, ',', '.') }} MT</td>
                        <td class="text-center">{{ $item->initial_stock_quantity ?? 0 }}</td>
                        <td class="text-center">{{ $item->product->quantity_in_principal_stock ?? 0 }}</td>
                        <td class="text-right">{{ number_format($item->product->buy_price ?? 0, 2, ',', '.') }} MT</td>
                        <td class="text-right">{{ number_format($total_compra_item_kitchen, 2, ',', '.') }} MT</td>
                        <td class="text-right">{{ number_format($lucro_item_kitchen, 2, ',', '.') }} MT</td>
                    </tr>
                    @php
                        $total_geral_quantidade_kitchen += $item->total_quantity;
                        $total_geral_valor_kitchen += $item->total_value;
                        $total_geral_compra_kitchen += $total_compra_item_kitchen;
                        $total_geral_lucro_kitchen += $lucro_item_kitchen;
                    @endphp
                @endforeach
                <tr class="total-row">
                    <th>Total</th>
                    <td class="text-center">{{ $total_geral_quantidade_kitchen }}</td>
                    <td></td>
                    <td class="text-right">{{ number_format($total_geral_valor_kitchen, 2, ',', '.') }} MT</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="text-right">{{ number_format($total_geral_compra_kitchen, 2, ',', '.') }} MT</td>
                    <td class="text-right">{{ number_format($total_geral_lucro_kitchen, 2, ',', '.') }} MT</td>
                </tr>
                
            </table>

           

        </div>
    </div>
    <br>

    <br>
    
    <div class="footer">
        &copy; {{ date('Y') }} LIV Beira. Todos os direitos reservados. | Beira, Mozambique
    </div>
</body>
</html>
