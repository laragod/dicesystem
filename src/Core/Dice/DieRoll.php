<?php

declare(strict_types=1);

namespace Laragod\DiceSystem\Core\Dice;

use DateTimeImmutable;
use Laragod\DiceSystem\Core\Contract\RandomEngineInterface;
use Laragod\DiceSystem\Core\Exception\InvalidDieException;

/**
 * Die represents a single die with a specific number of sides.
 * It uses a RandomEngineInterface to generate random values.
 */
final class DieRoll
{
    /**
     * @param int $sides The number of sides on the die (minimum 2)
     * @param RandomEngineInterface $engine The random engine to use for rolling
     * @throws InvalidDieException If sides is less than 2
     */
    public function __construct(
        public readonly int $sides,
        private RandomEngineInterface $engine,
    ) {
        if ($sides < 2) {
            throw new InvalidDieException(
                "Die must have at least 2 sides, got {$sides}"
            );
        }
    }

    /**
     * Roll the die and return the result.
     *
     * @return DieResult The result of the roll with metadata
     */
    public function roll(): DieResult
    {
        $value = $this->engine->next(1, $this->sides);

        return new DieResult(
            value: $value,
            sides: $this->sides,
            rolledAt: new DateTimeImmutable(),
            seed: $this->engine->getSeed(),
        );
    }
}
