<?php
namespace Vendor\CrudExample\Controller\Adminhtml\Item;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    protected $resultPageFactory;

    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        return $this->resultPageFactory->create();
    }

        /**
         * Check admin permissions
         */
        protected function _isAllowed()
        {
            return $this->_authorization->isAllowed('Vendor_CrudExample::crudexample_item');
        }
}
