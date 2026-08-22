<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\Validation\Regex;
use JOOservices\Dto\Core\Dto;

final class ValidationFixtureRegexInvalidPatternDto extends Dto
{
    public function __construct(
        // Intentionally malformed pattern (no ending delimiter) to exercise the
        // fail-closed branch: preg_match() emits a warning and returns false.
        #[Regex(pattern: '/(unterminated')]
        public readonly ?string $value = null,
    ) {
    }
}
