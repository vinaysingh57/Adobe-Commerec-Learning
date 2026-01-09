# Vendor_CrudModule

A Magento 2 module that demonstrates complete CRUD (Create, Read, Update, Delete) operations in the admin section.

## Features

- **Create**: Add new items through admin form
- **Read**: View items in a grid with filters and sorting
- **Update**: Edit existing items with inline editing support
- **Delete**: Delete single or multiple items (mass action)
- Admin menu integration
- ACL (Access Control List) implementation
- UI Components for grid and form
- Repository pattern implementation

## Installation

1. Copy the module to `app/code/Vendor/CrudModule`

2. Enable the module:
```bash
php bin/magento module:enable Vendor_CrudModule
```

3. Run setup upgrade:
```bash
php bin/magento setup:upgrade
```

4. Deploy static content (if in production mode):
```bash
php bin/magento setup:static-content:deploy -f
```

5. Clear cache:
```bash
php bin/magento cache:clean
php bin/magento cache:flush
```

## Usage

After installation, you can access the module from the admin panel:

1. Login to Magento Admin
2. Navigate to **CRUD Module** > **Manage Items**
3. Use the grid to view, edit, or delete items
4. Click "Add New Item" to create a new record

## Database Structure

The module creates a table `vendor_crud_item` with the following fields:

- `item_id` (Primary Key)
- `title` (Required)
- `description`
- `status` (Enable/Disable)
- `created_at`
- `updated_at`

## File Structure

```
Vendor/CrudModule/
├── Api/
│   ├── Data/
│   │   └── ItemInterface.php
│   └── ItemRepositoryInterface.php
├── Block/
│   └── Adminhtml/
│       └── Item/
│           └── Edit/
│               ├── BackButton.php
│               ├── DeleteButton.php
│               ├── SaveButton.php
│               └── SaveAndContinueButton.php
├── Controller/
│   └── Adminhtml/
│       └── Item/
│           ├── Index.php
│           ├── NewAction.php
│           ├── Edit.php
│           ├── Save.php
│           ├── Delete.php
│           └── MassDelete.php
├── Model/
│   ├── Item.php
│   ├── ItemRepository.php
│   ├── Item/
│   │   └── DataProvider.php
│   └── ResourceModel/
│       ├── Item.php
│       └── Item/
│           └── Collection.php
├── Ui/
│   └── Component/
│       └── Listing/
│           └── Column/
│               └── ItemActions.php
├── etc/
│   ├── acl.xml
│   ├── db_schema.xml
│   ├── di.xml
│   ├── module.xml
│   └── adminhtml/
│       ├── menu.xml
│       └── routes.xml
├── view/
│   └── adminhtml/
│       ├── layout/
│       │   ├── vendor_crud_item_index.xml
│       │   ├── vendor_crud_item_edit.xml
│       │   └── vendor_crud_item_new.xml
│       └── ui_component/
│           ├── vendor_crud_item_listing.xml
│           └── vendor_crud_item_form.xml
└── registration.php
```

## ACL Resources

The module implements the following ACL resources:

- `Vendor_CrudModule::crud` - Main menu access
- `Vendor_CrudModule::item` - View items
- `Vendor_CrudModule::item_save` - Create/Edit items
- `Vendor_CrudModule::item_delete` - Delete items

## Features Included

1. **Repository Pattern**: Proper implementation of data access layer
2. **UI Components**: Modern Magento 2 UI components for grid and form
3. **Mass Actions**: Delete multiple items at once
4. **Inline Editing**: Edit items directly from the grid
5. **Data Provider**: Custom data provider for form
6. **ACL**: Role-based access control
7. **Admin Menu**: Custom menu item in admin panel

## Author

Vendor

## License

Copyright © Vendor. All rights reserved.
