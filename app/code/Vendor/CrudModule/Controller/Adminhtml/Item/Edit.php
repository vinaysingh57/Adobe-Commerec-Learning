<?php
/**
 * Copyright © Vendor. All rights reserved.
 */

namespace Vendor\CrudModule\Controller\Adminhtml\Item;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Vendor\CrudModule\Api\ItemRepositoryInterface;

class Edit extends Action
{
    const ADMIN_RESOURCE = 'Vendor_CrudModule::item_save';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var ItemRepositoryInterface
     */
    protected $itemRepository;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ItemRepositoryInterface $itemRepository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ItemRepositoryInterface $itemRepository
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->itemRepository = $itemRepository;
    }

    /**
     * Edit action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('item_id');
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Vendor_CrudModule::item');
        
        if ($id) {
            try {
                $this->itemRepository->getById($id);
                $resultPage->getConfig()->getTitle()->prepend(__('Edit Item'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('This item no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('New Item'));
        }
        
        return $resultPage;
    }
}
