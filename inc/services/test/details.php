<?php 

namespace STARTER\Inc\Services\Test;

use STARTER\Inc\Services\Database\Starter_DB;

class Details {
    public function __construct(private Starter_DB $db) {
        
    }

    public function add_data(){
        $this->db->insert('Name', 'Description');
    }
}