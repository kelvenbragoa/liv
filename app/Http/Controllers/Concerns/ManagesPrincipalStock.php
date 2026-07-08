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
     * Debita stock do centro principal com lock + ledger.
     */
    protected function debitPrincipalStock(
        int $productId,
        int $quantity,
        string $reason = StockMovement::REASON_SALE,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null
    ): void {
        $principalStockCenterId = StockCenter::where('is_principal_stock', 1)->value('id');

        if (!$principalStockCenterId) {
            throw new RuntimeException('Centro de stock principal não encontrado.');
        }

        $this->debitStock(
            (int) $principalStockCenterId,
            $productId,
            $quantity,
            $reason,
            $referenceType,
            $referenceId,
            $notes
        );
    }

    /**
     * Credita stock do centro principal com lock + ledger.
     */
    protected function creditPrincipalStock(
        int $productId,
        int $quantity,
        string $reason = StockMovement::REASON_SALE_CANCEL,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null
    ): void {
        $principalStockCenterId = StockCenter::where('is_principal_stock', 1)->value('id');

        if (!$principalStockCenterId) {
            throw new RuntimeException('Centro de stock principal não encontrado.');
        }

        $this->creditStock(
            (int) $principalStockCenterId,
            $productId,
            $quantity,
            $reason,
            $referenceType,
            $referenceId,
            $notes,
            false
        );
    }

    /**
     * Debita stock de um centro qualquer (saída / transferência origem).
     */
    protected function debitStock(
        int $stockCenterId,
        int $productId,
        int $quantity,
        string $reason,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null
    ): void {
        if ($quantity <= 0) {
            return;
        }

        $stockCenterProduct = $this->lockedStockRow($stockCenterId, $productId, false);

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
            $stockCenterId,
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
     * Credita stock de um centro qualquer (entrada / transferência destino).
     */
    protected function creditStock(
        int $stockCenterId,
        int $productId,
        int $quantity,
        string $reason,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null,
        bool $createIfMissing = true
    ): void {
        if ($quantity <= 0) {
            return;
        }

        $stockCenterProduct = $this->lockedStockRow($stockCenterId, $productId, $createIfMissing);

        $before = (int) $stockCenterProduct->quantity;
        $stockCenterProduct->increment('quantity', $quantity);
        $after = $before + $quantity;

        $this->recordStockMovement(
            $stockCenterId,
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

    /**
     * Define quantidade absoluta (inventário) e regista o delta no ledger.
     */
    protected function setStockAbsolute(
        int $stockCenterId,
        int $productId,
        int $newQuantity,
        string $reason = StockMovement::REASON_INVENTORY,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null
    ): int {
        if ($newQuantity < 0) {
            throw new RuntimeException('A quantidade de inventário não pode ser negativa.');
        }

        $stockCenterProduct = $this->lockedStockRow($stockCenterId, $productId, true);
        $before = (int) $stockCenterProduct->quantity;
        $diff = $newQuantity - $before;

        if ($diff === 0) {
            return $before;
        }

        $stockCenterProduct->update(['quantity' => $newQuantity]);

        $this->recordStockMovement(
            $stockCenterId,
            $productId,
            $diff > 0 ? StockMovement::DIRECTION_IN : StockMovement::DIRECTION_OUT,
            abs($diff),
            $before,
            $newQuantity,
            $reason,
            $referenceType,
            $referenceId,
            $notes
        );

        return $before;
    }

    private function lockedStockRow(int $stockCenterId, int $productId, bool $createIfMissing): StockCenterProduct
    {
        $stockCenterProduct = StockCenterProduct::where('product_id', $productId)
            ->where('stock_center_id', $stockCenterId)
            ->lockForUpdate()
            ->first();

        if ($stockCenterProduct) {
            return $stockCenterProduct;
        }

        if (!$createIfMissing) {
            $productName = Product::find($productId)?->name ?? "ID {$productId}";
            throw new RuntimeException(
                "Produto {$productName} sem registo no stock do centro #{$stockCenterId}."
            );
        }

        // Criar linha e voltar a bloquear (evita corrida em inserts paralelos)
        try {
            StockCenterProduct::create([
                'stock_center_id' => $stockCenterId,
                'product_id' => $productId,
                'quantity' => 0,
            ]);
        } catch (\Throwable $e) {
            // Unique race: continue to lock existing
        }

        $stockCenterProduct = StockCenterProduct::where('product_id', $productId)
            ->where('stock_center_id', $stockCenterId)
            ->lockForUpdate()
            ->first();

        if (!$stockCenterProduct) {
            throw new RuntimeException('Não foi possível criar/obter a linha de stock.');
        }

        return $stockCenterProduct;
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
}
