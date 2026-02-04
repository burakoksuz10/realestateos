<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'office_id',
        'name',
        'description',
        'leader_id',
        'color',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the office that the team belongs to
     */
    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * Get the team leader
     */
    public function leader()
    {
        return $this->belongsTo(\App\Models\User::class, 'leader_id');
    }

    /**
     * Get all members of the team
     */
    public function members()
    {
        return $this->hasMany(\App\Models\User::class);
    }

    /**
     * Get member count
     */
    public function getMemberCountAttribute(): int
    {
        return $this->members()->count();
    }

    /**
     * Scope for active teams
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
