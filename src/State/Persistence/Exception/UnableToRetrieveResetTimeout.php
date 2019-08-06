<?php

declare(strict_types=1);

namespace DamianoPetrungaro\CircuitBreaker\State\Persistence\Exception;

use RuntimeException;
use Throwable;

final class UnableToRetrieveResetTimeout extends RuntimeException
{
    private const ERROR_MESSAGE = 'An error occurred retrieving the reset timeout.';

    public function __construct(Throwable $previous = null)
    {
        parent::__construct(self::ERROR_MESSAGE, $code = 0, $previous);
    }
}
