<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'invoice_id',
        'item_name',
        'description',
        'quantity',
        'weight',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'weight' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Get the invoice that owns the item.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    
    /**
     * Get formatted unit price
     */
    public function getFormattedUnitPriceAttribute()
    {
        return number_format($this->unit_price, 2);
    }

    /**
     * Get formatted total price
     */
    public function getFormattedTotalPriceAttribute()
    {
        return number_format($this->total_price, 2);
    }
}
