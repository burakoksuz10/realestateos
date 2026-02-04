<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'created_by',
        'assigned_to',
        'contact_id',
        'lead_id',
        'deal_id',
        'listing_id',
        
        'title',
        'description',
        'type', // call, email, meeting, showing, follow_up, document, other
        'priority', // low, medium, high, urgent
        'status', // pending, in_progress, completed, cancelled
        
        'due_date',
        'due_time',
        'reminder_at',
        'completed_at',
        
        'is_recurring',
        'recurrence_pattern',
        'recurrence_end_date',
        
        'result',
        'result_notes',
        
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'due_time' => 'datetime',
            'reminder_at' => 'datetime',
            'completed_at' => 'datetime',
            'recurrence_end_date' => 'date',
            'is_recurring' => 'boolean',
            'recurrence_pattern' => 'array',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the creator
     */
    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the assigned user
     */
    public function assignedTo()
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }

    /**
     * Get the contact
     */
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * Get the lead
     */
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the deal
     */
    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    /**
     * Get the listing
     */
    public function listing()
    {
        return $this->belongsTo(\Modules\RealEstate\Models\Listing::class);
    }

    /**
     * Check if task is overdue
     */
    public function getIsOverdueAttribute(): bool
    {
        if ($this->status === 'completed' || $this->status === 'cancelled') {
            return false;
        }

        return $this->due_date && $this->due_date->isPast();
    }

    /**
     * Check if task is due today
     */
    public function getIsDueTodayAttribute(): bool
    {
        return $this->due_date && $this->due_date->isToday();
    }

    /**
     * Get priority color
     */
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'urgent' => 'red',
            'high' => 'orange',
            'medium' => 'yellow',
            'low' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'completed' => 'green',
            'in_progress' => 'blue',
            'pending' => 'yellow',
            'cancelled' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted(string $result = null, string $notes = null): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'result' => $result,
            'result_notes' => $notes,
        ]);

        // Create activity
        Activity::create([
            'user_id' => auth()->id(),
            'contact_id' => $this->contact_id,
            'lead_id' => $this->lead_id,
            'deal_id' => $this->deal_id,
            'listing_id' => $this->listing_id,
            'type' => 'task_completed',
            'subject' => "Görev tamamlandı: {$this->title}",
            'description' => $notes,
            'outcome' => $result,
        ]);
    }

    /**
     * Scope for pending tasks
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for overdue tasks
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'completed')
                     ->where('status', '!=', 'cancelled')
                     ->where('due_date', '<', now());
    }

    /**
     * Scope for due today
     */
    public function scopeDueToday($query)
    {
        return $query->whereDate('due_date', today());
    }

    /**
     * Scope for upcoming
     */
    public function scopeUpcoming($query, $days = 7)
    {
        return $query->where('status', '!=', 'completed')
                     ->where('status', '!=', 'cancelled')
                     ->whereBetween('due_date', [now(), now()->addDays($days)]);
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($task) {
            if (!$task->created_by && auth()->check()) {
                $task->created_by = auth()->id();
            }
            if (!$task->status) {
                $task->status = 'pending';
            }
            if (!$task->priority) {
                $task->priority = 'medium';
            }
        });
    }
}
