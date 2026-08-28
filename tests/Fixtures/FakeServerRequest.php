<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use RuntimeException;

final class FakeServerRequest implements ServerRequestInterface
{
    /**
         * @param array<string, mixed>|object|null $parsedBody
         * @param array<string, mixed>             $queryParams
         */
    public function __construct(
        private readonly array | object | null $parsedBody = null,
        private readonly array $queryParams = [],
    ) {
    }

    public function getProtocolVersion(): string
    {
        return '1.1';
    }

    public function withProtocolVersion(string $version): static
    {
        return $this;
    }

    /** @return array<array<string>> */
    public function getHeaders(): array
    {
        return [];
    }

    public function hasHeader(string $name): bool
    {
        return false;
    }

    /** @return string[] */
    public function getHeader(string $name): array
    {
        return [];
    }

    public function getHeaderLine(string $name): string
    {
        return '';
    }

    /**
     * @param string|string[] $value
     */
    public function withHeader(string $name, $value): static
    {
        return $this;
    }

    /**
     * @param string|string[] $value
     */
    public function withAddedHeader(string $name, $value): static
    {
        return $this;
    }

    public function withoutHeader(string $name): static
    {
        return $this;
    }

    /**
     * @throws RuntimeException
     */
    public function getBody(): StreamInterface
    {
        throw new RuntimeException('Not implemented in fake.');
    }

    public function withBody(StreamInterface $body): static
    {
        return $this;
    }

    public function getRequestTarget(): string
    {
        return '/';
    }

    public function withRequestTarget(string $requestTarget): static
    {
        return $this;
    }

    public function getMethod(): string
    {
        return 'GET';
    }

    public function withMethod(string $method): static
    {
        return $this;
    }

    /**
     * @throws RuntimeException
     */
    public function getUri(): UriInterface
    {
        throw new RuntimeException('Not implemented in fake.');
    }

    public function withUri(UriInterface $uri, bool $preserveHost = false): static
    {
        return $this;
    }

    /** @return array<string, mixed> */
    public function getServerParams(): array
    {
        return [];
    }

    /** @return array<string, string|string[]> */
    public function getCookieParams(): array
    {
        return [];
    }

    /** @param mixed $cookies */
    public function withCookieParams(mixed $cookies): static
    {
        return $this;
    }

    /** @return array<string, mixed> */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /** @param mixed $query */
    public function withQueryParams(mixed $query): static
    {
        return $this;
    }

    /** @return array<string, mixed> */
    public function getUploadedFiles(): array
    {
        return [];
    }

    /** @param mixed $uploadedFiles */
    public function withUploadedFiles(mixed $uploadedFiles): static
    {
        return $this;
    }

    /** @return array<string, mixed>|object|null */
    public function getParsedBody(): array | object | null
    {
        return $this->parsedBody;
    }

    /** @param mixed $data */
    public function withParsedBody(mixed $data): static
    {
        return $this;
    }

    /** @return array<string, mixed> */
    public function getAttributes(): array
    {
        return [];
    }

    public function getAttribute(string $name, $default = null): mixed
    {
        return $default;
    }

    public function withAttribute(string $name, $value): static
    {
        return $this;
    }

    public function withoutAttribute(string $name): static
    {
        return $this;
    }
}
