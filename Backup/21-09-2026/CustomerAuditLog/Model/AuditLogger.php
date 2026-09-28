<?php
declare(strict_types=1);

namespace Abbott\CustomerAuditLog\Model;

use Abbott\CustomerAuditLog\Api\AuditLoggerInterface;
use Abbott\CustomerAuditLog\Api\Data\AuditEventInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 *
 * Writes each event as a single JSON log line to var/log/customer_audit_log/audit.log
 * (see Logger\Handler\AuditHandler). Nothing is persisted to the database.
 */
class AuditLogger implements AuditLoggerInterface
{
    private const SYSTEM_NAME = 'Adobe Commerce';

    /**
     * @var LoggerInterface
     */
    private $auditLogger;

    /**
     * @var LoggerInterface
     */
    private $errorLogger;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @param LoggerInterface $auditLogger
     * @param LoggerInterface $errorLogger
     * @param Json $json
     * @param DateTime $dateTime
     */
    public function __construct(
        LoggerInterface $auditLogger,
        LoggerInterface $errorLogger,
        Json $json,
        DateTime $dateTime
    ) {
        $this->auditLogger = $auditLogger;
        $this->errorLogger = $errorLogger;
        $this->json = $json;
        $this->dateTime = $dateTime;
    }

    /**
     * @inheritdoc
     */
    public function log(AuditEventInterface $event): void
    {
        try {
            $payload = [
                'event_id' => $event->getEventId(),
                'event_type' => $event->getEventType(),
                'occurred_at' => $event->getCreatedAt() ?? $this->dateTime->date('Y-m-d H:i:s'),
                'customer_id' => $event->getCustomerId(),
                'status' => $event->getStatus(),
                'system' => self::SYSTEM_NAME,
            ];

            // Audit log lines must never break - never fail-closed on logging.
            $this->auditLogger->info($this->json->serialize($payload));
        } catch (\Throwable $e) {
            $this->errorLogger->error(
                'Abbott_CustomerAuditLog: failed to write audit event "' . $event->getEventType() . '"',
                ['exception' => $e]
            );
        }
    }
}
