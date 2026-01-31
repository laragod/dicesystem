# API Reference

Complete documentation of all public classes and methods in the PHP Dice System library.

## Table of Contents

- [Random Engines](#random-engines)
- [Dice Rolling](#dice-rolling)
- [Custom Dice](#custom-dice)
- [Dice Pools](#dice-pools)
- [Parsing](#parsing)
- [Results](#results)
- [History](#history)
- [Statistics](#statistics)
- [Exceptions](#exceptions)

---

## Random Engines

### RandomEngineInterface

Interface for random number generators. All random engines implement this interface.

```php
namespace Laragod\DiceSystem\Core\Contract;

interface RandomEngineInterface
{
    /**
     * Set the seed for this random engine.
     * Once set, the seed becomes immutable.
     *
     * @param int $seed The seed value
     * @throws LogicException If seed already set
     */
    public function setSeed(int $seed): void;

    /**
     * Get the current seed value.
     *
     * @return int|null The seed, or null if not set
     */
    public function getSeed(): ?int;

    /**
     * Generate next random integer between min and max.
     *
     * @param int $min Minimum value (inclusive)
     * @param int $max Maximum value (inclusive)
     * @return int Random integer
     * @throws LogicException If seed not set (for Mt19937Engine)
     */
    public function next(int $min, int $max): int;

    /**
     * Reset the engine to initial seeded state.
     * Allows replaying the same sequence.
     *
     * @throws LogicException If seed not set
     */
    public function reset(): void;
}
```

### SystemRandomEngine

Cryptographically secure random number generator using `random_int()`. Cannot be seeded.

```php
namespace Laragod\DiceSystem\Core\Random;

final class SystemRandomEngine implements RandomEngineInterface
{
    /**
     * Create new instance.
     */
    public function __construct() {}

    /**
     * Throws exception - system random cannot be seeded.
     *
     * @throws LogicException Always throws
     */
    public function setSeed(int $seed): void;

    /**
     * @return null Always null
     */
    public function getSeed(): ?int;

    /**
     * Generate cryptographically secure random integer.
     *
     * @param int $min Minimum (inclusive)
     * @param int $max Maximum (inclusive)
     * @return int Random value
     */
    public function next(int $min, int $max): int;

    /**
     * No-op for system random.
     */
    public function reset(): void;
}
```

**Usage:**

```php
use Laragod\DiceSystem\Core\Random\SystemRandomEngine;

$engine = new SystemRandomEngine();
$value = $engine->next(1, 6); // Random value 1-6
```

### Mt19937Engine

Seeded Mersenne Twister random generator. Supports deterministic sequences.

```php
namespace Laragod\DiceSystem\Core\Random;

class Mt19937Engine implements RandomEngineInterface
{
    /**
     * Create new instance with optional seed.
     *
     * @param int|null $seed Optional seed to set immediately
     */
    public function __construct(?int $seed = null);

    /**
     * Set the seed for deterministic randomness.
     * Immutable - can only be set once.
     *
     * @param int $seed The seed value
     * @throws LogicException If seed already set
     */
    public function setSeed(int $seed): void;

    /**
     * Get the current seed.
     *
     * @return int|null The seed, or null if not set
     */
    public function getSeed(): ?int;

    /**
     * Generate next value in seeded sequence.
     *
     * @param int $min Minimum (inclusive)
     * @param int $max Maximum (inclusive)
     * @return int Value from seeded sequence
     * @throws LogicException If seed not set
     */
    public function next(int $min, int $max): int;

    /**
     * Reset to initial seeded state to replay sequence.
     *
     * @throws LogicException If seed not set
     */
    public function reset(): void;
}
```

**Usage:**

```php
use Laragod\DiceSystem\Core\Random\Mt19937Engine;

$engine = new Mt19937Engine(12345); // Seed immediately
$value = $engine->next(1, 6); // First value in sequence

$engine->reset(); // Replay from beginning
$value = $engine->next(1, 6); // Same value as before
```

---

## Dice Rolling

### DiceRoller

Main class for rolling dice using standard notation.

```php
namespace Laragod\DiceSystem\Core\Dice;

final class DiceRoller
{
    /**
     * Create new roller with optional random engine.
     *
     * @param RandomEngineInterface|null $engine Uses SystemRandomEngine if null
     */
    public function __construct(?RandomEngineInterface $engine = null);

    /**
     * Roll dice using standard notation.
     *
     * @param string $notation Dice notation (e.g., "2d6", "1d20")
     * @return RollResult Complete result with all metadata
     * @throws InvalidNotationException If notation is invalid
     */
    public function roll(string $notation): RollResult;
}
```

**Usage:**

```php
use Laragod\DiceSystem\Core\Dice\DiceRoller;

$roller = new DiceRoller();
$result = $roller->roll('2d6');

echo $result->total; // 7
```

### Die

Represents a single die with specified number of sides.

```php
namespace Laragod\DiceSystem\Core\Dice;

final class Die
{
    /**
     * Create a die with specified sides.
     *
     * @param int $sides Number of sides (minimum 2)
     * @param RandomEngineInterface $engine Random engine to use
     * @throws InvalidDieException If sides < 2
     */
    public function __construct(
        public readonly int $sides,
        private RandomEngineInterface $engine
    );

    /**
     * Roll the die once.
     *
     * @return DieResult Result with value and metadata
     */
    public function roll(): DieResult;
}
```

**Usage:**

```php
use Laragod\DiceSystem\Core\Dice\Die;
use Laragod\DiceSystem\Core\Random\SystemRandomEngine;

$engine = new SystemRandomEngine();
$die = new Die(6, $engine);
$result = $die->roll();

echo $result->value; // 1-6
```

---

## Custom Dice

### CustomDie

Die with custom (non-numeric) face values.

```php
namespace Laragod\DiceSystem\Core\Dice;

final class CustomDie
{
    /**
     * Create die with custom faces.
     *
     * @param array<mixed> $faces Array of face values
     * @param RandomEngineInterface $engine Random engine to use
     * @throws InvalidDieException If faces array is empty
     */
    public function __construct(
        array $faces,
        private RandomEngineInterface $engine
    );

    /**
     * Roll the die and return a face value.
     *
     * @return CustomDieResult Result with selected face and metadata
     */
    public function roll(): CustomDieResult;

    /**
     * Get all available faces.
     *
     * @return array<mixed> Copy of faces array
     */
    public function getFaces(): array;
}
```

**Usage:**

```php
use Laragod\DiceSystem\Core\Dice\CustomDie;
use Laragod\DiceSystem\Core\Random\SystemRandomEngine;

$die = new CustomDie(['Heads', 'Tails'], new SystemRandomEngine());
$result = $die->roll();

echo $result->value; // "Heads" or "Tails"
```

---

## Dice Pools

### DicePool

Manages a collection of dice with aggregate operations.

```php
namespace Laragod\DiceSystem\Core\Dice;

final class DicePool
{
    /**
     * Create pool with collection of dice.
     *
     * @param array<Die> $dice Array of Die objects
     * @param RandomEngineInterface $engine Random engine to use
     * @throws InvalidArgumentException If dice array empty
     */
    public function __construct(
        private array $dice,
        private RandomEngineInterface $engine
    );

    /**
     * Add a die to the pool.
     *
     * @param Die $die The die to add
     */
    public function add(Die $die): void;

    /**
     * Roll all dice in the pool.
     *
     * @return PoolResult Results from all dice
     */
    public function rollAll(): PoolResult;

    /**
     * Roll all dice and keep N highest or lowest.
     *
     * @param int $n Number of dice to keep
     * @param string $mode Either 'highest' or 'lowest'
     * @return PoolResult Filtered results
     * @throws InvalidArgumentException If mode invalid
     */
    public function keep(int $n, string $mode = 'highest'): PoolResult;

    /**
     * Roll all dice, reroll those matching predicate.
     *
     * @param callable(DieResult): bool $predicate Condition for rerolling
     * @return PoolResult Final results after rerolling
     */
    public function reroll(callable $predicate): PoolResult;

    /**
     * Get number of dice in pool.
     *
     * @return int Number of dice
     */
    public function count(): int;
}
```

**Usage:**

```php
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

// Keep 3 highest (4d6 keep 3)
$result = $pool->keep(3, 'highest');
echo $result->sum(); // Sum of top 3
```

---

## Parsing

### DiceNotationParser

Parses dice notation strings into DiceExpression objects.

```php
namespace Laragod\DiceSystem\Core\Parser;

class DiceNotationParser
{
    /**
     * Parse dice notation string.
     *
     * @param string $notation Dice notation (e.g., "2d6")
     * @return DiceExpression Parsed expression
     * @throws InvalidNotationException If notation format invalid
     */
    public function parse(string $notation): DiceExpression;
}
```

**Valid Formats:**
- `1d6`, `2d6`, `3d8`, etc.
- Case-insensitive: `2D6` = `2d6`
- Format: `XdY` where X >= 1 and Y >= 2

**Throws:**
- `InvalidNotationException` for invalid format

**Usage:**

```php
use Laragod\DiceSystem\Core\Parser\DiceNotationParser;

$parser = new DiceNotationParser();
$expression = $parser->parse('2d6');

echo $expression->quantity; // 2
echo $expression->sides;    // 6
```

### DiceExpression

Immutable value object representing parsed dice notation.

```php
namespace Laragod\DiceSystem\Core\Parser;

final readonly class DiceExpression
{
    /**
     * @param int $quantity Number of dice (>= 1)
     * @param int $sides Sides per die (>= 2)
     * @throws InvalidDieException If values invalid
     */
    public function __construct(
        public int $quantity,
        public int $sides
    );

    /**
     * Convert back to notation string.
     *
     * @return string Original notation (e.g., "2d6")
     */
    public function __toString(): string;
}
```

---

## Results

### RollResult

Immutable value object representing results of rolling multiple dice.

```php
namespace Laragod\DiceSystem\Core\Dice;

final readonly class RollResult
{
    /**
     * @param string $notation Original dice notation
     * @param array<DieResult> $individualResults Results of each die
     * @param int $total Sum of all rolls
     * @param DateTimeImmutable $rolledAt When roll occurred
     * @param int|null $seed Seed used, if any
     */
    public function __construct(
        public string $notation,
        public array $individualResults,
        public int $total,
        public DateTimeImmutable $rolledAt,
        public ?int $seed
    );

    /**
     * Factory method from array of DieResult objects.
     *
     * @param string $notation Original notation
     * @param array<DieResult> $individualResults Individual results
     * @return self
     */
    public static function fromResults(
        string $notation,
        array $individualResults
    ): self;

    /**
     * Get just the rolled values.
     *
     * @return array<int> Array of values
     */
    public function getValues(): array;

    /**
     * String representation.
     * Format: "2d6: [3, 5] = 8"
     *
     * @return string
     */
    public function __toString(): string;
}
```

**Properties:**
- `string $notation` - The dice notation used
- `array $individualResults` - Array of DieResult objects
- `int $total` - Sum of all dice
- `DateTimeImmutable $rolledAt` - Timestamp of roll
- `int|null $seed` - Seed used (null for unseeded)

**Usage:**

```php
use Laragod\DiceSystem\Core\Dice\DiceRoller;

$roller = new DiceRoller();
$result = $roller->roll('2d6');

echo $result->notation; // "2d6"
echo $result->total;    // e.g., 8
print_r($result->getValues()); // [3, 5]
```

### DieResult

Immutable value object representing a single die roll.

```php
namespace Laragod\DiceSystem\Core\Dice;

final readonly class DieResult
{
    /**
     * @param int $value The rolled value (1 to sides)
     * @param int $sides Number of sides on the die
     * @param DateTimeImmutable $rolledAt When rolled
     * @param int|null $seed Seed used, if any
     */
    public function __construct(
        public int $value,
        public int $sides,
        public DateTimeImmutable $rolledAt,
        public ?int $seed
    );
}
```

**Properties:**
- `int $value` - The rolled value
- `int $sides` - Number of sides
- `DateTimeImmutable $rolledAt` - Timestamp
- `int|null $seed` - Seed used

### CustomDieResult

Immutable value object representing a custom die roll.

```php
namespace Laragod\DiceSystem\Core\Dice;

final readonly class CustomDieResult
{
    /**
     * @param mixed $value The selected face value
     * @param int $faceCount Number of faces on the die
     * @param DateTimeImmutable $rolledAt When rolled
     * @param int|null $seed Seed used, if any
     */
    public function __construct(
        public mixed $value,
        public int $faceCount,
        public DateTimeImmutable $rolledAt,
        public ?int $seed
    );
}
```

**Properties:**
- `mixed $value` - The selected face value
- `int $faceCount` - Number of faces
- `DateTimeImmutable $rolledAt` - Timestamp
- `int|null $seed` - Seed used

### PoolResult

Immutable value object representing results of rolling a dice pool.

```php
namespace Laragod\DiceSystem\Core\Dice;

final readonly class PoolResult
{
    /**
     * @param array<DieResult> $results All die results
     * @param DateTimeImmutable $rolledAt When rolled
     * @param int|null $seed Seed used, if any
     * @throws InvalidArgumentException If results empty
     */
    public function __construct(
        public array $results,
        public DateTimeImmutable $rolledAt,
        public ?int $seed
    );

    /**
     * Sum of all die values.
     *
     * @return int Total
     */
    public function sum(): int;

    /**
     * Average value per die.
     *
     * @return float Mean
     */
    public function average(): float;

    /**
     * Get N highest valued dice.
     *
     * @param int $n Number to return (default 1)
     * @return array<DieResult> Highest results, descending
     */
    public function highest(int $n = 1): array;

    /**
     * Get N lowest valued dice.
     *
     * @param int $n Number to return (default 1)
     * @return array<DieResult> Lowest results, ascending
     */
    public function lowest(int $n = 1): array;

    /**
     * Get just the values from all results.
     *
     * @return array<int> Array of values
     */
    public function getValues(): array;
}
```

---

## History

### RollHistory

Tracks and manages a collection of roll results.

```php
namespace Laragod\DiceSystem\Core\History;

final class RollHistory
{
    /**
     * Create history tracker.
     *
     * @param int $maxSize Maximum rolls to store (default 1000)
     * @param PersistenceAdapterInterface|null $adapter Optional persistence
     */
    public function __construct(
        private int $maxSize = 1000,
        private ?PersistenceAdapterInterface $adapter = null
    );

    /**
     * Add a roll result to history.
     * Oldest rolls removed if maxSize exceeded (FIFO).
     *
     * @param RollResult $result The roll to store
     */
    public function add(RollResult $result): void;

    /**
     * Get all stored rolls.
     *
     * @return array<RollResult> All rolls in order
     */
    public function getAll(): array;

    /**
     * Get N most recent rolls.
     *
     * @param int $limit Maximum to return
     * @return array<RollResult> Recent rolls (newest first)
     */
    public function getRecent(int $limit): array;

    /**
     * Filter rolls by predicate function.
     *
     * @param callable(RollResult): bool $predicate Filter condition
     * @return array<RollResult> Matching rolls
     */
    public function filter(callable $predicate): array;

    /**
     * Remove all stored history.
     */
    public function clear(): void;

    /**
     * Get number of stored rolls.
     *
     * @return int Count
     */
    public function count(): int;
}
```

**Usage:**

```php
use Laragod\DiceSystem\Core\History\RollHistory;
use Laragod\DiceSystem\Core\Dice\DiceRoller;

$history = new RollHistory();
$roller = new DiceRoller();

$history->add($roller->roll('2d6'));
$history->add($roller->roll('1d20'));

// Filter by notation
$d20s = $history->filter(fn($r) => $r->notation === '1d20');
echo count($d20s); // 1
```

---

## Statistics

### RollStatistics

Statistical analysis of dice rolls.

```php
namespace Laragod\DiceSystem\Core\Statistics;

final class RollStatistics
{
    /**
     * Analyze array of values.
     *
     * @param array<int> $values Values to analyze
     * @return StatisticalResult Complete analysis
     * @throws InvalidArgumentException If values empty
     */
    public function analyze(array $values): StatisticalResult;

    /**
     * Get frequency distribution of values.
     *
     * @param array<int> $values Values to analyze
     * @return array<int|float, int> Value => frequency map
     */
    public function distributionMap(array $values): array;

    /**
     * Calculate expected uniform distribution.
     *
     * @param int $sides Number of sides on die
     * @param int $count Total number of rolls
     * @return array<int, float> Value => expected frequency
     */
    public function expectedDistribution(int $sides, int $count): array;

    /**
     * Perform chi-squared test for distribution fairness.
     *
     * @param array<int|float, int|float> $observed Observed frequencies
     * @param array<int|float, int|float> $expected Expected frequencies
     * @return float Chi-squared statistic
     */
    public function chiSquaredTest(
        array $observed,
        array $expected
    ): float;
}
```

**Usage:**

```php
use Laragod\DiceSystem\Core\Statistics\RollStatistics;

$stats = new RollStatistics();
$result = $stats->analyze([1, 2, 3, 2, 4, 3, 2]);

echo $result->mean; // 2.43
echo $result->median; // 2
echo $result->mode; // 2
```

### StatisticalResult

Immutable value object containing statistical analysis results.

```php
namespace Laragod\DiceSystem\Core\Statistics;

final readonly class StatisticalResult
{
    /**
     * @param float $mean Average value
     * @param float $median Middle value
     * @param int|float $mode Most frequent value
     * @param float $standardDeviation Measure of spread
     * @param int|float $min Minimum value
     * @param int|float $max Maximum value
     * @param int $range Max - Min
     * @param array<int|float, int> $distribution Value => frequency
     */
    public function __construct(
        public float $mean,
        public float $median,
        public int|float $mode,
        public float $standardDeviation,
        public int|float $min,
        public int|float $max,
        public int $range,
        public array $distribution
    );
}
```

**Properties:**
- `float $mean` - Average value
- `float $median` - Middle value when sorted
- `int|float $mode` - Most frequent value
- `float $standardDeviation` - Measure of dispersion
- `int|float $min` - Minimum value
- `int|float $max` - Maximum value
- `int $range` - Difference between max and min
- `array $distribution` - Value => frequency mapping

---

## Exceptions

### InvalidNotationException

Thrown when dice notation is invalid.

```php
namespace Laragod\DiceSystem\Core\Exception;

class InvalidNotationException extends Exception
{
    // Thrown for invalid notation format
}
```

### InvalidDieException

Thrown when die configuration is invalid.

```php
namespace Laragod\DiceSystem\Core\Exception;

class InvalidDieException extends Exception
{
    // Thrown for invalid die (sides < 2, empty faces, etc.)
}
```

**Usage:**

```php
use Laragod\DiceSystem\Core\Dice\DiceRoller;
use Laragod\DiceSystem\Core\Exception\InvalidNotationException;

$roller = new DiceRoller();

try {
    $roller->roll('invalid');
} catch (InvalidNotationException $e) {
    echo "Error: " . $e->getMessage();
}
```
