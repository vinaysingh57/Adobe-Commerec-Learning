<?php
namespace Vendor\CrudExample\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Item extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('vendor_crudexample_item', 'item_id');
    }
}
