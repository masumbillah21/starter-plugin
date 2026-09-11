<?php
/**
 * Settings Screen Template
 *
 * @var array $settings
 */

defined('ABSPATH') || exit;

$message = isset($_GET['message']) ? sanitize_text_field($_GET['message']) : '';
$per_page = get_option('starter_records_per_page', 10);
$default_status = get_option('starter_default_status', 'active');
$enable_notifications = get_option('starter_enable_notifications', '0');
?>

<div class="wrap starter-wrap">
    <!-- Header -->
    <div class="starter-header">
        <div class="starter-header-left">
            <h1>
                <span class="dashicons dashicons-admin-settings" style="font-size: 28px; width: 28px; height: 28px; color: var(--starter-primary);"></span>
                <?php esc_html_e('Starter Plugin Settings', 'starter'); ?>
            </h1>
            <p><?php esc_html_e('Configure preferences, pagination defaults, and database options.', 'starter'); ?></p>
        </div>
        <div class="starter-header-right">
            <a href="<?php echo esc_url(admin_url('admin.php?page=starter-plugin')); ?>" class="starter-btn starter-btn-secondary">
                <span class="dashicons dashicons-database" style="margin-right: 4px; font-size: 16px;"></span>
                <?php esc_html_e('View Records', 'starter'); ?>
            </a>
        </div>
    </div>

    <!-- Feedback Notice -->
    <?php if ($message === 'saved'): ?>
        <div class="starter-alert starter-alert-success">
            <span class="dashicons dashicons-yes-alt"></span>
            <?php esc_html_e('Settings saved successfully!', 'starter'); ?>
        </div>
    <?php endif; ?>

    <!-- Settings Card -->
    <div class="starter-card">
        <div class="starter-card-body">
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                <input type="hidden" name="action" value="starter_save_settings" />
                <?php wp_nonce_field('starter_settings_nonce_action', 'starter_settings_nonce'); ?>

                <!-- Per Page Setting -->
                <div class="starter-form-group">
                    <label for="starter_records_per_page">
                        <?php esc_html_e('Records Per Page', 'starter'); ?>
                    </label>
                    <select id="starter_records_per_page" name="starter_records_per_page" class="starter-form-control" style="max-width: 200px;">
                        <option value="5" <?php selected($per_page, 5); ?>>5</option>
                        <option value="10" <?php selected($per_page, 10); ?>>10</option>
                        <option value="20" <?php selected($per_page, 20); ?>>20</option>
                        <option value="50" <?php selected($per_page, 50); ?>>50</option>
                    </select>
                    <span class="starter-help-text"><?php esc_html_e('Number of records shown per page on the All Records screen.', 'starter'); ?></span>
                </div>

                <!-- Default Status Setting -->
                <div class="starter-form-group">
                    <label for="starter_default_status">
                        <?php esc_html_e('Default Record Status', 'starter'); ?>
                    </label>
                    <select id="starter_default_status" name="starter_default_status" class="starter-form-control" style="max-width: 200px;">
                        <option value="active" <?php selected($default_status, 'active'); ?>><?php esc_html_e('Active', 'starter'); ?></option>
                        <option value="inactive" <?php selected($default_status, 'inactive'); ?>><?php esc_html_e('Inactive', 'starter'); ?></option>
                        <option value="draft" <?php selected($default_status, 'draft'); ?>><?php esc_html_e('Draft', 'starter'); ?></option>
                    </select>
                    <span class="starter-help-text"><?php esc_html_e('Initial default status assigned when creating a new record.', 'starter'); ?></span>
                </div>

                <!-- Notifications Toggle -->
                <div class="starter-form-group">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="starter_enable_notifications" value="1" <?php checked($enable_notifications, '1'); ?> />
                        <span><?php esc_html_e('Enable Admin Email Notifications on new entries', 'starter'); ?></span>
                    </label>
                    <span class="starter-help-text" style="margin-left: 24px;"><?php esc_html_e('Send an email to the site admin whenever a new record is created.', 'starter'); ?></span>
                </div>

                <!-- Form Action Buttons -->
                <div class="starter-form-actions">
                    <button type="submit" class="starter-btn starter-btn-primary">
                        <span class="dashicons dashicons-saved" style="margin-right: 4px;"></span>
                        <?php esc_html_e('Save Settings', 'starter'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
