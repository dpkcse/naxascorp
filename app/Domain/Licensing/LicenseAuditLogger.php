<?php

namespace App\Domain\Licensing;

use Illuminate\Log\LogManager;

class LicenseAuditLogger
{
    public function __construct(private readonly LogManager $logger) {}

    /** @param array<string, scalar|null> $metadata */
    public function record(string $event, array $metadata = []): void
    {
        $allowed = array_intersect_key($metadata, array_flip(['request_id', 'status', 'failure_code', 'correlation_id', 'product', 'environment']));
        $this->logger->info($event, $allowed);
    }
}
