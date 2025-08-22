<?php

use PHPUnit\Framework\TestCase;
use Leopard\Core\Services\Config;

class ConfigTest extends TestCase
{
    private Config $config;

    protected function setUp(): void
    {
        $this->config = new Config();
    }

    public function testLoadValidFile(): void
    {
        $this->config->load(__DIR__ . '/../fixtures/config/app.yaml');
        $this->assertEquals('localhost', $this->config->get('database.host'));
    }

    public function testLoadInvalidFile(): void
    {
        $this->expectException(RuntimeException::class);
        $this->config->load(__DIR__ . '/../fixtures/config/invalid.yaml');
    }

    public function testGetExistingKey(): void
    {
        $this->config->set('app.name', 'Leopard Skeleton');
        $this->assertEquals('Leopard Skeleton', $this->config->get('app.name'));
    }

    public function testGetNonExistingKey(): void
    {
        $this->assertNull($this->config->get('non.existing.key'));
    }

    public function testSetAndGet(): void
    {
        $this->config->set('app.debug', true);
        $this->assertTrue($this->config->get('app.debug'));
    }

    public function testHasKey(): void
    {
        $this->config->set('app.version', '1.0.0');
        $this->assertTrue($this->config->has('app.version'));
        $this->assertFalse($this->config->has('non.existing.key'));
    }
}
