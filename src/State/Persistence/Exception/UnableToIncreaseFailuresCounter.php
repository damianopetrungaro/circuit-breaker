<?php

declare(strict_types=1);

namespace DamianoPetrungaro\CircuitBreaker\State\Persistence\Exception;

use RuntimeException;
use Throwable;

final class UnableToIncreaseFailuresCounter extends RuntimeException
{
    private const ERROR_MESSAGE = 'An error occurred increasing the failures counter.';

    public function __construct(Throwable $previous = null)
    {
        parent::__construct(self::ERROR_MESSAGE, $code = 0, $previous);
    }
}
