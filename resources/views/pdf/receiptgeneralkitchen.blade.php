<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo</title>
    <style type="text/css">
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            width: 80mm; /* Largura para impressoras térmicas de 80mm */
        }
        .header, .footer {
            text-align: center;
            margin-bottom: 10px;
        }
        .header h1, .footer p {
            margin: 0;
            padding: 0;
        }
        hr {
            border: 1px dashed #000;
        }
        .content {
            margin: 5px 0;
        }
        .details {
            margin-bottom: 10px;
        }
        .details p {
            margin: 2px 0;
        }
        .items {
            width: 100%;
        }
        .items th, .items td {
            text-align: left;
            padding: 5px 0;
        }
        .items th {
            border-bottom: 1px solid #000;
        }
        .total {
            margin-top: 10px;
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
        }
        @page {
            margin: 0px;
        }
        .break-page{
          page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LIV BEIRA</h1>
        <p>Maquinino,Beira</p>
        <p>Telefone: +258 84 000 000</p>
         <p>COZINHA</p>

    </div>
    <hr>
    <div class="content">
        <div class="details">
            <p>Estado: {{$orderitem->status?->name ?? 'NA'}}</p>
            <p>Data Criação: {{ $orderitem->created_at->format('d-m-Y H:i') }}</p>
            <p>Data Atualização: {{ $orderitem->updated_at->format('d-m-Y H:i') }}</p>
            <p>Pedido Nº: {{ $orderitem->order->id }}</p>
            <p>Mesa Nº: {{ $orderitem->order->table?->name ?? 'Pedido Rápido' }}</p>
            <p>Atendente:{{$orderitem->user?->name ?? 'NA'}}</p>
        </div>
        <table class="items">
            <thead>
                <tr>
                    <th>Qtd</th>
                    <th>Produto</th>
                  
                </tr>
            </thead>
            <tbody>
                
                <tr>
                    <td>{{ $orderitem->quantity }}</td>
                    <td>{{ $orderitem->product->name }}</td>
                    
                </tr>
                
            </tbody>
        </table>
        {{-- <div class="total">
            <p>Total Geral: MZN {{ number_format($order->total, 2) }}</p>
        </div> --}}
    </div>
    <hr>
    <div class="footer">
        <p>Obrigado pela preferência!</p>
        <p>Visite-nos novamente!</p>
    </div>
   
</body>
</html>


