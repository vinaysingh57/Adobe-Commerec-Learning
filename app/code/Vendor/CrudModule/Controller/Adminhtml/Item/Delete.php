<?php
/**
 * Copyright © Vendor. All rights reserved.
 */

namespace Vendor\CrudModule\Controller\Adminhtml\Item;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Vendor\CrudModule\Api\ItemRepositoryInterface;

class Delete extends Action
{
    const ADMIN_RESOURCE = 'Vendor_CrudModule::item_delete';

    /**
     * @var ItemRepositoryInterface
     */
    protected $itemRepository;

    /**
     * @param Context $context
     * @param ItemRepositoryInterface $itemRepository
     */
    public function __construct(
        Context $context,
        ItemRepositoryInterface $itemRepository
    ) {
        parent::__construct($context);
        $this->itemRepository = $itemRepository;
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('item_id');

        if ($id) {
            try {
                $this->itemRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('Item has been deleted.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        return $resultRedirect->setPath('*/*/');
    }
}
