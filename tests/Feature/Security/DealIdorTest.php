<?php

use App\Models\User;
use Modules\CRM\Models\Deal;
use Modules\Core\Models\Office;

beforeEach(function () {
    seedRoles();

    $this->officeA = Office::factory()->create();
    $this->officeB = Office::factory()->create();

    $this->agentA = User::factory()->create(['office_id' => $this->officeA->id]);
    $this->agentA->assignRole('agent');

    $this->dealB = Deal::factory()->create(['office_id' => $this->officeB->id]);
});

it('aborts 403 when agent shows a deal from another office', function () {
    $this->actingAs($this->agentA)
        ->get(route('admin.deals.show', $this->dealB))
        ->assertForbidden();
});

it('aborts 403 when agent updates a deal from another office', function () {
    $this->actingAs($this->agentA)
        ->put(route('admin.deals.update', $this->dealB), [
            'title' => 'hacked',
            'status' => 'won',
        ])
        ->assertForbidden();

    expect($this->dealB->fresh()->status)->toBe('open');
});

it('aborts 403 when agent destroys a deal from another office', function () {
    $this->actingAs($this->agentA)
        ->delete(route('admin.deals.destroy', $this->dealB))
        ->assertForbidden();

    expect(Deal::find($this->dealB->id))->not->toBeNull();
});
