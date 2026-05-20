<?php

namespace Modules\CRM\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\CRM\Models\Contact;
use Modules\CRM\Models\Deal;
use Modules\Core\Models\Office;

class DealFactory extends Factory
{
    protected $model = Deal::class;

    public function definition(): array
    {
        $value = fake()->numberBetween(1000000, 10000000);
        return [
            'office_id'        => Office::factory(),
            'contact_id'       => Contact::factory(),
            'title'            => 'Deal ' . fake()->bothify('?##'),
            'status'           => 'open',
            'deal_type'        => 'sale',
            'value'            => $value,
            'currency'         => 'TRY',
            'probability'      => 50,
            'commission_type'  => 'percentage',
            'commission_rate'  => 3.0,
            'stage_entered_at' => now(),
        ];
    }

    public function won(): static
    {
        return $this->state(fn ($attrs) => [
            'status'             => 'won',
            'probability'        => 100,
            'closed_at'          => now(),
            'actual_close_date'  => now(),
            'commission_amount'  => ($attrs['value'] ?? 0) * 0.03,
        ]);
    }

    public function lost(): static
    {
        return $this->state(fn () => [
            'status'      => 'lost',
            'probability' => 0,
            'closed_at'   => now(),
        ]);
    }
}
