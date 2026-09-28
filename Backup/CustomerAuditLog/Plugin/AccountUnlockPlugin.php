<?php
declare(strict_types=1);

namespace Abbott\CustomerAuditLog\Plugin;

use Abbott\CustomerAuditLog\Api\Data\AuditEventInterface;
use Abbott\CustomerAuditLog\Model\AuditEventBuilder;
use Magento\Customer\Model\Authentication;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Captures manual/automatic unlocks; Authentication::unlock() has no native event and is
 * called unconditionally on every successful login (see CustomerLoginSuccessObserver), so
 * this only logs when the account was actually locked beforehand.
 */
class AccountUnlockPlugin
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
    public function aroundUnlock(Authentication $subject, callable $proceed, $customerId)
    {
        $wasLocked = $subject->isLocked($customerId);

        $proceed($customerId);

        if ($wasLocked) {
            $this->auditEventBuilder->record(
                AuditEventInterface::TYPE_ACCOUNT_UNLOCK,
                AuditEventInterface::STATUS_SUCCESS,
                (int)$customerId,
                $this->resolveEmail((int)$customerId)
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
