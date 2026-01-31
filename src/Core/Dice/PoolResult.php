<?php

declare(strict_types=1);

namespace Laragod\DiceSystem\Core\Dice;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * PoolResult is an immutable value object representing the result of rolling multiple dice.
 */
final readonly class PoolResult
{
    /**
     * @param array<DieResult> $results Array of individual die results
     * @param DateTimeImmutable $rolledAt The timestamp when the pool was rolled
     * @param int|null $seed The seed used for the random engine, if any
     * @throws InvalidArgumentException If results array is empty
     */
    public function __construct(
        public array $results,
        public DateTimeImmutable $rolledAt,
        public ?int $seed,
    ) {
        if (empty($results)) {
            throw new InvalidArgumentException('PoolResult must contain at least one die result');
        }
    }

    /**
     * Calculate the sum of all die values in the pool.
     *
     * @return int The total of all die values
     */
    public function sum(): int
    {
        return array_sum($this->getValues());
    }

    /**
     * Calculate the average of all die values in the pool.
     *
     * @return float The mean of all die values
     */
    public function average(): float
    {
        return $this->sum() / count($this->results);
    }

    /**
     * Get the N highest die results from the pool.
     *
     * @param int $n Number of highest results to return (default 1)
     * @return array<DieResult> Array of the N highest die results, sorted descending
     */
    public function highest(int $n = 1): array
    {
        $sorted = $this->results;
        usort($sorted, fn(DieResult $a, DieResult $b) => $b->value <=> $a->value);
        return array_slice($sorted, 0, $n);
    }

    /**
     * Get the N lowest die results from the pool.
     *
     * @param int $n Number of lowest results to return (default 1)
     * @return array<DieResult> Array of the N lowest die results, sorted ascending
     */
    public function lowest(int $n = 1): array
    {
        $sorted = $this->results;
        usort($sorted, fn(DieResult $a, DieResult $b) => $a->value <=> $b->value);
        return array_slice($sorted, 0, $n);
    }

    /**
     * Get just the integer values from all die results.
     *
     * @return array<int> Array of die values
     */
    public function getValues(): array
    {
        return array_map(fn(DieResult $r) => $r->value, $this->results);
    }
}
