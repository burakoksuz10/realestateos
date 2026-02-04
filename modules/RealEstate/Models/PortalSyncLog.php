<?php

namespace Modules\RealEstate\Models;

use Illuminate\Database\Eloquent\Model;

class PortalSyncLog extends Model
{
    protected $fillable = [
        'listing_id',
        'portal',
        'action', // create, update, delete
        'status', // pending, success, failed
        'portal_listing_id',
        'request_data',
        'response_data',
        'error_message',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'request_data' => 'array',
            'response_data' => 'array',
            'synced_at' => 'datetime',
        ];
    }

    /**
     * Get the listing
     */
    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    /**
     * Get portal label
     */
    public function getPortalLabelAttribute(): string
    {
        return match($this->portal) {
            'sahibinden' => 'Sahibinden.com',
            'hepsiemlak' => 'Hepsiemlak',
            'emlakjet' => 'Emlakjet',
            default => ucfirst($this->portal),
        };
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'success' => 'green',
            'failed' => 'red',
            'pending' => 'yellow',
            default => 'gray',
        };
    }

    /**
     * Scope for specific portal
     */
    public function scopeForPortal($query, $portal)
    {
        return $query->where('portal', $portal);
    }

    /**
     * Scope for failed syncs
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
