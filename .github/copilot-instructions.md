# Copilot Instructions for Magento 2 Enterprise Edition

## Architecture Overview

This is a **Magento 2.4.7-p3 Enterprise Edition** codebase with standard MVC architecture plus service contracts pattern. Key directories:
- `app/code/` - Custom modules (follow `Vendor/ModuleName` structure)
- `vendor/magento/` - Core Magento modules via Composer
- `bin/magento` - CLI entry point for all operations
- `pub/index.php` - Web entry point
- `var/` - Generated code, cache, logs
- `generated/code/` - Auto-generated dependency injection code

## Module Development Patterns

### Module Registration & Structure
Every module requires:
1. `registration.php` - Register with `ComponentRegistrar::register()`
2. `etc/module.xml` - Declare module with setup version
3. `etc/di.xml` - Dependency injection preferences and type configurations
4. Follow namespace pattern: `Vendor\ModuleName\Layer\ClassName`

### Service Contract Pattern
- **API interfaces** in `Api/` directory define contracts (e.g., `ItemRepositoryInterface`)
- **Data interfaces** in `Api/Data/` define entity contracts (e.g., `ItemInterface`) 
- **Models** implement business logic, **ResourceModels** handle database operations
- **Repositories** implement API interfaces and coordinate between models

### Controller Architecture
- Admin controllers extend `\Magento\Backend\App\Action`
- Frontend controllers extend `\Magento\Framework\App\Action\HttpGetActionInterface`
- Define `ADMIN_RESOURCE` constant for ACL authorization
- Use `Context` dependency injection for common admin functionality

## Essential CLI Commands

```bash
# Module operations
bin/magento module:enable Vendor_ModuleName
bin/magento module:disable Vendor_ModuleName
bin/magento setup:upgrade                    # Apply schema changes
bin/magento setup:di:compile                 # Generate DI code
bin/magento setup:static-content:deploy      # Deploy static assets

# Cache operations  
bin/magento cache:flush                      # Clear all cache
bin/magento cache:clean config layout        # Clear specific cache types

# Development mode
bin/magento deploy:mode:set developer        # Enable developer mode
bin/magento deploy:mode:set production       # Production deployment
```

## Configuration Files

### Dependency Injection (`etc/di.xml`)
- Define `<preference>` for interface implementations
- Configure `<type>` arguments for constructor injection
- Use `<virtualType>` for specialized configurations

### Routing (`etc/adminhtml/routes.xml`, `etc/frontend/routes.xml`)
- Map frontName to module for URL generation
- Admin routes require `router id="admin"`

### ACL (`etc/acl.xml`)
- Define resource hierarchy under `Magento_Backend::admin`
- Match controller `ADMIN_RESOURCE` constants to ACL resource IDs

## Database Operations

### Schema Definition (`etc/db_schema.xml`)
```xml
<table name="vendor_item" resource="default">
    <column xsi:type="int" name="entity_id" identity="true"/>
    <constraint xsi:type="primary" referenceId="PRIMARY">
        <column name="entity_id"/>
    </constraint>
</table>
```

### Model/ResourceModel Pattern
- Models extend `\Magento\Framework\Model\AbstractModel`
- ResourceModels extend `\Magento\Framework\Model\ResourceModel\Db\AbstractDb`
- Collections extend `\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection`

## Testing Structure

- **Unit tests**: `Test/Unit/` - Fast, isolated tests with mocks
- **Integration tests**: `dev/tests/integration/` - Database interactions
- **Static tests**: `dev/tests/static/` - Code quality, dependencies
- Use `\Magento\Framework\TestFramework\Unit\Helper\ObjectManager` for unit test setup

## Enterprise Edition Specifics

### EE/CE Linking
```bash
# Link EE code to CE repository (development setup)
php dev/tools/build-ee.php --command link --exclude true
```

### Module Dependencies
- EE modules depend on corresponding CE modules
- Use `composer.json` dependencies, not `etc/module.xml` sequence
- EE-specific features often extend CE functionality via plugins/preferences

## Common Pitfalls

1. **Always run `setup:upgrade` after schema changes** - Magento won't detect new tables/columns
2. **Use generated factories** - `ItemFactory $itemFactory` not `new Item()`  
3. **Repository pattern** - Don't call model `save()` directly, use repository interfaces
4. **ACL resources** - Match exact strings between `acl.xml` and controller constants
5. **Cache invalidation** - Add cache tags to models for proper invalidation

## Development Workflow

1. Create module structure with `registration.php` and `etc/module.xml`
2. Define API contracts in `Api/` directory
3. Implement models and resource models
4. Create controllers with proper ACL
5. Run `module:enable` → `setup:upgrade` → `cache:flush`
6. Use `deploy:mode:set developer` for debugging without compilation