<?php
namespace STARTER\Inc\Services\Database;

class Starter_DB {

    /**
     * Database table name.
     *
     * @var string
     */
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'starter_records';
    }

    /**
     * Get table name.
     *
     * @return string
     */
    public function get_table_name(): string {
        return $this->table_name;
    }

    /**
     * Create or update database table schema.
     *
     * @return void
     */
    public function create_table(): void {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$this->table_name} (
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

        update_option('starter_db_version', STARTER_VERSION);
    }

    /**
     * Check if table exists in database.
     *
     * @return bool
     */
    public function table_exists(): bool {
        global $wpdb;
        $query = $wpdb->prepare("SHOW TABLES LIKE %s", $this->table_name);
        return $wpdb->get_var($query) === $this->table_name;
    }

    /**
     * Insert a new record into database.
     *
     * @param array $data
     * @return int|false
     */
    public function insert(array $data) {
        global $wpdb;

        $fields = [
            'name'        => sanitize_text_field($data['name'] ?? ''),
            'email'       => sanitize_email($data['email'] ?? ''),
            'status'      => sanitize_text_field($data['status'] ?? 'active'),
            'description' => sanitize_textarea_field($data['description'] ?? ''),
            'created_at'  => current_time('mysql'),
            'updated_at'  => current_time('mysql'),
        ];

        $formats = ['%s', '%s', '%s', '%s', '%s', '%s'];

        $result = $wpdb->insert($this->table_name, $fields, $formats);

        if ($result !== false) {
            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Retrieve a single record by ID.
     *
     * @param int $id
     * @return array|null
     */
    public function get_by_id(int $id): ?array {
        global $wpdb;

        $query = $wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id);
        $result = $wpdb->get_row($query, ARRAY_A);

        return $result ?: null;
    }

    /**
     * Retrieve records with search, filter, and pagination.
     *
     * @param array $args
     * @return array
     */
    public function get_all(array $args = []): array {
        global $wpdb;

        $defaults = [
            'search'   => '',
            'status'   => '',
            'orderby'  => 'id',
            'order'    => 'DESC',
            'per_page' => 10,
            'page'     => 1,
        ];

        $args = wp_parse_args($args, $defaults);

        $where = [];
        $params = [];

        if (!empty($args['search'])) {
            $like = '%' . $wpdb->esc_like(trim($args['search'])) . '%';
            $where[] = '(name LIKE %s OR email LIKE %s OR description LIKE %s)';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if (!empty($args['status'])) {
            $where[] = 'status = %s';
            $params[] = sanitize_text_field($args['status']);
        }

        $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Whitelist allowed orderby columns
        $allowed_orderby = ['id', 'name', 'email', 'status', 'created_at'];
        $orderby = in_array($args['orderby'], $allowed_orderby, true) ? $args['orderby'] : 'id';

        // Whitelist allowed order
        $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';

        // Pagination calculations
        $per_page = max(1, (int) $args['per_page']);
        $page     = max(1, (int) $args['page']);
        $offset   = ($page - 1) * $per_page;

        $sql = "SELECT * FROM {$this->table_name} {$where_clause} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
        $params[] = $per_page;
        $params[] = $offset;

        $prepared = $wpdb->prepare($sql, $params);
        $results = $wpdb->get_results($prepared, ARRAY_A);

        return $results ?: [];
    }

    /**
     * Count records matching search and status filter.
     *
     * @param array $args
     * @return int
     */
    public function count(array $args = []): int {
        global $wpdb;

        $where = [];
        $params = [];

        if (!empty($args['search'])) {
            $like = '%' . $wpdb->esc_like(trim($args['search'])) . '%';
            $where[] = '(name LIKE %s OR email LIKE %s OR description LIKE %s)';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if (!empty($args['status'])) {
            $where[] = 'status = %s';
            $params[] = sanitize_text_field($args['status']);
        }

        $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        if (!empty($params)) {
            $query = $wpdb->prepare("SELECT COUNT(*) FROM {$this->table_name} {$where_clause}", $params);
        } else {
            $query = "SELECT COUNT(*) FROM {$this->table_name} {$where_clause}";
        }

        return (int) $wpdb->get_var($query);
    }

    /**
     * Update an existing record.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool {
        global $wpdb;

        $fields = [
            'name'        => sanitize_text_field($data['name'] ?? ''),
            'email'       => sanitize_email($data['email'] ?? ''),
            'status'      => sanitize_text_field($data['status'] ?? 'active'),
            'description' => sanitize_textarea_field($data['description'] ?? ''),
            'updated_at'  => current_time('mysql'),
        ];

        $formats = ['%s', '%s', '%s', '%s', '%s'];

        $result = $wpdb->update(
            $this->table_name,
            $fields,
            ['id' => $id],
            $formats,
            ['%d']
        );

        return $result !== false;
    }

    /**
     * Delete a single record by ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        global $wpdb;

        $result = $wpdb->delete(
            $this->table_name,
            ['id' => $id],
            ['%d']
        );

        return $result !== false;
    }

    /**
     * Bulk delete records by IDs.
     *
     * @param array $ids
     * @return int Number of rows deleted.
     */
    public function bulk_delete(array $ids): int {
        global $wpdb;

        $ids = array_filter(array_map('absint', $ids));
        if (empty($ids)) {
            return 0;
        }

        $placeholders = implode(', ', array_fill(0, count($ids), '%d'));
        $sql = "DELETE FROM {$this->table_name} WHERE id IN ($placeholders)";

        $deleted = $wpdb->query($wpdb->prepare($sql, $ids));

        return (int) $deleted;
    }
}
