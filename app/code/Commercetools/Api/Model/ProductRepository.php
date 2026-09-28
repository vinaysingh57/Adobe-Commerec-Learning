<?php
/**
 * Copyright © Commercetools API Integration. All rights reserved.
 */

declare(strict_types=1);

namespace Commercetools\Api\Model;

use Commercetools\Api\Api\Data\ProductInterface;
use Commercetools\Api\Api\Data\ProductInterfaceFactory;
use Commercetools\Api\Api\ProductRepositoryInterface;
use Commercetools\Api\Service\ApiClient;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * Product repository implementation for Commercetools API
 */
class ProductRepository implements ProductRepositoryInterface
{
    /**
     * @var ApiClient
     */
    private ApiClient $apiClient;

    /**
     * @var ProductInterfaceFactory
     */
    private ProductInterfaceFactory $productFactory;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param ApiClient $apiClient
     * @param ProductInterfaceFactory $productFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ApiClient $apiClient,
        ProductInterfaceFactory $productFactory,
        LoggerInterface $logger
    ) {
        $this->apiClient = $apiClient;
        $this->productFactory = $productFactory;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function getById(string $productId): ProductInterface
    {
        try {
            $endpoint = sprintf('/products/%s', $productId);
            $response = $this->apiClient->get($endpoint);

            if (empty($response)) {
                throw new NoSuchEntityException(__('Product with ID %1 not found', $productId));
            }

            return $this->createProductFromResponse($response);

        } catch (LocalizedException $e) {
            $this->logger->error('Failed to get product by ID', [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Unexpected error getting product by ID', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new LocalizedException(__('Unable to retrieve product: %1', $e->getMessage()));
        }
    }

    /**
     * @inheritDoc
     */
    public function getByKey(string $productKey): ProductInterface
    {
        try {
            $endpoint = sprintf('/products/key=%s', $productKey);
            $response = $this->apiClient->get($endpoint);
            //echo "<pre>";print_r($response);echo "</pre>";die;
            if (empty($response)) {
                throw new NoSuchEntityException(__('Product with key %1 not found', $productKey));
            }

            return $this->createProductFromResponse($response);

        } catch (LocalizedException $e) {
            $this->logger->error('Failed to get product by key', [
                'product_key' => $productKey,
                'error' => $e->getMessage()
            ]);
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Unexpected error getting product by key', [
                'product_key' => $productKey,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new LocalizedException(__('Unable to retrieve product: %1', $e->getMessage()));
        }
    }

    /**
     * @inheritDoc
     */
    public function getList(int $limit = 20, int $offset = 0, array $filters = []): array
    {
        try {
            $params = [
                'limit' => $limit,
                'offset' => $offset
            ];

            // Add filters to params
            foreach ($filters as $key => $value) {
                $params[$key] = $value;
            }

            $response = $this->apiClient->get('/products', $params);

            if (empty($response['results'])) {
                return [];
            }

            $products = [];
            foreach ($response['results'] as $productData) {
                $products[] = $this->createProductFromResponse($productData);
            }

            return $products;

        } catch (LocalizedException $e) {
            $this->logger->error('Failed to get products list', [
                'limit' => $limit,
                'offset' => $offset,
                'filters' => $filters,
                'error' => $e->getMessage()
            ]);
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Unexpected error getting products list', [
                'limit' => $limit,
                'offset' => $offset,
                'filters' => $filters,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new LocalizedException(__('Unable to retrieve products: %1', $e->getMessage()));
        }
    }

    /**
     * @inheritDoc
     */
    public function searchProducts(string $query, int $limit = 20, int $offset = 0): array
    {
        try {
            $params = [
                'limit' => $limit,
                'offset' => $offset,
                'text.en' => $query, // Search in English text
                'fuzzy' => true
            ];

            $response = $this->apiClient->get('/product-projections/search', $params);

            if (empty($response['results'])) {
                return [];
            }

            $products = [];
            foreach ($response['results'] as $productData) {
                $products[] = $this->createProductFromResponse($productData);
            }

            return $products;

        } catch (LocalizedException $e) {
            $this->logger->error('Failed to search products', [
                'query' => $query,
                'limit' => $limit,
                'offset' => $offset,
                'error' => $e->getMessage()
            ]);
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Unexpected error searching products', [
                'query' => $query,
                'limit' => $limit,
                'offset' => $offset,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new LocalizedException(__('Unable to search products: %1', $e->getMessage()));
        }
    }

    /**
     * Create product instance from API response
     *
     * @param array $responseData
     * @return ProductInterface
     */
    private function createProductFromResponse(array $responseData): ProductInterface
    {
        /** @var ProductInterface $product */
        $product = $this->productFactory->create();
        
        if (isset($responseData['id'])) {
            $product->setId($responseData['id']);
        }
        
        if (isset($responseData['key'])) {
            $product->setKey($responseData['key']);
        }
        
        if (isset($responseData['version'])) {
            $product->setVersion($responseData['version']);
        }
        
        if (isset($responseData['createdAt'])) {
            $product->setCreatedAt($responseData['createdAt']);
        }
        
        if (isset($responseData['lastModifiedAt'])) {
            $product->setLastModifiedAt($responseData['lastModifiedAt']);
        }
        
        if (isset($responseData['masterData'])) {
            $product->setMasterData($responseData['masterData']);
        }
        
        if (isset($responseData['productType'])) {
            $product->setProductType($responseData['productType']);
        }
        
        if (isset($responseData['catalogData'])) {
            $product->setCatalogData($responseData['catalogData']);
        }

        // Set all response data for additional fields
        if (method_exists($product, 'setData')) {
            $product->setData($responseData);
        }

        return $product;
    }
}