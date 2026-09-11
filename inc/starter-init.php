<?php
namespace STARTER\Inc;

use STARTER\Inc\Services\Service_Init;
use STARTER\Inc\Services\Database\Starter_DB;

class Starter_Init {

    public function __construct() {
        $this->load_hooks();

        // Initialize auto-binding service container and boot services
        new Service_Init();
    }

    private function load_hooks() {
        add_action('wp_enqueue_scripts', [$this, 'load_styles']);
        add_action('wp_enqueue_scripts', [$this, 'load_scripts']);
    }

    public function load_styles() {
        if (file_exists(STARTER_DIR_PATH . 'assets/css/style.css')) {
            wp_enqueue_style(
                'starter-plugin-style', 
                STARTER_PATH_URL . 'assets/css/style.css',
                [],
                STARTER_VERSION,
                'all'
            );
        }
    }

    public function load_scripts() {
        if (file_exists(STARTER_DIR_PATH . 'assets/js/script.js')) {
            wp_enqueue_script(
                'starter-plugin-script',
                STARTER_PATH_URL . 'assets/js/script.js',
                ['jquery'],
                STARTER_VERSION, 
                true
            );

            $localized_data = [
                'ajax_url'   => admin_url('admin-ajax.php'),
                'nonce'      => wp_create_nonce('starter_plugin_nonce'),
                'plugin_url' => STARTER_PATH_URL
            ];

            wp_localize_script('starter-plugin-script', 'starterPluginData', $localized_data);
        }
    }

    /**
     * The activation hook for the plugin.
     * This method will run when the plugin is activated.
     */
    public static function activate() {
        if (!current_user_can('activate_plugins')) {
            return;
        }

        add_option('starter_plugin_activated', true);
        add_option('starter_records_per_page', 10);
        add_option('starter_default_status', 'active');
        add_option('starter_enable_notifications', '0');

        // Execute database schema migrations via container
        $migration_manager = starter_container()->resolve(\STARTER\Inc\Services\Database\Migration_Manager::class);
        $migration_manager->run_migrations();
    }

    /**
     * The deactivation hook for the plugin.
     * This method will run when the plugin is deactivated.
     */
    public static function deactivate() {
        if (!current_user_can('activate_plugins')) {
            return;
        }

        delete_option('starter_plugin_activated');
    }

    /**
     * The uninstall hook for the plugin.
     */
    public static function uninstall() {
        if (!defined('WP_UNINSTALL_PLUGIN')) {
            die;
        }

        delete_option('starter_plugin_activated');
        delete_option('starter_records_per_page');
        delete_option('starter_default_status');
        delete_option('starter_enable_notifications');
        delete_option('starter_db_version');
    }
}
