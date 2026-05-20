<?php

namespace Modules\Telegram\Console\Commands;

use Illuminate\Console\Command;
use Modules\AI\Services\DailyPlannerService;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\Task;
use Modules\Telegram\Models\TelegramUser;
use Modules\Telegram\Services\TelegramService;

class SendMorningBriefing extends Command
{
    protected $signature   = 'telegram:morning-briefing {--user=}';
    protected $description = 'Send each linked agent a personalised 08:30 daily briefing on Telegram.';

    public function handle(TelegramService $telegram, DailyPlannerService $planner): int
    {
        $query = TelegramUser::with('user')
            ->where('is_active', true)
            ->whereNotNull('telegram_chat_id')
            ->whereNotNull('user_id');

        if ($this->option('user')) {
            $query->where('user_id', (int) $this->option('user'));
        }

        $sent = 0;
        $query->each(function (TelegramUser $tu) use ($telegram, $planner, &$sent) {
            $userId = $tu->user_id;

            $todayTasks = Task::where('assigned_to', $userId)
                ->whereDate('due_date', today())
                ->whereIn('status', ['pending', 'in_progress'])
                ->orderByRaw("FIELD(priority, 'urgent','high','medium','low')")
                ->limit(10)
                ->get();

            $hotLeads = Lead::where('assigned_to', $userId)
                ->whereNotIn('status', ['converted', 'lost'])
                ->where(function ($q) {
                    $q->where('ai_score', '>=', 80)->orWhere('score', '>=', 80);
                })
                ->orderByDesc('ai_score')
                ->limit(5)
                ->get();

            $staleLeads = Lead::where('assigned_to', $userId)
                ->whereNotIn('status', ['converted', 'lost'])
                ->where(function ($q) {
                    $q->whereNull('last_activity_at')->orWhere('last_activity_at', '<', now()->subDays(7));
                })
                ->orderBy('last_activity_at')
                ->limit(3)
                ->get();

            // Don't spam: if there is nothing actionable, skip the briefing.
            if ($todayTasks->isEmpty() && $hotLeads->isEmpty() && $staleLeads->isEmpty()) {
                return;
            }

            $name = $tu->user?->name ? explode(' ', $tu->user->name)[0] : 'merhaba';
            $body  = "🌅 <b>Günaydın, {$name}!</b>\n";
            $body .= today()->translatedFormat('d F Y, l') . "\n\n";

            if ($todayTasks->isNotEmpty()) {
                $body .= "📋 <b>Bugünkü görevler (" . $todayTasks->count() . ")</b>\n";
                foreach ($todayTasks as $task) {
                    $emoji = match ($task->priority) {
                        'urgent' => '🔴',
                        'high'   => '🟠',
                        'medium' => '🟡',
                        default  => '⚪',
                    };
                    $time = $task->due_time?->format('H:i') ?? '';
                    $body .= "{$emoji} " . e($task->title) . ($time ? " — {$time}" : '') . "\n";
                }
                $body .= "\n";
            }

            if ($hotLeads->isNotEmpty()) {
                $body .= "🔥 <b>Sıcak lead'ler</b>\n";
                foreach ($hotLeads as $lead) {
                    $score = (int) ($lead->ai_score ?: $lead->score ?: 0);
                    $body .= "• #{$lead->id} " . e($lead->title ?: 'Lead') . " — <b>{$score}</b>\n";
                }
                $body .= "\n";
            }

            if ($staleLeads->isNotEmpty()) {
                $body .= "⏳ <b>Uzun süredir hareket yok</b>\n";
                foreach ($staleLeads as $lead) {
                    $last = $lead->last_activity_at ? $lead->last_activity_at->diffForHumans() : 'hiç temas yok';
                    $body .= "• #{$lead->id} " . e($lead->title ?: 'Lead') . " — {$last}\n";
                }
                $body .= "\n";
            }

            // AI günlük plan — en yüksek değerli 3 aksiyon
            try {
                $plan = $planner->generateForAgent($tu->user, force: false);
                $priorities = array_slice($plan['priorities'] ?? [], 0, 3);
                if (!empty($priorities)) {
                    $body .= "⚡ <b>AI önerileri</b>\n";
                    foreach ($priorities as $i => $p) {
                        $action = e($p['action'] ?? '');
                        $impact = e($p['impact'] ?? '');
                        $body .= ($i + 1) . ". {$action}";
                        if ($impact) $body .= " <i>({$impact})</i>";
                        $body .= "\n";
                    }
                    $body .= "\n";
                }
            } catch (\Throwable $e) {
                // AI yoksa veya patladıysa sessizce geç
            }

            $body .= "Komutlar: /today /leads /hot";

            if ($telegram->sendMessage($tu->telegram_chat_id, $body)) {
                $sent++;
            }
        });

        $this->info("Morning briefing sent to {$sent} agent(s).");
        return self::SUCCESS;
    }
}
