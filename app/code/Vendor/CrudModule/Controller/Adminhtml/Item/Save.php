<?php
/**
 * Copyright © Vendor. All rights reserved.
 */

namespace Vendor\CrudModule\Controller\Adminhtml\Item;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Vendor\CrudModule\Api\ItemRepositoryInterface;
use Vendor\CrudModule\Model\ItemFactory;

class Save extends Action
{
    const ADMIN_RESOURCE = 'Vendor_CrudModule::item_save';

    /**
     * @var ItemRepositoryInterface
     */
    protected $itemRepository;

    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @param Context $context
     * @param ItemRepositoryInterface $itemRepository
     * @param ItemFactory $itemFactory
     */
    public function __construct(
        Context $context,
        ItemRepositoryInterface $itemRepository,
        ItemFactory $itemFactory
    ) {
        parent::__construct($context);
        $this->itemRepository = $itemRepository;
        $this->itemFactory = $itemFactory;
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $id = $this->getRequest()->getParam('item_id');

            try {
                if ($id) {
                    $model = $this->itemRepository->getById($id);
                } else {
                    $model = $this->itemFactory->create();
                }

                $model->setData($data);
                $this->itemRepository->save($model);
                
                $this->messageManager->addSuccessMessage(__('Item has been saved.'));
                
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['item_id' => $model->getItemId()]);
                }
                
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                if ($id) {
                    return $resultRedirect->setPath('*/*/edit', ['item_id' => $id]);
                }
                return $resultRedirect->setPath('*/*/new');
            }
        }
        
        return $resultRedirect->setPath('*/*/');
    }
}
