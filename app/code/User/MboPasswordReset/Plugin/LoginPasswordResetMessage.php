<?php
declare(strict_types=1);

namespace User\MboPasswordReset\Plugin;

use Magento\Backend\Controller\Adminhtml\Auth\Login;
use Magento\Framework\Message\ManagerInterface;

/**
 * Injects the post-reset success message on the login page.
 *
 * The Save controller redirects here with ?password_reset=1 because the
 * admin session cookie is deleted during logout(), so storing the message
 * in the session would not survive the redirect.
 */
class LoginPasswordResetMessage
{
    private ManagerInterface $messageManager;

    public function __construct(ManagerInterface $messageManager)
    {
        $this->messageManager = $messageManager;
    }

    public function afterExecute(Login $subject, $result)
    {
        if ((string)$subject->getRequest()->getParam('password_reset') === '1') {
            $this->messageManager->addSuccessMessage(
                __('Password updated successfully. Please log in with your new password.')
            );
        }
        return $result;
    }
}
