<?php
/**
 * Copyright © Abbott Laboratories. All rights reserved.
 */
declare(strict_types=1);

namespace Abbott\CustomerAuditLog\Observer;

use Abbott\CustomerAuditLog\Model\AuditLogger;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Logs a frontend customer logout (event: customer_logout).
 */
class CustomerLogout implements ObserverInterface
{
    /**
     * @param AuditLogger $auditLogger
     */
    public function __construct(
        private readonly AuditLogger $auditLogger
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer): void
    {
        $customer = $observer->getEvent()->getData('customer');
        $customerId = $customer !== null ? $customer->getId() : null;

        $this->auditLogger->logLogout($customerId);
    }
}
