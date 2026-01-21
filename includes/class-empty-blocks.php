<?php

namespace Flashblocks\Accessibility\Includes;

// If this file is called directly, abort.
if (!defined('WPINC')) die;

/**
 * Class Empty_Blocks
 *
 * Removes empty blocks that would cause ADA compliance issues.
 *
 * @package Flashblocks\Accessibility\Includes
 * @version 1.0.0
 */
class Empty_Blocks {
    /**
     * Block names that should be removed if empty.
     */
    private const EMPTY_BLOCKS = [
        'core/heading',
        'core/post-title',
        'core/button',
        'core/navigation-link',
    ];

    /**
     * Empty_Blocks constructor.
     */
    public function __construct() {
        add_filter('render_block', array($this, 'remove_empty_blocks'), 10, 2);
    }

    /**
     * Get the list of blocks that should be removed if empty.
     *
     * @return array
     */
    private function get_empty_blocks(): array {
        /**
         * Filters the list of blocks that should be removed if empty.
         *
         * @since 1.0.0
         *
         * @param array $blocks Array of block names to check for empty content.
         */
        return apply_filters('flashblocks_accessibility_empty_blocks', self::EMPTY_BLOCKS);
    }

    /**
     * Remove empty blocks from the output.
     *
     * @param string $block_content The block's HTML.
     * @param array  $block         The block's attributes and information.
     *
     * @return string The block content, or empty string if block is empty.
     */
    public function remove_empty_blocks(string $block_content, array $block): string {
        // Only process blocks in our list
        if (!in_array($block['blockName'], $this->get_empty_blocks(), true)) {
            return $block_content;
        }

        // Strip tags and whitespace to check for actual content
        $text_content = trim(strip_tags($block_content));

        // If no text content, return empty string
        if ('' === $text_content) {
            return '';
        }

        return $block_content;
    }
}
