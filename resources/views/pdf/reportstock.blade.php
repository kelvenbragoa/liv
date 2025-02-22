<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Vendas</title>
    <style type="text/css">
        @page {
            margin: 20px;
        }
        body {
            font-family: Verdana, Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 90%;
            margin: auto;
        }
        .header, .footer {
            background-color: #01090e;
            color: white;
            padding: 10px;
            text-align: center;
        }
        .header img {
            max-width: 150px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 5px;
        }
        .content {
            text-align: center;
            margin-top: 30px;
        }
        .content h3 {
            margin-bottom: 10px;
            font-size: 18px;
        }
        .details, .cards {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .details th, .details td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .details th {
            background-color: #1795ee;
            color: white;
        }
        
        .footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            text-align: center;
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
            <h3>Relatório de Stock</h3>

            <h3>Produtos Stock</h3>
            <table class="details">
                <tr>
                    <th>Produto</th>
                    <th>Quantidade Vendida</th>
                    <th>Valor Total</th>
                    <th>Stock Atual</th>
                </tr>
                @php
                    $total_geral_quantidade = 0;
                    $total_geral_valor = 0;
                @endphp
                @foreach ($orderItemsTableReport as $item)
                    <tr>
                        <td>{{ $item->product->name ?? 'Desconhecido' }}</td>
                        <td>{{ $item->total_quantity }}</td>
                        <td>{{ number_format($item->total_value, 2, ',', '.') }} MT</td>
                        <td>{{ $item->product->quantity_in_principal_stock ?? 0 }}</td>
                    </tr>
                    @php
                        $total_geral_quantidade += $item->total_quantity;
                        $total_geral_valor += $item->total_value;
                    @endphp
                @endforeach
                <tr>
                    <th>Total</th>
                    <td>{{ $total_geral_quantidade }}</td>
                    <td>{{ number_format($total_geral_valor, 2, ',', '.') }} MT</td>
                </tr>
                
            </table>

           

        </div>
    </div>
    
    <div class="footer">
        &copy; {{ date('Y') }} LIV Beira. Todos os direitos reservados. | Beira, Mozambique
    </div>
</body>
</html>
