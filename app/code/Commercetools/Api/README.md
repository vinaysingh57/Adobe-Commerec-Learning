# Commercetools API Integration for Magento 2

This module provides integration with Commercetools platform through REST API to fetch product data.

## Features

- Fetch products by ID or Key from Commercetools
- Search products using text search
- Get products list with pagination and filters
- Configurable API credentials through admin panel
- Logging and debug mode support
- Service contracts for easy extensibility

## Installation

1. Copy the module to `app/code/Commercetools/Api/`
2. Enable the module:
   ```bash
   bin/magento module:enable Commercetools_Api
   ```
3. Run setup upgrade:
   ```bash
   bin/magento setup:upgrade
   ```
4. Clear cache:
   ```bash
   bin/magento cache:flush
   ```

## Configuration

1. Go to **Stores > Configuration > Commercetools > API Configuration**
2. Enable the module
3. Configure your Commercetools credentials:
   - **Project Key**: Your Commercetools project key
   - **Client ID**: Your Commercetools client ID
   - **Client Secret**: Your Commercetools client secret
   - **Access Token**: Your Commercetools access token
   - **API URL**: Commercetools API base URL (default: https://api.sphere.io)

## Usage

### Using the Repository (Recommended)

```php
<?php
use Commercetools\Api\Api\ProductRepositoryInterface;

class YourClass
{
    private ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function getProduct($productId)
    {
        try {
            // Get product by ID
            $product = $this->productRepository->getById($productId);
            
            // Get product by key
            $product = $this->productRepository->getByKey('product-key');
            
            // Get products list
            $products = $this->productRepository->getList($limit = 20, $offset = 0);
            
            // Search products
            $products = $this->productRepository->searchProducts('search query', $limit = 10);
            
        } catch (\Exception $e) {
            // Handle exceptions
        }
    }
}
```

### Console Command

Test the API integration using the console command:

```bash
# Test fetching a product by ID
bin/magento commercetools:api:test YOUR_PRODUCT_ID
```

### Web Controller (Testing)

Access the test controller via URL to test API calls:

```
# Get products list
http://yourstore.com/commercetools/index/test

# Get product by ID
http://yourstore.com/commercetools/index/test?product_id=YOUR_PRODUCT_ID

# Get product by key
http://yourstore.com/commercetools/index/test?product_key=YOUR_PRODUCT_KEY

# Search products
http://yourstore.com/commercetools/index/test?search=YOUR_SEARCH_QUERY&limit=10
```

## API Endpoints Supported

- `GET /products` - Get products list
- `GET /products/{id}` - Get product by ID  
- `GET /products/key={key}` - Get product by key
- `GET /product-projections/search` - Search products

## Service Contracts

The module follows Magento's service contract pattern:

- **ConfigurationInterface**: Access module configuration
- **ProductInterface**: Product data contract
- **ProductRepositoryInterface**: Product operations contract

## Logging

All API requests and responses are logged to `var/log/commercetools_api.log` when debug mode is enabled.

## Error Handling

The module properly handles:
- API connection errors
- Authentication failures  
- Rate limiting
- Invalid responses
- Network timeouts

All exceptions are logged and re-thrown as LocalizedException for consistent error handling.

## Extensibility

You can extend the module by:
- Creating custom repositories that implement the interfaces
- Adding new API endpoints to the ApiClient service
- Creating custom data models that implement ProductInterface
- Adding plugins to modify behavior

## Requirements

- Magento 2.4.x
- PHP 7.4+
- Valid Commercetools project with API credentials
- cURL extension enabled