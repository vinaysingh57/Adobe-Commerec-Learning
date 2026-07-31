<?php
declare(strict_types=1);

namespace User\MboPasswordReset\Controller\Adminhtml\Password;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session as AuthSession;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Renders the forced password-reset form (GET).
 *
 * Accessible only when:
 *   1. The user is authenticated in the backend session.
 *   2. The MBO force-password-change flag is set in that session.
 *
 * URL: /admin/mbopasswordreset/password/reset
 */
class Reset extends Action implements HttpGetActionInterface
{
    private PageFactory $resultPageFactory;
    private AuthSession $authSession;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        AuthSession $authSession
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->authSession       = $authSession;
    }

    /**
     * Allow any authenticated admin user — ACL is irrelevant for a forced reset.
     */
    protected function _isAllowed(): bool
    {
        return $this->authSession->isLoggedIn();
    }

    public function execute()
    {
        // Safety guard: if flag was already cleared, send to dashboard.
        if (!$this->authSession->getMboForcePasswordChange()) {
            return $this->resultRedirectFactory->create()->setPath('adminhtml/dashboard/index');
        }

        /** @var Page $page */
        $page = $this->resultPageFactory->create();
        $page->getConfig()->getTitle()->set(__('Reset Your Password'));
        return $page;
    }
}
