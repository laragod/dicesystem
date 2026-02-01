<?php
/**
 * Example 04: Dice Pools and Aggregate Operations
 *
 * Demonstrates how to use dice pools for complex operations like
 * keeping highest/lowest, conditional rerolling, and aggregates.
 * Run with: php examples/04-dice-pools.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Laragod\DiceSystem\Core\Dice\DieRoll;
use Laragod\DiceSystem\Core\Dice\DicePool;
use Laragod\DiceSystem\Core\Random\SystemRandomEngine;
use Laragod\DiceSystem\Core\Random\Mt19937Engine;

echo "=== PHP Dice System: Dice Pools ===\n\n";

// Example 1: Basic pool rolling
echo "Example 1: Basic pool rolling\n";
echo "-----------------------------\n";

$engine = new SystemRandomEngine();

// Create 3d6
$dice = [
    new DieRoll(6, $engine),
    new DieRoll(6, $engine),
    new DieRoll(6, $engine),
];

$pool = new DicePool($dice, $engine);
$result = $pool->rollAll();

echo "3d6 roll: " . implode(', ', $result->getValues()) . "\n";
echo "Sum: " . $result->sum() . "\n";
echo "Average: " . number_format($result->average(), 2) . "\n\n";

// Example 2: Keep highest (advantage mechanic)
echo "Example 2: Keep highest (D&D advantage)\n";
echo "--------------------------------------\n";

$engine = new SystemRandomEngine();
$d20Dice = [
    new DieRoll(20, $engine),
    new DieRoll(20, $engine),
];

$pool = new DicePool($d20Dice, $engine);

for ($i = 0; $i < 5; $i++) {
    $result = $pool->keep(1, 'highest');
    echo "Roll " . ($i + 1) . ": " . implode(', ', $result->getValues()) . " -> Highest: " . $result->sum() . "\n";
}
echo "\n";

// Example 3: 4d6 keep 3 (D&D ability scores)
echo "Example 3: Ability score generation (4d6 keep 3)\n";
echo "---------------------------------------------\n";

$engine = new SystemRandomEngine();
$stats = ['Strength', 'Dexterity', 'Constitution', 'Intelligence', 'Wisdom', 'Charisma'];

foreach ($stats as $stat) {
    $dice = [];
    for ($i = 0; $i < 4; $i++) {
        $dice[] = new DieRoll(6, $engine);
    }

    $pool = new DicePool($dice, $engine);
    $result = $pool->keep(3, 'highest');

    echo sprintf("%-12s: %s -> %d\n", $stat, implode(', ', $result->getValues()), $result->sum());
}
echo "\n";

// Example 4: Keep lowest
echo "Example 4: Keep lowest\n";
echo "---------------------\n";

$engine = new SystemRandomEngine();
$dice = [
    new DieRoll(20, $engine),
    new DieRoll(20, $engine),
    new DieRoll(20, $engine),
];

$pool = new DicePool($dice, $engine);
$result = $pool->keep(1, 'lowest');

echo "3d20, keep lowest: " . implode(', ', $result->getValues()) . " -> " . $result->sum() . "\n";
echo "Lowest results:\n";
foreach ($result->results as $die) {
    echo "  d20: " . $die->value . "\n";
}
echo "\n";

// Example 5: Conditional rerolling (reroll 1s)
echo "Example 5: Reroll 1s\n";
echo "-------------------\n";

$engine = new SystemRandomEngine();
$dice = [
    new DieRoll(6, $engine),
    new DieRoll(6, $engine),
    new DieRoll(6, $engine),
];

$pool = new DicePool($dice, $engine);

for ($i = 0; $i < 5; $i++) {
    $result = $pool->reroll(fn($die) => $die->value === 1);
    echo "Roll " . ($i + 1) . ": " . implode(', ', $result->getValues()) . " (sum: " . $result->sum() . ")\n";
}
echo "\n";

// Example 6: Reroll below threshold
echo "Example 6: Reroll below 2\n";
echo "------------------------\n";

$engine = new SystemRandomEngine();
$dice = [
    new DieRoll(6, $engine),
    new DieRoll(6, $engine),
];

$pool = new DicePool($dice, $engine);

for ($i = 0; $i < 5; $i++) {
    $result = $pool->reroll(fn($die) => $die->value < 2);
    echo "Roll " . ($i + 1) . ": " . implode(', ', $result->getValues()) . "\n";
}
echo "\n";

// Example 7: Complex reroll condition
echo "Example 7: Reroll even numbers\n";
echo "------------------------------\n";

$engine = new SystemRandomEngine();
$dice = [
    new DieRoll(6, $engine),
    new DieRoll(6, $engine),
    new DieRoll(6, $engine),
];

$pool = new DicePool($dice, $engine);

$result = $pool->reroll(fn($die) => $die->value % 2 === 0);
echo "Original rolls after rerolling evens: " . implode(', ', $result->getValues()) . "\n";
echo "Sum: " . $result->sum() . "\n\n";

// Example 8: Adding dice dynamically
echo "Example 8: Building pool dynamically\n";
echo "-----------------------------------\n";

$engine = new SystemRandomEngine();
$pool = new DicePool([new DieRoll(6, $engine)], $engine);

echo "Starting with 1d6\n";

for ($i = 2; $i <= 5; $i++) {
    $pool->add(new DieRoll(6, $engine));
    $result = $pool->rollAll();
    echo "After adding die: {$i}d6 -> Sum: " . $result->sum() . "\n";
}
echo "\n";

// Example 9: Seeded pools for reproducibility
echo "Example 9: Seeded pool rolls\n";
echo "---------------------------\n";

$engine = new Mt19937Engine();
$engine->setSeed(999);

$dice = [
    new DieRoll(6, $engine),
    new DieRoll(6, $engine),
    new DieRoll(6, $engine),
];

$pool = new DicePool($dice, $engine);
$result1 = $pool->keep(2, 'highest');
echo "First roll (seed 999): " . implode(', ', $result1->getValues()) . " -> " . $result1->sum() . "\n";

// Reset and replay
$engine->reset();
$result2 = $pool->keep(2, 'highest');
echo "Replayed roll: " . implode(', ', $result2->getValues()) . " -> " . $result2->sum() . "\n";
echo "Same result? " . ($result1->sum() === $result2->sum() ? "YES" : "NO") . "\n\n";

// Example 10: Pool with highest and lowest access
echo "Example 10: Accessing highest and lowest\n";
echo "---------------------------------------\n";

$engine = new SystemRandomEngine();
$dice = [];
for ($i = 0; $i < 5; $i++) {
    $dice[] = new DieRoll(6, $engine);
}

$pool = new DicePool($dice, $engine);
$result = $pool->rollAll();

echo "5d6 roll: " . implode(', ', $result->getValues()) . "\n";
echo "Highest: " . implode(', ', array_map(fn($d) => $d->value, $result->highest(2))) . "\n";
echo "Lowest: " . implode(', ', array_map(fn($d) => $d->value, $result->lowest(2))) . "\n";

echo "\n=== Complete! ===\n";
