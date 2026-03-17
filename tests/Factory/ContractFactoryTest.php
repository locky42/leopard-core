<?php

namespace Leopard\Core\Tests\Factory;

use Leopard\Core\Factory\ContractFactory;
use Leopard\Doctrine\ResolveTargetEntityRegistry;
use PHPUnit\Framework\TestCase;

/**
 * Test fixtures: Simple interfaces and implementations for testing
 */
interface TestEntityInterface
{
    public function getId(): int;
    public function getName(): string;
}

interface TestItemInterface
{
    public function getTitle(): string;
}

class TestEntity implements TestEntityInterface
{
    private int $id = 1;
    private string $name = 'Test Entity';

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}

class CustomTestEntity extends TestEntity
{
    public function customMethod(): string
    {
        return 'custom';
    }
}

class TestItem implements TestItemInterface
{
    public function getTitle(): string
    {
        return 'Test Item';
    }
}

class InvalidClass
{
}

/**
 * Tests for ContractFactory
 */
class ContractFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        ContractFactory::clear();
        if (class_exists(ResolveTargetEntityRegistry::class)) {
            $this->resetResolveTargetMappings();
        }
    }

    protected function tearDown(): void
    {
        ContractFactory::reset();
        if (class_exists(ResolveTargetEntityRegistry::class)) {
            $this->resetResolveTargetMappings();
        }
    }

    public function testRegisterAndCreate(): void
    {
        ContractFactory::register(TestEntityInterface::class, TestEntity::class);

        $entity = ContractFactory::create(TestEntityInterface::class);

        $this->assertInstanceOf(TestEntity::class, $entity);
        $this->assertInstanceOf(TestEntityInterface::class, $entity);
        $this->assertEquals(1, $entity->getId());
        $this->assertEquals('Test Entity', $entity->getName());
    }

    public function testCreateThrowsExceptionWhenNoMapping(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No mapping found for interface');

        ContractFactory::create(TestEntityInterface::class);
    }

    public function testRegisterThrowsExceptionForNonExistentClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Class not found');

        ContractFactory::register(TestEntityInterface::class, 'NonExistentClass');
    }

    public function testRegisterThrowsExceptionForNonExistentInterface(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Interface not found');

        ContractFactory::register('NonExistentInterface', TestEntity::class);
    }

    public function testRegisterThrowsExceptionWhenClassDoesNotImplementInterface(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('does not implement');

        ContractFactory::register(TestEntityInterface::class, InvalidClass::class);
    }

    public function testGetMapping(): void
    {
        ContractFactory::register(TestEntityInterface::class, TestEntity::class);

        $mapping = ContractFactory::getMapping(TestEntityInterface::class);

        $this->assertEquals(TestEntity::class, $mapping);
    }

    public function testGetMappingReturnsNullForUnregistered(): void
    {
        $mapping = ContractFactory::getMapping(TestEntityInterface::class);

        $this->assertNull($mapping);
    }

    public function testHasMapping(): void
    {
        $this->assertFalse(ContractFactory::hasMapping(TestEntityInterface::class));

        ContractFactory::register(TestEntityInterface::class, TestEntity::class);

        $this->assertTrue(ContractFactory::hasMapping(TestEntityInterface::class));
    }

    public function testGetMappings(): void
    {
        ContractFactory::register(TestEntityInterface::class, TestEntity::class);
        ContractFactory::register(TestItemInterface::class, TestItem::class);

        $mappings = ContractFactory::getMappings();

        $this->assertIsArray($mappings);
        $this->assertCount(2, $mappings);
        $this->assertArrayHasKey(TestEntityInterface::class, $mappings);
        $this->assertArrayHasKey(TestItemInterface::class, $mappings);
        $this->assertEquals(TestEntity::class, $mappings[TestEntityInterface::class]);
        $this->assertEquals(TestItem::class, $mappings[TestItemInterface::class]);
    }

    public function testUnregister(): void
    {
        ContractFactory::register(TestEntityInterface::class, TestEntity::class);
        $this->assertTrue(ContractFactory::hasMapping(TestEntityInterface::class));

        $result = ContractFactory::unregister(TestEntityInterface::class);

        $this->assertTrue($result);
        $this->assertFalse(ContractFactory::hasMapping(TestEntityInterface::class));
    }

    public function testUnregisterReturnsFalseForNonExistentMapping(): void
    {
        $result = ContractFactory::unregister(TestEntityInterface::class);

        $this->assertFalse($result);
    }

    public function testClear(): void
    {
        ContractFactory::register(TestEntityInterface::class, TestEntity::class);
        ContractFactory::register(TestItemInterface::class, TestItem::class);

        $this->assertCount(2, ContractFactory::getMappings());

        ContractFactory::clear();

        $this->assertCount(0, ContractFactory::getMappings());
    }

    public function testReset(): void
    {
        ContractFactory::register(TestEntityInterface::class, TestEntity::class);
        ContractFactory::register(TestItemInterface::class, TestItem::class);

        ContractFactory::reset();

        $this->assertCount(0, ContractFactory::getMappings());
    }

    public function testCanOverrideMapping(): void
    {
        ContractFactory::register(TestEntityInterface::class, TestEntity::class);
        $entity1 = ContractFactory::create(TestEntityInterface::class);
        $this->assertInstanceOf(TestEntity::class, $entity1);

        ContractFactory::register(TestEntityInterface::class, CustomTestEntity::class);
        $entity2 = ContractFactory::create(TestEntityInterface::class);
        $this->assertInstanceOf(CustomTestEntity::class, $entity2);
        $this->assertEquals('custom', $entity2->customMethod());
    }

    public function testMultipleInterfacesIndependently(): void
    {
        ContractFactory::register(TestEntityInterface::class, TestEntity::class);
        ContractFactory::register(TestItemInterface::class, TestItem::class);

        $entity = ContractFactory::create(TestEntityInterface::class);
        $item = ContractFactory::create(TestItemInterface::class);

        $this->assertInstanceOf(TestEntity::class, $entity);
        $this->assertInstanceOf(TestItem::class, $item);
        $this->assertNotSame($entity, $item);
    }

    public function testCreateReturnsNewInstanceEachTime(): void
    {
        ContractFactory::register(TestEntityInterface::class, TestEntity::class);

        $entity1 = ContractFactory::create(TestEntityInterface::class);
        $entity2 = ContractFactory::create(TestEntityInterface::class);

        $this->assertNotSame($entity1, $entity2);
        $this->assertEquals($entity1->getId(), $entity2->getId());
    }

    public function testRegisterSyncsResolveTargetEntityRegistryWhenAvailable(): void
    {
        if (!class_exists(ResolveTargetEntityRegistry::class)) {
            $this->markTestSkipped('ResolveTargetEntityRegistry is not available in this test environment.');
        }

        ContractFactory::register(TestEntityInterface::class, TestEntity::class);

        $mapping = ResolveTargetEntityRegistry::getMappingForInterface(TestEntityInterface::class);

        $this->assertIsArray($mapping);
        $this->assertEquals(TestEntity::class, $mapping['implementation']);
        $this->assertEquals([], $mapping['mapping']);
    }

    public function testRegisterSyncsResolveTargetEntityRegistryWithDoctrineMapping(): void
    {
        if (!class_exists(ResolveTargetEntityRegistry::class)) {
            $this->markTestSkipped('ResolveTargetEntityRegistry is not available in this test environment.');
        }

        $doctrineMapping = ['fetch' => 'EAGER'];
        ContractFactory::register(TestEntityInterface::class, TestEntity::class, $doctrineMapping);

        $mapping = ResolveTargetEntityRegistry::getMappingForInterface(TestEntityInterface::class);

        $this->assertIsArray($mapping);
        $this->assertEquals(TestEntity::class, $mapping['implementation']);
        $this->assertEquals($doctrineMapping, $mapping['mapping']);
    }

    private function resetResolveTargetMappings(): void
    {
        $reflection = new \ReflectionClass(ResolveTargetEntityRegistry::class);
        $property = $reflection->getProperty('mappings');
        $property->setAccessible(true);
        $property->setValue([]);
    }
}
