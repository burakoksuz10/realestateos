<?php

namespace Modules\CRM\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'drip_campaigns';

    protected $fillable = [
        'office_id',
        'created_by',
        'name',
        'slug',
        'description',
        'trigger',
        'trigger_config',
        'audience_filter',
        'is_active',
        'is_default',
        'enrollments_count',
        'completed_count',
    ];

    protected $casts = [
        'trigger_config' => 'array',
        'audience_filter' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'enrollments_count' => 'integer',
        'completed_count' => 'integer',
    ];

    public function steps(): HasMany
    {
        return $this->hasMany(CampaignStep::class)->orderBy('order');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(CampaignEnrollment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(\Modules\Core\Models\Office::class);
    }

    public function firstStep(): ?CampaignStep
    {
        return $this->steps()->first();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForTrigger($query, string $trigger)
    {
        return $query->where('trigger', $trigger);
    }
}
