<?php
namespace Vendor\CrudExample\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (!$setup->tableExists('vendor_crudexample_item')) {
            $table = $setup->getConnection()->newTable(
                $setup->getTable('vendor_crudexample_item')
            )
                ->addColumn(
                    'item_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Item ID'
                )
                ->addColumn(
                    'name',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Name'
                )
                ->addColumn(
                    'description',
                    Table::TYPE_TEXT,
                    '64k',
                    [],
                    'Description'
                )
                ->setComment('Vendor CrudExample Item Table');
            $setup->getConnection()->createTable($table);
        }

        $setup->endSetup();
    }
}
