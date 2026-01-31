<?php

declare(strict_types=1);

namespace Laragod\DiceSystem\Core\Dice;

use DateTimeImmutable;

/**
 * CustomDieResult is an immutable value object representing the result of a custom die roll.
 *
 * @template T
 */
final readonly class CustomDieResult
{
    /**
     * @param T $value The rolled face value
     * @param int $faceCount The number of faces on the die
     * @param DateTimeImmutable $rolledAt The timestamp when the roll occurred
     * @param int|null $seed The seed used for the random engine, if any
     */
    public function __construct(
        public mixed $value,
        public int $faceCount,
        public DateTimeImmutable $rolledAt,
        public ?int $seed,
    ) {
    }
}
