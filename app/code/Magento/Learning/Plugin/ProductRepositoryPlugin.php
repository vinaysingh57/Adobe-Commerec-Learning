<?php
namespace Magento\Learning\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class ProductRepositoryPlugin
{

    protected $extensionFactory;

    public function __construct(
        \Magento\Catalog\Api\Data\ProductExtensionFactory $extensionFactory
    ) {
        $this->extensionFactory = $extensionFactory;
    }
    public function afterGet(
        ProductRepositoryInterface $subject,
        ProductInterface $product
    ) {
        $extensionAttributes = $product->getExtensionAttributes();
        if ($extensionAttributes === null) {
            $extensionAttributes = $this->extensionFactory->create();
        }

        $extensionAttributes->setCustomLabel('Special Edition');
        $product->setExtensionAttributes($extensionAttributes);

        return $product;
    }

    public function afterGetById(
        ProductRepositoryInterface $subject,
        ProductInterface $product
    ) {
        return $this->afterGet($subject, $product);
    }


}
