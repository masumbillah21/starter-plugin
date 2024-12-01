<?php
namespace STARTER\Inc\Services;

use STARTER\Inc\Services\Container;
use STARTER\Inc\Services\Database\Starter_DB;
use STARTER\Inc\Services\Test\Details;

class Service_Init extends Container {
    public function __construct() {
        $this->register();
        $this->create();
        $this->add_details();
    }

    private function register(){
        $this->bind(Starter_DB::class, null, true);
        $this->bind(Details::class);
    }

    private function create(){
        $db = $this->resolve(Starter_DB::class);
        $db->create_table();
    }

    private function add_details(){
        $details = $this->resolve(Details::class);
        $details->add_data();
    }
}