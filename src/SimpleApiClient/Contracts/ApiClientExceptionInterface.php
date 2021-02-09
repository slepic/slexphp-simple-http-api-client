<?php

declare(strict_types=1);

namespace Slexphp\Http\SimpleApiClient\Contracts;

/**
 * Represents api client error.
 *
 * The code of the exception must be same as the response status code if there is a response object, zero otherwise.
 *
 * For network errors, there is no response object.
 *
 * For non 2xx status codes, there is a response object and it may or may not have a parsed body.
 *
 * For 2xx status codes there is a response object but it doesn't have a parsed body.
 * In other words, clients must never throw for 2xx statuses with correctly parsed bodies.
 *
 * Body parse errors and empty bodies both appear to have null parsed body.
 *
 * To distinguish the two cases see if raw body is empty.
 */
interface ApiClientExceptionInterface extends \Throwable
{
    /**
     * @return ApiResponseInterface|null
     */
    public function getResponse(): ?ApiResponseInterface;
}
