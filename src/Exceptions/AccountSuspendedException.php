<?php

namespace Rosalana\Roles\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class AccountSuspendedException extends HttpException
{
    public function __construct(string $message = 'Your account has been suspended. Please contact support.', int $code = 403, ?\Throwable $previous = null)
    {
        parent::__construct($code, $message, $previous);
    }
}