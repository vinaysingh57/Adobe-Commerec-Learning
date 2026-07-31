<?php
declare(strict_types=1);

namespace User\MboPasswordReset\Observer;

use Magento\Backend\Model\Auth\Session as AuthSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\User\Model\User;

/**
 * Listens to backend_auth_user_login_success.
 *
 * If the authenticated user is an MBO user with force_password_change = 1,
 * a flag is stored in the backend session so the EnforcePasswordReset plugin
 * can redirect subsequent requests to the reset page.
 */
class CheckForcePasswordChange implements ObserverInterface
{
    private AuthSession $authSession;

    public function __construct(AuthSession $authSession)
    {
        $this->authSession = $authSession;
    }

    public function execute(Observer $observer): void
    {
        /** @var User $user */
        $user = $observer->getEvent()->getUser();

        if (!$user || !$user->getId()) {
            return;
        }

        $mustReset = (bool)(int)$user->getData('force_password_change');

        if ($mustReset) {
            $this->authSession->setMboForcePasswordChange(true);
        } else {
            // Clear any stale flag from a previous session.
            $this->authSession->unsMboForcePasswordChange();
        }
    }
}
