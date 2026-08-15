<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'role',
        'default_team_mate_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Users that this user added as team members
    public function teamMembers()
    {
        return $this->belongsToMany(User::class, 'user_team_members', 'user_id', 'team_member_id')
                    ->withTimestamps();
    }

    // Users who added this user as a team member
    public function addedByUsers()
    {
        return $this->belongsToMany(User::class, 'user_team_members', 'team_member_id', 'user_id')
                    ->withTimestamps();
    }

    // Default team mate relationship
    public function defaultTeamMate()
    {
        return $this->belongsTo(User::class, 'default_team_mate_id');
    }
         public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
        public function teamInvoices()
    {
        return $this->hasMany(Invoice::class, 'team_id');
    }
      public static function getTopTeamMembersToday()
    {
        return self::select([
            'users.*',
            DB::raw('(SELECT COUNT(*) FROM invoices 
                WHERE invoices.team_id = users.id 
                AND DATE(invoices.created_at) = CURDATE() 
                AND invoices.deleted_at IS NULL) as total_invoices'),
            
            DB::raw('(SELECT COALESCE(SUM(invoice_items.quantity), 0) FROM invoice_items 
                WHERE invoice_items.invoice_id IN (
                    SELECT id FROM invoices 
                    WHERE invoices.team_id = users.id 
                    AND DATE(invoices.created_at) = CURDATE() 
                    AND invoices.deleted_at IS NULL
                )) as total_quantity'),
            
            DB::raw('(SELECT COALESCE(SUM(subtotal), 0) FROM invoices 
                WHERE invoices.team_id = users.id 
                AND DATE(invoices.created_at) = CURDATE() 
                AND invoices.deleted_at IS NULL) as total_subtotal'),
            
            DB::raw('(SELECT COALESCE(SUM(delivery_charge), 0) FROM invoices 
                WHERE invoices.team_id = users.id 
                AND DATE(invoices.created_at) = CURDATE() 
                AND invoices.deleted_at IS NULL) as total_delivery'),
            
            DB::raw('(SELECT COALESCE(SUM(total), 0) FROM invoices 
                WHERE invoices.team_id = users.id 
                AND DATE(invoices.created_at) = CURDATE() 
                AND invoices.deleted_at IS NULL) as total_amount'),
            
            DB::raw('(SELECT COALESCE(SUM(paid_amount), 0) FROM invoices 
                WHERE invoices.team_id = users.id 
                AND DATE(invoices.created_at) = CURDATE() 
                AND invoices.deleted_at IS NULL) as total_paid'),
            
            DB::raw('(SELECT COALESCE(SUM(due_amount), 0) FROM invoices 
                WHERE invoices.team_id = users.id 
                AND DATE(invoices.created_at) = CURDATE() 
                AND invoices.deleted_at IS NULL) as total_due'),
            
            DB::raw('(SELECT COUNT(*) FROM invoices 
                WHERE invoices.team_id = users.id 
                AND DATE(invoices.created_at) = CURDATE() 
                AND invoices.status = "confirmed" 
                AND invoices.deleted_at IS NULL) as confirmed_invoices'),
            
            DB::raw('(SELECT COUNT(*) FROM invoices 
                WHERE invoices.team_id = users.id 
                AND DATE(invoices.created_at) = CURDATE() 
                AND invoices.status = "pending" 
                AND invoices.deleted_at IS NULL) as pending_invoices'),
            
            DB::raw('(SELECT COUNT(*) FROM invoices 
                WHERE invoices.team_id = users.id 
                AND DATE(invoices.created_at) = CURDATE() 
                AND invoices.status = "cancelled" 
                AND invoices.deleted_at IS NULL) as cancelled_invoices')
        ])
        ->whereIn('users.id', function($query) {
            $query->select('team_id')
                  ->from('invoices')
                  ->whereDate('created_at', '=', date('Y-m-d'))
                  ->whereNull('deleted_at')
                  ->distinct();
        })
        ->having('total_invoices', '>', 0)
        ->orderBy('total_amount', 'desc')
        ->get();
    }

    /**
     * Get top team members performance for this month (similar to topCreatorsMonth)
     */
    public static function getTopTeamMembersMonth($startOfMonth = null)
    {
        if (!$startOfMonth) {
            $startOfMonth = now()->startOfMonth()->format('Y-m-d');
        }

        return self::select([
            'users.*',
            DB::raw('(SELECT COUNT(*) FROM invoices 
                WHERE invoices.team_id = users.id 
                AND DATE(invoices.created_at) >= "' . $startOfMonth . '" 
                AND invoices.deleted_at IS NULL) as total_invoices'),
            
            DB::raw('(SELECT COALESCE(SUM(invoice_items.quantity), 0) FROM invoice_items 
                WHERE invoice_items.invoice_id IN (
                    SELECT id FROM invoices 
                    WHERE invoices.team_id = users.id 
                    AND DATE(invoices.created_at) >= "' . $startOfMonth . '" 
                    AND invoices.deleted_at IS NULL
                )) as total_quantity'),
            
            DB::raw('(SELECT COALESCE(SUM(subtotal), 0) FROM invoices 
                WHERE invoices.team_id = users.id 
                AND DATE(invoices.created_at) >= "' . $startOfMonth . '" 
                AND invoices.deleted_at IS NULL) as total_subtotal'),
            
            DB::raw('(SELECT COALESCE(SUM(delivery_charge), 0) FROM invoices 
                WHERE invoices.team_id = users.id 
                AND DATE(invoices.created_at) >= "' . $startOfMonth . '" 
                AND invoices.deleted_at IS NULL) as total_delivery'),
            
            DB::raw('(SELECT COALESCE(SUM(total), 0) FROM invoices 
                WHERE invoices.team_id = users.id 
                AND DATE(invoices.created_at) >= "' . $startOfMonth . '" 
                AND invoices.deleted_at IS NULL) as total_amount'),
            
            DB::raw('(SELECT COALESCE(SUM(paid_amount), 0) FROM invoices 
                WHERE invoices.team_id = users.id 
                AND DATE(invoices.created_at) >= "' . $startOfMonth . '" 
                AND invoices.deleted_at IS NULL) as total_paid'),
            
            DB::raw('(SELECT COALESCE(SUM(due_amount), 0) FROM invoices 
                WHERE invoices.team_id = users.id 
                AND DATE(invoices.created_at) >= "' . $startOfMonth . '" 
                AND invoices.deleted_at IS NULL) as total_due'),
            
            DB::raw('(SELECT COUNT(*) FROM invoices 
                WHERE invoices.team_id = users.id 
                AND DATE(invoices.created_at) >= "' . $startOfMonth . '" 
                AND invoices.status = "confirmed" 
                AND invoices.deleted_at IS NULL) as confirmed_invoices'),
            
            DB::raw('(SELECT COUNT(*) FROM invoices 
                WHERE invoices.team_id = users.id 
                AND DATE(invoices.created_at) >= "' . $startOfMonth . '" 
                AND invoices.status = "pending" 
                AND invoices.deleted_at IS NULL) as pending_invoices'),
            
            DB::raw('(SELECT COUNT(*) FROM invoices 
                WHERE invoices.team_id = users.id 
                AND DATE(invoices.created_at) >= "' . $startOfMonth . '" 
                AND invoices.status = "cancelled" 
                AND invoices.deleted_at IS NULL) as cancelled_invoices')
        ])
        ->whereIn('users.id', function($query) use ($startOfMonth) {
            $query->select('team_id')
                  ->from('invoices')
                  ->whereDate('created_at', '>=', $startOfMonth)
                  ->whereNull('deleted_at')
                  ->distinct();
        })
        ->having('total_invoices', '>', 0)
        ->orderBy('total_amount', 'desc')
        ->get();
    }

    
}