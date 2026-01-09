<?php
namespace Webapi\Learning\Model;

use Webapi\Learning\Api\ProductNameUpdateInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class ProductNameUpdate implements ProductNameUpdateInterface
{
    protected $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function update($sku, $name)
    {
        try {
            $product = $this->productRepository->get($sku);
            $product->setName($name);
            $this->productRepository->save($product);
            return true;
        } catch (NoSuchEntityException $e) {
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
