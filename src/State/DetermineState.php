<?php

declare(strict_types=1);

namespace DamianoPetrungaro\CircuitBreaker\State;

use DateInterval;
use DateTimeImmutable;

final class DetermineState
{
    public function __invoke(
        int $failures,
        int $maxFailures,
        DateInterval $resetTimeout,
        ?DateTimeImmutable $lastFailure
    ): State {
        if ($failures < $maxFailures) {
            return State::CLOSED();
        }

        if (null !== $lastFailure && $lastFailure->add($resetTimeout) < new DateTimeImmutable()) {
            return State::HALF_OPEN();
        }

        return State::OPEN();
    }
}
