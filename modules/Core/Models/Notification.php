<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use SoftDeletes;

    protected $table = 'custom_notifications';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'icon',
        'color',
        'action_url',
        'action_text',
        'read_at',
        'sent_via',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'sent_via' => 'array',
            'read_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the notification
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Mark as read
     */
    public function markAsRead(): void
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Mark as unread
     */
    public function markAsUnread(): void
    {
        $this->update(['read_at' => null]);
    }

    /**
     * Check if notification is read
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for specific type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get icon based on type
     */
    public function getIconAttribute($value): string
    {
        if ($value) {
            return $value;
        }

        return match($this->type) {
            'lead' => 'user-plus',
            'deal' => 'handshake',
            'listing' => 'home',
            'task' => 'check-square',
            'message' => 'message-circle',
            'alert' => 'alert-triangle',
            'success' => 'check-circle',
            default => 'bell',
        };
    }

    /**
     * Get color based on type
     */
    public function getColorAttribute($value): string
    {
        if ($value) {
            return $value;
        }

        return match($this->type) {
            'lead' => 'blue',
            'deal' => 'green',
            'listing' => 'purple',
            'task' => 'orange',
            'message' => 'cyan',
            'alert' => 'red',
            'success' => 'green',
            default => 'gray',
        };
    }
}
