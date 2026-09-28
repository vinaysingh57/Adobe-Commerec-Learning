<?php
declare(strict_types=1);

namespace Abbott\CustomerAuditLog\Model;

use Abbott\CustomerAuditLog\Api\Data\AuditEventInterface;
use Magento\Framework\DataObject;

/**
 * Plain in-memory data holder for a single audit event; it is written straight to the
 * audit log file (see AuditLogger) and is never persisted to the database.
 */
class AuditEvent extends DataObject implements AuditEventInterface
{
    /**
     * @inheritdoc
     */
    public function getEventId(): ?string
    {
        $value = $this->getData(self::EVENT_ID);
        return $value === null ? null : (string)$value;
    }

    /**
     * @inheritdoc
     */
    public function setEventId(string $eventId): AuditEventInterface
    {
        return $this->setData(self::EVENT_ID, $eventId);
    }

    /**
     * @inheritdoc
     */
    public function getEventType(): string
    {
        return (string)$this->getData(self::EVENT_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setEventType(string $eventType): AuditEventInterface
    {
        return $this->setData(self::EVENT_TYPE, $eventType);
    }

    /**
     * @inheritdoc
     */
    public function getCustomerId(): ?int
    {
        $value = $this->getData(self::CUSTOMER_ID);
        return $value === null ? null : (int)$value;
    }

    /**
     * @inheritdoc
     */
    public function setCustomerId(?int $customerId): AuditEventInterface
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @inheritdoc
     */
    public function getStatus(): string
    {
        return (string)$this->getData(self::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus(string $status): AuditEventInterface
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt(string $createdAt): AuditEventInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}
