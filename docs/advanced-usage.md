# Advanced Usage

Master advanced features including seeded rolls, custom dice, pools, history tracking, and statistical analysis.

## Table of Contents

- [Seeded Rolls for Reproducibility](#seeded-rolls-for-reproducibility)
- [Custom Dice with Non-Numeric Faces](#custom-dice-with-non-numeric-faces)
- [Dice Pools and Aggregate Operations](#dice-pools-and-aggregate-operations)
- [Roll History Tracking](#roll-history-tracking)
- [Statistical Analysis](#statistical-analysis)

## Seeded Rolls for Reproducibility

For testing, debugging, or creating deterministic game sequences, use the `Mt19937Engine` with a seed:

### Creating a Seeded Roller

```php
<?php
require 'vendor/autoload.php';

use Laragod\DiceSystem\Core\Dice\DiceRoller;
use Laragod\DiceSystem\Core\Random\Mt19937Engine;

// Create a seeded random engine
$engine = new Mt19937Engine();
$engine->setSeed(12345);

// Create a roller with the seeded engine
$roller = new DiceRoller($engine);

// Now all rolls are reproducible
$result1 = $roller->roll('2d6');
echo "First roll: " . $result1; // e.g., "2d6: [5, 3] = 8"

// If we reset the engine and roll again, we get the same sequence
$engine->reset();
$result2 = $roller->roll('2d6');
echo "Second roll: " . $result2; // Same as first roll!
```

### Replaying Rolls

You can use the same seed to replay an entire sequence of rolls:

```php
<?php
require 'vendor/autoload.php';

use Laragod\DiceSystem\Core\Dice\DiceRoller;
use Laragod\DiceSystem\Core\Random\Mt19937Engine;

// Function to record rolls with a seed
function recordCombatRound(int $seed) {
    $engine = new Mt19937Engine();
    $engine->setSeed($seed);
    $roller = new DiceRoller($engine);

    $rolls = [];
    $rolls[] = $roller->roll('1d20'); // Initiative
    $rolls[] = $roller->roll('2d6');  // Attack
    $rolls[] = $roller->roll('1d8');  // Damage

    return $rolls;
}

// Record with seed 999
$roundA = recordCombatRound(999);
echo "Round A: " . implode(", ", $roundA) . "\n";

// Replay with same seed
$roundB = recordCombatRound(999);
echo "Round B: " . implode(", ", $roundB) . "\n";

// roundA and roundB will be identical!
```

### Accessing the Seed

Every roll result includes the seed that was used:

```php
<?php
require 'vendor/autoload.php';

use Laragod\DiceSystem\Core\Dice\DiceRoller;
use Laragod\DiceSystem\Core\Random\Mt19937Engine;

$engine = new Mt19937Engine();
$engine->setSeed(54321);

$roller = new DiceRoller($engine);
$result = $roller->roll('2d6');

// Access the seed from the result
echo "This roll used seed: " . $result->seed; // Output: 54321

// With system randomness (no seed)
$systemRoller = new DiceRoller();
$systemResult = $systemRoller->roll('2d6');
echo "Seed: " . ($systemResult->seed ?? 'null'); // Output: null
```

## Custom Dice with Non-Numeric Faces

Create dice with custom face values like cards, attributes, or text:

### Basic Custom Dice

```php
<?php
require 'vendor/autoload.php';

use Laragod\DiceSystem\Core\Dice\CustomDie;
use Laragod\DiceSystem\Core\Random\SystemRandomEngine;

// Create a coin (two faces)
$coin = new CustomDie(['Heads', 'Tails'], new SystemRandomEngine());
$result = $coin->roll();
echo "Coin: " . $result->value; // Output: "Heads" or "Tails"

// Create a playing card die
$cardDie = new CustomDie(
    ['2♠', '3♠', '4♠', '5♠', '6♠', '7♠', '8♠', '9♠', '10♠', 'J♠', 'Q♠', 'K♠', 'A♠'],
    new SystemRandomEngine()
);
$card = $cardDie->roll();
echo "Card: " . $card->value; // Output: e.g., "Q♠"
```

### Custom Dice with Seeded Rolls

```php
<?php
require 'vendor/autoload.php';

use Laragod\DiceSystem\Core\Dice\CustomDie;
use Laragod\DiceSystem\Core\Random\Mt19937Engine;

$engine = new Mt19937Engine();
$engine->setSeed(777);

// Create a custom die
$elements = new CustomDie(['Fire', 'Water', 'Earth', 'Air', 'Lightning'], $engine);

// Roll element assignment
$result = $elements->roll();
echo "Element: " . $result->value . "\n"; // Reproducible with seed

// Access metadata
echo "Face count: " . $result->faceCount . "\n"; // 5
echo "Rolled at: " . $result->rolledAt->format('Y-m-d H:i:s') . "\n";
echo "Seed: " . $result->seed . "\n"; // 777
```

### Getting Available Faces

```php
<?php
require 'vendor/autoload.php';

use Laragod\DiceSystem\Core\Dice\CustomDie;
use Laragod\DiceSystem\Core\Random\SystemRandomEngine;

$colors = new CustomDie(['Red', 'Green', 'Blue', 'Yellow'], new SystemRandomEngine());

// Get all faces
$faces = $colors->getFaces();
print_r($faces);
// Array ( [0] => Red [1] => Green [2] => Blue [3] => Yellow )
```

## Dice Pools and Aggregate Operations

Manage collections of dice and perform complex operations like "keep highest" or "reroll ones":

### Creating and Rolling a Pool

```php
<?php
require 'vendor/autoload.php';

use Laragod\DiceSystem\Core\Dice\Die;
use Laragod\DiceSystem\Core\Dice\DicePool;
use Laragod\DiceSystem\Core\Random\SystemRandomEngine;

$engine = new SystemRandomEngine();

// Create dice
$dice = [
    new Die(6, $engine),
    new Die(6, $engine),
    new Die(6, $engine),
];

// Create and roll the pool
$pool = new DicePool($dice, $engine);
$result = $pool->rollAll();

echo "Total: " . $result->sum() . "\n";        // Sum of all dice
echo "Average: " . $result->average() . "\n";  // Average per die
print_r($result->getValues());                 // Array of individual values
```

### Keep Highest/Lowest

Useful for D&D ability scores (4d6 keep 3) or other game mechanics:

```php
<?php
require 'vendor/autoload.php';

use Laragod\DiceSystem\Core\Dice\Die;
use Laragod\DiceSystem\Core\Dice\DicePool;
use Laragod\DiceSystem\Core\Random\SystemRandomEngine;

$engine = new SystemRandomEngine();
$dice = [
    new Die(6, $engine),
    new Die(6, $engine),
    new Die(6, $engine),
    new Die(6, $engine),
];

$pool = new DicePool($dice, $engine);

// Keep the 3 highest (4d6 keep 3 for ability scores)
$result = $pool->keep(3, 'highest');
echo "Ability score (4d6 keep 3): " . $result->sum() . "\n";

// Keep the 2 lowest
$result = $pool->keep(2, 'lowest');
echo "Lowest 2: " . $result->sum() . "\n";

// Access individual results
foreach ($result->results as $die) {
    echo "Roll: " . $die->value . "\n";
}
```

### Conditional Rerolling

Reroll dice that match certain conditions:

```php
<?php
require 'vendor/autoload.php';

use Laragod\DiceSystem\Core\Dice\Die;
use Laragod\DiceSystem\Core\Dice\DicePool;
use Laragod\DiceSystem\Core\Random\SystemRandomEngine;

$engine = new SystemRandomEngine();
$dice = [
    new Die(6, $engine),
    new Die(6, $engine),
    new Die(6, $engine),
];

$pool = new DicePool($dice, $engine);

// Reroll any dice that show 1 (exploding dice mechanic)
$result = $pool->reroll(fn($die) => $die->value === 1);
echo "After rerolling 1s: " . $result->sum() . "\n";

// Reroll dice below a threshold
$result = $pool->reroll(fn($die) => $die->value < 3);
echo "After rerolling below 3: " . $result->sum() . "\n";

// Complex condition: reroll if even number
$result = $pool->reroll(fn($die) => $die->value % 2 === 0);
echo "After rerolling even numbers: " . $result->sum() . "\n";
```

### Pool Operations with Seeded Rolls

```php
<?php
require 'vendor/autoload.php';

use Laragod\DiceSystem\Core\Dice\Die;
use Laragod\DiceSystem\Core\Dice\DicePool;
use Laragod\DiceSystem\Core\Random\Mt19937Engine;

$engine = new Mt19937Engine();
$engine->setSeed(999);

$dice = [
    new Die(20, $engine),
    new Die(20, $engine),
];

$pool = new DicePool($dice, $engine);

// Advantage: roll twice, keep highest
$result = $pool->keep(1, 'highest');
echo "Advantage: " . $result->results[0]->value . "\n";

// Can replay with same seed
$engine->reset();
$result2 = $pool->keep(1, 'highest');
echo "Replayed: " . $result2->results[0]->value . "\n"; // Same value!
```

## Roll History Tracking

Track and query previous rolls:

### Basic History

```php
<?php
require 'vendor/autoload.php';

use Laragod\DiceSystem\Core\Dice\DiceRoller;
use Laragod\DiceSystem\Core\History\RollHistory;

$history = new RollHistory();
$roller = new DiceRoller();

// Perform some rolls
$history->add($roller->roll('2d6'));
$history->add($roller->roll('1d20'));
$history->add($roller->roll('3d8'));

// Get all rolls
$allRolls = $history->getAll();
echo "Total rolls: " . count($allRolls) . "\n"; // 3

// Get recent rolls
$recent = $history->getRecent(2);
echo "Last 2 rolls:\n";
foreach ($recent as $roll) {
    echo "  " . $roll . "\n";
}
```

### Filtering History

```php
<?php
require 'vendor/autoload.php';

use Laragod\DiceSystem\Core\Dice\DiceRoller;
use Laragod\DiceSystem\Core\History\RollHistory;

$history = new RollHistory();
$roller = new DiceRoller();

// Add various rolls
$history->add($roller->roll('2d6'));
$history->add($roller->roll('1d20'));
$history->add($roller->roll('2d6'));
$history->add($roller->roll('3d8'));
$history->add($roller->roll('2d6'));

// Filter by notation (get only 2d6 rolls)
$d6Rolls = $history->filter(fn($result) => $result->notation === '2d6');
echo "2d6 rolls: " . count($d6Rolls) . "\n"; // 3

// Filter by total value
$highRolls = $history->filter(fn($result) => $result->total >= 10);
echo "Rolls >= 10: " . count($highRolls) . "\n";

// Filter by time
$now = new DateTimeImmutable();
$recentRolls = $history->filter(
    fn($result) => $result->rolledAt > $now->modify('-1 minute')
);
echo "Rolls in last minute: " . count($recentRolls) . "\n";
```

### History Size Limits

```php
<?php
require 'vendor/autoload.php';

use Laragod\DiceSystem\Core\Dice\DiceRoller;
use Laragod\DiceSystem\Core\History\RollHistory;

// Keep only the last 100 rolls (memory efficient)
$history = new RollHistory(maxSize: 100);
$roller = new DiceRoller();

// Add 150 rolls
for ($i = 0; $i < 150; $i++) {
    $history->add($roller->roll('1d6'));
}

// Only the last 100 are kept
echo "Count: " . $history->count() . "\n"; // 100
```

## Statistical Analysis

Analyze roll distributions to understand probability and fairness:

### Basic Statistical Analysis

```php
<?php
require 'vendor/autoload.php';

use Laragod\DiceSystem\Core\Statistics\RollStatistics;

$stats = new RollStatistics();

// Analyze some roll values
$rolls = [3, 4, 5, 2, 6, 4, 3, 5, 4];
$result = $stats->analyze($rolls);

echo "Mean: " . $result->mean . "\n";                    // Average
echo "Median: " . $result->median . "\n";                // Middle value
echo "Mode: " . $result->mode . "\n";                    // Most frequent
echo "Standard Deviation: " . $result->standardDeviation . "\n"; // Spread
echo "Min: " . $result->min . "\n";                      // Minimum
echo "Max: " . $result->max . "\n";                      // Maximum
echo "Range: " . $result->range . "\n";                  // Max - Min
```

### Distribution Analysis

```php
<?php
require 'vendor/autoload.php';

use Laragod\DiceSystem\Core\Dice\DiceRoller;
use Laragod\DiceSystem\Core\Statistics\RollStatistics;

$stats = new RollStatistics();
$roller = new DiceRoller();

// Roll d6 many times
$rolls = [];
for ($i = 0; $i < 1000; $i++) {
    $result = $roller->roll('1d6');
    $rolls[] = $result->total;
}

// Analyze distribution
$analysis = $stats->analyze($rolls);

echo "Distribution:\n";
foreach ($analysis->distribution as $value => $frequency) {
    echo "  $value: $frequency times\n";
}

// Expected distribution for fair d6
$expected = $stats->expectedDistribution(sides: 6, count: 1000);
print_r($expected);
```

### Chi-Squared Testing

Test whether dice rolls are fair using chi-squared test:

```php
<?php
require 'vendor/autoload.php';

use Laragod\DiceSystem\Core\Dice\DiceRoller;
use Laragod\DiceSystem\Core\Statistics\RollStatistics;

$stats = new RollStatistics();
$roller = new DiceRoller();

// Roll d6 many times
$rolls = [];
for ($i = 0; $i < 600; $i++) {
    $result = $roller->roll('1d6');
    $rolls[] = $result->total;
}

// Get observed and expected distributions
$analysis = $stats->analyze($rolls);
$observed = $analysis->distribution;
$expected = $stats->expectedDistribution(sides: 6, count: 600);

// Perform chi-squared test
$chiSquared = $stats->chiSquaredTest($observed, $expected);

echo "Chi-squared statistic: " . $chiSquared . "\n";
echo "Critical value (p=0.05): 11.07\n";

// Generally: chi-squared < 11.07 suggests the die is fair
if ($chiSquared < 11.07) {
    echo "Die appears fair!\n";
} else {
    echo "Die may be biased.\n";
}
```

### Full Statistical Report

```php
<?php
require 'vendor/autoload.php';

use Laragod\DiceSystem\Core\Dice\DiceRoller;
use Laragod\DiceSystem\Core\Statistics\RollStatistics;

$stats = new RollStatistics();
$roller = new DiceRoller();

// Roll d20 many times
$rolls = [];
for ($i = 0; $i < 1000; $i++) {
    $result = $roller->roll('1d20');
    $rolls[] = $result->total;
}

// Generate complete report
$analysis = $stats->analyze($rolls);

printf("=== 1d20 Statistics (1000 rolls) ===\n");
printf("Mean:     %.2f\n", $analysis->mean);
printf("Median:   %.2f\n", $analysis->median);
printf("Mode:     %s\n", $analysis->mode);
printf("Std Dev:  %.2f\n", $analysis->standardDeviation);
printf("Min:      %s\n", $analysis->min);
printf("Max:      %s\n", $analysis->max);
printf("Range:    %s\n", $analysis->range);

echo "\nDistribution:\n";
foreach ($analysis->distribution as $value => $frequency) {
    $percentage = ($frequency / 1000) * 100;
    printf("  %2d: %3d (%.1f%%)\n", $value, $frequency, $percentage);
}
```

## Next Steps

Explore more:
- **[API Reference](api-reference.md)** - Complete API documentation for all classes
- **[Examples](../examples/)** - Ready-to-run example files
