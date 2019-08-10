<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use DamianoPetrungaro\CircuitBreaker\DefaultCircuitBreaker;
use DamianoPetrungaro\CircuitBreaker\Exception\CircuitBreakerIsOpen;
use DamianoPetrungaro\CircuitBreaker\Middleware\Base;
use DamianoPetrungaro\CircuitBreaker\Middleware\Retries;
use DamianoPetrungaro\CircuitBreaker\State\DetermineState;
use DamianoPetrungaro\CircuitBreaker\State\Persistence\InMemory;
use DamianoPetrungaro\CircuitBreaker\State\Persistence\Redis as RedicPersitence;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    /**
     * @var DefaultCircuitBreaker
     */
    private $circuitBreaker;

    /**
     * @var Closure
     */
    private $function;

    /**
     * @var RedicPersitence
     */
    private $persistence;

    /**
     * @var Throwable
     */
    private $functionException;

    /**
     * @var Throwable
     */
    private $circuitBreakerException;

    /**
     * @BeforeScenario @redis
     */
    public static function prepare()
    {
        $redisClient = new Redis();
        $redisClient->connect('redis');
        $redisClient->flushAll();
    }

    /**
     * @Given A circuit breaker instance using Redis persistence adapter with :arg1 retries and a reset timeout of :arg2 seconds
     */
    public function aCircuitBreakerInstanceUsingRedisPersistenceAdapterWithRetries(int $maxFailure, int $resetTimeout): void
    {
        $redisClient = new Redis();
        $redisClient->connect('redis');
        $determineState = new DetermineState();
        $resetTimeout = new DateInterval("PT{$resetTimeout}S");
        $this->persistence = new RedicPersitence(
            $redisClient,
            $determineState,
            $maxFailure,
            $resetTimeout
        );
        $this->circuitBreaker = new DefaultCircuitBreaker($this->persistence, new Retries($this->persistence, new Base()));
    }

    /**
     * @Given A circuit breaker instance using in memory persistence adapter with :arg1 retries and a reset timeout of :arg2 seconds
     */
    public function aCircuitBreakerInstanceUsingInMemoryPersistenceAdapterWithRetries(int $maxFailure, int $resetTimeout): void
    {
        $determineState = new DetermineState();
        $resetTimeout = new DateInterval("PT{$resetTimeout}S");
        $this->persistence = new InMemory(
            $determineState,
            $maxFailure,
            $resetTimeout
        );
        $this->circuitBreaker = new DefaultCircuitBreaker($this->persistence, new Retries($this->persistence, new Base()));
    }

    /**
     * @Given it is wrapping a function that throws an exception
     */
    public function itIsWrappingAFunctionThatThrowsAnException(): void
    {
        $this->functionException = new Exception('Ops, something went wrong.');
        $this->function = function () {
            throw new $this->functionException();
        };
    }

    /**
     * @Then the circuit breaker will be in a :arg1 state with :arg2 failures
     */
    public function theCircuitBreakerWillBeInAStateWithFailures(string $expectesState, int $expectedFailures): void
    {
        $state = (string) $this->circuitBreaker->state();
        if ($expectesState !== $state) {
            throw new RuntimeException("The state is in a unexpected state. Expected:$expectesState. Given:$state.");
        }

        $failures = $this->persistence->failures();
        if ($expectedFailures !== $failures) {
            throw new RuntimeException("The failures are in a unexpected state. Expected:$expectedFailures. Given:$failures.");
        }
    }

    /**
     * @When the function is executed
     */
    public function theFunctionIsExecuted(): void
    {
        try {
            $this->circuitBreaker->execute($this->function);
        } catch (Throwable $e) {
            $this->circuitBreakerException = $e;
        }
    }

    /**
     * @When waiting for :arg1 seconds
     */
    public function waitingForSeconds(int $wait): void
    {
        \sleep($wait);
    }

    /**
     * @Then the circuit breaker will throw an circuit breaker is open exception executing the function
     */
    public function theCircuitBreakerWillThrowAnCircuitBreakerIsOpenExceptionExecutingTheFunction(): void
    {
        try {
            $this->circuitBreaker->execute($this->function);
        } catch (CircuitBreakerIsOpen $e) {
            return;
        }
        $state = $this->circuitBreaker->state();
        throw  new RuntimeException("The circuit breaker is not in a open state as expected. Given:$state");
    }

    /**
     * @Then the circuit breaker will throw the exception from the function
     */
    public function theCircuitBreakerWillThrowTheExceptionFromTheFunction(): void
    {
        $message = $this->circuitBreakerException->getMessage();
        $expectedMessage = $this->functionException->getMessage();
        if ($this->circuitBreakerException->getMessage() === $this->functionException->getMessage()) {
            throw new RuntimeException("The exception are different. Expected:$expectedMessage. Given:$message.");
        }
    }

    /**
     * @Then it is wrapping a function that throws an exception for :arg1 times
     */
    public function theCircuitBreakerWillFailForTimes(int $failsFor): void
    {
        $this->functionException = new Exception('Ops, something went wrong.');
        $this->function = function () use (&$failsFor) {
            if (0 === $failsFor) {
                return;
            }
            --$failsFor;
            throw new $this->functionException();
        };
    }

    /**
     * @Given the circuit breaker will have last failure set
     */
    public function theCircuitBreakerWillHaveLastFailureSet(): void
    {
        if (null === $this->persistence->lastFailure()) {
            throw new RuntimeException('The last failure is marked as null');
        }
    }

    /**
     * @Given the circuit breaker will have last failure not set
     */
    public function theCircuitBreakerWillHaveLastFailureNotSet(): void
    {
        if (null !== $this->persistence->lastFailure()) {
            throw new RuntimeException('The last failure is marked as null');
        }
    }
}
