<?php
/**
 * Copyright © Vendor. All rights reserved.
 */

namespace Vendor\CrudModule\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Patch to add sample data to vendor_crud_item table
 */
class AddSampleItems implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Apply patch
     *
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $tableName = $this->moduleDataSetup->getTable('vendor_crud_item');
        
        // Check if table exists before inserting data
        if ($this->moduleDataSetup->getConnection()->isTableExists($tableName)) {
            // Sample data to insert
            $data = [
                [
                    'title' => 'Welcome to CRUD Module',
                    'description' => 'This is the first sample item created by data patch. You can edit or delete this item from the admin panel.',
                    'status' => 1
                ],
                [
                    'title' => 'Sample Product Item',
                    'description' => 'This demonstrates how items can be used to manage different types of content in your Magento store.',
                    'status' => 1
                ],
                [
                    'title' => 'Test Item - Disabled',
                    'description' => 'This is a disabled item to show the status functionality. You can enable it from the grid or edit form.',
                    'status' => 0
                ],
                [
                    'title' => 'Feature Announcement',
                    'description' => 'Use this CRUD module as a base for creating your own custom entities with full admin management capabilities.',
                    'status' => 1
                ],
                [
                    'title' => 'Documentation Item',
                    'description' => 'Check the README.md and INSTALLATION.md files for complete documentation on using and customizing this module.',
                    'status' => 1
                ]
            ];

            // Insert data
            $this->moduleDataSetup->getConnection()->insertMultiple($tableName, $data);
        }

        $this->moduleDataSetup->getConnection()->endSetup();
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
