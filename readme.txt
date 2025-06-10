# Custom Content Blocks Plugin

**Version:** 1.0.0  
**Requires:** WordPress 5.0+  
**Tested up to:** WordPress 6.4  
**License:** GPL v2 or later

## Description

Create reusable content blocks with automatic shortcode generation. Perfect for displaying consistent content across multiple pages, posts, and widgets without duplicating content.

## Features

- ? Custom post type for content blocks
- ? Automatic shortcode generation for each content block
- ? Display content anywhere using shortcodes
- ? Multiple shortcode formats (ID and slug-based)
- ? Clean, responsive output
- ? Easy-to-use admin interface
- ? Copy-to-clipboard functionality
- ? Comprehensive help documentation

## Installation

1. Upload the plugin files to `/wp-content/plugins/custom-content-blocks/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Start creating content blocks from the new "Content Blocks" menu

## Usage

### Creating Content Blocks

1. Go to **Content Blocks** ? **Add New**
2. Enter a title and your content
3. Publish the content block
4. Copy the generated shortcode from the sidebar

### Using Shortcodes

**Basic Usage:**
```
[content_block id="123"]
[content_block slug="my-content-block"]
```

**Advanced Usage:**
```
[content_block id="123" class="custom-class"]
[content_block id="123" wrapper="span"]
[content_block slug="my-block" class="highlight" wrapper="section"]
```

### Shortcode Parameters

| Parameter | Description | Default | Example |
|-----------|-------------|---------|---------|
| `id` | Content block ID | - | `id="123"` |
| `slug` | Content block slug | - | `slug="my-block"` |
| `class` | Custom CSS class | - | `class="highlight"` |
| `wrapper` | HTML wrapper element | `div` | `wrapper="span"` |

### Where to Use Shortcodes

- Posts and pages content
- Text widgets
- Custom fields (if they support shortcodes)
- Template files using `do_shortcode('[content_block id="123"]')`

## File Structure

```
custom-content-blocks/
+-- custom-content-blocks.php     # Main plugin file
+-- readme.txt                    # This file
+-- includes/
¦   +-- class-post-type.php      # Post type registration
¦   +-- class-shortcode-handler.php # Shortcode processing
¦   +-- class-admin-interface.php   # Admin interface
+-- assets/
    +-- css/
    ¦   +-- admin-styles.css     # Admin styling
    +-- js/
        +-- admin-scripts.js     # Admin JavaScript
```

## Hooks & Filters

### Actions

- `ccb_content_block_created` - Fired when a new content block is created
- `ccb_content_block_updated` - Fired when a content block is updated

### Filters

- `ccb_shortcode_output` - Modify shortcode output
- `ccb_allowed_wrapper_tags` - Modify allowed wrapper HTML tags
- `ccb_content_block_content` - Modify content block content before output

### Example Usage

```php
// Modify shortcode output
add_filter('ccb_shortcode_output', function($output, $post, $class, $wrapper) {
    // Add custom attributes or modify output
    return $output;
}, 10, 4);

// Add custom wrapper tags
add_filter('ccb_allowed_wrapper_tags', function($tags) {
    $tags[] = 'main';
    $tags[] = 'header';
    return $tags;
});
```

## CSS Styling

The plugin adds minimal CSS for proper display. You can customize the appearance by targeting these classes:

```css
/* Main content block wrapper */
.content-block {
    margin: 1em 0;
    clear: both;
}

/* Specific content block by ID */
.content-block-123 {
    /* Your custom styles */
}

/* Error messages (only visible to editors) */
.content-block-error {
    background: #ffeaa7;
    border: 1px solid #fdcb6e;
    padding: 10px;
    border-radius: 3px;
}
```

## Troubleshooting

### Common Issues

**Q: Shortcode displays as text instead of content**
A: Make sure the content block is published and the shortcode is correctly formatted.

**Q: Content block not found error**
A: Verify the ID or slug exists and the content block is published.

**Q: Shortcode doesn't work in widgets**
A: Ensure your theme supports shortcodes in widgets, or add this to your theme's functions.php:
```php
add_filter('widget_text', 'do_shortcode');
```

### Debug Mode

Add this to your wp-config.php to see detailed error messages:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Performance Considerations

- Content blocks are cached automatically
- Use slug-based shortcodes sparingly on high-traffic sites
- Consider caching plugins for optimal performance

## Security

- All shortcode attributes are sanitized
- Content is filtered through WordPress security functions
- Error messages only shown to users with edit capabilities

## Changelog

### 1.0.0
- Initial release
- Custom post type registration
- Automatic shortcode generation
- Admin interface with copy functionality
- Comprehensive help documentation
- Multiple shortcode formats support

## Support

For support and feature requests, please visit the plugin's support forum or contact the developer.

## License

This plugin is licensed under the GPL v2 or later.

```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```