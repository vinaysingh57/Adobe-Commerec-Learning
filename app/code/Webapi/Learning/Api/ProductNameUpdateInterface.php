<?php
namespace Webapi\Learning\Api;

interface ProductNameUpdateInterface
{
    /**
     * Update product name by SKU
     * @param string $sku
     * @param string $name
     * @return bool
     */
    public function update($sku, $name);
}
