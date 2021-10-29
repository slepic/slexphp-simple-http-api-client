<?php

declare(strict_types=1);

namespace Slexphp\Http\SimpleApiClient\Contracts;

class ApiClientException extends \Exception implements ApiClientExceptionInterface
{
    public function __construct(
        string $message,
        private ?ApiResponseInterface $response,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $response ? $response->getStatusCode() : 0, $previous);
    }

    public function getResponse(): ?ApiResponseInterface
    {
        return $this->response;
    }
}
