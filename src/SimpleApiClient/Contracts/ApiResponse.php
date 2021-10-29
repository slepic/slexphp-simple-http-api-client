<?php

declare(strict_types=1);

namespace Slexphp\Http\SimpleApiClient\Contracts;

class ApiResponse implements ApiResponseInterface
{
    /**
     * @param int $status
     * @param array<string, string> $headers
     * @param string $rawBody
     * @param array|null $parsedBody
     */
    public function __construct(
        private int $status,
        private array $headers,
        private string $rawBody,
        private ?array $parsedBody = null
    ) {
    }

    public function getStatusCode(): int
    {
        return $this->status;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeaderLine(string $name): string
    {
        return $this->headers[\strtolower($name)] ?? '';
    }

    public function getRawBody(): string
    {
        return $this->rawBody;
    }

    public function getParsedBody(): ?array
    {
        return $this->parsedBody;
    }
}
