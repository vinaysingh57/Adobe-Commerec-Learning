<?php
namespace Vinay\Chatbot\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\Action;

class CsrfValidatorSkip
{
    public function aroundValidate(
        \Magento\Framework\App\Request\CsrfValidator $subject,
        \Closure $proceed,
        RequestInterface $request,
        $action
    ) {
        // Skip CSRF for chatbot AJAX endpoint
        if ($request->getModuleName() === 'chatbot' && $request->getControllerName() === 'ajax' && $request->getActionName() === 'message') {
            return null;
        }
        return $proceed($request, $action);
    }
}
