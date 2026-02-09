<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'name',
        'full_address',
        'phone_number_1',
        'phone_number_2',
        'merchant_order_id',
        'delivery_area',
        'note',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

     public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
    
    /**
     * Get active status badge
     */
    public function getStatusBadgeAttribute()
    {
        return $this->status ? 
            '<span class="badge bg-success">Active</span>' : 
            '<span class="badge bg-danger">Inactive</span>';
    }
    
    /**
     * Get formatted phone numbers
     */
    public function getFormattedPhonesAttribute()
    {
        $phones = [];
        if ($this->phone_number_1) {
            $phones[] = $this->phone_number_1;
        }
        if ($this->phone_number_2) {
            $phones[] = $this->phone_number_2;
        }
        return implode(' / ', $phones);
    }
}
