<?php

namespace JobBookers\Routing;

class JobBookersException extends \RuntimeException
{
    private string $errorCode;

    public function __construct(string $message, string $code = 'unknown', int $status = 0)
    {
        parent::__construct($message, $status);
        $this->errorCode = $code;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getStatus(): int
    {
        return $this->getCode();
    }
}

class AuthenticationException extends JobBookersException {}
class InvalidPostcodeException extends JobBookersException {}
class NotFoundException extends JobBookersException {}
class RateLimitException extends JobBookersException {}
class ServerException extends JobBookersException {}
