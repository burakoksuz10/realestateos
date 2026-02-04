<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Modules\CRM\Events\LeadCreated;
use Modules\CRM\Events\LeadUpdated;
use Modules\CRM\Events\LeadConverted;

class Lead extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'office_id',
        'contact_id',
        'assigned_to',
        'listing_id',
        'pipeline_id',
        'stage_id',
        
        // Lead Info
        'title',
        'status', // new, contacted, qualified, proposal, negotiation, converted, lost
        'priority', // low, medium, high, urgent
        'score', // 0-100
        'temperature', // cold, warm, hot
        
        // Source
        'source', // website, portal, referral, social, call, walk_in, whatsapp
        'source_detail',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'landing_page',
        'referrer_url',
        
        // Requirements
        'interest_type', // buy, rent, invest
        'property_type',
        'property_category',
        'budget_min',
        'budget_max',
        'budget_currency',
        'preferred_locations',
        'room_requirement',
        'size_min',
        'size_max',
        'requirements_notes',
        
        // Timeline
        'urgency', // immediate, 1_month, 3_months, 6_months, exploring
        'expected_close_date',
        
        // Qualification
        'is_qualified',
        'qualification_notes',
        'disqualification_reason',
        
        // AI Analysis
        'ai_score',
        'ai_analysis',
        'ai_suggestions',
        'intent_signals',
        'behavior_data',
        
        // Meta
        'tags',
        'notes',
        'custom_fields',
        'first_response_at',
        'last_activity_at',
        'converted_at',
        'lost_at',
        'lost_reason',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'ai_score' => 'integer',
            'budget_min' => 'decimal:2',
            'budget_max' => 'decimal:2',
            'size_min' => 'decimal:2',
            'size_max' => 'decimal:2',
            'preferred_locations' => 'array',
            'ai_analysis' => 'array',
            'ai_suggestions' => 'array',
            'intent_signals' => 'array',
            'behavior_data' => 'array',
            'tags' => 'array',
            'custom_fields' => 'array',
            'is_qualified' => 'boolean',
            'expected_close_date' => 'date',
            'first_response_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'converted_at' => 'datetime',
            'lost_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'stage_id', 'assigned_to', 'score', 'is_qualified'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the office
     */
    public function office()
    {
        return $this->belongsTo(\Modules\Core\Models\Office::class);
    }

    /**
     * Get the contact
     */
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * Get the assigned user
     */
    public function assignedTo()
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }

    /**
     * Get the listing
     */
    public function listing()
    {
        return $this->belongsTo(\Modules\RealEstate\Models\Listing::class);
    }

    /**
     * Get the pipeline
     */
    public function pipeline()
    {
        return $this->belongsTo(Pipeline::class);
    }

    /**
     * Get the stage
     */
    public function stage()
    {
        return $this->belongsTo(PipelineStage::class, 'stage_id');
    }

    /**
     * Get activities
     */
    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Get tasks
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get the deal if converted
     */
    public function deal()
    {
        return $this->hasOne(Deal::class);
    }

    /**
     * Calculate lead score
     */
    public function calculateScore(): int
    {
        $score = 0;
        $weights = config('reos.lead_scoring.weights');

        // Response time score
        if ($this->first_response_at) {
            $responseMinutes = $this->created_at->diffInMinutes($this->first_response_at);
            if ($responseMinutes <= 5) $score += $weights['response_time'];
            elseif ($responseMinutes <= 30) $score += $weights['response_time'] * 0.7;
            elseif ($responseMinutes <= 60) $score += $weights['response_time'] * 0.5;
            elseif ($responseMinutes <= 240) $score += $weights['response_time'] * 0.3;
        }

        // Engagement score
        $activityCount = $this->activities()->count();
        $engagementScore = min($activityCount * 5, $weights['engagement']);
        $score += $engagementScore;

        // Budget match score
        if ($this->budget_min || $this->budget_max) {
            $score += $weights['budget_match'] * 0.5;
            if ($this->is_qualified) {
                $score += $weights['budget_match'] * 0.5;
            }
        }

        // Location interest score
        if (!empty($this->preferred_locations)) {
            $score += $weights['location_interest'];
        }

        // Behavior signals
        $signals = $this->intent_signals ?? [];
        if (!empty($signals)) {
            $signalScore = count($signals) * 4;
            $score += min($signalScore, $weights['behavior_signals']);
        }

        return min($score, 100);
    }

    /**
     * Get temperature based on score
     */
    public function getTemperatureAttribute(): string
    {
        $thresholds = config('reos.lead_scoring.thresholds');
        
        if ($this->score >= $thresholds['hot']) {
            return 'hot';
        } elseif ($this->score >= $thresholds['warm']) {
            return 'warm';
        }
        return 'cold';
    }

    /**
     * Get temperature color
     */
    public function getTemperatureColorAttribute(): string
    {
        return match($this->temperature) {
            'hot' => 'red',
            'warm' => 'orange',
            'cold' => 'blue',
            default => 'gray',
        };
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
     * Convert to deal
     */
    public function convertToDeal(array $dealData = []): Deal
    {
        $deal = Deal::create(array_merge([
            'office_id' => $this->office_id,
            'contact_id' => $this->contact_id,
            'lead_id' => $this->id,
            'assigned_to' => $this->assigned_to,
            'listing_id' => $this->listing_id,
            'title' => $this->title ?? "Deal from Lead #{$this->id}",
            'status' => 'open',
            'value' => $this->budget_max ?? $this->budget_min,
            'currency' => $this->budget_currency ?? 'TRY',
        ], $dealData));

        $this->update([
            'status' => 'converted',
            'converted_at' => now(),
        ]);

        event(new LeadConverted($this, $deal));

        return $deal;
    }

    /**
     * Mark as lost
     */
    public function markAsLost(string $reason = null): void
    {
        $this->update([
            'status' => 'lost',
            'lost_at' => now(),
            'lost_reason' => $reason,
        ]);
    }

    /**
     * Scope for new leads
     */
    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    /**
     * Scope for hot leads
     */
    public function scopeHot($query)
    {
        return $query->where('score', '>=', config('reos.lead_scoring.thresholds.hot', 80));
    }

    /**
     * Scope for unassigned leads
     */
    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($lead) {
            if (!$lead->office_id && auth()->check()) {
                $lead->office_id = auth()->user()->office_id;
            }
            if (!$lead->status) {
                $lead->status = 'new';
            }
            if (!$lead->priority) {
                $lead->priority = 'medium';
            }
        });

        static::created(function ($lead) {
            event(new LeadCreated($lead));
        });

        static::updating(function ($lead) {
            // Recalculate score on update
            $lead->score = $lead->calculateScore();
        });

        static::updated(function ($lead) {
            event(new LeadUpdated($lead));
        });
    }
}
