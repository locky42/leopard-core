<?php

namespace App\Core\Controllers;

use App\Core\Container;

/**
 * Abstract class AbstractController
 *
 * This class serves as a base controller for all controllers in the application.
 * It provides common functionality and properties that can be used by all controllers.
 *
 * @package Core\Controllers
 */
abstract class AbstractController
{
    /**
     * @var Container The container instance used for dependency injection.
     */
    protected Container $container;

    /**
     * Constructor method for the AbstractController class.
     * Initializes the controller and sets up any necessary dependencies or configurations.
     */
    public function __construct()
    {
        $this->container = $GLOBALS['container'] ?? new Container();
    }

    /**
     * Retrieves an instance of the specified object.
     *
     * @param string $instance The name or identifier of the object instance to retrieve.
     * @return object The requested object instance.
     */
    public function get(string $instance): object
    {
        try {
            return $this->container->get($instance);
        } catch (\Exception $e) {
            // Handle the exception or log it as needed
            throw new \RuntimeException("Could not get instance: " . $e->getMessage());
        }
    }
}
