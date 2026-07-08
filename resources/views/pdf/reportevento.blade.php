<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Relatório Evento / Promotor</title>
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
        .details {
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
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .section-note { font-size: 10px; }
        .total-row th,
        .total-row td {
            font-weight: bold;
            background-color: #f3f4f6;
        }
        .summary-highlight {
            background-color: #ecfdf5;
            font-weight: bold;
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
            <h3>Relatório Evento / Promotor</h3>
            <small class="section-note">Data: {{ $date }} | Relatório partilhável (sem custos internos da casa)</small>

            <h3>Resumo do Evento</h3>
            <table class="details">
                <tr>
                    <th>Descrição</th>
                    <th class="text-right">Valor</th>
                </tr>
                <tr>
                    <td>Consumo total (operação)</td>
                    <td class="text-right">{{ number_format($totalSales, 2, ',', '.') }} MT</td>
                </tr>
                <tr>
                    <td>(-) Consumo interno</td>
                    <td class="text-right">{{ number_format($totalInternalConsumption, 2, ',', '.') }} MT</td>
                </tr>
                <tr class="summary-highlight">
                    <td>Base de partilha (consumo − interno)</td>
                    <td class="text-right">{{ number_format($shareBase, 2, ',', '.') }} MT</td>
                </tr>
                <tr>
                    <td>Receita já cobrada (pagamentos)</td>
                    <td class="text-right">{{ number_format($totalRevenuePayments, 2, ',', '.') }} MT</td>
                </tr>
                <tr>
                    <td>Crédito liquidado no dia</td>
                    <td class="text-right">{{ number_format($totalCreditSettled, 2, ',', '.') }} MT</td>
                </tr>
                <tr class="summary-highlight">
                    <td>Receita real (cash)</td>
                    <td class="text-right">{{ number_format($totalRevenue, 2, ',', '.') }} MT</td>
                </tr>
                <tr>
                    <td>Crédito emitido no dia</td>
                    <td class="text-right">{{ number_format($totalCreditIssued, 2, ',', '.') }} MT</td>
                </tr>
                <tr>
                    <td>Crédito em aberto (saldo)</td>
                    <td class="text-right">{{ number_format($totalCreditOpenBalance, 2, ',', '.') }} MT</td>
                </tr>
            </table>
            <small class="section-note">
                Nota: A percentagem casa/promotor aplica-se tipicamente sobre a Base de Partilha.
                Créditos em aberto constituem dívida e entram no acerto conforme acordo.
            </small>

            <h3>Vendas por Produto — Bar (apenas pagos)</h3>
            <table class="details compact">
                <tr>
                    <th>Produto</th>
                    <th class="text-center">Qtd. Vend.</th>
                    <th class="text-right">P. Venda</th>
                    <th class="text-right">V. Total</th>
                </tr>
                @php
                    $bar_qty_total = 0;
                    $bar_val_total = 0;
                @endphp
                @foreach ($productsBar as $item)
                    <tr>
                        <td>{{ $item->product->name ?? 'Desconhecido' }}</td>
                        <td class="text-center">{{ $item->total_quantity }}</td>
                        <td class="text-right">{{ number_format($item->product->price ?? 0, 2, ',', '.') }} MT</td>
                        <td class="text-right">{{ number_format($item->total_value, 2, ',', '.') }} MT</td>
                    </tr>
                    @php
                        $bar_qty_total += $item->total_quantity;
                        $bar_val_total += $item->total_value;
                    @endphp
                @endforeach
                <tr class="total-row">
                    <th>Total Bar</th>
                    <td class="text-center">{{ $bar_qty_total }}</td>
                    <td></td>
                    <td class="text-right">{{ number_format($bar_val_total, 2, ',', '.') }} MT</td>
                </tr>
            </table>

            <h3>Vendas por Produto — Cozinha (apenas pagos)</h3>
            <table class="details compact">
                <tr>
                    <th>Produto</th>
                    <th class="text-center">Qtd. Vend.</th>
                    <th class="text-right">P. Venda</th>
                    <th class="text-right">V. Total</th>
                </tr>
                @php
                    $kit_qty_total = 0;
                    $kit_val_total = 0;
                @endphp
                @foreach ($productsKitchen as $item)
                    <tr>
                        <td>{{ $item->product->name ?? 'Desconhecido' }}</td>
                        <td class="text-center">{{ $item->total_quantity }}</td>
                        <td class="text-right">{{ number_format($item->product->price ?? 0, 2, ',', '.') }} MT</td>
                        <td class="text-right">{{ number_format($item->total_value, 2, ',', '.') }} MT</td>
                    </tr>
                    @php
                        $kit_qty_total += $item->total_quantity;
                        $kit_val_total += $item->total_value;
                    @endphp
                @endforeach
                <tr class="total-row">
                    <th>Total Cozinha</th>
                    <td class="text-center">{{ $kit_qty_total }}</td>
                    <td></td>
                    <td class="text-right">{{ number_format($kit_val_total, 2, ',', '.') }} MT</td>
                </tr>
            </table>

            <h3>Créditos do Dia</h3>
            <table class="details compact">
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Pedido / Mesa</th>
                    <th class="text-right">Total</th>
                    <th class="text-right">Liquidado</th>
                    <th class="text-right">Saldo</th>
                    <th>Data</th>
                </tr>
                @forelse ($creditsReport as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->customer->name ?? '—' }}</td>
                        <td>{{ $item->order->table->name ?? ('Pedido #' . $item->order_id) }}</td>
                        <td class="text-right">{{ number_format($item->amount, 2, ',', '.') }} MT</td>
                        <td class="text-right">{{ number_format($item->amount_settled, 2, ',', '.') }} MT</td>
                        <td class="text-right">{{ number_format($item->amount_balance, 2, ',', '.') }} MT</td>
                        <td>{{ $item->created_at }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Nenhum crédito registado neste dia.</td>
                    </tr>
                @endforelse
            </table>
        </div>
    </div>
    <br><br>
    <div class="footer">
        &copy; {{ date('Y') }} LIV Beira. Todos os direitos reservados. | Beira, Mozambique
    </div>
</body>
</html>
