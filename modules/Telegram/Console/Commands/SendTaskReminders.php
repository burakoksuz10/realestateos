<?php

namespace Modules\Telegram\Console\Commands;

use Illuminate\Console\Command;
use Modules\CRM\Models\Task;
use Modules\Telegram\Models\TelegramUser;
use Modules\Telegram\Services\TelegramService;

class SendTaskReminders extends Command
{
    protected $signature   = 'telegram:task-reminders';
    protected $description = 'Push task reminders to Telegram for tasks whose reminder_at just elapsed (5-min window).';

    public function handle(TelegramService $telegram): int
    {
        $from = now()->subMinutes(5);
        $to   = now();

        $tasks = Task::whereIn('status', ['pending', 'in_progress'])
            ->whereNotNull('reminder_at')
            ->whereBetween('reminder_at', [$from, $to])
            ->whereNotNull('assigned_to')
            ->get();

        if ($tasks->isEmpty()) {
            return self::SUCCESS;
        }

        $sent = 0;
        foreach ($tasks as $task) {
            $linked = TelegramUser::where('user_id', $task->assigned_to)
                ->where('is_active', true)
                ->exists();

            if (!$linked) continue;

            $emoji = match ($task->priority) {
                'urgent' => '🔴',
                'high'   => '🟠',
                'medium' => '🟡',
                default  => '⚪',
            };

            $body  = "⏰ <b>Görev hatırlatması</b>\n\n";
            $body .= "{$emoji} " . e($task->title) . "\n";
            if ($task->description) {
                $body .= e(mb_substr(strip_tags((string) $task->description), 0, 300)) . "\n";
            }
            if ($task->due_time) {
                $body .= "🕐 " . $task->due_time->format('H:i') . "\n";
            }
            if ($task->lead_id) {
                $body .= "Lead: #{$task->lead_id}\n";
            }

            $options = [];
            if ($task->lead_id) {
                $options['reply_markup'] = json_encode([
                    'inline_keyboard' => [[
                        ['text' => '📋 Lead detayı', 'callback_data' => "lead.open:{$task->lead_id}"],
                    ]],
                ]);
            }

            $sent += $telegram->notifyUser($task->assigned_to, $body, $options);
        }

        $this->info("Sent {$sent} task reminder(s) for {$tasks->count()} task(s).");
        return self::SUCCESS;
    }
}
