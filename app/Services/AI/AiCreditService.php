<?php

namespace App\Services\AI;

use App\Models\AiCredit;
use Illuminate\Support\Facades\DB;

class AiCreditService
{
    /**
     * Ensure an office has a credit row; create one with defaults if missing.
     */
    public function ensure(?int $officeId): ?AiCredit
    {
        if (!$officeId) return null;

        return AiCredit::firstOrCreate(
            ['office_id' => $officeId],
            [
                'monthly_quota'   => (int) config('reos.ai.credits.default_monthly_quota', 500),
                'used_this_month' => 0,
                'extra_credits'   => 0,
                'period_start'    => now()->startOfMonth(),
                'period_end'      => now()->endOfMonth(),
            ]
        );
    }

    /**
     * Does the office have at least $units credits available?
     * When officeId is null we allow it (system jobs).
     */
    public function hasAvailable(?int $officeId, int $units = 1): bool
    {
        if (!$officeId) return true;
        $credit = $this->ensure($officeId);
        $this->rolloverIfNeeded($credit);
        return $credit && $credit->remaining() >= $units;
    }

    /**
     * Consume $units. Returns true on success, false when insufficient.
     */
    public function consume(?int $officeId, int $units = 1): bool
    {
        if (!$officeId) return true;

        return DB::transaction(function () use ($officeId, $units) {
            $credit = AiCredit::lockForUpdate()->firstOrCreate(
                ['office_id' => $officeId],
                [
                    'monthly_quota' => (int) config('reos.ai.credits.default_monthly_quota', 500),
                    'used_this_month' => 0,
                    'extra_credits' => 0,
                    'period_start' => now()->startOfMonth(),
                    'period_end' => now()->endOfMonth(),
                ]
            );
            $this->rolloverIfNeeded($credit);
            if ($credit->remaining() < $units) {
                return false;
            }
            $credit->increment('used_this_month', $units);
            return true;
        });
    }

    /**
     * Add bonus credits (e.g. purchased pack).
     */
    public function grantExtra(?int $officeId, int $units): bool
    {
        if (!$officeId || $units <= 0) return false;
        $credit = $this->ensure($officeId);
        $credit->increment('extra_credits', $units);
        return true;
    }

    /**
     * Roll the period over when the month flipped.
     */
    public function rolloverIfNeeded(AiCredit $credit): void
    {
        if (!$credit->period_end || now()->gt($credit->period_end)) {
            $credit->update([
                'used_this_month' => 0,
                'extra_credits'   => 0,
                'period_start'    => now()->startOfMonth(),
                'period_end'      => now()->endOfMonth(),
            ]);
        }
    }

    /**
     * Reset for all offices (cron task).
     */
    public function resetAll(): int
    {
        return AiCredit::query()->update([
            'used_this_month' => 0,
            'extra_credits'   => 0,
            'period_start'    => now()->startOfMonth(),
            'period_end'      => now()->endOfMonth(),
        ]);
    }
}
