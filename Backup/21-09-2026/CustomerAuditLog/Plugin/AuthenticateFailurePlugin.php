<?php
declare(strict_types=1);

namespace Abbott\CustomerAuditLog\Plugin;

use Abbott\CustomerAuditLog\Api\Data\AuditEventInterface;
use Abbott\CustomerAuditLog\Model\AuditEventBuilder;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\LocalizedException;
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
    )
    {
        $this->auditEventBuilder = $auditEventBuilder;
        $this->customerRepository = $customerRepository;
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
            $this->record((string)$email);
            throw $e;
        } catch (EmailNotConfirmedException $e) {
            $this->record((string)$email);
            throw $e;
        } catch (InvalidEmailOrPasswordException $e) {
            $this->record((string)$email);
            throw $e;
        } catch (NoSuchEntityException $e) {
            $this->record((string)$email);
            throw $e;
        }
    }

    /**
     * @param string $email
     * @return void
     */
    private function record(string $email): void
    {
        $this->auditEventBuilder->record(
            AuditEventInterface::TYPE_LOGIN_FAILURE,
            $this->resolveCustomerIdByEmail($email),
            AuditEventInterface::STATUS_FAILURE
        );
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
}
