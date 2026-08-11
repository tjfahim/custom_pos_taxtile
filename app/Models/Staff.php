<?php
// app/Models/Staff.php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'designation',
        'join_at',
        'status',
        'salary',
        'email',
        'phone',
        'address'
    ];

    protected $casts = [
        'join_at' => 'date',
        'salary' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Accessor for formatted salary
    public function getFormattedSalaryAttribute()
    {
        return 'BDT. ' . number_format($this->salary, 0);
    }

    // Accessor for status badge
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'active' => 'badge-success',
            'inactive' => 'badge-danger',
            'on_leave' => 'badge-warning'
        ];
        
        $labels = [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'on_leave' => 'On Leave'
        ];
        
        return '<span class="badge ' . ($badges[$this->status] ?? 'badge-secondary') . '">' . 
               ($labels[$this->status] ?? ucfirst($this->status)) . '</span>';
    }

    // Scope for active staff
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Scope for search
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('designation', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
        }
        return $query;
    }
 
public function attendances()
{
    return $this->hasMany(Attendance::class);
}

public function todayAttendance()
{
    return $this->hasOne(Attendance::class)
                ->whereDate('attendance_date', Carbon::today());
}
}