<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
}