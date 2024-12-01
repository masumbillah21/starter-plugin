<?php
namespace STARTER\Inc\Services\Database;

class Starter_DB {

    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'starter'; 
    }

    public function create_table() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE {$this->table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function insert($name, $description) {
        global $wpdb;

        $wpdb->insert(
            $this->table_name,
            array(
                'name' => $name,
                'description' => $description,
                'created_at' => current_time('mysql')
            )
        );
    }

    public function get_all() {
        global $wpdb;

        $results = $wpdb->get_results("SELECT * FROM {$this->table_name}", ARRAY_A);
        return $results;
    }

    public function get_by_id($id) {
        global $wpdb;

        $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id), ARRAY_A);
        return $result;
    }

    public function update($id, $name, $description) {
        global $wpdb;
        $wpdb->update(
            $this->table_name,
            array(
                'name' => $name,
                'description' => $description,
                'created_at' => current_time('mysql') // Optional, update timestamp
            ),
            array('id' => $id)
        );
    }
    public function delete($id) {
        global $wpdb;
        $wpdb->delete($this->table_name, array('id' => $id));
    }
}
?>
