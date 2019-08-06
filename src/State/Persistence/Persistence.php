<?php

declare(strict_types=1);

namespace DamianoPetrungaro\CircuitBreaker\State\Persistence;

use DamianoPetrungaro\CircuitBreaker\State\State;
use DateInterval;
use DateTimeImmutable;

interface Persistence
{
    public function isClosed(): bool;

    public function isHalfOpen(): bool;

    public function isOpen(): bool;

    public function state(): State;

    public function failure(): void;

    public function reset(): void;

    public function failures(): int;

    public function lastFailure(): ?DateTimeImmutable;

    public function maxFailures(): int;

    public function resetTimeout(): DateInterval;
}
