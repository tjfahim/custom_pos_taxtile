<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsideDhaka extends Model
{
    use HasFactory;

    protected $table = 'inside_dhaka';

    protected $fillable = [
        'zone_id',
        'is_active'
    ];

    protected $casts = [
        'zone_id' => 'string',
        'is_active' => 'boolean',
    ];

    /**
     * Get the zone that belongs to this inside dhaka entry
     */
    public function zone()
    {
        return $this->belongsTo(PathaoZone::class, 'zone_id', 'zone_id');
    }

    /**
     * Get the city through zone
     */
    public function city()
    {
        return $this->hasOneThrough(
            PathaoCity::class,
            PathaoZone::class,
            'zone_id', // Foreign key on pathao_zones table
            'city_id', // Foreign key on pathao_cities table
            'zone_id', // Local key on inside_dhaka table
            'city_id'  // Local key on pathao_zones table
        );
    }

    /**
     * Get all active inside dhaka zones
     */
    public static function getActiveZones()
    {
        return self::with('zone')
            ->where('is_active', true)
            ->get();
    }

    /**
     * Check if a zone is inside Dhaka
     */
    public static function isInsideDhaka($zoneId)
    {
        return self::where('zone_id', $zoneId)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Get inside dhaka zones by city
     */
    public static function getZonesByCity($cityId)
    {
        return self::with('zone')
            ->whereHas('zone', function($query) use ($cityId) {
                $query->where('city_id', $cityId);
            })
            ->where('is_active', true)
            ->get();
    }

    /**
     * Toggle status
     */
    public function toggleStatus()
    {
        $this->is_active = !$this->is_active;
        $this->save();
        return $this;
    }

    /**
     * Get zone name with city for display
     */
    public function getDisplayNameAttribute()
    {
        $zone = $this->zone;
        if ($zone && $zone->city) {
            return $zone->city->city_name . ' → ' . $zone->zone_name;
        }
        return $zone->zone_name ?? 'Unknown Zone';
    }

      public static function getDeliveryChargeByZone($zoneId, $totalQuantity = 1)
    {
        // Check if zone is inside Dhaka
        $isInsideDhaka = self::isInsideDhaka($zoneId);
        
        // Get delivery charge based on total quantity
        $deliveryCharge = DeliveryCharge::getCharge($totalQuantity, $isInsideDhaka);
        
        // Get zone details
        $zone = PathaoZone::where('zone_id', $zoneId)->with('city')->first();
        
        return [
            'success' => true,
            'data' => [
                'zone_id' => $zoneId,
                'zone_name' => $zone->zone_name ?? 'Unknown',
                'city_id' => $zone->city_id ?? null,
                'city_name' => $zone->city->city_name ?? 'Unknown',
                'total_quantity' => $totalQuantity,
                'is_inside_dhaka' => $isInsideDhaka,
                'delivery_charge' => $deliveryCharge,
                'price_type' => $isInsideDhaka ? 'Inside Dhaka' : 'Outside Dhaka',
                'currency' => 'BDT',
                'formatted' => '৳' . number_format($deliveryCharge, 0)
            ]
        ];
    }
}