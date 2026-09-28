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
            $this->record(null, $email);
            return $result;
        } catch (LocalizedException $e) {
            $this->record(null, $email, false);
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
        $email = $this->resolveEmail((int)$customerId);
        try {
            $result = $proceed($customerId, $currentPassword, $newPassword);
            $this->record((int)$customerId, $email);
            return $result;
        } catch (LocalizedException $e) {
            $this->record((int)$customerId, $email, false);
            throw $e;
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

    /**
     * @param int|null $customerId
     * @param string|null $email
     * @param bool $success
     * @return void
     */
    private function record(?int $customerId, ?string $email, bool $success = true): void
    {
        $this->auditEventBuilder->record(
            AuditEventInterface::TYPE_PASSWORD_CHANGE,
            $success ? AuditEventInterface::STATUS_SUCCESS : AuditEventInterface::STATUS_FAILURE,
            $customerId,
            $email,
            $success ? null : 'invalid_current_password_or_policy'
        );
    }
}
