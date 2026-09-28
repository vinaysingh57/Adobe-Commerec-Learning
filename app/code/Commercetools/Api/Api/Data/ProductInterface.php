<?php
/**
 * Copyright © Commercetools API Integration. All rights reserved.
 */

declare(strict_types=1);

namespace Commercetools\Api\Api\Data;

/**
 * Product interface for Commercetools API response
 * @api
 */
interface ProductInterface
{
    const ID = 'id';
    const KEY = 'key';
    const VERSION = 'version';
    const CREATED_AT = 'created_at';
    const LAST_MODIFIED_AT = 'last_modified_at';
    const MASTER_DATA = 'master_data';
    const PRODUCT_TYPE = 'product_type';
    const CATALOG_DATA = 'catalog_data';

    /**
     * Get product ID
     *
     * @return string|null
     */
    public function getId(): ?string;

    /**
     * Set product ID
     *
     * @param string $id
     * @return $this
     */
    public function setId(string $id): self;

    /**
     * Get product key
     *
     * @return string|null
     */
    public function getKey(): ?string;

    /**
     * Set product key
     *
     * @param string $key
     * @return $this
     */
    public function setKey(string $key): self;

    /**
     * Get version
     *
     * @return int|null
     */
    public function getVersion(): ?int;

    /**
     * Set version
     *
     * @param int $version
     * @return $this
     */
    public function setVersion(int $version): self;

    /**
     * Get created at
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt(string $createdAt): self;

    /**
     * Get last modified at
     *
     * @return string|null
     */
    public function getLastModifiedAt(): ?string;

    /**
     * Set last modified at
     *
     * @param string $lastModifiedAt
     * @return $this
     */
    public function setLastModifiedAt(string $lastModifiedAt): self;

    /**
     * Get master data
     *
     * @return array
     */
    public function getMasterData(): array;

    /**
     * Set master data
     *
     * @param array $masterData
     * @return $this
     */
    public function setMasterData(array $masterData): self;

    /**
     * Get product type
     *
     * @return array
     */
    public function getProductType(): array;

    /**
     * Set product type
     *
     * @param array $productType
     * @return $this
     */
    public function setProductType(array $productType): self;

    /**
     * Get catalog data
     *
     * @return array
     */
    public function getCatalogData(): array;

    /**
     * Set catalog data
     *
     * @param array $catalogData
     * @return $this
     */
    public function setCatalogData(array $catalogData): self;
}