# Basic Usage

Learn the fundamentals of rolling dice with the PHP Dice System library.

## Table of Contents

- [Introduction](#introduction)
- [Basic Dice Rolling](#basic-dice-rolling)
- [Understanding Roll Results](#understanding-roll-results)
- [Accessing Individual Rolls](#accessing-individual-rolls)
- [Roll Metadata](#roll-metadata)
- [Common Dice Notations](#common-dice-notations)
- [Error Handling](#error-handling)

## Introduction

The PHP Dice System Core library provides a simple yet powerful interface for rolling dice using standard dice notation. Whether you're building a game, simulation, or just need randomized values, the library makes it straightforward.

The main entry point is the `DiceRoller` class, which:
- Parses dice notation (e.g., "2d6", "1d20")
- Rolls the specified dice
- Returns comprehensive results with metadata

## Basic Dice Rolling

The simplest way to roll dice is with the `DiceRoller` class:

```php
<?php
require 'vendor/autoload.php';

use Laragod\DiceSystem\Core\Dice\DiceRoller;

// Create a roller with the default random engine (SystemRandomEngine)
$roller = new DiceRoller();

// Roll 2 six-sided dice
$result = $roller->roll('2d6');

// Print the result
echo $result; // Output: "2d6: [4, 2] = 6"
```

## Understanding Roll Results

Every call to `roll()` returns a `RollResult` object containing all information about the roll:

```php
<?php
require 'vendor/autoload.php';

use Laragod\DiceSystem\Core\Dice\DiceRoller;

$roller = new DiceRoller();
$result = $roller->roll('3d8');

// Access the total
echo "Total: " . $result->total; // Total: 14

// Access the notation
echo "Notation: " . $result->notation; // Notation: 3d8

// Access individual results
foreach ($result->individualResults as $die) {
    echo "Rolled: " . $die->value . " on d" . $die->sides . "\n";
}
// Output:
// Rolled: 5 on d8
// Rolled: 4 on d8
// Rolled: 5 on d8

// Get just the values
$values = $result->getValues();
print_r($values); // Array ( [0] => 5 [1] => 4 [2] => 5 )
```

## Accessing Individual Rolls

The `RollResult` object provides access to detailed information about each die:

```php
<?php
require 'vendor/autoload.php';

use Laragod\DiceSystem\Core\Dice\DiceRoller;

$roller = new DiceRoller();
$result = $roller->roll('4d6');

// Access individual die results
foreach ($result->individualResults as $index => $die) {
    echo "Die " . ($index + 1) . ": " . $die->value . "\n";
}

// Each DieResult has these properties:
foreach ($result->individualResults as $die) {
    echo "Value: " . $die->value . "\n";        // The rolled value
    echo "Sides: " . $die->sides . "\n";        // Number of sides
    echo "Rolled at: " . $die->rolledAt . "\n"; // Timestamp
    echo "Seed: " . $die->seed . "\n";          // Seed used (if any)
}
```

## Roll Metadata

Each `RollResult` includes metadata about when and how the roll was performed:

```php
<?php
require 'vendor/autoload.php';

use Laragod\DiceSystem\Core\Dice\DiceRoller;

$roller = new DiceRoller();
$result = $roller->roll('1d20');

// Get the timestamp when this roll occurred
echo "Rolled at: " . $result->rolledAt->format('Y-m-d H:i:s') . "\n";

// Get the seed (null for unseeded randomness)
if ($result->seed !== null) {
    echo "Seed: " . $result->seed . "\n";
} else {
    echo "No seed (using system randomness)\n";
}

// Store the complete result for later
$timestamp = $result->rolledAt;
$total = $result->total;
$notation = $result->notation;
```

## Common Dice Notations

The library supports standard dice notation format `XdY` where:
- `X` is the quantity of dice to roll
- `Y` is the number of sides per die

### Common Examples

```php
<?php
require 'vendor/autoload.php';

use Laragod\DiceSystem\Core\Dice\DiceRoller;

$roller = new DiceRoller();

// Single die rolls
$roller->roll('1d4');   // One 4-sided die
$roller->roll('1d6');   // One 6-sided die
$roller->roll('1d8');   // One 8-sided die
$roller->roll('1d10');  // One 10-sided die
$roller->roll('1d12');  // One 12-sided die
$roller->roll('1d20');  // One 20-sided die (D&D)
$roller->roll('1d100'); // One 100-sided die (percentile)

// Multiple dice
$roller->roll('2d6');   // Two 6-sided dice (classic)
$roller->roll('3d6');   // Three 6-sided dice
$roller->roll('4d6');   // Four 6-sided dice (D&D ability scores)
$roller->roll('2d10');  // Two 10-sided dice
$roller->roll('10d6');  // Ten 6-sided dice

// The notation is case-insensitive
$roller->roll('2D6');   // Same as '2d6'
$roller->roll('1D20');  // Same as '1d20'
```

### Understanding Result Ranges

Different dice have different ranges of possible totals:

```php
<?php
require 'vendor/autoload.php';

use Laragod\DiceSystem\Core\Dice\DiceRoller;

$roller = new DiceRoller();

// 1d6: minimum 1, maximum 6
$result = $roller->roll('1d6');

// 2d6: minimum 2, maximum 12 (average ~7)
$result = $roller->roll('2d6');

// 3d8: minimum 3, maximum 24 (average ~13.5)
$result = $roller->roll('3d8');

// 4d6: minimum 4, maximum 24 (average ~14)
$result = $roller->roll('4d6');
```

## Error Handling

The library throws exceptions for invalid input:

```php
<?php
require 'vendor/autoload.php';

use Laragod\DiceSystem\Core\Dice\DiceRoller;
use Laragod\DiceSystem\Core\Exception\InvalidNotationException;

$roller = new DiceRoller();

// Invalid notation format
try {
    $roller->roll('2-6');  // Wrong format
} catch (InvalidNotationException $e) {
    echo "Invalid notation: " . $e->getMessage();
}

// Invalid notation format
try {
    $roller->roll('d6');   // Missing quantity
} catch (InvalidNotationException $e) {
    echo "Invalid notation: " . $e->getMessage();
}

// Zero or negative quantity (invalid notation)
try {
    $roller->roll('0d6');  // Invalid: zero dice
} catch (InvalidNotationException $e) {
    echo "Invalid notation: " . $e->getMessage();
}

// Single-sided die (less than 2 sides)
try {
    $roller->roll('1d1');  // Invalid: die needs at least 2 sides
} catch (InvalidNotationException $e) {
    echo "Invalid notation: " . $e->getMessage();
}
```

## String Representation

The `RollResult` object has a convenient string representation:

```php
<?php
require 'vendor/autoload.php';

use Laragod\DiceSystem\Core\Dice\DiceRoller;

$roller = new DiceRoller();

$result = $roller->roll('2d6');

// Cast to string or use echo
echo $result; // Example output: "2d6: [3, 5] = 8"

// This is useful for logging or display
$logMessage = "Player rolled: " . $result;
```

## Next Steps

Now that you understand the basics, explore:
- **[Advanced Usage](advanced-usage.md)** - Seeded rolls, custom dice, pools, and statistics
- **[API Reference](api-reference.md)** - Complete API documentation
