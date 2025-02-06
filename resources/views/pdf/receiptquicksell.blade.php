{{-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style type="text/css">
        @page {
            margin: 0px;
        }
        .break-page{
          page-break-after: always;
        }

        html {
            margin-top: 70px ;
        
        }
        body {
            
            margin: 10px;
        }
        * {
            font-family: Verdana, Arial, sans-serif;
        }
        a {
            color: #fff;
            text-decoration: none;
        }
        table {
            font-size: x-small;
            border-collapse:collapse;
            border-radius:6px;
        }
        tfoot tr td {
            font-weight: bold;
            font-size: x-small;
        }
        .invoice {
            margin: 30px;
        }
        .invoice table {
            margin: 15px;
        }
        .invoice h3 {
            margin-left: 15px;
        }
        .information {
            background-color: #0eadf7;
            color: #000;
            margin: 30px;
        }

        .text{
            font-size:12px;
        }

        
        .information .logo {
            margin: 5px;
        }
        .information table {
            padding: 10px;

        }
        .body {
            margin: 30px;
        }
        table , tr , td{
        border:1px solid black;
        border-collapse: collapse;
}

.information2 {
            background-color: #0eadf7;
            color: #000;
            
        }

.header,
.footer {
    width: 100%;
    text-align: right;
    position: fixed;
    
}
.header {
    top: 0px;
}
.footer {
    bottom: 0px;
}
.pagenum:before {
    content: counter(page);
}

.remove_line{
border-right:2px solid white;    
}

.badge {
  color: white;
  padding: 3px 6px;
  text-align: center;
  border-radius: 5px;
}


    </style>
</head>
<body>
   <p>{{$order->created_at->format('d-m-Y')}}</p>
   <p>Original</p> 
   <p>23:30 BALCÃO 1</p>
   <hr>
   <p>DATA DE CAIXA: {{$order->created_at->format('d-m-Y')}}</p>
   <p>DATA E HORA: {{$order->created_at->format('d-m-Y H:i')}}</p>
   <p>PEDIDO Nº: {{$order->id}}</p>
   <h1>Balcao</h1>
   <hr>
   <ul>
    @foreach ($orderitens as $item)
        <li>{{$item->quantity}} - {{$item->product->name}}</li>
    @endforeach
   </ul>
   <hr>
   <p>Pedido por:User</p>

   <div class="break-page"></div>

   <p>{{$order->created_at->format('d-m-Y')}}</p>
   <p>Original</p> 
   <p>23:30 BALCÃO 1</p>
   <hr>
   <p>DATA DE CAIXA: {{$order->created_at->format('d-m-Y')}}</p>
   <p>DATA E HORA: {{$order->created_at->format('d-m-Y H:i')}}</p>
   <p>PEDIDO Nº: {{$order->id}}</p>
   <h1>Balcao</h1>
   <hr>
   <ul>
    @foreach ($orderitens as $item)
        <li>{{$item->quantity}} - {{$item->product->name}}</li>
    @endforeach
   </ul>
   <hr>
   <p>Pedido por:User</p>


</body>
</html> --}}

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
    @if($kitchenItems->isNotEmpty())
    <div class="header">
        <h1>LIV BEIRA</h1>
        <p>Maquinino,Beira</p>
        <p>Telefone: +258 84 000 000</p>
    </div>
    <hr>
    <div class="content">
        <div class="details">
            <p>Data: {{ $order->created_at->format('d-m-Y H:i') }}</p>
            <p>Pedido Nº: {{ $order->id }}</p>
            <p>Atendente: Nome do Usuário</p>
            <p>Departamento: Cozinha</p>

        </div>
        <table class="items">
            <thead>
                <tr>
                    <th>Qtd</th>
                    <th>Produto</th>
                    {{-- <th>Preço</th>
                    <th>Total</th> --}}
                </tr>
            </thead>
            <tbody>
                @foreach ($kitchenItems as $item)
                <tr>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ $item->product->name }}</td>
                    {{-- <td>{{ number_format($item->price, 2) }}</td>
                    <td>{{ number_format($item->quantity * $item->price, 2) }}</td> --}}
                </tr>
                @endforeach
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
    @if($barItems->isNotEmpty())
    <div class="break-page"></div>
    @endif
@endif
    @if($barItems->isNotEmpty())

        <div class="header">
            <h1>LIV BEIRA</h1>
            <p>Maquinino,Beira</p>
            <p>Telefone: +258 84 000 000</p>
            <p>Departamento: Bar</p>

        </div>
        <hr>
        <div class="content">
            <div class="details">
                <p>Data: {{ $order->created_at->format('d-m-Y H:i') }}</p>
                <p>Pedido Nº: {{ $order->id }}</p>
                <p>Atendente: Nome do Usuário</p>
            </div>
            <table class="items">
                <thead>
                    <tr>
                        <th>Qtd</th>
                        <th>Produto</th>
                        <th>Preço</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($barItems as $item)
                    <tr>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ number_format($item->price, 2) }}</td>
                        <td>{{ number_format($item->quantity * $item->price, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="total">
                <p>Total Geral: MZN {{ number_format($order->total, 2) }}</p>
                <p>Metódo Pagamento: {{ $payment->method->name}}</p>
            </div>
        </div>
        <hr>
        <div class="footer">
            <p>Obrigado pela preferência!</p>
            <p>Visite-nos novamente!</p>
        </div>
    @endif
</body>
</html>
