<?php
namespace STARTER\Inc\Services\Admin;

use STARTER\Inc\Contracts\Service_Interface;
use STARTER\Inc\Services\Database\Starter_DB;
use STARTER\Inc\Services\Database\Migration_Manager;

class Admin_Menu implements Service_Interface {

    /**
     * Stored page hook suffixes.
     *
     * @var array
     */
    private $page_hooks = [];

    /**
     * Services auto-wired by container.
     *
     * @param Starter_DB $db
     * @param Migration_Manager $migration_manager
     */
    public function __construct(
        private Starter_DB $db,
        private Migration_Manager $migration_manager
    ) {
    }

    /**
     * Register menu hooks and asset loaders.
     *
     * @return void
     */
    public function register(): void {
        add_action('admin_menu', [$this, 'register_menu_pages']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    /**
     * Register top-level and submenu pages in WordPress admin.
     *
     * @return void
     */
    public function register_menu_pages(): void {
        // Main top-level menu page
        $main_hook = add_menu_page(
            __('Starter Plugin', 'starter'),
            __('Starter Plugin', 'starter'),
            'manage_options',
            'starter-plugin',
            [$this, 'render_records_page'],
            'dashicons-database',
            26
        );
        $this->page_hooks[] = $main_hook;

        // Submenu: All Records (same slug as parent)
        $records_hook = add_submenu_page(
            'starter-plugin',
            __('All Records', 'starter'),
            __('All Records', 'starter'),
            'manage_options',
            'starter-plugin',
            [$this, 'render_records_page']
        );
        $this->page_hooks[] = $records_hook;

        // Submenu: Add New
        $add_hook = add_submenu_page(
            'starter-plugin',
            __('Add New Record', 'starter'),
            __('Add New', 'starter'),
            'manage_options',
            'starter-plugin-add',
            [$this, 'render_add_page']
        );
        $this->page_hooks[] = $add_hook;

        // Submenu: Settings
        $settings_hook = add_submenu_page(
            'starter-plugin',
            __('Starter Settings', 'starter'),
            __('Settings', 'starter'),
            'manage_options',
            'starter-plugin-settings',
            [$this, 'render_settings_page']
        );
        $this->page_hooks[] = $settings_hook;
    }

    /**
     * Enqueue styles and scripts only on plugin admin screens.
     *
     * @param string $hook
     * @return void
     */
    public function enqueue_admin_assets(string $hook): void {
        if (!in_array($hook, $this->page_hooks, true)) {
            return;
        }

        wp_enqueue_style(
            'starter-admin-style',
            STARTER_PATH_URL . 'assets/css/admin-style.css',
            ['dashicons'],
            STARTER_VERSION
        );
    }

    /**
     * Render the All Records list screen.
     *
     * @return void
     */
    public function render_records_page(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'starter'));
        }

        // Auto-create database table if not yet present
        if (!$this->db->table_exists()) {
            $this->db->create_table();
        }

        $search = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
        $current_status = isset($_GET['status']) ? sanitize_text_field(wp_unslash($_GET['status'])) : '';
        $current_page = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
        $per_page = (int) get_option('starter_records_per_page', 10);

        $query_args = [
            'search'   => $search,
            'status'   => $current_status,
            'per_page' => $per_page,
            'page'     => $current_page,
            'orderby'  => 'id',
            'order'    => 'DESC',
        ];

        $total_records = $this->db->count(['search' => $search, 'status' => $current_status]);
        $total_pages   = max(1, ceil($total_records / $per_page));
        $records       = $this->db->get_all($query_args);

        // Calculate counts for status filter pills
        $status_counts = [
            'all'      => $this->db->count(['search' => $search]),
            'active'   => $this->db->count(['search' => $search, 'status' => 'active']),
            'inactive' => $this->db->count(['search' => $search, 'status' => 'inactive']),
            'draft'    => $this->db->count(['search' => $search, 'status' => 'draft']),
        ];

        include STARTER_DIR_PATH . 'templates/admin/records-list.php';
    }

    /**
     * Render the Add New or Edit Record screen.
     *
     * @return void
     */
    public function render_add_page(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'starter'));
        }

        $record = null;
        $is_edit = false;

        if (isset($_GET['action']) && $_GET['action'] === 'edit' && !empty($_GET['id'])) {
            $id = absint($_GET['id']);
            $record = $this->db->get_by_id($id);

            if (!$record) {
                wp_die(esc_html__('The requested record was not found.', 'starter'));
            }

            $is_edit = true;
        }

        include STARTER_DIR_PATH . 'templates/admin/record-form.php';
    }

    /**
     * Render the Settings screen.
     *
     * @return void
     */
    public function render_settings_page(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'starter'));
        }

        $migration_manager = $this->migration_manager;
        include STARTER_DIR_PATH . 'templates/admin/settings.php';
    }
}
