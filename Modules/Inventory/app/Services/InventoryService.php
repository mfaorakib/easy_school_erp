<?php

namespace Modules\Inventory\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Services\AccountingService;
use Modules\Foundation\Services\SettingService;
use Modules\Foundation\Support\IdPattern;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\ItemIssue;
use Modules\Inventory\Models\ItemReceive;
use Modules\Inventory\Models\ItemSell;
use RuntimeException;

/**
 * Inventory stock movements. Stock is derived from the ledger
 * (Σ received − Σ issued-not-returned); receive adds, issue removes.
 */
class InventoryService
{
    private const DEFAULT_PURCHASE_VOUCHER_FORMAT = 'PUR-{YYYY}-{SEQ:5}';
    private const DEFAULT_SALE_VOUCHER_FORMAT = 'SALE-{YYYY}-{SEQ:5}';

    public function __construct(
        private readonly AccountingService $accounting,
        private readonly SettingService $settings,
    ) {}

    public function receive(array $data): ItemReceive
    {
        $receive = ItemReceive::create([
            'item_id'       => $data['item_id'],
            'item_store_id' => $data['item_store_id'] ?? null,
            'supplier_id'   => $data['supplier_id'] ?? null,
            'quantity'      => $data['quantity'],
            'unit_price'    => $data['unit_price'] ?? 0,
            'received_on'   => $data['received_on'] ?? now()->toDateString(),
            'note'          => $data['note'] ?? null,
            'received_by'   => Auth::id(),
            'voucher_no'    => $this->generateVoucherNo('purchase'),
        ]);

        $this->accounting->postFromSource(
            'inventory_receive',
            $receive->id,
            'expense',
            'Supplies',
            (float) $receive->quantity * (float) $receive->unit_price,
            $receive->received_on,
            'Inventory purchase — item #'.$receive->item_id,
        );

        return $receive;
    }

    /**
     * Return part or all of an already-received purchase to the supplier —
     * reduces the item's net-received stock and posts a linked accounting
     * adjustment (an expense reduction) against the original purchase entry.
     * Never edits the original ItemReceive row itself, only its running
     * returned_quantity, so the original purchase stays exactly as it happened.
     */
    public function adjustReceive(ItemReceive $receive, int $returnQty, string $reason): void
    {
        $available = (int) $receive->quantity - (int) $receive->returned_quantity;
        if ($returnQty <= 0 || $returnQty > $available) {
            throw new RuntimeException("Invalid return quantity (available to return: {$available}).");
        }

        DB::transaction(function () use ($receive, $returnQty, $reason) {
            $receive->increment('returned_quantity', $returnQty);

            $original = $this->accounting->transactionFor('inventory_receive', $receive->id);
            if ($original) {
                $this->accounting->postAdjustment(
                    $original,
                    $returnQty * (float) $receive->unit_price,
                    $reason,
                );
            }
        });
    }

    public function issue(array $data): ItemIssue
    {
        $item = Item::findOrFail($data['item_id']);

        if ($item->stock < (int) $data['quantity']) {
            throw new RuntimeException("Not enough stock (available: {$item->stock}).");
        }

        return ItemIssue::create([
            'item_id'       => $item->id,
            'item_store_id' => $data['item_store_id'] ?? null,
            'issued_to'     => $data['issued_to'] ?? null,
            'quantity'      => $data['quantity'],
            'issue_date'    => $data['issue_date'] ?? now()->toDateString(),
            'is_returned'   => false,
            'note'          => $data['note'] ?? null,
            'issued_by'     => Auth::id(),
        ]);
    }

    public function sell(array $data): ItemSell
    {
        $item = Item::findOrFail($data['item_id']);

        if ($item->stock < (int) $data['quantity']) {
            throw new RuntimeException("Not enough stock (available: {$item->stock}).");
        }

        $sell = ItemSell::create([
            'item_id'       => $item->id,
            'item_store_id' => $data['item_store_id'] ?? null,
            'sold_to'       => $data['sold_to'] ?? null,
            'buyer_name'    => $data['buyer_name'] ?? null,
            'buyer_contact' => $data['buyer_contact'] ?? null,
            'quantity'      => $data['quantity'],
            'unit_price'    => $data['unit_price'] ?? 0,
            'sold_on'       => $data['sold_on'] ?? now()->toDateString(),
            'note'          => $data['note'] ?? null,
            'sold_by'       => Auth::id(),
            'voucher_no'    => $this->generateVoucherNo('sale'),
        ]);

        $this->accounting->postFromSource(
            'inventory_sell',
            $sell->id,
            'income',
            'Inventory Sales',
            (float) $sell->quantity * (float) $sell->unit_price,
            $sell->sold_on,
            'Inventory sale — item #'.$sell->item_id,
        );

        return $sell;
    }

    /**
     * Return part or all of an already-sold item from the buyer — increases
     * the item's available stock back and posts a linked accounting
     * adjustment (an income reduction) against the original sale entry.
     */
    public function adjustSell(ItemSell $sell, int $returnQty, string $reason): void
    {
        $available = (int) $sell->quantity - (int) $sell->returned_quantity;
        if ($returnQty <= 0 || $returnQty > $available) {
            throw new RuntimeException("Invalid return quantity (available to return: {$available}).");
        }

        DB::transaction(function () use ($sell, $returnQty, $reason) {
            $sell->increment('returned_quantity', $returnQty);

            $original = $this->accounting->transactionFor('inventory_sell', $sell->id);
            if ($original) {
                $this->accounting->postAdjustment(
                    $original,
                    $returnQty * (float) $sell->unit_price,
                    $reason,
                );
            }
        });
    }

    public function returnIssue(ItemIssue $issue): void
    {
        if ($issue->is_returned) {
            return;
        }

        DB::transaction(fn () => $issue->update([
            'is_returned' => true,
            'return_date' => now()->toDateString(),
        ]));
    }

    /** The next formatted voucher number for a purchase or a sale, per an admin-configurable pattern (same IdPattern used for fee receipts). */
    private function generateVoucherNo(string $kind): string
    {
        if ($kind === 'purchase') {
            $pattern = $this->settings->get('inventory.purchase_voucher_format', self::DEFAULT_PURCHASE_VOUCHER_FORMAT);
            $seq     = ItemReceive::withTrashed()->whereNotNull('voucher_no')->count() + 1;
        } else {
            $pattern = $this->settings->get('inventory.sale_voucher_format', self::DEFAULT_SALE_VOUCHER_FORMAT);
            $seq     = ItemSell::withTrashed()->whereNotNull('voucher_no')->count() + 1;
        }

        return IdPattern::format($pattern, (int) date('Y'), $seq);
    }
}
