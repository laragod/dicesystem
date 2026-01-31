# PHP Dice System

A comprehensive, production-ready PHP library for dice rolling with support for standard notation, custom faces, deterministic seeding, and statistical analysis.

[![PHP Version](https://img.shields.io/badge/php-%3E%3D%208.4-blue.svg)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

## Features

### Core Library
- **Standard Dice Notation**: Roll dice using familiar notation like "2d6", "1d20", "3d8"
- **Deterministic Seeding**: Reproduce exact roll sequences for testing and debugging
- **Custom Dice**: Create dice with non-numeric faces (cards, attributes, etc.)
- **Dice Pools**: Manage collections of dice with aggregate operations (keep highest/lowest, reroll conditions)
- **Roll History**: Track and query previous rolls with filtering capabilities
- **Statistical Analysis**: Analyze roll distributions with mean, median, mode, standard deviation, chi-squared testing
- **Random Engines**: Choose between system randomness (cryptographically secure) or seeded Mersenne Twister

### 2d6 System (Extension)
- Multiple 2d6 roller variants for game systems
- Deck-based rolling for card games
- Deterministic seeding for reproducible game states

## Installation

Install via Composer:

```bash
composer require laragod/dice-system
```

Requires **PHP 8.4** or higher.

## Quick Start

Get rolling in seconds:

```php
<?php
require 'vendor/autoload.php';

use Laragod\DiceSystem\Core\Dice\DiceRoller;

$roller = new DiceRoller();
$result = $roller->roll('2d6');

echo $result->total; // 8
```

## Usage Examples

### Basic Rolling

Roll any standard dice notation with automatic parsing:

```php
use Laragod\DiceSystem\Core\Dice\DiceRoller;

$roller = new DiceRoller();

$result = $roller->roll('2d6');      // Roll 2 six-sided dice
$result = $roller->roll('1d20');     // D&D attack roll
$result = $roller->roll('4d6');      // 4 six-sided dice
$result = $roller->roll('3d8');      // 3 eight-sided dice

echo $result->total;    // Sum of all dice
echo $result->rolls;    // Array: [3, 5]
echo $result->notation; // "2d6"
```

### Seeded Rolls (Reproducible Testing)

Generate identical sequences for deterministic testing:

```php
use Laragod\DiceSystem\Core\Dice\DiceRoller;
use Laragod\DiceSystem\Core\Random\Mt19937Engine;

$engine = new Mt19937Engine(seed: 12345);
$roller = new DiceRoller($engine);

$result1 = $roller->roll('2d6'); // Always: [3, 5] = 8
$engine->reset();                // Reset to initial seed
$result2 = $roller->roll('2d6'); // Same: [3, 5] = 8
```

### Custom Dice (Non-Numeric Faces)

Create dice with custom values like cards, attributes, or strings:

```php
use Laragod\DiceSystem\Core\Dice\CustomDie;
use Laragod\DiceSystem\Core\Random\Mt19937Engine;

$engine = new Mt19937Engine(seed: 42);
$die = new CustomDie(['rock', 'paper', 'scissors'], $engine);

$result = $die->roll();
echo $result->value; // "paper"
```

### Dice Pools (Advantage/Disadvantage)

Manage collections of dice with aggregate operations:

```php
use Laragod\DiceSystem\Core\Dice\{DicePool, Die};
use Laragod\DiceSystem\Core\Random\Mt19937Engine;

$engine = new Mt19937Engine(seed: 100);
$pool = new DicePool([new Die(20), new Die(20)], $engine);

// D&D 5e Advantage: roll 2d20, keep highest
$result = $pool->keep(1, 'highest');
echo $result->total; // 18

// Disadvantage: roll 2d20, keep lowest
$result = $pool->keep(1, 'lowest');
echo $result->total; // 12

// Reroll dice under threshold
$result = $pool->reroll(fn($die) => $die->value < 5);
```

### Statistical Analysis

Analyze roll distributions for game balancing and fairness:

```php
use Laragod\DiceSystem\Core\Statistics\RollStatistics;

$stats = new RollStatistics();
$analysis = $stats->analyze([3, 5, 6, 2, 6, 4]);

echo $analysis->mean;              // 4.33
echo $analysis->median;            // 4.5
echo $analysis->mode;              // 6
echo $analysis->standardDeviation; // ~1.56
echo $analysis->min;               // 2
echo $analysis->max;               // 6

// Check die fairness with chi-squared test
$observed = [1 => 8, 2 => 12, 3 => 10];
$expected = [1 => 10, 2 => 10, 3 => 10];
$chiSquared = $stats->chiSquaredTest($observed, $expected);
```

### Roll History Tracking

Track and query previous rolls:

```php
use Laragod\DiceSystem\Core\History\RollHistory;
use Laragod\DiceSystem\Core\Dice\DiceRoller;

$history = new RollHistory(maxSize: 100);
$roller = new DiceRoller();

$result = $roller->roll('2d6');
$history->add($result);

// Get 10 most recent rolls
$recent = $history->getRecent(10);

// Filter rolls by criteria
$d20Rolls = $history->filter(fn($r) => $r->notation === '1d20');
$highRolls = $history->filter(fn($r) => $r->total >= 10);
```

## Common Use Cases

**Game Development**
Perfect for RPGs, board games, roguelikes, and simulations. Use seeded rolls for reproducible procedural generation and dice pools for complex mechanics like D&D advantage/disadvantage.

**Testing**
Seeded rolls ensure reproducible test scenarios. Test game balance, verify statistical distributions, and debug randomness issues without flakiness.

**Statistics**
Analyze fairness of custom dice mechanics. Use chi-squared testing to verify dice balance and distribution curves to visualize randomness patterns.

**Web Applications**
Integrate into Laravel apps, Symfony projects, or any PHP framework. Works in CLI tools, HTTP endpoints, and background jobs.

## Documentation

- **[Basic Usage](docs/basic-usage.md)** - Getting started with rolling dice and accessing results
- **[Advanced Usage](docs/advanced-usage.md)** - Seeded rolls, custom dice, pools, history, and statistics
- **[API Reference](docs/api-reference.md)** - Complete documentation for all public classes and methods

## Examples

Ready-to-run examples in the `examples/` directory:

```bash
php examples/01-basic-rolling.php        # Basic notation rolling
php examples/02-seeded-rolls.php         # Reproducible rolls with seeds
php examples/03-custom-dice.php          # Custom face dice
php examples/04-dice-pools.php           # Pool operations
php examples/05-statistics.php           # Statistical analysis
```

## Core Concepts

### Random Engines
The library supports two random engines:

- **SystemRandomEngine**: Uses PHP's `random_int()` for cryptographically secure randomness. Perfect for production use. Cannot be seeded.
- **Mt19937Engine**: Uses PHP's Mersenne Twister (mt_rand) with seed support. Perfect for reproducible testing.

### Dice Notation
Standard dice notation format: `XdY`
- `X` = quantity of dice
- `Y` = number of sides per die

Examples: `1d6`, `2d6`, `3d8`, `1d20`, `4d12`

### Roll Results
Every roll returns a `RollResult` object containing:
- Individual die results with timestamps and seed information
- Total sum of all dice
- Original notation
- Timestamp of the roll

## License

This library is open-sourced software licensed under the [MIT license](LICENSE).

## Contributing

Contributions are welcome! Please feel free to submit pull requests or open issues for bugs and feature requests.

## Support

For questions, issues, or suggestions, please open an issue on the GitHub repository.
