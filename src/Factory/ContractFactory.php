<?php

namespace Leopard\Core\Factory;

/**
 * Universal factory for creating contract-bound instances
 * Allows overriding default implementations via interface mapping.
 */
class ContractFactory
{
    /**
     * Optional Doctrine resolve-target registry class.
     */
    private const RESOLVE_TARGET_ENTITY_REGISTRY = 'Leopard\\Doctrine\\ResolveTargetEntityRegistry';

    /**
     * Registered interface-to-class mappings
     * @var array<string, string>
     */
    private static array $mappings = [];

    /**
     * Create a new instance by interface contract
     *
     * @param string $interface Interface name (e.g., UserInterface::class)
     * @return object Instance of the registered class
     * @throws \InvalidArgumentException If no mapping found for interface
     */
    public static function create(string $interface): object
    {
        $className = self::$mappings[$interface] ?? null;

        if (!$className) {
            throw new \InvalidArgumentException(
                "No mapping found for interface: $interface. Did you register it?"
            );
        }

        return new $className();
    }

    /**
     * Register a custom implementation
     *
     * @param string $interface Interface name (e.g., UserInterface::class)
     * @param string $className Class name implementing the interface
     * @param array<string, mixed> $doctrineMapping Optional Doctrine resolve-target mapping options
     * @throws \InvalidArgumentException If class doesn't exist or doesn't implement interface
     */
    public static function register(string $interface, string $className, array $doctrineMapping = []): void
    {
        if (!class_exists($className)) {
            throw new \InvalidArgumentException(
                "Class not found: $className"
            );
        }

        if (!interface_exists($interface)) {
            throw new \InvalidArgumentException(
                "Interface not found: $interface"
            );
        }

        $reflection = new \ReflectionClass($className);
        if (!$reflection->implementsInterface($interface)) {
            throw new \InvalidArgumentException(
                "$className does not implement $interface"
            );
        }

        self::$mappings[$interface] = $className;
        self::syncResolveTargetEntityMapping($interface, $className, $doctrineMapping);
    }

    /**
     * Get registered class for interface
     *
     * @param string $interface Interface name
     * @return string|null Registered class name or null if not found
     */
    public static function getMapping(string $interface): ?string
    {
        return self::$mappings[$interface] ?? null;
    }

    /**
     * Check if an interface has a registered mapping
     */
    public static function hasMapping(string $interface): bool
    {
        return isset(self::$mappings[$interface]);
    }

    /**
     * Get all registered mappings
     *
     * @return array<string, string>
     */
    public static function getMappings(): array
    {
        return self::$mappings;
    }

    /**
     * Unregister a mapping
     */
    public static function unregister(string $interface): bool
    {
        if (isset(self::$mappings[$interface])) {
            unset(self::$mappings[$interface]);
            return true;
        }

        return false;
    }

    /**
     * Clear all registered mappings
     */
    public static function clear(): void
    {
        self::$mappings = [];
    }

    /**
     * Reset to initial state (empty mappings)
     */
    public static function reset(): void
    {
        self::clear();
    }

    /**
     * Sync mapping into leopard-doctrine ResolveTargetEntityRegistry when available.
     *
     * Keeps leopard-core decoupled from leopard-doctrine by checking runtime availability.
     *
     * @param array<string, mixed> $doctrineMapping
     */
    private static function syncResolveTargetEntityMapping(string $interface, string $className, array $doctrineMapping = []): void
    {
        $registryClass = self::RESOLVE_TARGET_ENTITY_REGISTRY;

        if (!class_exists($registryClass) || !method_exists($registryClass, 'addResolveTargetEntity')) {
            return;
        }

        $registryClass::addResolveTargetEntity($interface, $className, $doctrineMapping);
    }
}
