<?php
declare(strict_types=1);

namespace Abbott\CustomerAuditLog\Plugin;

use Abbott\CustomerAuditLog\Api\Data\AuditEventInterface;
use Abbott\CustomerAuditLog\Model\AuditEventBuilder;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\UserLockedException;

/**
 * Captures failed login attempts; AccountManagement::authenticate() has no native
 * failure event (it only dispatches "customer_login" on success).
 */
class AuthenticateFailurePlugin
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
     * @param AccountManagementInterface $subject
     * @param callable $proceed
     * @param string $email
     * @param string $password
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Exception
     */
    public function aroundAuthenticate(
        AccountManagementInterface $subject,
        callable $proceed,
        $email,
        $password
    ) {
        try {
            return $proceed($email, $password);
        } catch (UserLockedException $e) {
            $this->record($email, 'account_locked');
            throw $e;
        } catch (EmailNotConfirmedException $e) {
            $this->record($email, 'email_not_confirmed');
            throw $e;
        } catch (InvalidEmailOrPasswordException $e) {
            $this->record($email, 'invalid_credentials');
            throw $e;
        } catch (NoSuchEntityException $e) {
            $this->record($email, 'no_such_customer');
            throw $e;
        }
    }

    /**
     * @param string $username
     * @param string $reasonCode
     * @return void
     */
    private function record(string $username, string $reasonCode): void
    {
        $this->auditEventBuilder->record(
            AuditEventInterface::TYPE_LOGIN_FAILURE,
            AuditEventInterface::STATUS_FAILURE,
            null,
            $username,
            $reasonCode
        );
    }
}
