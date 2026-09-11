<?php
/**
 * Settings Screen Template
 *
 * @var array $settings
 */

defined('ABSPATH') || exit;

use STARTER\Inc\Services\Database\Migration_Manager;

$message = isset($_GET['message']) ? sanitize_text_field($_GET['message']) : '';
$per_page = get_option('starter_records_per_page', 10);
$default_status = get_option('starter_default_status', 'active');
$enable_notifications = get_option('starter_enable_notifications', '0');

// Database Migration Service (Injected from Admin_Menu or resolved via container)
$migration_manager = $migration_manager ?? starter_container()->resolve(Migration_Manager::class);
$installed_version = $migration_manager->get_installed_version();
$target_version    = $migration_manager->get_target_version();
$needs_migration   = $migration_manager->needs_migration();
$all_migrations    = $migration_manager->get_all_migrations();
$migration_history = $migration_manager->get_history();
?>

<div class="wrap starter-wrap">
    <!-- Header -->
    <div class="starter-header">
        <div class="starter-header-left">
            <h1>
                <span class="dashicons dashicons-admin-settings" style="font-size: 28px; width: 28px; height: 28px; color: var(--starter-primary);"></span>
                <?php esc_html_e('Starter Plugin Settings', 'starter'); ?>
            </h1>
            <p><?php esc_html_e('Configure preferences, pagination defaults, and database schema migrations.', 'starter'); ?></p>
        </div>
        <div class="starter-header-right">
            <a href="<?php echo esc_url(admin_url('admin.php?page=starter-plugin')); ?>" class="starter-btn starter-btn-secondary">
                <span class="dashicons dashicons-database" style="margin-right: 4px; font-size: 16px;"></span>
                <?php esc_html_e('View Records', 'starter'); ?>
            </a>
        </div>
    </div>

    <!-- Feedback Notices -->
    <?php if ($message === 'saved'): ?>
        <div class="starter-alert starter-alert-success">
            <span class="dashicons dashicons-yes-alt"></span>
            <?php esc_html_e('Settings saved successfully!', 'starter'); ?>
        </div>
    <?php elseif ($message === 'migrated'): ?>
        <?php if ($needs_migration): ?>
            <div class="starter-alert starter-alert-error">
                <span class="dashicons dashicons-warning"></span>
                <?php esc_html_e('Some database migrations could not be completed. Please review the execution history below.', 'starter'); ?>
            </div>
        <?php else: ?>
            <div class="starter-alert starter-alert-success">
                <span class="dashicons dashicons-yes-alt"></span>
                <?php printf(esc_html__('Database migrations executed successfully! Schema is up to date (v%s).', 'starter'), esc_html($installed_version)); ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($needs_migration && $message !== 'migrated'): ?>
        <div class="starter-alert starter-alert-error" style="background: #fffbeb; border: 1px solid #fde68a; color: #92400e;">
            <span class="dashicons dashicons-warning" style="color: #b45309;"></span>
            <?php printf(esc_html__('Pending database migrations detected (Installed: v%1$s, Target: v%2$s). Click "Run / Recheck Migrations Now" below to apply.', 'starter'), esc_html($installed_version), esc_html($target_version)); ?>
        </div>
    <?php endif; ?>

    <!-- General Settings Card -->
    <div class="starter-card">
        <div class="starter-toolbar" style="background: #ffffff; border-bottom: 1px solid var(--starter-border);">
            <h2 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--starter-text-dark); display: flex; align-items: center; gap: 8px;">
                <span class="dashicons dashicons-admin-generic" style="color: var(--starter-primary);"></span>
                <?php esc_html_e('General Preferences', 'starter'); ?>
            </h2>
        </div>
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
                <div class="starter-form-actions" style="margin-top: 20px; padding-top: 16px;">
                    <button type="submit" class="starter-btn starter-btn-primary">
                        <span class="dashicons dashicons-saved" style="margin-right: 4px;"></span>
                        <?php esc_html_e('Save Settings', 'starter'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Database & Schema Migrations Card -->
    <div class="starter-card">
        <div class="starter-toolbar" style="background: #ffffff; border-bottom: 1px solid var(--starter-border);">
            <h2 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--starter-text-dark); display: flex; align-items: center; gap: 8px;">
                <span class="dashicons dashicons-database-view" style="color: var(--starter-primary);"></span>
                <?php esc_html_e('Database Migrations & Schema Versions', 'starter'); ?>
            </h2>
        </div>
        <div class="starter-card-body">
            <!-- Version Status Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
                <div style="padding: 16px; background: #f8fafc; border: 1px solid var(--starter-border); border-radius: 8px;">
                    <span style="font-size: 12px; font-weight: 600; color: var(--starter-text-muted); text-transform: uppercase;">
                        <?php esc_html_e('Installed DB Version', 'starter'); ?>
                    </span>
                    <div style="font-size: 20px; font-weight: 700; color: var(--starter-text-dark); margin-top: 4px;">
                        v<?php echo esc_html($installed_version); ?>
                    </div>
                </div>

                <div style="padding: 16px; background: #f8fafc; border: 1px solid var(--starter-border); border-radius: 8px;">
                    <span style="font-size: 12px; font-weight: 600; color: var(--starter-text-muted); text-transform: uppercase;">
                        <?php esc_html_e('Target Code Version', 'starter'); ?>
                    </span>
                    <div style="font-size: 20px; font-weight: 700; color: var(--starter-text-dark); margin-top: 4px;">
                        v<?php echo esc_html($target_version); ?>
                    </div>
                </div>

                <div style="padding: 16px; background: #f8fafc; border: 1px solid var(--starter-border); border-radius: 8px;">
                    <span style="font-size: 12px; font-weight: 600; color: var(--starter-text-muted); text-transform: uppercase;">
                        <?php esc_html_e('Schema Status', 'starter'); ?>
                    </span>
                    <div style="margin-top: 6px;">
                        <?php if ($needs_migration): ?>
                            <span class="starter-badge starter-badge-draft">
                                <?php esc_html_e('Migration Required', 'starter'); ?>
                            </span>
                        <?php else: ?>
                            <span class="starter-badge starter-badge-active">
                                <span class="dashicons dashicons-yes" style="font-size: 14px; margin-right: 2px;"></span>
                                <?php esc_html_e('Up to date', 'starter'); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Manual Migration Action -->
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" style="margin-bottom: 24px;">
                <input type="hidden" name="action" value="starter_run_migrations" />
                <?php wp_nonce_field('starter_migration_nonce_action', 'starter_migration_nonce'); ?>
                <button type="submit" class="starter-btn starter-btn-secondary">
                    <span class="dashicons dashicons-update" style="margin-right: 4px;"></span>
                    <?php esc_html_e('Run / Recheck Migrations Now', 'starter'); ?>
                </button>
                <span class="starter-help-text" style="display: inline-block; margin-left: 10px;">
                    <?php esc_html_e('Checks schema version and applies any pending migrations in order.', 'starter'); ?>
                </span>
            </form>

            <!-- Registered Migrations List -->
            <h3 style="font-size: 14px; font-weight: 700; color: var(--starter-text-dark); margin-bottom: 12px;">
                <?php esc_html_e('Registered Migrations', 'starter'); ?>
            </h3>
            <div class="starter-table-responsive" style="margin-bottom: 24px;">
                <table class="starter-table" style="background: #ffffff; border: 1px solid var(--starter-border); border-radius: 8px;">
                    <thead>
                        <tr>
                            <th style="width: 100px;"><?php esc_html_e('Version', 'starter'); ?></th>
                            <th style="width: 250px;"><?php esc_html_e('Migration Class', 'starter'); ?></th>
                            <th><?php esc_html_e('Description', 'starter'); ?></th>
                            <th style="width: 120px; text-align: right;"><?php esc_html_e('Status', 'starter'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_migrations as $mig): ?>
                            <?php 
                            $is_applied = version_compare($mig->get_version(), $installed_version, '<=');
                            ?>
                            <tr>
                                <td><strong>v<?php echo esc_html($mig->get_version()); ?></strong></td>
                                <td><code><?php echo esc_html((new \ReflectionClass($mig))->getShortName()); ?></code></td>
                                <td style="color: var(--starter-text-muted);"><?php echo esc_html($mig->get_description()); ?></td>
                                <td style="text-align: right;">
                                    <?php if ($is_applied): ?>
                                        <span class="starter-badge starter-badge-active"><?php esc_html_e('Applied', 'starter'); ?></span>
                                    <?php else: ?>
                                        <span class="starter-badge starter-badge-draft"><?php esc_html_e('Pending', 'starter'); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Migration History Log -->
            <?php if (!empty($migration_history)): ?>
                <h3 style="font-size: 14px; font-weight: 700; color: var(--starter-text-dark); margin-bottom: 12px;">
                    <?php esc_html_e('Execution History', 'starter'); ?>
                </h3>
                <div class="starter-table-responsive">
                    <table class="starter-table" style="background: #ffffff; border: 1px solid var(--starter-border); border-radius: 8px;">
                        <thead>
                            <tr>
                                <th style="width: 90px;"><?php esc_html_e('Version', 'starter'); ?></th>
                                <th><?php esc_html_e('Description', 'starter'); ?></th>
                                <th style="width: 180px;"><?php esc_html_e('Executed At', 'starter'); ?></th>
                                <th style="width: 100px; text-align: right;"><?php esc_html_e('Result', 'starter'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_reverse($migration_history) as $entry): ?>
                                <tr>
                                    <td><strong>v<?php echo esc_html($entry['version'] ?? ''); ?></strong></td>
                                    <td style="color: var(--starter-text-muted);"><?php echo esc_html($entry['description'] ?? ''); ?></td>
                                    <td><?php echo esc_html($entry['executed_at'] ?? ''); ?></td>
                                    <td style="text-align: right;">
                                        <span class="starter-badge starter-badge-active"><?php echo esc_html(strtoupper($entry['status'] ?? 'SUCCESS')); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>
