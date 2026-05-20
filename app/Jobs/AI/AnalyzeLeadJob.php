<?php

namespace App\Jobs\AI;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\AI\Services\AIService;
use Modules\AI\Services\CopilotService;
use Modules\CRM\Models\Lead;

class AnalyzeLeadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 90;
    public int $backoff = 30;

    public function __construct(
        public int $leadId,
        public ?int $userId = null,
    ) {
    }

    public function handle(AIService $ai, CopilotService $copilot): void
    {
        $lead = Lead::with(['contact', 'activities', 'listing', 'interestedListings'])->find($this->leadId);
        if (!$lead) {
            Log::warning('AnalyzeLeadJob: Lead not found', ['lead_id' => $this->leadId]);
            return;
        }

        $ai->withContext($lead->office_id, $this->userId, 'lead.analyze');

        $context = $this->buildContext($lead);

        $analysis = $ai->chatJson(
            messages: [
                [
                    'role' => 'system',
                    'content' => "Sen kıdemli bir emlak satış uzmanısın. Verilen lead profilini analiz et ve JSON formatında çıktı üret. Format:\n"
                        . "{\n"
                        . '  "score": 0-100,'
                        . "\n"
                        . '  "temperature": "hot|warm|cold",'
                        . "\n"
                        . '  "intent": "buy|rent|sell|invest|info",'
                        . "\n"
                        . '  "urgency": "immediate|soon|exploring",'
                        . "\n"
                        . '  "intent_signals": ["..."],'
                        . "\n"
                        . '  "strengths": ["..."],'
                        . "\n"
                        . '  "risks": ["..."],'
                        . "\n"
                        . '  "next_best_actions": [{"action":"call|whatsapp|email|meeting","priority":"high|medium|low","title":"...","reason":"..."}],'
                        . "\n"
                        . '  "suggested_listings_criteria": {"type":"...","city":"...","district":"...","min_rooms":n,"max_price":n},'
                        . "\n"
                        . '  "summary_tr": "2-3 cümlelik özet"'
                        . "\n"
                        . '}',
                ],
                [
                    'role' => 'user',
                    'content' => "Lead profili:\n" . $context,
                ],
            ],
        );

        if (!$analysis) {
            Log::info('AnalyzeLeadJob: empty analysis (AI not configured or error)', ['lead_id' => $lead->id]);
            return;
        }

        $lead->update([
            'ai_score'        => (int) ($analysis['score'] ?? $lead->score),
            'ai_analysis'     => $analysis,
            'ai_suggestions'  => $analysis['next_best_actions'] ?? [],
            'intent_signals'  => $analysis['intent_signals'] ?? $lead->intent_signals,
        ]);
    }

    protected function buildContext(Lead $lead): string
    {
        $contact = $lead->contact;
        $name = $contact ? trim(($contact->first_name ?? '') . ' ' . ($contact->last_name ?? '')) : 'Bilinmeyen';
        $locations = is_array($lead->preferred_locations) ? implode(', ', $lead->preferred_locations) : (string) $lead->preferred_locations;

        $activities = $lead->activities->take(10)->map(function ($a) {
            return "- [{$a->type}] {$a->subject}: " . ($a->description ?? '');
        })->implode("\n");

        return "İsim: {$name}\n"
            . "Kaynak: {$lead->source} ({$lead->source_detail})\n"
            . "İlgi: {$lead->interest_type} - {$lead->property_type}\n"
            . "Bütçe: {$lead->budget_min} - {$lead->budget_max} {$lead->budget_currency}\n"
            . "Tercih edilen yerler: {$locations}\n"
            . "Oda gereksinimi: {$lead->room_requirement}\n"
            . "Boyut: {$lead->size_min} - {$lead->size_max} m²\n"
            . "Aciliyet: {$lead->urgency}\n"
            . "Notlar: {$lead->requirements_notes}\n"
            . "Statü: {$lead->status} | Nitelikli: " . ($lead->is_qualified ? 'evet' : 'hayır') . "\n"
            . "Son aktivite: " . ($lead->last_activity_at ?? 'yok') . "\n"
            . "Aktivite geçmişi:\n" . ($activities ?: '- (henüz yok)');
    }
}
