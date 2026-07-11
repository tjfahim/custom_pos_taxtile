<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryCharge extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_range',
        'to_range',
        'inside_dhaka_price',
        'outside_dhaka_price'
    ];

    protected $casts = [
        'from_range' => 'integer',
        'to_range' => 'integer',
        'inside_dhaka_price' => 'integer',
        'outside_dhaka_price' => 'integer',
    ];

    /**
     * Get delivery charge based on distance and location
     */
    public static function getCharge($distance, $isInsideDhaka = true)
    {
        $query = self::where('from_range', '<=', $distance);
        
        // Check if distance is within range
        $query->where(function($q) use ($distance) {
            $q->where('to_range', '>=', $distance)
              ->orWhereNull('to_range');
        });
        
        $charge = $query->orderBy('from_range', 'asc')->first();
        
        if (!$charge) {
            return 0;
        }
        
        return $isInsideDhaka ? $charge->inside_dhaka_price : $charge->outside_dhaka_price;
    }

    /**
     * Get all active delivery charges sorted by range
     */
    public static function getActiveCharges()
    {
        return self::orderBy('from_range', 'asc')->get();
    }

    /**
     * Check if a distance falls within this range
     */
    public function isInRange($distance)
    {
        if ($distance < $this->from_range) {
            return false;
        }
        
        if ($this->to_range !== null && $distance > $this->to_range) {
            return false;
        }
        
        return true;
    }

    /**
     * Get formatted range display (integer values only)
     */
    public function getRangeDisplayAttribute()
    {
        $from = intval($this->from_range);
        $to = $this->to_range !== null ? intval($this->to_range) : null;
        
        if ($to === null) {
            return $from . '+';
        }
        return $from . ' - ' . $to;
    }

    /**
     * Get inside dhaka price as integer
     */
    public function getInsideDhakaPriceIntegerAttribute()
    {
        return intval($this->inside_dhaka_price);
    }

    /**
     * Get outside dhaka price as integer
     */
    public function getOutsideDhakaPriceIntegerAttribute()
    {
        return intval($this->outside_dhaka_price);
    }
}