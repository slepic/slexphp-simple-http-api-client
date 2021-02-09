<?php

declare(strict_types=1);

namespace Slexphp\Http\SimpleApiClient\Contracts;

/**
 * Simple API client
 */
interface ApiClientInterface
{
    /**
     * @param string $baseUrl
     * @param string $method
     * @param string $endpoint
     * @param array<string, mixed> $query
     * @param array<string, string> $headers
     * @param array|object|string|null $body array|object=>serialize; string=>already serialized body; null=>no body
     * @return ApiResponseInterface
     * @throws ApiClientExceptionInterface
     */
    public function call(
        string $baseUrl,
        string $method,
        string $endpoint,
        array $query = [],
        array $headers = [],
        $body = null
    ): ApiResponseInterface;
}
