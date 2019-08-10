<?php

declare(strict_types=1);

namespace DamianoPetrungaro\CircuitBreaker\State\Persistence;

use DamianoPetrungaro\CircuitBreaker\State\DetermineState;
use DamianoPetrungaro\CircuitBreaker\State\State;
use DateInterval;
use DateTimeImmutable;

final class InMemory implements Persistence
{
    /**
     * @var DetermineState
     */
    private $determineState;

    /**
     * @var int
     */
    private $maxFailures;

    /**
     * @var int
     */
    private $failures;

    /**
     * @var DateInterval
     */
    private $resetTimeout;

    /**
     * @var DateTimeImmutable|null
     */
    private $lastFailure;

    public function __construct(DetermineState $determineState, int $maxFailures, DateInterval $resetTimeout)
    {
        $this->failures = 0;
        $this->determineState = $determineState;
        $this->maxFailures = $maxFailures;
        $this->resetTimeout = $resetTimeout;
    }

    public function state(): State
    {
        return ($this->determineState)(
            $this->failures,
            $this->maxFailures,
            $this->resetTimeout,
            $this->lastFailure
        );
    }

    public function isClosed(): bool
    {
        return $this->state()->equals(State::CLOSED());
    }

    public function isHalfOpen(): bool
    {
        return $this->state()->equals(State::HALF_OPEN());
    }

    public function isOpen(): bool
    {
        return $this->state()->equals(State::OPEN());
    }

    public function failure(): void
    {
        ++$this->failures;
        $this->lastFailure = new DateTimeImmutable();
    }

    public function reset(): void
    {
        $this->failures = 0;
        $this->lastFailure = null;
    }

    public function failures(): int
    {
        return $this->failures;
    }

    public function lastFailure(): ?DateTimeImmutable
    {
        return $this->lastFailure;
    }

    public function maxFailures(): int
    {
        return $this->maxFailures;
    }

    public function resetTimeout(): DateInterval
    {
        return $this->resetTimeout;
    }
}
