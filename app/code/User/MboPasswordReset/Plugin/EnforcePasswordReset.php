<?php
declare(strict_types=1);

namespace User\MboPasswordReset\Plugin;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\Model\Auth\Session as AuthSession;
use Magento\Backend\Model\UrlInterface as BackendUrl;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;

/**
 * Intercepts every admin controller dispatch.
 *
 * If the logged-in user is an MBO user with force_password_change = 1,
 * all requests are redirected to the password-reset page except:
 *   - the reset/save controllers themselves (route: mbopasswordreset)
 *   - the logout action (admin/auth/logout)
 */
class EnforcePasswordReset
{
    private AuthSession $authSession;
    private RedirectFactory $resultRedirectFactory;
    private JsonFactory $resultJsonFactory;
    private BackendUrl $backendUrl;

    public function __construct(
        AuthSession $authSession,
        RedirectFactory $resultRedirectFactory,
        JsonFactory $resultJsonFactory,
        BackendUrl $backendUrl
    ) {
        $this->authSession           = $authSession;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->resultJsonFactory     = $resultJsonFactory;
        $this->backendUrl            = $backendUrl;
    }

    /**
     * @param AbstractAction   $subject
     * @param callable         $proceed
     * @param RequestInterface $request
     * @return mixed
     */
    public function aroundDispatch(
        AbstractAction $subject,
        callable $proceed,
        RequestInterface $request
    ) {
        if ($this->mustRedirectToReset($request)) {
            // AJAX callers expect JSON; use ajaxRedirect so admin JS handles the navigation.
            if ($request instanceof HttpRequest && $request->isXmlHttpRequest()) {
                return $this->resultJsonFactory->create()->setData([
                    'ajaxExpired'  => 1,
                    'ajaxRedirect' => $this->backendUrl->getUrl('mbopasswordreset/password/reset'),
                ]);
            }

            /** @var Redirect $redirect */
            $redirect = $this->resultRedirectFactory->create();
            $redirect->setPath('mbopasswordreset/password/reset');
            return $redirect;
        }

        return $proceed($request);
    }

    private function mustRedirectToReset(RequestInterface $request): bool
    {
        // Only enforce for authenticated sessions carrying the flag.
        if (!$this->authSession->isLoggedIn()) {
            return false;
        }

        if (!$this->authSession->getMboForcePasswordChange()) {
            return false;
        }

        if (!$request instanceof HttpRequest) {
            return false;
        }

        $module     = $request->getModuleName();
        $controller = $request->getControllerName();
        $action     = $request->getActionName();

        // Allow our own reset / save controllers.
        if ($module === 'mbopasswordreset') {
            return false;
        }

        // Allow logout so the user is never truly locked in.
        if ($module === 'admin' && $controller === 'auth' && $action === 'logout') {
            return false;
        }

        return true;
    }
}
