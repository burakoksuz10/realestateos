<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiCredit extends Model
{
    protected $fillable = [
        'office_id',
        'monthly_quota',
        'used_this_month',
        'extra_credits',
        'period_start',
        'period_end',
    ];

    protected $casts = [
        'monthly_quota' => 'integer',
        'used_this_month' => 'integer',
        'extra_credits' => 'integer',
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    public function office(): BelongsTo
    {
        return $this->belongsTo(\Modules\Core\Models\Office::class);
    }

    public function remaining(): int
    {
        return max(0, ($this->monthly_quota + $this->extra_credits) - $this->used_this_month);
    }

    public function isExhausted(): bool
    {
        return $this->remaining() <= 0;
    }

    public function progressPercent(): int
    {
        $total = $this->monthly_quota + $this->extra_credits;
        if ($total <= 0) return 100;
        return (int) round(($this->used_this_month / $total) * 100);
    }
}
