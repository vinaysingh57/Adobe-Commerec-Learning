# Voice Search Product Search Module for Magento 2

A custom Magento 2 module that enables voice search functionality for products using the Web Speech API, compatible with Chrome browsers.

## Features

- 🎤 **Voice Recognition**: Uses Web Speech API for accurate voice-to-text conversion
- 🔍 **Product Search**: Integrates with Magento's native search functionality
- 📱 **Responsive Design**: Works on desktop and mobile devices
- 🎯 **Two Interfaces**: Full-page voice search and header widget
- 🌐 **Chrome Compatible**: Optimized for Google Chrome browser
- ⚡ **Real-time Results**: AJAX-powered instant search results
- 🎨 **Modern UI**: Beautiful, intuitive user interface

## Requirements

- Magento 2.4.x (tested on 2.4.7-p3)
- PHP 7.4+ or 8.1+
- Google Chrome browser (for voice recognition)
- HTTPS connection (required by Web Speech API)
- Microphone access permission

## Installation

### Method 1: Manual Installation

1. Create the module directory:
```bash
mkdir -p app/code/VoiceSearch/ProductSearch
```

2. Copy all module files to the directory structure shown above

3. Enable the module:
```bash
bin/magento module:enable VoiceSearch_ProductSearch
bin/magento setup:upgrade
bin/magento cache:flush
```

### Method 2: Via Composer (if packaged)

```bash
composer require voicesearch/product-search
bin/magento module:enable VoiceSearch_ProductSearch
bin/magento setup:upgrade
bin/magento cache:flush
```

## Usage

### Full Page Voice Search

Navigate to: `your-domain.com/voicesearch`

Features:
- Large, prominent voice search button
- Real-time transcript display
- Grid layout for search results
- Usage instructions and help text

### Header Widget

The voice search widget appears automatically in the site header next to the search box.

Features:
- Compact microphone button
- Modal popup interface
- Compact results display
- Quick access from any page

## How It Works

1. **Voice Recognition**: 
   - Uses HTML5 Web Speech API
   - Supports continuous listening mode
   - Real-time transcript display
   - Error handling for various scenarios

2. **Search Integration**:
   - Leverages Magento's fulltext search
   - Searches product names, descriptions, and attributes
   - Respects catalog visibility settings
   - Returns formatted product data

3. **Results Display**:
   - Product images, names, and prices
   - Direct links to product pages
   - Responsive grid/list layouts
   - Loading states and error messages

## Browser Compatibility

| Browser | Support | Notes |
|---------|---------|--------|
| Chrome | ✅ Full | Primary target browser |
| Edge | ✅ Full | Chromium-based versions |
| Firefox | ❌ Limited | No Web Speech API support |
| Safari | ❌ Limited | Experimental support only |

## Configuration

The module includes default settings that work out of the box:

- **Language**: English (en-US)
- **Max Results**: 10 products
- **Search Scope**: All enabled products
- **Timeout**: 30 seconds for recognition

## Customization

### Modifying Search Logic

Edit `Controller/Index/Search.php` to customize:
- Search query processing
- Product filtering
- Result formatting
- Response structure

### Styling Changes

Edit `view/frontend/web/css/voice-search.css` to customize:
- Button colors and animations
- Layout and spacing
- Modal appearance
- Responsive breakpoints

### JavaScript Behavior

Edit the JavaScript files to modify:
- Speech recognition settings
- Error handling
- UI interactions
- AJAX request handling

## Technical Details

### File Structure
```
app/code/VoiceSearch/ProductSearch/
├── registration.php
├── etc/
│   ├── module.xml
│   ├── di.xml
│   └── frontend/
│       └── routes.xml
├── Controller/
│   └── Index/
│       ├── Index.php
│       └── Search.php
├── Block/
│   └── VoiceSearch.php
├── view/frontend/
│   ├── layout/
│   │   ├── default.xml
│   │   └── voicesearch_index_index.xml
│   ├── templates/
│   │   ├── voice-search.phtml
│   │   └── voice-search-widget.phtml
│   └── web/
│       ├── js/
│       │   ├── voice-search.js
│       │   └── voice-search-widget.js
│       └── css/
│           └── voice-search.css
```

### API Endpoints

- `POST /voicesearch/index/search`
  - Parameters: `q` (search query), `form_key`
  - Returns: JSON response with products array

### Events and Observers

The module integrates with Magento's native search without custom events.

## Troubleshooting

### Common Issues

1. **Microphone Not Working**
   - Check browser permissions
   - Ensure HTTPS connection
   - Verify microphone hardware

2. **No Search Results**
   - Check product visibility
   - Verify search indexing
   - Test with manual search

3. **JavaScript Errors**
   - Check browser console
   - Verify RequireJS loading
   - Clear browser cache

4. **Module Not Loading**
   - Run `bin/magento setup:upgrade`
   - Check module status: `bin/magento module:status`
   - Clear cache: `bin/magento cache:flush`

### Debug Mode

Enable developer mode for detailed error messages:
```bash
bin/magento deploy:mode:set developer
```

## Performance Considerations

- Voice recognition runs client-side (no server load)
- AJAX searches are lightweight
- Results are cached by Magento's search index
- Image loading is optimized with thumbnails

## Security

- CSRF protection via form_key
- Input sanitization in search controller
- XSS prevention in templates
- Magento's native ACL integration

## Future Enhancements

Potential improvements:
- Multi-language support
- Voice search analytics
- Admin configuration panel
- Search result ranking
- Voice commands for navigation
- Integration with Elasticsearch

## Support

For issues and questions:
1. Check the troubleshooting section
2. Review Magento logs in `var/log/`
3. Test in Chrome developer tools
4. Verify module installation

## License

This module is provided as-is for educational and commercial use. Please review and comply with Magento's licensing terms and any applicable open-source licenses.

## Credits

- Built for Magento 2.4.7-p3 Enterprise Edition
- Uses Web Speech API specification
- Follows Magento coding standards
- Implements responsive design principles