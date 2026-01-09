<?php
/**
 * Copyright © Vendor. All rights reserved.
 */

namespace Vendor\CrudModule\Block\Adminhtml\Item\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Backend\Block\Widget\Context;

class DeleteButton implements ButtonProviderInterface
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        $itemId = $this->context->getRequest()->getParam('item_id');
        
        if ($itemId) {
            $data = [
                'label' => __('Delete Item'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to delete this item?'
                ) . '\', \'' . $this->getDeleteUrl() . '\')',
                'sort_order' => 20,
            ];
        }
        
        return $data;
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        $itemId = $this->context->getRequest()->getParam('item_id');
        return $this->context->getUrlBuilder()->getUrl('*/*/delete', ['item_id' => $itemId]);
    }
}
