<?php

namespace Modules\CRM\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\CRM\Models\Pipeline;
use Modules\CRM\Models\PipelineStage;

class PipelineStageFactory extends Factory
{
    protected $model = PipelineStage::class;

    public function definition(): array
    {
        return [
            'pipeline_id' => Pipeline::factory(),
            'name'        => fake()->word(),
            'color'       => '#3b82f6',
            'order'       => 1,
            'probability' => 50,
        ];
    }
}
