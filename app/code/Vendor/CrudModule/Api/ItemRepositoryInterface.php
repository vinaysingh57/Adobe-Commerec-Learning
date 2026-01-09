<?php
/**
 * Copyright © Vendor. All rights reserved.
 */

namespace Vendor\CrudModule\Api;

use Vendor\CrudModule\Api\Data\ItemInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface ItemRepositoryInterface
{
    /**
     * Save Item
     *
     * @param ItemInterface $item
     * @return ItemInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(ItemInterface $item);

    /**
     * Get Item by ID
     *
     * @param int $itemId
     * @return ItemInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($itemId);

    /**
     * Delete Item
     *
     * @param ItemInterface $item
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(ItemInterface $item);

    /**
     * Delete Item by ID
     *
     * @param int $itemId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($itemId);
}
