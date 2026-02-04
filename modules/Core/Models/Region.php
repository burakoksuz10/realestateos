<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Region extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'manager_id',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the tenant that owns the region
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the region manager
     */
    public function manager()
    {
        return $this->belongsTo(\App\Models\User::class, 'manager_id');
    }

    /**
     * Get all offices in the region
     */
    public function offices()
    {
        return $this->hasMany(Office::class);
    }

    /**
     * Get office count
     */
    public function getOfficeCountAttribute(): int
    {
        return $this->offices()->count();
    }

    /**
     * Scope for active regions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
