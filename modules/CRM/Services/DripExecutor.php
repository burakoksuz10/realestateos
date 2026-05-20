<?php

namespace Modules\CRM\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\CRM\Models\Campaign;
use Modules\CRM\Models\CampaignEnrollment;
use Modules\CRM\Models\CampaignStep;
use Modules\CRM\Models\Contact;
use Modules\CRM\Models\Conversation;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\Task;
use Modules\Integrations\Channels\ChannelManager;

/**
 * Drip Campaign step executor.
 *
 * Step types:
 *   send_message → config: { channel, body, subject?, attachments? }
 *   wait         → config: { days?, hours?, minutes? }
 *   create_task  → config: { subject, description?, due_in_hours?, assigned_to? }
 *   branch       → config: { condition, value }
 *   ai_action    → config: { feature: 'follow_up_plan' | ... } [placeholder]
 */
class DripExecutor
{
    public function __construct(protected ChannelManager $channels) {}

    /**
     * Bir lead'i kampanyaya kaydet.
     */
    public function enroll(
        Campaign $campaign,
        Lead $lead,
        ?int $enrolledByUserId = null,
    ): ?CampaignEnrollment {
        if (!$campaign->is_active) {
            return null;
        }

        $first = $campaign->firstStep();
        if (!$first) {
            Log::info('Campaign has no steps, skipping enrollment', ['campaign' => $campaign->id]);
            return null;
        }

        // Aynı lead aynı kampanyaya iki kere enroll olmasın (unique key zaten var, race condition'ı esnek tutalım)
        $existing = CampaignEnrollment::where('campaign_id', $campaign->id)
            ->where('lead_id', $lead->id)
            ->first();
        if ($existing) {
            return $existing;
        }

        $enrollment = CampaignEnrollment::create([
            'campaign_id'         => $campaign->id,
            'lead_id'             => $lead->id,
            'contact_id'          => $lead->contact_id,
            'office_id'           => $lead->office_id ?? $campaign->office_id,
            'enrolled_by_user_id' => $enrolledByUserId,
            'status'              => 'active',
            'current_step_id'     => $first->id,
            'enrolled_at'         => now(),
            'next_run_at'         => now(),
        ]);

        $campaign->increment('enrollments_count');

        return $enrollment;
    }

    /**
     * Vadesi dolan enrollment'ları çalıştır.
     */
    public function tick(int $limit = 50): array
    {
        $due = CampaignEnrollment::due()->orderBy('next_run_at')->limit($limit)->get();

        $results = ['ran' => 0, 'completed' => 0, 'failed' => 0, 'skipped' => 0];

        foreach ($due as $enrollment) {
            try {
                $outcome = $this->processEnrollment($enrollment);
                $results[$outcome] = ($results[$outcome] ?? 0) + 1;
            } catch (\Throwable $e) {
                Log::error('Drip enrollment failed', [
                    'enrollment' => $enrollment->id,
                    'error' => $e->getMessage(),
                ]);
                $enrollment->update([
                    'status'     => 'failed',
                    'last_error' => mb_substr($e->getMessage(), 0, 1000),
                    'last_run_at' => now(),
                ]);
                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Bir enrollment'ı işle — wait step'ine ya da tamamlamaya kadar zincirleme step çalıştırır.
     */
    protected function processEnrollment(CampaignEnrollment $enrollment, int $maxStepsPerTick = 10): string
    {
        for ($i = 0; $i < $maxStepsPerTick; $i++) {
            // Tazele
            $enrollment->refresh();
            if ($enrollment->status !== 'active') {
                return 'skipped';
            }

            $step = $enrollment->current_step_id
                ? CampaignStep::find($enrollment->current_step_id)
                : $enrollment->campaign->firstStep();

            if (!$step) {
                $this->complete($enrollment);
                return 'completed';
            }

            $outcome = $this->runStep($enrollment, $step);

            if ($outcome === 'wait') {
                return 'ran';
            }
            if ($outcome === 'complete') {
                $this->complete($enrollment);
                return 'completed';
            }
            // 'advance' → loop yeniden
        }
        return 'ran';
    }

    /**
     * Tek bir step çalıştır — 'advance', 'wait' veya 'complete' döner.
     */
    protected function runStep(CampaignEnrollment $enrollment, CampaignStep $step): string
    {
        $context = $this->buildContext($enrollment);

        switch ($step->type) {
            case 'send_message':
                $this->doSendMessage($enrollment, $step, $context);
                return $this->advance($enrollment, $step);

            case 'wait':
                return $this->doWait($enrollment, $step);

            case 'create_task':
                $this->doCreateTask($enrollment, $step, $context);
                return $this->advance($enrollment, $step);

            case 'branch':
                // Basit ilk versiyon: koşul karşılanmazsa enrollment'ı sonlandır
                $ok = $this->evaluateCondition($step->config ?? [], $context);
                if (!$ok) {
                    return 'complete';
                }
                return $this->advance($enrollment, $step);

            case 'ai_action':
                // Placeholder — Faz 5'te genişletilecek
                Log::info('Drip ai_action step (placeholder)', ['step' => $step->id]);
                return $this->advance($enrollment, $step);

            default:
                Log::warning('Unknown campaign step type', ['type' => $step->type]);
                return $this->advance($enrollment, $step);
        }
    }

    protected function doSendMessage(CampaignEnrollment $enrollment, CampaignStep $step, array $context): void
    {
        $cfg = $step->config ?? [];
        $channelName = $cfg['channel'] ?? 'email';
        $bodyTemplate = $cfg['body'] ?? '';
        $body = $this->renderTemplate($bodyTemplate, $context);

        if (!$this->channels->has($channelName)) {
            throw new \RuntimeException("Channel not registered: {$channelName}");
        }
        $channel = $this->channels->get($channelName);
        if (!$channel->isEnabled()) {
            throw new \RuntimeException("Channel not configured: {$channelName}");
        }

        $conversation = $this->resolveConversationFor($enrollment, $channelName, $cfg);
        if (!$conversation) {
            throw new \RuntimeException("No reachable address for channel: {$channelName}");
        }

        $attachments = $cfg['attachments'] ?? [];

        $channel->send($conversation, $body, $attachments, null);

        $enrollment->increment('messages_sent');
    }

    protected function doWait(CampaignEnrollment $enrollment, CampaignStep $step): string
    {
        $waitSec = $step->waitSeconds();
        if ($waitSec <= 0) {
            // Sıfır bekleme — direkt advance et
            return $this->advance($enrollment, $step);
        }

        // İşaretçiyi bir sonraki step'e taşı, next_run_at'i wait sonrasına ayarla
        $next = $step->nextStep();
        $enrollment->update([
            'current_step_id' => $next?->id,
            'steps_completed' => $enrollment->steps_completed + 1,
            'last_run_at'     => now(),
            'next_run_at'     => now()->addSeconds($waitSec),
        ]);

        if (!$next) {
            return 'complete';
        }
        return 'wait';
    }

    protected function doCreateTask(CampaignEnrollment $enrollment, CampaignStep $step, array $context): void
    {
        $cfg = $step->config ?? [];
        $lead = $enrollment->lead;
        if (!$lead) return;

        $title = $this->renderTemplate($cfg['title'] ?? $cfg['subject'] ?? 'Kampanya görevi', $context);
        $description = $this->renderTemplate($cfg['description'] ?? '', $context);
        $dueInHours = (int) ($cfg['due_in_hours'] ?? 24);
        $assignedTo = $cfg['assigned_to'] ?? $lead->assigned_to;
        $due = now()->addHours($dueInHours);

        Task::create([
            'title'       => $title,
            'description' => $description,
            'type'        => $cfg['type'] ?? 'follow_up',
            'priority'    => $cfg['priority'] ?? 'medium',
            'status'      => 'pending',
            'lead_id'     => $lead->id,
            'contact_id'  => $lead->contact_id,
            'assigned_to' => $assignedTo,
            'reminder_at' => $due,
            'due_date'    => $due->toDateString(),
            'due_time'    => $due,
            'created_by'  => $enrollment->enrolled_by_user_id,
        ]);
    }

    protected function evaluateCondition(array $cfg, array $context): bool
    {
        $cond = $cfg['condition'] ?? null;
        $val  = $cfg['value']     ?? null;

        $lead = $context['lead'] ?? null;

        return match ($cond) {
            'lead_status_in'     => in_array($lead['status'] ?? null, (array) $val, true),
            'lead_status_not_in' => !in_array($lead['status'] ?? null, (array) $val, true),
            'lead_score_gte'     => (int) ($lead['score'] ?? 0) >= (int) $val,
            'lead_score_lt'      => (int) ($lead['score'] ?? 0) < (int) $val,
            default              => true,
        };
    }

    protected function advance(CampaignEnrollment $enrollment, CampaignStep $step): string
    {
        $next = $step->nextStep();
        $enrollment->update([
            'current_step_id' => $next?->id,
            'steps_completed' => $enrollment->steps_completed + 1,
            'last_run_at'     => now(),
            'next_run_at'     => $next ? now() : null,
        ]);

        return $next ? 'advance' : 'complete';
    }

    protected function complete(CampaignEnrollment $enrollment): void
    {
        $enrollment->update([
            'status'       => 'completed',
            'completed_at' => now(),
            'next_run_at'  => null,
        ]);
        $enrollment->campaign?->increment('completed_count');
    }

    /**
     * Mesaj göndermek için bir Conversation bul/oluştur.
     */
    protected function resolveConversationFor(
        CampaignEnrollment $enrollment,
        string $channel,
        array $cfg,
    ): ?Conversation {
        $contact = $enrollment->contact ?? $enrollment->lead?->contact;
        $lead = $enrollment->lead;

        // Telegram için mevcut conversation'a bağlı olmak zorunda (chat_id pairing gerektirir)
        if ($channel === 'telegram') {
            return Conversation::where('channel', 'telegram')
                ->where(function ($q) use ($lead, $contact) {
                    if ($lead) $q->orWhere('lead_id', $lead->id);
                    if ($contact) $q->orWhere('contact_id', $contact->id);
                })
                ->first();
        }

        $threadId = match ($channel) {
            'whatsapp', 'sms' => $contact?->phone,
            'email'           => $contact?->email,
            default           => null,
        };

        if (!$threadId) {
            return null;
        }

        return Conversation::firstOrCreate(
            [
                'channel'           => $channel,
                'channel_thread_id' => (string) $threadId,
            ],
            [
                'office_id'  => $enrollment->office_id,
                'contact_id' => $contact?->id,
                'lead_id'    => $lead?->id,
                'subject'    => $cfg['subject'] ?? null,
                'status'     => 'open',
            ],
        );
    }

    /**
     * Template context değişkenleri.
     */
    protected function buildContext(CampaignEnrollment $enrollment): array
    {
        $lead = $enrollment->lead;
        $contact = $enrollment->contact ?? $lead?->contact;
        $agent = $lead?->assignedTo;

        return [
            'contact' => $contact ? [
                'first_name' => $contact->first_name,
                'last_name'  => $contact->last_name,
                'full_name'  => $contact->full_name,
                'phone'      => $contact->phone,
                'email'      => $contact->email,
            ] : [],
            'lead' => $lead ? [
                'id'     => $lead->id,
                'status' => $lead->status,
                'score'  => $lead->score,
            ] : [],
            'agent' => $agent ? [
                'name'  => $agent->name,
                'phone' => $agent->phone ?? null,
                'email' => $agent->email,
            ] : [],
            'office' => $enrollment->office_id ? [
                'id' => $enrollment->office_id,
            ] : [],
        ];
    }

    /**
     * Basit {{path.to.value}} replacement — nested 1 seviye yeter.
     */
    protected function renderTemplate(string $template, array $context): string
    {
        return preg_replace_callback('/\{\{\s*([\w\.]+)\s*\}\}/', function ($m) use ($context) {
            $path = explode('.', $m[1]);
            $val = $context;
            foreach ($path as $key) {
                if (is_array($val) && array_key_exists($key, $val)) {
                    $val = $val[$key];
                } else {
                    return '';
                }
            }
            return is_scalar($val) ? (string) $val : '';
        }, $template) ?? $template;
    }
}
