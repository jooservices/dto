<?php

declare(strict_types=1);

namespace JOOservices\Dto\Validation\Validators;

use InvalidArgumentException;
use JOOservices\Dto\Attributes\Validation\Valid;
use JOOservices\Dto\Attributes\Validation\ValidationRuleAttribute;
use JOOservices\Dto\Core\AbstractDto;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\ClassMeta;
use JOOservices\Dto\Validation\ValidatorInterface;
use ReflectionException;

final class ValidValidator implements ValidatorInterface
{
    private const int MAX_DEPTH = 32;

    public function supports(ValidationRuleAttribute $rule): bool
    {
        return $rule instanceof Valid;
    }

    /**
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function validate(
        ValidationRuleAttribute $rule,
        ClassMeta $meta,
        string $propertyName,
        mixed $value,
        array $values,
        ?Context $ctx,
    ): void {
        unset($rule, $meta, $propertyName, $values);
        $next = $this->nextValidationContext($ctx);

        if ($value instanceof AbstractDto) {
            $value->validate($next);

            return;
        }

        if (! is_array($value)) {
            return;
        }

        foreach ($value as $item) {
            if ($item instanceof AbstractDto) {
                $item->validate($next);
            }
        }
    }

    /**
     * @throws InvalidArgumentException
     * @throws ValidationException
     */
    private function nextValidationContext(?Context $ctx): Context
    {
        $base = $ctx ?? new Context(validationEnabled: true, sourceKeyOut: false);
        $raw = $base->customData['validDepth'] ?? 0;
        $depth = is_int($raw) ? $raw : 0;

        if ($depth >= self::MAX_DEPTH) {
            throw new ValidationException(message: 'Nested validation depth exceeded.');
        }

        return $base->withCustomData(['validDepth' => $depth + 1]);
    }
}
