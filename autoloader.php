<?php
spl_autoload_register(function($class_name) {
    $namespace_prefix = 'STARTER\\';
    $base_dir = plugin_dir_path(__FILE__) . '';

    $len = strlen($namespace_prefix);
    if (strncmp($namespace_prefix, $class_name, $len) !== 0) {
        return;
    }

    $relative_class = substr($class_name, $len);

    $file = strtolower(str_replace('_', '-', $relative_class));

    $paths = explode("\\", $file);

    $updated_file = implode("/", $paths);

    $updated_file = $base_dir . str_replace('\\', '/', $updated_file) . '.php';

    if (file_exists($updated_file)) {
        require_once $updated_file;
    }
});
