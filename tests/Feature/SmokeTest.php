<?php

use App\Models\User;
use Modules\Core\Models\Office;
use Modules\Core\Models\Tenant;

it('boots the application', function () {
    expect(app())->not->toBeNull();
    expect(config('app.name'))->toBe('ReCRM');
});

it('runs migrations and creates a user', function () {
    $tenant = Tenant::factory()->create();
    $office = Office::factory()->create(['tenant_id' => $tenant->id]);
    $user   = User::factory()->create(['office_id' => $office->id]);

    expect($user->id)->toBeInt();
    expect($user->office)->not->toBeNull();
    expect($user->office->tenant->id)->toBe($tenant->id);
});
