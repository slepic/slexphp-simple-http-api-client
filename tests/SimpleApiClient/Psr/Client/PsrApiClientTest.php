<?php

declare(strict_types=1);

namespace Slexphp\Tests\Http\SimpleApiClient\Psr\Client;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Slexphp\Http\BodySerialization\TypeMap\Deserializer\BodyDeserializer;
use Slexphp\Http\BodySerialization\TypeMap\Serializer\BodySerializer;
use Slexphp\Http\SimpleApiClient\Contracts\ApiClientExceptionInterface;
use Slexphp\Http\SimpleApiClient\Contracts\ApiClientInterface;
use Slexphp\Http\SimpleApiClient\Contracts\ApiResponseInterface;
use Slexphp\Http\SimpleApiClient\Psr\Client\PsrApiClient;

class PsrApiClientTest extends TestCase
{
    public function testImplements(): void
    {
        $psrRequestFactory = self::createMock(RequestFactoryInterface::class);
        $psrClient = self::createMock(ClientInterface::class);
        $requestSerializer = self::createMock(BodySerializer::class);
        $responseDeserializer = self::createMock(BodyDeserializer::class);
        $defaultContentType = 'application/json';
        $client = new PsrApiClient(
            $psrRequestFactory,
            $psrClient,
            $requestSerializer,
            $responseDeserializer,
            $defaultContentType
        );
        self::assertInstanceOf(ApiClientInterface::class, $client);
    }

    /**
     * @param callable $setup
     * @param callable|null $successAssertion
     * @param callable|null $errorAssertion
     * @param string $baseUrl
     * @param string $method
     * @param string $endpoint
     * @param array $query
     * @param array $headers
     * @param null $body
     * @param string|null $defaultContentType
     *
     * @dataProvider provideCallData
     */
    public function testCall(
        callable $setup,
        ?callable $successAssertion,
        ?callable $errorAssertion,
        string $baseUrl,
        string $method,
        string $endpoint,
        array $query = [],
        array $headers = [],
        $body = null,
        ?string $defaultContentType = null
    ): void {
        $psrRequestFactory = self::createMock(RequestFactoryInterface::class);
        $psrClient = self::createMock(ClientInterface::class);
        $requestSerializer = self::createMock(BodySerializer::class);
        $responseDeserializer = self::createMock(BodyDeserializer::class);

        $setup($psrRequestFactory, $psrClient, $requestSerializer, $responseDeserializer);

        $client = new PsrApiClient(
            $psrRequestFactory,
            $psrClient,
            $requestSerializer,
            $responseDeserializer,
            $defaultContentType ?? 'application/json'
        );


        try {
            $response = $client->call($baseUrl, $method, $endpoint, $query, $headers, $body);
        } catch (ApiClientExceptionInterface $e) {
            if ($errorAssertion) {
                $errorAssertion($e);
            } else {
                self::assertTrue(false, 'Exception not expected:' . $e->getMessage());
            }
            return;
        }

        if ($successAssertion) {
            $successAssertion($response);
        } else {
            self::assertTrue(false, 'Expected exception not caught.');
        }
    }

    public function provideCallData(): array
    {
        return [
            [
                function (MockObject $requestFactory, MockObject $client, MockObject $requestSerializer, MockObject $responseDeserializer) {
                    $request = self::createMock(RequestInterface::class);
                    $requestFactory->expects(self::once())
                        ->method('createRequest')
                        ->with('POST', 'http://localhost/path?q=v')
                        ->willReturn($request);
                    $request->expects(self::once())
                        ->method('withHeader')
                        ->with('x-custom-header', 'header_value')
                        ->willReturn($request);

                    $requestBody = self::createMock(StreamInterface::class);
                    $request->expects(self::once())
                        ->method('getBody')
                        ->willReturn($requestBody);

                    $requestJson = '{"b":"t"}';
                    $requestSerializer->expects(self::once())
                        ->method('serializeBody')
                        ->with('some/content-type', ['body' => 'test'])
                        ->willReturn($requestJson);

                    $requestBody->expects(self::once())
                        ->method('write')
                        ->with($requestJson);

                    $response = self::createMock(ResponseInterface::class);
                    $client->expects(self::once())
                        ->method('sendRequest')
                        ->with($request)
                        ->willReturn($response);

                    $response->expects(self::once())
                        ->method('getStatusCode')
                        ->willReturn(200);

                    $response->expects(self::once())
                        ->method('getHeaders')
                        ->willReturn([]);

                    $responseBody = self::createMock(StreamInterface::class);
                    $response->expects(self::once())
                        ->method('getBody')
                        ->willReturn($responseBody);

                    $responseJson = '{"r":"d"}';
                    $responseBody->expects(self::once())
                        ->method('getSize')
                        ->willReturn(\strlen($responseJson));
                    $responseBody->expects(self::once())
                        ->method('__toString')
                        ->willReturn($responseJson);

                    $parsedResponse = ['data'];
                    $responseDeserializer->expects(self::once())
                        ->method('deserializeBody')
                        ->with('some/content-type', $responseJson)
                        ->willReturn($parsedResponse);
                },
                function (ApiResponseInterface $response) {
                    self::assertSame(200, $response->getStatusCode());
                    self::assertSame([], $response->getHeaders());
                    self::assertSame('{"r":"d"}', $response->getRawBody());
                    self::assertSame(['data'], $response->getParsedBody());
                },
                null,
                'http://localhost',
                'POST',
                '/path',
                ['q' => 'v'],
                ['x-custom-header' => 'header_value'],
                ['body' => 'test'],
                'some/content-type',
            ]
        ];
    }
}
