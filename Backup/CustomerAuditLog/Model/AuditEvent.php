<?php
declare(strict_types=1);

namespace Abbott\CustomerAuditLog\Model;

use Abbott\CustomerAuditLog\Api\Data\AuditEventInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;

/**
 * Plain in-memory data holder for a single audit event; it is written straight to the
 * audit log file (see AuditLogger) and is never persisted to the database.
 */
class AuditEvent extends DataObject implements AuditEventInterface
{
    /**
     * @var Json
     */
    private $json;

    /**
     * @return Json
     */
    private function getJsonSerializer(): Json
    {
        if ($this->json === null) {
            $this->json = ObjectManager::getInstance()->get(Json::class);
        }
        return $this->json;
    }

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
    public function getEmailHash(): ?string
    {
        return $this->getData(self::EMAIL_HASH);
    }

    /**
     * @inheritdoc
     */
    public function setEmailHash(?string $emailHash): AuditEventInterface
    {
        return $this->setData(self::EMAIL_HASH, $emailHash);
    }

    /**
     * @inheritdoc
     */
    public function getWebsiteId(): ?int
    {
        $value = $this->getData(self::WEBSITE_ID);
        return $value === null ? null : (int)$value;
    }

    /**
     * @inheritdoc
     */
    public function setWebsiteId(?int $websiteId): AuditEventInterface
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
    }

    /**
     * @inheritdoc
     */
    public function getStoreId(): ?int
    {
        $value = $this->getData(self::STORE_ID);
        return $value === null ? null : (int)$value;
    }

    /**
     * @inheritdoc
     */
    public function setStoreId(?int $storeId): AuditEventInterface
    {
        return $this->setData(self::STORE_ID, $storeId);
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
    public function getReasonCode(): ?string
    {
        return $this->getData(self::REASON_CODE);
    }

    /**
     * @inheritdoc
     */
    public function setReasonCode(?string $reasonCode): AuditEventInterface
    {
        return $this->setData(self::REASON_CODE, $reasonCode);
    }

    /**
     * @inheritdoc
     */
    public function getIpHash(): ?string
    {
        return $this->getData(self::IP_HASH);
    }

    /**
     * @inheritdoc
     */
    public function setIpHash(?string $ipHash): AuditEventInterface
    {
        return $this->setData(self::IP_HASH, $ipHash);
    }

    /**
     * @inheritdoc
     */
    public function getUserAgent(): ?string
    {
        return $this->getData(self::USER_AGENT);
    }

    /**
     * @inheritdoc
     */
    public function setUserAgent(?string $userAgent): AuditEventInterface
    {
        return $this->setData(self::USER_AGENT, $userAgent);
    }

    /**
     * @inheritdoc
     */
    public function getCorrelationId(): ?string
    {
        return $this->getData(self::CORRELATION_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCorrelationId(?string $correlationId): AuditEventInterface
    {
        return $this->setData(self::CORRELATION_ID, $correlationId);
    }

    /**
     * @inheritdoc
     */
    public function getMetadata(): array
    {
        $raw = $this->getData(self::METADATA);
        if (!$raw) {
            return [];
        }
        if (is_array($raw)) {
            return $raw;
        }
        try {
            $decoded = $this->getJsonSerializer()->unserialize($raw);
        } catch (\InvalidArgumentException $e) {
            return [];
        }
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @inheritdoc
     */
    public function setMetadata(array $metadata): AuditEventInterface
    {
        return $this->setData(self::METADATA, $this->getJsonSerializer()->serialize($metadata));
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
