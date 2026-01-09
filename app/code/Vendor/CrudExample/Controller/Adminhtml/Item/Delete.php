<?php
namespace Vendor\CrudExample\Controller\Adminhtml\Item;

use Magento\Backend\App\Action;
use Vendor\CrudExample\Model\ItemFactory;

class Delete extends Action
{
    protected $itemFactory;

    public function __construct(
        Action\Context $context,
        ItemFactory $itemFactory
    ) {
        $this->itemFactory = $itemFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('item_id');
        if ($id) {
            $item = $this->itemFactory->create()->load($id);
            if ($item->getId()) {
                $item->delete();
                $this->messageManager->addSuccessMessage(__('Item deleted successfully.'));
            }
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }

        /**
         * Check admin permissions
         */
        protected function _isAllowed()
        {
            return $this->_authorization->isAllowed('Vendor_CrudExample::crudexample_item');
        }
}
