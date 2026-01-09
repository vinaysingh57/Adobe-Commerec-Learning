# Vendor_CrudModule - Installation & Usage Guide

## Module Overview

This is a complete Magento 2 CRUD (Create, Read, Update, Delete) module for the admin section. It demonstrates best practices for Magento 2 module development including:

- Full CRUD operations
- Repository Pattern
- UI Components (Grid & Form)
- ACL (Access Control List)
- Admin Menu Integration
- Mass Actions
- Inline Editing

## Installation Steps

### Step 1: Module Registration
The module is already placed at: `app/code/Vendor/CrudModule`

### Step 2: Enable Module
Since we cannot run `php bin/magento` commands due to PHP version constraints, the module has been manually registered in `app/etc/config.php`.

### Step 3: Setup Database

The module includes **three methods** to create the database table:

#### Method 1: Using db_schema.xml (Recommended - Magento 2.3+)

The table will be created automatically when you run:

```bash
php bin/magento setup:upgrade
```

This uses Magento's declarative schema from `etc/db_schema.xml`.

#### Method 2: Using Setup Patches (Programmatic Approach)

If you prefer using patches, they are already included:

- **Schema Patch:** `Setup/Patch/Schema/CreateItemTable.php` - Creates table
- **Data Patch:** `Setup/Patch/Data/AddSampleItems.php` - Adds 5 sample items

Run:
```bash
php bin/magento setup:upgrade
```

The patches will execute automatically. See `Setup/PATCHES.md` for detailed documentation.

#### Method 3: Manual SQL (Quick Testing)

Run the following SQL directly in your database:

```sql
CREATE TABLE IF NOT EXISTS `vendor_crud_item` (
  `item_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Item ID',
  `title` varchar(255) NOT NULL COMMENT 'Item Title',
  `description` text DEFAULT NULL COMMENT 'Item Description',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Status',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created At',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Updated At',
  PRIMARY KEY (`item_id`),
  KEY `VENDOR_CRUD_ITEM_TITLE` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Vendor CRUD Item Table';
```

Or use the provided SQL file:
```bash
mysql -u username -p database_name < app/code/Vendor/CrudModule/Setup/setup.sql
```

### Step 4: Clear Cache
```bash
rm -rf var/cache/* var/page_cache/* var/view_preprocessed/* pub/static/*
```

Or if you can run Magento commands:
```bash
php bin/magento cache:clean
php bin/magento cache:flush
```

### Step 5: Deploy Static Content (Production Mode Only)
```bash
php bin/magento setup:static-content:deploy -f
```

## Accessing the Module

1. Login to Magento Admin Panel
2. Navigate to: **CRUD Module** → **Manage Items**
3. You will see a grid with options to:
   - Add New Item
   - Edit existing items
   - Delete items (single or bulk)
   - Filter and search items

## Module Features

### 1. List Items (Index Page)
- URL: `admin/vendor_crud/item/index`
- Features:
  - Paginated grid
  - Column sorting
  - Filters (search, date range)
  - Mass delete action
  - Inline editing
  - Actions column (Edit/Delete)

### 2. Create New Item
- URL: `admin/vendor_crud/item/new`
- Fields:
  - Title (Required)
  - Description (Optional)
  - Status (Enable/Disable)

### 3. Edit Item
- URL: `admin/vendor_crud/item/edit/item_id/{id}`
- Same form as create with pre-filled data
- Options:
  - Save
  - Save and Continue Edit
  - Delete
  - Back

### 4. Delete Item
- Single delete from Edit page
- Single delete from grid Actions
- Mass delete from grid (select multiple items)

## File Structure

```
Vendor/CrudModule/
│
├── Api/
│   ├── Data/
│   │   └── ItemInterface.php              # Data interface
│   └── ItemRepositoryInterface.php        # Repository interface
│
├── Block/
│   └── Adminhtml/
│       └── Item/
│           └── Edit/
│               ├── BackButton.php         # Back button
│               ├── DeleteButton.php       # Delete button
│               ├── SaveButton.php         # Save button
│               └── SaveAndContinueButton.php
│
├── Controller/
│   └── Adminhtml/
│       └── Item/
│           ├── Index.php                  # List items
│           ├── NewAction.php              # New item form
│           ├── Edit.php                   # Edit item form
│           ├── Save.php                   # Save item
│           ├── Delete.php                 # Delete single item
│           └── MassDelete.php             # Delete multiple items
│
├── Model/
│   ├── Item.php                           # Item model
│   ├── ItemRepository.php                 # Repository implementation
│   ├── Item/
│   │   └── DataProvider.php               # Form data provider
│   └── ResourceModel/
│       ├── Item.php                       # Resource model
│       └── Item/
│           └── Collection.php             # Collection model
│
├── Ui/
│   └── Component/
│       └── Listing/
│           └── Column/
│               └── ItemActions.php        # Grid actions column
│
├── etc/
│   ├── acl.xml                            # Access Control List
│   ├── db_schema.xml                      # Database schema
│   ├── di.xml                             # Dependency Injection
│   ├── module.xml                         # Module declaration
│   └── adminhtml/
│       ├── menu.xml                       # Admin menu
│       └── routes.xml                     # Admin routes
│
├── view/
│   └── adminhtml/
│       ├── layout/
│       │   ├── vendor_crud_item_index.xml # List page layout
│       │   ├── vendor_crud_item_edit.xml  # Edit page layout
│       │   └── vendor_crud_item_new.xml   # New page layout
│       └── ui_component/
│           ├── vendor_crud_item_listing.xml # Grid UI Component
│           └── vendor_crud_item_form.xml    # Form UI Component
│
├── registration.php                       # Module registration
└── README.md                              # Documentation
```

## Database Schema

**Table Name:** `vendor_crud_item`

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| item_id | INT(10) UNSIGNED | NO | AUTO_INCREMENT | Primary Key |
| title | VARCHAR(255) | NO | - | Item Title |
| description | TEXT | YES | NULL | Item Description |
| status | TINYINT(1) | NO | 1 | Enable/Disable |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | Creation Time |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | Last Update Time |

## ACL Resources

Configure user permissions for:

- **Vendor_CrudModule::crud** - Main menu access
- **Vendor_CrudModule::item** - View items list
- **Vendor_CrudModule::item_save** - Create and edit items
- **Vendor_CrudModule::item_delete** - Delete items

Go to: **System** → **Permissions** → **User Roles** → Select Role → **Role Resources**

## Customization

### Add New Fields

1. Update `etc/db_schema.xml` with new column
2. Add field to `Api/Data/ItemInterface.php`
3. Add getter/setter to `Model/Item.php`
4. Add field to `view/adminhtml/ui_component/vendor_crud_item_form.xml`
5. Add column to `view/adminhtml/ui_component/vendor_crud_item_listing.xml`
6. Run setup upgrade

### Change Table Name

1. Update table name in `etc/db_schema.xml`
2. Update `Model/ResourceModel/Item.php` _construct method
3. Update `etc/di.xml` virtualType mainTable argument

### Add Frontend Display

1. Create `etc/frontend/routes.xml`
2. Create Controller at `Controller/Index/Index.php`
3. Create Block at `Block/Item/View.php`
4. Create Layout at `view/frontend/layout/`
5. Create Template at `view/frontend/templates/`

## Testing the Module

### Test CRUD Operations:

1. **Create**
   - Click "Add New Item"
   - Fill Title: "Test Item 1"
   - Fill Description: "This is a test item"
   - Set Status: Enable
   - Click "Save Item"

2. **Read**
   - View item in grid
   - Check if data displays correctly
   - Test filters and search

3. **Update**
   - Click "Edit" on an item
   - Change title to "Updated Test Item 1"
   - Click "Save and Continue Edit"
   - Verify changes are saved

4. **Delete**
   - Single delete: Click "Delete" in Actions column
   - Mass delete: Select multiple items, choose "Delete" from mass actions

## Troubleshooting

### Module Not Appearing in Admin Menu

1. Check if module is enabled in `app/etc/config.php`
2. Clear cache
3. Check ACL permissions for your user role

### Database Table Not Created

Run the SQL script manually (provided in Step 3 above)

### Changes Not Reflecting

1. Clear cache: `rm -rf var/cache/* var/page_cache/*`
2. Clear generated code: `rm -rf generated/code/*`
3. Deploy static content if in production mode

## Support

For issues or questions, review the Magento 2 documentation:
- [Magento 2 Developer Guide](https://devdocs.magento.com/)
- [UI Components Guide](https://devdocs.magento.com/guides/v2.4/ui_comp_guide/bk-ui_comps.html)

## License

Copyright © Vendor. All rights reserved.

## Version

1.0.0
