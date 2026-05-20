<?php

use App\Models\User;
use Modules\CRM\Models\Contact;
use Modules\Core\Models\Office;

beforeEach(function () {
    seedRoles();

    $this->officeA = Office::factory()->create();
    $this->officeB = Office::factory()->create();

    $this->agentA = User::factory()->create(['office_id' => $this->officeA->id]);
    $this->agentA->assignRole('agent');

    $this->contactB = Contact::factory()->create(['office_id' => $this->officeB->id]);
});

it('aborts 403 when agent destroys a contact from another office', function () {
    $this->actingAs($this->agentA)
        ->delete(route('admin.contacts.destroy', $this->contactB))
        ->assertForbidden();

    expect(Contact::find($this->contactB->id))->not->toBeNull();
});

it('aborts 403 when agent toggles status on a contact from another office', function () {
    $original = $this->contactB->is_active;

    $this->actingAs($this->agentA)
        ->post(route('admin.contacts.toggle-status', $this->contactB))
        ->assertForbidden();

    expect($this->contactB->fresh()->is_active)->toBe($original);
});
