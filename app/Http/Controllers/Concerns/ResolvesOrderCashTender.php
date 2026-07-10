<?php

namespace App\Http\Controllers\Concerns;

use RuntimeException;

trait ResolvesOrderCashTender
{
    protected function resolveOrderCashTender(array $data, float $total, bool $isCredit): array
    {
        if ($isCredit) {
            return [
                'amount_tendered' => null,
                'change_amount' => null,
            ];
        }

        if (! isset($data['amount_tendered']) || $data['amount_tendered'] === '' || $data['amount_tendered'] === null) {
            return [
                'amount_tendered' => null,
                'change_amount' => null,
            ];
        }

        $amountTendered = round((float) $data['amount_tendered'], 2);

        if ($amountTendered < $total) {
            throw new RuntimeException('O valor entregue deve ser maior ou igual ao total do pedido.');
        }

        return [
            'amount_tendered' => $amountTendered,
            'change_amount' => round($amountTendered - $total, 2),
        ];
    }
}
