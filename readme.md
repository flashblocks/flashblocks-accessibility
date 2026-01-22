# Flashblocks Accessibility

A WordPress plugin that enhances accessibility for Gutenberg blocks and automatically fixes common ADA issues.

## Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Installation](#installation)
- [Usage](#usage)
- [Configuration](#configuration)
- [Updating](#updating)
- [Developer Notes](#developer-notes)

## Overview

Flashblocks Accessibility improves ADA compliance by:
- Adding ARIA attributes to Gutenberg blocks
- Removing empty blocks that cause accessibility issues
- Automatically fixing common ADA issues in rendered HTML (from any source)

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

### Automatic ADA Fixes
Scans the final rendered HTML and fixes common accessibility issues from any source (plugins, themes, etc.):

| Issue | Fix Applied |
|-------|-------------|
| Progressbars without accessible names | Adds `aria-label="Progress: X% complete"` |
| Empty links (icon links, social links) | Adds `aria-label` based on href (Facebook, Email, Phone, etc.) |
| Empty buttons (icon buttons) | Adds `aria-label` based on class (Close, Menu, Search, etc.) |
| Images without alt attributes | Adds `alt=""` for decorative, or uses title attribute |
| Form inputs without labels | Adds `aria-label` from placeholder, name, or input type |
| Redundant aria-label on labels | Removes `aria-label` when it matches visible text content |

### FluentForms-Specific Fixes
These fixes are only loaded when FluentForms is active:

| Issue | Fix Applied |
|-------|-------------|
| Multiple labels for file upload | Removes `for` attribute from `ff_file_upload_holder` wrapper labels |
| Orphaned group labels | Converts `<label>` to `<span>` in radio/checkbox group headers |

## Installation

1. Download the latest release from the [GitHub repository](https://github.com/sunmorgn/flashblocks-accessibility/releases)
2. Upload to your WordPress plugins directory
3. Activate the plugin

## Usage

After activation, the plugin works automatically:

- **Aria controls** appear in the block inspector for supported blocks (in the Advanced panel by default)
- **Empty blocks** are automatically removed from the frontend output
- **ADA fixes** are applied to all frontend HTML via output buffering

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

### ADA Fixes Settings

Use the `flashblocks_accessibility_ada_fixes` filter to customize which fixes are applied:

```php
add_filter('flashblocks_accessibility_ada_fixes', function($fixes) {
    // Remove a specific fix (e.g., don't auto-fix images)
    return array_filter($fixes, function($fix) {
        return $fix[1] !== 'fix_images';
    });
});

// Or add a custom fix
add_filter('flashblocks_accessibility_ada_fixes', function($fixes) {
    $fixes[] = function($html) {
        // Your custom fix logic
        return $html;
    };
    return $fixes;
});
```

**Default fixes:**
- `fix_progressbars` - ARIA progressbar elements
- `fix_empty_links` - Links without text content
- `fix_empty_buttons` - Buttons without text content
- `fix_images` - Images without alt attributes
- `fix_inputs` - Form inputs without labels
- `fix_duplicate_labels` - Redundant aria-labels on labels

**FluentForms fixes** (when plugin is active):
- `fix_file_upload_labels` - Multiple labels for file uploads
- `fix_orphaned_group_labels` - Orphaned labels in radio/checkbox groups

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
    ├── class-ada-fixes.php          # Automatic ADA fixes
    ├── class-fluentforms-fixes.php  # FluentForms-specific fixes
    └── class-updater.php            # GitHub updater
```

### Namespace

All classes use the `Flashblocks\Accessibility` namespace:
- `Flashblocks\Accessibility\Includes\Aria_Attributes`
- `Flashblocks\Accessibility\Includes\Empty_Blocks`
- `Flashblocks\Accessibility\Includes\ADA_Fixes`
- `Flashblocks\Accessibility\Includes\FluentForms_Fixes`
- `Flashblocks\Accessibility\Includes\Updater`

### Key Hooks

**Actions:**
- `enqueue_block_editor_assets` - Loads the admin.js for block editor controls
- `template_redirect` - Starts output buffering for ADA fixes
- `shutdown` - Ends output buffering and applies ADA fixes

**Filters:**
- `render_block` - Modifies block HTML for aria attributes and empty block removal
- `flashblocks_accessibility_settings` - Customize aria label settings
- `flashblocks_accessibility_empty_blocks` - Customize empty block list
- `flashblocks_accessibility_ada_fixes` - Customize automatic ADA fixes

### How ADA Fixes Work

The `ADA_Fixes` class uses output buffering to capture the final HTML before it's sent to the browser. It then applies fixes using:

- **WP_HTML_Tag_Processor** - For robust attribute manipulation on self-closing tags (`<img>`, `<input>`) and attribute-only checks (`role="progressbar"`)
- **Regex** - Only where inner content must be inspected (`<a>...</a>`, `<button>...</button>`)

This hybrid approach ensures reliability while still being able to check if elements have visible text content.
