<?php

declare(strict_types=1);

namespace Slexphp\Http\SimpleApiClient\Contracts;

interface ApiResponseInterface
{
    /**
     * @return int Can be any HTTP status code.
     */
    public function getStatusCode(): int;

    /**
     * @return array<string, string> Always lowercase header keys and only one header line each
     */
    public function getHeaders(): array;

    /**
     * @param string $name Case insensitive header name
     * @return string
     */
    public function getHeaderLine(string $name): string;

    /**
     * @return string
     */
    public function getRawBody(): string;

    /**
     * @return array|null Returns parsed response body as associative array.
     *  Returns null if there is no body or it could have not been parsed.
     *  Parse error can only occur if raw body is not empty.
     *  In case of parse error the response must be returned as part of client exception
     *  and the client exception then describes the parse error message.
     */
    public function getParsedBody(): ?array;
}
