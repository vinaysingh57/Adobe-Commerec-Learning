<?php
/**
 * Copyright © Vendor. All rights reserved.
 */

namespace Vendor\CrudModule\Api\Data;

interface ItemInterface
{
    const ITEM_ID = 'item_id';
    const TITLE = 'title';
    const DESCRIPTION = 'description';
    const STATUS = 'status';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Get Item ID
     *
     * @return int|null
     */
    public function getItemId();

    /**
     * Set Item ID
     *
     * @param int $itemId
     * @return $this
     */
    public function setItemId($itemId);

    /**
     * Get Title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set Title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * Get Description
     *
     * @return string|null
     */
    public function getDescription();

    /**
     * Set Description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * Get Status
     *
     * @return bool
     */
    public function getStatus();

    /**
     * Set Status
     *
     * @param bool $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Get Created At
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set Created At
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get Updated At
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set Updated At
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);
}
