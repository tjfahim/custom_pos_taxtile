<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
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
     

    public static function getTopTeamMembersForRange($startDate, $endDate)
{
    $invoices = Invoice::where('status', 'confirmed')
        ->where('is_inhouse_sale', 0)
        ->whereDate('invoice_date', '>=', $startDate)
        ->whereDate('invoice_date', '<=', $endDate)
        ->whereNotNull('team_id')
        ->whereNull('deleted_at')
        ->with(['items', 'returnItems'])
        ->get();

    $grouped = $invoices->groupBy('team_id');

    return self::whereIn('id', $grouped->keys())
        ->get()
        ->map(function ($user) use ($grouped) {
            $userInvoices = $grouped->get($user->id, collect());

            $quantity = $userInvoices->sum(function ($invoice) {
                return $invoice->items->sum('quantity') - $invoice->returnItems->sum('quantity');
            });

            return (object) [
                'id'             => $user->id,
                'name'           => $user->name,
                'email'          => $user->email,
                'total_invoices' => $userInvoices->count(),
                'total_quantity' => $quantity,
                'total_subtotal' => $userInvoices->sum('subtotal'),
                'total_delivery' => $userInvoices->sum('delivery_charge'),
                'total_amount'   => $userInvoices->sum('total'),
                'total_paid'     => $userInvoices->sum('paid_amount'),
                'total_due'      => $userInvoices->sum('due_amount'),
            ];
        })
        ->sortByDesc('total_amount')
        ->values();
}

public static function getTopCreatorsForRange($startDate, $endDate)
{
    $invoices = Invoice::where('status', 'confirmed')
        ->where('is_inhouse_sale', 0)
        ->whereDate('invoice_date', '>=', $startDate)
        ->whereDate('invoice_date', '<=', $endDate)
        ->whereNotNull('created_by')
        ->whereNull('deleted_at')
        ->with(['items', 'returnItems'])
        ->get();

    $grouped = $invoices->groupBy('created_by');

    return self::whereIn('id', $grouped->keys())
        ->get()
        ->map(function ($user) use ($grouped) {
            $userInvoices = $grouped->get($user->id, collect());

            $quantity = $userInvoices->sum(function ($invoice) {
                return $invoice->items->sum('quantity') - $invoice->returnItems->sum('quantity');
            });

            return (object) [
                'id'             => $user->id,
                'name'           => $user->name,
                'email'          => $user->email,
                'total_invoices' => $userInvoices->count(),
                'total_quantity' => $quantity,
                'total_subtotal' => $userInvoices->sum('subtotal'),
                'total_delivery' => $userInvoices->sum('delivery_charge'),
                'total_amount'   => $userInvoices->sum('total'),
                'total_paid'     => $userInvoices->sum('paid_amount'),
                'total_due'      => $userInvoices->sum('due_amount'),
            ];
        })
        ->sortByDesc('total_amount')
        ->values();
}
   /**
 * Get top team members performance for today
 * Uses invoice_date (matches Today's Summary card logic)
 */
public static function getTopTeamMembersToday()
{
    $today = Carbon::today();

    // Get invoices from TODAY'S SUMMARY (same as Today's Summary card)
    $todayInvoices = Invoice::where('status', 'confirmed')
        ->where('is_inhouse_sale', 0)  // Exclude in-house
        ->whereDate('invoice_date', $today)  // Use invoice_date NOT created_at
        ->whereNotNull('team_id')  // Only assigned to team members
        ->whereNull('deleted_at')
        ->with(['items', 'returnItems'])
        ->get();

    // Group invoices by team member
    $grouped = $todayInvoices->groupBy('team_id');

    $teamMembers = self::whereIn('id', $grouped->keys())
        ->get()
        ->map(function ($user) use ($grouped) {
            $invoices = $grouped->get($user->id, collect());

            // Quantity = items - returns
            $quantity = $invoices->sum(function ($invoice) {
                $itemsQty = $invoice->items->sum('quantity');
                $returnQty = $invoice->returnItems->sum('quantity');
                return $itemsQty - $returnQty;
            });

            return (object) [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'total_invoices' => $invoices->count(),
                'total_quantity' => $quantity,
                'total_subtotal' => $invoices->sum('subtotal'),
                'total_delivery' => $invoices->sum('delivery_charge'),
                'total_amount' => $invoices->sum('total'),
                'total_paid' => $invoices->sum('paid_amount'),
                'total_due' => $invoices->sum('due_amount'),
                
                // Break down by role
                'as_creator' => $invoices->where('created_by', $user->id)->count(),
                'as_team_member' => $invoices->count(), // All are team members since we filtered
            ];
        })
        ->sortByDesc('total_amount')
        ->values();

    return $teamMembers;
}

/**
 * Get top team members performance for this month
 * FIXED: Now uses invoice_date instead of created_at for consistency
 * FIXED: Uses the same filtering logic as getTopTeamMembersToday()
 */
public static function getTopTeamMembersMonth($startOfMonth = null)
{
    if (!$startOfMonth) {
        $startOfMonth = now()->startOfMonth()->format('Y-m-d');
    }

    return self::select([
        'users.*',
        // Total invoices (confirmed only, matching today's logic)
        DB::raw('(SELECT COUNT(*) FROM invoices 
            WHERE invoices.team_id = users.id 
            AND invoices.status = "confirmed"
            AND invoices.is_inhouse_sale = 0
            AND DATE(invoices.invoice_date) >= "' . $startOfMonth . '" 
            AND invoices.deleted_at IS NULL) as total_invoices'),
        
        // Total quantity from invoice items (excluding returns)
        DB::raw('(SELECT COALESCE(SUM(invoice_items.quantity), 0) FROM invoice_items 
            WHERE invoice_items.invoice_id IN (
                SELECT id FROM invoices 
                WHERE invoices.team_id = users.id 
                AND invoices.status = "confirmed"
                AND invoices.is_inhouse_sale = 0
                AND DATE(invoices.invoice_date) >= "' . $startOfMonth . '" 
                AND invoices.deleted_at IS NULL
            )) as total_quantity'),
        
        // Total subtotal
        DB::raw('(SELECT COALESCE(SUM(subtotal), 0) FROM invoices 
            WHERE invoices.team_id = users.id 
            AND invoices.status = "confirmed"
            AND invoices.is_inhouse_sale = 0
            AND DATE(invoices.invoice_date) >= "' . $startOfMonth . '" 
            AND invoices.deleted_at IS NULL) as total_subtotal'),
        
        // Total delivery charge
        DB::raw('(SELECT COALESCE(SUM(delivery_charge), 0) FROM invoices 
            WHERE invoices.team_id = users.id 
            AND invoices.status = "confirmed"
            AND invoices.is_inhouse_sale = 0
            AND DATE(invoices.invoice_date) >= "' . $startOfMonth . '" 
            AND invoices.deleted_at IS NULL) as total_delivery'),
        
        // Total amount
        DB::raw('(SELECT COALESCE(SUM(total), 0) FROM invoices 
            WHERE invoices.team_id = users.id 
            AND invoices.status = "confirmed"
            AND invoices.is_inhouse_sale = 0
            AND DATE(invoices.invoice_date) >= "' . $startOfMonth . '" 
            AND invoices.deleted_at IS NULL) as total_amount'),
        
        // Total paid
        DB::raw('(SELECT COALESCE(SUM(paid_amount), 0) FROM invoices 
            WHERE invoices.team_id = users.id 
            AND invoices.status = "confirmed"
            AND invoices.is_inhouse_sale = 0
            AND DATE(invoices.invoice_date) >= "' . $startOfMonth . '" 
            AND invoices.deleted_at IS NULL) as total_paid'),
        
        // Total due
        DB::raw('(SELECT COALESCE(SUM(due_amount), 0) FROM invoices 
            WHERE invoices.team_id = users.id 
            AND invoices.status = "confirmed"
            AND invoices.is_inhouse_sale = 0
            AND DATE(invoices.invoice_date) >= "' . $startOfMonth . '" 
            AND invoices.deleted_at IS NULL) as total_due'),
        
        // Status breakdowns (optional - keeps them for completeness)
        DB::raw('(SELECT COUNT(*) FROM invoices 
            WHERE invoices.team_id = users.id 
            AND DATE(invoices.invoice_date) >= "' . $startOfMonth . '" 
            AND invoices.status = "confirmed" 
            AND invoices.is_inhouse_sale = 0
            AND invoices.deleted_at IS NULL) as confirmed_invoices'),
        
        DB::raw('(SELECT COUNT(*) FROM invoices 
            WHERE invoices.team_id = users.id 
            AND DATE(invoices.invoice_date) >= "' . $startOfMonth . '" 
            AND invoices.status = "pending" 
            AND invoices.is_inhouse_sale = 0
            AND invoices.deleted_at IS NULL) as pending_invoices'),
        
        DB::raw('(SELECT COUNT(*) FROM invoices 
            WHERE invoices.team_id = users.id 
            AND DATE(invoices.invoice_date) >= "' . $startOfMonth . '" 
            AND invoices.status = "cancelled" 
            AND invoices.is_inhouse_sale = 0
            AND invoices.deleted_at IS NULL) as cancelled_invoices')
    ])
    ->whereIn('users.id', function($query) use ($startOfMonth) {
        $query->select('team_id')
              ->from('invoices')
              ->where('status', 'confirmed')
              ->where('is_inhouse_sale', 0)
              ->whereDate('invoice_date', '>=', $startOfMonth)  // FIXED: Use invoice_date
              ->whereNull('deleted_at')
              ->distinct();
    })
    ->having('total_invoices', '>', 0)
    ->orderBy('total_amount', 'desc')
    ->get();
}
}