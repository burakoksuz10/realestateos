<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

/*
|--------------------------------------------------------------------------
| Test Case + global hooks
|--------------------------------------------------------------------------
|
| Feature/* — Laravel TestCase + RefreshDatabase (in-memory sqlite).
| Tüm dış sistemleri default'ta fake'liyoruz; gerçek I/O isteyen test
| kendi içinde fake'i kaldırır.
*/

pest()->extend(Tests\TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function () {
        Bus::fake();
        Event::fake();
        Notification::fake();
    })
    ->in('Feature');

pest()->extend(Tests\TestCase::class)
    ->in('Unit');

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

/**
 * Test'lerde permission/role kuralları hazır olsun — DatabaseSeeder'ın
 * permissions/roles bölümünü minimal versiyonu olarak çağrılır.
 */
function seedRoles(): void
{
    $perms = [
        'listings.view', 'contacts.view', 'leads.view', 'leads.edit',
        'deals.view', 'deals.edit', 'reports.view',
    ];
    foreach ($perms as $p) {
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $p]);
    }
    foreach (['super-admin', 'admin', 'office-manager', 'agent'] as $r) {
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => $r]);
        if ($r !== 'super-admin') {
            $role->syncPermissions($perms);
        }
    }
}

/**
 * Yeni ofis ve içinde bir agent kullanıcı oluştur, login ederek döndür.
 * IDOR ve office-scope testlerinde tek satırda kurulum için.
 */
function actingAsAgentInOffice(?\Modules\Core\Models\Office $office = null, string $role = 'agent'): \App\Models\User
{
    seedRoles();
    $office ??= \Modules\Core\Models\Office::factory()->create();
    $user = \App\Models\User::factory()->create(['office_id' => $office->id]);
    $user->assignRole($role);
    test()->actingAs($user);
    return $user;
}
