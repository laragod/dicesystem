<?php

declare(strict_types=1);

namespace Laragod\DiceSystem\Core\Random;

use Laragod\DiceSystem\Core\Contract\RandomEngineInterface;
use LogicException;

/**
 * SystemRandomEngine provides cryptographically secure random numbers using PHP's random_int().
 * This engine is unseeded and cannot be seeded for deterministic sequences.
 * Use this for production randomness; use Mt19937Engine for reproducible testing.
 */
final class SystemRandomEngine implements RandomEngineInterface
{
    /**
     * Set the seed for this random engine.
     * System random cannot be seeded - this will throw an exception.
     *
     * @param int $seed The seed value
     * @throws LogicException Always throws - system random cannot be seeded
     */
    public function setSeed(int $seed): void
    {
        throw new LogicException('SystemRandomEngine cannot be seeded. Use Mt19937Engine for seeded randomness.');
    }

    /**
     * Get the current seed value.
     * System random is unseeded, so this always returns null.
     *
     * @return int|null Always returns null
     */
    public function getSeed(): ?int
    {
        return null;
    }

    /**
     * Generate the next random integer using cryptographically secure randomness.
     *
     * @param int $min Minimum value (inclusive)
     * @param int $max Maximum value (inclusive)
     * @return int A random integer between $min and $max (inclusive)
     */
    public function next(int $min, int $max): int
    {
        return random_int($min, $max);
    }

    /**
     * Reset the random engine to its initial state.
     * For system random, this is a no-op since there's no state to reset.
     */
    public function reset(): void
    {
        // No-op: system random has no state to reset
    }
}
