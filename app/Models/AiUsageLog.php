<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsageLog extends Model
{
    protected $fillable = [
        'office_id',
        'user_id',
        'feature',
        'model',
        'kind',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'cost_usd',
        'latency_ms',
        'status',
        'error',
    ];

    protected $casts = [
        'cost_usd' => 'float',
        'prompt_tokens' => 'integer',
        'completion_tokens' => 'integer',
        'total_tokens' => 'integer',
        'latency_ms' => 'integer',
    ];

    public function office(): BelongsTo
    {
        return $this->belongsTo(\Modules\Core\Models\Office::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForOffice($query, ?int $officeId)
    {
        return $officeId ? $query->where('office_id', $officeId) : $query;
    }

    public function scopeThisMonth($query)
    {
        return $query->where('created_at', '>=', now()->startOfMonth());
    }
}
