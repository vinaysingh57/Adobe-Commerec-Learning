<?php
namespace Vendor\CrudExample\Ui\Component;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Vendor\CrudExample\Model\ResourceModel\Item\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    protected $collection;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        $items = $this->collection->getItems();
        return [
            'totalRecords' => $this->collection->getSize(),
            'items' => array_map(function ($item) {
                return $item->getData();
            }, $items)
        ];
    }
}
