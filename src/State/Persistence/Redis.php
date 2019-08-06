<?php

declare(strict_types=1);

namespace DamianoPetrungaro\CircuitBreaker\State\Persistence;

use DamianoPetrungaro\CircuitBreaker\State\DetermineState;
use DamianoPetrungaro\CircuitBreaker\State\Persistence\Exception\UnableToIncreaseFailuresCounter;
use DamianoPetrungaro\CircuitBreaker\State\Persistence\Exception\UnableToResetFailuresCounter;
use DamianoPetrungaro\CircuitBreaker\State\Persistence\Exception\UnableToRetrieveFailuresCounter;
use DamianoPetrungaro\CircuitBreaker\State\Persistence\Exception\UnableToRetrieveLastFailure;
use DamianoPetrungaro\CircuitBreaker\State\Persistence\Exception\UnableToRetrieveMaxFailures;
use DamianoPetrungaro\CircuitBreaker\State\Persistence\Exception\UnableToRetrieveResetTimeout;
use DamianoPetrungaro\CircuitBreaker\State\Persistence\Exception\UnableToStoreLastFailure;
use DamianoPetrungaro\CircuitBreaker\State\Persistence\Exception\UnableToStoreMaxFailures;
use DamianoPetrungaro\CircuitBreaker\State\Persistence\Exception\UnableToStoreResetTimeout;
use DamianoPetrungaro\CircuitBreaker\State\State;
use DateInterval;
use DateTimeImmutable;
use Redis as Client;
use RuntimeException;
use Throwable;

final class Redis implements Persistence
{
    private const KEY_FAILURES = 'failures';

    private const KEY_LAST_FAILURES = 'last_failure';

    private const KEY_RESET_TIMEOUT = 'reset_timeout';

    private const KEY_MAX_FAILURES = 'max_failure';

    private const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var DetermineState
     */
    private $determineState;

    /**
     * @var string
     */
    private $failuresKey;

    /**
     * @var string
     */
    private $lastFailuresKey;

    /**
     * @var string
     */
    private $resetTimeoutKey;

    /**
     * @var string
     */
    private $maxFailuresKey;

    public function __construct(
        Client $client,
        DetermineState $determineState,
        int $maxFailures,
        DateInterval $resetTimeout,
        string $failuresKey = self::KEY_FAILURES,
        string $lastFailuresKey = self::KEY_LAST_FAILURES,
        string $resetTimeoutKey = self::KEY_RESET_TIMEOUT,
        string $maxFailuresKey = self::KEY_MAX_FAILURES
    ) {
        $this->client = $client;
        $this->determineState = $determineState;
        $this->failuresKey = $failuresKey;
        $this->lastFailuresKey = $lastFailuresKey;
        $this->resetTimeoutKey = $resetTimeoutKey;
        $this->maxFailuresKey = $maxFailuresKey;
        $this->setResetTimeout($resetTimeout);
        $this->setMaxFailures($maxFailures);
    }

    public function state(): State
    {
        return ($this->determineState)(
            $this->failures(),
            $this->maxFailures(),
            $this->resetTimeout(),
            $this->lastFailure()
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
        $lastFailure = (new DateTimeImmutable())->format(self::DATE_TIME_FORMAT);
        if (!$this->client->incr($this->failuresKey)) {
            throw new UnableToIncreaseFailuresCounter();
        }

        if (!$this->client->set($this->lastFailuresKey, $lastFailure)) {
            throw new UnableToStoreLastFailure();
        }
    }

    public function reset(): void
    {
        if (!$this->client->set($this->failuresKey, 0)) {
            throw new UnableToResetFailuresCounter();
        }
        if (0 === $this->client->delete($this->lastFailuresKey)) {
            throw new UnableToStoreLastFailure();
        }
    }

    public function failures(): int
    {
        if (!$failures = $this->client->get($this->failuresKey)) {
            throw new UnableToRetrieveFailuresCounter(new RuntimeException('key does not exists'));
        }

        if (!\is_numeric($failures)) {
            throw new UnableToRetrieveFailuresCounter(new RuntimeException('value is not an integer'));
        }

        return (int) $failures;
    }

    public function lastFailure(): ?DateTimeImmutable
    {
        if (!$lastFailure = $this->client->get($this->lastFailuresKey)) {
            return null;
        }

        try {
            return DateTimeImmutable::createFromFormat(self::DATE_TIME_FORMAT, $lastFailure);
        } catch (Throwable $e) {
            throw new UnableToRetrieveLastFailure($e);
        }
    }

    public function maxFailures(): int
    {
        if (!$failures = $this->client->get($this->maxFailuresKey)) {
            throw new UnableToRetrieveMaxFailures(new RuntimeException('key does not exists'));
        }

        if (!\is_numeric($failures)) {
            throw new UnableToRetrieveMaxFailures(new RuntimeException('value is not an integer'));
        }

        return (int) $failures;
    }

    public function resetTimeout(): DateInterval
    {
        if (!$resetTimeout = $this->client->get($this->resetTimeoutKey)) {
            throw new UnableToRetrieveResetTimeout(new RuntimeException('key does not exists'));
        }

        try {
            return new DateInterval("PT{$resetTimeout}S");
        } catch (Throwable $e) {
            throw  new UnableToRetrieveResetTimeout($e);
        }
    }

    private function setResetTimeout(DateInterval $resetTimeout): void
    {
        if (!$this->client->set($this->resetTimeoutKey, $resetTimeout->s)) {
            throw new UnableToStoreResetTimeout();
        }
    }

    /**
     * @param int $maxFailures
     */
    private function setMaxFailures(int $maxFailures): void
    {
        if (!$this->client->set($this->maxFailuresKey, $maxFailures)) {
            throw new UnableToStoreMaxFailures();
        }
    }
}
