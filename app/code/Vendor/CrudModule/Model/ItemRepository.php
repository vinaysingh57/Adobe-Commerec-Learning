<?php
/**
 * Copyright © Vendor. All rights reserved.
 */

namespace Vendor\CrudModule\Model;

use Vendor\CrudModule\Api\Data\ItemInterface;
use Vendor\CrudModule\Api\ItemRepositoryInterface;
use Vendor\CrudModule\Model\ResourceModel\Item as ItemResource;
use Vendor\CrudModule\Model\ItemFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;

class ItemRepository implements ItemRepositoryInterface
{
    /**
     * @var ItemResource
     */
    protected $resource;

    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @param ItemResource $resource
     * @param ItemFactory $itemFactory
     */
    public function __construct(
        ItemResource $resource,
        ItemFactory $itemFactory
    ) {
        $this->resource = $resource;
        $this->itemFactory = $itemFactory;
    }

    /**
     * Save Item
     *
     * @param ItemInterface $item
     * @return ItemInterface
     * @throws CouldNotSaveException
     */
    public function save(ItemInterface $item)
    {
        try {
            $this->resource->save($item);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the item: %1', $exception->getMessage())
            );
        }
        return $item;
    }

    /**
     * Get Item by ID
     *
     * @param int $itemId
     * @return ItemInterface
     * @throws NoSuchEntityException
     */
    public function getById($itemId)
    {
        $item = $this->itemFactory->create();
        $this->resource->load($item, $itemId);
        if (!$item->getId()) {
            throw new NoSuchEntityException(__('Item with id "%1" does not exist.', $itemId));
        }
        return $item;
    }

    /**
     * Delete Item
     *
     * @param ItemInterface $item
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ItemInterface $item)
    {
        try {
            $this->resource->delete($item);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the item: %1', $exception->getMessage())
            );
        }
        return true;
    }

    /**
     * Delete Item by ID
     *
     * @param int $itemId
     * @return bool
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById($itemId)
    {
        return $this->delete($this->getById($itemId));
    }
}
