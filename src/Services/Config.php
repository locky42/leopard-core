<?php

namespace Leopard\Core\Services;

use Symfony\Component\Yaml\Yaml;

/**
 * Config Service
 *
 * This service is responsible for loading and managing configuration data
 * from YAML files. It provides methods to retrieve configuration values
 * by keys and supports caching for improved performance.
 */
class Config implements SettingInterface
{
    /**
     * @var array The configuration data loaded from YAML files.
     */
    private array $config = [];

    /**
     * Loads a YAML configuration file.
     *
     * @param string $filePath The path to the YAML configuration file.
     * @return void
     * @throws \RuntimeException If the file cannot be loaded or parsed.
     */
    public function load(string $filePath): void
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("Configuration file not found: $filePath");
        }

        $this->config = array_merge($this->config, Yaml::parseFile($filePath));
    }

    /**
     * Retrieves a configuration value by key.
     *
     * @param string $key The configuration key (e.g., 'database.host').
     * @param mixed $default The default value to return if the key is not found.
     * @return mixed The configuration value or the default value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Sets a configuration value by key.
     *
     * @param string $key The configuration key (e.g., 'database.host').
     * @param mixed $value The value to set.
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $config = &$this->config;

        foreach ($keys as $k) {
            if (!isset($config[$k])) {
                $config[$k] = [];
            }
            $config = &$config[$k];
        }

        $config = $value;
    }

    /**
     * Checks if a configuration key exists.
     *
     * @param string $key The configuration key (e.g., 'database.host').
     * @return bool True if the key exists, false otherwise.
     */
    public function has(string $key): bool
    {
        $keys = explode('.', $key);
        $value = $this->config;
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return false;
            }
            $value = $value[$k];
        }
        return true;
    }

    /**
     * Retrieves all configuration values.
     *
     * @return array The complete configuration array.
     */
    public function getAll(): array
    {
        return $this->config;
    }
}
