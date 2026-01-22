<?php

namespace Flashblocks\Accessibility\Includes;

use WP_HTML_Tag_Processor;

if ( ! defined( 'WPINC' ) ) die;

/**
 * Automatically fixes common ADA accessibility issues in rendered HTML.
 */
class ADA_Fixes {

    /**
     * Label mappings for social platforms, button classes, and input types.
     */
    private const SOCIAL_LABELS = [
        'facebook'  => 'Facebook',
        'twitter'   => 'Twitter',
        'x.com'     => 'X',
        'linkedin'  => 'LinkedIn',
        'instagram' => 'Instagram',
        'youtube'   => 'YouTube',
        'tiktok'    => 'TikTok',
        'pinterest' => 'Pinterest',
        'github'    => 'GitHub',
    ];

    private const BUTTON_LABELS = [
        'close'     => 'Close',
        'dismiss'   => 'Dismiss',
        'menu'      => 'Menu',
        'hamburger' => 'Menu',
        'toggle'    => 'Toggle',
        'search'    => 'Search',
        'submit'    => 'Submit',
        'prev'      => 'Previous',
        'previous'  => 'Previous',
        'next'      => 'Next',
        'play'      => 'Play',
        'pause'     => 'Pause',
        'expand'    => 'Expand',
        'collapse'  => 'Collapse',
        'nav'       => 'Navigation',
    ];

    private const INPUT_LABELS = [
        'text'     => 'Text input',
        'email'    => 'Email address',
        'tel'      => 'Phone number',
        'password' => 'Password',
        'search'   => 'Search',
        'url'      => 'Website URL',
        'number'   => 'Number',
        'date'     => 'Date',
        'time'     => 'Time',
        'checkbox' => 'Checkbox',
        'radio'    => 'Radio option',
        'file'     => 'File upload',
    ];

    public function __construct() {
        if ( ! is_admin() && ! wp_doing_ajax() && ! wp_doing_cron() && ! defined( 'REST_REQUEST' ) ) {
            add_action( 'template_redirect', [ $this, 'start_buffer' ], 1 );
            add_action( 'shutdown', [ $this, 'end_buffer' ], 0 );
        }
    }

    public function start_buffer(): void {
        ob_start( [ $this, 'process' ] );
    }

    public function end_buffer(): void {
        if ( ob_get_level() > 0 ) {
            ob_end_flush();
        }
    }

    /**
     * Process HTML and apply all ADA fixes.
     */
    public function process( string $html ): string {
        if ( empty( $html ) || stripos( $html, '<html' ) === false ) {
            return $html;
        }

        $fixes = apply_filters( 'flashblocks_accessibility_ada_fixes', [
            [ $this, 'fix_progressbars' ],
            [ $this, 'fix_empty_links' ],
            [ $this, 'fix_empty_buttons' ],
            [ $this, 'fix_images' ],
            [ $this, 'fix_inputs' ],
            [ $this, 'fix_duplicate_labels' ],
        ] );

        foreach ( $fixes as $fix ) {
            if ( is_callable( $fix ) ) {
                $html = $fix( $html );
            }
        }

        return $html;
    }

    /**
     * Check if element already has an accessible name.
     */
    private function has_accessible_name( WP_HTML_Tag_Processor $tags ): bool {
        return $tags->get_attribute( 'aria-label' ) !== null
            || $tags->get_attribute( 'aria-labelledby' ) !== null
            || $tags->get_attribute( 'title' ) !== null;
    }

    /**
     * Find first matching key in a string and return its value.
     */
    private function match_label( string $haystack, array $labels, string $default ): string {
        $haystack = strtolower( $haystack );
        foreach ( $labels as $key => $label ) {
            if ( strpos( $haystack, $key ) !== false ) {
                return $label;
            }
        }
        return $default;
    }

    /**
     * Extract percentage from aria-valuenow or style width.
     */
    private function get_percentage( WP_HTML_Tag_Processor $tags ): ?int {
        $value = $tags->get_attribute( 'aria-valuenow' );
        if ( $value !== null ) {
            return (int) $value;
        }

        $style = $tags->get_attribute( 'style' );
        if ( $style && preg_match( '/width:\s*(\d+)%/', $style, $m ) ) {
            return (int) $m[1];
        }

        return null;
    }

    /**
     * Fix progressbar elements without accessible names.
     */
    public function fix_progressbars( string $html ): string {
        $tags = new WP_HTML_Tag_Processor( $html );

        while ( $tags->next_tag() ) {
            if ( $tags->get_attribute( 'role' ) !== 'progressbar' || $this->has_accessible_name( $tags ) ) {
                continue;
            }

            $pct = $this->get_percentage( $tags );
            $label = $pct !== null
                ? sprintf( 'Progress: %d%% complete', $pct )
                : 'Progress indicator';

            $tags->set_attribute( 'aria-label', $label );
        }

        return $tags->get_updated_html();
    }

    /**
     * Fix empty links (icon links, social links without text).
     */
    public function fix_empty_links( string $html ): string {
        // Pre-scan: find empty <a> tags by matching pattern
        // This regex finds <a...>content</a> and we check if content is empty
        return preg_replace_callback(
            '/<a\s([^>]*)>(.*?)<\/a>/is',
            function( $m ) {
                $attrs   = $m[1];
                $content = $m[2];

                // Skip if has accessible name
                if ( preg_match( '/aria-label(?:ledby)?=|(?<!\w)title=/i', $attrs ) ) {
                    return $m[0];
                }

                // Check for text content or img with alt
                $text = trim( strip_tags( $content ) );
                if ( empty( $text ) && preg_match( '/<img[^>]+alt=["\']([^"\']+)/', $content, $alt ) ) {
                    $text = $alt[1];
                }

                if ( ! empty( $text ) ) {
                    return $m[0];
                }

                // Derive label from href
                $label = 'Link';
                if ( preg_match( '/href=["\']([^"\']+)/', $attrs, $href ) ) {
                    $url = strtolower( $href[1] );
                    if ( strpos( $url, 'mailto:' ) === 0 ) {
                        $label = 'Email';
                    } elseif ( strpos( $url, 'tel:' ) === 0 ) {
                        $label = 'Phone';
                    } else {
                        $label = $this->match_label( $url, self::SOCIAL_LABELS, 'Link' );
                    }
                }

                return '<a aria-label="' . esc_attr( $label ) . '" ' . $attrs . '>' . $content . '</a>';
            },
            $html
        );
    }

    /**
     * Fix empty buttons (icon buttons without text).
     */
    public function fix_empty_buttons( string $html ): string {
        return preg_replace_callback(
            '/<button\s([^>]*)>(.*?)<\/button>/is',
            function( $m ) {
                $attrs   = $m[1];
                $content = $m[2];

                // Skip if has accessible name or value
                if ( preg_match( '/aria-label(?:ledby)?=|(?<!\w)title=|(?<!\w)value=/i', $attrs ) ) {
                    return $m[0];
                }

                // Skip if has text content
                if ( ! empty( trim( strip_tags( $content ) ) ) ) {
                    return $m[0];
                }

                // Derive label from class
                $label = 'Button';
                if ( preg_match( '/class=["\']([^"\']+)/', $attrs, $class ) ) {
                    $label = $this->match_label( $class[1], self::BUTTON_LABELS, 'Button' );
                }

                return '<button aria-label="' . esc_attr( $label ) . '" ' . $attrs . '>' . $content . '</button>';
            },
            $html
        );
    }

    /**
     * Fix images without alt attributes.
     */
    public function fix_images( string $html ): string {
        $tags = new WP_HTML_Tag_Processor( $html );

        while ( $tags->next_tag( 'img' ) ) {
            if ( $tags->get_attribute( 'alt' ) !== null ) {
                continue;
            }

            $role    = $tags->get_attribute( 'role' );
            $classes = strtolower( $tags->get_attribute( 'class' ) ?? '' );
            $title   = $tags->get_attribute( 'title' );

            $is_decorative = $role === 'presentation'
                || $role === 'none'
                || (bool) preg_match( '/decorative|bg-|background|spacer/', $classes );

            $tags->set_attribute( 'alt', ( $title && ! $is_decorative ) ? $title : '' );
        }

        return $tags->get_updated_html();
    }

    /**
     * Fix inputs without labels.
     */
    public function fix_inputs( string $html ): string {
        $tags = new WP_HTML_Tag_Processor( $html );
        $skip = [ 'hidden', 'submit', 'button', 'image', 'reset' ];

        while ( $tags->next_tag( 'input' ) ) {
            $type = strtolower( $tags->get_attribute( 'type' ) ?? 'text' );

            if ( in_array( $type, $skip, true ) ) {
                continue;
            }

            // Skip if already accessible
            if ( $tags->get_attribute( 'aria-label' ) !== null
                || $tags->get_attribute( 'aria-labelledby' ) !== null
                || $tags->get_attribute( 'id' ) !== null ) {
                continue;
            }

            // Try placeholder
            $label = $tags->get_attribute( 'placeholder' );

            // Try name
            if ( ! $label ) {
                $name = $tags->get_attribute( 'name' );
                if ( $name ) {
                    $label = ucwords( trim( preg_replace( '/[\[\]_-]+/', ' ', $name ) ) );
                }
            }

            // Fallback to type
            if ( ! $label ) {
                $label = self::INPUT_LABELS[ $type ] ?? 'Input field';
            }

            $tags->set_attribute( 'aria-label', $label );
        }

        return $tags->get_updated_html();
    }

    /**
     * Fix duplicate labels (multiple labels with same 'for' attribute).
     * Removes 'for' attribute from wrapper labels used for styling.
     */
    public function fix_duplicate_labels( string $html ): string {
        // Fix FluentForms file upload wrapper labels
        // These have class="ff_file_upload_holder" and wrap the input for click handling
        $html = preg_replace(
            '/<label\s+for="([^"]+)"\s+class="ff_file_upload_holder"/',
            '<label class="ff_file_upload_holder"',
            $html
        );

        // Handle reverse attribute order: class before for
        $html = preg_replace(
            '/<label\s+class="ff_file_upload_holder"\s+for="([^"]+)"/',
            '<label class="ff_file_upload_holder"',
            $html
        );

        return $html;
    }
}
