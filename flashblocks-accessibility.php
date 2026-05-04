<?php

/**
 * Flashblocks Accessibility
 *
 * @link              https://github.com/sunmorgn/flashblocks-accessibility
 *
 * @wordpress-plugin
 * Plugin Name:       Flashblocks Accessibility
 * Plugin URI:        https://github.com/sunmorgn/flashblocks-accessibility
 * Description:       Enhances accessibility by adding ARIA attributes to Gutenberg blocks, removing empty blocks, and automatically fixing common ADA issues in rendered HTML.
 * Version:           1.1.1
 * Author:            Sunny Morgan
 * Author URI:        https://github.com/sunmorgn
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       flashblocks-accessibility
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Flashblocks Module: yes
 * Flashblocks Category: Extensions
 * Flashblocks Tags: accessibility, aria, ada
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

// FluentForms-specific fixes (only if plugin is active)
function load_fluentforms_fixes(): void {
	if (defined('FLUENTFORM')) {
		require_once DIR . '/includes/class-fluentforms-fixes.php';
		new Includes\FluentForms_Fixes();
	}
}

if (did_action('plugins_loaded')) {
	load_fluentforms_fixes();
} else {
	add_action('plugins_loaded', __NAMESPACE__ . '\load_fluentforms_fixes');
}

/**
 * Registers the Video Play Toggle block when it is not provided by the
 * standalone Flashblocks Video Play Toggle plugin.
 */
function register_video_play_toggle_block(): void {
	$registry = \WP_Block_Type_Registry::get_instance();

	if ($registry->is_registered('flashblocks/video-play-toggle')) {
		return;
	}

	$block_path = DIR . '/build/video-play-toggle';

	register_block_type($block_path);

	if ($registry->is_registered('flashblocks/video-controls')) {
		return;
	}

	register_block_type(
		$block_path,
		array(
			'name'        => 'flashblocks/video-controls',
			'title'       => __('Video Play Toggle (Legacy)', 'flashblocks-accessibility'),
			'description' => __('Legacy alias for Video Play Toggle blocks saved before the rename.', 'flashblocks-accessibility'),
			'supports'    => array(
				'inserter' => false,
			),
		)
	);
}
add_action('init', __NAMESPACE__ . '\register_video_play_toggle_block', 20);

if (is_admin()) {
    require_once DIR . '/includes/class-updater.php';
    new Includes\Updater();
}
