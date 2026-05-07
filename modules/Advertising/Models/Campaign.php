<?php

namespace Modules\Advertising\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'hedef',
        'durum',
        'budget',
        'city',
        'external_id',
        'latest_ai_analysis',
        'health_score',
    ];

    protected $casts = [
        'latest_ai_analysis' => 'array',
        'budget' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function metrics()
    {
        return $this->hasMany(CampaignMetric::class);
    }

    public function getTotalsAttribute(): array
    {
        $metrics = $this->metrics;
        return [
            'harcama' => $metrics->sum('harcama'),
            'erisme' => $metrics->sum('erisme'),
            'result_count' => $metrics->sum('lead') + $metrics->sum('mesaj') + $metrics->sum('donusum'),
            'cost_per_result' => ($metrics->sum('lead') + $metrics->sum('mesaj') + $metrics->sum('donusum')) > 0
                ? $metrics->sum('harcama') / ($metrics->sum('lead') + $metrics->sum('mesaj') + $metrics->sum('donusum'))
                : 0,
        ];
    }
}
