<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PathaoCity extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_id',
        'city_name'
    ];

    protected $casts = [
        'city_id' => 'string',
    ];

    public function zones()
    {
        return $this->hasMany(PathaoZone::class, 'city_id', 'city_id');
    }
}