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
        'payment_date',
'paid_amount2',
'payment_method2',
'payment_details2',
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
        'created_by',
        'has_return_items',
        'is_wholesale',     
        'is_inhouse_sale',  
        'courier_name',     
        'confirmed_at',
        'team_id' // Add this field
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'confirmed_at' => 'date',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'total_weight' => 'decimal:2',
        'amount_to_collect' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'is_wholesale' => 'boolean',
        'is_inhouse_sale' => 'boolean',
        'has_return_items' => 'boolean',
        'payment_date' => 'datetime',
        'paid_amount2' => 'decimal:2',
    ];
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    // Add relationship to team member
    public function teamMember()
    {
        return $this->belongsTo(User::class, 'team_id');
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

    public function editHistories()
{
    return $this->hasMany(InvoiceEditHistory::class)->orderBy('created_at', 'desc');
}

public function latestEditHistory()
{
    return $this->hasOne(InvoiceEditHistory::class)->latest();
}


// Get items with their changes in history
public function getItemsWithHistory()
{
    return $this->items;
}

// Get return items with their changes in history
public function getReturnItemsWithHistory()
{
    return $this->returnItems;
}
    public static function generateInvoiceNumber($status = 'confirmed')
    {
        $today = date('Ymd');
        
        $maxSuffix = self::withTrashed()
            ->where('invoice_number', 'like', 'INV-' . $today . '-%')
            ->max(\DB::raw('CAST(SUBSTRING(invoice_number, 14) AS UNSIGNED)'));
        
        $nextSuffix = $maxSuffix ? $maxSuffix + 1 : 1;
        
        return 'INV-' . $today . '-' . str_pad($nextSuffix, 4, '0', STR_PAD_LEFT);
    }
    
    public static function generateUniqueInvoiceNumber($status = 'confirmed')
    {
        do {
            $invoiceNumber = self::generateInvoiceNumber($status);
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
    
    public function returnItems()
    {
        return $this->hasMany(ReturnItem::class);
    }
    
    /**
     * Calculate totals including return items deduction
     */
    public function calculateTotals()
    {
        // Calculate subtotal from items
        $subtotal = $this->items->sum('total_price');
        
        // Calculate return items total
        $returnTotal = $this->returnItems->sum('total_price');
        
        // Subtract return items from subtotal
        $subtotalAfterReturn = $subtotal - $returnTotal;
        
        // Set subtotal (after returns)
        $this->subtotal = $subtotalAfterReturn;
        
        // Calculate total (subtotal + delivery)
        $this->total = $subtotalAfterReturn + $this->delivery_charge;
        
        // Calculate total weight from items only (returns don't affect weight)
        $this->total_weight = $this->items->sum('weight');
            $totalPaid = (float) $this->paid_amount + (float) ($this->paid_amount2 ?? 0);

        // Update due amount
    $this->due_amount = $this->total - $totalPaid;
        
        // Update payment status
        if ($this->due_amount <= 0) {
            $this->payment_status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->payment_status = 'partial';
        } else {
            $this->payment_status = 'unpaid';
        }
        
        $this->save();
        
        return $this;
    }
    
    /**
     * Get original subtotal before return deduction
     */
    public function getOriginalSubtotalAttribute()
    {
        return $this->items->sum('total_price');
    }
    
    /**
     * Get return items total
     */
    public function getReturnTotalAttribute()
    {
        return $this->returnItems->sum('total_price');
    }
    
    /**
     * Get formatted subtotal display
     */
    public function getFormattedSubtotalAttribute()
    {
        return number_format($this->subtotal, 0);
    }
    
    /**
     * Get formatted total display
     */
    public function getFormattedTotalAttribute()
    {
        return number_format($this->total, 0);
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