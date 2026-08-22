<?php

declare(strict_types=1);

namespace JOOservices\Dto\Meta;

use JOOservices\Dto\Attributes\CastWith;
use JOOservices\Dto\Attributes\DefaultFrom;
use JOOservices\Dto\Attributes\MapFrom;
use JOOservices\Dto\Attributes\MapTo;
use JOOservices\Dto\Attributes\Pipeline;
use JOOservices\Dto\Attributes\StrictType;
use JOOservices\Dto\Attributes\TransformWith;
use JOOservices\Dto\Attributes\Validation\ValidationRuleAttribute;

/**
 * Resolves property attributes once at meta-build time.
 */
final class PropertyMetaAttributeResolver
{
    /**
     * @param  list<object>  $attributes
     */
    public function resolve(string $propertyName, array $attributes): PropertyMetaResolvedAttributes
    {
        $scalars = $this->resolveScalarAttributes($attributes);
        $lists = $this->resolveListAttributes($attributes);

        return new PropertyMetaResolvedAttributes(
            mapFromKey: $scalars['mapFromKey'],
            mapToKey: $scalars['mapToKey'],
            castWith: $scalars['castWith'],
            strictType: $scalars['strictType'],
            defaultFrom: $scalars['defaultFrom'],
            pipelines: $lists['pipelines'],
            transformWith: $lists['transformWith'],
            validationRules: $lists['validationRules'],
            sourceKeys: $this->buildSourceKeys($propertyName, $scalars['mapFromKey']),
        );
    }

    /**
     * @param  list<object>  $attributes
     * @return array{
     *     mapFromKey: ?string,
     *     mapToKey: ?string,
     *     castWith: ?CastWith,
     *     strictType: ?StrictType,
     *     defaultFrom: ?DefaultFrom,
     * }
     */
    private function resolveScalarAttributes(array $attributes): array
    {
        $mapFromKey = null;
        $mapToKey = null;
        $castWith = null;
        $strictType = null;
        $defaultFrom = null;

        foreach ($attributes as $attribute) {
            if ($attribute instanceof MapFrom) {
                $mapFromKey = $attribute->key;
                continue;
            }

            if ($attribute instanceof MapTo) {
                $mapToKey = $attribute->key;
                continue;
            }

            if ($attribute instanceof CastWith) {
                $castWith = $attribute;
                continue;
            }

            if ($attribute instanceof StrictType) {
                $strictType = $attribute;
                continue;
            }

            if ($attribute instanceof DefaultFrom) {
                $defaultFrom = $attribute;
            }
        }

        return [
            'mapFromKey' => $mapFromKey,
            'mapToKey' => $mapToKey,
            'castWith' => $castWith,
            'strictType' => $strictType,
            'defaultFrom' => $defaultFrom,
        ];
    }

    /**
     * @param  list<object>  $attributes
     * @return array{
     *     pipelines: list<Pipeline>,
     *     transformWith: list<TransformWith>,
     *     validationRules: list<ValidationRuleAttribute>,
     * }
     */
    private function resolveListAttributes(array $attributes): array
    {
        $pipelines = [];
        $transformWith = [];
        $validationRules = [];

        foreach ($attributes as $attribute) {
            if ($attribute instanceof Pipeline) {
                $pipelines[] = $attribute;
                continue;
            }

            if ($attribute instanceof TransformWith) {
                $transformWith[] = $attribute;
                continue;
            }

            if ($attribute instanceof ValidationRuleAttribute) {
                $validationRules[] = $attribute;
            }
        }

        return [
            'pipelines' => $pipelines,
            'transformWith' => $transformWith,
            'validationRules' => $validationRules,
        ];
    }

    /**
     * @return list<string>
     */
    private function buildSourceKeys(string $propertyName, ?string $mapFromKey): array
    {
        $sourceKeys = [$propertyName];
        if ($mapFromKey !== null) {
            array_unshift($sourceKeys, $mapFromKey);
        }

        return array_values(array_unique($sourceKeys));
    }
}
