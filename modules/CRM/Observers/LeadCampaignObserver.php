<?php

namespace Modules\CRM\Observers;

use Illuminate\Support\Facades\Log;
use Modules\CRM\Models\Campaign;
use Modules\CRM\Models\Lead;
use Modules\CRM\Services\DripExecutor;

/**
 * Yeni bir lead oluşturulduğunda trigger='lead_created' olan aktif kampanyalara enroll eder.
 * Lead modelindeki mevcut `created` static hook'u event dispatch ediyor; bu observer ona ek olarak
 * doğrudan kampanya enroll'unu yapar.
 */
class LeadCampaignObserver
{
    public function created(Lead $lead): void
    {
        try {
            $campaigns = Campaign::active()
                ->forTrigger('lead_created')
                ->where(function ($q) use ($lead) {
                    $q->whereNull('office_id')
                      ->orWhere('office_id', $lead->office_id);
                })
                ->get();

            if ($campaigns->isEmpty()) {
                return;
            }

            $executor = app(DripExecutor::class);

            foreach ($campaigns as $campaign) {
                if (!$this->matchesAudience($campaign, $lead)) {
                    continue;
                }
                $executor->enroll($campaign, $lead);
            }
        } catch (\Throwable $e) {
            Log::warning('LeadCampaignObserver enroll failed', [
                'lead'  => $lead->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function matchesAudience(Campaign $campaign, Lead $lead): bool
    {
        $filter = $campaign->audience_filter ?? [];
        if (empty($filter)) return true;

        if (!empty($filter['status_in']) && !in_array($lead->status, (array) $filter['status_in'], true)) {
            return false;
        }
        if (!empty($filter['source_in']) && !in_array($lead->source_type, (array) $filter['source_in'], true)) {
            return false;
        }
        if (isset($filter['score_gte']) && (int) $lead->score < (int) $filter['score_gte']) {
            return false;
        }
        return true;
    }
}
