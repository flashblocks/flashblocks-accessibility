# Flashblocks Accessibility

A WordPress plugin that enhances accessibility for Gutenberg blocks.

## Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Installation](#installation)
- [Usage](#usage)
- [Configuration](#configuration)
- [Updating](#updating)
- [Developer Notes](#developer-notes)

## Overview

Flashblocks Accessibility improves ADA compliance by adding ARIA attributes to Gutenberg blocks and removing empty blocks that would cause accessibility issues.

## Features

### Aria Attributes
- Adds `aria-hidden` and `aria-label` attributes to Gutenberg blocks
- Applies `aria-label` to the correct interactive element (e.g., the `<a>` tag inside a button block)
- Automatically adds `aria-hidden="true"` to decorative images (images with empty alt text)
- Works with WordPress 6.8+ by hiding its control when native support is present
- Configurable via PHP filter

### Empty Block Removal
- Automatically removes empty blocks that cause ADA compliance issues:
  - `core/heading` - Empty h1-h6 tags
  - `core/post-title` - Empty post titles
  - `core/button` - Buttons with no text
  - `core/navigation-link` - Links with no text
- Extendable via PHP filter

## Installation

1. Download the latest release from the [GitHub repository](https://github.com/sunmorgn/flashblocks-accessibility/releases)
2. Upload to your WordPress plugins directory
3. Activate the plugin

## Usage

After activation, the plugin works automatically:

- **Aria controls** appear in the block inspector for supported blocks (in the Advanced panel by default)
- **Empty blocks** are automatically removed from the frontend output

## Configuration

### Aria Label Settings

Use the `flashblocks_accessibility_settings` filter to customize aria label behavior:

```php
add_filter('flashblocks_accessibility_settings', function($settings) {
    // Move controls out of 'Advanced' panel into their own panel
    $settings['moveToAdvanced'] = false;

    // Add a block to the allowed list
    $settings['allowedBlocks'][] = 'core/list';

    // Remove a block from the list
    $settings['allowedBlocks'] = array_filter(
        $settings['allowedBlocks'],
        fn($block) => $block !== 'core/image'
    );

    return $settings;
});
```

**Default allowed blocks:**
- `core/button`
- `core/file`
- `core/search`
- `core/social-link`
- `core/image`
- `core/video`
- `core/cover`
- `core/gallery`
- `core/group`
- `core/columns`
- `core/column`

### Empty Blocks Settings

Use the `flashblocks_accessibility_empty_blocks` filter to customize which blocks are removed when empty:

```php
add_filter('flashblocks_accessibility_empty_blocks', function($blocks) {
    // Add additional blocks to check
    $blocks[] = 'core/table';
    $blocks[] = 'core/list';

    return $blocks;
});
```

**Default empty blocks:**
- `core/heading`
- `core/post-title`
- `core/button`
- `core/navigation-link`

## Updating

1. Create a new **Release** on GitHub with a tag version higher than the current version
2. In WordPress admin, go to **Dashboard > Updates** and click **Check Again**
3. The update will appear on the Plugins page

## Developer Notes

### File Structure

```
flashblocks-accessibility/
├── flashblocks-accessibility.php    # Main plugin file
├── admin.js                         # Block editor controls
└── includes/
    ├── class-aria-attributes.php    # Aria label/hidden functionality
    ├── class-empty-blocks.php       # Empty block removal
    └── class-updater.php            # GitHub updater
```

### Namespace

All classes use the `Flashblocks\Accessibility` namespace:
- `Flashblocks\Accessibility\Includes\Aria_Attributes`
- `Flashblocks\Accessibility\Includes\Empty_Blocks`
- `Flashblocks\Accessibility\Includes\Updater`

### Key Hooks

**Actions:**
- `enqueue_block_editor_assets` - Loads the admin.js for block editor controls

**Filters:**
- `render_block` - Modifies block HTML for aria attributes and empty block removal
- `flashblocks_accessibility_settings` - Customize aria label settings
- `flashblocks_accessibility_empty_blocks` - Customize empty block list
