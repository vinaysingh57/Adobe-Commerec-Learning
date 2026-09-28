<?php
declare(strict_types=1);

namespace Abbott\CustomerAuditLog\Plugin;

use Abbott\CustomerAuditLog\Api\Data\AuditEventInterface;
use Abbott\CustomerAuditLog\Model\AuditEventBuilder;
use Magento\Integration\Api\CustomerTokenServiceInterface;

/**
 * Captures GraphQL/REST/SOAP token-based logout; unlike storefront sessions, revoking a
 * customer access token never dispatches "customer_logout".
 */
class TokenLogoutPlugin
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
     * @param CustomerTokenServiceInterface $subject
     * @param bool $result
     * @param int $customerId
     * @return bool
     */
    public function afterRevokeCustomerAccessToken(CustomerTokenServiceInterface $subject, $result, $customerId)
    {
        if ($result) {
            $this->auditEventBuilder->record(
                AuditEventInterface::TYPE_LOGOUT,
                (int)$customerId
            );
        }
        return $result;
    }
}
