<?php
/**
 * Records List Screen Template
 *
 * @var array $records
 * @var int $total_records
 * @var int $current_page
 * @var int $total_pages
 * @var string $search
 * @var string $current_status
 * @var array $status_counts
 */

defined('ABSPATH') || exit;

// Retrieve flash message from query args if present
$message = isset($_GET['message']) ? sanitize_text_field($_GET['message']) : '';
$error   = isset($_GET['error']) ? sanitize_text_field($_GET['error']) : '';
?>

<div class="wrap starter-wrap">
    <!-- Header -->
    <div class="starter-header">
        <div class="starter-header-left">
            <h1>
                <span class="dashicons dashicons-database" style="font-size: 28px; width: 28px; height: 28px; color: var(--starter-primary);"></span>
                <?php esc_html_e('Starter Records', 'starter'); ?>
                <span class="starter-version-badge">v<?php echo esc_html(STARTER_VERSION); ?></span>
            </h1>
            <p><?php esc_html_e('Manage, view, search, and edit database records with full CRUD operations.', 'starter'); ?></p>
        </div>
        <div class="starter-header-right">
            <a href="<?php echo esc_url(admin_url('admin.php?page=starter-plugin-add')); ?>" class="starter-btn starter-btn-primary">
                <span class="dashicons dashicons-plus-alt2" style="margin-right: 4px; font-size: 16px; line-height: 1.2;"></span>
                <?php esc_html_e('Add New Record', 'starter'); ?>
            </a>
        </div>
    </div>

    <!-- Feedback Notices -->
    <?php if ($message === 'created'): ?>
        <div class="starter-alert starter-alert-success">
            <span class="dashicons dashicons-yes-alt"></span>
            <?php esc_html_e('Record created successfully!', 'starter'); ?>
        </div>
    <?php elseif ($message === 'updated'): ?>
        <div class="starter-alert starter-alert-success">
            <span class="dashicons dashicons-yes-alt"></span>
            <?php esc_html_e('Record updated successfully!', 'starter'); ?>
        </div>
    <?php elseif ($message === 'deleted'): ?>
        <div class="starter-alert starter-alert-success">
            <span class="dashicons dashicons-yes-alt"></span>
            <?php esc_html_e('Record deleted successfully!', 'starter'); ?>
        </div>
    <?php elseif (!empty($error)): ?>
        <div class="starter-alert starter-alert-error">
            <span class="dashicons dashicons-warning"></span>
            <?php echo esc_html($error); ?>
        </div>
    <?php endif; ?>

    <!-- Main Card -->
    <div class="starter-card">
        <!-- Toolbar & Filter Bar -->
        <div class="starter-toolbar">
            <!-- Status Filters -->
            <div class="starter-filters">
                <a href="<?php echo esc_url(remove_query_arg(['status', 'paged'])); ?>" 
                   class="starter-filter-link <?php echo empty($current_status) ? 'active' : ''; ?>">
                    <?php esc_html_e('All', 'starter'); ?> 
                    <span>(<?php echo esc_html($status_counts['all'] ?? 0); ?>)</span>
                </a>
                <a href="<?php echo esc_url(add_query_arg(['status' => 'active', 'paged' => 1])); ?>" 
                   class="starter-filter-link <?php echo $current_status === 'active' ? 'active' : ''; ?>">
                    <?php esc_html_e('Active', 'starter'); ?> 
                    <span>(<?php echo esc_html($status_counts['active'] ?? 0); ?>)</span>
                </a>
                <a href="<?php echo esc_url(add_query_arg(['status' => 'inactive', 'paged' => 1])); ?>" 
                   class="starter-filter-link <?php echo $current_status === 'inactive' ? 'active' : ''; ?>">
                    <?php esc_html_e('Inactive', 'starter'); ?> 
                    <span>(<?php echo esc_html($status_counts['inactive'] ?? 0); ?>)</span>
                </a>
                <a href="<?php echo esc_url(add_query_arg(['status' => 'draft', 'paged' => 1])); ?>" 
                   class="starter-filter-link <?php echo $current_status === 'draft' ? 'active' : ''; ?>">
                    <?php esc_html_e('Draft', 'starter'); ?> 
                    <span>(<?php echo esc_html($status_counts['draft'] ?? 0); ?>)</span>
                </a>
            </div>

            <!-- Search Form -->
            <form method="get" class="starter-search-form">
                <input type="hidden" name="page" value="starter-plugin" />
                <?php if (!empty($current_status)): ?>
                    <input type="hidden" name="status" value="<?php echo esc_attr($current_status); ?>" />
                <?php endif; ?>
                <input type="search" 
                       name="s" 
                       class="starter-search-input" 
                       placeholder="<?php esc_attr_e('Search by name, email...', 'starter'); ?>" 
                       value="<?php echo esc_attr($search); ?>" />
                <button type="submit" class="starter-btn starter-btn-secondary">
                    <span class="dashicons dashicons-search" style="font-size: 16px; margin-right: 2px;"></span>
                    <?php esc_html_e('Search', 'starter'); ?>
                </button>
                <?php if (!empty($search)): ?>
                    <a href="<?php echo esc_url(remove_query_arg(['s', 'paged'])); ?>" class="starter-btn starter-btn-secondary" title="<?php esc_attr_e('Reset Search', 'starter'); ?>">
                        <span class="dashicons dashicons-dismiss" style="font-size: 16px;"></span>
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Table View -->
        <?php if (!empty($records)): ?>
            <div class="starter-table-responsive">
                <table class="starter-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;"><?php esc_html_e('ID', 'starter'); ?></th>
                            <th style="width: 200px;"><?php esc_html_e('Name', 'starter'); ?></th>
                            <th style="width: 200px;"><?php esc_html_e('Email', 'starter'); ?></th>
                            <th style="width: 100px;"><?php esc_html_e('Status', 'starter'); ?></th>
                            <th><?php esc_html_e('Description', 'starter'); ?></th>
                            <th style="width: 150px;"><?php esc_html_e('Created At', 'starter'); ?></th>
                            <th style="width: 140px; text-align: right;"><?php esc_html_e('Actions', 'starter'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $item): ?>
                            <tr>
                                <td><strong>#<?php echo esc_html($item['id']); ?></strong></td>
                                <td>
                                    <strong>
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=starter-plugin-add&action=edit&id=' . absint($item['id']))); ?>" style="text-decoration: none; color: var(--starter-text-dark);">
                                            <?php echo esc_html($item['name']); ?>
                                        </a>
                                    </strong>
                                </td>
                                <td>
                                    <?php if (!empty($item['email'])): ?>
                                        <a href="mailto:<?php echo esc_attr($item['email']); ?>" style="color: var(--starter-primary); text-decoration: none;">
                                            <?php echo esc_html($item['email']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span style="color: var(--starter-text-muted);">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $status = strtolower($item['status'] ?? 'active');
                                    $badge_class = 'starter-badge-inactive';
                                    if ($status === 'active') $badge_class = 'starter-badge-active';
                                    if ($status === 'draft') $badge_class = 'starter-badge-draft';
                                    ?>
                                    <span class="starter-badge <?php echo esc_attr($badge_class); ?>">
                                        <?php echo esc_html(ucfirst($status)); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo esc_html(wp_trim_words($item['description'], 12, '...')); ?>
                                </td>
                                <td style="color: var(--starter-text-muted); font-size: 13px;">
                                    <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($item['created_at']))); ?>
                                </td>
                                <td style="text-align: right;">
                                    <div class="starter-action-group" style="justify-content: flex-end;">
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=starter-plugin-add&action=edit&id=' . absint($item['id']))); ?>" 
                                           class="starter-btn starter-btn-secondary starter-btn-sm" 
                                           title="<?php esc_attr_e('Edit Record', 'starter'); ?>">
                                            <span class="dashicons dashicons-edit" style="font-size: 14px; margin-right: 2px;"></span>
                                            <?php esc_html_e('Edit', 'starter'); ?>
                                        </a>
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=starter_delete_record&id=' . absint($item['id'])), 'starter_delete_nonce')); ?>" 
                                           class="starter-btn starter-btn-danger starter-btn-sm" 
                                           onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete this record? This action cannot be undone.', 'starter')); ?>');" 
                                           title="<?php esc_attr_e('Delete Record', 'starter'); ?>">
                                            <span class="dashicons dashicons-trash" style="font-size: 14px; margin-right: 2px;"></span>
                                            <?php esc_html_e('Delete', 'starter'); ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <?php if ($total_pages > 1): ?>
                <div class="starter-pagination">
                    <div>
                        <?php 
                        printf(
                            esc_html__('Showing page %1$d of %2$d (%3$d total records)', 'starter'),
                            $current_page,
                            $total_pages,
                            $total_records
                        ); 
                        ?>
                    </div>
                    <div class="starter-page-links">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="<?php echo esc_url(add_query_arg(['paged' => $i])); ?>" 
                               class="starter-page-link <?php echo $i === $current_page ? 'current' : ''; ?>">
                                <?php echo esc_html($i); ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- Empty State -->
            <div class="starter-empty-state">
                <span class="dashicons dashicons-database"></span>
                <h3><?php esc_html_e('No records found', 'starter'); ?></h3>
                <p><?php esc_html_e('There are currently no records matching your criteria. Create your first record now!', 'starter'); ?></p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=starter-plugin-add')); ?>" class="starter-btn starter-btn-primary">
                    <span class="dashicons dashicons-plus-alt2" style="margin-right: 4px;"></span>
                    <?php esc_html_e('Create First Record', 'starter'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
