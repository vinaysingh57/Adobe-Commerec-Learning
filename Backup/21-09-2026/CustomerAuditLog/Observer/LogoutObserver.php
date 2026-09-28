<?php
declare(strict_types=1);

namespace Abbott\CustomerAuditLog\Observer;

use Abbott\CustomerAuditLog\Api\Data\AuditEventInterface;
use Abbott\CustomerAuditLog\Model\AuditEventBuilder;
use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Captures customer logouts via the native "customer_logout" event.
 */
class LogoutObserver implements ObserverInterface
{
    /**
     * @var AuditEventBuilder
     */
    private $auditEventBuilder;

    /**
     * @param AuditEventBuilder $auditEventBuilder
     */
    public function __construct(AuditEventBuilder $auditEventBuilder)
    {
        $this->auditEventBuilder = $auditEventBuilder;
    }

    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /** @var Customer $customer */
        $customer = $observer->getEvent()->getData('customer');

        $this->auditEventBuilder->record(
            AuditEventInterface::TYPE_LOGOUT,
            $customer ? (int)$customer->getId() : null
        );
    }
}
