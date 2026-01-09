<?php
namespace Vendor\CrudExample\Model;

use Magento\Framework\ObjectManagerInterface;

class ItemFactory
{
    protected $objectManager;
    protected $instanceName;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
        $this->instanceName = Item::class;
    }

    public function create(array $data = [])
    {
        return $this->objectManager->create($this->instanceName, $data);
    }
}
