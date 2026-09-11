<?php
namespace STARTER\Inc\Services\Crud;

use STARTER\Inc\Contracts\Service_Interface;
use STARTER\Inc\Services\Database\Starter_DB;

class Crud_Handler implements Service_Interface {

    /**
     * Database service dependency auto-wired by container.
     *
     * @param Starter_DB $db
     */
    public function __construct(private Starter_DB $db) {
    }

    /**
     * Register form submission and action hooks.
     *
     * @return void
     */
    public function register(): void {
        add_action('admin_post_starter_create_record', [$this, 'handle_create']);
        add_action('admin_post_starter_update_record', [$this, 'handle_update']);
        add_action('admin_post_starter_delete_record', [$this, 'handle_delete']);
        add_action('admin_post_starter_save_settings', [$this, 'handle_save_settings']);
    }

    /**
     * Handle new record creation.
     *
     * @return void
     */
    public function handle_create(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Unauthorized capability.', 'starter'));
        }

        check_admin_referer('starter_record_action_nonce', 'starter_record_nonce');

        $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
        $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        $status = isset($_POST['status']) ? sanitize_text_field(wp_unslash($_POST['status'])) : 'active';
        $description = isset($_POST['description']) ? sanitize_textarea_field(wp_unslash($_POST['description'])) : '';

        // Validation
        if (empty($name)) {
            $redirect_url = add_query_arg(
                ['error' => urlencode(__('Record name is required.', 'starter'))],
                admin_url('admin.php?page=starter-plugin-add')
            );
            wp_safe_redirect($redirect_url);
            exit;
        }

        if (!empty($_POST['email']) && !is_email($email)) {
            $redirect_url = add_query_arg(
                ['error' => urlencode(__('Please enter a valid email address.', 'starter'))],
                admin_url('admin.php?page=starter-plugin-add')
            );
            wp_safe_redirect($redirect_url);
            exit;
        }

        $inserted_id = $this->db->insert([
            'name'        => $name,
            'email'       => $email,
            'status'      => $status,
            'description' => $description,
        ]);

        if ($inserted_id) {
            // Check if admin notification is enabled
            if (get_option('starter_enable_notifications') === '1') {
                $admin_email = get_option('admin_email');
                $subject = sprintf(__('New Starter Record Created: %s', 'starter'), $name);
                $message = sprintf(
                    __("A new record has been created on %s:\n\nID: %d\nName: %s\nStatus: %s\n\nView all: %s", 'starter'),
                    get_bloginfo('name'),
                    $inserted_id,
                    $name,
                    $status,
                    admin_url('admin.php?page=starter-plugin')
                );
                wp_mail($admin_email, $subject, $message);
            }

            wp_safe_redirect(admin_url('admin.php?page=starter-plugin&message=created'));
            exit;
        }

        $redirect_url = add_query_arg(
            ['error' => urlencode(__('Failed to save record to database.', 'starter'))],
            admin_url('admin.php?page=starter-plugin-add')
        );
        wp_safe_redirect($redirect_url);
        exit;
    }

    /**
     * Handle record update.
     *
     * @return void
     */
    public function handle_update(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Unauthorized capability.', 'starter'));
        }

        check_admin_referer('starter_record_action_nonce', 'starter_record_nonce');

        $record_id = isset($_POST['record_id']) ? absint($_POST['record_id']) : 0;
        if (!$record_id) {
            wp_safe_redirect(admin_url('admin.php?page=starter-plugin'));
            exit;
        }

        $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
        $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        $status = isset($_POST['status']) ? sanitize_text_field(wp_unslash($_POST['status'])) : 'active';
        $description = isset($_POST['description']) ? sanitize_textarea_field(wp_unslash($_POST['description'])) : '';

        // Validation
        if (empty($name)) {
            $redirect_url = add_query_arg(
                ['error' => urlencode(__('Record name is required.', 'starter')), 'action' => 'edit', 'id' => $record_id],
                admin_url('admin.php?page=starter-plugin-add')
            );
            wp_safe_redirect($redirect_url);
            exit;
        }

        if (!empty($_POST['email']) && !is_email($email)) {
            $redirect_url = add_query_arg(
                ['error' => urlencode(__('Please enter a valid email address.', 'starter')), 'action' => 'edit', 'id' => $record_id],
                admin_url('admin.php?page=starter-plugin-add')
            );
            wp_safe_redirect($redirect_url);
            exit;
        }

        $updated = $this->db->update($record_id, [
            'name'        => $name,
            'email'       => $email,
            'status'      => $status,
            'description' => $description,
        ]);

        if ($updated !== false) {
            wp_safe_redirect(admin_url('admin.php?page=starter-plugin&message=updated'));
            exit;
        }

        $redirect_url = add_query_arg(
            ['error' => urlencode(__('Failed to update record.', 'starter')), 'action' => 'edit', 'id' => $record_id],
            admin_url('admin.php?page=starter-plugin-add')
        );
        wp_safe_redirect($redirect_url);
        exit;
    }

    /**
     * Handle record deletion.
     *
     * @return void
     */
    public function handle_delete(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Unauthorized capability.', 'starter'));
        }

        check_admin_referer('starter_delete_nonce');

        $record_id = isset($_REQUEST['id']) ? absint($_REQUEST['id']) : 0;
        if ($record_id) {
            $this->db->delete($record_id);
        }

        wp_safe_redirect(admin_url('admin.php?page=starter-plugin&message=deleted'));
        exit;
    }

    /**
     * Handle saving settings.
     *
     * @return void
     */
    public function handle_save_settings(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Unauthorized capability.', 'starter'));
        }

        check_admin_referer('starter_settings_nonce_action', 'starter_settings_nonce');

        $per_page = isset($_POST['starter_records_per_page']) ? absint($_POST['starter_records_per_page']) : 10;
        $default_status = isset($_POST['starter_default_status']) ? sanitize_text_field(wp_unslash($_POST['starter_default_status'])) : 'active';
        $notifications = isset($_POST['starter_enable_notifications']) ? '1' : '0';

        update_option('starter_records_per_page', $per_page);
        update_option('starter_default_status', $default_status);
        update_option('starter_enable_notifications', $notifications);

        wp_safe_redirect(admin_url('admin.php?page=starter-plugin-settings&message=saved'));
        exit;
    }
}
