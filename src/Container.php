<?php

namespace App\Core;

use Psr\Container\ContainerInterface;

/**
 * A simple dependency injection container implementation.
 * 
 * This class allows you to register service definitions and retrieve instances
 * of those services. It supports lazy instantiation and resolves dependencies
 * when requested.
 * 
 * @implements ContainerInterface
 */
class Container implements ContainerInterface
{
    /**
     * @var array The instances of services that have been created.
     */
    private array $instances = [];

    /**
     * @var array The service definitions, where the key is the service identifier
     * and the value is a callable that returns the service instance.
     */
    private array $definitions = [];

    /**
     * Registers a service definition in the container.
     *
     * @param string $id The unique identifier for the service.
     * @param callable $definition A callable that defines how to create the service.
     *
     * @return void
     */
    public function set(string $id, callable $definition): void
    {
        $this->definitions[$id] = $definition;
        
        if (isset($this->instances[$id])) {
            $this->instances[$id] = ($this->definitions[$id])($this);
        }
    }

    /**
     * Retrieves an object from the container by its identifier.
     *
     * @param string $id The unique identifier of the object to retrieve.
     * @return object The object associated with the given identifier.
     * @throws \Psr\Container\NotFoundExceptionInterface If no entry is found for the given identifier.
     * @throws \Psr\Container\ContainerExceptionInterface If an error occurs while retrieving the entry.
     */
    public function get(string $id): object
    {
        if (!isset($this->instances[$id])) {
            if (isset($this->definitions[$id])) {
                $this->instances[$id] = ($this->definitions[$id])($this);
            } else {
                if (!class_exists($id)) {
                    throw new \RuntimeException("Class $id does not exist.");
                }
                $this->instances[$id] = new $id();
            }
        }
        return $this->instances[$id];
    }

    /**
     * Determines whether the container has an entry for the given identifier.
     *
     * @param string $id The identifier of the entry to check.
     * @return bool True if the container has the entry, false otherwise.
     */
    public function has(string $id): bool
    {
        return isset($this->definitions[$id]) || class_exists($id);
    }
}
