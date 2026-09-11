<?php
namespace STARTER\Inc\Services\Database;

use STARTER\Inc\Contracts\Service_Interface;
use STARTER\Inc\Contracts\Migration_Interface;
use STARTER\Inc\Services\Database\Migrations\Create_Records_Table;
use STARTER\Inc\Services\Database\Migrations\Add_Priority_Column;

class Migration_Manager implements Service_Interface {

    /**
     * Option key for storing installed database schema version.
     */
    const DB_VERSION_OPTION = 'starter_db_version';

    /**
     * Option key for migration execution log.
     */
    const MIGRATIONS_LOG_OPTION = 'starter_migrations_log';

    /**
     * Registered migration class names in order.
     *
     * @var array
     */
    protected $migration_classes = [
        Create_Records_Table::class,
        Add_Priority_Column::class,
    ];

    /**
     * Register hooks for automatic migration checks and admin actions.
     *
     * @return void
     */
    public function register(): void {
        add_action('admin_init', [$this, 'auto_migrate_if_needed']);
        add_action('admin_post_starter_run_migrations', [$this, 'handle_manual_migration']);
    }

    /**
     * Get currently installed schema version from database options.
     *
     * @return string
     */
    public function get_installed_version(): string {
        return get_option(self::DB_VERSION_OPTION, '0.0.0');
    }

    /**
     * Get target database version defined by plugin constant or highest registered migration.
     *
     * @return string
     */
    public function get_target_version(): string {
        $target = defined('STARTER_DB_VERSION') ? STARTER_DB_VERSION : '1.0.0';

        // Automatically account for any registered migration with a higher version
        foreach ($this->get_all_migrations() as $migration) {
            if (version_compare($migration->get_version(), $target, '>')) {
                $target = $migration->get_version();
            }
        }

        return $target;
    }

    /**
     * Check if pending migrations need to be executed.
     *
     * @return bool
     */
    public function needs_migration(): bool {
        return version_compare($this->get_installed_version(), $this->get_target_version(), '<');
    }

    /**
     * Register an additional migration class.
     *
     * @param string $migration_class
     * @return void
     */
    public function add_migration(string $migration_class): void {
        if (!in_array($migration_class, $this->migration_classes, true)) {
            $this->migration_classes[] = $migration_class;
        }
    }

    /**
     * Automatically execute pending migrations in admin context.
     *
     * @return void
     */
    public function auto_migrate_if_needed(): void {
        if ($this->needs_migration() && current_user_can('manage_options')) {
            $this->run_migrations();
        }
    }

    /**
     * Run all pending migrations up to target version.
     *
     * @return array Summary of migration execution.
     */
    public function run_migrations(): array {
        $installed_version = $this->get_installed_version();
        $target_version    = $this->get_target_version();
        $executed          = [];
        $errors            = [];

        // Instantiate and sort migrations by version ASC
        $instances = [];
        foreach ($this->migration_classes as $class) {
            if (class_exists($class)) {
                $migration = new $class();
                if ($migration instanceof Migration_Interface) {
                    $instances[] = $migration;
                }
            }
        }

        usort($instances, function (Migration_Interface $a, Migration_Interface $b) {
            return version_compare($a->get_version(), $b->get_version());
        });

        $log = get_option(self::MIGRATIONS_LOG_OPTION, []);

        foreach ($instances as $migration) {
            $migration_version = $migration->get_version();

            // Execute if migration is newer than installed version and <= target version
            if (version_compare($migration_version, $installed_version, '>') 
                && version_compare($migration_version, $target_version, '<=')) {

                try {
                    $success = $migration->up();

                    if ($success) {
                        $installed_version = $migration_version;
                        update_option(self::DB_VERSION_OPTION, $installed_version);

                        $log_entry = [
                            'version'     => $migration_version,
                            'migration'   => get_class($migration),
                            'description' => $migration->get_description(),
                            'executed_at' => current_time('mysql'),
                            'status'      => 'success',
                        ];

                        $log[] = $log_entry;
                        $executed[] = $log_entry;
                    } else {
                        $errors[] = sprintf("Migration %s (%s) returned false.", get_class($migration), $migration_version);
                        break;
                    }
                } catch (\Throwable $e) {
                    $errors[] = sprintf("Migration %s failed: %s", get_class($migration), $e->getMessage());
                    break;
                }
            }
        }

        update_option(self::MIGRATIONS_LOG_OPTION, $log);

        return [
            'installed_version' => $this->get_installed_version(),
            'target_version'    => $target_version,
            'executed'          => $executed,
            'errors'            => $errors,
        ];
    }

    /**
     * Get complete migration log history.
     *
     * @return array
     */
    public function get_history(): array {
        return get_option(self::MIGRATIONS_LOG_OPTION, []);
    }

    /**
     * Get list of all registered migration instances.
     *
     * @return array
     */
    public function get_all_migrations(): array {
        $instances = [];
        foreach ($this->migration_classes as $class) {
            if (class_exists($class)) {
                $migration = new $class();
                if ($migration instanceof Migration_Interface) {
                    $instances[] = $migration;
                }
            }
        }
        return $instances;
    }

    /**
     * Handle manual migration request from Settings screen.
     *
     * @return void
     */
    public function handle_manual_migration(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Unauthorized.', 'starter'));
        }

        check_admin_referer('starter_migration_nonce_action', 'starter_migration_nonce');

        $result = $this->run_migrations();

        $redirect_url = add_query_arg(
            ['message' => 'migrated'],
            admin_url('admin.php?page=starter-plugin-settings')
        );

        wp_safe_redirect($redirect_url);
        exit;
    }
}
