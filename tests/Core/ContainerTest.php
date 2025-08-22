<?php

use PHPUnit\Framework\TestCase;
use Leopard\Core\Container;

class ContainerTest extends TestCase
{
    public function testGetInstance(): void
    {
        $container = new Container();
        $instance = $container->get(stdClass::class);

        $this->assertInstanceOf(stdClass::class, $instance);
    }

    public function testHasClass(): void
    {
        $container = new Container();
        $this->assertTrue($container->has(stdClass::class));
        $this->assertFalse($container->has('NonExistentClass'));
    }
}