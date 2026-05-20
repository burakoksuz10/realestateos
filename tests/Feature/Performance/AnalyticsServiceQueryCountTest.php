<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\BI\Services\AnalyticsService;
use Modules\CRM\Models\Activity;
use Modules\CRM\Models\Deal;
use Modules\CRM\Models\Lead;
use Modules\Core\Models\Office;

/**
 * AnalyticsService::getAgentPerformance — 10 agent için query sayısı
 * O(N) değil O(1) olmalı. N+1 regression koruması.
 *
 * Eski kod: agent başına ~4 query (leads.count, deals.count, activities.count,
 * showings.count) + bazı ekler. Yeni kod: 4 toplu query (users, leads, deals,
 * activities) — agent sayısından bağımsız.
 */

it('runs in O(1) queries regardless of agent count', function () {
    seedRoles();
    $office = Office::factory()->create();

    // 10 agent oluştur
    $agents = User::factory()->count(10)->create(['office_id' => $office->id]);
    foreach ($agents as $a) $a->assignRole('agent');

    // Her birine birkaç lead/deal/activity ekle
    foreach ($agents as $a) {
        Lead::factory()->count(3)->create(['office_id' => $office->id, 'assigned_to' => $a->id]);
        Deal::factory()->count(2)->create(['office_id' => $office->id, 'assigned_to' => $a->id]);
        Activity::factory()->count(2)->create(['user_id' => $a->id]);
    }

    $queryCount = 0;
    DB::listen(function ($q) use (&$queryCount) {
        $queryCount++;
    });

    $service = app(AnalyticsService::class);
    $result  = $service->getAgentPerformance([
        'date_from' => now()->subYear(),
        'date_to'   => now()->addDay(),
    ]);

    expect($result)->toBeArray();
    expect(count($result))->toBe(10);

    // Eski kod: ~10 * 4 + 1 = 41+ query.
    // Yeni: 1 (users) + 1 (leads) + 1 (deals) + 1 (activities) = 4
    // Eager-load office'i sayarsak 5. Üst sınır 10.
    expect($queryCount)->toBeLessThanOrEqual(10);
});
