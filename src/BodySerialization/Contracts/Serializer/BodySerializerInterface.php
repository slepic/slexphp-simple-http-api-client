<?php

declare(strict_types=1);

namespace Slexphp\Http\BodySerialization\Contracts\Serializer;

/**
 * @template T of array|object
 */
interface BodySerializerInterface
{
    /**
     * Serializes an object representation of type T into string given a desired content type
     *
     * @param string $contentType
     * @param array|object $parsedBody
     * @return string
     *
     * @throws BodySerializationExceptionInterface
     *
     * @template-param T $parsedBody
     */
    public function serializeBody(string $contentType, $parsedBody): string;
}
