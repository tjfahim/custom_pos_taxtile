<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PathaoArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'area_id',
        'area_name',
        'zone_id',
        'home_delivery_available',
        'pickup_available'
    ];

    protected $casts = [
        'area_id' => 'string',
        'zone_id' => 'string',
        'home_delivery_available' => 'boolean',
        'pickup_available' => 'boolean',
    ];

    public function zone()
    {
        return $this->belongsTo(PathaoZone::class, 'zone_id', 'zone_id');
    }
}