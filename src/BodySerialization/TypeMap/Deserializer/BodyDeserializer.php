<?php

declare(strict_types=1);

namespace Slexphp\Http\BodySerialization\TypeMap\Deserializer;

use Slexphp\Http\BodySerialization\Contracts\Deserializer\BodyDeserializationException;
use Slexphp\Http\BodySerialization\Contracts\Deserializer\BodyDeserializerInterface;
use Slexphp\Serialization\Contracts\Decoder\DecoderInterface;

/**
 * @template T of array|object
 * @template-implements BodyDeserializerInterface<T>
 */
class BodyDeserializer implements BodyDeserializerInterface
{
    /**
     * @var array<string, DecoderInterface<T>>
     */
    private array $deserializers;

    /**
     * @param array<string, DecoderInterface<T>> $deserializers
     */
    public function __construct(array $deserializers)
    {
        $this->deserializers = $deserializers;
    }

    public function deserializeBody(string $contentType, string $rawBody)
    {
        $deserializer = $this->getDeserializer($contentType);
        try {
            return $deserializer->decode($rawBody);
        } catch (\Throwable $e) {
            throw new BodyDeserializationException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * @param string $contentType
     * @return DecoderInterface<T>
     * @throws BodyDeserializationException
     */
    private function getDeserializer(string $contentType): DecoderInterface
    {
        if (!isset($this->deserializers[$contentType])) {
            foreach ($this->deserializers as $deserializerType => $deserializer) {
                if (\strpos($contentType, $deserializerType) === 0) {
                    return $deserializer;
                }
            }
            throw new BodyDeserializationException(\sprintf('No decoder for content type "%s"', $contentType));
        }

        return $this->deserializers[$contentType];
    }
}
