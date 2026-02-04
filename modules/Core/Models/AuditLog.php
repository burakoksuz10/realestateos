<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'resource',
        'resource_id',
        'ip_address',
        'user_agent',
        'request_data',
        'response_code',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'request_data' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the user that performed the action
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Scope for specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for specific resource
     */
    public function scopeForResource($query, $resource)
    {
        return $query->where('resource', 'like', "%{$resource}%");
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    /**
     * Get action label
     */
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'GET' => 'Görüntüleme',
            'POST' => 'Oluşturma',
            'PUT', 'PATCH' => 'Güncelleme',
            'DELETE' => 'Silme',
            default => $this->action,
        };
    }
}
