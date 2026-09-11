# WordPress Starter Plugin Boilerplate

A modern, object-oriented WordPress Starter Plugin boilerplate featuring:
- **Dependency Injection Container with Auto-Wiring & Auto-Binding** (Reflection-based automatic constructor resolution)
- **Service-Oriented Architecture** with auto-registration (`Service_Interface`)
- **PSR-like WordPress Autoloader** (`STARTER\Inc\Services\My_Service` &rarr; `inc/services/my-service.php`)
- **Plugin Action Links & Row Meta** ("Settings", "All Records", "Add New", Docs, Support on `plugins.php`)
- **Screen Admin Menu Pages** with modern, responsive dashboard UI
- **Complete Database CRUD System** with schema migrations (`dbDelta`), pagination, search, status filters, nonces, and input sanitization

---

## 📁 Directory Structure

```text
starter-plugin/
├── assets/
│   ├── css/
│   │   ├── admin-style.css       # Modern CSS design system for admin screens
│   │   └── style.css             # Frontend styles
│   └── js/
│       └── script.js             # Frontend scripts
├── inc/
│   ├── contracts/
│   │   ├── migration-interface.php # Migration_Interface (version, up, down contract)
│   │   └── service-interface.php   # Service_Interface (register() lifecycle hook)
│   ├── services/
│   │   ├── admin/
│   │   │   ├── action-links.php    # Plugins.php action & meta links
│   │   │   └── admin-menu.php      # WP Admin menu & page routing (injects Starter_DB & Migration_Manager)
│   │   ├── crud/
│   │   │   └── crud-handler.php    # CRUD request processing & validation
│   │   ├── database/
│   │   │   ├── migrations/
│   │   │   │   ├── create-records-table.php # Initial schema migration (v1.0.0)
│   │   │   │   └── add-priority-column.php  # Schema evolution migration (v1.1.0)
│   │   │   ├── migration-manager.php        # Schema versioning & migration runner
│   │   │   └── starter-db.php               # Database CRUD model (injects Migration_Manager)
│   │   ├── container.php           # DI Container with Auto-Wiring
│   │   └── service-init.php        # Service Bootstrapper & Auto-Binding
│   └── starter-init.php            # Plugin lifecycle (activate, deactivate, hooks)
├── templates/
│   └── admin/
│       ├── record-form.php         # Add/Edit record card form
│       ├── records-list.php        # Data table, search, status filters, pagination
│       └── settings.php            # Settings page (Preferences & Migration UI)
├── autoloader.php                  # Class autoloader
├── index.php                       # Plugin bootstrap entry point
└── README.md                       # Documentation
```

---

## 🚀 How to Use This Boilerplate for a New Plugin

Follow these steps to customize this starter boilerplate for your own plugin.

### Step 1: Copy & Rename the Plugin Folder
Copy `starter-plugin` to `wp-content/plugins/your-plugin-name`:
```bash
cp -r starter-plugin your-plugin-name
```

---

### Step 2: Update Plugin Header and Constants in `index.php`
Open `index.php` and update the plugin metadata and constant prefixes:

```php
/*
 * Plugin Name:       My Awesome Plugin
 * Plugin URI:        https://example.com
 * Description:       My custom WordPress plugin description.
 * Version:           1.0.0
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Author:            Your Name
 * Author URI:        https://example.com
 * Text Domain:       my-plugin
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/autoloader.php';

// Change constant prefixes to match your plugin slug
if ( ! defined('MY_PLUGIN_VERSION') )    define( 'MY_PLUGIN_VERSION', '1.0.0' );
if ( ! defined('MY_PLUGIN_DB_VERSION') ) define( 'MY_PLUGIN_DB_VERSION', '1.1.0' );
if ( ! defined('MY_PLUGIN_DIR_PATH') )   define( 'MY_PLUGIN_DIR_PATH', plugin_dir_path(__FILE__) );
if ( ! defined('MY_PLUGIN_PATH_URL') )   define( 'MY_PLUGIN_PATH_URL', plugin_dir_url(__FILE__) );

use MY_PLUGIN\Inc\Starter_Init;
use MY_PLUGIN\Inc\Services\Service_Init;

function my_plugin_init() {
    require_once __DIR__ . '/inc/starter-init.php';
    new Starter_Init();
}
add_action( 'plugins_loaded', 'my_plugin_init' );

function my_plugin_container(): Service_Init {
    return Service_Init::get_instance();
}

register_activation_hook( __FILE__, [ 'MY_PLUGIN\Inc\Starter_Init', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'MY_PLUGIN\Inc\Starter_Init', 'deactivate' ] );
register_uninstall_hook( __FILE__, [ 'MY_PLUGIN\Inc\Starter_Init', 'uninstall' ] );
```

---

### Step 3: Update Namespaces & Autoloader

1. Open `autoloader.php` and update the namespace prefix:
   ```php
   $namespace_prefix = 'MY_PLUGIN\\';
   ```

2. Search and replace `STARTER\` with `MY_PLUGIN\` across all files in `inc/`.

> **Autoloader Rule**: The autoloader automatically maps:
> `MY_PLUGIN\Inc\Services\Emails\Email_Notification` &rarr; `inc/services/emails/email-notification.php`.
> Namespaces match folder paths (lowercase) and underscores in class names become hyphens.

---

### Step 4: Creating New Services with Auto-Binding

All services can implement `STARTER\Inc\Contracts\Service_Interface` which defines the `register(): void` method.

#### 1. Create your service class:
Create `inc/services/emails/email-service.php`:

```php
<?php
namespace MY_PLUGIN\Inc\Services\Emails;

use MY_PLUGIN\Inc\Contracts\Service_Interface;
use MY_PLUGIN\Inc\Services\Database\Starter_DB;

class Email_Service implements Service_Interface {

    /**
     * Dependencies are automatically resolved and injected by the container!
     */
    public function __construct(private Starter_DB $db) {
    }

    public function register(): void {
        add_action('user_register', [$this, 'send_welcome_email']);
    }

    public function send_welcome_email(int $user_id): void {
        $user = get_userdata($user_id);
        // Custom logic...
    }
}
```

#### 2. Register your service in `Service_Init`:
Open `inc/services/service-init.php` and add your class to `get_services()`:

```php
protected function get_services(): array {
    return [
        Starter_DB::class,
        Action_Links::class,
        Admin_Menu::class,
        Crud_Handler::class,
        Email_Service::class, // <-- Your new service is auto-bound and registered!
    ];
}
```

That's it! The container will:
1. Automatically inspect `Email_Service` constructor.
2. Auto-wire its dependency `Starter_DB`.
3. Instantiate `Email_Service`.
4. Call `register()` to hook its actions/filters into WordPress.

---

### Step 5: On-the-Fly Dynamic Auto-Binding

You can also resolve or auto-bind any class dynamically anywhere in your code without registering it beforehand:

```php
// Resolves class and all its dependencies recursively via reflection
$email_service = starter_container()->resolve(My_Service::class);

// Or auto-bind and register hooks if it implements Service_Interface
starter_container()->auto_bind(My_Service::class);
```

---

### Step 6: Database Migrations & Versioning System

The plugin includes an enterprise-grade schema migration manager that tracks database versions, applies incremental migrations in chronological order, and enforces strict Constructor Dependency Injection.

#### 1. How Migrations Work
- **Installed Version**: Stored in `wp_options` under `starter_db_version`.
- **Target Version & Auto-Detection**: Defined in `index.php` as `STARTER_DB_VERSION` (e.g. `'1.1.0'`). `Migration_Manager::get_target_version()` automatically detects the highest version among registered migrations, ensuring no pending migration is accidentally ignored.
- **Automatic Execution**: On plugin activation and in the admin lifecycle (`admin_init`), `Migration_Manager` checks if `version_compare(installed, target, '<')` and executes pending migrations in ascending version order.
- **Audit History**: Every executed migration is recorded in `starter_migrations_log` with migration name, description, execution timestamp, and status.

#### 2. Strict Dependency Injection
`Migration_Manager` is registered as a service and auto-wired into dependent classes via constructor injection:
- **`Starter_DB`**: Injects `Migration_Manager` to delegate table creation and schema updates:
  ```php
  public function __construct(private Migration_Manager $migration_manager) { ... }
  ```
- **`Admin_Menu`**: Injects `Migration_Manager` to provide version status and execution controls to the admin UI:
  ```php
  public function __construct(private Starter_DB $db, private Migration_Manager $migration_manager) { ... }
  ```

#### 3. Creating a New Migration
To evolve your database schema in a new release (e.g. adding a new column or table for version 1.2.0):

1. Create a migration class in `inc/services/database/migrations/`:
```php
<?php
namespace MY_PLUGIN\Inc\Services\Database\Migrations;

use MY_PLUGIN\Inc\Contracts\Migration_Interface;

class Add_Category_Column implements Migration_Interface {

    public function get_version(): string {
        return '1.2.0';
    }

    public function get_description(): string {
        return 'Add category column and index to records table.';
    }

    public function up(): bool {
        global $wpdb;
        $table_name = $wpdb->prefix . 'starter_records';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            category varchar(100) DEFAULT 'general' NOT NULL,
            email varchar(255) DEFAULT '' NOT NULL,
            status varchar(50) DEFAULT 'active' NOT NULL,
            description text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY category (category)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        return true;
    }

    public function down(): bool {
        global $wpdb;
        $table_name = $wpdb->prefix . 'starter_records';
        $wpdb->query("ALTER TABLE {$table_name} DROP COLUMN category");
        return true;
    }
}
```

2. Register the migration in `inc/services/database/migration-manager.php`:
```php
protected $migration_classes = [
    Create_Records_Table::class,
    Add_Priority_Column::class,
    Add_Category_Column::class, // <-- Added for 1.2.0
];
```

3. Bump the DB version in `index.php`:
```php
define( 'STARTER_DB_VERSION', '1.2.0' );
```
Upon visiting WP Admin or opening the Settings screen, the migration will execute automatically and record the execution history!

#### 4. Settings Screen Migration UI
The plugin provides a dedicated management dashboard on **Starter Plugin &rarr; Settings**:
- **Version Metrics**: Live comparison between Installed DB Version and Target Code Version.
- **Schema Status Badge**: Real-time indicator (`Up to date` or `Migration Required`).
- **Run / Recheck Migrations Now**: Button to manually trigger pending migrations with nonce security.
- **Registered Migrations Table**: Visual status (`Applied` vs `Pending`) for every migration class.
- **Execution History Log**: Chronological audit trail of executed migrations and timestamps.

#### 5. Database Model (`inc/services/database/starter-db.php`)
- Built-in CRUD methods:
  - `$db->insert($data)`: Sanitized record creation.
  - `$db->get_by_id($id)`: Fetches a single record using prepared statements.
  - `$db->get_all($args)`: Fetches records with `search`, `status`, `orderby`, `order`, `per_page`, and `page`.
  - `$db->count($args)`: Counts total matching records.
  - `$db->update($id, $data)`: Updates an existing record.
  - `$db->delete($id)`: Deletes a single record.
  - `$db->bulk_delete($ids)`: Deletes an array of record IDs.

#### 6. CRUD Form Handler (`inc/services/crud/crud-handler.php`)
Handles `admin-post.php` requests with:
- Nonce verification (`check_admin_referer()`)
- Capability checks (`current_user_can('manage_options')`)
- Sanitization (`sanitize_text_field()`, `sanitize_email()`, `sanitize_textarea_field()`)
- Redirects with status notices (`&message=created`, `&message=updated`, `&message=deleted`, `&message=migrated`)

---

### Step 7: Customizing the Admin Menu & Templates

- **Menu Configuration**: Edit `inc/services/admin/admin-menu.php` to change menu title, icon (`dashicons-*`), position, and capability.
- **Templates**:
  - `templates/admin/records-list.php`: Customize table headers, columns, badges, and search fields.
  - `templates/admin/record-form.php`: Customize form inputs for Add/Edit operations.
  - `templates/admin/settings.php`: Customize plugin configuration options.
- **Styling**: `assets/css/admin-style.css` contains a clean, modern design system with CSS custom properties (`--starter-primary`, `--starter-border`, etc.) that you can tailor to your brand colors.

---

### Step 8: Customizing Action Links & Meta Links

Edit `inc/services/admin/action-links.php` to adjust the quick links shown on `wp-admin/plugins.php`:
```php
public function add_action_links(array $links): array {
    $custom_links = [
        '<a href="' . esc_url(admin_url('admin.php?page=my-plugin-settings')) . '">Settings</a>',
        '<a href="' . esc_url(admin_url('admin.php?page=my-plugin')) . '">All Records</a>',
    ];
    return array_merge($custom_links, $links);
}
```

---

## 🔒 Security Best Practices Included

- **Direct file access prevention**: `defined( 'ABSPATH' ) || exit;` in all files.
- **CSRF Protection**: Nonce verification (`wp_nonce_field`, `check_admin_referer`, `wp_verify_nonce`) on all CRUD and settings submissions.
- **Capability Checks**: `current_user_can('manage_options')` before rendering admin views or executing write actions.
- **SQL Injection Prevention**: All queries in `Starter_DB` use `$wpdb->prepare()`.
- **XSS Prevention**: Output escaping using `esc_html()`, `esc_attr()`, `esc_url()`, and `esc_js()`.
- **Input Sanitization**: `sanitize_text_field()`, `sanitize_email()`, `sanitize_textarea_field()`, and `absint()`.

---

## 📄 License
GPL v2 or later. Feel free to use this starter boilerplate for open-source or commercial WordPress projects!
