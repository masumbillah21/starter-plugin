<?php
namespace STARTER\Inc\Contracts;

interface Migration_Interface {
    /**
     * Get the version string this migration upgrades to (e.g. '1.0.0').
     *
     * @return string
     */
    public function get_version(): string;

    /**
     * Get a human-readable description of what this migration accomplishes.
     *
     * @return string
     */
    public function get_description(): string;

    /**
     * Execute the migration (up).
     *
     * @return bool
     */
    public function up(): bool;

    /**
     * Rollback the migration (down).
     *
     * @return bool
     */
    public function down(): bool;
}
