<?php

namespace Flashblocks\Accessibility\Includes;

use WP_HTML_Tag_Processor;

if (! defined('WPINC')) die;

/**
 * FluentForms-specific accessibility fixes.
 * Only loaded when FluentForms plugin is active.
 */
class FluentForms_Fixes {

    public function __construct() {
        // Hook into single-pass tag processor for label fixes
        add_action('flashblocks_accessibility_process_tag', [$this, 'process_tag'], 10, 2);

        // Register regex-based fixes
        add_filter('flashblocks_accessibility_ada_fixes', [$this, 'register_fixes']);
    }

    /**
     * Process tags during single-pass traversal.
     * Handles label fixes without additional DOM parsing.
     */
    public function process_tag(WP_HTML_Tag_Processor $tags, string $tag_name): void {
        if ($tag_name !== 'label') {
            return;
        }

        $class = $tags->get_attribute('class');

        // Remove 'for' from file upload wrapper labels
        if ($class && strpos($class, 'ff_file_upload_holder') !== false) {
            $tags->remove_attribute('for');
        }
    }

    /**
     * Register regex-based fixes that need to inspect element structure.
     */
    public function register_fixes(array $fixes): array {
        $fixes[] = [$this, 'fix_orphaned_group_labels'];
        return $fixes;
    }

    /**
     * Fix orphaned labels in FluentForms field group headers.
     * Bare <label> tags (no attributes) are semantically incorrect - they're not
     * associated with any form control. Convert them to <span>.
     */
    public function fix_orphaned_group_labels(string $html): string {
        // Match bare <label> tags (with optional whitespace before >)
        return preg_replace(
            '/<label\s*>([^<]*)<\/label>/i',
            '<span>$1</span>',
            $html
        );
    }
}
