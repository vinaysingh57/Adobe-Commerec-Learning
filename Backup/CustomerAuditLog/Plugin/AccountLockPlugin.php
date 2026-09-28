<?php
declare(strict_types=1);

namespace Abbott\CustomerAuditLog\Plugin;

use Abbott\CustomerAuditLog\Api\Data\AuditEventInterface;
use Abbott\CustomerAuditLog\Model\AuditEventBuilder;
use Magento\Customer\Model\Authentication;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Captures the false -> true lock transition; Authentication::processAuthenticationFailure()
 * has no native event and is also called on every non-locking failed attempt.
 */
class AccountLockPlugin
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
     * @param Authentication $subject
     * @param callable $proceed
     * @param int $customerId
     * @return void
     */
    public function aroundProcessAuthenticationFailure(Authentication $subject, callable $proceed, $customerId)
    {
        $wasLocked = $subject->isLocked($customerId);

        $proceed($customerId);

        if (!$wasLocked && $subject->isLocked($customerId)) {
            $this->auditEventBuilder->record(
                AuditEventInterface::TYPE_ACCOUNT_LOCK,
                AuditEventInterface::STATUS_SUCCESS,
                (int)$customerId,
                $this->resolveEmail((int)$customerId),
                'max_attempts_exceeded'
            );
        }
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
