# Circuit breaker

This is a [circuit breaker](https://en.wikipedia.org/wiki/Circuit_breaker) implementation for PHP applications.

The library is tested with integration and functional tests in order to make you feel more comfortable about it.

Later unit tests will be added too. 

# Installation

`php composer.phar require damianopetrungaro/circuit-breaker`

## How does it work?

This circuit breaker implementation wrap the function that you want to execute inside a callable (a function or an invokable object).

The function is then passed across the different middleware stages that an be used.

It's really easy to add your own custom middleware (such as a delay or even logging).
Take a look to the Retry middleware as an example.   

## Example

This is a basic example using an in memory adapter with a retry middleware.

```php
<?php

use DamianoPetrungaro\CircuitBreaker\DefaultCircuitBreaker;
use DamianoPetrungaro\CircuitBreaker\Middleware\Base;
use DamianoPetrungaro\CircuitBreaker\Middleware\Retries;
use DamianoPetrungaro\CircuitBreaker\State\DetermineState;
use DamianoPetrungaro\CircuitBreaker\State\Persistence\InMemory;

$determineState = new DetermineState();
$resetTimeout = new DateInterval('PT10S');
$persistence = new InMemory($determineState,$maxFailure = 5,$resetTimeout);
$circuitBreaker = new DefaultCircuitBreaker($persistence, new Retries($persistence, new Base()));
$circuitBreaker->execute(static function (): string {
    // Retrieve the username from the db...
    // Log possible failure...
    return 'username';
});
```

You can find more examples in the `examples` directory.
