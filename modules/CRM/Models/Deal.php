<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Deal extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'office_id',
        'contact_id',
        'lead_id',
        'assigned_to',
        'listing_id',
        'pipeline_id',
        'stage_id',
        
        // Deal Info
        'title',
        'status', // open, won, lost
        'deal_type', // sale, rent, lease
        'value',
        'currency',
        'probability', // 0-100
        
        // Commission
        'commission_rate',
        'commission_type', // percentage, fixed
        'commission_amount',
        'commission_split',
        
        // Partner Deal (MLS)
        'is_partner_deal',
        'partner_office_id',
        'partner_agent_id',
        'partner_commission_split',
        
        // Timeline
        'expected_close_date',
        'actual_close_date',
        
        // Documents
        'contract_signed',
        'contract_signed_at',
        'deposit_received',
        'deposit_amount',
        'deposit_received_at',
        
        // Meta
        'tags',
        'notes',
        'custom_fields',
        'won_reason',
        'lost_reason',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'probability' => 'integer',
            'commission_split' => 'array',
            'partner_commission_split' => 'array',
            'tags' => 'array',
            'custom_fields' => 'array',
            'is_partner_deal' => 'boolean',
            'contract_signed' => 'boolean',
            'deposit_received' => 'boolean',
            'expected_close_date' => 'date',
            'actual_close_date' => 'date',
            'contract_signed_at' => 'datetime',
            'deposit_received_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'stage_id', 'value', 'probability', 'assigned_to'])
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
     * Get the lead
     */
    public function lead()
    {
        return $this->belongsTo(Lead::class);
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
     * Get partner office
     */
    public function partnerOffice()
    {
        return $this->belongsTo(\Modules\Core\Models\Office::class, 'partner_office_id');
    }

    /**
     * Get partner agent
     */
    public function partnerAgent()
    {
        return $this->belongsTo(\App\Models\User::class, 'partner_agent_id');
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
     * Get formatted value
     */
    public function getFormattedValueAttribute(): string
    {
        $symbol = match($this->currency) {
            'TRY' => '₺',
            'USD' => '$',
            'EUR' => '€',
            default => $this->currency,
        };

        return $symbol . number_format($this->value, 0, ',', '.');
    }

    /**
     * Calculate commission
     */
    public function calculateCommission(): float
    {
        if ($this->commission_type === 'fixed') {
            return $this->commission_amount ?? 0;
        }

        return ($this->value * ($this->commission_rate ?? 0)) / 100;
    }

    /**
     * Get weighted value (value * probability)
     */
    public function getWeightedValueAttribute(): float
    {
        return $this->value * ($this->probability / 100);
    }

    /**
     * Mark as won
     */
    public function markAsWon(string $reason = null): void
    {
        $this->update([
            'status' => 'won',
            'probability' => 100,
            'won_reason' => $reason,
            'closed_at' => now(),
            'actual_close_date' => now(),
        ]);

        // Update listing status if applicable
        if ($this->listing) {
            $this->listing->update([
                'status' => $this->deal_type === 'sale' ? 'sold' : 'rented',
                $this->deal_type === 'sale' ? 'sold_at' : 'rented_at' => now(),
            ]);
        }
    }

    /**
     * Mark as lost
     */
    public function markAsLost(string $reason = null): void
    {
        $this->update([
            'status' => 'lost',
            'probability' => 0,
            'lost_reason' => $reason,
            'closed_at' => now(),
        ]);
    }

    /**
     * Scope for open deals
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope for won deals
     */
    public function scopeWon($query)
    {
        return $query->where('status', 'won');
    }

    /**
     * Scope for this month
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($deal) {
            if (!$deal->office_id && auth()->check()) {
                $deal->office_id = auth()->user()->office_id;
            }
            if (!$deal->status) {
                $deal->status = 'open';
            }
            if (!$deal->probability) {
                $deal->probability = 50;
            }
        });

        static::saving(function ($deal) {
            // Calculate commission amount
            if ($deal->commission_type === 'percentage' && $deal->commission_rate) {
                $deal->commission_amount = ($deal->value * $deal->commission_rate) / 100;
            }
        });
    }
}
