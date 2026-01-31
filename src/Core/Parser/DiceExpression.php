<?php

declare(strict_types=1);

namespace Laragod\DiceSystem\Core\Parser;

use Laragod\DiceSystem\Core\Exception\InvalidDieException;

/**
 * DiceExpression is an immutable value object representing a parsed dice notation.
 */
final readonly class DiceExpression
{
    /**
     * @param int $quantity Number of dice to roll (must be >= 1)
     * @param int $sides Number of sides per die (must be >= 2)
     * @throws InvalidDieException
     */
    public function __construct(
        public int $quantity,
        public int $sides,
    ) {
        if ($quantity < 1) {
            throw new InvalidDieException("Quantity must be at least 1, got: {$quantity}");
        }

        if ($sides < 2) {
            throw new InvalidDieException("Sides must be at least 2, got: {$sides}");
        }
    }

    /**
     * Returns the original dice notation string.
     */
    public function __toString(): string
    {
        return "{$this->quantity}d{$this->sides}";
    }
}
