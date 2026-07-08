<?php

namespace App\Console\Commands;

use App\Models\StockCenter;
use App\Models\StockCenterProduct;
use App\Models\StockMovement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReconcileStockCommand extends Command
{
    protected $signature = 'stock:reconcile
                            {--center= : ID do centro de stock (default: principal)}
                            {--seed-opening : Cria movimentos de saldo inicial para produtos sem ledger}
                            {--only-diff : Mostra apenas divergências}';

    protected $description = 'Compara stock_center_products com o ledger stock_movements';

    public function handle(): int
    {
        $centerId = $this->option('center')
            ? (int) $this->option('center')
            : StockCenter::where('is_principal_stock', 1)->value('id');

        if (!$centerId) {
            $this->error('Centro de stock não encontrado.');
            return self::FAILURE;
        }

        $center = StockCenter::find($centerId);
        $this->info("Reconciliação — centro #{$centerId} ({$center?->name})");

        if ($this->option('seed-opening')) {
            $seeded = $this->seedOpeningBalances($centerId);
            $this->info("Saldos iniciais criados/actualizados: {$seeded}");
        }

        $rows = $this->buildDiffRows($centerId);
        $diffs = array_filter($rows, fn ($r) => $r['diff'] !== 0);

        if ($this->option('only-diff')) {
            $rows = array_values($diffs);
        }

        if (empty($rows)) {
            $this->info('Sem linhas para mostrar.');
            return self::SUCCESS;
        }

        $this->table(
            ['product_id', 'product', 'on_hand', 'ledger', 'diff'],
            array_map(fn ($r) => [
                $r['product_id'],
                $r['product_name'],
                $r['on_hand'],
                $r['ledger'],
                $r['diff'],
            ], $rows)
        );

        $this->info('Total produtos: ' . count($rows) . ' | Divergências: ' . count($diffs));

        return empty($diffs) ? self::SUCCESS : self::FAILURE;
    }

    private function seedOpeningBalances(int $centerId): int
    {
        $count = 0;

        StockCenterProduct::where('stock_center_id', $centerId)
            ->orderBy('id')
            ->chunkById(200, function ($products) use ($centerId, &$count) {
                foreach ($products as $row) {
                    $hasLedger = StockMovement::where('stock_center_id', $centerId)
                        ->where('product_id', $row->product_id)
                        ->exists();

                    if ($hasLedger) {
                        continue;
                    }

                    $qty = (int) $row->quantity;

                    StockMovement::create([
                        'stock_center_id' => $centerId,
                        'product_id' => $row->product_id,
                        'direction' => StockMovement::DIRECTION_IN,
                        'quantity' => $qty,
                        'quantity_before' => 0,
                        'quantity_after' => $qty,
                        'reason' => StockMovement::REASON_ADJUSTMENT,
                        'reference_type' => null,
                        'reference_id' => null,
                        'user_id' => null,
                        'notes' => 'Saldo inicial (seed opening balance)',
                    ]);

                    $count++;
                }
            });

        return $count;
    }

    /**
     * @return array<int, array{product_id:int,product_name:?string,on_hand:int,ledger:int,diff:int}>
     */
    private function buildDiffRows(int $centerId): array
    {
        $ledger = DB::table('stock_movements')
            ->selectRaw("
                product_id,
                SUM(CASE WHEN direction = 'in' THEN quantity ELSE 0 END) -
                SUM(CASE WHEN direction = 'out' THEN quantity ELSE 0 END) as ledger_qty
            ")
            ->where('stock_center_id', $centerId)
            ->groupBy('product_id')
            ->pluck('ledger_qty', 'product_id');

        $rows = [];

        StockCenterProduct::with('product')
            ->where('stock_center_id', $centerId)
            ->orderBy('product_id')
            ->chunkById(200, function ($products) use ($ledger, &$rows) {
                foreach ($products as $row) {
                    $onHand = (int) $row->quantity;
                    $ledgerQty = (int) ($ledger[$row->product_id] ?? 0);
                    $diff = $onHand - $ledgerQty;

                    $rows[] = [
                        'product_id' => (int) $row->product_id,
                        'product_name' => $row->product?->name,
                        'on_hand' => $onHand,
                        'ledger' => $ledgerQty,
                        'diff' => $diff,
                    ];
                }
            });

        return $rows;
    }
}
