<?php
declare(strict_types=1);

namespace Abbott\CustomerAuditLog\Model;

use Abbott\CustomerAuditLog\Api\AuditLoggerInterface;
use Abbott\CustomerAuditLog\Api\Data\AuditEventInterface;
use Magento\Framework\Math\Random;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Builds AuditEvent instances with the common fields (ids, hashes, context)
 * already populated, and dispatches them to the logger.
 */
class AuditEventBuilder
{
    /**
     * @var AuditEventFactory
     */
    private $auditEventFactory;

    /**
     * @var PiiMasker
     */
    private $piiMasker;

    /**
     * @var RequestContext
     */
    private $requestContext;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

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
     * @param PiiMasker $piiMasker
     * @param RequestContext $requestContext
     * @param StoreManagerInterface $storeManager
     * @param Random $random
     * @param AuditLoggerInterface $auditLogger
     */
    public function __construct(
        AuditEventFactory $auditEventFactory,
        PiiMasker $piiMasker,
        RequestContext $requestContext,
        StoreManagerInterface $storeManager,
        Random $random,
        AuditLoggerInterface $auditLogger
    ) {
        $this->auditEventFactory = $auditEventFactory;
        $this->piiMasker = $piiMasker;
        $this->requestContext = $requestContext;
        $this->storeManager = $storeManager;
        $this->random = $random;
        $this->auditLogger = $auditLogger;
    }

    /**
     * @param string $eventType
     * @param string $status
     * @param int|null $customerId
     * @param string|null $email
     * @param string|null $reasonCode
     * @param array $metadata
     * @return void
     */
    public function record(
        string $eventType,
        string $status,
        ?int $customerId = null,
        ?string $email = null,
        ?string $reasonCode = null,
        array $metadata = []
    ): void {
        try {
            $store = $this->storeManager->getStore();
            $websiteId = (int)$store->getWebsiteId();
            $storeId = (int)$store->getId();
        } catch (\Throwable $e) {
            $websiteId = null;
            $storeId = null;
        }

        /** @var AuditEventInterface $event */
        $event = $this->auditEventFactory->create();
        $event->setEventId($this->random->getUniqueHash())
            ->setEventType($eventType)
            ->setStatus($status)
            ->setCustomerId($customerId)
            ->setEmailHash($this->piiMasker->hashEmail($email))
            ->setWebsiteId($websiteId)
            ->setStoreId($storeId)
            ->setReasonCode($reasonCode)
            ->setIpHash($this->piiMasker->hashIp($this->requestContext->getClientIp()))
            ->setUserAgent($this->requestContext->getUserAgent())
            ->setCorrelationId($this->requestContext->getCorrelationId())
            ->setMetadata($metadata);

        $this->auditLogger->log($event);
    }
}
