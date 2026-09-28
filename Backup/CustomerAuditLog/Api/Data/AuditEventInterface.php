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
    public const EMAIL_HASH = 'email_hash';
    public const WEBSITE_ID = 'website_id';
    public const STORE_ID = 'store_id';
    public const STATUS = 'status';
    public const REASON_CODE = 'reason_code';
    public const IP_HASH = 'ip_hash';
    public const USER_AGENT = 'user_agent';
    public const CORRELATION_ID = 'correlation_id';
    public const METADATA = 'metadata';
    public const CREATED_AT = 'created_at';

    public const TYPE_LOGIN_SUCCESS = 'login_success';
    public const TYPE_LOGIN_FAILURE = 'login_failure';
    public const TYPE_LOGOUT = 'logout';
    public const TYPE_PASSWORD_CHANGE = 'password_change';
    public const TYPE_PASSWORD_RESET_REQUESTED = 'password_reset_requested';
    public const TYPE_PASSWORD_RESET_COMPLETED = 'password_reset_completed';
    public const TYPE_ACCOUNT_LOCK = 'account_lock';
    public const TYPE_ACCOUNT_UNLOCK = 'account_unlock';

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
     * @return string|null
     */
    public function getEmailHash(): ?string;

    /**
     * @param string|null $emailHash
     * @return $this
     */
    public function setEmailHash(?string $emailHash): self;

    /**
     * @return int|null
     */
    public function getWebsiteId(): ?int;

    /**
     * @param int|null $websiteId
     * @return $this
     */
    public function setWebsiteId(?int $websiteId): self;

    /**
     * @return int|null
     */
    public function getStoreId(): ?int;

    /**
     * @param int|null $storeId
     * @return $this
     */
    public function setStoreId(?int $storeId): self;

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
    public function getReasonCode(): ?string;

    /**
     * @param string|null $reasonCode
     * @return $this
     */
    public function setReasonCode(?string $reasonCode): self;

    /**
     * @return string|null
     */
    public function getIpHash(): ?string;

    /**
     * @param string|null $ipHash
     * @return $this
     */
    public function setIpHash(?string $ipHash): self;

    /**
     * @return string|null
     */
    public function getUserAgent(): ?string;

    /**
     * @param string|null $userAgent
     * @return $this
     */
    public function setUserAgent(?string $userAgent): self;

    /**
     * @return string|null
     */
    public function getCorrelationId(): ?string;

    /**
     * @param string|null $correlationId
     * @return $this
     */
    public function setCorrelationId(?string $correlationId): self;

    /**
     * @return array
     */
    public function getMetadata(): array;

    /**
     * @param array $metadata
     * @return $this
     */
    public function setMetadata(array $metadata): self;

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
