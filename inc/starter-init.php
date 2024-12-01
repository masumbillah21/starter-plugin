<?php
namespace STARTER\Inc;

use STARTER\Inc\Services\Service_Init;

class Starter_Init {

    public function __construct() {
        $this->load_hooks();

        new Service_Init();
    }

    private function load_hooks(){
        add_action('wp_enqueue_scripts', [$this, 'load_styles']);
        add_action('wp_enqueue_scripts', [$this, 'load_scripts']);
    }

    public function load_styles(){
        wp_enqueue_style(
            'starter-plugin-style', 
            STARTER_PATH_URL . 'assets/css/style.css', // Path to CSS file
            [],
            STARTER_VERSION,
            'all'
        );
    }


    public function load_scripts() {
        wp_enqueue_script(
            'starter-plugin-script',
            STARTER_PATH_URL . 'assets/js/script.js',
            ['jquery'],
            STARTER_VERSION, 
            true
        );

        $localized_data = [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('starter_plugin_nonce'),
            'plugin_url' => STARTER_PATH_URL
        ];

        wp_localize_script('starter-plugin-script', 'starterPluginData', $localized_data);
    }

    /**
     * The activation hook for the plugin.
     * This method will run when the plugin is activated.
     */
    public static function activate() {

        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }

        add_option( 'starter_plugin_activated', true );

    }

    /**
     * The deactivation hook for the plugin.
     * This method will run when the plugin is deactivated.
     */
    public static function deactivate() {
        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }

        delete_option( 'starter_plugin_activated' );
    }

    public static function uninstall() {
        if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
            die; // Exit if this is not a valid uninstall request
        }

        delete_option( 'starter_plugin_activated' );
        
    }
}
