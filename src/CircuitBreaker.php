<?php

declare(strict_types=1);

namespace DamianoPetrungaro\CircuitBreaker;

use DamianoPetrungaro\CircuitBreaker\State\State;

interface CircuitBreaker
{
    public function execute(callable $function);

    public function isOpen(): bool;

    public function isHalfOpen(): bool;

    public function isClosed(): bool;

    public function state(): State;
}
