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
        ->whereNull('deleted_at')
        ->where(function ($q) {
            $q->whereNull('courier_name')->orWhere('courier_name', '!=', 'Exchange');
        })
        ->whereDate('invoice_date', '>=', $startDate)
        ->whereDate('invoice_date', '<=', $endDate)
        ->with(['items', 'returnItems'])
        ->get();

    $grouped = $invoices->groupBy(function ($invoice) {
        return $invoice->team_id ?: $invoice->created_by;
    });

    $realUserIds = $grouped->keys()->filter()->values();
    $users = self::whereIn('id', $realUserIds)->get()->keyBy('id');

    $teamMembers = collect();

    foreach ($grouped as $userId => $userInvoices) {
        $quantity = $userInvoices->sum(function ($invoice) {
            return $invoice->items->sum('quantity') - $invoice->returnItems->sum('quantity');
        });

        if ($userId && $users->has($userId)) {
            $u = $users->get($userId);
            $teamMembers->push((object) [
                'id'             => $u->id,
                'name'           => $u->name,
                'email'          => $u->email,
                'total_invoices' => $userInvoices->count(),
                'total_quantity' => $quantity,
                'total_subtotal' => $userInvoices->sum('subtotal'),
                'total_delivery' => $userInvoices->sum('delivery_charge'),
                'total_amount'   => $userInvoices->sum('total'),
                'total_paid'     => $userInvoices->sum('paid_amount'),
                'total_due'      => $userInvoices->sum('due_amount'),
            ]);
        } else {
            $teamMembers->push((object) [
                'id'             => null,
                'name'           => 'Unassigned',
                'email'          => '-',
                'total_invoices' => $userInvoices->count(),
                'total_quantity' => $quantity,
                'total_subtotal' => $userInvoices->sum('subtotal'),
                'total_delivery' => $userInvoices->sum('delivery_charge'),
                'total_amount'   => $userInvoices->sum('total'),
                'total_paid'     => $userInvoices->sum('paid_amount'),
                'total_due'      => $userInvoices->sum('due_amount'),
            ]);
        }
    }

    return $teamMembers->sortByDesc('total_amount')->values();
}

public static function getTopCreatorsForRange($startDate, $endDate)
{
    $invoices = Invoice::where('status', 'confirmed')
        ->whereNull('deleted_at')
        ->where(function ($q) {
            $q->whereNull('courier_name')->orWhere('courier_name', '!=', 'Exchange');
        })
        ->whereDate('invoice_date', '>=', $startDate)
        ->whereDate('invoice_date', '<=', $endDate)
        ->with(['items', 'returnItems'])
        ->get();

    $grouped = $invoices->groupBy('created_by');

    $realUserIds = $grouped->keys()->filter()->values();
    $users = self::whereIn('id', $realUserIds)->get()->keyBy('id');

    $creators = collect();

    foreach ($grouped as $userId => $userInvoices) {
        $quantity = $userInvoices->sum(function ($invoice) {
            return $invoice->items->sum('quantity') - $invoice->returnItems->sum('quantity');
        });

        if ($userId && $users->has($userId)) {
            $u = $users->get($userId);
            $creators->push((object) [
                'id'             => $u->id,
                'name'           => $u->name,
                'email'          => $u->email,
                'total_invoices' => $userInvoices->count(),
                'total_quantity' => $quantity,
                'total_subtotal' => $userInvoices->sum('subtotal'),
                'total_delivery' => $userInvoices->sum('delivery_charge'),
                'total_amount'   => $userInvoices->sum('total'),
                'total_paid'     => $userInvoices->sum('paid_amount'),
                'total_due'      => $userInvoices->sum('due_amount'),
            ]);
        } else {
            $creators->push((object) [
                'id'             => null,
                'name'           => 'Unassigned',
                'email'          => '-',
                'total_invoices' => $userInvoices->count(),
                'total_quantity' => $quantity,
                'total_subtotal' => $userInvoices->sum('subtotal'),
                'total_delivery' => $userInvoices->sum('delivery_charge'),
                'total_amount'   => $userInvoices->sum('total'),
                'total_paid'     => $userInvoices->sum('paid_amount'),
                'total_due'      => $userInvoices->sum('due_amount'),
            ]);
        }
    }

    return $creators->sortByDesc('total_amount')->values();
}
   /**
 * Get top team members performance for today
 * Uses invoice_date (matches Today's Summary card logic)
 */
public static function getTopTeamMembersToday()
{
    $today = Carbon::today();

    // Exact same base scope as Today's Summary (DashboardController::$todayBase)
    $todayInvoices = Invoice::where('status', 'confirmed')
        ->whereNull('deleted_at')
        ->where(function ($q) {
            $q->whereNull('courier_name')->orWhere('courier_name', '!=', 'Exchange');
        })
        ->whereDate('invoice_date', $today)
        ->with(['items', 'returnItems'])
        ->get();

    // Effective team member = team_id if assigned, else the creator.
    // Group every invoice into exactly one bucket, so nothing is dropped.
    $grouped = $todayInvoices->groupBy(function ($invoice) {
        return $invoice->team_id ?: $invoice->created_by; // may be null
    });

    // Resolve all real user ids in one shot
    $realUserIds = $grouped->keys()->filter()->values();
    $users = self::whereIn('id', $realUserIds)->get()->keyBy('id');

    $teamMembers = collect();

    foreach ($grouped as $userId => $invoices) {

        $quantity = $invoices->sum(function ($invoice) {
            return $invoice->items->sum('quantity') - $invoice->returnItems->sum('quantity');
        });

        if ($userId && $users->has($userId)) {
            // Normal user (either the assigned team member, or the creator when no team member)
            $user = $users->get($userId);
            $teamMembers->push((object) [
                'id'             => $user->id,
                'name'           => $user->name,
                'email'          => $user->email,
                'total_invoices' => $invoices->count(),
                'total_quantity' => $quantity,
                'total_subtotal' => $invoices->sum('subtotal'),
                'total_delivery' => $invoices->sum('delivery_charge'),
                'total_amount'   => $invoices->sum('total'),
                'total_paid'     => $invoices->sum('paid_amount'),
                'total_due'      => $invoices->sum('due_amount'),
                'as_creator'     => $invoices->where('created_by', $user->id)->count(),
                'as_team_member' => $invoices->where('team_id', $user->id)->count(),
            ]);
        } else {
            // Safety net: both team_id and created_by were null (or pointed to a deleted user)
            $teamMembers->push((object) [
                'id'             => null,
                'name'           => 'Unassigned',
                'email'          => '-',
                'total_invoices' => $invoices->count(),
                'total_quantity' => $quantity,
                'total_subtotal' => $invoices->sum('subtotal'),
                'total_delivery' => $invoices->sum('delivery_charge'),
                'total_amount'   => $invoices->sum('total'),
                'total_paid'     => $invoices->sum('paid_amount'),
                'total_due'      => $invoices->sum('due_amount'),
                'as_creator'     => 0,
                'as_team_member' => 0,
            ]);
        }
    }

    return $teamMembers->sortByDesc('total_amount')->values();
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

    // Exact same base scope as month totals
    $monthInvoices = Invoice::where('status', 'confirmed')
        ->whereNull('deleted_at')
        ->where(function ($q) {
            $q->whereNull('courier_name')->orWhere('courier_name', '!=', 'Exchange');
        })
        ->whereDate('invoice_date', '>=', $startOfMonth)
        ->with(['items', 'returnItems'])
        ->get();

    $grouped = $monthInvoices->groupBy(function ($invoice) {
        return $invoice->team_id ?: $invoice->created_by;
    });

    $realUserIds = $grouped->keys()->filter()->values();
    $users = self::whereIn('id', $realUserIds)->get()->keyBy('id');

    $teamMembers = collect();

    foreach ($grouped as $userId => $invoices) {

        $quantity = $invoices->sum(function ($invoice) {
            return $invoice->items->sum('quantity') - $invoice->returnItems->sum('quantity');
        });

        if ($userId && $users->has($userId)) {
            $user = $users->get($userId);
            $teamMembers->push((object) [
                'id'             => $user->id,
                'name'           => $user->name,
                'email'          => $user->email,
                'total_invoices' => $invoices->count(),
                'total_quantity' => $quantity,
                'total_subtotal' => $invoices->sum('subtotal'),
                'total_delivery' => $invoices->sum('delivery_charge'),
                'total_amount'   => $invoices->sum('total'),
                'total_paid'     => $invoices->sum('paid_amount'),
                'total_due'      => $invoices->sum('due_amount'),
                'as_creator'     => $invoices->where('created_by', $user->id)->count(),
                'as_team_member' => $invoices->where('team_id', $user->id)->count(),
            ]);
        } else {
            $teamMembers->push((object) [
                'id'             => null,
                'name'           => 'Unassigned',
                'email'          => '-',
                'total_invoices' => $invoices->count(),
                'total_quantity' => $quantity,
                'total_subtotal' => $invoices->sum('subtotal'),
                'total_delivery' => $invoices->sum('delivery_charge'),
                'total_amount'   => $invoices->sum('total'),
                'total_paid'     => $invoices->sum('paid_amount'),
                'total_due'      => $invoices->sum('due_amount'),
                'as_creator'     => 0,
                'as_team_member' => 0,
            ]);
        }
    }

    return $teamMembers->sortByDesc('total_amount')->values();
}
}