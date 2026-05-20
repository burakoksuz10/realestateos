<?php

namespace Modules\CRM\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignEnrollment extends Model
{
    use HasFactory;

    protected $table = 'drip_enrollments';

    protected $fillable = [
        'campaign_id',
        'lead_id',
        'contact_id',
        'office_id',
        'enrolled_by_user_id',
        'status',
        'current_step_id',
        'steps_completed',
        'messages_sent',
        'enrolled_at',
        'next_run_at',
        'last_run_at',
        'completed_at',
        'last_error',
        'meta',
    ];

    protected $casts = [
        'enrolled_at'    => 'datetime',
        'next_run_at'    => 'datetime',
        'last_run_at'    => 'datetime',
        'completed_at'   => 'datetime',
        'meta'           => 'array',
        'steps_completed' => 'integer',
        'messages_sent'  => 'integer',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function currentStep(): BelongsTo
    {
        return $this->belongsTo(CampaignStep::class, 'current_step_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function enrolledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enrolled_by_user_id');
    }

    public function scopeDue($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('next_run_at')
                  ->orWhere('next_run_at', '<=', now());
            });
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
