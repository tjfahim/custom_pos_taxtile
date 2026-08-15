<?php

namespace App\Traits;

use App\Models\InvoiceEditHistory;
use Illuminate\Support\Facades\Auth;

trait TracksInvoiceEdits
{
    protected function trackEdit($invoice, $oldData, $newData, $actionType = 'update')
    {
        $changedFields = [];
        $oldValues = [];
        $newValues = [];

        $trackableFields = [
            'recipient_name', 'recipient_phone', 'recipient_secondary_phone',
            'recipient_address', 'merchant_order_id', 'delivery_area',
            'delivery_type', 'store_location', 'delivery_charge', 'courier_name',
            'status', 'payment_method', 'paid_amount', 'amount_to_collect',
            'is_wholesale', 'is_inhouse_sale', 'team_id', 'special_instructions',
            'notes', 'subtotal', 'total', 'due_amount', 'payment_status',
        ];

        foreach ($trackableFields as $field) {
            $oldValue = $oldData[$field] ?? null;
            $newValue = $newData[$field] ?? null;

            if ($this->valuesDiffer($oldValue, $newValue)) {
                $changedFields[$field] = ['old' => $oldValue, 'new' => $newValue];
                $oldValues[$field] = $oldValue;
                $newValues[$field] = $newValue;
            }
        }

        // Items — diffed by DB id, not name, so duplicate item names and
        // renamed items are both handled correctly.
        $itemChanges = $this->diffLineItems($oldData['items'] ?? [], $newData['items'] ?? []);
        if (!empty($itemChanges)) {
            $changedFields['items_changes'] = $itemChanges;
        }

        // Return items — same diffing, plus return_reason in the comparison.
        $returnItemChanges = $this->diffLineItems($oldData['return_items'] ?? [], $newData['return_items'] ?? [], true);
        if (!empty($returnItemChanges)) {
            $changedFields['return_items_changes'] = $returnItemChanges;
        }

        // Always store a full items/return-items snapshot alongside the diff,
        // so each history row is self-contained even if nothing on that
        // particular row changed.
        $oldValues['items'] = $oldData['items'] ?? [];
        $newValues['items'] = $newData['items'] ?? [];
        $oldValues['return_items'] = $oldData['return_items'] ?? [];
        $newValues['return_items'] = $newData['return_items'] ?? [];

        $hasScalarChanges = !empty(array_diff_key(
            $changedFields,
            array_flip(['items_changes', 'return_items_changes'])
        ));
        $hasLineItemChanges = !empty($itemChanges) || !empty($returnItemChanges);

        // Always log 'create' (there's nothing to diff against the first time,
        // but we still want the initial item list recorded). Otherwise only
        // log when something actually changed.
        if ($actionType === 'create' || $hasScalarChanges || $hasLineItemChanges) {
            InvoiceEditHistory::create([
                'invoice_id' => $invoice->id,
                'user_id' => Auth::id(),
                'user_name' => Auth::user() ? Auth::user()->name : 'System',
                'action_type' => $actionType,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'changed_fields' => $changedFields,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }

        return $changedFields;
    }

    /**
     * Compare two sets of line items (invoice items OR return items) by id
     * and return a structured diff: added / removed / updated rows.
     *
     * @param  array  $oldItems  Each item: ['id'=>, 'item_name'=>, 'description'=>, 'weight'=>, 'quantity'=>, 'unit_price'=>, 'total_price'=>, ('return_reason'=> for return items)]
     * @param  array  $newItems  Same shape.
     * @param  bool   $isReturnItem  Whether to also compare return_reason.
     */
    protected function diffLineItems(array $oldItems, array $newItems, bool $isReturnItem = false)
    {
        $changes = [];

        $oldById = collect($oldItems)->keyBy('id');
        $newById = collect($newItems)->keyBy('id');

        $compareFields = ['item_name', 'description', 'weight', 'quantity', 'unit_price'];
        if ($isReturnItem) {
            $compareFields[] = 'return_reason';
        }

        // Added: present in new, not in old
        foreach ($newById as $id => $item) {
            if (!$oldById->has($id)) {
                $changes[] = [
                    'type' => 'added',
                    'item_name' => $item['item_name'] ?? 'Unnamed item',
                    'quantity' => $item['quantity'] ?? null,
                    'unit_price' => $item['unit_price'] ?? null,
                    'total_price' => $item['total_price'] ?? null,
                ];
            }
        }

        // Removed: present in old, not in new
        foreach ($oldById as $id => $item) {
            if (!$newById->has($id)) {
                $changes[] = [
                    'type' => 'removed',
                    'item_name' => $item['item_name'] ?? 'Unnamed item',
                    'quantity' => $item['quantity'] ?? null,
                    'unit_price' => $item['unit_price'] ?? null,
                    'total_price' => $item['total_price'] ?? null,
                ];
            }
        }

        // Updated: present in both, compare field-by-field
        foreach ($oldById as $id => $oldItem) {
            if (!$newById->has($id)) {
                continue;
            }
            $newItem = $newById->get($id);
            $fieldDiffs = [];

            foreach ($compareFields as $field) {
                $oldVal = $oldItem[$field] ?? null;
                $newVal = $newItem[$field] ?? null;
                if ($this->valuesDiffer($oldVal, $newVal)) {
                    $fieldDiffs[$field] = ['old' => $oldVal, 'new' => $newVal];
                }
            }

            if (!empty($fieldDiffs)) {
                $changes[] = [
                    'type' => 'updated',
                    'item_name' => $newItem['item_name'] ?? ($oldItem['item_name'] ?? 'Unnamed item'),
                    'changes' => $fieldDiffs,
                ];
            }
        }

        return $changes;
    }

    protected function valuesDiffer($old, $new)
    {
        if (is_numeric($old) && is_numeric($new)) {
            return (float) $old != (float) $new;
        }
        if (is_bool($old) || is_bool($new)) {
            return (bool) $old !== (bool) $new;
        }
        if (is_null($old) && is_null($new)) {
            return false;
        }
        if (is_null($old) xor is_null($new)) {
            return true;
        }
        return (string) $old !== (string) $new;
    }

    /**
     * Snapshot of everything worth tracking on an invoice, including its
     * current items and return items (each keyed by DB id so diffLineItems
     * can match rows across old/new snapshots).
     */
    protected function getInvoiceDataForTracking($invoice)
    {
        return [
            'recipient_name' => $invoice->recipient_name,
            'recipient_phone' => $invoice->recipient_phone,
            'recipient_secondary_phone' => $invoice->recipient_secondary_phone,
            'recipient_address' => $invoice->recipient_address,
            'merchant_order_id' => $invoice->merchant_order_id,
            'delivery_area' => $invoice->delivery_area,
            'delivery_type' => $invoice->delivery_type,
            'store_location' => $invoice->store_location,
            'delivery_charge' => $invoice->delivery_charge,
            'courier_name' => $invoice->courier_name,
            'status' => $invoice->status,
            'payment_method' => $invoice->payment_method,
            'paid_amount' => $invoice->paid_amount,
            'amount_to_collect' => $invoice->amount_to_collect,
            'is_wholesale' => $invoice->is_wholesale,
            'is_inhouse_sale' => $invoice->is_inhouse_sale,
            'team_id' => $invoice->team_id,
            'special_instructions' => $invoice->special_instructions,
            'notes' => $invoice->notes,
            'subtotal' => $invoice->subtotal,
            'total' => $invoice->total,
            'due_amount' => $invoice->due_amount,
            'payment_status' => $invoice->payment_status,
            'items' => $invoice->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item_name' => $item->item_name,
                    'description' => $item->description,
                    'weight' => $item->weight,
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'total_price' => (float) $item->total_price,
                ];
            })->toArray(),
            'return_items' => $invoice->returnItems->map(function ($ri) {
                return [
                    'id' => $ri->id,
                    'item_name' => $ri->item_name,
                    'description' => $ri->description,
                    'weight' => $ri->weight,
                    'quantity' => $ri->quantity,
                    'unit_price' => (float) $ri->unit_price,
                    'total_price' => (float) $ri->total_price,
                    'return_reason' => $ri->return_reason,
                ];
            })->toArray(),
        ];
    }
}