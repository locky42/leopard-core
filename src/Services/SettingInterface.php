<?php

namespace App\Core\Services;

/**
 * SettingInterface
 * This interface defines the methods for managing application settings.
 * It allows loading settings from a file, retrieving
 * settings by key, setting values, checking existence,
 * and retrieving all settings.
 */
interface SettingInterface
{
    /**
     * Loads settings from a specified file.
     *
     * @param string $filePath The path to the settings file.
     * @return void
     * @throws \RuntimeException If the file cannot be loaded or parsed.
     */
    public function load(string $filePath): void;

    /**
     * Retrieves a setting value by its key.
     *
     * @param string $key The setting key (e.g., 'app.name').
     * @param mixed $default The default value to return if the key is not found.
     * @return mixed The setting value or the default value.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Sets a setting value by its key.
     *
     * @param string $key The setting key.
     * @param mixed $value The value to set.
     * @return void
     */
    public function set(string $key, mixed $value): void;

    /**
     * Checks if a setting exists by its key.
     *
     * @param string $key The setting key.
     * @return bool True if the setting exists, false otherwise.
     */
    public function has(string $key): bool;

    /**
     * Retrieves all settings as an associative array.
     *
     * @return array An associative array of all settings.
     */
    public function getAll(): array;
}
