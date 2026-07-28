<?php

namespace App\Domain\Licensing;

use Illuminate\Contracts\Foundation\Application;

class EnvironmentResolver
{
    public function __construct(private readonly Application $application) {}

    public function resolve(): string
    {
        return match ($this->application->environment()) {
            'production' => 'production',
            'staging' => 'staging',
            'testing' => 'testing',
            'local' => 'local',
            default => 'development',
        };
    }
}
