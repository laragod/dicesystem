<?php
/**
 * Example 01: Basic Dice Rolling
 *
 * Demonstrates the fundamentals of rolling dice using standard notation.
 * Run with: php examples/01-basic-rolling.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Laragod\DiceSystem\Core\Dice\DiceRoller;

echo "=== PHP Dice System: Basic Rolling ===\n\n";

// Create a roller (uses secure system randomness by default)
$roller = new DiceRoller();

// Example 1: Roll a single d6
echo "Example 1: Single d6 roll\n";
echo "------------------------\n";
$result = $roller->roll('1d6');
echo "Result: " . $result . "\n";
echo "Total: " . $result->total . "\n";
echo "Notation: " . $result->notation . "\n\n";

// Example 2: Roll multiple dice (2d6)
echo "Example 2: Multiple dice (2d6)\n";
echo "------------------------------\n";
$result = $roller->roll('2d6');
echo "Result: " . $result . "\n";
echo "Individual rolls: " . implode(", ", $result->getValues()) . "\n";
echo "Total: " . $result->total . "\n\n";

// Example 3: Different dice
echo "Example 3: Various dice types\n";
echo "-----------------------------\n";
$notations = ['1d4', '1d8', '1d10', '1d12', '1d20', '1d100'];

foreach ($notations as $notation) {
    $result = $roller->roll($notation);
    echo sprintf("%8s -> Total: %3d\n", $notation, $result->total);
}
echo "\n";

// Example 4: Multiple rolls of the same type
echo "Example 4: Ten 2d6 rolls\n";
echo "------------------------\n";
$rolls = [];
for ($i = 0; $i < 10; $i++) {
    $result = $roller->roll('2d6');
    $rolls[] = $result->total;
    echo sprintf("Roll %2d: %s\n", $i + 1, $result);
}
echo "\nRolls: " . implode(", ", $rolls) . "\n";
echo "Average: " . (array_sum($rolls) / count($rolls)) . "\n";
echo "Min: " . min($rolls) . ", Max: " . max($rolls) . "\n\n";

// Example 5: Roll metadata
echo "Example 5: Roll metadata\n";
echo "------------------------\n";
$result = $roller->roll('3d8');
echo "Notation: " . $result->notation . "\n";
echo "Total: " . $result->total . "\n";
echo "Rolled at: " . $result->rolledAt->format('Y-m-d H:i:s.u') . "\n";
echo "Seed: " . ($result->seed ?? 'null (system randomness)') . "\n";

echo "\n=== Complete! ===\n";
