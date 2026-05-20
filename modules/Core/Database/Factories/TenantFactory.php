<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Core\Models\Tenant;

class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        $slug = Str::slug(fake()->unique()->company());
        return [
            'name'              => fake()->company(),
            'subdomain'         => $slug . '-' . fake()->unique()->numerify('####'),
            'primary_color'     => '#0ea5e9',
            'secondary_color'   => '#64748b',
            'subscription_plan' => 'trial',
            'trial_ends_at'     => now()->addDays(14),
            'is_active'         => true,
        ];
    }
}
