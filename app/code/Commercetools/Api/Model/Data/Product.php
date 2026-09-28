<?php
/**
 * Copyright © Commercetools API Integration. All rights reserved.
 */

declare(strict_types=1);

namespace Commercetools\Api\Model\Data;

use Commercetools\Api\Api\Data\ProductInterface;

/**
 * Product data model for Commercetools API response
 */
class Product implements ProductInterface
{
    /**
     * @var array
     */
    private array $data = [];

    /**
     * @inheritDoc
     */
    public function getId(): ?string
    {
        return $this->data[self::ID] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function setId(string $id): ProductInterface
    {
        $this->data[self::ID] = $id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getKey(): ?string
    {
        return $this->data[self::KEY] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function setKey(string $key): ProductInterface
    {
        $this->data[self::KEY] = $key;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getVersion(): ?int
    {
        return $this->data[self::VERSION] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function setVersion(int $version): ProductInterface
    {
        $this->data[self::VERSION] = $version;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): ?string
    {
        return $this->data[self::CREATED_AT] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(string $createdAt): ProductInterface
    {
        $this->data[self::CREATED_AT] = $createdAt;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getLastModifiedAt(): ?string
    {
        return $this->data[self::LAST_MODIFIED_AT] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function setLastModifiedAt(string $lastModifiedAt): ProductInterface
    {
        $this->data[self::LAST_MODIFIED_AT] = $lastModifiedAt;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMasterData(): array
    {
        return $this->data[self::MASTER_DATA] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function setMasterData(array $masterData): ProductInterface
    {
        $this->data[self::MASTER_DATA] = $masterData;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getProductType(): array
    {
        return $this->data[self::PRODUCT_TYPE] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function setProductType(array $productType): ProductInterface
    {
        $this->data[self::PRODUCT_TYPE] = $productType;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCatalogData(): array
    {
        return $this->data[self::CATALOG_DATA] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function setCatalogData(array $catalogData): ProductInterface
    {
        $this->data[self::CATALOG_DATA] = $catalogData;
        return $this;
    }

    /**
     * Set data from array
     *
     * @param array $data
     * @return $this
     */
    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Get all data as array
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}