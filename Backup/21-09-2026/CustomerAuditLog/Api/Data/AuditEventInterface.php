<?php
declare(strict_types=1);

namespace Abbott\CustomerAuditLog\Api\Data;

/**
 * Audit event data contract for a single customer authentication event.
 */
interface AuditEventInterface
{
    public const EVENT_ID = 'event_id';
    public const EVENT_TYPE = 'event_type';
    public const CUSTOMER_ID = 'customer_id';
    public const CREATED_AT = 'created_at';
    public const SYSTEM = 'system';
    public const STATUS = 'status';

    public const TYPE_LOGIN_SUCCESS = 'login_success';
    public const TYPE_LOGIN_FAILURE = 'login_failure';
    public const TYPE_LOGOUT = 'logout';
    public const TYPE_PASSWORD_CHANGE = 'password_change';

    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILURE = 'failure';

    /**
     * @return string|null
     */
    public function getEventId(): ?string;

    /**
     * @param string $eventId
     * @return $this
     */
    public function setEventId(string $eventId): self;

    /**
     * @return string
     */
    public function getEventType(): string;

    /**
     * @param string $eventType
     * @return $this
     */
    public function setEventType(string $eventType): self;

    /**
     * @return int|null
     */
    public function getCustomerId(): ?int;

    /**
     * @param int|null $customerId
     * @return $this
     */
    public function setCustomerId(?int $customerId): self;

    /**
     * @return string
     */
    public function getStatus(): string;

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus(string $status): self;

    /**
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt(string $createdAt): self;
}
