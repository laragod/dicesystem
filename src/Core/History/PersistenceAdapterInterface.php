<?php

declare(strict_types=1);

namespace Laragod\DiceSystem\Core\History;

/**
 * PersistenceAdapterInterface defines contract for persisting roll history.
 *
 * This interface enables future implementations for file storage, databases,
 * or other persistence mechanisms without changing the RollHistory class.
 */
interface PersistenceAdapterInterface
{
    /**
     * Save roll results to persistent storage.
     *
     * @param string $key Storage identifier (e.g., session ID, user ID)
     * @param array<\Laragod\DiceSystem\Core\Dice\RollResult> $results Array of RollResult objects to save
     * @return void
     */
    public function save(string $key, array $results): void;

    /**
     * Load roll results from persistent storage.
     *
     * @param string $key Storage identifier
     * @return array<\Laragod\DiceSystem\Core\Dice\RollResult> Array of RollResult objects
     */
    public function load(string $key): array;
}
