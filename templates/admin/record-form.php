<?php
/**
 * Add / Edit Record Form Template
 *
 * @var array|null $record
 * @var bool $is_edit
 */

defined('ABSPATH') || exit;

$is_edit = !empty($record) && isset($record['id']);
$form_action = $is_edit ? 'starter_update_record' : 'starter_create_record';
$page_title = $is_edit ? __('Edit Record #' . $record['id'], 'starter') : __('Add New Record', 'starter');
$page_desc = $is_edit ? __('Update existing database record details.', 'starter') : __('Create and insert a new record into the database.', 'starter');

$error = isset($_GET['error']) ? sanitize_text_field($_GET['error']) : '';
?>

<div class="wrap starter-wrap">
    <!-- Header -->
    <div class="starter-header">
        <div class="starter-header-left">
            <h1>
                <span class="dashicons <?php echo $is_edit ? 'dashicons-edit' : 'dashicons-plus-alt2'; ?>" style="font-size: 28px; width: 28px; height: 28px; color: var(--starter-primary);"></span>
                <?php echo esc_html($page_title); ?>
            </h1>
            <p><?php echo esc_html($page_desc); ?></p>
        </div>
        <div class="starter-header-right">
            <a href="<?php echo esc_url(admin_url('admin.php?page=starter-plugin')); ?>" class="starter-btn starter-btn-secondary">
                <span class="dashicons dashicons-arrow-left-alt" style="margin-right: 4px; font-size: 16px;"></span>
                <?php esc_html_e('Back to All Records', 'starter'); ?>
            </a>
        </div>
    </div>

    <!-- Error notice -->
    <?php if (!empty($error)): ?>
        <div class="starter-alert starter-alert-error">
            <span class="dashicons dashicons-warning"></span>
            <?php echo esc_html($error); ?>
        </div>
    <?php endif; ?>

    <!-- Form Card -->
    <div class="starter-card">
        <div class="starter-card-body">
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" id="starter-record-form">
                <input type="hidden" name="action" value="<?php echo esc_attr($form_action); ?>" />
                <?php wp_nonce_field('starter_record_action_nonce', 'starter_record_nonce'); ?>

                <?php if ($is_edit): ?>
                    <input type="hidden" name="record_id" value="<?php echo esc_attr($record['id']); ?>" />
                <?php endif; ?>

                <!-- Name Field -->
                <div class="starter-form-group">
                    <label for="record_name">
                        <?php esc_html_e('Record Name', 'starter'); ?> <span class="required">*</span>
                    </label>
                    <input type="text" 
                           id="record_name" 
                           name="name" 
                           class="starter-form-control" 
                           required 
                           placeholder="<?php esc_attr_e('Enter record title or name', 'starter'); ?>"
                           value="<?php echo esc_attr($record['name'] ?? ''); ?>" />
                    <span class="starter-help-text"><?php esc_html_e('A descriptive name or title for this entry.', 'starter'); ?></span>
                </div>

                <!-- Email Field -->
                <div class="starter-form-group">
                    <label for="record_email">
                        <?php esc_html_e('Contact Email', 'starter'); ?>
                    </label>
                    <input type="email" 
                           id="record_email" 
                           name="email" 
                           class="starter-form-control" 
                           placeholder="<?php esc_attr_e('e.g. user@example.com', 'starter'); ?>"
                           value="<?php echo esc_attr($record['email'] ?? ''); ?>" />
                    <span class="starter-help-text"><?php esc_html_e('Optional email address associated with this record.', 'starter'); ?></span>
                </div>

                <!-- Status Field -->
                <div class="starter-form-group">
                    <label for="record_status">
                        <?php esc_html_e('Status', 'starter'); ?>
                    </label>
                    <?php $current_status = $record['status'] ?? 'active'; ?>
                    <select id="record_status" name="status" class="starter-form-control" style="max-width: 240px;">
                        <option value="active" <?php selected($current_status, 'active'); ?>><?php esc_html_e('Active', 'starter'); ?></option>
                        <option value="inactive" <?php selected($current_status, 'inactive'); ?>><?php esc_html_e('Inactive', 'starter'); ?></option>
                        <option value="draft" <?php selected($current_status, 'draft'); ?>><?php esc_html_e('Draft', 'starter'); ?></option>
                    </select>
                    <span class="starter-help-text"><?php esc_html_e('Specify whether this record is active, inactive, or a draft.', 'starter'); ?></span>
                </div>

                <!-- Description Field -->
                <div class="starter-form-group">
                    <label for="record_description">
                        <?php esc_html_e('Description / Details', 'starter'); ?> <span class="required">*</span>
                    </label>
                    <textarea id="record_description" 
                              name="description" 
                              class="starter-form-control" 
                              required 
                              rows="6" 
                              placeholder="<?php esc_attr_e('Enter full description or notes...', 'starter'); ?>"><?php echo esc_textarea($record['description'] ?? ''); ?></textarea>
                    <span class="starter-help-text"><?php esc_html_e('Detailed content or metadata for this record.', 'starter'); ?></span>
                </div>

                <!-- Form Action Buttons -->
                <div class="starter-form-actions">
                    <button type="submit" class="starter-btn starter-btn-primary">
                        <span class="dashicons dashicons-saved" style="margin-right: 4px;"></span>
                        <?php echo $is_edit ? esc_html__('Update Record', 'starter') : esc_html__('Save Record', 'starter'); ?>
                    </button>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=starter-plugin')); ?>" class="starter-btn starter-btn-secondary">
                        <?php esc_html_e('Cancel', 'starter'); ?>
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
