<?php

declare(strict_types=1);

namespace Laragod\DiceSystem\Core\Contract;

/**
 * RandomEngineInterface defines a seeded random number generator
 * that supports deterministic, reproducible sequences.
 */
interface RandomEngineInterface
{
    /**
     * Set the seed for this random engine.
     * Once set, the seed becomes immutable and defines the sequence.
     *
     * @param int $seed The seed value to initialize the random number generator
     */
    public function setSeed(int $seed): void;

    /**
     * Get the current seed value.
     *
     * @return int|null The seed value, or null if not yet set
     */
    public function getSeed(): ?int;

    /**
     * Generate the next random integer in the sequence.
     *
     * @param int $min Minimum value (inclusive)
     * @param int $max Maximum value (inclusive)
     * @return int A random integer between $min and $max (inclusive)
     */
    public function next(int $min, int $max): int;

    /**
     * Reset the random engine to its initial seeded state.
     * This allows replaying the same sequence from the beginning.
     */
    public function reset(): void;
}
