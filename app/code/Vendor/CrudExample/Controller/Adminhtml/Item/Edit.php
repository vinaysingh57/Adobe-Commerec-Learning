<?php
namespace Vendor\CrudExample\Controller\Adminhtml\Item;

use Magento\Backend\App\Action;
use Vendor\CrudExample\Model\ItemFactory;

class Edit extends Action
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
        $item = $this->itemFactory->create();
        if ($id) {
            $item->load($id);
        }
        $this->_coreRegistry->register('crudexample_item', $item);
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
