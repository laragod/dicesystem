<?php

declare(strict_types=1);

namespace Laragod\DiceSystem\Core\Parser;

use Laragod\DiceSystem\Core\Exception\InvalidNotationException;

/**
 * DiceNotationParser parses standard dice notation (XdY format).
 */
class DiceNotationParser
{
    /**
     * Parse a dice notation string into a DiceExpression.
     *
     * @param string $notation Dice notation (e.g., "2d6", "1d20", "3D8")
     * @return DiceExpression
     * @throws InvalidNotationException
     */
    public function parse(string $notation): DiceExpression
    {
        if (!preg_match('/^(\d+)d(\d+)$/i', $notation, $matches)) {
            throw new InvalidNotationException("Invalid dice notation: '{$notation}'. Expected format: XdY (e.g., 2d6)");
        }

        $quantity = (int) $matches[1];
        $sides = (int) $matches[2];

        return new DiceExpression($quantity, $sides);
    }

    /**
     * Hook for future modifier parsing (e.g., +2, -1, advantage, etc.).
     *
     * @param string $modifierString The modifier portion of the notation
     * @return array<string, mixed>
     */
    protected function parseModifiers(string $modifierString): array
    {
        // Reserved for future implementation
        return [];
    }
}
