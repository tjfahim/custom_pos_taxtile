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

    /**
     * Get top team members performance for a custom date range.
     * Includes an Exchange count per member (column: total_exchange).
     */
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

        // Exchange counts for the same range
        $exchangeCounts = self::getExchangeCountsForRange($startDate, $endDate);

        $teamMembers = collect();

        foreach ($grouped as $userId => $userInvoices) {
            $quantity = $userInvoices->sum(function ($invoice) {
                return $invoice->items->sum('quantity') - $invoice->returnItems->sum('quantity');
            });

            // Resolve exchange count for this bucket
            $exchange = 0;
            if ($userId && $exchangeCounts->has($userId)) {
                $row = $exchangeCounts->get($userId);
                // Bucket came from team_id if any invoice has team_id = $userId
                $isTeamBucket = $userInvoices->contains(fn ($inv) => $inv->team_id == $userId);
                $exchange = $isTeamBucket ? $row['as_team_member'] : $row['as_creator'];
            }

            if ($userId && $users->has($userId)) {
                $u = $users->get($userId);
                $teamMembers->push((object) [
                    'id'             => $u->id,
                    'name'           => $u->name,
                    'email'          => $u->email,
                    'total_invoices' => $userInvoices->count(),
                    'total_quantity' => $quantity,
                    'total_exchange' => $exchange,
                    'total_subtotal' => $userInvoices->sum('subtotal'),
                    'total_delivery' => $userInvoices->sum('delivery_charge'),
                    'total_amount'   => $userInvoices->sum('total'),
                    'total_paid'     => $userInvoices->sum('total_paid_amount'),
                    'total_due'      => $userInvoices->sum('due_amount'),
                ]);
            } else {
                $teamMembers->push((object) [
                    'id'             => null,
                    'name'           => 'Unassigned',
                    'email'          => '-',
                    'total_invoices' => $userInvoices->count(),
                    'total_quantity' => $quantity,
                    'total_exchange' => $exchange,
                    'total_subtotal' => $userInvoices->sum('subtotal'),
                    'total_delivery' => $userInvoices->sum('delivery_charge'),
                    'total_amount'   => $userInvoices->sum('total'),
                    'total_paid'     => $userInvoices->sum('total_paid_amount'),
                    'total_due'      => $userInvoices->sum('due_amount'),
                ]);
            }
        }

        return $teamMembers->sortByDesc('total_amount')->values();
    }

    /**
     * Get top creators performance for a custom date range.
     * Includes an Exchange count per creator (column: total_exchange).
     */
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

        // Exchange counts for the same range (as_creator = created_by)
        $exchangeCounts = self::getExchangeCountsForRange($startDate, $endDate);

        $creators = collect();

        foreach ($grouped as $userId => $userInvoices) {
            $quantity = $userInvoices->sum(function ($invoice) {
                return $invoice->items->sum('quantity') - $invoice->returnItems->sum('quantity');
            });

            $exchange = 0;
            if ($userId && $exchangeCounts->has($userId)) {
                $exchange = $exchangeCounts->get($userId)['as_creator'];
            }

            if ($userId && $users->has($userId)) {
                $u = $users->get($userId);
                $creators->push((object) [
                    'id'             => $u->id,
                    'name'           => $u->name,
                    'email'          => $u->email,
                    'total_invoices' => $userInvoices->count(),
                    'total_quantity' => $quantity,
                    'total_exchange' => $exchange,
                    'total_subtotal' => $userInvoices->sum('subtotal'),
                    'total_delivery' => $userInvoices->sum('delivery_charge'),
                    'total_amount'   => $userInvoices->sum('total'),
                    'total_paid'     => $userInvoices->sum('total_paid_amount'),
                    'total_due'      => $userInvoices->sum('due_amount'),
                ]);
            } else {
                $creators->push((object) [
                    'id'             => null,
                    'name'           => 'Unassigned',
                    'email'          => '-',
                    'total_invoices' => $userInvoices->count(),
                    'total_quantity' => $quantity,
                    'total_exchange' => $exchange,
                    'total_subtotal' => $userInvoices->sum('subtotal'),
                    'total_delivery' => $userInvoices->sum('delivery_charge'),
                    'total_amount'   => $userInvoices->sum('total'),
                    'total_paid'     => $userInvoices->sum('total_paid_amount'),
                    'total_due'      => $userInvoices->sum('due_amount'),
                ]);
            }
        }

        return $creators->sortByDesc('total_amount')->values();
    }

    /**
     * Get Exchange invoice counts per user for a date range.
     *
     * Returns a collection keyed by user id with two values:
     *   - as_creator:     exchanges where created_by = user
     *   - as_team_member: exchanges where team_id = user
     */
    public static function getExchangeCountsForRange($startDate, $endDate)
    {
        $start = $startDate instanceof \Carbon\Carbon ? $startDate->toDateString() : $startDate;
        $end   = $endDate   instanceof \Carbon\Carbon ? $endDate->toDateString()   : $endDate;

        $exchanges = Invoice::where('status', 'confirmed')
            ->whereNull('deleted_at')
            ->where('courier_name', 'Exchange')
            ->whereDate('invoice_date', '>=', $start)
            ->whereDate('invoice_date', '<=', $end)
            ->get(['id', 'created_by', 'team_id']);

        // Build two lookup maps
        $byCreator = $exchanges->whereNotNull('created_by')
            ->groupBy('created_by')
            ->map->count();

        $byTeam = $exchanges->whereNotNull('team_id')
            ->groupBy('team_id')
            ->map->count();

        // Merge into one collection keyed by user id
        $userIds = $byCreator->keys()->merge($byTeam->keys())->filter()->unique();

        return $userIds->mapWithKeys(function ($id) use ($byCreator, $byTeam) {
            return [
                $id => [
                    'as_creator'     => (int) ($byCreator[$id] ?? 0),
                    'as_team_member' => (int) ($byTeam[$id] ?? 0),
                ],
            ];
        });
    }

    /**
     * Attach exchange_sell_qty (what customers took) and exchange_return_qty
     * (what customers gave back) to each performance row, per user.
     * $bucket: 'team' => team_id based, 'creator' => created_by based.
     */
    public static function attachExchangeQty($rows, $startDate, $endDate, string $bucket = 'creator')
    {
        $start = $startDate instanceof \Carbon\Carbon ? $startDate->toDateString() : $startDate;
        $end   = $endDate   instanceof \Carbon\Carbon ? $endDate->toDateString()   : $endDate;

        $exchanges = Invoice::where('status', 'confirmed')
            ->whereNull('deleted_at')
            ->where('courier_name', 'Exchange')
            ->whereDate('invoice_date', '>=', $start)
            ->whereDate('invoice_date', '<=', $end)
            ->with(['items', 'returnItems'])
            ->get();

        $sum = function ($col, $field) use ($exchanges) {
            $out = [];
            foreach ($exchanges as $inv) {
                $uid = $inv->{$col};
                if (!$uid) continue;
                $out[$uid]['sell']   = ($out[$uid]['sell']   ?? 0) + (int) $inv->items->sum('quantity');
                $out[$uid]['return'] = ($out[$uid]['return'] ?? 0) + (int) $inv->returnItems->sum('quantity');
            }
            return $out;
        };
        $byCreator = $sum('created_by', null);
        $byTeam    = $sum('team_id', null);

        foreach ($rows as $row) {
            $id = $row->id;
            $useTeam = $bucket === 'team' && (($row->as_team_member ?? 0) > 0);
            $src = $useTeam ? $byTeam : $byCreator;
            $row->exchange_sell_qty   = $id ? ($src[$id]['sell']   ?? 0) : 0;
            $row->exchange_return_qty = $id ? ($src[$id]['return'] ?? 0) : 0;
        }
        return $rows;
    }

    /**
     * Get top team members performance for today
     * Uses invoice_date (matches Today's Summary card logic)
     */
    public static function getTopTeamMembersToday()
    {
        $today = Carbon::today();
        $exchangeCounts = self::getExchangeCountsForRange($today, $today);

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

            // Exchange count for this bucket
            $exchange = 0;
            if ($userId && $exchangeCounts->has($userId)) {
                $row = $exchangeCounts->get($userId);
                $isTeamBucket = $invoices->contains(fn ($inv) => $inv->team_id == $userId);
                $exchange = $isTeamBucket ? $row['as_team_member'] : $row['as_creator'];
            }

            if ($userId && $users->has($userId)) {
                // Normal user (either the assigned team member, or the creator when no team member)
                $user = $users->get($userId);
                $teamMembers->push((object) [
                    'id'             => $user->id,
                    'name'           => $user->name,
                    'email'          => $user->email,
                    'total_invoices' => $invoices->count(),
                    'total_quantity' => $quantity,
                    'total_exchange' => $exchange,
                    'total_subtotal' => $invoices->sum('subtotal'),
                    'total_delivery' => $invoices->sum('delivery_charge'),
                    'total_amount'   => $invoices->sum('total'),
                    'total_paid'     => $invoices->sum('total_paid_amount'),
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
                    'total_exchange' => $exchange,
                    'total_subtotal' => $invoices->sum('subtotal'),
                    'total_delivery' => $invoices->sum('delivery_charge'),
                    'total_amount'   => $invoices->sum('total'),
                    'total_paid'     => $invoices->sum('total_paid_amount'),
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
     * Uses invoice_date (matches Today's Summary card logic)
     */
    public static function getTopTeamMembersMonth($startOfMonth = null)
    {
        if (!$startOfMonth) {
            $startOfMonth = now()->startOfMonth()->format('Y-m-d');
        }

        $exchangeCounts = self::getExchangeCountsForRange($startOfMonth, now()->toDateString());

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

            // Exchange count for this bucket
            $exchange = 0;
            if ($userId && $exchangeCounts->has($userId)) {
                $row = $exchangeCounts->get($userId);
                $isTeamBucket = $invoices->contains(fn ($inv) => $inv->team_id == $userId);
                $exchange = $isTeamBucket ? $row['as_team_member'] : $row['as_creator'];
            }

            if ($userId && $users->has($userId)) {
                $user = $users->get($userId);
                $teamMembers->push((object) [
                    'id'             => $user->id,
                    'name'           => $user->name,
                    'email'          => $user->email,
                    'total_invoices' => $invoices->count(),
                    'total_quantity' => $quantity,
                    'total_exchange' => $exchange,
                    'total_subtotal' => $invoices->sum('subtotal'),
                    'total_delivery' => $invoices->sum('delivery_charge'),
                    'total_amount'   => $invoices->sum('total'),
                    'total_paid'     => $invoices->sum('total_paid_amount'),
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
                    'total_exchange' => $exchange,
                    'total_subtotal' => $invoices->sum('subtotal'),
                    'total_delivery' => $invoices->sum('delivery_charge'),
                    'total_amount'   => $invoices->sum('total'),
                    'total_paid'     => $invoices->sum('total_paid_amount'),
                    'total_due'      => $invoices->sum('due_amount'),
                    'as_creator'     => 0,
                    'as_team_member' => 0,
                ]);
            }
        }

        return $teamMembers->sortByDesc('total_amount')->values();
    }
}