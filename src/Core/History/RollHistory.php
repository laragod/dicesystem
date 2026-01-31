<?php

declare(strict_types=1);

namespace Laragod\DiceSystem\Core\History;

use Laragod\DiceSystem\Core\Dice\RollResult;

/**
 * RollHistory tracks and manages a collection of dice roll results.
 *
 * Features:
 * - Memory-efficient storage with configurable max size
 * - FIFO eviction when max size exceeded
 * - Filtering and querying capabilities
 * - Optional persistence adapter for long-term storage
 */
final class RollHistory
{
    /**
     * @var array<RollResult> Stored roll results in chronological order
     */
    private array $results = [];

    /**
     * @param int $maxSize Maximum number of rolls to store (default: 1000)
     * @param PersistenceAdapterInterface|null $adapter Optional persistence adapter
     */
    public function __construct(
        private int $maxSize = 1000,
        private ?PersistenceAdapterInterface $adapter = null,
    ) {
    }

    /**
     * Add a roll result to the history.
     *
     * When maxSize is exceeded, removes the oldest entries (FIFO).
     *
     * @param RollResult $result The roll result to store
     * @return void
     */
    public function add(RollResult $result): void
    {
        $this->results[] = $result;

        // Enforce maxSize by removing oldest entries
        if (count($this->results) > $this->maxSize) {
            $this->results = array_slice($this->results, -$this->maxSize);
        }
    }

    /**
     * Get all stored roll results.
     *
     * @return array<RollResult> All stored RollResult objects in chronological order
     */
    public function getAll(): array
    {
        return $this->results;
    }

    /**
     * Get the N most recent rolls.
     *
     * @param int $limit Maximum number of recent rolls to return
     * @return array<RollResult> Recent rolls, newest first
     */
    public function getRecent(int $limit): array
    {
        return array_slice($this->results, -$limit);
    }

    /**
     * Filter results using a callback predicate.
     *
     * Examples:
     * - Filter by notation: $history->filter(fn($r) => $r->notation === '2d6')
     * - Filter by total: $history->filter(fn($r) => $r->total >= 10)
     * - Filter by date: $history->filter(fn($r) => $r->rolledAt > $date)
     *
     * @param callable(RollResult): bool $predicate Filter callback
     * @return array<RollResult> Filtered results
     */
    public function filter(callable $predicate): array
    {
        return array_filter($this->results, $predicate);
    }

    /**
     * Remove all stored history.
     *
     * @return void
     */
    public function clear(): void
    {
        $this->results = [];
    }

    /**
     * Get the number of stored rolls.
     *
     * @return int Number of roll results currently stored
     */
    public function count(): int
    {
        return count($this->results);
    }
}
