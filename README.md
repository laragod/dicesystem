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

```php
<?php
require 'vendor/autoload.php';

use Laragod\DiceSystem\Core\Dice\DiceRoller;

$roller = new DiceRoller();

// Roll 2d6 and get results
$result = $roller->roll('2d6');

echo $result; // Output: "2d6: [3, 5] = 8"
echo $result->total; // 8
echo $result->notation; // "2d6"
```

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
