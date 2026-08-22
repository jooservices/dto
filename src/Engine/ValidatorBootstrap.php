<?php

declare(strict_types=1);

namespace JOOservices\Dto\Engine;

use JOOservices\Dto\Validation\ObjectValidator;
use JOOservices\Dto\Validation\ValidatorRegistry;
use JOOservices\Dto\Validation\Validators\BetweenValidator;
use JOOservices\Dto\Validation\Validators\EmailValidator;
use JOOservices\Dto\Validation\Validators\LengthValidator;
use JOOservices\Dto\Validation\Validators\MaxValidator;
use JOOservices\Dto\Validation\Validators\MinValidator;
use JOOservices\Dto\Validation\Validators\RegexValidator;
use JOOservices\Dto\Validation\Validators\RequiredIfValidator;
use JOOservices\Dto\Validation\Validators\RequiredValidator;
use JOOservices\Dto\Validation\Validators\UrlValidator;
use JOOservices\Dto\Validation\Validators\ValidValidator;

final class ValidatorBootstrap
{
    public function create(): ObjectValidator
    {
        $validators = new ValidatorRegistry();
        $validators->register(new RequiredValidator());
        $validators->register(new RequiredIfValidator());
        $validators->register(new EmailValidator());
        $validators->register(new UrlValidator());
        $validators->register(new RegexValidator());
        $validators->register(new LengthValidator());
        $validators->register(new MinValidator());
        $validators->register(new MaxValidator());
        $validators->register(new BetweenValidator());
        $validators->register(new ValidValidator());

        return new ObjectValidator($validators);
    }
}
