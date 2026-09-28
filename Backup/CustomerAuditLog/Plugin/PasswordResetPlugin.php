<?php
declare(strict_types=1);

namespace Abbott\CustomerAuditLog\Plugin;

use Abbott\CustomerAuditLog\Api\Data\AuditEventInterface;
use Abbott\CustomerAuditLog\Model\AuditEventBuilder;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Captures the two password-reset phases; neither AccountManagement::initiatePasswordReset()
 * nor ::resetPassword() dispatch a native event.
 */
class PasswordResetPlugin
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
     * @param bool $result
     * @param string $email
     * @return bool
     */
    public function afterInitiatePasswordReset(AccountManagementInterface $subject, $result, $email)
    {
        $this->record(AuditEventInterface::TYPE_PASSWORD_RESET_REQUESTED, $email, $result);
        return $result;
    }

    /**
     * @param AccountManagementInterface $subject
     * @param bool $result
     * @param string $email
     * @return bool
     */
    public function afterResetPassword(AccountManagementInterface $subject, $result, $email)
    {
        $this->record(AuditEventInterface::TYPE_PASSWORD_RESET_COMPLETED, $email, $result);
        return $result;
    }

    /**
     * @param string $eventType
     * @param string $email
     * @param bool $success
     * @return void
     */
    private function record(string $eventType, string $email, bool $success): void
    {
        $customerId = null;
        try {
            $customerId = (int)$this->customerRepository->get($email)->getId();
        } catch (LocalizedException $e) {
            // customer lookup best-effort only; audit still recorded with hashed email
        }

        $this->auditEventBuilder->record(
            $eventType,
            $success ? AuditEventInterface::STATUS_SUCCESS : AuditEventInterface::STATUS_FAILURE,
            $customerId,
            $email
        );
    }
}
