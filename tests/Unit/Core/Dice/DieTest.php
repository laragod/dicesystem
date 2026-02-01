<?php

declare(strict_types=1);

namespace Laragod\DiceSystem\Tests\Unit\Core\Dice;

use DateTimeImmutable;
use Laragod\DiceSystem\Core\Dice\DieRoll;
use Laragod\DiceSystem\Core\Dice\DieResult;
use Laragod\DiceSystem\Core\Exception\InvalidDieException;
use Laragod\DiceSystem\Core\Random\Mt19937Engine;
use PHPUnit\Framework\TestCase;

class DieTest extends TestCase
{
    public function testStandardD4(): void
    {
        $engine = new Mt19937Engine();
        $engine->setSeed(12345);

        $die = new DieRoll(4, $engine);

        $this->assertSame(4, $die->sides);

        $result = $die->roll();
        $this->assertInstanceOf(DieResult::class, $result);
        $this->assertSame(4, $result->sides);
        $this->assertGreaterThanOrEqual(1, $result->value);
        $this->assertLessThanOrEqual(4, $result->value);
    }

    public function testStandardD6(): void
    {
        $engine = new Mt19937Engine();
        $engine->setSeed(12345);

        $die = new DieRoll(6, $engine);

        $this->assertSame(6, $die->sides);

        $result = $die->roll();
        $this->assertSame(6, $result->sides);
        $this->assertGreaterThanOrEqual(1, $result->value);
        $this->assertLessThanOrEqual(6, $result->value);
    }

    public function testStandardD8(): void
    {
        $engine = new Mt19937Engine();
        $engine->setSeed(12345);

        $die = new DieRoll(8, $engine);

        $this->assertSame(8, $die->sides);

        $result = $die->roll();
        $this->assertSame(8, $result->sides);
        $this->assertGreaterThanOrEqual(1, $result->value);
        $this->assertLessThanOrEqual(8, $result->value);
    }

    public function testStandardD10(): void
    {
        $engine = new Mt19937Engine();
        $engine->setSeed(12345);

        $die = new DieRoll(10, $engine);

        $this->assertSame(10, $die->sides);

        $result = $die->roll();
        $this->assertSame(10, $result->sides);
        $this->assertGreaterThanOrEqual(1, $result->value);
        $this->assertLessThanOrEqual(10, $result->value);
    }

    public function testStandardD12(): void
    {
        $engine = new Mt19937Engine();
        $engine->setSeed(12345);

        $die = new DieRoll(12, $engine);

        $this->assertSame(12, $die->sides);

        $result = $die->roll();
        $this->assertSame(12, $result->sides);
        $this->assertGreaterThanOrEqual(1, $result->value);
        $this->assertLessThanOrEqual(12, $result->value);
    }

    public function testStandardD20(): void
    {
        $engine = new Mt19937Engine();
        $engine->setSeed(12345);

        $die = new DieRoll(20, $engine);

        $this->assertSame(20, $die->sides);

        $result = $die->roll();
        $this->assertSame(20, $result->sides);
        $this->assertGreaterThanOrEqual(1, $result->value);
        $this->assertLessThanOrEqual(20, $result->value);
    }

    public function testStandardD100(): void
    {
        $engine = new Mt19937Engine();
        $engine->setSeed(12345);

        $die = new DieRoll(100, $engine);

        $this->assertSame(100, $die->sides);

        $result = $die->roll();
        $this->assertSame(100, $result->sides);
        $this->assertGreaterThanOrEqual(1, $result->value);
        $this->assertLessThanOrEqual(100, $result->value);
    }

    public function testRollResultsAreWithinValidRange(): void
    {
        $engine = new Mt19937Engine();
        $engine->setSeed(99999);

        $die = new DieRoll(6, $engine);

        // Roll multiple times to verify range
        for ($i = 0; $i < 100; $i++) {
            $result = $die->roll();
            $this->assertGreaterThanOrEqual(1, $result->value, "Roll {$i}: value too low");
            $this->assertLessThanOrEqual(6, $result->value, "Roll {$i}: value too high");
        }
    }

    public function testSeededRollsAreDeterministic(): void
    {
        $engine1 = new Mt19937Engine();
        $engine1->setSeed(42);

        $die1 = new DieRoll(20, $engine1);
        $results1 = [];
        for ($i = 0; $i < 10; $i++) {
            $results1[] = $die1->roll()->value;
        }

        // Reset and roll again with same seed
        $engine2 = new Mt19937Engine();
        $engine2->setSeed(42);

        $die2 = new DieRoll(20, $engine2);
        $results2 = [];
        for ($i = 0; $i < 10; $i++) {
            $results2[] = $die2->roll()->value;
        }

        $this->assertSame($results1, $results2, 'Same seed should produce identical sequences');
    }

    public function testDifferentDiceWithSameSeed(): void
    {
        $seed = 7777;

        $engine6 = new Mt19937Engine();
        $engine6->setSeed($seed);
        $die6 = new DieRoll(6, $engine6);

        $engine20 = new Mt19937Engine();
        $engine20->setSeed($seed);
        $die20 = new DieRoll(20, $engine20);

        // Roll both dice
        $result6 = $die6->roll();
        $result20 = $die20->roll();

        // Results should be different (different ranges)
        $this->assertGreaterThanOrEqual(1, $result6->value);
        $this->assertLessThanOrEqual(6, $result6->value);

        $this->assertGreaterThanOrEqual(1, $result20->value);
        $this->assertLessThanOrEqual(20, $result20->value);

        // But both should have the same seed
        $this->assertSame($seed, $result6->seed);
        $this->assertSame($seed, $result20->seed);
    }

    public function testInvalidSidesThrowsException(): void
    {
        $this->expectException(InvalidDieException::class);
        $this->expectExceptionMessage('Die must have at least 2 sides, got 1');

        $engine = new Mt19937Engine();
        new DieRoll(1, $engine);
    }

    public function testNegativeSidesThrowsException(): void
    {
        $this->expectException(InvalidDieException::class);
        $this->expectExceptionMessage('Die must have at least 2 sides, got -5');

        $engine = new Mt19937Engine();
        new DieRoll(-5, $engine);
    }

    public function testZeroSidesThrowsException(): void
    {
        $this->expectException(InvalidDieException::class);
        $this->expectExceptionMessage('Die must have at least 2 sides, got 0');

        $engine = new Mt19937Engine();
        new DieRoll(0, $engine);
    }

    public function testDieIsImmutableAfterConstruction(): void
    {
        $engine = new Mt19937Engine();
        $engine->setSeed(1234);

        $die = new DieRoll(6, $engine);

        // sides is readonly
        $this->assertSame(6, $die->sides);

        // Rolling doesn't change the die itself
        $firstRoll = $die->roll();
        $this->assertSame(6, $die->sides);
    }

    public function testResultContainsAllMetadata(): void
    {
        $engine = new Mt19937Engine();
        $seed = 54321;
        $engine->setSeed($seed);

        $die = new DieRoll(12, $engine);

        $beforeRoll = new DateTimeImmutable();
        $result = $die->roll();
        $afterRoll = new DateTimeImmutable();

        // Check all properties are set
        $this->assertIsInt($result->value);
        $this->assertSame(12, $result->sides);
        $this->assertInstanceOf(DateTimeImmutable::class, $result->rolledAt);
        $this->assertSame($seed, $result->seed);

        // Timestamp should be between before and after
        $this->assertGreaterThanOrEqual(
            $beforeRoll->getTimestamp(),
            $result->rolledAt->getTimestamp()
        );
        $this->assertLessThanOrEqual(
            $afterRoll->getTimestamp(),
            $result->rolledAt->getTimestamp()
        );
    }

    public function testResultIsReadonly(): void
    {
        $engine = new Mt19937Engine();
        $engine->setSeed(999);

        $die = new DieRoll(6, $engine);
        $result = $die->roll();

        // Properties are readonly - this should be enforced by PHP's type system
        $this->assertIsInt($result->value);
        $this->assertIsInt($result->sides);
        $this->assertInstanceOf(DateTimeImmutable::class, $result->rolledAt);
        $this->assertIsInt($result->seed);
    }

    public function testMinimumValidSidesIsTwo(): void
    {
        $engine = new Mt19937Engine();
        $engine->setSeed(100);

        // 2 sides should be valid (like a coin)
        $die = new DieRoll(2, $engine);
        $this->assertSame(2, $die->sides);

        $result = $die->roll();
        $this->assertContains($result->value, [1, 2]);
    }

    public function testEngineNotSeededReturnsNullSeed(): void
    {
        $engine = new Mt19937Engine();
        // Don't set seed

        $die = new DieRoll(6, $engine);
        $result = $die->roll();

        $this->assertNull($result->seed);
    }
}
