<?php
/**
 * Example 02: Seeded Rolls for Reproducibility
 *
 * Demonstrates how to use seeded random engines to reproduce exact
 * sequences of rolls. Useful for testing and debugging.
 * Run with: php examples/02-seeded-rolls.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Laragod\DiceSystem\Core\Dice\DiceRoller;
use Laragod\DiceSystem\Core\Random\Mt19937Engine;

echo "=== PHP Dice System: Seeded Rolls ===\n\n";

// Example 1: Basic seeded roll
echo "Example 1: Basic seeded roll\n";
echo "----------------------------\n";

$engine1 = new Mt19937Engine();
$engine1->setSeed(12345);
$roller1 = new DiceRoller($engine1);

$result1a = $roller1->roll('2d6');
echo "First roll: " . $result1a . "\n";
echo "Seed: " . $result1a->seed . "\n\n";

// Example 2: Replaying the same sequence
echo "Example 2: Replaying same sequence\n";
echo "----------------------------------\n";

// Reset the engine to replay
$engine1->reset();

$result1b = $roller1->roll('2d6');
echo "After reset: " . $result1b . "\n";
echo "Same result? " . ($result1a->total === $result1b->total ? "YES" : "NO") . "\n\n";

// Example 3: Different seeds produce different sequences
echo "Example 3: Different seeds\n";
echo "---------------------------\n";

for ($seed = 1; $seed <= 5; $seed++) {
    $engine = new Mt19937Engine();
    $engine->setSeed($seed);
    $roller = new DiceRoller($engine);
    $result = $roller->roll('1d20');
    echo "Seed $seed: " . $result->total . "\n";
}
echo "\n";

// Example 4: Deterministic sequences for testing
echo "Example 4: Deterministic combat scenario\n";
echo "----------------------------------------\n";

function simulateCombat(int $seed) {
    $engine = new Mt19937Engine();
    $engine->setSeed($seed);
    $roller = new DiceRoller($engine);

    echo "Combat with seed $seed:\n";
    $initiative = $roller->roll('1d20');
    echo "  Initiative roll: " . $initiative->total . "\n";

    $attack = $roller->roll('1d20');
    echo "  Attack roll: " . $attack->total . "\n";

    if ($attack->total >= 10) {
        $damage = $roller->roll('1d8');
        echo "  Hit! Damage: " . $damage->total . "\n";
    } else {
        echo "  Miss!\n";
    }
}

simulateCombat(9999);
echo "\n";
simulateCombat(9999); // Same seed = same results
echo "\n";

// Example 5: Building reproducible game states
echo "Example 5: Reproducible game state\n";
echo "-----------------------------------\n";

class GameSession {
    private DiceRoller $roller;
    private int $seed;

    public function __construct(int $seed) {
        $this->seed = $seed;
        $engine = new Mt19937Engine();
        $engine->setSeed($seed);
        $this->roller = new DiceRoller($engine);
    }

    public function generateCharacterStats() {
        $stats = [];
        $statNames = ['Strength', 'Dexterity', 'Constitution', 'Intelligence', 'Wisdom', 'Charisma'];

        foreach ($statNames as $name) {
            // 4d6 keep 3
            $rolls = [];
            for ($i = 0; $i < 4; $i++) {
                $rolls[] = $this->roller->roll('1d6')->total;
            }
            rsort($rolls);
            $total = array_sum(array_slice($rolls, 0, 3));
            $stats[$name] = $total;
        }

        return $stats;
    }

    public function getSeed(): int {
        return $this->seed;
    }
}

// Create game session with seed
$game1 = new GameSession(55555);
$stats1 = $game1->generateCharacterStats();

echo "Character 1 (Seed: " . $game1->getSeed() . "):\n";
foreach ($stats1 as $stat => $value) {
    echo "  $stat: $value\n";
}
echo "\n";

// Create identical character with same seed
$game2 = new GameSession(55555);
$stats2 = $game2->generateCharacterStats();

echo "Character 2 (Seed: " . $game2->getSeed() . "):\n";
foreach ($stats2 as $stat => $value) {
    echo "  $stat: $value\n";
}
echo "\n";

// Compare
$identical = $stats1 === $stats2;
echo "Characters identical? " . ($identical ? "YES" : "NO") . "\n";

echo "\n=== Complete! ===\n";
