<?php

declare(strict_types=1);

namespace JOOservices\Dto\Core\Concerns;

use FilesystemIterator;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use SplFileInfo;

/**
 * Provides auto-discovery and lazy instantiation capabilities for registries.
 *
 * This trait can be used by CasterRegistry and ValidatorRegistry to:
 * - Auto-discover implementations from a directory
 * - Support lazy instantiation via PSR-11 container
 * - Register by class name for deferred instantiation
 *
 * @template T
 */
trait SupportsDiscovery
{
    private ?ContainerInterface $container = null;

    /** @var array<array{class: class-string, priority: int}> */
    private array $pendingClasses = [];

    /**
     * Set PSR-11 container for lazy instantiation.
     *
     * When a container is set, classes registered via registerClass() will be
     * resolved through the container, enabling dependency injection.
     */
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * Register a caster/validator by class name for lazy instantiation.
     *
     * @param  class-string  $className
     */
    public function registerClass(string $className, int $priority = 0): void
    {
        $interface = $this->getItemInterface();

        if (! is_subclass_of($className, $interface)) {
            throw new InvalidArgumentException(
                "Class {$className} must implement {$interface}",
            );
        }

        $this->pendingClasses[] = [
            'class' => $className,
            'priority' => $priority,
        ];

        $this->sorted = false;
    }

    /**
     * Auto-discover and register implementations from a directory.
     *
     * Scans the given directory for PHP files that implement the required interface.
     * Only classes that can be instantiated without constructor arguments are registered.
     *
     * @param  string  $directory  Absolute path to scan
     * @param  string  $namespace  PSR-4 namespace prefix for the directory
     */
    public function discoverFrom(string $directory, string $namespace): void
    {
        if (! is_dir($directory)) {
            throw new InvalidArgumentException(
                "Directory does not exist: {$directory}",
            );
        }

        $namespace = rtrim($namespace, '\\').'\\';

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            $this->processDiscoveredFile($file, $directory, $namespace);
        }
    }

    /**
     * Resolve pending class registrations.
     *
     * Called before iteration to instantiate any classes registered via registerClass().
     */
    protected function resolvePendingClasses(): void
    {
        foreach ($this->pendingClasses as $pending) {
            $className = $pending['class'];
            $priority = $pending['priority'];

            /** @var object $instance */
            $instance = $this->container !== null
                ? $this->container->get($className)
                : new $className;

            $this->doRegister($instance, $priority);
        }

        $this->pendingClasses = [];
    }

    /**
     * Get the interface that items must implement.
     *
     * @return class-string
     */
    abstract protected function getItemInterface(): string;

    /**
     * Internal registration method called by discovery.
     * Must be implemented by the using class to delegate to the public register method.
     *
     * @param  object  $instance  The instance to register
     * @param  int  $priority  Registration priority
     */
    abstract protected function doRegister(object $instance, int $priority): void;

    /**
     * Process a discovered file and register it if valid.
     */
    private function processDiscoveredFile(SplFileInfo $file, string $directory, string $namespace): void
    {
        if ($file->getExtension() !== 'php') {
            return;
        }

        $className = $this->fileToClassName($file, $directory, $namespace);

        if (! $this->isValidDiscoveredClass($className)) {
            return;
        }

        /** @var class-string $className */
        $this->doRegister(new $className, 0);
    }

    /**
     * Check if a discovered class is valid for registration.
     */
    private function isValidDiscoveredClass(?string $className): bool
    {
        if ($className === null || ! class_exists($className)) {
            return false;
        }

        if (! is_subclass_of($className, $this->getItemInterface())) {
            return false;
        }

        return $this->canInstantiateWithoutArgs($className);
    }

    /**
     * Check if a class can be instantiated without constructor arguments.
     */
    private function canInstantiateWithoutArgs(string $className): bool
    {
        /** @var class-string $className */
        $reflection = new ReflectionClass($className);

        if ($reflection->isAbstract() || $reflection->isInterface()) {
            return false;
        }

        $constructor = $reflection->getConstructor();

        return $constructor === null || $constructor->getNumberOfRequiredParameters() === 0;
    }

    /**
     * Convert a file path to a fully qualified class name.
     */
    private function fileToClassName(SplFileInfo $file, string $baseDir, string $namespace): ?string
    {
        $relativePath = substr($file->getPathname(), strlen($baseDir) + 1);
        $relativePath = str_replace('/', '\\', $relativePath);
        $relativePath = substr($relativePath, 0, -4); // Remove .php

        return $namespace.$relativePath;
    }
}
