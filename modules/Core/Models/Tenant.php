<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Tenant extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'subdomain',
        'domain',
        'logo',
        'favicon',
        'primary_color',
        'secondary_color',
        'settings',
        'features',
        'subscription_plan',
        'subscription_ends_at',
        'is_active',
        'trial_ends_at',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'features' => 'array',
            'is_active' => 'boolean',
            'subscription_ends_at' => 'datetime',
            'trial_ends_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'subdomain', 'is_active', 'subscription_plan'])
            ->logOnlyDirty();
    }

    /**
     * Get all offices for the tenant
     */
    public function offices()
    {
        return $this->hasMany(Office::class);
    }

    /**
     * Get all users for the tenant
     */
    public function users()
    {
        return $this->hasManyThrough(\App\Models\User::class, Office::class);
    }

    /**
     * Check if tenant has a specific feature
     */
    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? []);
    }

    /**
     * Check if subscription is active
     */
    public function isSubscriptionActive(): bool
    {
        if (!$this->subscription_ends_at) {
            return $this->trial_ends_at && $this->trial_ends_at->isFuture();
        }
        
        return $this->subscription_ends_at->isFuture();
    }

    /**
     * Get setting value
     */
    public function getSetting(string $key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    /**
     * Set setting value
     */
    public function setSetting(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->settings = $settings;
        $this->save();
    }
}
