<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'contact_id',
        'lead_id',
        'deal_id',
        'listing_id',
        
        'type', // call, email, meeting, showing, note, whatsapp, sms, task_completed
        'subject',
        'description',
        'outcome',
        
        // Call specific
        'call_duration',
        'call_recording_url',
        'call_transcript',
        'call_sentiment',
        
        // Meeting/Showing specific
        'location',
        'scheduled_at',
        'completed_at',
        'attendees',
        
        // Email specific
        'email_message_id',
        'email_thread_id',
        
        // AI Analysis
        'ai_summary',
        'ai_next_actions',
        'ai_sentiment',
        'ai_intent',
        
        'is_automated',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'call_duration' => 'integer',
            'attendees' => 'array',
            'ai_next_actions' => 'array',
            'metadata' => 'array',
            'is_automated' => 'boolean',
            'scheduled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Get the user who performed the activity
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
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
     * Get type icon
     */
    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'call' => 'phone',
            'email' => 'mail',
            'meeting' => 'users',
            'showing' => 'home',
            'note' => 'file-text',
            'whatsapp' => 'message-circle',
            'sms' => 'smartphone',
            'task_completed' => 'check-circle',
            default => 'activity',
        };
    }

    /**
     * Get type color
     */
    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'call' => 'blue',
            'email' => 'purple',
            'meeting' => 'green',
            'showing' => 'orange',
            'note' => 'gray',
            'whatsapp' => 'green',
            'sms' => 'cyan',
            'task_completed' => 'green',
            default => 'gray',
        };
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute(): ?string
    {
        if (!$this->call_duration) {
            return null;
        }

        $minutes = floor($this->call_duration / 60);
        $seconds = $this->call_duration % 60;

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    /**
     * Scope by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for calls
     */
    public function scopeCalls($query)
    {
        return $query->where('type', 'call');
    }

    /**
     * Scope for showings
     */
    public function scopeShowings($query)
    {
        return $query->where('type', 'showing');
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($activity) {
            if (!$activity->user_id && auth()->check()) {
                $activity->user_id = auth()->id();
            }
        });

        static::created(function ($activity) {
            // Update last_activity_at on related models
            if ($activity->lead) {
                $activity->lead->update(['last_activity_at' => now()]);
            }
            if ($activity->contact) {
                $activity->contact->update(['last_contact_at' => now()]);
            }
        });
    }
}
