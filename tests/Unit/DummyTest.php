<?php

declare(strict_types=1);

namespace DamianoPetrungaro\CircuitBreaker;

use DamianoPetrungaro\CircuitBreaker\DetermineState\DefaultDetermineState;
use DamianoPetrungaro\CircuitBreaker\DetermineState\Persistence\InMemory;
use DamianoPetrungaro\CircuitBreaker\Exception\CircuitBreakerIsOpen;
use DamianoPetrungaro\CircuitBreaker\Middleware\Base;
use DamianoPetrungaro\CircuitBreaker\Middleware\Retries;
use DateInterval;
use Exception;
use PHPUnit\Framework\TestCase;
use Throwable;

final class DummyTest extends TestCase
{
    public function test_base(): void
    {
        $reset = new DateInterval('PT5S');
        $adapter = new InMemory(7, $reset);
        $stack = new Retries($adapter, new Base());
        $cb = new DefaultCircuitBreaker(new DefaultDetermineState(), $adapter, $stack);

        \var_dump($cb->state());
        try {
            $cb->execute(static function (): void {
                \var_dump('trigger!');
                throw new Exception('ops');
            });
        } catch (Throwable $e) {
            \var_dump($e->getMessage());
        }
        \var_dump($adapter->failures());
        \var_dump($cb->state());
        \var_dump('asdasd');

        $i = 7;
        while ($i--) {
            \sleep(1);
            \var_dump($i, $cb->state());
            try {
                $cb->execute(static function (): void {
                    \var_dump('Done!');
                });
            } catch (CircuitBreakerIsOpen $e) {
                \var_dump('Still open');
            }
        }
    }
}
