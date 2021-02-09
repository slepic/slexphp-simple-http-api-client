<?php

declare(strict_types=1);

namespace Slexphp\Http\SimpleApiClient\Psr\JsonClientFactory;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Slexphp\Http\BodySerialization\TypeMap\Deserializer\BodyDeserializer;
use Slexphp\Http\BodySerialization\TypeMap\Serializer\BodySerializer;
use Slexphp\Http\SimpleApiClient\Contracts\ApiClientInterface;
use Slexphp\Http\SimpleApiClient\Psr\Client\PsrApiClient;
use Slexphp\Serialization\Contracts\Decoder\DecoderInterface;
use Slexphp\Serialization\Contracts\Encoder\EncoderInterface;
use Slexphp\Serialization\Json\Decoder\JsonAssocDecoder;
use Slexphp\Serialization\Json\Decoder\JsonDecoder;
use Slexphp\Serialization\Json\Encoder\JsonEncoder;

final class JsonApiClient
{
    /**
     * @param RequestFactoryInterface $psrRequestFactory
     * @param ClientInterface $psrClient
     * @param EncoderInterface<array|object>|null $jsonEncoder
     * @param DecoderInterface<array>|null $jsonDecoder
     * @param string|null $jsonContentType
     * @return ApiClientInterface
     */
    public static function create(
        RequestFactoryInterface $psrRequestFactory,
        ClientInterface $psrClient,
        ?EncoderInterface $jsonEncoder = null,
        ?DecoderInterface $jsonDecoder = null,
        ?string $jsonContentType = null
    ): ApiClientInterface {
        $jsonContentType = $jsonContentType ?: 'application/json';

        /** @var EncoderInterface<array|object> */
        $jsonEncoder = $jsonEncoder ?? new JsonEncoder();

        /** @var DecoderInterface<array> */
        $jsonDecoder = $jsonDecoder ?? new JsonAssocDecoder();

        /** @var BodySerializer<array|object> */
        $serializer = new BodySerializer([$jsonContentType => $jsonEncoder]);

        /** @var BodyDeserializer<array> */
        $deserializer = new BodyDeserializer([$jsonContentType => $jsonDecoder]);

        return new PsrApiClient(
            $psrRequestFactory,
            $psrClient,
            $serializer,
            $deserializer,
            $jsonContentType
        );
    }
}
