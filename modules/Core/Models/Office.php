<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Office extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'tenant_id',
        'region_id',
        'name',
        'code',
        'address',
        'city',
        'district',
        'postal_code',
        'country',
        'phone',
        'email',
        'website',
        'logo',
        'latitude',
        'longitude',
        'settings',
        'is_active',
        'is_headquarters',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_active' => 'boolean',
            'is_headquarters' => 'boolean',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'is_active', 'city', 'district'])
            ->logOnlyDirty();
    }

    /**
     * Get the tenant that owns the office
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the region that the office belongs to
     */
    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Get all users in the office
     */
    public function users()
    {
        return $this->hasMany(\App\Models\User::class);
    }

    /**
     * Get all teams in the office
     */
    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    /**
     * Get all listings from the office
     */
    public function listings()
    {
        return $this->hasMany(\Modules\RealEstate\Models\Listing::class);
    }

    /**
     * Get office manager
     */
    public function manager()
    {
        return $this->users()->whereHas('roles', function ($query) {
            $query->where('name', 'office-manager');
        })->first();
    }

    /**
     * Get full address
     */
    public function getFullAddressAttribute(): string
    {
        return implode(', ', array_filter([
            $this->address,
            $this->district,
            $this->city,
            $this->postal_code,
            $this->country,
        ]));
    }

    /**
     * Scope for active offices
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
