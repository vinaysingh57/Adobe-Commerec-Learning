<?php
namespace Vendor\CrudExample\Model;

use Magento\Framework\Model\AbstractModel;

class Item extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('Vendor\CrudExample\Model\ResourceModel\Item');
    }
}
