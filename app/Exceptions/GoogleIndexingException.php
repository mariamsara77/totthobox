<?php

namespace App\Exceptions;

use Exception;

class GoogleIndexingException extends Exception
{
    public function __construct(
        string $message,
        public readonly bool $isRetryable = true,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }
}