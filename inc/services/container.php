<?php
namespace STARTER\Inc\Services;

use Exception;
use ReflectionClass;
use ReflectionNamedType;

class Container
{
    /**
     * Stored transient bindings.
     *
     * @var array
     */
    protected $instances = [];

    /**
     * Stored singleton / shared definitions and instances.
     *
     * @var array
     */
    protected $shared = [];

    /**
     * Bind a class or interface to a concrete implementation.
     *
     * @param string $abstract
     * @param mixed $concrete
     * @param bool $shared
     * @return void
     */
    public function bind($abstract, $concrete = null, $shared = false)
    {
        if ($concrete === null) {
            $concrete = $abstract;
        }

        if ($shared) {
            $this->shared[$abstract] = [
                'concrete' => $concrete,
                'instance' => null,
            ];
        } else {
            $this->instances[$abstract] = $concrete;
        }
    }

    /**
     * Register a shared singleton in the container.
     *
     * @param string $abstract
     * @param mixed $concrete
     * @return void
     */
    public function singleton($abstract, $concrete = null)
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Register an existing instance as shared.
     *
     * @param string $abstract
     * @param object $instance
     * @return void
     */
    public function instance($abstract, $instance)
    {
        $this->shared[$abstract] = [
            'concrete' => get_class($instance),
            'instance' => $instance,
        ];
    }

    /**
     * Check if a service is bound or auto-resolvable.
     *
     * @param string $abstract
     * @return bool
     */
    public function has($abstract)
    {
        return isset($this->shared[$abstract]) 
            || isset($this->instances[$abstract]) 
            || class_exists($abstract);
    }

    /**
     * PSR-11 style alias for resolve().
     *
     * @param string $abstract
     * @return object
     * @throws Exception
     */
    public function get($abstract)
    {
        return $this->resolve($abstract);
    }

    /**
     * Resolve a service from the container, with auto-binding support.
     *
     * @param string $abstract
     * @return object
     * @throws Exception
     */
    public function resolve($abstract)
    {
        // 1. Shared instance check
        if (isset($this->shared[$abstract])) {
            return $this->getSharedInstance($abstract);
        }

        // 2. Explicit transient binding check
        if (isset($this->instances[$abstract])) {
            $concrete = $this->instances[$abstract];
            return $this->build($concrete);
        }

        // 3. Auto-binding / Auto-wiring: check if class exists and is instantiable
        if (class_exists($abstract)) {
            $reflector = new ReflectionClass($abstract);
            if ($reflector->isInstantiable()) {
                return $this->build($abstract);
            }
        }

        throw new Exception("Class or binding '{$abstract}' cannot be resolved by container.");
    }

    /**
     * Retrieve or instantiate a shared service.
     *
     * @param string $abstract
     * @return object
     */
    protected function getSharedInstance($abstract)
    {
        if (!isset($this->shared[$abstract]['instance']) || $this->shared[$abstract]['instance'] === null) {
            $concrete = $this->shared[$abstract]['concrete'];
            $this->shared[$abstract]['instance'] = $this->build($concrete);
        }

        return $this->shared[$abstract]['instance'];
    }

    /**
     * Instantiate a concrete class with automatic dependency resolution.
     *
     * @param mixed $concrete
     * @return object
     * @throws Exception
     */
    protected function build($concrete)
    {
        // If closure/callable passed as concrete
        if ($concrete instanceof \Closure) {
            return $concrete($this);
        }

        $reflector = new ReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            throw new Exception("Target [{$concrete}] is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        if ($constructor === null) {
            return $reflector->newInstance();
        }

        $parameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $dependencyClass = $type->getName();

                // If class is Container itself, pass this instance
                if ($dependencyClass === self::class || is_subclass_of($this, $dependencyClass)) {
                    $dependencies[] = $this;
                } else {
                    $dependencies[] = $this->resolve($dependencyClass);
                }
            } elseif ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
            } elseif ($parameter->allowsNull()) {
                $dependencies[] = null;
            } else {
                throw new Exception("Cannot resolve un-typed parameter \${$parameter->getName()} for class {$concrete}");
            }
        }

        return $reflector->newInstanceArgs($dependencies);
    }
}
