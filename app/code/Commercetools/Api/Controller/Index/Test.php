<?php
/**
 * Copyright © Commercetools API Integration. All rights reserved.
 */

declare(strict_types=1);

namespace Commercetools\Api\Controller\Index;

use Commercetools\Api\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Psr\Log\LoggerInterface;

/**
 * Test controller for Commercetools API integration
 */
class Test implements HttpGetActionInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var JsonFactory
     */
    private JsonFactory $resultJsonFactory;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param RequestInterface $request
     * @param JsonFactory $resultJsonFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        RequestInterface $request,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger
    ) {
        $this->productRepository = $productRepository;
        $this->request = $request;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
    }

    /**
     * Execute action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $resultJson = $this->resultJsonFactory->create();

        try {
            $productId = $this->request->getParam('product_id');
            $productKey = $this->request->getParam('product_key');
            $query = $this->request->getParam('search');
            $limit = (int)$this->request->getParam('limit', 10);

            if ($productId) {
                // Get single product by ID
                $product = $this->productRepository->getById($productId);
                return $resultJson->setData([
                    'success' => true,
                    'product' => [
                        'id' => $product->getId(),
                        'key' => $product->getKey(),
                        'version' => $product->getVersion(),
                        'created_at' => $product->getCreatedAt(),
                        'last_modified_at' => $product->getLastModifiedAt(),
                        'master_data' => $product->getMasterData(),
                        'product_type' => $product->getProductType(),
                        'catalog_data' => $product->getCatalogData()
                    ]
                ]);
            } elseif ($productKey) {
                // Get single product by key
                
                //echo "<pre>";print_r($productKey);echo "</pre>";die;
                $product = $this->productRepository->getByKey($productKey);
                echo "product <pre>";print_r($product);echo "</pre>";die;
                return $resultJson->setData([
                    'success' => true,
                    'product' => [
                        'id' => $product->getId(),
                        'key' => $product->getKey(),
                        'version' => $product->getVersion(),
                        'created_at' => $product->getCreatedAt(),
                        'last_modified_at' => $product->getLastModifiedAt(),
                        'master_data' => $product->getMasterData(),
                        'product_type' => $product->getProductType(),
                        'catalog_data' => $product->getCatalogData()
                    ]
                ]);
            } elseif ($query) {
                // Search products
                $products = $this->productRepository->searchProducts($query, $limit);
                $productsData = [];
                foreach ($products as $product) {
                    $productsData[] = [
                        'id' => $product->getId(),
                        'key' => $product->getKey(),
                        'version' => $product->getVersion(),
                        'created_at' => $product->getCreatedAt(),
                        'last_modified_at' => $product->getLastModifiedAt(),
                        'master_data' => $product->getMasterData(),
                        'product_type' => $product->getProductType(),
                        'catalog_data' => $product->getCatalogData()
                    ];
                }
                return $resultJson->setData([
                    'success' => true,
                    'products' => $productsData,
                    'count' => count($productsData)
                ]);
            } else {
                // Get products list
                $products = $this->productRepository->getList($limit);
                $productsData = [];
                foreach ($products as $product) {
                    $productsData[] = [
                        'id' => $product->getId(),
                        'key' => $product->getKey(),
                        'version' => $product->getVersion(),
                        'created_at' => $product->getCreatedAt(),
                        'last_modified_at' => $product->getLastModifiedAt(),
                        'master_data' => $product->getMasterData(),
                        'product_type' => $product->getProductType(),
                        'catalog_data' => $product->getCatalogData()
                    ];
                }
                return $resultJson->setData([
                    'success' => true,
                    'products' => $productsData,
                    'count' => count($productsData)
                ]);
            }

        } catch (\Exception $e) {
            $this->logger->error('Commercetools API test controller error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return $resultJson->setData([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}