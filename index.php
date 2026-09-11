<?php 
/*
 * Plugin Name:       Starter Plugin
 * Plugin URI:        http://masum-billah.com
 * Description:       WordPress Starter Plugin with Auto-Binding Service Container, Action Links, Admin Screens, and Full Database CRUD
 * Version:           1.0.0
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Author:            H M Masum Billah
 * Author URI:        http://masum-billah.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        http://masum-billah.com
 * Text Domain:       starter
 * Domain Path:       /languages
 * Requires Plugins:   
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/autoloader.php';

if ( ! defined('STARTER_VERSION') ) define( 'STARTER_VERSION', '1.0.0' );

if ( ! defined('STARTER_DIR_PATH') ) define( 'STARTER_DIR_PATH', plugin_dir_path(__FILE__) );

if ( ! defined('STARTER_PATH_URL') ) define( 'STARTER_PATH_URL', plugin_dir_url(__FILE__) );

use STARTER\Inc\Starter_Init;
use STARTER\Inc\Services\Service_Init;

/**
 * Initialize starter plugin.
 */
function starter_plugin_init() {
    require_once __DIR__ . '/inc/starter-init.php';
    new Starter_Init();
}
add_action( 'plugins_loaded', 'starter_plugin_init' );

/**
 * Global helper to access the plugin service container.
 *
 * @return Service_Init
 */
function starter_container(): Service_Init {
    return Service_Init::get_instance();
}

register_activation_hook( __FILE__, [ 'STARTER\Inc\Starter_Init', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'STARTER\Inc\Starter_Init', 'deactivate' ] );
register_uninstall_hook( __FILE__, [ 'STARTER\Inc\Starter_Init', 'uninstall' ] );