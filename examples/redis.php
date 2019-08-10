<?php

use DamianoPetrungaro\CircuitBreaker\DefaultCircuitBreaker;
use DamianoPetrungaro\CircuitBreaker\Middleware\Base;
use DamianoPetrungaro\CircuitBreaker\Middleware\Retries;
use DamianoPetrungaro\CircuitBreaker\State\DetermineState;
use DamianoPetrungaro\CircuitBreaker\State\Persistence\Redis as RedisPersistence;
use Redis as RedisClient;

$redisClient = new RedisClient();
$redisClient->connect('host');

$determineState = new DetermineState();
$resetTimeout = new DateInterval('PT10S');
$persistence = new RedisPersistence(
    $redisClient,
    $determineState,
    $maxFailure = 5,
    $resetTimeout
);

$circuitBreaker = new DefaultCircuitBreaker($persistence, new Retries($persistence, new Base()));
$circuitBreaker->execute(static function (): string {
    // Retrieve the username from the db...
    // Log possible failure...
    return 'username';
});
