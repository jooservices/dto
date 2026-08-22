<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration\Pipeline;

use DOMDocument;
use DOMElement;
use DOMNode;
use JOOservices\Dto\Hydration\PipelineStepInterface;

/**
 * Strip tags and attributes (S2). `$allowed` is a strip_tags allowlist such as `<b><i>`.
 *
 * Attribute removal is DOM-based rather than regex-based: a regex that stops at the
 * first `>` can be tricked by a `>` inside a quoted attribute value into leaving a
 * live attribute behind. DOMDocument parses quoting correctly, so every attribute on
 * every allowed element is removed regardless of its content.
 */
final class StripTags implements PipelineStepInterface
{
    public function __construct(
        private readonly string $allowed = '',
    ) {
    }

    public function handle(mixed $value): mixed
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        $stripped = strip_tags($value, $this->allowed);
        if ($this->allowed === '') {
            return $stripped;
        }

        return $this->stripAttributes($stripped);
    }

    private function stripAttributes(string $html): string
    {
        $document = new DOMDocument();
        $previousErrorMode = libxml_use_internal_errors(true);

        $loaded = $document->loadHTML(
            '<?xml encoding="utf-8" ?><dto-root>' . $html . '</dto-root>',
            LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previousErrorMode);

        $root = $document->getElementsByTagName('dto-root')->item(0);
        if (! $loaded || $root === null) {
            return strip_tags($html);
        }

        $this->stripAttributesRecursively($root);

        $out = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $out .= $document->saveHTML($child);
        }

        return $out;
    }

    private function stripAttributesRecursively(DOMNode $node): void
    {
        if ($node instanceof DOMElement) {
            foreach (iterator_to_array($node->attributes ?? []) as $attribute) {
                $node->removeAttribute($attribute->name);
            }
        }

        foreach (iterator_to_array($node->childNodes) as $child) {
            $this->stripAttributesRecursively($child);
        }
    }
}
