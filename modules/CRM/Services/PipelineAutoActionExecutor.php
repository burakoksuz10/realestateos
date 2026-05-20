<?php

namespace Modules\CRM\Services;

use Illuminate\Support\Facades\Log;
use Modules\CRM\Models\Campaign;
use Modules\CRM\Models\Deal;
use Modules\CRM\Models\PipelineStage;
use Modules\CRM\Models\Task;
use Modules\Telegram\Services\TelegramService;

/**
 * Pipeline stage auto-action executor.
 *
 * `PipelineStage::auto_actions` JSON kolonunda tanımlı aksiyonları,
 * bir deal o stage'e girdiğinde sırayla çalıştırır.
 *
 * Desteklenen aksiyon tipleri:
 *   - create_task        { title, description?, due_in_hours?, priority?, assigned_to? = deal owner }
 *   - notify_agent       { message }
 *   - notify_office      { message }
 *   - set_field          { field, value }            // deal kolonu
 *   - enroll_campaign    { campaign_id | campaign_slug }
 *   - update_probability { probability }             // stage probability'sini ezer
 *
 * Aksiyon objeleri: [{ "type": "create_task", ...params }, ...]
 */
class PipelineAutoActionExecutor
{
    public function __construct(protected TelegramService $telegram) {}

    /**
     * Bir deal yeni stage'e girdiğinde çağrılır.
     */
    public function onStageEntered(Deal $deal, PipelineStage $stage): array
    {
        $actions = $stage->auto_actions ?? [];
        if (!is_array($actions) || empty($actions)) {
            return ['ran' => 0];
        }

        $ran = 0;
        $failed = 0;

        foreach ($actions as $action) {
            if (!is_array($action) || empty($action['type'])) {
                continue;
            }
            try {
                $this->runAction($deal, $stage, $action);
                $ran++;
            } catch (\Throwable $e) {
                $failed++;
                Log::error('Pipeline auto-action failed', [
                    'deal'   => $deal->id,
                    'stage'  => $stage->id,
                    'type'   => $action['type'],
                    'error'  => $e->getMessage(),
                ]);
            }
        }

        return ['ran' => $ran, 'failed' => $failed];
    }

    protected function runAction(Deal $deal, PipelineStage $stage, array $action): void
    {
        $context = $this->buildContext($deal, $stage);

        switch ($action['type']) {
            case 'create_task':
                $this->createTask($deal, $action, $context);
                return;

            case 'notify_agent':
                $this->notifyAgent($deal, $action, $context);
                return;

            case 'notify_office':
                $this->notifyOffice($deal, $action, $context);
                return;

            case 'set_field':
                $this->setField($deal, $action);
                return;

            case 'enroll_campaign':
                $this->enrollCampaign($deal, $action);
                return;

            case 'update_probability':
                $this->updateProbability($deal, $action);
                return;

            default:
                Log::warning('Unknown pipeline auto-action type', ['type' => $action['type']]);
        }
    }

    protected function createTask(Deal $deal, array $action, array $context): void
    {
        $title = $this->render($action['title'] ?? 'Stage görevi', $context);
        $description = $this->render($action['description'] ?? '', $context);
        $dueInHours = (int) ($action['due_in_hours'] ?? 24);
        $priority = $action['priority'] ?? 'medium';
        $assignedTo = $action['assigned_to'] ?? $deal->assigned_to;
        $due = now()->addHours($dueInHours);

        Task::create([
            'title'       => $title,
            'description' => $description,
            'type'        => $action['task_type'] ?? 'follow_up',
            'priority'    => $priority,
            'status'      => 'pending',
            'lead_id'     => $deal->lead_id,
            'contact_id'  => $deal->contact_id,
            'deal_id'     => $deal->id,
            'assigned_to' => $assignedTo,
            'reminder_at' => $due,
            'due_date'    => $due->toDateString(),
            'due_time'    => $due,
            'created_by'  => $deal->assigned_to,
        ]);
    }

    protected function notifyAgent(Deal $deal, array $action, array $context): void
    {
        if (!$deal->assigned_to) return;
        $message = $this->render($action['message'] ?? '', $context);
        if ($message === '') return;
        $this->telegram->notifyUser($deal->assigned_to, $message);
    }

    protected function notifyOffice(Deal $deal, array $action, array $context): void
    {
        if (!$deal->office_id) return;
        $message = $this->render($action['message'] ?? '', $context);
        if ($message === '') return;
        $this->telegram->notifyOffice($deal->office_id, $message);
    }

    protected function setField(Deal $deal, array $action): void
    {
        $field = $action['field'] ?? null;
        if (!$field) return;
        // Whitelist — yalnız güvenli kolonlar
        $allowed = ['probability', 'value', 'expected_close_date', 'won_reason', 'lost_reason', 'notes'];
        if (!in_array($field, $allowed, true)) {
            Log::warning('set_field rejected — column not whitelisted', ['field' => $field]);
            return;
        }
        $deal->update([$field => $action['value'] ?? null]);
    }

    protected function updateProbability(Deal $deal, array $action): void
    {
        $p = (int) ($action['probability'] ?? -1);
        if ($p < 0 || $p > 100) return;
        $deal->update(['probability' => $p]);
    }

    protected function enrollCampaign(Deal $deal, array $action): void
    {
        if (!$deal->lead_id) return;
        $lead = $deal->lead;
        if (!$lead) return;

        $campaign = null;
        if (!empty($action['campaign_id'])) {
            $campaign = Campaign::find($action['campaign_id']);
        } elseif (!empty($action['campaign_slug'])) {
            $campaign = Campaign::where('slug', $action['campaign_slug'])->first();
        }

        if (!$campaign || !$campaign->is_active) return;

        app(DripExecutor::class)->enroll($campaign, $lead);
    }

    protected function buildContext(Deal $deal, PipelineStage $stage): array
    {
        $contact = $deal->contact;
        $lead = $deal->lead;
        $agent = $deal->assignedTo;
        $listing = $deal->listing;

        return [
            'deal' => [
                'id'        => $deal->id,
                'title'     => $deal->title,
                'value'     => $deal->value,
                'currency'  => $deal->currency,
                'formatted_value' => $deal->formatted_value ?? null,
                'probability' => $deal->probability,
            ],
            'stage' => [
                'id'   => $stage->id,
                'name' => $stage->name,
            ],
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
                'email' => $agent->email,
            ] : [],
            'listing' => $listing ? [
                'id'        => $listing->id,
                'reference' => $listing->reference ?? null,
                'title'     => $listing->title ?? null,
            ] : [],
        ];
    }

    protected function render(string $template, array $context): string
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
