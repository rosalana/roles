<?php

namespace Rosalana\Roles\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class InsufficientRoleException extends HttpException
{
    public function __construct(string $message = 'You do not have sufficient permissions to access this resource.', int $statusCode = 403, ?\Throwable $previous = null)
    {
        parent::__construct($statusCode, $message, $previous);
    }
}
