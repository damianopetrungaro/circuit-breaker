<?php

declare(strict_types=1);

namespace DamianoPetrungaro\CircuitBreaker\Exception;

use SebastianBergmann\CodeCoverage\RuntimeException;

final class CircuitBreakerIsOpen extends RuntimeException
{
    private const ERROR_MESSAGE = 'The circuit breaker is open.';

    public function __construct()
    {
        parent::__construct(self::ERROR_MESSAGE);
    }
}
