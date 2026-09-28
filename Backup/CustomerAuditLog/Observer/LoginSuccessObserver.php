<?php
declare(strict_types=1);

namespace Abbott\CustomerAuditLog\Observer;

use Abbott\CustomerAuditLog\Api\Data\AuditEventInterface;
use Abbott\CustomerAuditLog\Model\AuditEventBuilder;
use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Captures successful customer logins via the native "customer_customer_authenticated" event,
 * which fires for every authenticate() call regardless of channel (storefront session, GraphQL
 * generateCustomerToken, REST/SOAP integration token) - unlike "customer_login", which only
 * fires for storefront PHP-session logins.
 */
class LoginSuccessObserver implements ObserverInterface
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
        $customer = $observer->getEvent()->getData('model');

        $this->auditEventBuilder->record(
            AuditEventInterface::TYPE_LOGIN_SUCCESS,
            AuditEventInterface::STATUS_SUCCESS,
            $customer ? (int)$customer->getId() : null,
            $customer ? (string)$customer->getEmail() : null
        );
    }
}
