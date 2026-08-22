<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    private ?Generator $faker = null;

    protected function faker(): Generator
    {
        return $this->faker ??= Factory::create();
    }
}
