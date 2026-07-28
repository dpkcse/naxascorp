<?php

namespace App\Domain\Installation;

use Illuminate\Contracts\Session\Session;

class InstallationState
{
    public const Steps = [
        'welcome_completed',
        'requirements_passed',
        'permissions_passed',
        'database_connection_verified',
        'administrator_created',
        'license_request_created',
        'license_entitlement_verified',
        'license_acknowledged',
    ];

    public function __construct(private readonly Session $session) {}

    public function hasCompleted(string $step): bool
    {
        return in_array($step, self::Steps, true)
            && $this->session->get("installer.progress.{$step}") === true;
    }

    public function markCompleted(string $step): void
    {
        $this->guardKnownStep($step);
        $this->session->put("installer.progress.{$step}", true);
    }

    public function forgetFrom(string $step): void
    {
        $this->guardKnownStep($step);
        $index = array_search($step, self::Steps, true);

        foreach (array_slice(self::Steps, $index) as $stepToForget) {
            $this->session->forget("installer.progress.{$stepToForget}");
        }
    }

    public function reset(): void
    {
        $this->session->forget('installer.progress');
    }

    public function previousStepIsComplete(string $step): bool
    {
        $this->guardKnownStep($step);
        $index = array_search($step, self::Steps, true);

        return $index === 0 || $this->hasCompleted(self::Steps[$index - 1]);
    }

    private function guardKnownStep(string $step): void
    {
        if (! in_array($step, self::Steps, true)) {
            throw new \InvalidArgumentException('Unknown installer step.');
        }
    }
}
