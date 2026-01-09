<?php
/**
 * Copyright © Vendor. All rights reserved.
 */

namespace Vendor\CrudModule\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Item extends AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('vendor_crud_item', 'item_id');
    }
}
