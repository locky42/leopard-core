<?php

namespace App\Core\Services;

/**
 * Params Service
 *
 * This service provides a simple interface for managing application parameters.
 * It allows loading parameters from PHP files, setting, getting, and checking their existence.
 */
class Params implements SettingInterface
{
    /**
     * @var array The parameters loaded from PHP files.
     */
    private array $params = [];

    /**
     * Loads parameters from a PHP file.
     *
     * @param string $filePath The path to the PHP file returning an array of parameters.
     * @return void
     * @throws \RuntimeException If the file cannot be loaded or does not return an array.
     */
    public function load(string $filePath): void
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("Parameters file not found: $filePath");
        }

        $data = include $filePath;

        if (!is_array($data)) {
            throw new \RuntimeException("Parameters file must return an array: $filePath");
        }

        $this->params = array_merge($this->params, $data);
    }

    /**
     * Sets a parameter value by key.
     *
     * @param string $key The parameter key.
     * @param mixed $value The value to set.
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $this->params[$key] = $value;
    }

    /**
     * Retrieves a parameter value by key.
     *
     * @param string $key The parameter key.
     * @param mixed $default The default value to return if the key is not found.
     * @return mixed The parameter value or the default value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }

    /**
     * Checks if a parameter exists by key.
     *
     * @param string $key The parameter key.
     * @return bool True if the parameter exists, false otherwise.
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->params);
    }
    
    /**
     * Retrieves all parameters.
     *
     * @return array The array of all parameters.
     */
    public function getAll(): array
    {
        return $this->params;
    }
}
