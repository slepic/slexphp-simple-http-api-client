# About

A simple PSR compatible PHP HTTP API client

## Installation

Install via [Composer](https://getcomposer.org/) by running `composer require slexphp/simple-http-api-client` in your project directory.

## Public API

### Classes

#### `Slexphp\Http\SimpleApiClient\Psr\JsonClientFactory\JsonApiClient`

This class provides a static `create()` method.

It accepts a psr client and request factory and creates an instance of `ApiClientInterface` (see below)..

The `create()` method with it's first two arguments are the only part of public API of this library.

The actual returned class as well as any details behind it should not be relied upon at this point.

```
$jsonClient = JsonApiClient::create($psrRequestFactory, $psrClient);
```

### Interfaces

#### `Slexphp\Http\SimpleApiClient\Contracts\ApiClientInterface`

This is the main interface with which all your api clients will interact. 
It offers just one method with all parameters required to send a http request.

```
$client->call($baseUrl, $method, $endpoint, $query, $headers, $body);
```

It will also process request body serialization and response body deserialization
according to passed and received content-type headers.

Implementation should by default fallback to application/json content type 
request body serializer if no request content-type header is provided.
Implementation should also by default fallback to application/json content type
response body deserializer if no response content-type header is received from the server.

Nevertheless, passsing request content-type explicitly in request headers is recommended.
Because using a default request body serializer doesn't imply adding default content-type
header to the actual request.

#### `Slexphp\Http\SimpleApiClient\Contracts\ApiResponseInterface`

This interface describes the api response object.
It resembles PSR-7 `\Psr\Http\Message\ResponseInterface`, but it has no mutator methods
and it offers `getParsedBody()` method so that you dont need to worry about parsing.

#### `Slexphp\Http\SimpleApiClient\Contracts\ApiClientExceptionInterface`

This interface describes all possible errors that can occur during a http call
or during (de)serialization of request/response messages.


And all requests are hidden in a single method `call` with several arguments:
```
try {
    $response = $jsonClient->call(
        'https://myapi.com',
        'POST',
        '/some/endpoint',
        ['queryParam' => 'value'], // query
        ['Authorization' => 'Basic u:p'], // headers
        ['bodyProperty' => 'value'] // body
    );
} catch (ApiClientExceptionInterface $e) {
    $code = $e->getCode();
    var_dump($code);
    
    $response = $e->getResponse();
    if (!$response) {
        // connect error
        assert($code === 0);
        throw $e;
    }
    
    $status = $response->getStatusCode();
    var_dump($status);
    assert($code === $status);
    var_dump($response->getHeaders());
    
    if ($response->getRawBody()) {
        var_dump($response->getRawBody());
        assert($response->getRawBody() !== '');
        if ($response->getParsedBody() === null) {
            // response body parse error, any http status code
        } else {
            // non 2xx response
            assert($status < 200 || $status >= 300);
            var_dump($response->getParsedBody());
            assert(\is_array($response->getParsedBody());
        }
    } else {
        // no response body, only non 2xx responses
        assert($status < 200 || $status >= 300);
        assert($response->getRawBody() === '');
        assert($response->getParsedBody() === null);        
    }
    
    throw $e;
}

// 2xx responses succesfully parsed

$status = $response->getStatusCode();
var_dump($status);
assert($status >= 200 && $status < 300);
var_dump($response->getHeaders());

if ($response->getRawBody()) {
    var_dump($response->getRawBody());
    assert($response->getRawBody() !== '');
    var_dump($response->getParsedBody());
    assert(\is_array($response->getParsedBody());
} else {
    assert($response->getRawBody() === '');
    assert($response->getParsedBody() === null);        
}
```

The ApiClientInterface implementation is a stateless service (as long as the underlying response factory and client are).
You can use just one instance for all your backend-backend calls.
