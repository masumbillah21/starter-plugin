<?php
namespace STARTER\Inc\Services;

use STARTER\Inc\Contracts\Service_Interface;
use STARTER\Inc\Services\Container;
use STARTER\Inc\Services\Database\Starter_DB;
use STARTER\Inc\Services\Admin\Action_Links;
use STARTER\Inc\Services\Admin\Admin_Menu;
use STARTER\Inc\Services\Crud\Crud_Handler;
use STARTER\Inc\Services\Test\Details;

class Service_Init extends Container {

    /**
     * Singleton instance of Service_Init.
     *
     * @var Service_Init|null
     */
    private static $instance = null;

    /**
     * Array of booted service instances.
     *
     * @var array
     */
    protected $booted_services = [];

    public function __construct() {
        self::$instance = $this;

        // Auto-register container itself
        $this->instance(Container::class, $this);
        $this->instance(self::class, $this);

        $this->boot_services();
    }

    /**
     * Get singleton instance of the service container.
     *
     * @return Service_Init
     */
    public static function get_instance(): Service_Init {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * List of core services to auto-bind and initialize.
     *
     * @return array
     */
    protected function get_services(): array {
        return [
            Starter_DB::class,
            Action_Links::class,
            Admin_Menu::class,
            Crud_Handler::class,
            Details::class,
        ];
    }

    /**
     * Auto-bind and boot all declared services.
     *
     * @return void
     */
    protected function boot_services(): void {
        foreach ($this->get_services() as $service_class) {
            $this->auto_bind($service_class, true);
        }
    }

    /**
     * Auto-bind a service class, resolve dependencies, and register hooks if applicable.
     *
     * @param string $service_class
     * @param bool $as_singleton
     * @return object
     */
    public function auto_bind(string $service_class, bool $as_singleton = true): object {
        // If not already registered, bind as singleton or transient
        if (!isset($this->shared[$service_class]) && !isset($this->instances[$service_class])) {
            $this->bind($service_class, null, $as_singleton);
        }

        // Resolve service instance via auto-wiring reflection
        $service = $this->resolve($service_class);

        // If service implements Service_Interface, auto-register its WordPress hooks
        if ($service instanceof Service_Interface && !isset($this->booted_services[$service_class])) {
            $service->register();
            $this->booted_services[$service_class] = $service;
        }

        return $service;
    }
}