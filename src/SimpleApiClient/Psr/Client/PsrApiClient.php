<?php

declare(strict_types=1);

namespace Slexphp\Http\SimpleApiClient\Psr\Client;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slexphp\Http\BodySerialization\Contracts\Deserializer\BodyDeserializerInterface;
use Slexphp\Http\BodySerialization\Contracts\Serializer\BodySerializerInterface;
use Slexphp\Http\SimpleApiClient\Contracts\ApiClientException;
use Slexphp\Http\SimpleApiClient\Contracts\ApiClientExceptionInterface;
use Slexphp\Http\SimpleApiClient\Contracts\ApiClientInterface;
use Slexphp\Http\SimpleApiClient\Contracts\ApiResponse;
use Slexphp\Http\SimpleApiClient\Contracts\ApiResponseInterface;

class PsrApiClient implements ApiClientInterface
{
    /**
     * @param RequestFactoryInterface $requestFactory
     * @param ClientInterface $client
     * @param BodySerializerInterface<array|object> $requestSerializer
     * @param BodyDeserializerInterface<array> $responseDeserializer
     * @param string $defaultContentType
     */
    public function __construct(
        private RequestFactoryInterface $requestFactory,
        private ClientInterface $client,
        private BodySerializerInterface $requestSerializer,
        private BodyDeserializerInterface $responseDeserializer,
        private string $defaultContentType
    ) {
    }

    public function call(
        string $baseUrl,
        string $method,
        string $endpoint,
        array $query = [],
        array $headers = [],
        array|object|string|null $body = null
    ): ApiResponseInterface {
        $psrRequest = $this->createPsrRequest($baseUrl, $method, $endpoint, $query, $headers, $body);

        try {
            $psrResponse = $this->client->sendRequest($psrRequest);
        } catch (\Throwable $e) {
            throw $this->createException($psrRequest, null, $e);
        }

        return $this->createApiResponse($psrResponse, $psrRequest);
    }

    /**
     * @param string $baseUrl
     * @param string $method
     * @param string $endpoint
     * @param array<string, mixed> $query
     * @param array<string, string> $headers
     * @param array|object|string|null $body
     * @return RequestInterface
     * @throws ApiClientExceptionInterface
     */
    private function createPsrRequest(
        string $baseUrl,
        string $method,
        string $endpoint,
        array $query = [],
        array $headers = [],
        array|object|string|null $body = null
    ): RequestInterface {
        $uri = $baseUrl
            . ($endpoint !== '' && $endpoint[0] !== '/' ? '/' : '')
            . $endpoint
            . ($query ? '?' . \http_build_query($query) : '');
        $psrRequest = $this->requestFactory->createRequest($method, $uri);

        foreach ($headers as $headerName => $headerValue) {
            /** @psalm-suppress RedundantCastGivenDocblockType cast to string if header name is numeric array-key */
            $psrRequest = $psrRequest->withHeader((string) $headerName, $headerValue);
        }

        if ($body === null) {
            return $psrRequest->withoutHeader('Content-Type');
        }

        if (\is_string($body)) {
            $serializedBody = $body;
        } else {
            $contentType = $psrRequest->getHeaderLine('Content-Type') ?: $this->defaultContentType;
            try {
                $serializedBody = $this->requestSerializer->serializeBody($contentType, $body);
            } catch (\Throwable $e) {
                throw $this->createException($psrRequest, null, $e);
            }
        }

        $psrRequest->getBody()->write($serializedBody);
        return $psrRequest;
    }

    private function createApiResponse(
        ResponseInterface $psrResponse,
        RequestInterface $psrRequest
    ): ApiResponseInterface {
        $responseHeaders = [];
        foreach (\array_keys($psrResponse->getHeaders()) as $headerName) {
            $responseHeaders[\strtolower((string) $headerName)] = $psrResponse->getHeaderLine((string) $headerName);
        }

        $status = $psrResponse->getStatusCode();
        /** @var \Throwable|null $error */
        $error = null;
        $errorMessage = null;
        $responseBody = (string) $psrResponse->getBody();
        if ($responseBody !== '') {
            $contentType = $psrResponse->getHeaderLine('Content-Type') ?: $this->defaultContentType;

            try {
                /** @var array|mixed $parsedResponse */
                $parsedResponse = $this->responseDeserializer->deserializeBody($contentType, $responseBody);
            } catch (\Throwable $error) {
                $errorMessage = 'Cannot decode response body: ' . $error->getMessage();
            }

            if (!isset($parsedResponse) || !\is_array($parsedResponse)) {
                $parsedResponse = null;
                $errorMessage = $errorMessage ?? 'Response body does not contain array or object.';
            }
        }

        /** @var array|null $parsedResponse */
        $apiResponse = new ApiResponse($status, $responseHeaders, $responseBody, $parsedResponse ?? null);

        if ($status < 200 || $status >= 300) {
            $errorMessage = \sprintf('Server returned error status code %d', $status);
        }

        if ($errorMessage !== null) {
            throw $this->createException(
                $psrRequest,
                $apiResponse,
                $error ?? null,
                $errorMessage
            );
        }

        return $apiResponse;
    }

    private function createException(
        RequestInterface $request,
        ?ApiResponseInterface $response = null,
        ?\Throwable $e = null,
        string $message = ''
    ): ApiClientExceptionInterface {
        return new ApiClientException(
            \sprintf(
                '%s %s => (%d): %s',
                $request->getMethod(),
                (string) $request->getUri(),
                $response ? $response->getStatusCode() : 0,
                $e && !$message ? $e->getMessage() : $message
            ),
            $response,
            $e
        );
    }
}
