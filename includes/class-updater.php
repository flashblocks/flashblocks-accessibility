<?php

namespace Flashblocks\Accessibility\Includes;

// If this file is called directly, abort.
if (!defined('WPINC')) die;

// Stop if the class already exists
if (class_exists('Flashblocks\Accessibility\Includes\Updater')) {
    return;
}

/**
 * Updater Class
 *
 * Update a plugin from GitHub
 *
 * @since 1.0.0
 * @version 1.0.0
 */
class Updater {
    private const REPOSITORY = 'sunmorgn/flashblocks-accessibility';
    private const PLUGIN_FILE = 'flashblocks-accessibility/flashblocks-accessibility.php';
    private const BASENAME = 'flashblocks-accessibility';
    private const TRANSIENT_KEY = 'flashblocks_accessibility_github_response';
    private $github_response;

    /**
     * Constructor class to register all the hooks.
     *
     * @since 1.0.0
     * @return void
     */
    public function __construct() {
        add_filter('plugins_api', [$this, 'plugin_popup'], 10, 3);
        add_filter('pre_set_site_transient_update_plugins', [$this, 'modify_transient']);
        add_filter('upgrader_post_install', [$this, 'install_update'], 10, 3);
    }

    /**
     * Get the instance of the Updater class
     *
     * @since 1.0.0
     * @return Updater
     */
    public static function get_instance(): Updater {
        static $instance = null;
        if (null === $instance) {
            $instance = new static();
        }
        return $instance;
    }

    /**
     * Get the latest release from the selected repository
     *
     * @since 1.0.0
     * @return array
     */
    private function get_latest_repository_release(): array {
        $request_uri = sprintf(
            'https://api.github.com/repos/%s/releases/latest',
            $this::REPOSITORY
        );

        $request = wp_remote_get($request_uri);

        $response_codes = wp_remote_retrieve_response_code($request);
        if ( is_wp_error( $request ) || $response_codes < 200 || $response_codes >= 300 ) {
            return [];
        }

        $response = json_decode(wp_remote_retrieve_body($request), true);

        if ( ! is_array( $response ) ) {
            return [];
        }

        return $response;
    }

    /**
     * Private method to get repository information for a plugin
     *
     * @since 1.0.0
     * @return array $response
     */
    private function get_repository_info(): array {
        if (!empty($this->github_response)) {
            return $this->github_response;
        }

        $force_check = ! empty( $_GET['force-check'] );

        $cached_response = $force_check ? false : get_transient( self::TRANSIENT_KEY );
        if (false !== $cached_response) {
            $this->github_response = $cached_response;
            return $cached_response;
        }

        $response = $this->get_latest_repository_release();

        if (!empty($response)) {
            set_transient(self::TRANSIENT_KEY, $response, 12 * HOUR_IN_SECONDS);
        }

        $this->github_response = $response;

        return $response;
    }

    /**
     * Add details to the plugin popup
     *
     * @since 1.0.0
     * @param boolean $result
     * @param string $action
     * @param object $args
     * @return boolean|object|array $result
     */
    public function plugin_popup($result, $action, $args) {
        if ($action !== 'plugin_information') {
            return $result;
        }

        if ($args->slug !== $this::BASENAME) {
            return $result;
        }

        $repo = $this->get_repository_info();

        if (empty($repo)) return $result;

        $details = \get_plugin_data(plugin_dir_path(__FILE__) . '../flashblocks-accessibility.php');

        $plugin = [
            'name' => $details['Name'],
            'slug' => $this::BASENAME,
            'requires' => $details['RequiresWP'],
            'requires_php' => $details['RequiresPHP'],
            'version' => $repo['tag_name'],
            'author' => $details['AuthorName'],
            'author_profile' => $details['AuthorURI'],
            'last_updated' => $repo['published_at'],
            'homepage' => $details['PluginURI'],
            'short_description' => $details['Description'],
            'sections' => [
                'Description' => $details['Description'],
                'Updates' => $repo['body']
            ],
            'download_link' => $repo['zipball_url']
        ];

        return (object) $plugin;
    }

    /**
     * Modify transient for module
     *
     * @since 1.0.0
     * @param object $transient
     * @return object
     */
    public function modify_transient(object $transient): object {
        if (!isset($transient->checked)) return $transient;

        $checked = $transient->checked;

        if (empty($checked)) return $transient;

        if (!array_key_exists($this::PLUGIN_FILE, $checked)) {
            return $transient;
        }

        $repo_info = $this->get_repository_info();

        if (empty($repo_info)) return $transient;

        $github_version = ltrim($repo_info['tag_name'], 'v');

        $out_of_date = version_compare(
            $github_version,
            $checked[$this::PLUGIN_FILE],
            'gt'
        );

        if (!$out_of_date) return $transient;

        $transient->response[$this::PLUGIN_FILE] = (object) [
            'id' => $repo_info['html_url'],
            'url' => $repo_info['html_url'],
            'slug' => current(explode('/', $this::BASENAME)),
            'package' => $repo_info['zipball_url'],
            'new_version' => $repo_info['tag_name']
        ];

        return $transient;
    }

    /**
     * Install the plugin from GitHub
     *
     * @since 1.0.0
     * @param boolean $response
     * @param array $hook_extra
     * @param array $result
     * @return boolean|array $result
     */
    public function install_update($response, $hook_extra, $result) {
        if ( empty( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== self::PLUGIN_FILE ) {
            return $result;
        }

        global $wp_filesystem;

        $correct_directory_name = dirname( self::PLUGIN_FILE );
        $downloaded_directory_path = $result['destination'];
        $parent_directory_path = dirname($downloaded_directory_path);
        $correct_directory_path = $parent_directory_path . '/' . $correct_directory_name;

        $wp_filesystem->move($downloaded_directory_path, $correct_directory_path);

        $result['destination'] = $correct_directory_path;

        if (\is_plugin_active($this::PLUGIN_FILE)) {
            activate_plugin($this::PLUGIN_FILE);
        }

        return $result;
    }

}
