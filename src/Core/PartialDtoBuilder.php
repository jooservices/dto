<?php

declare(strict_types=1);

namespace JOOservices\Dto\Core;

use InvalidArgumentException;
use JOOservices\Dto\Engine\EngineHolder;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use ReflectionException;

/**
 * @template T of AbstractDto
 */
final class PartialDtoBuilder
{
    /**
     * @param  class-string<T>  $dtoClass
     * @param  list<string>  $allowedFields  Source keys (MapFrom / raw input)
     */
    public function __construct(
        private readonly string $dtoClass,
        private readonly array $allowedFields,
    ) {
    }

    /**
     * @return T
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function from(mixed $source, ?Context $ctx = null): AbstractDto
    {
        $data = EngineHolder::get()->normalizeInput($source);
        $allowed = array_fill_keys($this->allowedFields, true);
        $filtered = [];
        foreach ($data as $key => $value) {
            if (isset($allowed[$key])) {
                $filtered[$key] = $value;
            }
        }

        /** @var array<string, mixed> $filtered */
        return $this->dtoClass::from($filtered, $ctx);
    }

    /**
     * @return list<string>
     */
    public function getAllowedFields(): array
    {
        return $this->allowedFields;
    }
}
