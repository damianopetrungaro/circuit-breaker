<?php

declare(strict_types=1);

namespace DamianoPetrungaro\CircuitBreaker\Middleware;

final class Base implements Middleware
{
    public function __invoke(callable $callback)
    {
        return $callback();
    }
}
