<?php

declare(strict_types=1);

namespace Laragod\DiceSystem\Core\Dice;

use Laragod\DiceSystem\Core\Contract\RandomEngineInterface;
use Laragod\DiceSystem\Core\Parser\DiceNotationParser;
use Laragod\DiceSystem\Core\Random\SystemRandomEngine;

/**
 * DiceRoller is the main class for rolling dice using standard dice notation.
 * It parses dice notation and executes rolls, returning comprehensive results.
 */
final class DiceRoller
{
    private readonly RandomEngineInterface $engine;
    private readonly DiceNotationParser $parser;

    /**
     * Create a new DiceRoller instance.
     *
     * @param RandomEngineInterface|null $engine Optional random engine. If null, uses SystemRandomEngine.
     */
    public function __construct(?RandomEngineInterface $engine = null)
    {
        $this->engine = $engine ?? new SystemRandomEngine();
        $this->parser = new DiceNotationParser();
    }

    /**
     * Roll dice using standard dice notation.
     *
     * @param string $notation Dice notation (e.g., "2d6", "1d20", "3d8")
     * @return RollResult Complete result with all metadata
     */
    public function roll(string $notation): RollResult
    {
        // Parse the dice notation
        $expression = $this->parser->parse($notation);

        // Create individual dice and roll them
        $results = [];
        for ($i = 0; $i < $expression->quantity; $i++) {
            $die = new DieRoll($expression->sides, $this->engine);
            $results[] = $die->roll();
        }

        // Create and return the roll result
        return RollResult::fromResults($notation, $results);
    }
}
