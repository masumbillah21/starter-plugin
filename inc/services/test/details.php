<?php 

namespace STARTER\Inc\Services\Test;

use STARTER\Inc\Services\Database\Starter_DB;

class Details {
    public function __construct(private Starter_DB $db) {
    }

    public function add_data(string $name = 'Sample Record', string $description = 'Auto-created demo record via service container.') {
        return $this->db->insert([
            'name'        => $name,
            'description' => $description,
            'status'      => 'active',
        ]);
    }
}