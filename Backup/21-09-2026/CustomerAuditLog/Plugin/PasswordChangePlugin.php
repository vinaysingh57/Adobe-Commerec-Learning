<?php
declare(strict_types=1);

namespace Abbott\CustomerAuditLog\Plugin;

use Abbott\CustomerAuditLog\Api\Data\AuditEventInterface;
use Abbott\CustomerAuditLog\Model\AuditEventBuilder;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Captures password change attempts; AccountManagement::changePassword[ById]() has
 * no native event.
 */
class PasswordChangePlugin
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
     * @param AccountManagementInterface $subject
     * @param callable $proceed
     * @param string $email
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool
     * @throws \Exception
     */
    public function aroundChangePassword(
        AccountManagementInterface $subject,
        callable $proceed,
        $email,
        $currentPassword,
        $newPassword
    ) {
        try {
            $result = $proceed($email, $currentPassword, $newPassword);
            $this->record($this->resolveCustomerIdByEmail((string)$email));
            return $result;
        } catch (LocalizedException $e) {
            $this->record($this->resolveCustomerIdByEmail((string)$email), false);
            throw $e;
        }
    }

    /**
     * @param AccountManagementInterface $subject
     * @param callable $proceed
     * @param int $customerId
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool
     * @throws \Exception
     */
    public function aroundChangePasswordById(
        AccountManagementInterface $subject,
        callable $proceed,
        $customerId,
        $currentPassword,
        $newPassword
    ) {
        try {
            $result = $proceed($customerId, $currentPassword, $newPassword);
            $this->record((int)$customerId);
            return $result;
        } catch (LocalizedException $e) {
            $this->record((int)$customerId, false);
            throw $e;
        }
    }

    /**
     * @param string $email
     * @return int|null
     */
    private function resolveCustomerIdByEmail(string $email): ?int
    {
        try {
            return (int)$this->customerRepository->get($email)->getId();
        } catch (LocalizedException $e) {
            return null;
        }
    }

    /**
     * @param int|null $customerId
     * @param bool $success
     * @return void
     */
    private function record(?int $customerId, bool $success = true): void
    {
        $this->auditEventBuilder->record(
            AuditEventInterface::TYPE_PASSWORD_CHANGE,
            $customerId,
            $success ? AuditEventInterface::STATUS_SUCCESS : AuditEventInterface::STATUS_FAILURE
        );
    }
}
