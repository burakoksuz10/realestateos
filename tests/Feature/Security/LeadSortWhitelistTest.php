<?php

use App\Models\User;
use Modules\CRM\Models\Lead;
use Modules\Core\Models\Office;

/**
 * LeadController index'inde sort/direction whitelist'i — keyfi kolon
 * adıyla orderBy çağrılmamalı (SQL injection vektörü).
 */

beforeEach(function () {
    seedRoles();
    $this->office = Office::factory()->create();
    $this->agent  = User::factory()->create(['office_id' => $this->office->id]);
    $this->agent->assignRole('agent');

    Lead::factory()->count(3)->create(['office_id' => $this->office->id]);
});

it('ignores arbitrary sort field and falls back to created_at', function () {
    // password gibi var olan ama whitelist'te olmayan bir kolon —
    // direkt orderBy yapılırsa SQL hatası alırdık.
    $this->actingAs($this->agent)
        ->get(route('admin.leads.index', ['sort' => 'password', 'direction' => 'desc']))
        ->assertOk();
});

it('ignores SQL-injection-style sort string', function () {
    $this->actingAs($this->agent)
        ->get(route('admin.leads.index', [
            'sort'      => "id; DROP TABLE leads;--",
            'direction' => 'asc',
        ]))
        ->assertOk();

    // Tablo hâlâ durmalı
    expect(Lead::count())->toBe(3);
});

it('coerces invalid direction to desc', function () {
    $this->actingAs($this->agent)
        ->get(route('admin.leads.index', ['sort' => 'created_at', 'direction' => 'evil']))
        ->assertOk();
});

it('accepts a whitelisted sort field', function () {
    $this->actingAs($this->agent)
        ->get(route('admin.leads.index', ['sort' => 'score', 'direction' => 'asc']))
        ->assertOk();
});
