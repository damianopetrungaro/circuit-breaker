<?php

declare(strict_types=1);

namespace DamianoPetrungaro\CircuitBreaker\State;

use MyCLabs\Enum\Enum;

/**
 * @method static State CLOSED()
 * @method static State HALF_OPEN()
 * @method static State OPEN()
 */
final class State extends Enum
{
    private const CLOSED = 'closed';

    private const HALF_OPEN = 'half_open';

    private const OPEN = 'open';
}
