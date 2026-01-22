<?php

/**
 * Flashblocks Accessibility
 *
 * @link              https://github.com/sunmorgn/flashblocks-accessibility
 * @since             1.1.0
 *
 * @wordpress-plugin
 * Plugin Name:       Flashblocks Accessibility
 * Plugin URI:        https://github.com/sunmorgn/flashblocks-accessibility
 * Description:       Enhances accessibility by adding ARIA attributes to Gutenberg blocks, removing empty blocks, and automatically fixing common ADA issues in rendered HTML.
 * Version:           1.1.0
 * Author:            Sunny Morgan
 * Author URI:        https://github.com/sunmorgn
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       flashblocks-accessibility
 * Requires at least: 6.0
 * Requires PHP:      7.4
 */

namespace Flashblocks\Accessibility;

// Stop if this file is called directly.
if (! defined('WPINC')) die;

const DIR = __DIR__;

// Aria label/hidden attributes for blocks
require_once DIR . '/includes/class-aria-attributes.php';
new Includes\Aria_Attributes();

// Remove empty blocks from output
require_once DIR . '/includes/class-empty-blocks.php';
new Includes\Empty_Blocks();

// Fix common ADA issues in rendered HTML
require_once DIR . '/includes/class-ada-fixes.php';
new Includes\ADA_Fixes();

if (is_admin()) {
	require_once DIR . '/includes/class-updater.php';
	new Includes\Updater();
}
