<?php
namespace Vendor\CrudExample\Model\ResourceModel\Item;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Vendor\CrudExample\Model\Item', 'Vendor\CrudExample\Model\ResourceModel\Item');
    }
}
