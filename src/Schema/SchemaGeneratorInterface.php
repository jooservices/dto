<?php

declare(strict_types=1);

namespace JOOservices\Dto\Schema;

interface SchemaGeneratorInterface
{
    /**
     * @param  class-string  $className
     * @return array<string, mixed>
     */
    public function generate(string $className): array;
}
