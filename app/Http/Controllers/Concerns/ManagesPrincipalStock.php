<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Product;
use App\Models\StockCenter;
use App\Models\StockCenterProduct;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

trait ManagesPrincipalStock
{
    /**
     * Debita stock do centro principal com lock + ledger. Falha se não existir linha ou stock insuficiente.
     */
    protected function debitPrincipalStock(
        int $productId,
        int $quantity,
        string $reason = StockMovement::REASON_SALE,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null
    ): void {
        $stockCenterProduct = $this->lockedPrincipalStockRow($productId);

        if ($stockCenterProduct->quantity < $quantity) {
            $productName = Product::find($productId)?->name ?? "ID {$productId}";
            throw new RuntimeException(
                "A quantidade de {$productName} não é suficiente. Atualmente tem {$stockCenterProduct->quantity} unidades em estoque."
            );
        }

        $before = (int) $stockCenterProduct->quantity;
        $stockCenterProduct->decrement('quantity', $quantity);
        $after = $before - $quantity;

        $this->recordStockMovement(
            (int) $stockCenterProduct->stock_center_id,
            $productId,
            StockMovement::DIRECTION_OUT,
            $quantity,
            $before,
            $after,
            $reason,
            $referenceType,
            $referenceId,
            $notes
        );
    }

    /**
     * Credita stock do centro principal com lock + ledger (ex.: cancelamento de item).
     */
    protected function creditPrincipalStock(
        int $productId,
        int $quantity,
        string $reason = StockMovement::REASON_SALE_CANCEL,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null
    ): void {
        $stockCenterProduct = $this->lockedPrincipalStockRow($productId);

        $before = (int) $stockCenterProduct->quantity;
        $stockCenterProduct->increment('quantity', $quantity);
        $after = $before + $quantity;

        $this->recordStockMovement(
            (int) $stockCenterProduct->stock_center_id,
            $productId,
            StockMovement::DIRECTION_IN,
            $quantity,
            $before,
            $after,
            $reason,
            $referenceType,
            $referenceId,
            $notes
        );
    }

    private function recordStockMovement(
        int $stockCenterId,
        int $productId,
        string $direction,
        int $quantity,
        int $before,
        int $after,
        string $reason,
        ?string $referenceType,
        ?int $referenceId,
        ?string $notes
    ): void {
        StockMovement::create([
            'stock_center_id' => $stockCenterId,
            'product_id' => $productId,
            'direction' => $direction,
            'quantity' => $quantity,
            'quantity_before' => $before,
            'quantity_after' => $after,
            'reason' => $reason,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'user_id' => Auth::id(),
            'notes' => $notes,
        ]);
    }

    private function lockedPrincipalStockRow(int $productId): StockCenterProduct
    {
        $principalStockCenterId = StockCenter::where('is_principal_stock', 1)->value('id');

        if (!$principalStockCenterId) {
            throw new RuntimeException('Centro de stock principal não encontrado.');
        }

        $stockCenterProduct = StockCenterProduct::where('product_id', $productId)
            ->where('stock_center_id', $principalStockCenterId)
            ->lockForUpdate()
            ->first();

        if (!$stockCenterProduct) {
            $productName = Product::find($productId)?->name ?? "ID {$productId}";
            throw new RuntimeException(
                "Produto {$productName} sem registo no stock principal. Não é possível continuar."
            );
        }

        return $stockCenterProduct;
    }
}
