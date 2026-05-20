<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Office;

class OfficeFactory extends Factory
{
    protected $model = Office::class;

    public function definition(): array
    {
        return [
            'tenant_id'       => \Modules\Core\Models\Tenant::factory(),
            'name'            => 'Ofis ' . fake()->unique()->numerify('###'),
            'code'            => strtoupper(fake()->unique()->lexify('OFS-???')),
            'city'            => 'İstanbul',
            'district'        => fake()->randomElement(['Kadıköy', 'Beşiktaş', 'Şişli', 'Üsküdar']),
            'address'         => fake()->streetAddress(),
            'phone'           => '+90 216 ' . fake()->numerify('### ## ##'),
            'email'           => fake()->companyEmail(),
            'country'         => 'TR',
            'is_active'       => true,
            'is_headquarters' => false,
        ];
    }
}
