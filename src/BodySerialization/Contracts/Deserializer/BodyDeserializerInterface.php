<?php

declare(strict_types=1);

namespace Slexphp\Http\BodySerialization\Contracts\Deserializer;

/**
 * @template T
 */
interface BodyDeserializerInterface
{
    /**
     * Deserializes a string into type T instance assuming the input string has given content type.
     *
     * @param string $contentType
     * @param string $rawBody
     * @return mixed
     * @throws BodyDeserializationExceptionInterface
     * @template-return T
     */
    public function deserializeBody(string $contentType, string $rawBody);
}
