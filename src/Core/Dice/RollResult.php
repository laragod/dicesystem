<?php

declare(strict_types=1);

namespace Laragod\DiceSystem\Core\Dice;

use DateTimeImmutable;

/**
 * RollResult is an immutable value object representing the result of rolling multiple dice.
 */
final readonly class RollResult
{
    /**
     * @param string $notation The original dice notation (e.g., "2d6")
     * @param array<DieResult> $individualResults Array of individual die roll results
     * @param int $total The sum of all rolled values
     * @param DateTimeImmutable $rolledAt The timestamp when the roll occurred
     * @param int|null $seed The seed used for the random engine, if any
     */
    public function __construct(
        public string $notation,
        public array $individualResults,
        public int $total,
        public DateTimeImmutable $rolledAt,
        public ?int $seed,
    ) {
    }

    /**
     * Factory method to create a RollResult from individual die results.
     * Automatically calculates the total from the individual results.
     *
     * @param string $notation The original dice notation
     * @param array<DieResult> $individualResults Array of individual die roll results
     * @return self
     */
    public static function fromResults(string $notation, array $individualResults): self
    {
        $total = array_reduce(
            $individualResults,
            fn(int $sum, DieResult $result) => $sum + $result->value,
            0
        );

        $rolledAt = !empty($individualResults)
            ? $individualResults[0]->rolledAt
            : new DateTimeImmutable();

        $seed = !empty($individualResults)
            ? $individualResults[0]->seed
            : null;

        return new self(
            notation: $notation,
            individualResults: $individualResults,
            total: $total,
            rolledAt: $rolledAt,
            seed: $seed,
        );
    }

    /**
     * Get just the integer values from all die results.
     *
     * @return array<int>
     */
    public function getValues(): array
    {
        return array_map(
            fn(DieResult $result) => $result->value,
            $this->individualResults
        );
    }

    /**
     * String representation of the roll result.
     * Format: "2d6: [3, 5] = 8"
     */
    public function __toString(): string
    {
        $values = implode(', ', $this->getValues());
        return "{$this->notation}: [{$values}] = {$this->total}";
    }
}
