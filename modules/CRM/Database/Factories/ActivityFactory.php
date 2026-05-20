<?php

namespace Modules\CRM\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\CRM\Models\Activity;

class ActivityFactory extends Factory
{
    protected $model = Activity::class;

    public function definition(): array
    {
        return [
            'user_id'      => User::factory(),
            'type'         => 'note',
            'subject'      => fake()->sentence(3),
            'description'  => fake()->paragraph(),
            'is_automated' => false,
        ];
    }

    public function showing(): static
    {
        return $this->state(fn () => ['type' => 'showing']);
    }

    public function call(): static
    {
        return $this->state(fn () => [
            'type'          => 'call',
            'call_duration' => fake()->numberBetween(60, 600),
        ]);
    }
}
