<?php
namespace STARTER\Inc\Contracts;

interface Service_Interface {
    /**
     * Register service hooks and listeners with WordPress.
     *
     * @return void
     */
    public function register(): void;
}
