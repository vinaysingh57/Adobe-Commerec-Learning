<?php
declare(strict_types=1);

namespace User\MboPasswordReset\Controller\Adminhtml\Password;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session as AuthSession;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\User\Model\UserFactory;

/**
 * Handles the forced password-reset form submission (POST).
 *
 * On success:
 *   - Sets force_password_change = 0 in the database.
 *   - Clears the session flag.
 *   - Logs the user out.
 *   - Redirects to the admin login page with a success message.
 *
 * On failure:
 *   - Adds an error message and redirects back to the reset form.
 *
 * URL: /admin/mbopasswordreset/password/save
 */
class Save extends Action implements HttpPostActionInterface
{
    private AuthSession $authSession;
    private UserFactory $userFactory;
    private ResourceConnection $resourceConnection;

    public function __construct(
        Context $context,
        AuthSession $authSession,
        UserFactory $userFactory,
        ResourceConnection $resourceConnection
    ) {
        parent::__construct($context);
        $this->authSession        = $authSession;
        $this->userFactory        = $userFactory;
        $this->resourceConnection = $resourceConnection;
    }

    protected function _isAllowed(): bool
    {
        return $this->authSession->isLoggedIn();
    }

    public function execute(): Redirect
    {
        /** @var Redirect $redirect */
        $redirect = $this->resultRedirectFactory->create();

        if (!$this->authSession->getMboForcePasswordChange()) {
            return $redirect->setPath('adminhtml/dashboard/index');
        }

        // CSRF form-key validation.
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(__('Invalid form key. Please refresh and try again.'));
            return $redirect->setPath('*/*/reset');
        }

        $currentPassword = (string)$this->getRequest()->getPost('current_password', '');
        $newPassword     = (string)$this->getRequest()->getPost('new_password', '');
        $confirmPassword = (string)$this->getRequest()->getPost('confirm_password', '');

        try {
            $this->validateInput($currentPassword, $newPassword, $confirmPassword);

            $userId    = (int)$this->authSession->getUser()->getId();
            $userModel = $this->userFactory->create()->load($userId);

            if (!$userModel->getId()) {
                throw new LocalizedException(__('User session is invalid. Please log in again.'));
            }

            // Verify the current password against the stored hash.
            if (!$userModel->verifyIdentity($currentPassword)) {
                throw new LocalizedException(__('Current password is incorrect.'));
            }

            // Persist new password and clear the force-reset flag atomically.
            $userModel->setPassword($newPassword);
            $userModel->setData('force_password_change', 0);
            $userModel->save();

            // Clear the session flag.
            $this->authSession->unsMboForcePasswordChange();

            // Write the success message before logout so it survives session teardown.
            $this->messageManager->addSuccessMessage(
                __('Password updated successfully. Please log in with your new password.')
            );

            // Log out so the user must authenticate with the new password.
            $this->_auth->logout();

            // Carry ?password_reset=1 so the Login plugin can re-inject the message
            // after the session cookie is gone (session is deleted on logout).
            return $redirect->setPath('adminhtml/auth/login', ['_query' => ['password_reset' => '1']]);

        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $redirect->setPath('*/*/reset');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('An unexpected error occurred while changing your password. Please try again.')
            );
            return $redirect->setPath('*/*/reset');
        }
    }

    /**
     * @throws LocalizedException
     */
    private function validateInput(string $current, string $new, string $confirm): void
    {
        if ($current === '' || $new === '' || $confirm === '') {
            throw new LocalizedException(__('All password fields are required.'));
        }

        if ($new !== $confirm) {
            throw new LocalizedException(__('New password and confirmation do not match.'));
        }

        if (mb_strlen($new) < 7) {
            throw new LocalizedException(__('Password must be at least 7 characters long.'));
        }

        // Require at least one letter and one digit (Magento admin password policy).
        if (!preg_match('/[a-zA-Z]/', $new) || !preg_match('/[0-9]/', $new)) {
            throw new LocalizedException(
                __('Password must contain at least one letter and one numeric character.')
            );
        }

        if ($current === $new) {
            throw new LocalizedException(
                __('Your new password must be different from the current password.')
            );
        }
    }
}
