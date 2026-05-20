<?php

namespace Modules\CRM\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\CRM\Models\Contact;
use Modules\CRM\Models\Lead;
use Modules\Core\Models\Office;

class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'office_id'     => Office::factory(),
            'contact_id'    => Contact::factory(),
            'title'         => fake()->sentence(4),
            'status'        => 'new',
            'priority'      => 'medium',
            'score'         => 0,
            'source'        => 'website',
            'interest_type' => 'buy',
            'property_type' => 'apartment',
            'budget_min'    => 1000000,
            'budget_max'    => 3000000,
            'budget_currency' => 'TRY',
        ];
    }

    public function converted(): static
    {
        return $this->state(fn () => [
            'status'       => 'converted',
            'converted_at' => now(),
        ]);
    }

    public function lost(): static
    {
        return $this->state(fn () => [
            'status'  => 'lost',
            'lost_at' => now(),
        ]);
    }
}
