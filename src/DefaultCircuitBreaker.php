<?php

declare(strict_types=1);

namespace DamianoPetrungaro\CircuitBreaker;

use DamianoPetrungaro\CircuitBreaker\Exception\CircuitBreakerIsOpen;
use DamianoPetrungaro\CircuitBreaker\Middleware\Middleware;
use DamianoPetrungaro\CircuitBreaker\State\Persistence\Persistence;
use DamianoPetrungaro\CircuitBreaker\State\State;

final class DefaultCircuitBreaker implements CircuitBreaker
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

    public function state(): State
    {
        return $this->persistence->state();
    }

    public function isClosed(): bool
    {
        return $this->persistence->isClosed();
    }

    public function isHalfOpen(): bool
    {
        return $this->persistence->isHalfOpen();
    }

    public function isOpen(): bool
    {
        return $this->persistence->isOpen();
    }

    public function execute(callable $function)
    {
        $state = $this->persistence->state();

        if ($state->equals(State::OPEN())) {
            throw new CircuitBreakerIsOpen();
        }

        return ($this->middleware)($function);
    }
}
