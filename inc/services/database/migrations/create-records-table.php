<?php
namespace STARTER\Inc\Services\Database\Migrations;

use STARTER\Inc\Contracts\Migration_Interface;

class Create_Records_Table implements Migration_Interface {

    /**
     * Target schema version.
     *
     * @return string
     */
    public function get_version(): string {
        return '1.0.0';
    }

    /**
     * Migration description.
     *
     * @return string
     */
    public function get_description(): string {
        return 'Create starter_records table with initial schema (id, name, email, status, description, created_at, updated_at).';
    }

    /**
     * Run the schema upgrade.
     *
     * @return bool
     */
    public function up(): bool {
        global $wpdb;

        $table_name = $wpdb->prefix . 'starter_records';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            email varchar(255) DEFAULT '' NOT NULL,
            status varchar(50) DEFAULT 'active' NOT NULL,
            description text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY status (status)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Verify table was created
        $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) === $table_name;

        return (bool) $table_exists;
    }

    /**
     * Rollback the schema migration.
     *
     * @return bool
     */
    public function down(): bool {
        global $wpdb;

        $table_name = $wpdb->prefix . 'starter_records';
        $wpdb->query("DROP TABLE IF EXISTS {$table_name}");

        return true;
    }
}
