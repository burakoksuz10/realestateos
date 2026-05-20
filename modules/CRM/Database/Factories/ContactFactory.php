<?php

namespace Modules\CRM\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\CRM\Models\Contact;
use Modules\Core\Models\Office;

class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        return [
            'office_id'   => Office::factory(),
            'type'        => 'individual',
            'status'      => 'active',
            'first_name'  => fake()->firstName(),
            'last_name'   => fake()->lastName(),
            'email'       => fake()->unique()->safeEmail(),
            'phone'       => '+90 53' . fake()->numerify('# ### ## ##'),
            'city'        => 'İstanbul',
            'source'      => 'website',
            'kvkk_consent'      => true,
            'kvkk_consent_date' => now(),
        ];
    }

    public function company(): static
    {
        return $this->state(fn () => [
            'type'         => 'company',
            'company_name' => fake()->company(),
        ]);
    }
}
