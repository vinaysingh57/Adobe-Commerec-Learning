<?php
/**
 * Copyright © Commercetools API Integration. All rights reserved.
 */

declare(strict_types=1);

namespace Commercetools\Api\Api;

use Commercetools\Api\Api\Data\ProductInterface;

/**
 * Product repository interface for Commercetools API
 * @api
 */
interface ProductRepositoryInterface
{
    /**
     * Get product by ID from Commercetools
     *
     * @param string $productId
     * @return ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById(string $productId): ProductInterface;

    /**
     * Get product by key from Commercetools
     *
     * @param string $productKey
     * @return ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByKey(string $productKey): ProductInterface;

    /**
     * Get products list from Commercetools
     *
     * @param int $limit
     * @param int $offset
     * @param array $filters
     * @return ProductInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(int $limit = 20, int $offset = 0, array $filters = []): array;

    /**
     * Search products by query
     *
     * @param string $query
     * @param int $limit
     * @param int $offset
     * @return ProductInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function searchProducts(string $query, int $limit = 20, int $offset = 0): array;
}