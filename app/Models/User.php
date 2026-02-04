<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'title',
        'bio',
        'office_id',
        'team_id',
        'is_active',
        'settings',
        'last_login_at',
        'last_login_ip',
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'settings' => 'array',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'phone', 'is_active', 'office_id', 'team_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the office that the user belongs to.
     */
    public function office()
    {
        return $this->belongsTo(\Modules\Core\Models\Office::class);
    }

    /**
     * Get the team that the user belongs to.
     */
    public function team()
    {
        return $this->belongsTo(\Modules\Core\Models\Team::class);
    }

    /**
     * Get the leads assigned to the user.
     */
    public function leads()
    {
        return $this->hasMany(\Modules\CRM\Models\Lead::class, 'assigned_to');
    }

    /**
     * Get the deals assigned to the user.
     */
    public function deals()
    {
        return $this->hasMany(\Modules\CRM\Models\Deal::class, 'assigned_to');
    }

    /**
     * Get the listings created by the user.
     */
    public function listings()
    {
        return $this->hasMany(\Modules\RealEstate\Models\Listing::class, 'agent_id');
    }

    /**
     * Get the tasks assigned to the user.
     */
    public function tasks()
    {
        return $this->hasMany(\Modules\CRM\Models\Task::class, 'assigned_to');
    }

    /**
     * Get the activities performed by the user.
     */
    public function activities()
    {
        return $this->hasMany(\Modules\CRM\Models\Activity::class, 'user_id');
    }

    /**
     * Get user's full name with title
     */
    public function getFullTitleAttribute(): string
    {
        return $this->title ? "{$this->title} {$this->name}" : $this->name;
    }

    /**
     * Get user's initials
     */
    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);
        $initials = '';
        foreach ($words as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
        return substr($initials, 0, 2);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(['super-admin', 'admin']);
    }

    /**
     * Check if user is office manager
     */
    public function isOfficeManager(): bool
    {
        return $this->hasRole(['office-manager', 'admin', 'super-admin']);
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for users in specific office
     */
    public function scopeInOffice($query, $officeId)
    {
        return $query->where('office_id', $officeId);
    }
}
