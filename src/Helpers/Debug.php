<?php

namespace Leopard\Core\Helpers;

/**
 * Debug class provides methods for debugging purposes.
 * This class includes methods to dump variable contents and
 * to terminate script execution after dumping, which is
 * useful for inspecting variable states during development.
 * * @package Core\Helpers
 */
class Debug
{
    /**
     * Dumps the provided arguments for debugging purposes.
     *
     * This method accepts a variable number of arguments and outputs their
     * contents in a readable format. It is typically used for debugging
     * and inspecting variable values during development.
     *
     * @param mixed ...$args The arguments to be dumped.
     * @return void
     */
    public static function dump(...$args): void
    {
        foreach ($args as $arg) {
            echo '<pre>';
            var_dump($arg);
            echo '</pre>';
        }
    }

    /**
     * Dumps the given variables and terminates the script execution.
     *
     * This method is useful for debugging purposes. It accepts any number of arguments,
     * dumps their contents, and stops further execution of the script.
     *
     * @param mixed ...$args The variables to be dumped.
     * @return void
     */
    public static function dd(...$args): void
    {
        self::dump(...$args);
        die();
    }
}
