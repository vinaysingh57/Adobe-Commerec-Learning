<?php
namespace Vendor\CrudExample\Controller\Adminhtml\Item;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Vendor\CrudExample\Model\ItemFactory;

class Save extends Action
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
        $data = $this->getRequest()->getPostValue();
        $item = $this->itemFactory->create();
        if (isset($data['item_id'])) {
            $item->load($data['item_id']);
        }
        $item->setData($data);
        $item->save();
        $this->messageManager->addSuccessMessage(__('Item saved successfully.'));
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
