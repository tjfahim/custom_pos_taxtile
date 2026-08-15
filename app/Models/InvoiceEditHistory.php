<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceEditHistory extends Model
{
    protected $fillable = [
        'invoice_id',
        'user_id',
        'user_name',
        'action_type',
        'old_values',
        'new_values',
        'changed_fields',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'changed_fields' => 'array',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Formatted scalar field changes (recipient name, status, delivery
     * charge, etc). Line-item changes are NOT included here — they're
     * structured differently and are exposed via item_changes /
     * return_item_changes instead.
     */
    public function getFormattedChangesAttribute()
    {
        if (!$this->changed_fields) {
            return [];
        }

        $formatted = [];
        $fieldLabels = [
            'recipient_name' => 'Recipient Name',
            'recipient_phone' => 'Recipient Phone',
            'recipient_secondary_phone' => 'Secondary Phone',
            'recipient_address' => 'Recipient Address',
            'merchant_order_id' => 'Merchant Order ID',
            'delivery_area' => 'Delivery Area',
            'delivery_type' => 'Delivery Type',
            'store_location' => 'Store Location',
            'delivery_charge' => 'Delivery Charge',
            'courier_name' => 'Courier Name',
            'status' => 'Status',
            'payment_method' => 'Payment Method',
            'paid_amount' => 'Paid Amount',
            'amount_to_collect' => 'Amount to Collect',
            'is_wholesale' => 'Is Wholesale',
            'is_inhouse_sale' => 'Is Inhouse Sale',
            'team_id' => 'Team Member',
            'special_instructions' => 'Special Instructions',
            'notes' => 'Notes',
            'subtotal' => 'Subtotal',
            'total' => 'Total',
            'due_amount' => 'Due Amount',
            'payment_status' => 'Payment Status',
        ];

        foreach ($this->changed_fields as $field => $change) {
            // Structured line-item diffs — rendered separately.
            if (in_array($field, ['items_changes', 'return_items_changes'])) {
                continue;
            }

            $label = $fieldLabels[$field] ?? ucwords(str_replace('_', ' ', $field));
            $oldValue = $change['old'] ?? '-';
            $newValue = $change['new'] ?? '-';

            if (in_array($field, ['status', 'payment_status'])) {
                $oldValue = $oldValue ? ucfirst($oldValue) : '-';
                $newValue = $newValue ? ucfirst($newValue) : '-';
            } elseif (in_array($field, ['is_wholesale', 'is_inhouse_sale'])) {
                $oldValue = $oldValue ? 'Yes' : 'No';
                $newValue = $newValue ? 'Yes' : 'No';
            } elseif ($field === 'team_id') {
                $oldValue = $this->getTeamName($oldValue);
                $newValue = $this->getTeamName($newValue);
            }

            $formatted[] = [
                'label' => $label,
                'old' => $oldValue,
                'new' => $newValue,
            ];
        }

        return $formatted;
    }

    /**
     * Structured invoice-item changes for this history row:
     * [ ['type'=>'added'|'removed'|'updated', 'item_name'=>..., ...], ... ]
     */
    public function getItemChangesAttribute()
    {
        return $this->changed_fields['items_changes'] ?? [];
    }

    /**
     * Structured return-item changes for this history row, same shape as
     * item_changes but rows may also include a 'return_reason' field diff.
     */
    public function getReturnItemChangesAttribute()
    {
        return $this->changed_fields['return_items_changes'] ?? [];
    }

    /**
     * True if this row has anything at all to show (scalar field changes,
     * item changes, or return-item changes).
     */
    public function getHasAnyChangesAttribute()
    {
        return count($this->formatted_changes) > 0
            || !empty($this->item_changes)
            || !empty($this->return_item_changes);
    }

    private function getTeamName($userId)
    {
        if (!$userId) return '-';
        $user = \App\Models\User::find($userId);
        return $user ? $user->name : '-';
    }
}