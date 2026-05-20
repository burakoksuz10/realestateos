<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\CRM\Http\Controllers\InboxController;
use Modules\CRM\Models\Conversation;
use Modules\Core\Models\Office;

/**
 * InboxController index'i — status başına ayrı count() yerine tek
 * aggregate query. 4 → 1 query'lik refactor regression koruması.
 *
 * Blade view rendering test edilmiyor (admin layout'u test ortamında
 * kurulu değil); direkt controller'ı çağırıp DB query'lerini ölçüyoruz.
 */

beforeEach(function () {
    seedRoles();
    $this->office = Office::factory()->create();
    $this->agent  = User::factory()->create(['office_id' => $this->office->id]);
    $this->agent->assignRole('agent');

    Conversation::factory()->count(5)->create(['office_id' => $this->office->id, 'status' => 'open']);
    Conversation::factory()->count(2)->unread()->create(['office_id' => $this->office->id]);
    Conversation::factory()->count(3)->archived()->create(['office_id' => $this->office->id]);
    Conversation::factory()->count(1)->closed()->create(['office_id' => $this->office->id]);

    $this->actingAs($this->agent);
});

function invokeInboxIndex(): void
{
    $controller = app(InboxController::class);
    $request    = Request::create('/admin/inbox', 'GET');
    $request->setUserResolver(fn () => auth()->user());

    try {
        $controller->index($request);
    } catch (\Throwable $e) {
        // view rendering hatalarını yutuyoruz — bizi sadece query sayısı ilgilendiriyor
        if (! str_contains($e->getMessage(), 'view') && ! str_contains($e->getMessage(), 'View')) {
            throw $e;
        }
    }
}

it('uses a single aggregate query for inbox counts', function () {
    $countQueries = 0;
    DB::listen(function ($query) use (&$countQueries) {
        if (preg_match('/SUM\(CASE WHEN status/i', $query->sql)
            && str_contains($query->sql, 'conversations')) {
            $countQueries++;
        }
    });

    invokeInboxIndex();

    expect($countQueries)->toBe(1);
});

it('does NOT issue 4 separate count queries for status counts', function () {
    $perStatusCountQueries = 0;
    DB::listen(function ($query) use (&$perStatusCountQueries) {
        if (preg_match('/select count\([^)]*\) from .conversations. where .status. = /i', $query->sql)) {
            $perStatusCountQueries++;
        }
    });

    invokeInboxIndex();

    expect($perStatusCountQueries)->toBe(0);
});
