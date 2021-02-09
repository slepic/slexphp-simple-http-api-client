<?php

declare(strict_types=1);

namespace SimpleApiClient\Psr\JsonClientFactory;

use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Slexphp\Http\SimpleApiClient\Contracts\ApiClientInterface;
use Slexphp\Http\SimpleApiClient\Psr\JsonClientFactory\JsonApiClient;

class JsonApiClientTest extends TestCase
{
    public function testCreate(): void
    {
        $psrRequestFactory = self::createMock(RequestFactoryInterface::class);
        $psrClient = self::createMock(ClientInterface::class);
        $client = JsonApiClient::create($psrRequestFactory, $psrClient);
        self::assertInstanceOf(ApiClientInterface::class, $client);
    }
}
