<?php

declare(strict_types=1);

namespace DamianoPetrungaro\CircuitBreaker\Middleware;

interface Middleware
{
    public function __invoke(callable $callback);
}
