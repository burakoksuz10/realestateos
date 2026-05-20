<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\CRM\Models\Campaign;
use Modules\CRM\Models\CampaignStep;

class OnboardingCampaignSeeder extends Seeder
{
    public function run(): void
    {
        $campaign = Campaign::firstOrCreate(
            ['slug' => 'onboarding-5-day'],
            [
                'name'        => '5 Günlük Karşılama Serisi',
                'description' => 'Yeni lead\'lere ilk 5 günde otomatik bilgilendirme + nazik hatırlatma.',
                'trigger'     => 'lead_created',
                'is_active'   => true,
                'is_default'  => true,
            ],
        );

        // Var olan adımları temizleyip yeniden kur — idempotent seeder
        $campaign->steps()->delete();

        $steps = [
            [
                'order' => 10,
                'type'  => 'send_message',
                'label' => 'Karşılama (e-posta)',
                'config' => [
                    'channel' => 'email',
                    'subject' => 'Hoş geldiniz {{contact.first_name}}',
                    'body'    => "Merhaba {{contact.first_name}},\n\nBaşvurunuzu aldık. Önümüzdeki birkaç gün içinde size en uygun mülk seçeneklerini paylaşacağız.\n\nSorularınız için bana her zaman ulaşabilirsiniz.\n\n— {{agent.name}}",
                ],
            ],
            [
                'order' => 20,
                'type'  => 'wait',
                'label' => '1 gün bekle',
                'config' => ['days' => 1],
            ],
            [
                'order' => 30,
                'type'  => 'create_task',
                'label' => 'Danışman: Ara',
                'config' => [
                    'title'        => '{{contact.full_name}} ile ilk telefon görüşmesi',
                    'description'  => 'Yeni lead — onboarding kampanyasının 2. günü, ilk kişisel arama.',
                    'type'         => 'call',
                    'priority'     => 'high',
                    'due_in_hours' => 2,
                ],
            ],
            [
                'order' => 40,
                'type'  => 'wait',
                'label' => '2 gün bekle',
                'config' => ['days' => 2],
            ],
            [
                'order' => 50,
                'type'  => 'send_message',
                'label' => 'Hatırlatma (SMS)',
                'config' => [
                    'channel' => 'sms',
                    'body'    => 'Merhaba {{contact.first_name}}, gönderdiğimiz seçenekleri inceleyebildiniz mi? Sorularınız için yanıtlayabilirsiniz. — {{agent.name}}',
                ],
            ],
            [
                'order' => 60,
                'type'  => 'wait',
                'label' => '2 gün bekle',
                'config' => ['days' => 2],
            ],
            [
                'order' => 70,
                'type'  => 'send_message',
                'label' => 'Kapanış (e-posta)',
                'config' => [
                    'channel' => 'email',
                    'subject' => 'Hâlâ ilgileniyor musunuz? — {{agent.name}}',
                    'body'    => "Merhaba {{contact.first_name}},\n\nSize uygun ilanları takip ediyoruz. Yeni bir seçenek çıktığında ilk haber vereceğim taraf olalım — şu an aramaya devam etmek ister misiniz?\n\n— {{agent.name}}",
                ],
            ],
        ];

        foreach ($steps as $stepData) {
            CampaignStep::create([
                'campaign_id' => $campaign->id,
                ...$stepData,
            ]);
        }

        $this->command->info("Onboarding kampanyası kuruldu: #{$campaign->id} ({$campaign->name}) — " . count($steps) . ' step');
    }
}
