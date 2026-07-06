<?php

namespace App\Services;

use RuntimeException;
use Throwable;

class PumpCommandException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $httpStatus = 500,
        public readonly string $step = 'failed',
        public readonly ?array $pump = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
