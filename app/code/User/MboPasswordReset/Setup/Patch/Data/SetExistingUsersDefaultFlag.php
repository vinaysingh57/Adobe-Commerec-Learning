<?php
declare(strict_types=1);

namespace User\MboPasswordReset\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

/**
 * Ensures all existing admin users have force_password_change = 0 (No).
 * New MBO users will have it set to 1 via the admin_user_save_after observer.
 */
class SetExistingUsersDefaultFlag implements DataPatchInterface, PatchRevertableInterface
{
    private ModuleDataSetupInterface $moduleDataSetup;

    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public function apply(): self
    {
        $this->moduleDataSetup->startSetup();

        $connection = $this->moduleDataSetup->getConnection();
        $tableName  = $this->moduleDataSetup->getTable('admin_user');

        // Explicitly set 0 for any existing rows that may have NULL after the column is added.
        $connection->update(
            $tableName,
            ['force_password_change' => 0],
            ['force_password_change IS NULL OR force_password_change != ?' => 1]
        );

        $this->moduleDataSetup->endSetup();

        return $this;
    }

    public function revert(): void
    {
        // Nothing to revert — default value covers future installs.
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
