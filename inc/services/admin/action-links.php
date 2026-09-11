<?php
namespace STARTER\Inc\Services\Admin;

use STARTER\Inc\Contracts\Service_Interface;

class Action_Links implements Service_Interface {

    /**
     * Register action links hooks.
     *
     * @return void
     */
    public function register(): void {
        $plugin_basename = plugin_basename(STARTER_DIR_PATH . 'index.php');

        add_filter("plugin_action_links_{$plugin_basename}", [$this, 'add_action_links']);
        add_filter('plugin_row_meta', [$this, 'add_row_meta_links'], 10, 2);
    }

    /**
     * Add Settings and CRUD navigation links to plugins.php action links.
     *
     * @param array $links
     * @return array
     */
    public function add_action_links(array $links): array {
        $custom_links = [
            '<a href="' . esc_url(admin_url('admin.php?page=starter-plugin-settings')) . '" style="font-weight: 600; color: #2271b1;">' . esc_html__('Settings', 'starter') . '</a>',
            '<a href="' . esc_url(admin_url('admin.php?page=starter-plugin')) . '">' . esc_html__('All Records', 'starter') . '</a>',
            '<a href="' . esc_url(admin_url('admin.php?page=starter-plugin-add')) . '">' . esc_html__('Add New', 'starter') . '</a>',
        ];

        return array_merge($custom_links, $links);
    }

    /**
     * Add documentation and support meta links below plugin row description.
     *
     * @param array $links
     * @param string $file
     * @return array
     */
    public function add_row_meta_links(array $links, string $file): array {
        $plugin_basename = plugin_basename(STARTER_DIR_PATH . 'index.php');

        if ($file === $plugin_basename) {
            $row_meta = [
                'docs' => '<a href="' . esc_url('https://github.com') . '" target="_blank" rel="noopener noreferrer">' . esc_html__('Documentation', 'starter') . '</a>',
                'support' => '<a href="' . esc_url('http://masum-billah.com') . '" target="_blank" rel="noopener noreferrer">' . esc_html__('Support', 'starter') . '</a>',
            ];

            return array_merge($links, $row_meta);
        }

        return $links;
    }
}
