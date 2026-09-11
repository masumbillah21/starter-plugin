<?php
namespace STARTER\Inc\Services\Database\Migrations;

use STARTER\Inc\Contracts\Migration_Interface;

class Add_Priority_Column implements Migration_Interface {

    /**
     * Target schema version.
     *
     * @return string
     */
    public function get_version(): string {
        return '1.1.0';
    }

    /**
     * Migration description.
     *
     * @return string
     */
    public function get_description(): string {
        return 'Add priority column and index to starter_records table for sorting.';
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
            priority int(11) DEFAULT 0 NOT NULL,
            description text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY status (status),
            KEY priority (priority)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        return true;
    }

    /**
     * Rollback the schema migration.
     *
     * @return bool
     */
    public function down(): bool {
        global $wpdb;

        $table_name = $wpdb->prefix . 'starter_records';
        $column = $wpdb->get_results($wpdb->prepare("SHOW COLUMNS FROM {$table_name} LIKE %s", 'priority'));

        if (!empty($column)) {
            $wpdb->query("ALTER TABLE {$table_name} DROP COLUMN priority");
        }

        return true;
    }
}
