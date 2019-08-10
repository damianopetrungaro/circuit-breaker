<?php

declare(strict_types=1);

namespace DamianoPetrungaro\CircuitBreaker\Middleware;

use DamianoPetrungaro\CircuitBreaker\Middleware\Exception\TooManyRetries;
use DamianoPetrungaro\CircuitBreaker\State\Persistence\Persistence;
use Throwable;

final class Retries implements Middleware
{
    /**
     * @var Persistence
     */
    private $persistence;

    /**
     * @var Middleware
     */
    private $middleware;

    public function __construct(Persistence $persistence, Middleware $middleware)
    {
        $this->persistence = $persistence;
        $this->middleware = $middleware;
    }

    public function __invoke(callable $callback)
    {
        if (!$this->persistence->isHalfOpen() &&
            $this->persistence->failures() >= $this->persistence->maxFailures()
        ) {
            throw new TooManyRetries();
        }

        try {
            $result = ($this->middleware)($callback);
        } catch (Throwable $e) {
            $this->persistence->failure();

            return $this($callback);
        }
        $this->persistence->reset();

        return $result;
    }
}
