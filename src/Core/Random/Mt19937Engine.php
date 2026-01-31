<?php

declare(strict_types=1);

namespace Laragod\DiceSystem\Core\Random;

use Laragod\DiceSystem\Core\Contract\RandomEngineInterface;
use LogicException;

/**
 * Mt19937Engine is a seeded random number generator using PHP's mt_rand (Mersenne Twister).
 * It provides deterministic, reproducible random sequences.
 */
class Mt19937Engine implements RandomEngineInterface
{
    /**
     * The seed value for this engine. Immutable after first set.
     */
    private ?int $seed = null;

    /**
     * Create a new Mt19937Engine instance.
     *
     * @param int|null $seed Optional seed to initialize immediately
     */
    public function __construct(?int $seed = null)
    {
        if ($seed !== null) {
            $this->setSeed($seed);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setSeed(int $seed): void
    {
        if ($this->seed !== null) {
            throw new LogicException('Seed is immutable and has already been set');
        }

        $this->seed = $seed;
        mt_srand($seed);
    }

    /**
     * {@inheritDoc}
     */
    public function getSeed(): ?int
    {
        return $this->seed;
    }

    /**
     * {@inheritDoc}
     */
    public function next(int $min, int $max): int
    {
        if ($this->seed === null) {
            throw new LogicException('Seed must be set before generating random numbers');
        }

        return mt_rand($min, $max);
    }

    /**
     * {@inheritDoc}
     */
    public function reset(): void
    {
        if ($this->seed === null) {
            throw new LogicException('Cannot reset: no seed has been set');
        }

        mt_srand($this->seed);
    }
}
