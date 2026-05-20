<?php

use App\Models\User;
use Modules\CRM\Models\Lead;
use Modules\Core\Models\Office;

/**
 * EnforcesOfficeIsolation trait — Lead controller'da IDOR koruması.
 * Bir ofisin agent'i başka ofisin lead'ine show/edit/update/destroy
 * üzerinden ulaşamamalı.
 */

beforeEach(function () {
    seedRoles();

    $this->officeA = Office::factory()->create();
    $this->officeB = Office::factory()->create();

    $this->agentA = User::factory()->create(['office_id' => $this->officeA->id]);
    $this->agentA->assignRole('agent');

    $this->leadB = Lead::factory()->create(['office_id' => $this->officeB->id]);
});

it('aborts 403 when agent shows a lead from another office', function () {
    $this->actingAs($this->agentA)
        ->get(route('admin.leads.show', $this->leadB))
        ->assertForbidden();
});

it('aborts 403 when agent edits a lead from another office', function () {
    $this->actingAs($this->agentA)
        ->get(route('admin.leads.edit', $this->leadB))
        ->assertForbidden();
});

it('aborts 403 when agent updates a lead from another office', function () {
    $this->actingAs($this->agentA)
        ->put(route('admin.leads.update', $this->leadB), [
            'status' => 'contacted',
        ])
        ->assertForbidden();
});

it('aborts 403 when agent deletes a lead from another office', function () {
    $this->actingAs($this->agentA)
        ->delete(route('admin.leads.destroy', $this->leadB))
        ->assertForbidden();

    expect(Lead::find($this->leadB->id))->not->toBeNull();
});

it('allows same office agent to access the lead', function () {
    $leadA = Lead::factory()->create(['office_id' => $this->officeA->id]);

    $response = $this->actingAs($this->agentA)
        ->delete(route('admin.leads.destroy', $leadA));

    $response->assertRedirect();
    expect(Lead::find($leadA->id))->toBeNull();
});

it('skips office check when user has no office_id (superadmin)', function () {
    $super = User::factory()->create(['office_id' => null]);
    $super->assignRole('super-admin');

    $this->actingAs($super)
        ->delete(route('admin.leads.destroy', $this->leadB))
        ->assertRedirect();

    expect(Lead::find($this->leadB->id))->toBeNull();
});
