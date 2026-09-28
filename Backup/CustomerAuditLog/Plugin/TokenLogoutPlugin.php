<?php
declare(strict_types=1);

namespace Abbott\CustomerAuditLog\Plugin;

use Abbott\CustomerAuditLog\Api\Data\AuditEventInterface;
use Abbott\CustomerAuditLog\Model\AuditEventBuilder;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
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
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param AuditEventBuilder $auditEventBuilder
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        AuditEventBuilder $auditEventBuilder,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->auditEventBuilder = $auditEventBuilder;
        $this->customerRepository = $customerRepository;
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
                AuditEventInterface::STATUS_SUCCESS,
                (int)$customerId,
                $this->resolveEmail((int)$customerId)
            );
        }
        return $result;
    }

    /**
     * @param int $customerId
     * @return string|null
     */
    private function resolveEmail(int $customerId): ?string
    {
        try {
            return $this->customerRepository->getById($customerId)->getEmail();
        } catch (LocalizedException $e) {
            return null;
        }
    }
}
