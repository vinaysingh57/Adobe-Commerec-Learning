# Setup Patches Documentation

## Overview

This module uses Magento 2's modern **Patch System** for database operations. Patches are the recommended way to handle database schema and data modifications in Magento 2.3+.

## Files Created

### 1. Schema Patch (Table Creation)
**File:** `Setup/Patch/Schema/CreateItemTable.php`

This patch creates the `vendor_crud_item` table with all required columns and indexes.

**Features:**
- Creates table if it doesn't exist
- Adds all columns (item_id, title, description, status, created_at, updated_at)
- Creates indexes for better query performance
- Uses Magento's Table DDL constants

### 2. Data Patch (Sample Data)
**File:** `Setup/Patch/Data/AddSampleItems.php`

This patch inserts 5 sample items into the table for demonstration purposes.

**Features:**
- Inserts sample data only if table exists
- Adds 5 sample items with different statuses
- Safe to run multiple times (won't duplicate data)

## How Patches Work

### Execution Order
1. **Schema Patches** run first (CreateItemTable)
2. **Data Patches** run after (AddSampleItems)
3. Magento tracks executed patches in `patch_list` table

### Advantages
- ✅ Version control friendly
- ✅ Idempotent (can run multiple times safely)
- ✅ Automatic dependency management
- ✅ No need for version numbers
- ✅ Better than old InstallSchema/UpgradeSchema

## Installation Methods

### Method 1: Using db_schema.xml (Recommended - Already Configured)

The module already includes `etc/db_schema.xml` which is the declarative schema approach.

**To use:**
```bash
php bin/magento setup:upgrade
php bin/magento setup:db-schema:upgrade
```

**Note:** db_schema.xml is the preferred method in Magento 2.3+. The table will be created automatically.

### Method 2: Using Schema Patch (Alternative)

If you want to use the patch file instead:

1. Remove or rename `etc/db_schema.xml` (to prevent conflicts)
2. Run setup upgrade:
```bash
php bin/magento setup:upgrade
```

The patch will execute automatically and create the table.

### Method 3: Manual SQL (Quick Testing)

For quick testing without running setup:upgrade, use the SQL file:
```bash
mysql -u username -p database_name < Setup/setup.sql
```

## Running Patches

### First Time Installation
```bash
# This will run all pending patches
php bin/magento setup:upgrade
```

### Check Patch Status
```bash
# List all applied patches
php bin/magento setup:db:status

# Or query database directly
SELECT * FROM patch_list WHERE patch_name LIKE '%CrudModule%';
```

### Revert/Remove Patches

To remove patches (for development):

1. **Remove from database:**
```sql
DELETE FROM patch_list WHERE patch_name LIKE '%Vendor\\CrudModule%';
```

2. **Drop table:**
```sql
DROP TABLE IF EXISTS vendor_crud_item;
```

3. **Run setup upgrade again:**
```bash
php bin/magento setup:upgrade
```

## Patch Classes Explained

### Schema Patch Structure

```php
class CreateItemTable implements SchemaPatchInterface
{
    // Implements three required methods:
    
    public function apply()
    {
        // Your table creation logic here
        // Uses SchemaSetupInterface
    }
    
    public static function getDependencies()
    {
        // List other patches that must run first
        return [];
    }
    
    public function getAliases()
    {
        // Previous patch names (for backward compatibility)
        return [];
    }
}
```

### Data Patch Structure

```php
class AddSampleItems implements DataPatchInterface
{
    // Similar structure but uses ModuleDataSetupInterface
    
    public function apply()
    {
        // Your data insertion logic here
    }
    
    public static function getDependencies()
    {
        // Example: depend on schema patch
        return [
            \Vendor\CrudModule\Setup\Patch\Schema\CreateItemTable::class
        ];
    }
}
```

## Adding Dependencies

If your data patch depends on the schema patch:

```php
public static function getDependencies()
{
    return [
        \Vendor\CrudModule\Setup\Patch\Schema\CreateItemTable::class
    ];
}
```

## Creating Additional Patches

### Add New Column Patch

```php
namespace Vendor\CrudModule\Setup\Patch\Schema;

class AddImageColumn implements SchemaPatchInterface
{
    public function apply()
    {
        $this->schemaSetup->getConnection()->addColumn(
            $this->schemaSetup->getTable('vendor_crud_item'),
            'image',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment' => 'Item Image'
            ]
        );
    }
    
    public static function getDependencies()
    {
        return [
            CreateItemTable::class
        ];
    }
}
```

### Add More Sample Data Patch

```php
namespace Vendor\CrudModule\Setup\Patch\Data;

class AddMoreItems implements DataPatchInterface
{
    public function apply()
    {
        $data = [
            ['title' => 'New Item', 'description' => 'Added by new patch', 'status' => 1]
        ];
        
        $this->moduleDataSetup->getConnection()->insertMultiple(
            $this->moduleDataSetup->getTable('vendor_crud_item'),
            $data
        );
    }
    
    public static function getDependencies()
    {
        return [
            AddSampleItems::class  // Runs after first data patch
        ];
    }
}
```

## Best Practices

### ✅ DO:
- Use patches for schema changes
- Use patches for initial data
- Make patches idempotent (check before create/insert)
- Add proper dependencies
- Use declarative schema (db_schema.xml) for table structure
- Use patches for data population

### ❌ DON'T:
- Don't use InstallSchema/UpgradeSchema (deprecated)
- Don't modify executed patches
- Don't delete patches after deployment
- Don't use patches for regular data operations

## Troubleshooting

### Patch Not Running

**Check if already executed:**
```sql
SELECT * FROM patch_list WHERE patch_name = 'Vendor\\CrudModule\\Setup\\Patch\\Schema\\CreateItemTable';
```

**Force re-run (development only):**
```sql
DELETE FROM patch_list WHERE patch_name = 'Vendor\\CrudModule\\Setup\\Patch\\Schema\\CreateItemTable';
```
Then run: `php bin/magento setup:upgrade`

### Table Already Exists Error

The patches check if table exists before creating. If you still get errors:

```php
if (!$this->schemaSetup->getConnection()->isTableExists($tableName)) {
    // Create table
}
```

### Dependency Errors

Ensure dependencies are listed in correct order:
1. Schema patches before data patches
2. Parent patches before dependent patches

## Comparison: db_schema.xml vs Patches

### db_schema.xml (Declarative Schema)
- ✅ Preferred for table structure
- ✅ Easier to read and maintain
- ✅ Automatically handles changes
- ✅ Version-independent
- ⚠️ Already included in this module

### Schema Patches
- ✅ Better for complex logic
- ✅ Programmatic control
- ✅ Can add conditions
- ⚠️ More code to maintain

### Recommendation
- **Use db_schema.xml** for structure (already implemented)
- **Use Data Patches** for sample/initial data
- **Use Schema Patches** only for complex migrations

## Module Configuration

### Current Setup
This module provides **three options**:

1. **db_schema.xml** (Active) - Declarative schema
2. **Schema Patch** (Alternative) - Programmatic approach
3. **SQL File** (Manual) - Direct SQL execution

Choose one method based on your preference!

## Example: Complete Workflow

```bash
# 1. Clear previous installation (if exists)
mysql -u root -p magento -e "DROP TABLE IF EXISTS vendor_crud_item; DELETE FROM patch_list WHERE patch_name LIKE '%CrudModule%';"

# 2. Run setup upgrade (executes patches)
php bin/magento setup:upgrade

# 3. Verify table creation
mysql -u root -p magento -e "DESCRIBE vendor_crud_item;"

# 4. Check data was inserted
mysql -u root -p magento -e "SELECT * FROM vendor_crud_item;"

# 5. Clear cache
php bin/magento cache:clean
```

## Summary

The patch system provides a robust, maintainable way to handle database operations. This module includes:

- ✅ **Schema Patch** - Creates table structure
- ✅ **Data Patch** - Adds 5 sample items
- ✅ **db_schema.xml** - Declarative schema (recommended)
- ✅ **SQL File** - Manual installation option

All approaches are included for flexibility and learning purposes!
