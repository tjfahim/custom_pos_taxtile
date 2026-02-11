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
        'status',
        'amount_to_collect',
        'paid_amount',
        'due_amount',
        'payment_status',
        'payment_method',
        'payment_details',
        'pathao_city_id',
        'pathao_zone_id',
        'pathao_area_id',
        'notes',
        'created_by'
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
    
      public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateUniqueInvoiceNumber($invoice->status);
            }
        });
    }
    
    public static function generateInvoiceNumber($status = 'confirmed')
    {
        $today = date('Ymd');
        
        // Get the maximum invoice number suffix for today
        $maxSuffix = self::withTrashed()
            ->where('invoice_number', 'like', 'INV-' . $today . '-%')
            ->max(\DB::raw('CAST(SUBSTRING(invoice_number, 14) AS UNSIGNED)'));
        
        // Start from 1 if no invoices exist for today
        $nextSuffix = $maxSuffix ? $maxSuffix + 1 : 1;
        
        return 'INV-' . $today . '-' . str_pad($nextSuffix, 4, '0', STR_PAD_LEFT);
    }
    
    public static function generateUniqueInvoiceNumber($status = 'confirmed')
    {
        do {
            $invoiceNumber = self::generateInvoiceNumber($status);
            // Check if this invoice number already exists
            $exists = self::withTrashed()->where('invoice_number', $invoiceNumber)->exists();
        } while ($exists);
        
        return $invoiceNumber;
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
    
    // Helper method to get the suffix number from invoice number
    public static function extractSuffix($invoiceNumber)
    {
        if (preg_match('/INV-\d{8}-(\d+)/', $invoiceNumber, $matches)) {
            return (int) $matches[1];
        }
        return 0;
    }
}