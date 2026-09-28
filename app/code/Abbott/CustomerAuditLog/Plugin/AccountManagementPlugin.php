<?php
/**
 * Copyright © Abbott Laboratories. All rights reserved.
 */
declare(strict_types=1);

namespace Abbott\CustomerAuditLog\Plugin;

use Abbott\CustomerAuditLog\Model\AuditLogger;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Captures frontend authentication failures and password changes.
 *
 * Declared only in the frontend area (see etc/frontend/di.xml) so that admin
 * and web API authentication are never audited by this module.
 */
class AccountManagementPlugin
{
    /**
     * @param AuditLogger $auditLogger
     * @param CustomerRepositoryInterface $customerRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Audit failed customer authentication attempts without altering behaviour.
     *
     * @param AccountManagementInterface $subject
     * @param callable $proceed
     * @param string $email
     * @param string $password
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundAuthenticate(
        AccountManagementInterface $subject,
        callable $proceed,
        $email,
        $password
    ) {
        try {
            return $proceed($email, $password);
        } catch (\Exception $e) {
            $this->auditLogger->logLoginFailure(
                $this->resolveCustomerId((string) $email),
                (string) $email,
                $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * Audit a successful password change performed with the current password.
     *
     * @param AccountManagementInterface $subject
     * @param bool $result
     * @param string $email
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterChangePassword(
        AccountManagementInterface $subject,
        $result,
        $email,
        $currentPassword,
        $newPassword
    ) {
        if ($result) {
            $this->auditLogger->logPasswordChanged($this->resolveCustomerId((string) $email));
        }

        return $result;
    }

    /**
     * Audit a successful password change performed by customer id.
     *
     * @param AccountManagementInterface $subject
     * @param bool $result
     * @param int $customerId
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterChangePasswordById(
        AccountManagementInterface $subject,
        $result,
        $customerId,
        $currentPassword,
        $newPassword
    ) {
        if ($result) {
            $this->auditLogger->logPasswordChanged($customerId);
        }

        return $result;
    }

    /**
     * Audit a successful password reset through the frontend reset-password flow.
     *
     * @param AccountManagementInterface $subject
     * @param bool $result
     * @param string $email
     * @param string $resetToken
     * @param string $newPassword
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterResetPassword(
        AccountManagementInterface $subject,
        $result,
        $email,
        $resetToken,
        $newPassword
    ) {
        if ($result) {
            $this->auditLogger->logPasswordChanged($this->resolveCustomerId((string) $email));
        }

        return $result;
    }

    /**
     * Best-effort resolution of a customer id from an email address.
     *
     * @param string $email
     * @return int|null
     */
    private function resolveCustomerId(string $email): ?int
    {
        if ($email === '') {
            return null;
        }

        try {
            return (int) $this->customerRepository->get($email)->getId();
        } catch (\Exception $e) {
            // Unknown email or unavailable account scope; id is simply omitted.
            return null;
        }
    }
}
