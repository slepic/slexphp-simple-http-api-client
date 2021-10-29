<?php

declare(strict_types=1);

namespace Slexphp\Http\BodySerialization\TypeMap\Serializer;

use Slexphp\Http\BodySerialization\Contracts\Serializer\BodySerializationException;
use Slexphp\Http\BodySerialization\Contracts\Serializer\BodySerializerInterface;
use Slexphp\Serialization\Contracts\Encoder\EncoderInterface;

/**
 * @template T of array|object
 * @template-implements BodySerializerInterface<T>
 */
class BodySerializer implements BodySerializerInterface
{
    /**
     * @param array<string, EncoderInterface<T>> $serializers
     */
    public function __construct(private array $serializers)
    {
    }

    public function serializeBody(string $contentType, $parsedBody): string
    {
        $encoder = $this->getSerializer($contentType);

        try {
            return $encoder->encode($parsedBody);
        } catch (\Throwable $e) {
            throw new BodySerializationException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    private function getSerializer(string $contentType): EncoderInterface
    {
        if (!isset($this->serializers[$contentType])) {
            foreach ($this->serializers as $serializerType => $serializer) {
                if (\strpos($contentType, $serializerType) === 0) {
                    return $serializer;
                }
            }
            throw new BodySerializationException(\sprintf('No encoder for content type "%s"', $contentType));
        }

        return $this->serializers[$contentType];
    }
}
