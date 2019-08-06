<?php

declare(strict_types=1);

namespace DamianoPetrungaro\CircuitBreaker\Middleware\Exception;

use RuntimeException;
use Throwable;

final class TooManyRetries extends RuntimeException
{
    private const ERROR_MESSAGE = 'Callback executed too many times due to failure.';

    public function __construct(Throwable $previous = null)
    {
        parent::__construct(self::ERROR_MESSAGE, $code = 0, $previous);
    }
}
