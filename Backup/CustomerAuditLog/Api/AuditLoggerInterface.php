<?php
declare(strict_types=1);

namespace Abbott\CustomerAuditLog\Api;

use Abbott\CustomerAuditLog\Api\Data\AuditEventInterface;

/**
 * Central entry point for recording customer authentication audit events.
 */
interface AuditLoggerInterface
{
    /**
     * Persist the event locally and dispatch it for downstream (Data Lake) delivery.
     *
     * Implementations must never let a logging failure propagate to the caller,
     * so that audit capture cannot interrupt the customer authentication flow.
     *
     * @param AuditEventInterface $event
     * @return void
     */
    public function log(AuditEventInterface $event): void;
}
