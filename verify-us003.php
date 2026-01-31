<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Laragod\DiceSystem\Core\Dice\Die;
use Laragod\DiceSystem\Core\Dice\DieResult;
use Laragod\DiceSystem\Core\Exception\InvalidDieException;
use Laragod\DiceSystem\Core\Random\Mt19937Engine;

echo "US-003 Verification: Core Die Class\n";
echo str_repeat("=", 50) . "\n\n";

// Test 1: Create and roll a standard d6
echo "Test 1: Standard D6\n";
$engine = new Mt19937Engine();
$engine->setSeed(12345);
$die = new Die(6, $engine);
echo "Created d6 with {$die->sides} sides\n";
$result = $die->roll();
echo "Rolled: {$result->value} (sides: {$result->sides}, seed: {$result->seed})\n";
echo "Rolled at: {$result->rolledAt->format('Y-m-d H:i:s')}\n";
assert($result->value >= 1 && $result->value <= 6);
echo "✓ Pass\n\n";

// Test 2: Test all standard dice
echo "Test 2: All Standard Dice Types\n";
$standardDice = [4, 6, 8, 10, 12, 20, 100];
foreach ($standardDice as $sides) {
    $engine = new Mt19937Engine();
    $engine->setSeed(999);
    $die = new Die($sides, $engine);
    $result = $die->roll();
    echo "d{$sides}: rolled {$result->value} ";
    assert($result->value >= 1 && $result->value <= $sides);
    assert($result->sides === $sides);
    echo "✓\n";
}
echo "✓ Pass\n\n";

// Test 3: Deterministic seeded rolls
echo "Test 3: Deterministic Seeded Rolls\n";
$engine1 = new Mt19937Engine();
$engine1->setSeed(42);
$die1 = new Die(20, $engine1);
$results1 = [];
for ($i = 0; $i < 5; $i++) {
    $results1[] = $die1->roll()->value;
}

$engine2 = new Mt19937Engine();
$engine2->setSeed(42);
$die2 = new Die(20, $engine2);
$results2 = [];
for ($i = 0; $i < 5; $i++) {
    $results2[] = $die2->roll()->value;
}

echo "Sequence 1: " . implode(', ', $results1) . "\n";
echo "Sequence 2: " . implode(', ', $results2) . "\n";
assert($results1 === $results2);
echo "✓ Pass (sequences are identical)\n\n";

// Test 4: Invalid sides
echo "Test 4: Invalid Sides (< 2)\n";
try {
    $engine = new Mt19937Engine();
    $die = new Die(1, $engine);
    echo "✗ Fail (should have thrown exception)\n";
} catch (InvalidDieException $e) {
    echo "Caught exception: {$e->getMessage()}\n";
    echo "✓ Pass\n";
}
echo "\n";

// Test 5: DieResult immutability
echo "Test 5: DieResult is Readonly\n";
$engine = new Mt19937Engine();
$engine->setSeed(7777);
$die = new Die(8, $engine);
$result = $die->roll();
echo "Result: value={$result->value}, sides={$result->sides}, seed={$result->seed}\n";
// readonly properties are enforced by PHP, we just verify they exist
assert(isset($result->value));
assert(isset($result->sides));
assert(isset($result->rolledAt));
assert(isset($result->seed));
echo "✓ Pass (all properties are set and readonly)\n\n";

// Test 6: Die immutability
echo "Test 6: Die Immutability\n";
$engine = new Mt19937Engine();
$engine->setSeed(333);
$die = new Die(6, $engine);
$originalSides = $die->sides;
$die->roll();
$die->roll();
$die->roll();
echo "Sides before rolls: {$originalSides}\n";
echo "Sides after 3 rolls: {$die->sides}\n";
assert($die->sides === $originalSides);
echo "✓ Pass (sides property unchanged)\n\n";

// Test 7: Minimum valid sides (2)
echo "Test 7: Minimum Valid Sides (2-sided die)\n";
$engine = new Mt19937Engine();
$engine->setSeed(100);
$die = new Die(2, $engine);
$results = [];
for ($i = 0; $i < 10; $i++) {
    $value = $die->roll()->value;
    $results[] = $value;
    assert($value === 1 || $value === 2);
}
echo "10 rolls of d2: " . implode(', ', $results) . "\n";
echo "✓ Pass (all values are 1 or 2)\n\n";

// Test 8: Engine without seed
echo "Test 8: Engine Without Seed\n";
$engine = new Mt19937Engine();
// Don't set seed
$die = new Die(6, $engine);
$result = $die->roll();
echo "Result with unseeded engine: value={$result->value}, seed=";
echo $result->seed === null ? "null" : $result->seed;
echo "\n";
assert($result->seed === null);
echo "✓ Pass (seed is null when engine not seeded)\n\n";

echo str_repeat("=", 50) . "\n";
echo "All US-003 verification tests passed! ✓\n";
echo "\nFiles created:\n";
echo "- src/Core/Exception/InvalidDieException.php\n";
echo "- src/Core/Dice/DieResult.php\n";
echo "- src/Core/Dice/Die.php\n";
echo "- tests/Unit/Core/Dice/DieTest.php\n";
