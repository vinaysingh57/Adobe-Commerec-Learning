<?php
namespace Vendor\CrudExample\Ui\Component;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Vendor\CrudExample\Model\ResourceModel\Item\CollectionFactory;
use Magento\Framework\App\RequestInterface;

class FormDataProvider extends AbstractDataProvider
{
    protected $collection;
    protected $request;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->request = $request;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        $itemId = $this->request->getParam('item_id');
        $data = [];
        if ($itemId) {
            $item = $this->collection->getItemById($itemId);
            if ($item) {
                $data[$itemId] = $item->getData();
            }
        }
        return $data;
    }
}
