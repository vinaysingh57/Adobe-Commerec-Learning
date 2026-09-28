<?php
declare(strict_types=1);

namespace Abbott\CustomerAuditLog\Model;

use Abbott\CustomerAuditLog\Api\AuditLoggerInterface;
use Abbott\CustomerAuditLog\Api\Data\AuditEventInterface;
use Magento\Framework\Math\Random;

/**
 * Builds AuditEvent instances with the common fields already populated,
 * and dispatches them to the logger.
 */
class AuditEventBuilder
{
    /**
     * @var AuditEventFactory
     */
    private $auditEventFactory;

    /**
     * @var Random
     */
    private $random;

    /**
     * @var AuditLoggerInterface
     */
    private $auditLogger;

    /**
     * @param AuditEventFactory $auditEventFactory
     * @param Random $random
     * @param AuditLoggerInterface $auditLogger
     */
    public function __construct(
        AuditEventFactory $auditEventFactory,
        Random $random,
        AuditLoggerInterface $auditLogger
    ) {
        $this->auditEventFactory = $auditEventFactory;
        $this->random = $random;
        $this->auditLogger = $auditLogger;
    }

    /**
     * @param string $eventType
     * @param int|null $customerId
     * @param string $status
     * @return void
     */
    public function record(
        string $eventType,
        ?int $customerId = null,
        string $status = AuditEventInterface::STATUS_SUCCESS
    ): void {
        /** @var AuditEventInterface $event */
        $event = $this->auditEventFactory->create();
        $event->setEventId($this->random->getUniqueHash())
            ->setEventType($eventType)
            ->setCustomerId($customerId)
            ->setStatus($status);

        $this->auditLogger->log($event);
    }
}
