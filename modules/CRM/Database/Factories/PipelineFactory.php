<?php

namespace Modules\CRM\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\CRM\Models\Pipeline;
use Modules\Core\Models\Office;

class PipelineFactory extends Factory
{
    protected $model = Pipeline::class;

    public function definition(): array
    {
        return [
            'office_id'  => Office::factory(),
            'name'       => 'Pipeline ' . fake()->word(),
            'type'       => 'lead',
            'is_default' => false,
            'is_active'  => true,
        ];
    }

    public function deal(): static
    {
        return $this->state(fn () => ['type' => 'deal']);
    }
}
