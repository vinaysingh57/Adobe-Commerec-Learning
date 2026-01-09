<?php
/**
 * Copyright © Vendor. All rights reserved.
 */

namespace Vendor\CrudModule\Model\ResourceModel\Item;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'item_id';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Vendor\CrudModule\Model\Item::class,
            \Vendor\CrudModule\Model\ResourceModel\Item::class
        );
    }
}
