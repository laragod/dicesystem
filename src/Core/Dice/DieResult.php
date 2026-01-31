<?php

declare(strict_types=1);

namespace Laragod\DiceSystem\Core\Dice;

use DateTimeImmutable;

/**
 * DieResult is an immutable value object representing the result of a single die roll.
 */
final readonly class DieResult
{
    /**
     * @param int $value The rolled value (between 1 and sides)
     * @param int $sides The number of sides on the die
     * @param DateTimeImmutable $rolledAt The timestamp when the roll occurred
     * @param int|null $seed The seed used for the random engine, if any
     */
    public function __construct(
        public int $value,
        public int $sides,
        public DateTimeImmutable $rolledAt,
        public ?int $seed,
    ) {
    }
}
