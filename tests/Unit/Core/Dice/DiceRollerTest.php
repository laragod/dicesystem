<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Dice;

use DateTimeImmutable;
use Laragod\DiceSystem\Core\Dice\DiceRoller;
use Laragod\DiceSystem\Core\Dice\RollResult;
use Laragod\DiceSystem\Core\Random\Mt19937Engine;
use Laragod\DiceSystem\Core\Random\SystemRandomEngine;
use PHPUnit\Framework\TestCase;

class DiceRollerTest extends TestCase
{
    public function test_roll_basic_2d6(): void
    {
        $engine = new Mt19937Engine(12345);
        $roller = new DiceRoller($engine);

        $result = $roller->roll('2d6');

        $this->assertInstanceOf(RollResult::class, $result);
        $this->assertSame('2d6', $result->notation);
        $this->assertCount(2, $result->individualResults);
        $this->assertIsInt($result->total);
        $this->assertGreaterThanOrEqual(2, $result->total); // Minimum 2 (1+1)
        $this->assertLessThanOrEqual(12, $result->total); // Maximum 12 (6+6)
    }

    public function test_roll_1d20(): void
    {
        $engine = new Mt19937Engine(67890);
        $roller = new DiceRoller($engine);

        $result = $roller->roll('1d20');

        $this->assertSame('1d20', $result->notation);
        $this->assertCount(1, $result->individualResults);
        $this->assertGreaterThanOrEqual(1, $result->total);
        $this->assertLessThanOrEqual(20, $result->total);
    }

    public function test_roll_3d8(): void
    {
        $engine = new Mt19937Engine(11111);
        $roller = new DiceRoller($engine);

        $result = $roller->roll('3d8');

        $this->assertSame('3d8', $result->notation);
        $this->assertCount(3, $result->individualResults);
        $this->assertGreaterThanOrEqual(3, $result->total);
        $this->assertLessThanOrEqual(24, $result->total);
    }

    public function test_total_calculation_is_correct(): void
    {
        $engine = new Mt19937Engine(99999);
        $roller = new DiceRoller($engine);

        $result = $roller->roll('4d6');

        $expectedTotal = array_sum($result->getValues());
        $this->assertSame($expectedTotal, $result->total);

        // Verify each individual result contributes to total
        $manualSum = 0;
        foreach ($result->individualResults as $dieResult) {
            $manualSum += $dieResult->value;
        }
        $this->assertSame($manualSum, $result->total);
    }

    public function test_individual_results_tracked(): void
    {
        $engine = new Mt19937Engine(55555);
        $roller = new DiceRoller($engine);

        $result = $roller->roll('3d6');

        $this->assertCount(3, $result->individualResults);

        foreach ($result->individualResults as $dieResult) {
            $this->assertGreaterThanOrEqual(1, $dieResult->value);
            $this->assertLessThanOrEqual(6, $dieResult->value);
            $this->assertSame(6, $dieResult->sides);
        }
    }

    public function test_seeded_rolls_are_reproducible(): void
    {
        $seed = 42;

        $roller1 = new DiceRoller(new Mt19937Engine($seed));
        $result1 = $roller1->roll('2d6');

        $roller2 = new DiceRoller(new Mt19937Engine($seed));
        $result2 = $roller2->roll('2d6');

        $this->assertSame($result1->total, $result2->total);
        $this->assertSame($result1->getValues(), $result2->getValues());
    }

    public function test_different_seeds_produce_different_results(): void
    {
        $roller1 = new DiceRoller(new Mt19937Engine(1111));
        $roller2 = new DiceRoller(new Mt19937Engine(2222));

        $results1 = [];
        $results2 = [];

        // Roll multiple times to ensure statistical difference
        for ($i = 0; $i < 10; $i++) {
            $results1[] = $roller1->roll('2d6')->total;
            $results2[] = $roller2->roll('2d6')->total;
        }

        // Arrays should be different (statistically extremely unlikely to be identical)
        $this->assertNotSame($results1, $results2);
    }

    public function test_notation_is_preserved_in_result(): void
    {
        $engine = new Mt19937Engine(77777);
        $roller = new DiceRoller($engine);

        $result = $roller->roll('5d10');
        $this->assertSame('5d10', $result->notation);

        $result = $roller->roll('1D4'); // Case insensitive
        $this->assertSame('1D4', $result->notation);
    }

    public function test_timestamp_is_set(): void
    {
        $roller = new DiceRoller(new Mt19937Engine(33333));

        $before = new DateTimeImmutable();
        $result = $roller->roll('2d6');
        $after = new DateTimeImmutable();

        $this->assertInstanceOf(DateTimeImmutable::class, $result->rolledAt);
        $this->assertGreaterThanOrEqual($before->getTimestamp(), $result->rolledAt->getTimestamp());
        $this->assertLessThanOrEqual($after->getTimestamp(), $result->rolledAt->getTimestamp());
    }

    public function test_unseeded_roller_works(): void
    {
        // Should use SystemRandomEngine by default
        $roller = new DiceRoller();

        $result = $roller->roll('2d6');

        $this->assertInstanceOf(RollResult::class, $result);
        $this->assertSame('2d6', $result->notation);
        $this->assertCount(2, $result->individualResults);
        $this->assertGreaterThanOrEqual(2, $result->total);
        $this->assertLessThanOrEqual(12, $result->total);
        $this->assertNull($result->seed); // System random has no seed
    }

    public function test_unseeded_system_random_produces_varied_results(): void
    {
        $roller = new DiceRoller(new SystemRandomEngine());

        $results = [];
        for ($i = 0; $i < 20; $i++) {
            $results[] = $roller->roll('1d6')->total;
        }

        // Should have some variety (not all the same)
        $unique = array_unique($results);
        $this->assertGreaterThan(1, count($unique));
    }

    public function test_seed_is_preserved_in_result(): void
    {
        $seed = 88888;
        $engine = new Mt19937Engine($seed);
        $roller = new DiceRoller($engine);

        $result = $roller->roll('2d6');

        $this->assertSame($seed, $result->seed);
    }

    public function test_get_values_returns_array_of_integers(): void
    {
        $engine = new Mt19937Engine(44444);
        $roller = new DiceRoller($engine);

        $result = $roller->roll('3d6');
        $values = $result->getValues();

        $this->assertIsArray($values);
        $this->assertCount(3, $values);

        foreach ($values as $value) {
            $this->assertIsInt($value);
            $this->assertGreaterThanOrEqual(1, $value);
            $this->assertLessThanOrEqual(6, $value);
        }
    }

    public function test_to_string_format(): void
    {
        $engine = new Mt19937Engine(12345);
        $roller = new DiceRoller($engine);

        $result = $roller->roll('2d6');
        $string = (string) $result;

        // Should match format "2d6: [X, Y] = Z"
        $this->assertMatchesRegularExpression('/^2d6: \[\d+, \d+\] = \d+$/', $string);
        $this->assertStringContainsString('2d6:', $string);
        $this->assertStringContainsString('[', $string);
        $this->assertStringContainsString(']', $string);
        $this->assertStringContainsString('=', $string);
    }
}
