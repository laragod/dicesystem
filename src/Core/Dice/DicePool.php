<?php

declare(strict_types=1);

namespace Laragod\DiceSystem\Core\Dice;

use DateTimeImmutable;
use InvalidArgumentException;
use Laragod\DiceSystem\Core\Contract\RandomEngineInterface;

/**
 * DicePool manages a collection of dice and provides aggregate rolling operations.
 */
final class DicePool
{
    /**
     * @param array<Die> $dice Array of Die objects in the pool
     * @param RandomEngineInterface $engine The random engine to use for rolling
     * @throws InvalidArgumentException If dice array is empty
     */
    public function __construct(
        private array $dice,
        private RandomEngineInterface $engine,
    ) {
        if (empty($dice)) {
            throw new InvalidArgumentException('DicePool must contain at least one die');
        }
    }

    /**
     * Add a die to the pool.
     *
     * @param Die $die The die to add
     */
    public function add(Die $die): void
    {
        $this->dice[] = $die;
    }

    /**
     * Roll all dice in the pool and return the aggregate result.
     *
     * @return PoolResult The result of rolling all dice
     */
    public function rollAll(): PoolResult
    {
        $results = [];
        foreach ($this->dice as $die) {
            $results[] = $die->roll();
        }

        return new PoolResult(
            results: $results,
            rolledAt: new DateTimeImmutable(),
            seed: $this->engine->getSeed(),
        );
    }

    /**
     * Roll all dice and keep only the N highest or lowest results.
     *
     * @param int $n Number of dice to keep
     * @param string $mode Either 'highest' or 'lowest'
     * @return PoolResult Result containing only the kept dice
     * @throws InvalidArgumentException If mode is invalid
     */
    public function keep(int $n, string $mode = 'highest'): PoolResult
    {
        if (!in_array($mode, ['highest', 'lowest'], true)) {
            throw new InvalidArgumentException("Mode must be 'highest' or 'lowest', got '{$mode}'");
        }

        $allResults = $this->rollAll();

        $kept = match ($mode) {
            'highest' => $allResults->highest($n),
            'lowest' => $allResults->lowest($n),
        };

        return new PoolResult(
            results: $kept,
            rolledAt: $allResults->rolledAt,
            seed: $allResults->seed,
        );
    }

    /**
     * Roll all dice, then re-roll any that match the predicate.
     *
     * @param callable(DieResult): bool $predicate Function that returns true for results to reroll
     * @return PoolResult The final result after rerolling
     */
    public function reroll(callable $predicate): PoolResult
    {
        $results = [];

        foreach ($this->dice as $die) {
            $result = $die->roll();

            // If the predicate matches, re-roll once
            if ($predicate($result)) {
                $result = $die->roll();
            }

            $results[] = $result;
        }

        return new PoolResult(
            results: $results,
            rolledAt: new DateTimeImmutable(),
            seed: $this->engine->getSeed(),
        );
    }

    /**
     * Get the number of dice in the pool.
     *
     * @return int The number of dice
     */
    public function count(): int
    {
        return count($this->dice);
    }
}
