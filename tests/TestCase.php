<?php

namespace Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Factory adı çözücüsünü modül namespace'lerine genişlet.
        // App\Models\User → Database\Factories\UserFactory
        // Modules\CRM\Models\Lead → Modules\CRM\Database\Factories\LeadFactory
        Factory::guessFactoryNamesUsing(function (string $modelName): string {
            if (Str::startsWith($modelName, 'Modules\\')) {
                return preg_replace(
                    '/\\\\Models\\\\([^\\\\]+)$/',
                    '\\\\Database\\\\Factories\\\\$1Factory',
                    $modelName
                );
            }
            return 'Database\\Factories\\'
                . Str::after($modelName, 'App\\Models\\')
                . 'Factory';
        });
    }
}
