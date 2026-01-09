<?php
/**
 * Copyright © Vendor. All rights reserved.
 */

namespace Vendor\CrudModule\Controller\Adminhtml\Item;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Vendor\CrudModule\Model\ResourceModel\Item\CollectionFactory;
use Vendor\CrudModule\Api\ItemRepositoryInterface;

class MassDelete extends Action
{
    const ADMIN_RESOURCE = 'Vendor_CrudModule::item_delete';

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ItemRepositoryInterface
     */
    protected $itemRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param ItemRepositoryInterface $itemRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ItemRepositoryInterface $itemRepository
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->itemRepository = $itemRepository;
    }

    /**
     * Mass delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();

        foreach ($collection as $item) {
            $this->itemRepository->delete($item);
        }

        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $collectionSize));

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
