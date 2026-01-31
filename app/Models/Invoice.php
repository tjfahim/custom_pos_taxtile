<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'store_location',
        'product_type',
        'merchant_order_id',
        'customer_id',
        'recipient_name',
        'recipient_phone',
        'recipient_secondary_phone',
        'recipient_address',
        'delivery_area',
        'delivery_type',
        'delivery_charge',
        'total_weight',
        'special_instructions',
        'invoice_date',
        'subtotal',
        'total',
        'amount_to_collect',
        'paid_amount',
        'due_amount',
        'payment_status',
        'payment_method',
        'payment_details',
        'notes'
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'total_weight' => 'decimal:2',
        'amount_to_collect' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2'
    ];
protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = 'INV-' . date('Ymd') . '-' . str_pad(Invoice::withTrashed()->count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

      public function calculateTotals()
{
    $subtotal = $this->items->sum('total_price');
    $this->subtotal = $subtotal;
    $this->total = $subtotal + $this->delivery_charge;
    $this->total_weight = $this->items->sum('weight');
    
    // Update due amount
    $this->due_amount = $this->total - $this->paid_amount;
    
    // Update payment status
    if ($this->due_amount <= 0) {
        $this->payment_status = 'paid';
    } elseif ($this->paid_amount > 0) {
        $this->payment_status = 'partial';
    } else {
        $this->payment_status = 'unpaid';
    }
    
    $this->save();
}
}
