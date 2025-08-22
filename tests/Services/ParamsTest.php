<?php

use PHPUnit\Framework\TestCase;
use Leopard\Core\Services\Params;

class ParamsTest extends TestCase
{
    private Params $params;

    protected function setUp(): void
    {
        $this->params = new Params();
    }

    public function testLoadValidFile(): void
    {
        $this->params->load(__DIR__ . '/../fixtures/config/params.php');
        $this->assertEquals('Leopard Skeleton', $this->params->get('app.name'));
    }

    public function testLoadInvalidFile(): void
    {
        $this->expectException(RuntimeException::class);
        $this->params->load(__DIR__ . '/../fixtures/config/invalid.php');
    }

    public function testGetExistingKey(): void
    {
        $this->params->set('app.name', 'Leopard Skeleton');
        $this->assertEquals('Leopard Skeleton', $this->params->get('app.name'));
    }

    public function testGetNonExistingKey(): void
    {
        $this->assertEquals('default', $this->params->get('non.existing.key', 'default'));
    }

    public function testSetAndGet(): void
    {
        $this->params->set('app.debug', true);
        $this->assertTrue($this->params->get('app.debug'));
    }

    public function testHasKey(): void
    {
        $this->params->set('app.version', '1.0.0');
        $this->assertTrue($this->params->has('app.version'));
        $this->assertFalse($this->params->has('non.existing.key'));
    }
}
