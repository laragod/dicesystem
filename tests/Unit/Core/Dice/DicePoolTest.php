<?php

declare(strict_types=1);

namespace Laragod\DiceSystem\Tests\Unit\Core\Dice;

use DateTimeImmutable;
use InvalidArgumentException;
use Laragod\DiceSystem\Core\Contract\RandomEngineInterface;
use Laragod\DiceSystem\Core\Dice\Die;
use Laragod\DiceSystem\Core\Dice\DicePool;
use Laragod\DiceSystem\Core\Dice\DieResult;
use Laragod\DiceSystem\Core\Dice\PoolResult;
use PHPUnit\Framework\TestCase;

class DicePoolTest extends TestCase
{
    private RandomEngineInterface $engine;

    protected function setUp(): void
    {
        $this->engine = $this->createMock(RandomEngineInterface::class);
    }

    public function test_empty_pool_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('DicePool must contain at least one die');

        new DicePool([], $this->engine);
    }

    public function test_rollAll_with_multiple_dice(): void
    {
        $this->engine->method('next')
            ->willReturnOnConsecutiveCalls(3, 5, 2);
        $this->engine->method('getSeed')->willReturn(12345);

        $dice = [
            new Die(6, $this->engine),
            new Die(6, $this->engine),
            new Die(6, $this->engine),
        ];

        $pool = new DicePool($dice, $this->engine);
        $result = $pool->rollAll();

        $this->assertInstanceOf(PoolResult::class, $result);
        $this->assertCount(3, $result->results);
        $this->assertSame([3, 5, 2], $result->getValues());
        $this->assertSame(12345, $result->seed);
    }

    public function test_sum_calculation(): void
    {
        $this->engine->method('next')
            ->willReturnOnConsecutiveCalls(3, 5, 2);

        $dice = [
            new Die(6, $this->engine),
            new Die(6, $this->engine),
            new Die(6, $this->engine),
        ];

        $pool = new DicePool($dice, $this->engine);
        $result = $pool->rollAll();

        $this->assertSame(10, $result->sum());
    }

    public function test_average_calculation(): void
    {
        $this->engine->method('next')
            ->willReturnOnConsecutiveCalls(2, 4, 6);

        $dice = [
            new Die(6, $this->engine),
            new Die(6, $this->engine),
            new Die(6, $this->engine),
        ];

        $pool = new DicePool($dice, $this->engine);
        $result = $pool->rollAll();

        $this->assertSame(4.0, $result->average());
    }

    public function test_highest_returns_correct_results(): void
    {
        $this->engine->method('next')
            ->willReturnOnConsecutiveCalls(3, 5, 2, 6);

        $dice = [
            new Die(6, $this->engine),
            new Die(6, $this->engine),
            new Die(6, $this->engine),
            new Die(6, $this->engine),
        ];

        $pool = new DicePool($dice, $this->engine);
        $result = $pool->rollAll();

        $highest = $result->highest(2);

        $this->assertCount(2, $highest);
        $this->assertSame(6, $highest[0]->value);
        $this->assertSame(5, $highest[1]->value);
    }

    public function test_lowest_returns_correct_results(): void
    {
        $this->engine->method('next')
            ->willReturnOnConsecutiveCalls(3, 5, 2, 6);

        $dice = [
            new Die(6, $this->engine),
            new Die(6, $this->engine),
            new Die(6, $this->engine),
            new Die(6, $this->engine),
        ];

        $pool = new DicePool($dice, $this->engine);
        $result = $pool->rollAll();

        $lowest = $result->lowest(2);

        $this->assertCount(2, $lowest);
        $this->assertSame(2, $lowest[0]->value);
        $this->assertSame(3, $lowest[1]->value);
    }

    public function test_keep_highest_for_advantage_mechanics(): void
    {
        // D&D advantage: roll 2d20, keep highest
        $this->engine->method('next')
            ->willReturnOnConsecutiveCalls(12, 17);

        $dice = [
            new Die(20, $this->engine),
            new Die(20, $this->engine),
        ];

        $pool = new DicePool($dice, $this->engine);
        $result = $pool->keep(1, 'highest');

        $this->assertCount(1, $result->results);
        $this->assertSame(17, $result->results[0]->value);
    }

    public function test_keep_lowest_for_disadvantage_mechanics(): void
    {
        // D&D disadvantage: roll 2d20, keep lowest
        $this->engine->method('next')
            ->willReturnOnConsecutiveCalls(12, 17);

        $dice = [
            new Die(20, $this->engine),
            new Die(20, $this->engine),
        ];

        $pool = new DicePool($dice, $this->engine);
        $result = $pool->keep(1, 'lowest');

        $this->assertCount(1, $result->results);
        $this->assertSame(12, $result->results[0]->value);
    }

    public function test_keep_invalid_mode_throws_exception(): void
    {
        $dice = [new Die(6, $this->engine)];
        $pool = new DicePool($dice, $this->engine);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Mode must be 'highest' or 'lowest', got 'invalid'");

        $pool->keep(1, 'invalid');
    }

    public function test_mixed_die_types_in_pool(): void
    {
        // 2d6 + 1d4 + 1d8 (common RPG damage roll)
        $this->engine->method('next')
            ->willReturnOnConsecutiveCalls(4, 5, 3, 7);

        $dice = [
            new Die(6, $this->engine),  // 4
            new Die(6, $this->engine),  // 5
            new Die(4, $this->engine),  // 3
            new Die(8, $this->engine),  // 7
        ];

        $pool = new DicePool($dice, $this->engine);
        $result = $pool->rollAll();

        $this->assertCount(4, $result->results);
        $this->assertSame([4, 5, 3, 7], $result->getValues());
        $this->assertSame(19, $result->sum());
    }

    public function test_reroll_with_predicate(): void
    {
        // Re-roll all 1s (common house rule)
        $this->engine->method('next')
            ->willReturnOnConsecutiveCalls(
                1, 4,  // First die: rolls 1, rerolls to 4
                3,     // Second die: rolls 3, keeps it
                1, 5,  // Third die: rolls 1, rerolls to 5
            );

        $dice = [
            new Die(6, $this->engine),
            new Die(6, $this->engine),
            new Die(6, $this->engine),
        ];

        $pool = new DicePool($dice, $this->engine);
        $result = $pool->reroll(fn(DieResult $r) => $r->value === 1);

        $this->assertSame([4, 3, 5], $result->getValues());
    }

    public function test_deterministic_behavior_with_seeds(): void
    {
        $engine1 = $this->createMock(RandomEngineInterface::class);
        $engine1->method('next')->willReturnOnConsecutiveCalls(3, 5, 2);
        $engine1->method('getSeed')->willReturn(42);

        $engine2 = $this->createMock(RandomEngineInterface::class);
        $engine2->method('next')->willReturnOnConsecutiveCalls(3, 5, 2);
        $engine2->method('getSeed')->willReturn(42);

        $dice1 = [
            new Die(6, $engine1),
            new Die(6, $engine1),
            new Die(6, $engine1),
        ];

        $dice2 = [
            new Die(6, $engine2),
            new Die(6, $engine2),
            new Die(6, $engine2),
        ];

        $pool1 = new DicePool($dice1, $engine1);
        $pool2 = new DicePool($dice2, $engine2);

        $result1 = $pool1->rollAll();
        $result2 = $pool2->rollAll();

        $this->assertSame($result1->getValues(), $result2->getValues());
        $this->assertSame($result1->seed, $result2->seed);
    }

    public function test_adding_dice_to_pool(): void
    {
        $dice = [new Die(6, $this->engine)];
        $pool = new DicePool($dice, $this->engine);

        $this->assertSame(1, $pool->count());

        $pool->add(new Die(6, $this->engine));
        $this->assertSame(2, $pool->count());

        $pool->add(new Die(8, $this->engine));
        $this->assertSame(3, $pool->count());
    }

    public function test_count_returns_number_of_dice(): void
    {
        $dice = [
            new Die(6, $this->engine),
            new Die(6, $this->engine),
            new Die(6, $this->engine),
            new Die(6, $this->engine),
        ];

        $pool = new DicePool($dice, $this->engine);

        $this->assertSame(4, $pool->count());
    }

    public function test_pool_result_empty_array_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('PoolResult must contain at least one die result');

        new PoolResult(
            results: [],
            rolledAt: new DateTimeImmutable(),
            seed: null,
        );
    }

    public function test_get_values_extracts_int_array(): void
    {
        $this->engine->method('next')
            ->willReturnOnConsecutiveCalls(2, 4, 6);

        $dice = [
            new Die(6, $this->engine),
            new Die(6, $this->engine),
            new Die(6, $this->engine),
        ];

        $pool = new DicePool($dice, $this->engine);
        $result = $pool->rollAll();

        $values = $result->getValues();

        $this->assertSame([2, 4, 6], $values);
        $this->assertContainsOnly('int', $values);
    }
}
