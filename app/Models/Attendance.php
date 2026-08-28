<?php
// app/Models/Attendance.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_date',
        'in_time',
        'out_time',
        'is_friday',
        'is_govt_holiday',
        'on_leave',
        'note',
        'status'
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'is_friday' => 'boolean',
        'is_govt_holiday' => 'boolean',
        'on_leave' => 'boolean',
        'in_time' => 'datetime:H:i:s',
        'out_time' => 'datetime:H:i:s',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'present' => 'badge-success',
            'absent' => 'badge-danger',
            'late' => 'badge-warning',
            'half_day' => 'badge-info',
            'holiday' => 'badge-primary',
            'friday' => 'badge-secondary',
            'leave' => 'badge-dark'
        ];
        
        $labels = [
            'present' => 'Present',
            'absent' => 'Absent',
            'late' => 'Late',
            'half_day' => 'Half Day',
            'holiday' => 'Holiday',
            'friday' => 'Friday',
            'leave' => 'On Leave'
        ];
        
        return '<span class="badge ' . ($badges[$this->status] ?? 'badge-secondary') . '">' . 
               ($labels[$this->status] ?? ucfirst($this->status)) . '</span>';
    }

    public function getFormattedInTimeAttribute()
    {
        return $this->in_time ? Carbon::parse($this->in_time)->format('h:i A') : '-';
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('attendance_date', $date);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForMonth($query, $year, $month)
    {
        return $query->whereYear('attendance_date', $year)
                     ->whereMonth('attendance_date', $month);
    }

    public function scopeWithFilters($query, $filters)
    {
        if (isset($filters['date'])) {
            $query->whereDate('attendance_date', $filters['date']);
        }
        
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        return $query;
    }

    public static function calculateWorkingHours($inTime, $outTime)
    {
        if (!$inTime || !$outTime) {
            return 0;
        }
        
        $start = Carbon::parse($inTime);
        $end = Carbon::parse($outTime);
        return $end->diffInHours($start);
    }

    public static function getStatusFromTimes($inTime, $outTime, $isFriday = false, $isHoliday = false, $onLeave = false)
    {
        if ($onLeave) {
            return 'leave';
        }
        
        if ($isHoliday) {
            return 'holiday';
        }
        
        if ($isFriday) {
            return 'friday';
        }
        
        if (!$inTime && !$outTime) {
            return 'absent';
        }
        
        if ($inTime) {
            $cutoffTime = Carbon::parse('11:31:00');
            $inTimeParsed = Carbon::parse($inTime);
            
            if ($inTimeParsed->gt($cutoffTime)) {
                return 'late';
            }
        }
        
        if ($inTime && $outTime) {
            $hours = self::calculateWorkingHours($inTime, $outTime);
            if ($hours < 4) {
                return 'half_day';
            }
        }
        
        return 'present';
    }
}