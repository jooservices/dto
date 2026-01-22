<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Core\Concerns;

use InvalidArgumentException;
use JOOservices\Dto\Casting\CasterInterface;
use JOOservices\Dto\Casting\CasterRegistry;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use Psr\Container\ContainerInterface;
use stdClass;

#[CoversClass(CasterRegistry::class)]
#[UsesClass(PropertyMeta::class)]
#[UsesClass(TypeDescriptor::class)]
final class SupportsDiscoveryTest extends TestCase
{
    private const string TEMP_DIR_PREFIX = '/dto_test_discovery_';

    private CasterRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->registry = new CasterRegistry;
    }

    #[Test]
    public function test_discover_from_throws_exception_for_nonexistent_directory(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Directory does not exist');

        $this->registry->discoverFrom('/nonexistent/path', 'SomeNamespace');
    }

    #[Test]
    public function test_register_class_throws_exception_for_invalid_class(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must implement');

        // stdClass does not implement CasterInterface
        $this->registry->registerClass(stdClass::class);
    }

    #[Test]
    public function test_register_class_works_with_valid_caster(): void
    {
        $this->registry->registerClass(SimpleCaster::class);

        $property = $this->createPropertyMeta('test', 'string');
        $result = $this->registry->canCast($property, 'test value');

        $this->assertTrue($result);
    }

    #[Test]
    public function test_set_container_enables_dependency_injection(): void
    {
        $mockCaster = self::createStub(CasterInterface::class);
        $mockCaster->method('supports')->willReturn(true);
        $mockCaster->method('cast')->willReturn('casted');

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('get')
            ->with(SimpleCaster::class)
            ->willReturn($mockCaster);

        $this->registry->setContainer($container);
        $this->registry->registerClass(SimpleCaster::class);

        $property = $this->createPropertyMeta('test', 'string');
        $result = $this->registry->canCast($property, 'value');

        $this->assertTrue($result);
    }

    #[Test]
    public function test_discover_from_ignores_non_php_files(): void
    {
        $tempDir = sys_get_temp_dir().self::TEMP_DIR_PREFIX.uniqid();
        mkdir($tempDir, 0o777, true);

        file_put_contents($tempDir.'/readme.txt', 'This is not a PHP file');

        try {
            $this->registry->discoverFrom($tempDir, 'TestNamespace');

            // Should not throw and registry should be empty
            $property = $this->createPropertyMeta('test', 'string');
            $this->assertFalse($this->registry->canCast($property, 'value'));
        } finally {
            unlink($tempDir.'/readme.txt');
            rmdir($tempDir);
        }
    }

    #[Test]
    public function test_register_class_with_priority(): void
    {
        $this->registry->registerClass(SimpleCaster::class, 100);

        $property = $this->createPropertyMeta('test', 'string');
        $this->assertTrue($this->registry->canCast($property, 'value'));
    }

    #[Test]
    public function test_discover_from_empty_directory(): void
    {
        $tempDir = sys_get_temp_dir().self::TEMP_DIR_PREFIX.uniqid();
        mkdir($tempDir, 0o777, true);

        try {
            $this->registry->discoverFrom($tempDir, 'EmptyNamespace');

            $property = $this->createPropertyMeta('test', 'string');
            $this->assertFalse($this->registry->canCast($property, 'value'));
        } finally {
            rmdir($tempDir);
        }
    }

    #[Test]
    public function test_multiple_register_class_calls(): void
    {
        $this->registry->registerClass(SimpleCaster::class, 10);
        $this->registry->registerClass(SimpleCaster::class, 20);

        $property = $this->createPropertyMeta('test', 'string');
        $this->assertTrue($this->registry->canCast($property, 'value'));
    }

    private function createPropertyMeta(string $name, string $type): PropertyMeta
    {
        return new PropertyMeta(
            name: $name,
            type: new TypeDescriptor(
                name: $type,
                isBuiltin: true,
                isNullable: false,
                isArray: false,
                arrayItemType: null,
                isEnum: false,
                enumClass: null,
                isDto: false,
                isDateTime: false,
            ),
            isReadonly: false,
            hasDefault: false,
            defaultValue: null,
            mapFrom: null,
            casterClass: null,
            transformerClass: null,
            isHidden: false,
            validationRules: [],
            attributes: [],
        );
    }
}

/**
 * Simple test caster for registration testing.
 */
class SimpleCaster implements CasterInterface
{
    public function supports(PropertyMeta $property, mixed $value): bool
    {
        return true;
    }

    public function cast(PropertyMeta $property, mixed $value, ?Context $ctx): mixed
    {
        return 'simple_cast';
    }
}
