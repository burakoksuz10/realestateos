<?php

namespace Modules\AI\Services;

use Modules\CRM\Models\Lead;
use Modules\CRM\Models\Contact;
use Modules\CRM\Models\Deal;
use Modules\RealEstate\Models\Listing;
use App\Models\User;

class CopilotService
{
    protected AIService $ai;

    public function __construct(AIService $ai)
    {
        $this->ai = $ai;
    }

    /**
     * Get AI suggestions for a new lead
     */
    public function getLeadSuggestions(Lead $lead): array
    {
        $contact = $lead->contact;
        $listing = $lead->listing;
        
        // Get matching listings
        $matchingListings = $this->getMatchingListings($lead);
        
        // Generate communication templates
        $templates = $this->generateCommunicationTemplates($lead, $contact);
        
        // Suggest next actions
        $nextActions = $this->suggestNextActions($lead);
        
        // Generate follow-up plan
        $followUpPlan = $this->generateFollowUpPlan($lead);

        return [
            'matching_listings' => $matchingListings,
            'communication_templates' => $templates,
            'next_actions' => $nextActions,
            'follow_up_plan' => $followUpPlan,
            'lead_score_analysis' => $this->analyzeLeadScore($lead),
            'buyer_intent_signals' => $this->analyzeBuyerIntent($lead),
        ];
    }

    /**
     * Get matching listings for a lead
     */
    protected function getMatchingListings(Lead $lead): array
    {
        $query = Listing::active();

        // Filter by interest type
        if ($lead->interest_type === 'buy') {
            $query->forSale();
        } elseif ($lead->interest_type === 'rent') {
            $query->forRent();
        }

        // Filter by budget
        if ($lead->budget_min) {
            $query->where('price', '>=', $lead->budget_min);
        }
        if ($lead->budget_max) {
            $query->where('price', '<=', $lead->budget_max);
        }

        // Filter by property type
        if ($lead->property_type) {
            $query->where('type', $lead->property_type);
        }

        // Filter by locations
        if (!empty($lead->preferred_locations)) {
            $query->where(function ($q) use ($lead) {
                foreach ($lead->preferred_locations as $location) {
                    $q->orWhere('city', 'like', "%{$location}%")
                      ->orWhere('district', 'like', "%{$location}%");
                }
            });
        }

        // Filter by room requirement
        if ($lead->room_requirement) {
            $query->where('room_count', '>=', $lead->room_requirement);
        }

        // Filter by size
        if ($lead->size_min) {
            $query->where('gross_sqm', '>=', $lead->size_min);
        }
        if ($lead->size_max) {
            $query->where('gross_sqm', '<=', $lead->size_max);
        }

        $listings = $query->orderBy('quality_score', 'desc')
            ->take(10)
            ->get();

        return $listings->map(function ($listing) use ($lead) {
            return [
                'id' => $listing->id,
                'title' => $listing->title,
                'price' => $listing->formatted_price,
                'location' => $listing->full_location,
                'room_info' => $listing->room_info,
                'gross_sqm' => $listing->gross_sqm,
                'match_score' => $this->calculateMatchScore($listing, $lead),
                'thumbnail' => $listing->getFirstMediaUrl('photos', 'thumb'),
            ];
        })->sortByDesc('match_score')->values()->toArray();
    }

    /**
     * Calculate match score between listing and lead
     */
    protected function calculateMatchScore(Listing $listing, Lead $lead): int
    {
        $score = 50; // Base score

        // Budget match
        if ($lead->budget_min && $lead->budget_max) {
            if ($listing->price >= $lead->budget_min && $listing->price <= $lead->budget_max) {
                $score += 20;
            } elseif ($listing->price <= $lead->budget_max * 1.1) {
                $score += 10;
            }
        }

        // Location match
        if (!empty($lead->preferred_locations)) {
            foreach ($lead->preferred_locations as $location) {
                if (stripos($listing->city, $location) !== false || 
                    stripos($listing->district, $location) !== false) {
                    $score += 15;
                    break;
                }
            }
        }

        // Room match
        if ($lead->room_requirement && $listing->room_count >= $lead->room_requirement) {
            $score += 10;
        }

        // Size match
        if ($lead->size_min && $lead->size_max) {
            if ($listing->gross_sqm >= $lead->size_min && $listing->gross_sqm <= $lead->size_max) {
                $score += 10;
            }
        }

        // Quality bonus
        $score += ($listing->quality_score / 20);

        return min(100, $score);
    }

    /**
     * Generate communication templates
     */
    protected function generateCommunicationTemplates(Lead $lead, ?Contact $contact): array
    {
        $name = $contact ? $contact->first_name : 'Değerli Müşterimiz';
        $source = $lead->source;
        
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a professional real estate agent assistant. Generate communication templates in Turkish. Return JSON with: whatsapp_message, email_subject, email_body, call_script, sms_message.'
            ],
            [
                'role' => 'user',
                'content' => "Generate professional communication templates for a new lead:
- Name: {$name}
- Source: {$source}
- Interest: {$lead->interest_type}
- Budget: {$lead->budget_min} - {$lead->budget_max} {$lead->budget_currency}
- Property Type: {$lead->property_type}
- Locations: " . implode(', ', $lead->preferred_locations ?? []) . "
- Urgency: {$lead->urgency}
- Notes: {$lead->requirements_notes}"
            ]
        ];

        $templates = $this->ai->chatJson($messages);

        return $templates ?? [
            'whatsapp_message' => "Merhaba {$name}, emlak talebiniz için teşekkür ederiz. Size en uygun seçenekleri sunmak için hazırız. Müsait olduğunuzda görüşebilir miyiz?",
            'email_subject' => "Emlak Talebiniz Hakkında - RE-OS",
            'email_body' => "Sayın {$name},\n\nEmlak talebiniz tarafımıza ulaşmıştır. En kısa sürede sizinle iletişime geçeceğiz.\n\nSaygılarımızla",
            'call_script' => "Merhaba, ben [İsim], RE-OS'tan arıyorum. Emlak talebinizi aldık ve size yardımcı olmak istiyoruz.",
            'sms_message' => "Merhaba {$name}, emlak talebiniz alındı. En kısa sürede sizinle iletişime geçeceğiz. RE-OS",
        ];
    }

    /**
     * Suggest next actions for a lead
     */
    protected function suggestNextActions(Lead $lead): array
    {
        $actions = [];
        
        // Check if first contact made
        if (!$lead->first_response_at) {
            $actions[] = [
                'type' => 'call',
                'priority' => 'high',
                'title' => 'İlk iletişimi kur',
                'description' => 'Lead ile henüz iletişim kurulmamış. Hemen arayın veya WhatsApp mesajı gönderin.',
                'suggested_time' => now()->addMinutes(5)->format('H:i'),
            ];
        }

        // Check lead temperature
        if ($lead->temperature === 'hot') {
            $actions[] = [
                'type' => 'meeting',
                'priority' => 'high',
                'title' => 'Randevu ayarla',
                'description' => 'Sıcak lead - hemen randevu teklif edin.',
            ];
        }

        // Check if listings sent
        $listingsSent = $lead->activities()
            ->where('type', 'email')
            ->where('subject', 'like', '%ilan%')
            ->exists();

        if (!$listingsSent) {
            $actions[] = [
                'type' => 'email',
                'priority' => 'medium',
                'title' => 'Uygun ilanları gönder',
                'description' => 'Müşterinin kriterlerine uygun ilanları e-posta ile paylaşın.',
            ];
        }

        // Check qualification status
        if (!$lead->is_qualified) {
            $actions[] = [
                'type' => 'call',
                'priority' => 'medium',
                'title' => 'Lead\'i nitelendir',
                'description' => 'Bütçe, zaman çizelgesi ve gereksinimleri doğrulayın.',
            ];
        }

        // Check last activity
        $daysSinceActivity = $lead->last_activity_at 
            ? $lead->last_activity_at->diffInDays(now()) 
            : $lead->created_at->diffInDays(now());

        if ($daysSinceActivity >= 3) {
            $actions[] = [
                'type' => 'follow_up',
                'priority' => 'high',
                'title' => 'Takip et',
                'description' => "{$daysSinceActivity} gündür aktivite yok. Takip araması yapın.",
            ];
        }

        return $actions;
    }

    /**
     * Generate follow-up plan
     */
    protected function generateFollowUpPlan(Lead $lead): array
    {
        $urgency = $lead->urgency ?? 'exploring';
        
        $plans = [
            'immediate' => [
                ['day' => 0, 'action' => 'call', 'note' => 'İlk arama - ihtiyaçları belirle'],
                ['day' => 1, 'action' => 'whatsapp', 'note' => 'Uygun ilanları gönder'],
                ['day' => 2, 'action' => 'call', 'note' => 'Randevu ayarla'],
                ['day' => 3, 'action' => 'showing', 'note' => 'Yer gösterimi'],
                ['day' => 5, 'action' => 'call', 'note' => 'Geri bildirim al'],
            ],
            '1_month' => [
                ['day' => 0, 'action' => 'call', 'note' => 'İlk arama'],
                ['day' => 2, 'action' => 'email', 'note' => 'İlan listesi gönder'],
                ['day' => 5, 'action' => 'whatsapp', 'note' => 'Takip mesajı'],
                ['day' => 7, 'action' => 'call', 'note' => 'Randevu teklifi'],
                ['day' => 14, 'action' => 'email', 'note' => 'Yeni ilanlar'],
            ],
            '3_months' => [
                ['day' => 0, 'action' => 'call', 'note' => 'İlk arama'],
                ['day' => 3, 'action' => 'email', 'note' => 'Bilgilendirme'],
                ['day' => 7, 'action' => 'whatsapp', 'note' => 'Takip'],
                ['day' => 14, 'action' => 'email', 'note' => 'Piyasa raporu'],
                ['day' => 30, 'action' => 'call', 'note' => 'Durum kontrolü'],
            ],
            'exploring' => [
                ['day' => 0, 'action' => 'email', 'note' => 'Hoş geldin e-postası'],
                ['day' => 3, 'action' => 'whatsapp', 'note' => 'Tanışma mesajı'],
                ['day' => 7, 'action' => 'email', 'note' => 'Piyasa bilgisi'],
                ['day' => 14, 'action' => 'email', 'note' => 'Öne çıkan ilanlar'],
                ['day' => 30, 'action' => 'call', 'note' => 'Durum kontrolü'],
            ],
        ];

        $plan = $plans[$urgency] ?? $plans['exploring'];

        return array_map(function ($item) {
            $item['scheduled_date'] = now()->addDays($item['day'])->format('Y-m-d');
            return $item;
        }, $plan);
    }

    /**
     * Analyze lead score
     */
    protected function analyzeLeadScore(Lead $lead): array
    {
        $score = $lead->score;
        $factors = [];

        // Response time
        if ($lead->first_response_at) {
            $responseMinutes = $lead->created_at->diffInMinutes($lead->first_response_at);
            $factors['response_time'] = [
                'value' => $responseMinutes,
                'label' => $responseMinutes <= 5 ? 'Mükemmel' : ($responseMinutes <= 30 ? 'İyi' : 'Geliştirilebilir'),
                'impact' => $responseMinutes <= 5 ? 'positive' : ($responseMinutes <= 30 ? 'neutral' : 'negative'),
            ];
        }

        // Engagement
        $activityCount = $lead->activities()->count();
        $factors['engagement'] = [
            'value' => $activityCount,
            'label' => $activityCount >= 5 ? 'Yüksek' : ($activityCount >= 2 ? 'Orta' : 'Düşük'),
            'impact' => $activityCount >= 5 ? 'positive' : ($activityCount >= 2 ? 'neutral' : 'negative'),
        ];

        // Budget clarity
        $hasBudget = $lead->budget_min || $lead->budget_max;
        $factors['budget'] = [
            'value' => $hasBudget,
            'label' => $hasBudget ? 'Belirli' : 'Belirsiz',
            'impact' => $hasBudget ? 'positive' : 'negative',
        ];

        // Location preference
        $hasLocation = !empty($lead->preferred_locations);
        $factors['location'] = [
            'value' => $hasLocation,
            'label' => $hasLocation ? 'Belirli' : 'Belirsiz',
            'impact' => $hasLocation ? 'positive' : 'neutral',
        ];

        return [
            'score' => $score,
            'temperature' => $lead->temperature,
            'factors' => $factors,
            'improvement_tips' => $this->getScoreImprovementTips($factors),
        ];
    }

    /**
     * Get score improvement tips
     */
    protected function getScoreImprovementTips(array $factors): array
    {
        $tips = [];

        if (isset($factors['response_time']) && $factors['response_time']['impact'] === 'negative') {
            $tips[] = 'Yanıt süresini kısaltın - 5 dakika içinde ilk iletişimi kurun.';
        }

        if (isset($factors['engagement']) && $factors['engagement']['impact'] === 'negative') {
            $tips[] = 'Daha fazla etkileşim sağlayın - düzenli takip yapın.';
        }

        if (isset($factors['budget']) && $factors['budget']['impact'] === 'negative') {
            $tips[] = 'Bütçe bilgisini netleştirin.';
        }

        if (isset($factors['location']) && $factors['location']['impact'] === 'neutral') {
            $tips[] = 'Tercih edilen lokasyonları belirleyin.';
        }

        return $tips;
    }

    /**
     * Analyze buyer intent signals
     */
    protected function analyzeBuyerIntent(Lead $lead): array
    {
        $signals = [];
        $intentScore = 50;

        // Check urgency
        if ($lead->urgency === 'immediate') {
            $signals[] = ['type' => 'positive', 'signal' => 'Acil alım ihtiyacı'];
            $intentScore += 20;
        } elseif ($lead->urgency === '1_month') {
            $signals[] = ['type' => 'positive', 'signal' => '1 ay içinde alım planı'];
            $intentScore += 15;
        }

        // Check if qualified
        if ($lead->is_qualified) {
            $signals[] = ['type' => 'positive', 'signal' => 'Nitelikli lead'];
            $intentScore += 15;
        }

        // Check budget
        if ($lead->budget_min && $lead->budget_max) {
            $signals[] = ['type' => 'positive', 'signal' => 'Net bütçe aralığı'];
            $intentScore += 10;
        }

        // Check activity level
        $recentActivities = $lead->activities()
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        if ($recentActivities >= 3) {
            $signals[] = ['type' => 'positive', 'signal' => 'Yüksek aktivite'];
            $intentScore += 10;
        } elseif ($recentActivities === 0) {
            $signals[] = ['type' => 'negative', 'signal' => 'Son 7 günde aktivite yok'];
            $intentScore -= 10;
        }

        // Check showings
        $showings = $lead->activities()->where('type', 'showing')->count();
        if ($showings > 0) {
            $signals[] = ['type' => 'positive', 'signal' => "{$showings} yer gösterimi yapıldı"];
            $intentScore += $showings * 5;
        }

        return [
            'intent_score' => min(100, max(0, $intentScore)),
            'signals' => $signals,
            'recommendation' => $intentScore >= 70 ? 'Yüksek öncelikli takip' : 
                               ($intentScore >= 50 ? 'Normal takip' : 'Nurturing gerekli'),
        ];
    }

    /**
     * Analyze call and generate summary
     */
    public function analyzeCall(string $transcript): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a real estate call analyst. Analyze the call transcript and extract key information in Turkish. Return JSON with: summary (string), customer_intent (buy/rent/sell/info), sentiment (positive/negative/neutral), key_points (array), action_items (array), property_requirements (object with budget, location, type, rooms, size), next_steps (array), objections (array), buying_signals (array).'
            ],
            [
                'role' => 'user',
                'content' => $transcript
            ]
        ];

        return $this->ai->chatJson($messages) ?? [
            'summary' => 'Arama analizi yapılamadı.',
            'customer_intent' => 'info',
            'sentiment' => 'neutral',
            'key_points' => [],
            'action_items' => [],
            'property_requirements' => [],
            'next_steps' => [],
            'objections' => [],
            'buying_signals' => [],
        ];
    }

    /**
     * Generate appointment suggestions
     */
    public function suggestAppointments(Lead $lead, User $agent): array
    {
        // Get agent's calendar availability (placeholder)
        $availableSlots = $this->getAgentAvailability($agent);
        
        // Get matching listings for showing
        $listings = $this->getMatchingListings($lead);
        
        // Generate optimal route if multiple showings
        $route = count($listings) > 1 ? $this->optimizeShowingRoute($listings) : null;

        return [
            'available_slots' => $availableSlots,
            'suggested_listings' => array_slice($listings, 0, 5),
            'optimal_route' => $route,
            'estimated_duration' => count($listings) * 30 + 15, // 30 min per listing + travel
        ];
    }

    protected function getAgentAvailability(User $agent): array
    {
        // Placeholder - would integrate with calendar
        $slots = [];
        for ($i = 1; $i <= 5; $i++) {
            $date = now()->addDays($i);
            if ($date->isWeekday()) {
                $slots[] = [
                    'date' => $date->format('Y-m-d'),
                    'times' => ['10:00', '14:00', '16:00'],
                ];
            }
        }
        return $slots;
    }

    protected function optimizeShowingRoute(array $listings): ?array
    {
        // Placeholder - would use Google Maps API for route optimization
        return [
            'order' => array_column($listings, 'id'),
            'total_distance' => '15 km',
            'total_time' => '45 dakika',
        ];
    }
}
