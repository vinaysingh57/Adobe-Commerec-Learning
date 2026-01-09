<?php
/**
 * Copyright © Vendor. All rights reserved.
 */

namespace Vendor\CrudModule\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Patch to create vendor_crud_item table
 */
class CreateItemTable implements SchemaPatchInterface
{
    /**
     * @var SchemaSetupInterface
     */
    private $schemaSetup;

    /**
     * @param SchemaSetupInterface $schemaSetup
     */
    public function __construct(
        SchemaSetupInterface $schemaSetup
    ) {
        $this->schemaSetup = $schemaSetup;
    }

    /**
     * Apply patch
     *
     * @return void
     */
    public function apply()
    {
        $this->schemaSetup->startSetup();

        $tableName = $this->schemaSetup->getTable('vendor_crud_item');

        // Check if table already exists
        if (!$this->schemaSetup->getConnection()->isTableExists($tableName)) {
            $table = $this->schemaSetup->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'item_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'Item ID'
                )
                ->addColumn(
                    'title',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => false
                    ],
                    'Item Title'
                )
                ->addColumn(
                    'description',
                    Table::TYPE_TEXT,
                    '2M',
                    [
                        'nullable' => true
                    ],
                    'Item Description'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_SMALLINT,
                    null,
                    [
                        'nullable' => false,
                        'default' => 1
                    ],
                    'Status'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    [
                        'nullable' => false,
                        'default' => Table::TIMESTAMP_INIT
                    ],
                    'Created At'
                )
                ->addColumn(
                    'updated_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    [
                        'nullable' => false,
                        'default' => Table::TIMESTAMP_INIT_UPDATE
                    ],
                    'Updated At'
                )
                ->addIndex(
                    $this->schemaSetup->getIdxName('vendor_crud_item', ['title']),
                    ['title']
                )
                ->addIndex(
                    $this->schemaSetup->getIdxName('vendor_crud_item', ['status']),
                    ['status']
                )
                ->addIndex(
                    $this->schemaSetup->getIdxName('vendor_crud_item', ['created_at']),
                    ['created_at']
                )
                ->setComment('Vendor CRUD Item Table');

            $this->schemaSetup->getConnection()->createTable($table);
        }

        $this->schemaSetup->endSetup();
    }

    /**
     * Get dependencies
     *
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Get aliases
     *
     * @return array
     */
    public function getAliases()
    {
        return [];
    }
}
