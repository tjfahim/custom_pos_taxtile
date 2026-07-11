<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PathaoZone extends Model
{
    use HasFactory;

    protected $fillable = [
        'zone_id',
        'zone_name',
        'city_id'
    ];

    protected $casts = [
        'zone_id' => 'string',
        'city_id' => 'string',
    ];

    public function city()
    {
        return $this->belongsTo(PathaoCity::class, 'city_id', 'city_id');
    }

    public function areas()
    {
        return $this->hasMany(PathaoArea::class, 'zone_id', 'zone_id');
    }
}