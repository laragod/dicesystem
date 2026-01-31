<?php

declare(strict_types=1);

namespace Laragod\DiceSystem\Tests\Unit\Core\Random;

use Laragod\DiceSystem\Core\Random\Mt19937Engine;
use LogicException;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Mt19937Engine - seeded random number generator.
 */
class Mt19937EngineTest extends TestCase
{
    /**
     * Test that the same seed produces an identical sequence of random numbers.
     */
    public function test_same_seed_produces_identical_sequences(): void
    {
        $seed = 42;
        $engine1 = new Mt19937Engine($seed);
        $engine2 = new Mt19937Engine($seed);

        $sequence1 = [];
        $sequence2 = [];

        // Generate 100 random numbers from each engine
        for ($i = 0; $i < 100; $i++) {
            $sequence1[] = $engine1->next(1, 100);
            $sequence2[] = $engine2->next(1, 100);
        }

        $this->assertSame($sequence1, $sequence2, 'Same seed should produce identical sequences');
    }

    /**
     * Test that different seeds produce different sequences.
     */
    public function test_different_seeds_produce_different_sequences(): void
    {
        $engine1 = new Mt19937Engine(42);
        $engine2 = new Mt19937Engine(1337);

        $sequence1 = [];
        $sequence2 = [];

        // Generate 100 random numbers from each engine
        for ($i = 0; $i < 100; $i++) {
            $sequence1[] = $engine1->next(1, 100);
            $sequence2[] = $engine2->next(1, 100);
        }

        $this->assertNotSame($sequence1, $sequence2, 'Different seeds should produce different sequences');
    }

    /**
     * Test that getSeed returns the current seed value.
     */
    public function test_get_seed_returns_current_seed(): void
    {
        $seed = 12345;
        $engine = new Mt19937Engine($seed);

        $this->assertSame($seed, $engine->getSeed(), 'getSeed should return the seed value');
    }

    /**
     * Test that getSeed returns null when no seed is set.
     */
    public function test_get_seed_returns_null_when_not_set(): void
    {
        $engine = new Mt19937Engine();

        $this->assertNull($engine->getSeed(), 'getSeed should return null when no seed is set');
    }

    /**
     * Test that reset restores the engine to its original seeded state.
     */
    public function test_reset_restores_to_original_state(): void
    {
        $engine = new Mt19937Engine(999);

        // Generate initial sequence
        $initialSequence = [];
        for ($i = 0; $i < 50; $i++) {
            $initialSequence[] = $engine->next(1, 100);
        }

        // Reset and generate again
        $engine->reset();
        $resetSequence = [];
        for ($i = 0; $i < 50; $i++) {
            $resetSequence[] = $engine->next(1, 100);
        }

        $this->assertSame($initialSequence, $resetSequence, 'Reset should replay the same sequence');
    }

    /**
     * Test deterministic behavior across multiple resets.
     */
    public function test_deterministic_behavior_verified(): void
    {
        $engine = new Mt19937Engine(7777);

        // Generate, reset, and generate again multiple times
        $sequences = [];
        for ($iteration = 0; $iteration < 3; $iteration++) {
            $sequence = [];
            for ($i = 0; $i < 20; $i++) {
                $sequence[] = $engine->next(1, 6); // Simulate d6
            }
            $sequences[] = $sequence;
            $engine->reset();
        }

        // All sequences should be identical
        $this->assertSame($sequences[0], $sequences[1], 'First and second sequence should match');
        $this->assertSame($sequences[1], $sequences[2], 'Second and third sequence should match');
    }

    /**
     * Test that setSeed can be called on uninitialized engine.
     */
    public function test_set_seed_on_uninitialized_engine(): void
    {
        $engine = new Mt19937Engine();
        $this->assertNull($engine->getSeed());

        $engine->setSeed(555);
        $this->assertSame(555, $engine->getSeed());
    }

    /**
     * Test that setSeed throws exception when called twice.
     */
    public function test_set_seed_throws_exception_when_already_set(): void
    {
        $engine = new Mt19937Engine(100);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Seed is immutable and has already been set');

        $engine->setSeed(200);
    }

    /**
     * Test that next throws exception when seed not set.
     */
    public function test_next_throws_exception_when_seed_not_set(): void
    {
        $engine = new Mt19937Engine();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Seed must be set before generating random numbers');

        $engine->next(1, 10);
    }

    /**
     * Test that reset throws exception when seed not set.
     */
    public function test_reset_throws_exception_when_seed_not_set(): void
    {
        $engine = new Mt19937Engine();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot reset: no seed has been set');

        $engine->reset();
    }

    /**
     * Test that next respects the min/max bounds.
     */
    public function test_next_respects_bounds(): void
    {
        $engine = new Mt19937Engine(123);
        $min = 10;
        $max = 20;

        for ($i = 0; $i < 100; $i++) {
            $value = $engine->next($min, $max);
            $this->assertGreaterThanOrEqual($min, $value, 'Value should be >= min');
            $this->assertLessThanOrEqual($max, $value, 'Value should be <= max');
        }
    }

    /**
     * Test reproducibility with specific known seed.
     */
    public function test_reproducibility_with_known_seed(): void
    {
        // This test verifies that the exact sequence is reproducible
        $engine1 = new Mt19937Engine(42);
        $engine2 = new Mt19937Engine(42);

        $firstValue1 = $engine1->next(1, 1000);
        $firstValue2 = $engine2->next(1, 1000);

        $this->assertSame($firstValue1, $firstValue2, 'First values should match');

        // Generate more values and verify they continue to match
        for ($i = 0; $i < 10; $i++) {
            $this->assertSame(
                $engine1->next(1, 1000),
                $engine2->next(1, 1000),
                "Value at position {$i} should match"
            );
        }
    }

    /**
     * Test that reset can be called multiple times.
     */
    public function test_reset_can_be_called_multiple_times(): void
    {
        $engine = new Mt19937Engine(333);

        $sequence1 = [];
        for ($i = 0; $i < 10; $i++) {
            $sequence1[] = $engine->next(1, 100);
        }

        $engine->reset();
        $sequence2 = [];
        for ($i = 0; $i < 10; $i++) {
            $sequence2[] = $engine->next(1, 100);
        }

        $engine->reset();
        $sequence3 = [];
        for ($i = 0; $i < 10; $i++) {
            $sequence3[] = $engine->next(1, 100);
        }

        $this->assertSame($sequence1, $sequence2);
        $this->assertSame($sequence2, $sequence3);
    }
}
